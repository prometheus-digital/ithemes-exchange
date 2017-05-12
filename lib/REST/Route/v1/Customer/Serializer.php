<?php
/**
 * Customer Serializer.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Customer;

/**
 * Class Serializer
 *
 * @package iThemes\Exchange\REST\Route\v1\Customer
 */
class Serializer {

	/**
	 * Serialize a customer.
	 *
	 * @since 2.0.0
	 *
	 * @param \IT_Exchange_Customer $customer
	 * @param string                $context
	 *
	 * @return array
	 */
	public function serialize( \IT_Exchange_Customer $customer, $context ) {

		$data = array(
			'id'               => $customer->ID,
			'email'            => $customer->get_email(),
			'display_name'     => $customer->get_display_name(),
			'first_name'       => $customer->get_first_name(),
			'last_name'        => $customer->get_last_name(),
			'avatar_url'       => get_avatar_url( $customer->get_email() ),
			'billing_address'  => ( $a = $customer->get_billing_address( true ) ) ? $a->get_pk() : 0,
			'shipping_address' => ( $a = $customer->get_shipping_address( true ) ) ? $a->get_pk() : 0,
			'created_at'       => date( 'c', strtotime( $customer->wp_user->user_registered ) )
		);

		if ( $context === 'stats' ) {
			$data['total_spent']       = $customer->get_total_spent();
			$data['transaction_count'] = $customer->get_transactions_count();
		}

		return $data;
	}

	/**
	 * Get the schema.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_schema() {
		return array(
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
					'description' => __( "The customer's primary billing address.", 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' ),
					'oneOf'       => array(
						array(
							'type'        => 'integer',
							'description' => __( 'The billing address ID.', 'it-l10n-ithemes-exchange' ),
						),
						array(
							'$ref'        => \iThemes\Exchange\REST\url_for_schema( 'customer-address' ),
							'description' => __( 'An address object.', 'it-l10n-ithemes-exchange' ),
						),
					)
				),
				'shipping_address'  => array(
					'description' => __( "The customer's primary shipping address.", 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' ),
					'oneOf'       => array(
						array(
							'type'        => 'integer',
							'description' => __( 'The shipping address ID.', 'it-l10n-ithemes-exchange' ),
						),
						array(
							'$ref'        => \iThemes\Exchange\REST\url_for_schema( 'customer-address' ),
							'description' => __( 'An address object.', 'it-l10n-ithemes-exchange' ),
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
	}
}