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
 * Returns an array of all products in the cart
 *
 * @since 0.3.7
 * @param $args array optional array of args. All cart add-ons will not use this.
 * @return array
*/
function it_cart_buddy_get_cart_products( $args=array() ) {
	$active_cart = it_cart_buddy_get_active_shopping_cart( 'slug' );
	return apply_filters( 'it_cart_buddy_get_cart_products-' . $active_cart, array(), $args );
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
	$active_cart = it_cart_buddy_get_active_shopping_cart( 'slug' );
	return apply_filters( 'it_cart_buddy_get_cart_product-' . $active_cart, false, $identifier, $args );
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
	$active_cart = it_cart_buddy_get_active_shopping_cart( 'slug' );
	return apply_filters( 'it_cart_buddy_get_cart_product_attribute-' . $active_cart, false, $cart_product, $attribute, $args );
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
	$active_cart = it_cart_buddy_get_active_shopping_cart( 'slug' );
	return apply_filters( 'it_cart_buddy_get_cart_form_vars-' . $active_cart, false, $args );
}

/**
 * Returns the total for the cart.
 *
 * @since 0.3.7
 * @param $args array optional array of args. All cart add-ons will not use this.
 * @return mixed total
*/
function it_cart_buddy_get_cart_total( $args=array() ) {
	$active_cart = it_cart_buddy_get_active_shopping_cart( 'slug' );
	return apply_filters( 'it_cart_buddy_get_cart_total-' . $active_cart, false, $args );
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
