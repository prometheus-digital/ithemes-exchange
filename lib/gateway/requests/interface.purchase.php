<?php
/**
 * Purchase Request interface.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Interface ITE_Gateway_Purchase_Request
 */
interface ITE_Gateway_Purchase_Request_Interface extends ITE_Gateway_Request {

	/**
	 * Get the cart being purchased.
	 *
	 * @since 1.36
	 *
	 * @return \ITE_Cart
	 */
	public function get_cart();

	/**
	 * Get the HTTP request.
	 *
	 * @since 1.36.0
	 *
	 * @return array
	 */
	public function get_http_request();

	/**
	 * Get the nonce.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_nonce();

	/**
	 * Get the card being used for the purchase.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Gateway_Card|null
	 */
	public function get_card();

	/**
	 * Set the card to be used for the purchase.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Gateway_Card $card
	 */
	public function set_card( ITE_Gateway_Card $card );

	/**
	 * Get the payment token.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Payment_Token|null
	 */
	public function get_token();

	/**
	 * Set the payment token.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Payment_Token|null $token
	 *
	 * @throws \InvalidArgumentException
	 */
	public function set_token( ITE_Payment_Token $token );

	/**
	 * Get the possible tokenize request.
	 *
	 * This might be used in scenarios like Guest Checkout.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Gateway_Tokenize_Request|null
	 */
	public function get_tokenize();

	/**
	 * Set the tokenize request.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Gateway_Tokenize_Request $tokenize
	 */
	public function set_tokenize( ITE_Gateway_Tokenize_Request $tokenize );

	/**
	 * Get the destination the customer should be redirected to after purchase.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_redirect_to();

	/**
	 * Set the destination the customer should be redirected to after purchase.
	 *
	 * This defaults to the confirmation page.
	 *
	 * @since 1.36.0
	 *
	 * @param string $redirect_to
	 */
	public function set_redirect_to( $redirect_to );
}