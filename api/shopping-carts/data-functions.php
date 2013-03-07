<?php
/**
 * This file contains functions intended for theme developers to interact with the active shopping cart plugin
 *
 * The active shopping cart plugin should add the needed hooks below within its codebase.
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * Returns an array of all data in the cart
 *
 * @since 0.3.7
 * @param $args array optional array of args. All cart add-ons will not use this.
 * @return array
*/
function it_cart_buddy_get_cart_data( $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_cart_data', array(), $args );
}

/**
 * Returns an array of all products in the cart
 *
 * @since 0.3.7
 * @param $args array optional array of args. All cart add-ons will not use this.
 * @return array
*/
function it_cart_buddy_get_cart_products( $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_cart_products', array(), $args );
}

/**
 * Returns a specific product from the cart.
 *
 * The returned data may not reflect an IT_Cart_Buddy_Product object
 * depending on how the active shopping cart add-on stores products in its cart.
 *
 * @since 0.3.7
 * @param mixed $identifier identifier for the active shopping cart add-on's product data
 * @param $args array optional array of args. All cart add-ons will not use this.
 * @return mixed
*/
function it_cart_buddy_get_cart_product( $identifier, $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_cart_product', false, $identifier, $args );
}

/**
 * Returns a specific attribute for a specific cart item
 *
 * @since 0.3.7
 * @param mixed $identifier identifier for the active shopping cart add-on's product data
 * @param string $attribute the attribute being requested
 * @param $args array optional array of args. All cart add-ons will not use this.
 * @return mixed
*/
function it_cart_buddy_get_cart_product_attribute( $cart_product, $attribute, $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_cart_product_attribute', false, $cart_product, $attribute, $args );
}

/**
 * Returns an array of vars or a single var used to build a checkout form. This includes form and field properties.
 *
 * To return a specific var add it to the args array with key as the value: $arg=array( 'key' => 'form_action' )
 *
 * @since 0.3.7
 * @param $args array optional array of args. All cart add-ons will not use this.
 * @return mixed array of all form and field vars or a specifc var passed back as a string
*/
function it_cart_buddy_get_cart_form_vars( $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_cart_form_vars', false, $args );
}

/**
 * Returns the title for a product in the cart. 
 *
 * The title in the cart may not always be the same as the title in the DB depending on variants / etc
 *
 * @since 0.3.7
 * @param array $product cart product array
 * @param $args array optional array of args. All cart add-ons will not use this.
 * return mixed product title
*/
function it_cart_buddy_get_cart_product_title( $product, $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_cart_product_title', false, $product, $args );
}

/**
 * Returns the quantity for a product in the cart. 
 *
 * @since 0.3.7
 * @param array $product cart product array
 * @param $args array optional array of args. All cart add-ons will not use this.
 * return mixed product quantity
*/
function it_cart_buddy_get_cart_product_quantity( $product, $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_cart_product_quantity', false, $product, $args );
}

/**
 * Returns the base price for a product in the cart. 
 *
 * This returns the DB price of the product but allows other add-ons to modify it based on
 * cart product itemized or additional data
 * eg: variants, coupons, tax, shipping, etc
 *
 * @since 0.3.7
 * @param array $product cart product array
 * @param $args array optional array of args. All cart add-ons will not use this.
 * return mixed product base_price 
*/
function it_cart_buddy_get_cart_product_base_price( $product, $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_cart_product_base_price', false, $product, $args );
}

/**
 * Returns the subtotal for a product in the cart. 
 *
 * Cart price * quantity
 *
 * @since 0.3.7
 * @param array $product cart product array
 * @param $args array optional array of args. All cart add-ons will not use this.
 * return mixed product subtotal
*/
function it_cart_buddy_get_cart_product_subtotal( $product, $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_cart_product_subtotal', false, $product, $args );
}

/**
 * Returns the subtotal for the cart.
 *
 * @since 0.3.7
 * @param $args array optional array of args. All cart add-ons will not use this.
 * @return mixed subtotal
*/
function it_cart_buddy_get_cart_subtotal( $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_cart_subtotal', false, $args );
}

/**
 * Returns the total for the cart.
 *
 * @since 0.3.7
 * @param $args array optional array of args. All cart add-ons will not use this.
 * @return mixed total
*/
function it_cart_buddy_get_cart_total( $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_cart_total', false, $args );
}

/**
 * This returns the active shopping cart plugin
 *
 * If multiple add-ons are enabled, it will return the first enabled add-on it finds.
 *
 * @since 0.3.7
 * @return mixed add_on array or WP_Error
*/
function it_cart_buddy_get_active_shopping_cart( $return='array' ) { 
    $enabled_shopping_cart_add_ons = it_cart_buddy_get_enabled_addons( array( 'category' => 'shopping-carts' ) );
    if ( empty( $enabled_shopping_cart_add_ons ) ) 
        return new WP_Error( 'no-add-on-carts-enabled', __( 'Oops! You have no shopping cart add-ons enabled.', 'LION' ) );

    $add_on = reset( $enabled_shopping_cart_add_ons );

    if ( 'slug' == $return )
        return $add_on['slug'];

    return $add_on;
}
