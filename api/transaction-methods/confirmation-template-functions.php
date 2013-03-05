<?php
/**
 * Theme and add-on developers should use these functions to output confirmation details
 * Transaction method add-ons should use the referenced filters to provide HTML
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * Returns the HTML for the Confirmation Page
 *
 * @since 0.3.7
 * @return html
*/
function it_cart_buddy_get_transaction_confirmation_page_html( $id=false, $shortcode_atts=array(), $shortcode_content='' ) {

	// Base hook
	$hook = 'it_cart_buddy_get_transaction_confirmation_page_html';

	// Get var for transaction method
	$var = it_cart_buddy_get_action_var( 'transaction_id' );

	// Set transaction ID from REQUEST if it exists
	$transaction_id = empty( $_REQUEST[$var] ) ? false : $_REQUEST[$var];
	
	// If function was passed a transaction ID, that overrides REQUEST value
	$transaction_id = empty( $id ) ? $transaction_id : $id;

	// Grab transaction method
	$transaction_method = it_cart_buddy_get_transaction_method( $transaction_id );

	// Append transaction method to base hook
	$hook = empty( $transaction_method ) ? $hook : $hook . '-' . $transaction_method;

	// Return filtered content
	return apply_filters( $hook, '', $transaction_id, $shortcode_atts, $shortcode_content );
}
