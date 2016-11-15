<?php
/**
 * Refund Request.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Gateway_Refund_Request
 */
class ITE_Gateway_Refund_Request implements ITE_Gateway_Request {

	/** @var IT_Exchange_Transaction */
	private $transaction;

	/** @var float */
	private $amount = 0.00;

	/** @var string */
	private $reason = '';

	/** @var WP_User */
	private $issued_by;

	/**
	 * ITE_Gateway_Refund_Request constructor.
	 *
	 * @param \IT_Exchange_Transaction $transaction
	 * @param float                    $amount
	 * @param string                   $reason
	 */
	public function __construct( \IT_Exchange_Transaction $transaction, $amount, $reason = '' ) {
		$this->transaction = $transaction;
		$this->amount      = $amount;
		$this->reason      = $reason;
	}

	/**
	 * Get the transaction to refund.
	 *
	 * @since 2.0.0
	 *
	 * @return \IT_Exchange_Transaction
	 */
	public function get_transaction() {	return $this->transaction; }

	/**
	 * Get the total amount to refund.
	 *
	 * @since 2.0.0
	 *
	 * @return float
	 */
	public function get_amount() { return $this->amount; }

	/**
	 * Get the refund reason.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_reason() { return $this->reason; }

	/**
	 * Get the user who issued the refund.
	 *
	 * @since 2.0.0
	 *
	 * @return \WP_User|null
	 */
	public function issued_by() { return $this->issued_by; }

	/**
	 * Set the user who issued the refund.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_User $issued_by
	 */
	public function set_issued_by( WP_User $issued_by ) {
		$this->issued_by = $issued_by;
	}

	/**
	 * @inheritDoc
	 */
	public function get_customer() { return $this->transaction->get_customer(); }

	/**
	 * @inheritDoc
	 */
	public static function get_name() { return 'refund'; }
}
