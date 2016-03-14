<?php
/**
 * Contains the simple email recipient class.
 *
 * @since   1.36
 * @license GPLv2
 */

/***
 * Class IT_Exchange_Email_Recipient_Email
 */
class IT_Exchange_Email_Recipient_Email implements IT_Exchange_Email_Recipient {

	/**
	 * @var string
	 */
	private $email;

	/**
	 * IT_Exchange_Email_Recipient_Email constructor.
	 *
	 * @param string $email
	 */
	public function __construct( $email ) {
		$this->email;
	}

	/**
	 * Get the recipient's email address.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Get the recipient's first name.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_first_name() {

		$parts = explode( '@', $this->get_email() );

		return $parts[0];
	}

	/**
	 * Get the recipient's full name.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_full_name() {
		return $this->get_first_name();
	}
}