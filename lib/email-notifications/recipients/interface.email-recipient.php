<?php
/**
 * Contains the email recipient interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * interface IT_Exchange_Email_Recipient
 */
interface IT_Exchange_Email_Recipient {

	/**
	 * Get the recipient's email address.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_email();

	/**
	 * Get the recipient's first name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_first_name();

	/**
	 * Get the recipient's last name.
	 * 
	 * @since 2.0.0
	 * 
	 * @return string
	 */
	public function get_last_name();

	/**
	 * Get the recipient's full name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_full_name();

	/**
	 * Get the recipient's username, if one exists.
	 * 
	 * @since 2.0.0
	 * 
	 * @return string
	 */
	public function get_username();
}
