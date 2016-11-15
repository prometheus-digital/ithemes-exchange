<?php
/**
 * JS Tokenize Request interface.
 *
 * @since 2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Gateway_JS_Tokenize_Handler
 */
interface ITE_Gateway_JS_Tokenize_Handler {

	/**
	 * Get JavaScript to tokenize a payment source without touching the Exchange server.
	 *
	 * This will be revealed in the ITExchangeAPI global.
	 *
	 * Example:
	 *
	 * function( source ) {
	 *      return Stripe.card.createToken( source );
	 * }
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_js();
}
