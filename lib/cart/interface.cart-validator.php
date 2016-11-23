<?php
/**
 * Cart Validator interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Cart_Validator
 */
interface ITE_Cart_Validator {

	/**
	 * Get the name of this validator.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public static function get_name();

	/**
	 * Perform validation on the cart.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart          $cart
	 * @param \ITE_Cart_Feedback $feedback
	 *
	 * @return bool
	 */
	public function validate( ITE_Cart $cart, ITE_Cart_Feedback $feedback = null );

	/**
	 * Coerce a cart to be valid.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart          $cart
	 * @param \ITE_Line_Item     $new_item The most recently added item.
	 * @param \ITE_Cart_Feedback $feedback
	 *
	 * @return bool True if coercion took place, false if not.
	 *
	 * @throws ITE_Cart_Coercion_Failed_Exception
	 */
	public function coerce( ITE_Cart $cart, \ITE_Line_Item $new_item = null, ITE_Cart_Feedback $feedback = null );
}
