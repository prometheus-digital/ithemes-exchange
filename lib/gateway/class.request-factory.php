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

					if ( is_array( $card ) ) {
						$card = $this->build_card( $card );
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
			case ITE_Gateway_Tokenize_Request::get_name():

				if ( empty( $args['customer'] ) ) {
					$customer = it_exchange_get_current_customer();
				} else {
					$customer = it_exchange_get_customer( $args['customer'] );
				}

				if ( empty( $args['source'] ) ) {
					$source = '';
				} elseif ( is_array( $args['source'] ) ) {
					$source = $this->build_card( $args['source'] );

					if ( ! $source ) {
						$source = $this->build_bank_account( $args['source'] );
					}
				} else {
					$source = $args['source'];
				}

				$label   = empty( $args['label'] ) ? '' : $args['label'];
				$primary = empty( $args['primary'] ) ? false : $args['primary'];

				if ( ! $customer ) {
					throw new InvalidArgumentException( 'Invalid `customer` option.' );
				}

				if ( ! $source ) {
					throw new InvalidArgumentException( 'Invalid `source` option.' );
				}

				return new ITE_Gateway_Tokenize_Request( $customer, $source, $label, $primary );
			default:
				return null;
		}
	}

	/**
	 * Build a card from an array.
	 *
	 * @since 1.36.0
	 *
	 * @param array $card
	 *
	 * @return \ITE_Gateway_Card|null
	 */
	protected function build_card( array $card ) {
		if ( isset( $card['number'], $card['year'], $card['month'], $card['cvc'] ) ) {
			$name = empty( $card['name'] ) ? '' : $card['name'];

			return new ITE_Gateway_Card( $card['number'], $card['year'], $card['month'], $card['cvc'], $name );
		}

		return null;
	}

	/**
	 * Build a bank account from an array.
	 *
	 * @since 1.36.0
	 *
	 * @param array $account
	 *
	 * @return \ITE_Gateway_Bank_Account|null
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function build_bank_account( array $account ) {

		if ( isset( $account['name'], $account['number'], $account['type'] ) ) {
			$routing = empty( $account['routing'] ) ? '' : $account['routing'];

			return new ITE_Gateway_Bank_Account( $account['name'], $account['type'], $account['number'], $routing );
		}

		return null;
	}
}