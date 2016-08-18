<?php
/**
 * Tax Provider Interface.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * class ITE_Tax_Provider
 */
abstract class ITE_Tax_Provider {

	/**
	 * Get the item class.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public abstract function get_item_class();

	/**
	 * Get the tax rate for a given product.
	 *
	 * @since 1.36.0
	 *
	 * @param \IT_Exchange_Product $product
	 *
	 * @return float
	 */
	public abstract function get_tax_code_for_product( IT_Exchange_Product $product );

	/**
	 * Check if a product is tax exempt.
	 *
	 * @since 1.36.0
	 *
	 * @param \IT_Exchange_Product $product
	 *
	 * @return bool
	 */
	public abstract function is_product_tax_exempt( IT_Exchange_Product $product );

	/**
	 * Add taxes to the given item.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Taxable_Line_Item $item
	 * @param \ITE_Cart              $cart
	 */
	public abstract function add_taxes_to( ITE_Taxable_Line_Item $item, ITE_Cart $cart );

	/**
	 * Finalize the taxes for a given cart.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Cart $cart
	 */
	public function finalize_taxes( ITE_Cart $cart ) { }

	/**
	 * Return the zone this tax type is restricted to, if any.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Zone
	 */
	public function is_restricted_to_location() { return null; }
}
