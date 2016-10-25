<?php
/**
 * Transaction Endpoint.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Transaction;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;
use iThemes\Exchange\REST\Route\Customer\Customer;

/**
 * Class Transaction
 * @package iThemes\Exchange\REST\Route\Transaction
 */
class Transaction extends Base implements Getable {

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$t = it_exchange_get_transaction( $request->get_param( 'transaction_id', 'URL' ) );

		$data = array(
			'id'                   => $t->get_ID(),
			'customer'             => $t->get_customer() ? $t->get_customer()->ID : 0,
			'customer_email'       => $t->get_customer_email(),
			'customer_ip'          => $t->get_customer_ip(),
			'method'               => array( 'slug' => $t->get_method(), 'label' => $t->get_method( true ) ),
			'method_id'            => $t->get_method_id(),
			'status'               => array( 'slug' => $t->get_status(), 'label' => $t->get_status( true ) ),
			'cleared_for_delivery' => $t->is_cleared_for_delivery(),
			'order_date'           => mysql_to_rfc3339( $t->get_date() ),
			'payment_token'        => $t->payment_token ? $t->payment_token->ID : 0,
			'purchase_mode'        => $t->purchase_mode,
			'parent'               => $t->has_parent() ? $t->get_parent()->get_ID() : 0,
			'subtotal'             => $t->get_subtotal(),
			'total'                => $t->get_total(),
			'total_before_refunds' => $t->get_total( false ),
			'currency'             => $t->get_currency(),
			'description'          => $t->get_description(),
			'billing_address'      => $t->get_billing_address() ? $t->get_billing_address()->to_array() : array(),
			'shipping_address'     => $t->get_shipping_address() ? $t->get_shipping_address()->to_array() : array(),
		);

		$response = new \WP_REST_Response( $data );

		if ( $data['customer'] ) {
			$response->add_link(
				'customer',
				\iThemes\Exchange\REST\get_rest_url( new Customer(), array( 'customer_id' => $data['customer'] ) ),
				array( 'embeddable' => true )
			);
		}

		if ( $data['parent'] ) {
			$response->add_link(
				'parent',
				\iThemes\Exchange\REST\get_rest_url( $this, array( 'transaction_id' => $data['parent'] ) ),
				array( 'embeddable' => true )
			);
		}

		if ( $data['payment_token'] && $data['customer'] ) {
			$token = $this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\Customer\Token\Token' );
			$response->add_link(
				'payment_token',
				\iThemes\Exchange\REST\get_rest_url( $token, array(
					'customer_id' => $data['customer'],
					'token_id'    => $data['payment_token'],
				) ),
				array( 'embeddable' => true )
			);
		}

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {

		$cap = $request['context'] === 'view' ? 'read_it_transaction' : 'edit_it_transaction';

		if ( ! $user || ! user_can( $user->wp_user, $cap, $request->get_param( 'transaction_id', 'URL' ) ) ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to access this transaction.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::UNAUTHORIZED )
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
	public function get_path() { return 'transactions/(?P<transaction_id>\d+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'transaction',
			'type'       => 'object',
			'properties' => array(
				'id'                   => array(
					'description' => __( 'The unique id for this transaction.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'customer'             => array(
					'description' => __( 'The id of the customer for this transaction.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'customer_email'       => array(
					'description' => __( 'The email address of the customer for this transaction.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'format'      => 'email',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'customer_ip'          => array(
					'description' => __( 'The IP address of the customer for this transaction.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'method'               => array(
					'description' => __( 'The transaction method used for this transaction.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
					'properties'  => array(
						'slug'  => array(
							'description' => __( 'The transaction method slug.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'label' => array(
							'description' => __( 'The transaction method name.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' )
						),
					),
				),
				'method_id'            => array(
					'description' => __( 'The method id used for this transaction.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'status'               => array(
					'description' => __( 'The transaction status.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'slug'  => array(
							'description' => __( 'The transaction status slug.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'edit' )
						),
						'label' => array(
							'description' => __( 'The transaction status label.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' )
						),
					),
				),
				'cleared_for_delivery' => array(
					'description' => __( 'The cleared for delivery status of the transaction.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'order_date'           => array(
					'description' => __( 'The date the transaction was placed, as GMT.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'payment_token'        => array(
					'description' => __( 'The payment token used for this transaction.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'purchase_mode'        => array(
					'description' => __( 'The mode the transaction was created in.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'enum'        => array(
						\IT_Exchange_Transaction::P_MODE_SANDBOX,
						\IT_Exchange_Transaction::P_MODE_LIVE,
					),
					'readonly'    => true,
				),
				'parent'               => array(
					'description' => __( 'The id of the parent transaction.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'subtotal'             => array(
					'description' => __( 'The transaction subtotal.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'float',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'total'                => array(
					'description' => __( 'The transaction total.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'float',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'total_before_refunds' => array(
					'description' => __( 'The transaction total before refunds have been applied.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'float',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'currency'             => array(
					'description' => __( 'The transaction currency.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'description'          => array(
					'description' => __( 'The transaction description.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'billing_address'      => array(
					'description' => __( 'The billing address for this transaction.', 'it-l10n-ithemes-exchange' ),
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
				'shipping_address'     => array(
					'description' => __( 'The shipping address for this transaction.', 'it-l10n-ithemes-exchange' ),
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
			)
		);
	}
}