<?php
/**
 * Item Serializer.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

/**
 * Class Item_Serializer
 * @package iThemes\Exchange\REST\Route\Cart
 */
class Item_Serializer {

	/** @var \Closure */
	private $extend;

	/** @var \ITE_Line_Item_Type */
	private $type;

	/** @var array */
	private $schema;

	/**
	 * Item_Serializer constructor.
	 *
	 * @param \ITE_Line_Item_Type $type
	 */
	public function __construct( \ITE_Line_Item_Type $type ) {
		$this->type   = $type;
		$this->schema = $this->generate_schema();
	}

	/**
	 * Extend the Item Serializer without subclassing.
	 *
	 * @since 1.36.0
	 *
	 * @param \Closure $extend
	 *
	 * @return $this
	 */
	public function extend( \Closure $extend ) {
		$this->extend = $extend;

		return $this;
	}

	/**
	 * Get the schema for this item serializer.
	 *
	 * @since 1.36.0
	 *
	 * @return array
	 */
	public function get_schema() { return $this->schema; }

	/**
	 * Serialize a line item.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Line_Item $item
	 * @param \ITE_Cart      $cart
	 *
	 * @return array
	 */
	public function serialize( \ITE_Line_Item $item, \ITE_Cart $cart ) {

		$schema = $this->get_schema();

		$data = array(
			'id'          => $item->get_id(),
			'type'        => $item->get_type(),
			'name'        => $item->get_name(),
			'description' => $item->get_description(),
			'amount'      => $item->get_amount(),
			'quantity'    => array(
				'selected' => $item->get_quantity(),
				'editable' => false,
			),
			'total'       => $item->get_total(),
		);

		if ( $item instanceof \ITE_Quantity_Modifiable_Item && $item->is_quantity_modifiable() ) {
			$data['quantity']['max']      = ( $max = $item->get_max_quantity_available() ) && is_numeric( $max ) ? (int) $max : '';
			$data['quantity']['editable'] = \ITE_Line_Item_Types::get( $item->get_type() )->is_editable_in_rest();
		}

		foreach ( $data as $key => $_ ) {
			if ( ! isset( $schema['properties'][ $key ] ) ) {
				unset( $data[ $key ] );
			}
		}

		if ( $this->extend ) {
			$data = call_user_func( $this->extend, $data, $item, $schema, $cart );
		}

		return $data;
	}

	/**
	 * Generate the schema.
	 *
	 * @since 1.36.0
	 *
	 * @return array
	 */
	protected function generate_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => "cart_item_{$this->type->get_type()}",
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description' => __( 'The unique id for this item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'type'        => array(
					'description' => __( 'The type of this item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'name'        => array(
					'description' => __( 'The name of this line item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'description' => array(
					'description' => __( 'The description of this line item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'amount'      => array(
					'description' => __( 'The cost of this line item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'float',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'quantity'    => array(
					'description' => __( 'The quantity purchased of this line item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'selected' => array(
							'description' => __( 'Selected quantity for the line item.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'default'     => 1,
						),
						'max'      => array(
							'description' => __( 'Maximum purchase quantity for the line item.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'editable' => array(
							'description' => __( 'Whether the item quantity can be edited.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					)
				),
				'total'       => array(
					'description' => __( 'The total amount of this line item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'float',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		foreach ( $this->type->get_additional_schema_props() as $prop => $schema_prop ) {
			$schema['properties'][ $prop ] = $schema_prop;
		}

		return $schema;
	}

}
