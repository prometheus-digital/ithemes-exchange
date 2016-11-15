<?php
/**
 * Contains the transaction email recipient class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Recipient_Transaction
 */
class IT_Exchange_Email_Recipient_Transaction implements IT_Exchange_Email_Recipient {

	/**
	 * @var IT_Exchange_Transaction
	 */
	private $transaction;

	/**
	 * IT_Exchange_Email_Recipient_Transaction constructor.
	 *
	 * @param IT_Exchange_Transaction $transaction
	 */
	public function __construct( IT_Exchange_Transaction $transaction ) {
		$this->transaction = $transaction;
	}

	/**
	 * Get the recipient's email address.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->transaction->get_customer_email();
	}

	/**
	 * Get the recipient's first name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_first_name() {

		if ( $this->transaction->is_guest_purchase() && ! $this->transaction->get_customer()->get_first_name() ) {

			$parts = explode( '@', $this->get_email() );

			return $parts[0];
		}

		return $this->transaction->get_customer()->get_first_name() ?: $this->transaction->get_customer()->get_display_name();
	}

	/**
	 * Get the recipient's last name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_last_name() {

		if ( $this->transaction->is_guest_purchase() && ! $this->transaction->get_customer()->get_last_name() ) {

			$parts = explode( '@', $this->get_email() );

			return $parts[0];
		}

		return $this->transaction->get_customer()->get_last_name() ?: $this->transaction->get_customer()->get_display_name();
	}

	/**
	 * Get the recipient's full name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_full_name() {

		$customer = $this->transaction->get_customer();

		if ( ! $customer ) {
			return $this->get_email();
		}

		if ( $customer->get_full_name() ) {
			return $customer->get_full_name();
		}

		return $this->get_email();
	}

	/**
	 * Get the recipient's username, if one exists.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_username() {
		return it_exchange_get_transaction_customer( $this->transaction )->data->user_login;
	}
}
