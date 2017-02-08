<?php
/**
 * Update Token request.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Gateway_Update_Payment_Token_Request
 */
class ITE_Gateway_Update_Payment_Token_Request implements ITE_Gateway_Request {

	/** @var ITE_Payment_Token */
	private $token;

	/** @var int */
	private $expiration_month;

	/** @var int */
	private $expiration_year;

	/**
	 * ITE_Gateway_Update_Token_Request constructor.
	 *
	 * @param ITE_Payment_Token $token
	 */
	public function __construct( ITE_Payment_Token $token ) { $this->token = $token; }

	/**
	 * Get the token's new expiration month.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_expiration_month() {
		return $this->expiration_month;
	}

	/**
	 * Set the token's new expiration month.
	 *
	 * @since 2.0.0
	 *
	 * @param int $expiration_month
	 */
	public function set_expiration_month( $expiration_month ) {
		$this->expiration_month = $expiration_month;
	}

	/**
	 * Get the new token's expiration year.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_expiration_year() {
		return $this->expiration_year;
	}

	/**
	 * Set the token's new expiration year.
	 *
	 * @since 2.0.0
	 *
	 * @param int $expiration_year
	 */
	public function set_expiration_year( $expiration_year ) {
		$this->expiration_year = $expiration_year;
	}

	/**
	 * Get the token being updated.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Payment_Token
	 */
	public function get_token() {
		return $this->token;
	}

	/**
	 * @inheritDoc
	 */
	public function get_customer() { return $this->token->customer; }

	/**
	 * @inheritDoc
	 */
	public static function get_name() { return 'update-payment-token'; }
}