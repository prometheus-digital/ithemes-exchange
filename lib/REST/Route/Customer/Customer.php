<?php
/**
 * Single Customer Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Customer;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route;

/**
 * Class Customer
 * @package iThemes\Exchange\REST\Customer
 */
class Customer extends Route\Base implements Getable {

	/** @var array */
	private $schema = array();

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$customer = it_exchange_get_customer( $request->get_param( 'customer_id', 'URL' ) );

		$data = array(
			'id'               => $customer->ID,
			'email'            => $customer->get_email(),
			'display_name'     => $customer->get_display_name(),
			'first_name'       => $customer->get_first_name(),
			'last_name'        => $customer->get_last_name(),
			'avatar_url'       => get_avatar_url( $customer->get_email() ),
			'billing_address'  => ( $a = $customer->get_billing_address() ) ? $a->to_array() : array(),
			'shipping_address' => ( $a = $customer->get_shipping_address() ) ? $a->to_array() : array(),
			'created_at'       => date( 'c', strtotime( $customer->wp_user->user_registered ) )
		);

		if ( $request['context'] === 'stats' ) {
			$data['total_spent']       = $customer->get_total_spent();
			$data['transaction_count'] = $customer->get_transactions_count();
		}

		$response = new \WP_REST_Response( $data );

		$this->linkify( $response, $customer );

		return $response;
	}

	/**
	 * Linkify the response.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_REST_Response     $response
	 * @param \IT_Exchange_Customer $customer
	 */
	protected function linkify( \WP_REST_Response $response, \IT_Exchange_Customer $customer ) {

		$tokens = new Route\Customer\Token\Tokens(
			new Route\Customer\Token\Serializer(),
			new \ITE_Gateway_Request_Factory()
		);
		$tokens->set_parent( $this );
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

		$customer = it_exchange_get_customer( $request->get_param( 'customer_id', 'URL' ) );

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
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return 'customers/(?P<customer_id>\d+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() {

		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'customer',
			'type'       => 'object',
			'properties' => array(
				'id'                => array(
					'description' => __( 'The unique id for this customer.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'stats', 'embed' ),
					'readonly'    => true,
				),
				'email'             => array(
					'description' => __( "The customer's email address.", 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'format'      => 'email',
					'context'     => array( 'view', 'edit', 'stats', 'embed' ),
				),
				'display_name'      => array(
					'description' => __( "The customer's display name.", 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'stats', 'embed' ),
					'readonly'    => true,
				),
				'first_name'        => array(
					'description' => __( "The customer's first name.", 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'stats', 'embed' ),
				),
				'last_name'         => array(
					'description' => __( "The customer's last name.", 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'stats', 'embed' ),
				),
				'avatar_url'        => array(
					'description' => __( "The customer's avatar URL.", 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'format'      => 'url',
					'context'     => array( 'view', 'edit', 'stats', 'embed' ),
				),
				'created_at'        => array(
					'description' => __( 'The date the customer was created.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit', 'stats', 'embed' ),
				),
				'billing_address'   => array(
					'description' => __( 'The billing address for this cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'first-name'   => array(
							'description' => __( 'The first name of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'last-name'    => array(
							'description' => __( 'The last name of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'company-name' => array(
							'description' => __( 'The company name of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'address1'     => array(
							'description' => __( 'The address line 1 of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'address2'     => array(
							'description' => __( 'The address line 2 of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'city'         => array(
							'description' => __( 'The city of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'state'        => array(
							'description' => __( 'The state two-letter abbreviation of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'country'      => array(
							'description' => __( 'The country two-letter abbreviation of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'zip'          => array(
							'description' => __( 'The zip code of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'email'        => array(
							'description' => __( 'The email address of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'format'      => 'email',
						),
						'phone'        => array(
							'description' => __( 'The phone number of the billing address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
					)
				),
				'shipping_address'  => array(
					'description' => __( 'The shipping address for this cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'first-name'   => array(
							'description' => __( 'The first name of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'last-name'    => array(
							'description' => __( 'The last name of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'company-name' => array(
							'description' => __( 'The company name of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'address1'     => array(
							'description' => __( 'The address line 1 of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'address2'     => array(
							'description' => __( 'The address line 2 of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'city'         => array(
							'description' => __( 'The city of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'state'        => array(
							'description' => __( 'The state two-letter abbreviation of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'country'      => array(
							'description' => __( 'The country two-letter abbreviation of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'zip'          => array(
							'description' => __( 'The zip code of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'email'        => array(
							'description' => __( 'The email address of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'format'      => 'email',
						),
						'phone'        => array(
							'description' => __( 'The phone number of the shipping address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
					)
				),
				'total_spent'       => array(
					'description' => __( 'The total amount the customer has spent in the store.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'number',
					'context'     => array( 'stats' ),
				),
				'transaction_count' => array(
					'description' => __( 'The total number of transactions this customer created.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'stats' ),
				),
			),
		);

		return $this->schema;
	}
}
