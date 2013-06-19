<?php
/**
 * This is basically a fancy setting masquerading as an addon.
 * @package IT_Exchange
 * @since 0.4.0
*/
// No settings. This is either enabled or disabled.

function it_exchange_register_multi_item_cart_pages() {

    // Cart
    $options = array(
        'slug'          => 'cart',
        'name'          => __( 'Shopping Cart', 'LION' ),
        'rewrite-rules' => array( 215, 'it_exchange_multi_item_cart_get_page_rewrites' ),
        'url'           => 'it_exchange_multi_item_cart_get_page_urls',
        'settings-name' => __( 'Customer Shopping Cart', 'LION' ),
        'type'          => 'exchange',
        'menu'          => true,
        'optional'      => true,
    );  
    it_exchange_register_page( 'cart', $options );

    // Checkout 
    $options = array(
        'slug'          => 'checkout',
        'name'          => __( 'Checkout', 'LION' ),
        'rewrite-rules' => array( 216, 'it_exchange_multi_item_cart_get_page_rewrites' ),
        'url'           => 'it_exchange_multi_item_cart_get_page_urls',
        'settings-name' => __( 'Customer Checkout', 'LION' ),
        'type'          => 'exchange',
        'menu'          => true,
        'optional'      => true,
    );  
    it_exchange_register_page( 'checkout', $options );
}
add_action( 'init', 'it_exchange_register_multi_item_cart_pages' );

/**
 * Returns rewrites for cart and checkout pages
 *
 * @since 0.4.4
 *
 * @param string page
 * @return array
*/
function it_exchange_multi_item_cart_get_page_rewrites( $page ) {
    $slug       = it_exchange_get_page_slug( $page );
    $store_slug = it_exchange_get_page_slug( 'store' );
	return array( $store_slug . '/' . $slug => 'index.php?' . $slug . '=1', );
}

/**
 * Returns URL for cart and checkout pages
 *
 * @since 0.4.4
 *
 * @param string page
 * @return array
*/
function it_exchange_multi_item_cart_get_page_urls( $page ) {
	// Get slugs
	$slug       = it_exchange_get_page_slug( $page );
	$store_slug = it_exchange_get_page_slug( 'store' );

	// Set cart and page urls
	if ( (boolean) get_option( 'permalink_structure' ) ) {
		$cart_url     = trailingslashit( get_home_url() . '/' . $store_slug . '/' . $slug );
		$checkout_url = trailingslashit( get_home_url() . '/' . $store_slug . '/' . $slug );
	} else {
		$cart_url     = add_query_arg( $slug, 1, get_home_url() );
		$checkout_url = add_query_arg( $slug, 1, get_home_url() );
	}

	if ( 'cart' == $page )
		return $cart_url;
	else if ( 'checkout' == $page )
		return $checkout_url;
}

/**
 * Enables multi item carts
 * @since 0.4.0
*/
add_filter( 'it_exchange_multi_item_cart_allowed', '__return_true' );
