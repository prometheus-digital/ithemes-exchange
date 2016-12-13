<?php
/**
 * Optional Product Feature.
 *
 * @since   2.0.0
 * @license GPlv2
 */

/**
 * Interface ITE_Optionally_Supported_Product_Feature
 */
interface ITE_Optionally_Supported_Product_Feature extends ITE_Optionally_Supported_Feature {

	/**
	 * Get details for a product.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Product $product
	 *
	 * @return array
	 */
	public function get_details_for_product( IT_Exchange_Product $product );
}