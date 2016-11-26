<?php
/**
 * Cart Serializer.
 *
 * @since 2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

/**
 * Class Serializer
 *
 * @package iThemes\Exchange\REST\Route\Cart
 */
class Serializer {

	/** @var array */
	private $schema;

	/**
	 * Serialize a cart.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 *
	 * @return array
	 */
	public function serialize( \ITE_Cart $cart ) {

		$data = array(
			'id'               => $cart->get_id(),
			'customer'         => $cart->get_customer() ? $cart->get_customer()->id : 0,
			'is_main'          => $cart->is_main(),
			'shipping_address' => null,
			'billing_address'  => null,
			'subtotal'         => it_exchange_get_cart_subtotal( false, array( 'cart' => $cart ) ),
			'total'            => it_exchange_get_cart_total( false, array( 'cart' => $cart ) ),
		);

		if ( $cart->get_billing_address() ) {
			$data['billing_address'] = $cart->get_billing_address()->to_array();
		}

		if ( $cart->get_shipping_address() ) {
			$data['shipping_address'] = $cart->get_shipping_address()->to_array();
		}

		$items = array();

		foreach ( \ITE_Line_Item_Types::shows_in_rest() as $item_type ) {
			foreach ( $cart->get_items( $item_type->get_type() ) as $item ) {
				$items[] = $item_type->get_rest_serializer()->serialize( $item, $cart );
			}
		}

		$data['items'] = $items;

		$totals_info  = array();
		$summary_only = $cart->get_items( '', true )->summary_only();

		if ( $summary_only->count() ) {

			$totals = $summary_only->segment();

			foreach ( $totals as $total_by_type ) {
				$segmented = $total_by_type->segment( function ( \ITE_Line_Item $item ) {
					return get_class( $item ) . $item->get_name();
				} );

				foreach ( $segmented as $segment ) {
					$type        = $segment->first()->get_type();
					$name        = $segment->first()->get_name();
					$total       = $segment->total();
					$description = $segment->filter( function ( \ITE_Line_Item $item ) {
						return trim( $item->get_description() !== '' );
					} )->first();

					$totals_info[] = array(
						'slug'        => $type,
						'label'       => $name,
						'total'       => $total,
						'description' => $description ? $description->get_description() : ''
					);
				}
			}
		}

		$data['total_lines'] = $totals_info;

		return $data;
	}

	/**
	 * Get the cart schema.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_schema() {

		if ( $this->schema ) {
			return $this->schema;
		}

		$item_references = array();

		foreach ( \ITE_Line_Item_Types::shows_in_rest() as $item_type ) {

			$item_schema = $item_type->get_rest_serializer()->get_schema();
			$title       = $item_schema['title'];

			$item_references[]['$ref'] = \iThemes\Exchange\REST\url_for_schema( $title );
		}

		$this->schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cart',
			'type'       => 'object',
			'properties' => array(
				'id'               => array(
					'description' => __( 'The unique id for this cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'customer'         => array(
					'description' => __( 'The customer id for this cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'is_main'          => array(
					'description' => __( 'Is this the main cart for the customer.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
					'default'     => true,
				),
				'billing_address'  => array(
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
				'shipping_address' => array(
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
				'items'            => array(
					'oneOf' => $item_references,
				),
				'subtotal'         => array(
					'description' => __( 'The subtotal of the cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'total'            => array(
					'description' => __( 'The total of the cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'total_lines'      => array(
					'description' => __( 'Line item totals of the cart.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
					'items'       => array(
						'title'      => __( 'Line item total lines.', 'it-l10n-ithemes-exchange' ),
						'type'       => 'object',
						'properties' => array(
							'slug'        => array(
								'description' => __( 'The slug of the line item type.', 'it-l10n-ithemes-exchange' ),
								'type'        => 'string',
								'readonly'    => true,
								'context'     => array( 'view', 'edit' )
							),
							'label'       => array(
								'description' => __( 'The label of the line item type.', 'it-l10n-ithemes-exchange' ),
								'type'        => 'string',
								'readonly'    => true,
								'context'     => array( 'view', 'edit' )
							),
							'description' => array(
								'description' => __( 'The description of the line item type.', 'it-l10n-ithemes-exchange' ),
								'type'        => 'string',
								'readonly'    => true,
								'context'     => array( 'view', 'edit' )
							),
							'total'       => array(
								'description' => __( 'The total of the line item type.', 'it-l10n-ithemes-exchange' ),
								'type'        => 'number',
								'readonly'    => true,
								'context'     => array( 'view', 'edit' )
							),
						),
					),
				),
			),
		);

		return $this->schema;
	}
}