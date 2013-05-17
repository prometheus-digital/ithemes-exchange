<?php
/**
 * This is basically a fancy setting masquerading as an addon.
 * @package IT_Exchange
 * @since 0.4.0
*/
// No settings. This is either enabled or disabled.

function it_exchange_register_multi_item_cart_checkout_rewrites( $existing ) {
	$pages    = it_exchange_get_option( 'settings_pages' );
	$rewrites = array();
	$url      = '';

	// Cart Defaults
	if ( empty( $pages['cart-slug'] ) ) {
		$pages['cart-slug'] = 'cart';
		$pages['cart-name'] = __( 'Shopping Cart', 'LION' );
		it_exchange_save_option( 'settings_pages', $pages );
		it_exchange_clear_option_cache( 'settings_pages' );
	}

	// Checkout Defaults
	if ( empty( $pages['checkout-slug'] ) ) {
		$pages['checkout-slug'] = 'checkout';
		$pages['checkout-name'] = __( 'Checkout', 'LION' );
		it_exchange_save_option( 'settings_pages', $pages );
		it_exchange_clear_option_cache( 'settings_pages' );
	}

	// Set cart rewrite rules
	$cart_rewrites = array( $pages['store-slug'] . '/' . $pages['cart-slug'] => 'index.php?' . $pages['cart-slug'] . '=1', );

	// Set checkout rewrite rules
	$checkout_rewrites = array( $pages['store-slug'] . '/' . $pages['checkout-slug'] => 'index.php?' . $pages['checkout-slug'] . '=1', );

	// Set cart and page urls
	if ( (boolean) get_option( 'permalink_structure' ) ) {
		$cart_url     = trailingslashit( get_home_url() . '/' . $pages['store-slug'] . '/' . $pages['cart-slug'] );
		$checkout_url = trailingslashit( get_home_url() . '/' . $pages['store-slug'] . '/' . $pages['checkout-slug'] );
	} else {
		$cart_url     = add_query_arg( $pages['cart-slug'], 1, get_home_url() );
		$checkout_url = add_query_arg( $pages['checkout-slug'], 1, get_home_url() );
	}

	$custom = array(
		'cart' => array(
			'slug'     => 'cart',
			'name'     => __( 'Shopping Cart', 'LION' ),
			'rewrites' => $cart_rewrites,
			'include_in_settings_pages' => true,
			'url'      => $cart_url,
		),
		'checkout' => array(
			'slug'     => 'checkout',
			'name'     => __( 'Checkout', 'LION' ),
			'rewrites' => $checkout_rewrites,
			'include_in_settings_pages' => true,
			'url'      => $checkout_url,
		),
	);
	$existing = array_merge( $custom, $existing );
	return $existing;
}
add_filter( 'it_exchange_add_ghost_pages', 'it_exchange_register_multi_item_cart_checkout_rewrites' );
