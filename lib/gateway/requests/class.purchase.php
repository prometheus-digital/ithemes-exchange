<?php
/**
 * Purchase Request class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Gateway_Purchase_Request
 */
class ITE_Gateway_Purchase_Request implements ITE_Gateway_Request {

	/** @var ITE_Cart */
	protected $cart;

	/** @var array */
	protected $http_request;

	/** @var string */
	protected $nonce;

	/** @var ITE_Gateway_Card|null */
	protected $card;

	/**
	 * ITE_Gateway_Purchase_Request constructor.
	 *
	 * @param \ITE_Cart $cart
	 * @param string    $nonce
	 * @param array     $http_request
	 */
	public function __construct( \ITE_Cart $cart, $nonce, array $http_request = array() ) {
		$this->cart         = $cart;
		$this->http_request = $http_request;
		$this->nonce        = $nonce;
	}

	/**
	 * Get the cart being purchased.
	 *
	 * @since 1.36
	 *
	 * @return \ITE_Cart
	 */
	public function get_cart() {
		return $this->cart;
	}

	/**
	 * Get the HTTP request.
	 *
	 * @since 1.36.0
	 *
	 * @return array
	 */
	public function get_http_request() {
		return $this->http_request;
	}

	/**
	 * Get the nonce.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_nonce() {
		return $this->nonce;
	}

	/**
	 * Get the card being used for the purchase.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Gateway_Card|null
	 */
	public function get_card() {
		return $this->card;
	}

	/**
	 * Set the card to be used for the purchase.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Gateway_Card $card
	 */
	public function set_card( ITE_Gateway_Card $card ) {
		$this->card = $card;
	}

	/**
	 * @inheritDoc
	 */
	public function get_customer() {
		return $this->cart->get_customer();
	}

	/**
	 * @inheritDoc
	 */
	public static function get_name() {
		return 'purchase';
	}
}