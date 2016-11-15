<?php
/**
 * Contains the sender aware interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Email_Sender_Aware
 */
interface IT_Exchange_Email_Sender_Aware {

	/**
	 * Set the email sender to be used.
	 * 
	 * @since 2.0.0
	 * 
	 * @param IT_Exchange_Email_Sender $sender
	 */
	public function set_sender( IT_Exchange_Email_Sender $sender );
}
