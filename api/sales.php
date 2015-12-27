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

	$product = it_exchange_get_product( is_int( $product ) ? $product : $product->ID );

	if ( ! $product ) {
		return false;
	}

	if ( ! it_exchange_product_has_feature( $product->ID, 'sale-price' ) ) {
		return false;
	}

	$base = it_exchange_get_product_feature( $product->ID, 'base-price' );
	$sale = it_exchange_get_product_feature( $product->ID, 'sale-price' );

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

/**
 * Add sale information to the base price in the IT_Theme_API_Product class.
 *
 * @since 1.32.0
 *
 * @param string $price
 * @param int    $product_id
 *
 * @return string
 */
function it_exchange_add_sale_information_to_base_price_theme( $price, $product_id ) {

	if ( it_exchange_is_product_sale_active( $product_id ) ) {

		$sale_price = it_exchange_get_product_feature( $product_id, 'sale-price' );
		$sale_price = it_exchange_format_price( $sale_price );

		remove_filter( 'it_exchange_api_theme_product_base_price', 'it_exchange_add_sale_information_to_base_price_theme', 20 );
		$sale_price = apply_filters( 'it_exchange_api_theme_product_base_price', $sale_price, $product_id );
		add_filter( 'it_exchange_api_theme_product_base_price', 'it_exchange_add_sale_information_to_base_price_theme', 20, 2 );

		$price = "<del>$price</del>&nbsp;";
		$price .= "<ins>$sale_price</ins>";
	}

	return $price;
}

add_filter( 'it_exchange_api_theme_product_base_price', 'it_exchange_add_sale_information_to_base_price_theme', 20, 2 );