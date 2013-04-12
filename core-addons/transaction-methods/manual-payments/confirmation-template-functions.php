<?php
/**
 * These functions are intended to be used on the confirmation page
 *
 * @since 0.3.7
 * @package IT_Exchange
*/

/**
 * Returns the instructions setup in the admin for manual payments
 *
 * @since 0.3.7
 * @param integer $transaction_id optional
 * @return string HTML
*/
function it_exchange_manual_transactions_get_instructions( $transaction_id=false ) {
	$options = it_exchange_get_option( 'it-exchange-addon-manual-payments' );
	$instructions = empty( $options['manual_payments_instructions'] ) ? false : esc_html( $options['manual_payments_instructions'] );
	return apply_filters( 'it_exchange_get_manual_transactions_instructions', $instructions, $transaction_id );
}
