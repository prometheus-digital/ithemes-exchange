<?php
/**
 * These functions are intended to be used on the confirmation page
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * Prints the HMTL for the confirmation page
 *
 * @since 0.3.7
 * @param $discarded values passed through by WP Filter API. Discarded here.
 * @return string HTML
*/
function it_cart_buddy_manual_transactions_confirmation_page_html( $discarded, $transaction_id, $shortcode_atts=array(), $shortcode_content='' ) {
	$html  = '<h3>Details</h3>';
	$html .= '<div>';
	$html .= it_cart_buddy_manual_transactions_get_instructions( $transaction_id );
	$html .= '<p>Transaction Order: ' . $transaction_id . '</p>';
	$html .= '</div>';
	return $html;
}

/**
 * Returns the instructions setup in the admin for manual payments
 *
 * @since 0.3.7
 * @param integer $transaction_id optional
 * @return string HTML
*/
function it_cart_buddy_manual_transactions_get_instructions( $transaction_id=false ) {
	$options = it_cart_buddy_get_option( 'cart-buddy-addon-manual-payments' );
	$instructions = empty( $options['manual_payments_instructions'] ) ? false : esc_html( $options['manual_payments_instructions'] );
	return apply_filters( 'it_cart_buddy_get_manual_transactions_instructions', $instructions, $transaction_id );
}
