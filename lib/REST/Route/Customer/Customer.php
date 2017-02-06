<?php
/**
 * Single Customer Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Customer;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Putable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route;
use iThemes\Exchange\REST\RouteObjectExpandable;

/**
 * Class Customer
 *
 * @package iThemes\Exchange\REST\Customer
 */
class Customer extends Route\Base implements Getable, Putable, RouteObjectExpandable {

	/** @var array */
	private $schema = array();

	/** @var Serializer */
	private $serializer;

	/**
	 * Customer constructor.
	 *
	 * @param Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$customer = $request->get_route_object( 'customer_id' );
		$response = new \WP_REST_Response( $this->serializer->serialize( $customer, $request['context'] ) );

		$this->linkify( $response, $customer );

		return $response;
	}

	/**
	 * Linkify the response.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_REST_Response $response
	 * @param \IT_Exchange_Customer $customer
	 */
	protected function linkify( \WP_REST_Response $response, \IT_Exchange_Customer $customer ) {

		$address = $this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\Customer\Address\Address' );

		if ( $a = $customer->get_billing_address( true ) ) {
			$response->add_link(
				'billing-address',
				\iThemes\Exchange\REST\get_rest_url( $address, array(
					'customer_id' => $customer->get_ID(),
					'address_id'  => $a->get_pk()
				) ),
				array( 'embeddable' => true )
			);
		}

		if ( $a = $customer->get_shipping_address( true ) ) {
			$response->add_link(
				'shipping-address',
				\iThemes\Exchange\REST\get_rest_url( $address, array(
					'customer_id' => $customer->get_ID(),
					'address_id'  => $a->get_pk()
				) ),
				array( 'embeddable' => true )
			);
		}

		$response->add_link(
			'addresses',
			\iThemes\Exchange\REST\get_rest_url(
				$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\Customer\Address\Addresses' ),
				array( 'customer_id' => $customer->get_ID() )
			)
		);

		$tokens = $this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\Customer\Token\Tokens' );
		$response->add_link(
			'tokens',
			\iThemes\Exchange\REST\get_rest_url( $tokens, array( 'customer_id' => $customer->ID ) ),
			array( 'embeddable' => true )
		);

		$session = \ITE_Session_Model::find_best_for_customer( $customer );

		if ( $session && $session->cart_id ) {
			$response->add_link(
				'cart',
				\iThemes\Exchange\REST\get_rest_url(
					$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\Cart\Cart' ),
					array( 'cart_id' => $session->cart_id )
				)
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {

		if ( ! $user || $user instanceof \IT_Exchange_Guest_Customer ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to access this customer.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		$customer = $request->get_route_object( 'customer_id' );

		if ( ! $customer ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_customer',
				__( 'Invalid customer.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::NOT_FOUND )
			);
		}

		if ( ! user_can( $user->wp_user, 'edit_user', $customer->ID ) ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to access this customer.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_put( Request $request ) {

		$customer = it_exchange_get_customer( $request->get_param( 'customer_id', 'URL' ) );

		if ( $request['billing_address'] ) {
			$e = $this->handle_address_update( $customer, $request['billing_address'], 'billing' );

			if ( is_wp_error( $e ) ) {
				return $e;
			}
		}

		if ( $request['shipping_address'] ) {
			$e = $this->handle_address_update( $customer, $request['shipping_address'], 'shipping' );

			if ( is_wp_error( $e ) ) {
				return $e;
			}
		}

		$response = new \WP_REST_Response( $this->serializer->serialize( $customer, 'edit' ), \WP_Http::OK );
		$this->linkify( $response, $customer );

		return $response;
	}

	/**
	 * Handle an address update.
	 *
	 * @since 2.0.0
	 *
	 * @param \IT_Exchange_Customer $customer
	 * @param array|int             $input
	 * @param string                $type
	 *
	 * @return null|\WP_Error
	 */
	protected function handle_address_update( \IT_Exchange_Customer $customer, $input, $type ) {

		if ( is_int( $input ) ) {

			if ( ( $current = call_user_func( array(
					$customer,
					"get_{$type}_address"
				), true ) ) && $current->get_pk() == $input
			) {
				return null;
			}

			$address = \ITE_Saved_Address::get( $input );

			if ( ! $address || $address->is_trashed() || ! $address->customer || $address->customer->get_ID() != $customer->get_ID() ) {
				return new \WP_Error(
					'it_exchange_rest_invalid_address',
					__( 'Invalid address ID.', 'it-l10n-ithemes-exchange' ),
					array( 'status' => \WP_Http::BAD_REQUEST )
				);
			}
		} elseif ( is_array( $input ) ) {
			$address = new \ITE_In_Memory_Address( $input );
		} else {
			return null;
		}

		try {
			if ( call_user_func( array( $customer, "set_{$type}_address" ), $address ) ) {
				return null;
			}
		} catch ( \InvalidArgumentException $e ) {
			return new \WP_Error(
				'it_exchange_rest_address_failed_validation',
				__( 'Address failed to verification.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::BAD_REQUEST )
			);
		}

		return new \WP_Error(
			'it_exchange_rest_unable_to_create_address',
			__( 'Unable to create an address from the data provided.', 'it-l10n-ithemes-exchange' ),
			array( 'status' => \WP_Http::INTERNAL_SERVER_ERROR )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( Request $request, \IT_Exchange_Customer $user = null ) {
		return true; // Cascades
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return 'customers/(?P<customer_id>\d+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_route_object_map() { return array( 'customer_id' => 'it_exchange_get_customer' ); }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() {

		if ( ! $this->schema ) {
			$this->schema = $this->serializer->get_schema();
		}

		return $this->schema;
	}
}
