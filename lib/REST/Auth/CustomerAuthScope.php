<?php
/**
 * CustomerAuthScope class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Auth;

/**
 * Class CustomerAuthScope
 *
 * @package iThemes\Exchange\REST\Auth
 */
class CustomerAuthScope implements AuthScope {

	/** @var \IT_Exchange_Customer */
	private $customer;

	/**
	 * CustomerAuthScope constructor.
	 *
	 * @param \IT_Exchange_Customer $customer
	 */
	public function __construct( \IT_Exchange_Customer $customer ) { $this->customer = $customer; }

	/**
	 * @inheritDoc
	 */
	public function can( $capability, $args = null ) {
		$user = $this->customer->wp_user;

		if ( ! $user instanceof \WP_User ) {
			return false;
		}

		$args = func_get_args();
		$args = array_merge( array( $user ), $args );

		return call_user_func_array( 'user_can', $args );
	}
}