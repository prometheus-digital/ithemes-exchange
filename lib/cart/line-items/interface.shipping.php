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
	 * Null may be returned if the shipping provider no longer exists.
	 *
	 * @since 2.0.0
	 *
	 * @return IT_Exchange_Shipping_Provider|null
	 */
	public function get_provider();

	/**
	 * Get the shipping provider slug.
	 *
	 * This should be used when a unique identifier is required as ::get_provider() may return null.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_provider_slug();

	/**
	 * Get the shipping method.
	 *
	 * Null may be returned if the shipping method no longer exists.
	 *
	 * @since 2.0.0
	 *
	 * @return IT_Exchange_Shipping_Method|null
	 */
	public function get_method();

	/**
	 * Get the shipping method slug.
	 *
	 * This should be used when a unique identifier is required as ::get_method() may return null.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_method_slug();

	/**
	 * Is the shipping applied to the cart or to a product.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_cart_wide();
}
