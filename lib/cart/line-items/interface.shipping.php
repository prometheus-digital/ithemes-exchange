<?php
/**
 * Shipping Line Item interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Shipping_Line_Item
 */
interface ITE_Shipping_Line_Item extends ITE_Aggregatable_Line_Item {

	/**
	 * Get the shipping provider.
	 *
	 * @since 2.0.0
	 *
	 * @return IT_Exchange_Shipping_Provider
	 */
	public function get_provider();

	/**
	 * Get the shipping method.
	 *
	 * @since 2.0.0
	 *
	 * @return IT_Exchange_Shipping_Method
	 */
	public function get_method();

	/**
	 * Is the shipping applied to the cart or to a product.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_cart_wide();
}
