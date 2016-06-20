<?php
/**
 * Gateway Request interface.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Interface ITE_Gateway_Request
 */
interface ITE_Gateway_Request {

	/**
	 * Get the customer associated with this request.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Customer
	 */
	public function get_customer();

	/**
	 * Get the name of this request.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public static function get_name();
}