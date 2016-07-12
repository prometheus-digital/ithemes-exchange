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
}