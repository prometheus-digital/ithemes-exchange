<?php
/**
 * Cart Serializer.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Cart;

/**
 * Class Serializer
 *
 * @package iThemes\Exchange\REST\Route\v1\Cart
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
			'id'                => $cart->get_id(),
			'customer'          => 0,
			'is_main'           => $cart->is_main(),
			'shipping_address'  => null,
			'billing_address'   => null,
			'requires_shipping' => false,
			'subtotal'          => $cart->get_subtotal(),
			'total'             => $cart->get_total(),
			'expires_at'        => $cart->expires_at() ? \iThemes\Exchange\REST\format_rfc339( $cart->expires_at() ) : '',
			'meta'              => array(),
		);

		if ( $cart->get_customer() instanceof \IT_Exchange_Guest_Customer ) {
			$data['customer'] = $cart->get_customer()->get_email();
		} elseif ( $cart->get_customer() ) {
			$data['customer'] = $cart->get_customer()->get_ID();
		}

		if ( $cart->get_billing_address() ) {
			$data['billing_address'] = $cart->get_billing_address()->to_array( true );
		}

		if ( $cart->get_shipping_address() ) {
			$data['shipping_address'] = $cart->get_shipping_address()->to_array( true );
		}

		if ( $cart->requires_shipping() ) {
			$data['requires_shipping'] = true;
		}

		$items = array();

		foreach ( \ITE_Line_Item_Types::shows_in_rest() as $item_type ) {
			foreach ( $cart->get_items( $item_type->get_type() )->non_summary_only() as $item ) {
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
						'id'          => $segment->first()->get_id(),
						'type'        => $type,
						'label'       => $name,
						'total'       => $total,
						'description' => $description ? $description->get_description() : ''
					);
				}
			}
		}

		$data['total_lines'] = $totals_info;

		foreach ( $cart->get_all_meta() as $key => $value ) {
			if ( ( $config = \ITE_Cart_Meta_Registry::get( $key ) ) && $config->show_in_rest() ) {
				$data['meta'][ $key ] = $value;
			}
		}

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

		$meta_properties = array();

		foreach ( \ITE_Cart_Meta_Registry::shows_in_rest() as $meta ) {
			$meta_properties[ $meta->get_key() ] = $meta->get_schema();
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
					'default'     => true,
				),
				'billing_address'  => array(
					'description' => __( 'The billing address for this cart.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' ),
					'oneOf'       => array(
						array(
							'type'        => 'integer',
							'description' => __( 'Billing address id.', 'it-l10n-ithemes-exchange' ),
						),
						array(
							'$ref' => \iThemes\Exchange\REST\url_for_schema( 'address' )
						)
					),
				),
				'shipping_address' => array(
					'description' => __( 'The shipping address for this cart.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' ),
					'oneOf'       => array(
						array(
							'type'        => 'integer',
							'description' => __( 'Shipping address id.', 'it-l10n-ithemes-exchange' ),
						),
						array(
							'$ref' => \iThemes\Exchange\REST\url_for_schema( 'address' )
						)
					),
				),
				'items'            => array(
					'context'  => array( 'view', 'edit' ),
					'oneOf'    => $item_references,
					'readonly' => true,
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
				'expires_at'       => array(
					'description' => __( 'The time at which the cart expires. Maximum of two days in the future.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
					'oneOf'       => array(
						array(
							'type'   => 'string',
							'format' => 'date-time',
						),
						array(
							'type' => 'string',
							'enum' => array( '' ),
						),
					)
				),
			),
		);

		if ( $meta_properties ) {
			$this->schema['properties']['meta'] = array(
				'description' => __( 'Cart meta fields.', 'it-l10n-ithemes-exchange' ),
				'context'     => array( 'view', 'edit' ),
				'type'        => 'object',
				'properties'  => $meta_properties,
			);
		}

		return $this->schema;
	}
}