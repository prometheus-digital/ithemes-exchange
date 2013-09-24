<?php
/**
 * Add Shipping to the content-cart totals and content-checkout loop
 *
 * @since 1.0.0
 *
 * @param array $elements list of existing elements
 * @return array
*/
function it_exchange_addon_add_shipping_to_template_totals_loops( $elements ) {
    $shipping_options           = it_exchange_get_option( 'addon_shipping_settings' );

    // Locate the discounts key in elements array (if it exists)
    $index = array_search( 'totals-savings', $elements );
    if ( false === $index )
        $index = -1;

    array_splice( $elements, $index, 0, 'totals-shipping' );
    return $elements;
}
add_filter( 'it_exchange_get_content_cart_totals_elements', 'it_exchange_addon_add_shipping_to_template_totals_loops' );
add_filter( 'it_exchange_get_content_checkout_totals_elements', 'it_exchange_addon_add_shipping_to_template_totals_loops' );

/**
 * Add Shipping to the super-widget-checkout totals loop
 *
 * @since 1.0.0
 *
 * @param array $loops list of existing elements
 * @return array
*/
function it_exchange_addon_add_shipping_to_sw_template_totals_loops( $loops ) {
    $shipping_options      = it_exchange_get_option( 'addon_shipping_settings' );

    // Shipping Address 
    array_splice( $loops, -1, 0, 'shipping-address' );

    // Locate the discounts key in elements array (if it exists)
    $index = array_search( 'discounts', $loops );
    if ( false === $index )
        $index = -1;

    // Shipping Costs
    array_splice( $loops, $index, 0, 'shipping-cost' );
    return $loops;
}
add_filter( 'it_exchange_get_super-widget-checkout_after-cart-items_loops', 'it_exchange_addon_add_shipping_to_sw_template_totals_loops' );

/**
 * Adds our templates directory to the list of directories
 * searched by Exchange
 *
 * @since 1.0.0
 *
 * @param array $template_path existing array of paths Exchange will look in for templates
 * @param array $template_names existing array of file names Exchange is looking for in $template_paths directories
 * @return array
*/
function it_exchange_addon_shipping_register_templates( $template_paths, $template_names ) {
    // Bail if not looking for one of our templates. No need to make exchange search this directory
	// If the file its looking for won't be there.
    $add_path = false;
    $templates = array(
        'content-cart/elements/totals-shipping.php',
        'content-checkout/elements/purchase-requirements/shipping-address.php',
        'content-checkout/elements/purchase-requirements/shipping/edit-address.php',
        'content-checkout/elements/totals-shipping.php',
        'super-widget-checkout/loops/shipping-address.php',
        'super-widget-checkout/loops/shipping-cost.php',
        'super-widget-shipping.php',
    );
    foreach( $templates as $template ) {
        if ( in_array( $template, (array) $template_names ) )
            $add_path = true;
    }
    if ( ! $add_path )
        return $template_paths;

    $template_paths[] = dirname( __FILE__ ) . '/templates';
    return $template_paths;
}
add_filter( 'it_exchange_possible_template_paths', 'it_exchange_addon_shipping_register_templates', 10, 2 );
