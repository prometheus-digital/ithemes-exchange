<?php
/**
 * Cart Aware Interface
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Interface ITE_Cart_Aware
 */
interface ITE_Cart_Aware {

	/**
	 * Set the cart object.
	 * 
	 * @since 1.36.0
	 * 
	 * @param \ITE_Cart $cart
	 */
	public function set_cart( ITE_Cart $cart );
}