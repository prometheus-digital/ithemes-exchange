<?php
/**
 * Cart Coercion Failed Exception.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Cart_Coercion_Failed_Exception
 */
class ITE_Cart_Coercion_Failed_Exception extends Exception {

	/**
	 * @var \ITE_Cart
	 */
	private $cart;

	/**
	 * @var \ITE_Cart_Validator
	 */
	private $validator;

	/**
	 * ITE_Cart_Coercion_Failed_Exception constructor.
	 *
	 * @param string              $message
	 * @param \ITE_Cart           $cart
	 * @param \ITE_Cart_Validator $validator
	 * @param int                 $code
	 * @param \Exception|null     $previous
	 */
	public function __construct( $message, ITE_Cart $cart, ITE_Cart_Validator $validator, $code = 0, Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );

		$this->cart      = $cart;
		$this->validator = $validator;
	}

	/**
	 * Get the cart validator.
	 * 
	 * @since 1.36.0
	 * 
	 * @return \ITE_Cart_Validator
	 */
	public function get_validator() {
		return $this->validator;
	}

	/**
	 * Get the cart that failed coercion.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Cart
	 */
	public function get_cart() {
		return $this->cart;
	}
}