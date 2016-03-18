<?php
/**
 * Contains the 'sender' interface.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Email_Sender
 */
interface IT_Exchange_Email_Sender {

	/**
	 * Send the email.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Sendable $email
	 *
	 * @return bool
	 * @throws IT_Exchange_Email_Delivery_Exception
	 */
	public function send( IT_Exchange_Sendable $email );
}