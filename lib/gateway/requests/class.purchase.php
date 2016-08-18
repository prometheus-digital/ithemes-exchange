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

	/**
	 * ITE_Gateway_Purchase_Request constructor.
	 *
	 * @param \ITE_Cart $cart
	 */
	public function __construct( \ITE_Cart $cart ) {
		$this->cart = $cart;
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