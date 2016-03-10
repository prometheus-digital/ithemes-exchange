<?php
/**
 * Contains the transaction email recipient class.
 *
 * @since   1.36
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
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_email() {
		return it_exchange_get_transaction_customer_email( $this->transaction );
	}

	/**
	 * Get the recipient's first name.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_first_name() {

		if ( $this->transaction->cart_details->is_guest_checkout ) {
			return $this->get_full_name();
		}

		$user = it_exchange_get_transaction_customer( $this->transaction )->wp_user;

		return $user->first_name;
	}

	/**
	 * Get the recipient's full name.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_full_name() {
		return it_exchange_get_transaction_customer_display_name( $this->transaction );
	}
}