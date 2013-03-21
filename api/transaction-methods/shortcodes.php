<?php
/**
 * Shortcodes for Transaction Methods go here
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

add_shortcode( 'cart_buddy_purchase_confirmation', 'it_cart_buddy_shortcode_get_transaction_confirmation_page_html' );

/**
 * Passes the shortcode call on to the more general function
 *
 * @since 0.3.7
 * @return HTML
*/
function it_cart_buddy_shortcode_get_transaction_confirmation_page_html( $shortcode_atts, $shortcode_content='' ) {
	return it_cart_buddy_get_transaction_confirmation_page_html();
}
