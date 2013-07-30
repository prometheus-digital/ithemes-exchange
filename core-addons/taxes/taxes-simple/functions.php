<?php
/**
 * This file contains functions for interacting with the addon
 * @since 1.0.0
 * @package IT_Exchange
*/

/**
 * Get taxes for cart
 *
 * @since 1.0.0
 *
*/
function it_exchange_addon_get_simple_taxes_for_cart( $format_price=true ) {

	// Grab the tax rate
	$options  = it_exchange_get_option( 'addon_taxes_simple' );
	$tax_rate = empty( $options['default-tax-rate'] ) ? 1 : (float) $options['default-tax-rate'];
	$process_after_discounts = ! empty( $options['calculate-after-discounts'] );

	// Grab the cart subtotal or the cart total depending on the process after discounts option
	$cart_total = it_exchange_get_cart_subtotal( false );

	if ( $process_after_discounts )
		$cart_total -= it_exchange_get_total_coupons_discount( 'cart', array( 'format_price' => false ) );

	// Calculate taxes
	$cart_taxes = $cart_total * ( $tax_rate / 100 );

	$taxes = apply_filters( 'it_exchange_addon_get_simple_taxes_for_cart', $cart_taxes );
	if ( $format_price )
		$taxes = it_exchange_format_price( $taxes );
	return $taxes;
}
