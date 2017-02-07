<?php
/**
 * Contains the cart route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Cart;

use iThemes\Exchange\REST as r;
use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Putable;
use iThemes\Exchange\REST\Request;

/**
 * Class Cart
 *
 * @package iThemes\Exchange\REST\Route\v1\Cart
 */
class Cart extends r\Route\Base implements Getable, Putable, Deletable, r\RouteObjectExpandable {

	/** @var Serializer */
	private $serializer;

	/**
	 * Cart constructor.
	 *
	 * @param Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		if ( ! $request->get_route_object( 'cart_id' ) ) {
			return new \WP_REST_Response( array(), 500 );
		}

		return $this->prepare_item_for_response( $request->get_route_object( 'cart_id' ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {
		return $this->permission_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_put( Request $request ) {

		/** @var \ITE_Cart $cart */
		$cart = $request->get_route_object( 'cart_id' );

		if ( $e = $this->handle_address_update( $cart, $request['billing_address'], 'billing') ) {
			return $e;
		}

		if ( $e = $this->handle_address_update( $cart, $request['shipping_address'], 'shipping') ) {
			return $e;
		}

		return $this->prepare_item_for_response( $cart );
	}

	/**
	 * Handle an address update.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 * @param array|int $input
	 * @param string    $type
	 *
	 * @return null|\WP_Error
	 */
	protected function handle_address_update( \ITE_Cart $cart, $input, $type ) {

		$current = call_user_func( array( $cart, "get_{$type}_address" ) );

		if ( is_int( $input ) ) {

			if ( $current instanceof \ITE_Saved_Address && $current->get_pk() == $input ) {
				return null;
			}

			$address = \ITE_Saved_Address::get( $input );

			if ( ! $address || $address->is_trashed() || ! $address->customer || $address->customer->get_ID() != $cart->get_customer()->get_ID() ) {
				return new \WP_Error(
					'it_exchange_rest_invalid_address',
					__( 'Invalid address ID.', 'it-l10n-ithemes-exchange' ),
					array( 'status' => \WP_Http::BAD_REQUEST )
				);
			}
		} elseif ( is_array( $input ) ) {
			$address = new \ITE_In_Memory_Address( $input );

			if ( $address->equals( $current ) ) {
				return null;
			}
		} else {
			return null;
		}

		if ( call_user_func( array( $cart, "set_{$type}_address" ), $address ) ) {
			return null;
		} else {
			return new \WP_Error(
				'it_exchange_rest_address_failed_validation',
				__( 'Address failed to verification.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::BAD_REQUEST )
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( Request $request, \IT_Exchange_Customer $user = null ) {
		return $this->permission_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_delete( Request $request ) {

		/** @var \ITE_Cart $cart */
		$cart = $request->get_route_object( 'cart_id' );
		$cart->empty_cart();

		return new \WP_HTTP_Response( '', 204 );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( Request $request, \IT_Exchange_Customer $user = null ) {
		return $this->permission_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return '(?P<cart_id>\w+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_route_object_map() { return array( 'cart_id' => 'it_exchange_get_cart' ); }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function has_parent() { return true; }

	/**
	 * @inheritDoc
	 */
	public function get_parent() { return new Carts( $this ); }

	/**
	 * Perform a permission check.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 * @param \IT_Exchange_Customer          $user
	 *
	 * @return bool|\WP_Error
	 */
	protected function permission_check( Request $request, \IT_Exchange_Customer $user = null ) {

		/** @var \ITE_Cart $cart */
		if ( ! $cart = $request->get_route_object( 'cart_id' ) ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_cart',
				__( 'Invalid cart id.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => 404 )
			);
		}

		if ( $cart->is_guest() ) {
			if ( $user instanceof \IT_Exchange_Guest_Customer && $user->get_email() === $cart->get_customer()->get_email() ) {
				return true;
			}
		}

		if ( $cart->get_customer() && $user && $cart->get_customer()->id === $user->id ) {
			return true;
		}

		return new \WP_Error(
			'it_exchange_rest_forbidden_context',
			__( 'Sorry, you are not allowed to access this cart.', 'it-l10n-ithemes-exchange' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	/**
	 * Prepare a cart for response.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 *
	 * @return \WP_REST_Response
	 */
	protected function prepare_item_for_response( \ITE_Cart $cart ) {

		$data = $this->serializer->serialize( $cart );

		$response = new \WP_REST_Response( $data );

		$shipping_methods = new Shipping_Methods();
		$shipping_methods->set_parent( $this );
		$response->add_link( 'shipping_methods', r\get_rest_url( $shipping_methods, array( 'cart_id' => $cart->get_id() ) ) );

		if ( $cart->get_customer() && ! $cart->get_customer() instanceof \IT_Exchange_Guest_Customer ) {
			$response->add_link(
				'customer',
				r\get_rest_url(
					$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\v1\Customer\Customer' ),
					array( 'customer_id' => $cart->get_customer()->ID )
				),
				array( 'embeddable' => true )
			);
		}

		$response->add_link(
			'purchase-methods',
			r\get_rest_url(
				$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\v1\Cart\Purchase' ),
				array( 'cart_id' => $cart->get_id() )
			),
			array( 'embeddable' => true )
		);

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}