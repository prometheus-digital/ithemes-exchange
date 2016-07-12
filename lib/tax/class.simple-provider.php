<?php
/**
 * Tax Provider.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Simple_Taxes_Provider
 */
class ITE_Simple_Taxes_Provider extends ITE_Tax_Provider {

	/**
	 * @inheritDoc
	 */
	public function get_tax_code_for_product( IT_Exchange_Product $product ) {
		return 0; // Simple Taxes does't have any codes!
	}

	/**
	 * @inheritDoc
	 */
	public function is_product_tax_exempt( IT_Exchange_Product $product ) {
		return false;
	}
}