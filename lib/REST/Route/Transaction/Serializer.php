<?php
/**
 * Serialize a transaction.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Transaction;

use iThemes\Exchange\REST\Manager;
use iThemes\Exchange\REST as r;

/**
 * Class Serializer
 * @package iThemes\Exchange\REST\Route\Transaction
 */
class Serializer {

	/**
	 * Serialize a transaction.
	 *
	 * @since 1.36.0
	 *
	 * @param \IT_Exchange_Transaction $transaction
	 * @param \IT_Exchange_Customer    $user
	 *
	 * @return array
	 */
	public function serialize( \IT_Exchange_Transaction $transaction, \IT_Exchange_Customer $user ) {
		$t = $transaction;

		return array(
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
			'open_for_refund'      => it_exchange_transaction_can_be_refunded( $t ),
			'currency'             => $t->get_currency(),
			'description'          => $t->get_description(),
			'billing_address'      => $t->get_billing_address() ? $t->get_billing_address()->to_array() : array(),
			'shipping_address'     => $t->get_shipping_address() ? $t->get_shipping_address()->to_array() : array(),
		);
	}

	/**
	 * Generate links for a transaction.
	 *
	 * @since 1.36.0
	 *
	 * @param \IT_Exchange_Transaction       $transaction
	 * @param \iThemes\Exchange\REST\Manager $manager
	 * @param \IT_Exchange_Customer          $user
	 *
	 * @return array
	 */
	public function generate_links( \IT_Exchange_Transaction $transaction, Manager $manager, \IT_Exchange_Customer $user ) {

		$t = $transaction;

		$links = array();

		if ( $t->get_customer() ) {
			$route               = $manager->get_first_route( 'iThemes\Exchange\REST\Route\Customer\Customer' );
			$links['customer'][] = array(
				'href'       => r\get_rest_url( $route, array( 'customer_id' => $t->get_customer()->ID ) ),
				'embeddable' => true
			);
		}

		if ( $t->has_parent() ) {
			$route             = $manager->get_first_route( 'iThemes\Exchange\REST\Route\Transaction\Transaction' );
			$links['parent'][] = array(
				'href'       => r\get_rest_url( $route, array( 'transaction_id' => $t->get_parent()->ID ) ),
				'embeddable' => true
			);
		}

		if ( $t->payment_token && $t->get_customer() ) {
			$route                    = $manager->get_first_route( 'iThemes\Exchange\REST\Route\Customer\Token\Token' );
			$links['payment_token'][] = array(
				'href'       => r\get_rest_url( $route, array(
					'customer_id' => $t->get_customer()->ID,
					'token_id'    => $t->payment_token->ID
				) ),
				'embeddable' => true
			);
		}

		return $links;
	}

	/**
	 * Get the transaction schema.
	 *
	 * @since 1.36.0
	 *
	 * @return array
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
				'open_for_refund'      => array(
					'description' => __( 'Is the transaction open for refunds.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'boolean',
					'readonly'    => true,
					'context'     => array( 'edit' ),
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