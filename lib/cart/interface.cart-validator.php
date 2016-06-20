<?php
/**
 * Cart Validator interface.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Interface ITE_Cart_Validator
 */
interface ITE_Cart_Validator {

	/**
	 * Get the name of this validator.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public static function get_name();

	/**
	 * Perform validation on the cart.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Cart $cart
	 *
	 * @return bool
	 */
	public function validate( ITE_Cart $cart );

	/**
	 * Coerce a cart to be valid.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Cart      $cart
	 * @param \ITE_Line_Item $new_item The most recently added item.
	 *
	 * @return bool Returns false if the cart could not be coerced.
	 */
	public function coerce( ITE_Cart $cart, \ITE_Line_Item $new_item = null );
}