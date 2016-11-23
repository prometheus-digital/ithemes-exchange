<?php
/**
 * Contains the customer email recipient class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Recipient_Customer
 */
class IT_Exchange_Email_Recipient_Customer implements IT_Exchange_Email_Recipient {

	/**
	 * @var IT_Exchange_Customer
	 */
	private $customer;

	/**
	 * IT_Exchange_Email_Recipient_Customer constructor.
	 *
	 * @param IT_Exchange_Customer $customer
	 */
	public function __construct( IT_Exchange_Customer $customer ) {
		$this->customer = $customer;
	}

	/**
	 * Get the recipient's email address.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->customer->data->user_email;
	}

	/**
	 * Get the recipient's first name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_first_name() {

		if ( ! empty( $this->customer->data->first_name ) ) {
			return $this->customer->data->first_name;
		} else if ( ! empty( $this->customer->data->display_name ) ) {
			return $this->customer->data->display_name;
		}

		return '';
	}

	/**
	 * Get the recipient's last name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_last_name() {
		
		if ( ! empty( $this->customer->data->last_name ) ) {
			return $this->customer->data->last_name;
		} else if ( ! empty( $this->customer->data->display_name ) ) {
			return $this->customer->data->display_name;
		}

		return '';
	}

	/**
	 * Get the recipient's full name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_full_name() {

		if ( ! empty( $this->customer->data->first_name ) && ! empty( $this->customer->data->last_name ) ) {
			return $this->customer->data->first_name . ' ' . $this->customer->data->last_name;
		} else if ( ! empty( $this->customer->data->display_name ) ) {
			return $this->customer->data->display_name;
		}

		return '';
	}

	/**
	 * Get the recipient's username, if one exists.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_username() {
		return $this->customer->data->user_login;
	}
}
