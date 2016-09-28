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
	 * @return ITE_Gateway_Request|null
	 *
	 * @throws \InvalidArgumentException
	 */
	public function make( $request, array $args = array() ) {

		switch ( $request ) {
			case ITE_Gateway_Purchase_Request::get_name():
				$cart  = empty( $args['cart'] ) ? it_exchange_get_current_cart() : $args['cart'];
				$nonce = empty( $args['nonce'] ) ? '' : $args['nonce'];
				$http  = empty( $args['http_request'] ) ? array() : (array) $args['http_request'];

				$request = new ITE_Gateway_Purchase_Request( $cart, $nonce, $http );

				if ( ! empty( $args['card'] ) ) {
					$card = $args['card'];

					if ( is_array( $card ) && isset( $card['number'], $card['year'], $card['month'], $card['cvc'] ) ) {
						$card = new ITE_Gateway_Card( $card['number'], $card['year'], $card['month'], $card['cvc'] );
					}

					if ( ! $card instanceof ITE_Gateway_Card ) {
						throw new InvalidArgumentException( 'Invalid `card` option.' );
					}

					$request->set_card( $args['card'] );
				}

				if ( ! empty( $args['token'] ) ) {
					$token = $args['token'];

					if ( is_int( $token ) ) {
						$token = ITE_Payment_Token::get( $token );
					}

					if ( ! $token instanceof ITE_Payment_Token ) {
						throw new InvalidArgumentException( 'Invalid `token` option.' );
					}

					$request->set_token( $token );
				}

				return $request;
			case ITE_Webhook_Gateway_Request::get_name():
				return new ITE_Webhook_Gateway_Request( $args['webhook_data'] );
			default:
				return null;
		}
	}
}