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

	/**
	 * @var \Closure
	 */
	private $extend;

	/**
	 * Item_Serializer constructor.
	 *
	 * @param \Closure|null $extend
	 */
	public function __construct( \Closure $extend = null ) {
		$this->extend = $extend;
	}

	/**
	 * Serialize a line item.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Line_Item $item
	 * @param array          $schema
	 * @param \ITE_Cart      $cart
	 *
	 * @return array
	 */
	public function serialize( \ITE_Line_Item $item, array $schema, \ITE_Cart $cart ) {
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
}
