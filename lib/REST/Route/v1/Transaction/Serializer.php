<?php
/**
 * Serialize a transaction.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Transaction;

use iThemes\Exchange\REST\Manager;
use iThemes\Exchange\REST as r;

/**
 * Class Serializer
 *
 * @package iThemes\Exchange\REST\Route\v1\Transaction
 */
class Serializer {

	/**
	 * Serialize a transaction.
	 *
	 * @since 2.0.0
	 *
	 * @param \IT_Exchange_Transaction $transaction
	 * @param int                      $size
	 *
	 * @return array
	 */
	public function serialize( \IT_Exchange_Transaction $transaction, $size = 96 ) {
		$t = $transaction;

		$payment = array();

		if ( $source = $t->get_payment_source() ) {
			$payment['label'] = $source->get_label();
		}

		if ( $card = $t->get_card() ) {
			$payment['card'] = array(
				'number' => $card->get_redacted_number(),
				'year'   => $card->get_expiration_year(),
				'month'  => $card->get_expiration_month(),
				'name'   => $card->get_holder_name(),
			);
		} elseif ( $t->payment_token ) {
			$payment['token'] = $t->payment_token->get_ID();
		}

		return array(
			'id'                   => $t->get_ID(),
			'order_number'         => $t->get_order_number(),
			'customer'             => $t->get_customer() ? $t->get_customer()->ID : 0,
			'customer_email'       => $t->get_customer_email(),
			'customer_ip'          => $t->get_customer_ip(),
			'customer_name'        => it_exchange_get_transaction_customer_display_name( $t ),
			'customer_avatar'      => get_avatar_url( $t->get_customer_email(), array( 'size' => $size ) ),
			'method'               => array( 'slug' => $t->get_method(), 'label' => $t->get_method( true ) ),
			'method_id'            => $t->get_method_id(),
			'status'               => array( 'slug' => $t->get_status(), 'label' => $t->get_status( true ) ),
			'cleared_for_delivery' => $t->is_cleared_for_delivery(),
			'order_date'           => \iThemes\Exchange\REST\format_rfc339( $t->get_date() ),
			'payment'              => $payment,
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
	 * @since 2.0.0
	 *
	 * @param \IT_Exchange_Transaction       $transaction
	 * @param \iThemes\Exchange\REST\Manager $manager
	 *
	 * @return array
	 */
	public function generate_links( \IT_Exchange_Transaction $transaction, Manager $manager ) {

		$t = $transaction;

		$links = array();

		$links['alternate'][] = array(
			'href'       => it_exchange_get_transaction_confirmation_url( $transaction->get_ID() ),
			'embeddable' => false,
			'mediaType'  => 'text/html'
		);

		$links['refunds'][] = array(
			'href'       => r\get_rest_url(
				$manager->get_first_route( 'iThemes\Exchange\REST\Route\v1\Transaction\Refunds\Refunds' ),
				array( 'transaction_id' => $transaction->get_ID() )
			),
			'embeddable' => true,
		);

		$links['edit'][] = array(
			'href'       => get_edit_post_link( $transaction->get_ID(), 'raw' ),
			'mediaType'  => 'text/html',
			'embeddable' => false,
		);

		$links['activity'][] = array(
			'href'       => r\get_rest_url(
				$manager->get_first_route( 'iThemes\Exchange\REST\Route\v1\Transaction\Activity\Activity' ),
				array( 'transaction_id' => $transaction->get_ID() )
			),
			'embeddable' => false,
		);

		if ( $t->get_customer() ) {
			$route               = $manager->get_first_route( 'iThemes\Exchange\REST\Route\v1\Customer\Customer' );
			$links['customer'][] = array(
				'href'       => r\get_rest_url( $route, array( 'customer_id' => $t->get_customer()->ID ) ),
				'embeddable' => true
			);
		}

		if ( $t->has_parent() ) {
			$route             = $manager->get_first_route( 'iThemes\Exchange\REST\Route\v1\Transaction\Transaction' );
			$links['parent'][] = array(
				'href'       => r\get_rest_url( $route, array( 'transaction_id' => $t->get_parent()->ID ) ),
				'embeddable' => true
			);
		}

		if ( $t->payment_token && $t->get_customer() ) {
			$route                    = $manager->get_first_route( 'iThemes\Exchange\REST\Route\v1\Customer\Token\Token' );
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
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_schema() {
		return array(
			'$schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'       => 'transaction',
			'type'        => 'object',
			'definitions' => array(
				'address' => array(
					'description' => __( 'A customer address.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'first-name'   => array(
							'description' => __( 'The first name of the address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'last-name'    => array(
							'description' => __( 'The last name of the address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'company-name' => array(
							'description' => __( 'The company name of the address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'address1'     => array(
							'description' => __( 'The address line 1 of the address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'address2'     => array(
							'description' => __( 'The address line 2 of the address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'city'         => array(
							'description' => __( 'The city of the address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'state'        => array(
							'description' => __( 'The state two-letter abbreviation of the address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'country'      => array(
							'description' => __( 'The country two-letter abbreviation of the address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'zip'          => array(
							'description' => __( 'The zip code of the address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'email'        => array(
							'description' => __( 'The email address of the address.', 'it-l10n-ithemes-exchange' ),
							'context'     => array( 'view', 'edit' ),
							'oneOf'       => array(
								array(
									'type'   => 'string',
									'format' => 'email',
								),
								array(
									'type' => 'string',
									'enum' => array( '' ),
								),
							)
						),
						'phone'        => array(
							'description' => __( 'The phone number of the address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
					)
				)
			),
			'properties'  => array(
				'id'                   => array(
					'description' => __( 'The unique id for this transaction.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'order_number'         => array(
					'description' => __( 'The order number.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
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
				'customer_name'        => array(
					'description' => __( "The customer's name.", 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'customer_avatar'      => array(
					'description' => __( "A URL pointing to the customer's avatar.", 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
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
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'method_id'            => array(
					'description' => __( 'The method id used for this transaction.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
				),
				'status'               => array(
					'description' => __( 'The transaction status.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
					'oneOf'       => array(
						array(
							'type'       => 'object',
							'properties' => array(
								'slug'  => array(
									'description' => __( 'The transaction status slug.', 'it-l10n-ithemes-exchange' ),
									'type'        => 'string',
									'context'     => array( 'edit' ),
									'required'    => true,
								),
								'label' => array(
									'description' => __( 'The transaction status label.', 'it-l10n-ithemes-exchange' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit', 'embed' )
								),
							),
						),
						array(
							'type'        => 'string',
							'description' => __( 'The transaction status slug.', 'it-l10n-ithemes-exchange' ),
						),
					),
				),
				'cleared_for_delivery' => array(
					'description' => __( 'The cleared for delivery status of the transaction.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'order_date'           => array(
					'description' => __( 'The date the transaction was placed, as GMT.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'payment'              => array(
					'description' => __( 'The method of payment for this transaction.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
					'properties'  => array(
						'label' => array(
							'description' => __( 'Human readable label of the payment method used.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
						'card'  => array( '$ref' => r\url_for_schema( 'card' ) ),
						'token' => array(
							'description' => __( 'The payment token used.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
						)
					),
				),
				'purchase_mode'        => array(
					'description' => __( 'The mode the transaction was created in.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'enum'        => array(
						\ITE_Const::P_MODE_SANDBOX,
						\ITE_Const::P_MODE_LIVE,
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
					'type'        => 'number',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'total'                => array(
					'description' => __( 'The transaction total.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'total_before_refunds' => array(
					'description' => __( 'The transaction total before refunds have been applied.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'number',
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
					'context'     => array( 'view', 'edit', 'embed' ),
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
					'$ref'        => '#/definitions/address'
				),
				'shipping_address'     => array(
					'description' => __( 'The shipping address for this transaction.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'$ref'        => '#/definitions/address',
				),
			)
		);
	}
}
