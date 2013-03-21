<?php
/**
 * These functions are intended to be used on the confirmation page
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

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
