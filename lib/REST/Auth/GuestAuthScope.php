<?php
/**
 * Guest Auth Scope.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Auth;

/**
 * Class GuestAuthScope
 *
 * @package iThemes\Exchange\REST\Auth
 */
class GuestAuthScope implements AuthScope {

	/** @var \IT_Exchange_Guest_Customer */
	private $customer;

	/**
	 * GuestAuthScope constructor.
	 *
	 * @param \IT_Exchange_Guest_Customer $customer
	 */
	public function __construct( \IT_Exchange_Guest_Customer $customer ) { $this->customer = $customer; }

	/**
	 * @inheritDoc
	 */
	public function can( $capability, $args = null ) {

		$args = array_slice( func_get_args(), 1 );

		switch ( $capability ) {
			case 'it_create_others_carts':
				return false;
			case 'it_create_carts':
				return true;
			case 'it_read_cart':
			case 'it_edit_cart':

				$cart = null;

				if ( ! isset( $args[0] ) ) {
					$cart = it_exchange_get_current_cart( false );
				} elseif ( $args[0] instanceof \ITE_Cart ) {
					$cart = $args[0];
				} elseif ( is_string( $args[0] ) ) {
					$cart = it_exchange_get_cart( $args[0] );
				}

				if ( ! $cart || ! $cart->is_guest() ) {
					return false;
				}

				return $cart->get_customer()->get_email() === $this->customer->get_email();
			default:
				return false;
		}
	}
}