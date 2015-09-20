<?php
/**
 * This file contains functions making up the Sales API
 *
 * @since   1.24.0
 * @package IT_Exchange
 */

/**
 * Check if a sale price is active.
 *
 * @since 1.24.0
 *
 * @param int|IT_Exchange_Product $product
 *
 * @return bool
 */
function it_exchange_is_product_sale_active( $product ) {

	$product = it_exchange_get_product( $product );

	if ( ! $product ) {
		return false;
	}

	if ( ! it_exchange_product_has_feature( $product->ID, 'sale-price' ) ) {
		return false;
	}

	/**
	 * Filters whether or not a product sale is active.
	 *
	 * If add-ons want to restrict the time period of a sale, they should use this filter.
	 *
	 * @since 1.24.0
	 *
	 * @param bool                $active
	 * @param IT_Exchange_Product $product
	 */

	return apply_filters( 'it_exchange_is_product_sale_active', true, $product );
}