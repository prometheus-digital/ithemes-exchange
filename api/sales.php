<?php
/**
 * This file contains functions making up the Sales API
 *
 * @since   1.32.0
 * @package IT_Exchange
 */

/**
 * Check if a sale price is active.
 *
 * @since 1.32.0
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

	if ( ! $product->has_feature( 'sale-price' ) ) {
		return false;
	}

	$base = $product->get_feature( 'base-price' );
	$sale = $product->get_feature( 'sale-price' );

	if ( it_exchange_convert_to_database_number( $base ) == it_exchange_convert_to_database_number( $sale ) ) {
		return false;
	}

	/**
	 * Filters whether or not a product sale is active.
	 *
	 * If add-ons want to restrict the time period of a sale, they should use this filter.
	 *
	 * @since 1.32.0
	 *
	 * @param bool                $active
	 * @param IT_Exchange_Product $product
	 */

	return apply_filters( 'it_exchange_is_product_sale_active', true, $product );
}