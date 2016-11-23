<?php
/**
 * Null sender class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Null_Sender
 */
class IT_Exchange_Email_Null_Sender implements IT_Exchange_Email_Sender {

	/**
	 * Send the email.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Sendable $email
	 *
	 * @return bool
	 * @throws IT_Exchange_Email_Delivery_Exception
	 */
	public function send( IT_Exchange_Sendable $email ) {
		return true;
	}

	/**
	 * Bulk send emails.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Sendable[] $emails
	 *
	 * @return bool
	 * @throws InvalidArgumentException If a given email does not implement IT_Exchange_Sendable
	 * @throws IT_Exchange_Email_Delivery_Exception
	 */
	public function bulk_send( array $emails ) {
		return true;
	}
}
