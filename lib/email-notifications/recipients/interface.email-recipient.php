<?php
/**
 * Contains the email recipient interface.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * interface IT_Exchange_Email_Recipient
 */
interface IT_Exchange_Email_Recipient {

	/**
	 * Get the recipient's email address.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_email();

	/**
	 * Get the recipient's first name.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_first_name();

	/**
	 * Get the recipient's last name.
	 * 
	 * @since 1.36
	 * 
	 * @return string
	 */
	public function get_last_name();

	/**
	 * Get the recipient's full name.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_full_name();

	/**
	 * Get the recipient's username, if one exists.
	 * 
	 * @since 1.36
	 * 
	 * @return string
	 */
	public function get_username();
}