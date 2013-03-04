<?php
/**
 * This file contains functions useful for building a checkout page
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * This will print the chekcout page HTML
 *
 * Heavy lifing it done by the active shopping cart add-on
 * This is a highlevel function that may also be called by a shortcode.
 *
 * @since 0.3.7
 * @param array $shortcode_atts atts passed from WP Shortcode API if function is being invoked by it.
 * @param string $shortcode_content content passed from WP Shortcode API if function is being invoked by it.
 * @return string html for the shopping cart
*/
function it_cart_buddy_get_shopping_cart_checkout_page_html( $shortcode_atts=array(), $shortcode_content='' ) {
	return apply_filters( 'it_cart_buddy_get_cart_checkout_page_html', '', $shortcode_atts, $shortcode_content );
}

/**
 * Returns the form open tag for the checkout page
 *
 * @since 0.3.7
 * @param array $args optional. not used by all add-ons
 * @return string HTML
*/
function it_cart_buddy_get_cart_checkout_form_open_html( $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_cart_checkout_form_open_html', '', $args );
}

/**
 * Returns the form close tag for the checkout page
 *
 * @since 0.3.7
 * @param array $args optional. not used by all add-ons
 * @return string HTML
*/
function it_cart_buddy_get_cart_checkout_form_close_html( $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_cart_checkout_form_close_html', '', $args );
}

/**
 * Returns the HTML for the order button on the checkout page
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_cart_buddy_get_cart_checkout_order_button( $args=array() ) {
	return apply_filters( 'it_cart_buddy_get_cart_checkout_order_button', '', $args );
}
