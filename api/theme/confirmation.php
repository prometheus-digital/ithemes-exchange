<?php
/**
 * Theme and add-on developers should use these functions to output confirmation details
 * Transaction method add-ons should use the referenced filters to provide HTML
 * @since 0.3.7
 * @package IT_Exchange
*/

/**
 * Returns the HTML for the Confirmation Page
 *
 * Theme developers can call this from a tempalte to produce the confirmation page.
 * It looks for a transaction_id via paramater or REQUEST and passes it to the template part
 * iThemes Exchange provides a generic template part in its it-exchange/lib/templates folder but transaction-method add-ons
 * or theme developers can overwrite it with transactin-confirmation-[addon-slug].php in a registered template directory
 *
 * @since 0.3.7
 * @param integer $id transaction id or false
 * @return html
*/
function it_exchange_get_transaction_confirmation_page_html( $id=false ) {

	// Get var for transaction method
	$var = it_exchange_get_field_name( 'transaction_id' );

	// Set transaction ID from REQUEST if it exists
	$transaction_id = empty( $_REQUEST[$var] ) ? false : $_REQUEST[$var];
	
	// If function was passed a transaction ID, that overrides REQUEST value
	$transaction_id = empty( $id ) ? $transaction_id : $id;

	// Grab transaction method
	$transaction_method = it_exchange_get_transaction_method( $transaction_id );

	// Set template part args 
	it_exchange_set_template_part_args( array( 'transaction_id' => $transaction_id ), 'transaction-confirmation', $transaction_method );

	ob_start();
	it_exchange_get_template_part( 'transaction-confirmation', $transaction_method );
	return ob_get_clean();
}
