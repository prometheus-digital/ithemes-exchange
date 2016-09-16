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

	/**
	 * @var ITE_Cart
	 */
	protected $cart;

	/** @var string */
	protected $nonce;

	/**
	 * ITE_Gateway_Purchase_Request constructor.
	 *
	 * @param \ITE_Cart $cart
	 * @param string    $nonce
	 */
	public function __construct( \ITE_Cart $cart, $nonce ) {
		$this->cart  = $cart;
		$this->nonce = $nonce;
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