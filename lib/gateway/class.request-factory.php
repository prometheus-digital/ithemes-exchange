<?php
/**
 * Payment Gateway Request Factory.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Gateway_Request_Factory
 */
class ITE_Gateway_Request_Factory {

	/**
	 * Construct a request object.
	 *
	 * @since 1.36
	 *
	 * @param string $request
	 * @param array  $args
	 *
	 * @return ITE_Gateway_Request
	 */
	public function make( $request, array $args = array() ) {

		switch ( $request ) {
			case ITE_Gateway_Purchase_Request::get_name():
				$cart  = empty( $args['cart'] ) ? it_exchange_get_current_cart() : $args['cart'];
				$nonce = empty( $args['nonce'] ) ? '' : $args['nonce'];

				return new ITE_Gateway_Purchase_Request( $cart, $nonce );
			case ITE_Webhook_Gateway_Request::get_name():
				return new ITE_Webhook_Gateway_Request( $args['webhook_data'] );
			default:
				return null;
		}
	}
}