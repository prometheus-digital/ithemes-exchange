<?php
/**
 * Line Item Validator interface.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Interface ITE_Line_Item_Validator
 */
interface ITE_Line_Item_Validator {

	/**
	 * Get the name of this validator.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public static function get_name();

	/**
	 * Does this validator accept items of the given type.
	 *
	 * @since 1.36
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public function accepts( $type );

	/**
	 * Perform validation on the cart.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Line_Item $item
	 * @param \ITE_Cart      $cart
	 *
	 * @return bool
	 */
	public function validate( ITE_Line_Item $item, ITE_Cart $cart );

	/**
	 * Coerce a cart to be valid.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Line_Item $item
	 * @param \ITE_Cart      $cart
	 *
	 * @return bool Returns false if the cart could not be coerced.
	 */
	public function coerce( ITE_Line_Item $item, ITE_Cart $cart );
}