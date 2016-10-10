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

				if ( ! empty( $args['tokenize'] ) ) {

					if ( ! is_object( $args['tokenize'] ) ) {
						$tokenize = $this->make( 'tokenize', array(
							'source'   => $args['tokenize'],
							'customer' => $cart->get_customer()
						) );
					} else {
						$tokenize = $args['tokenize'];
					}

					if ( ! $tokenize instanceof ITE_Gateway_Tokenize_Request ) {
						throw new InvalidArgumentException( 'Invalid `tokenize` option.' );
					}

					$request->set_tokenize( $tokenize );
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
			case ITE_Gateway_Refund_Request::get_name():

				if ( empty( $args['transaction'] ) || ! $txn = it_exchange_get_transaction( $args['transaction'] ) ) {
					throw new InvalidArgumentException( 'Invalid `transaction` option.' );
				}

				if ( empty( $args['amount'] ) || $args['amount'] <= 0.00 ) {
					throw new InvalidArgumentException( 'Invalid `amount` option.' );
				}

				$reason = empty( $args['reason'] ) ? '' : $args['reason'];

				$refund = new ITE_Gateway_Refund_Request( $txn, $args['amount'], $reason );

				if ( ! empty( $args['issued_by'] ) ) {
					$issued_by = $args['issued_by'];

					if ( is_numeric( $issued_by ) ) {
						$issued_by = get_user_by( 'id', $issued_by );
					}

					if ( ! $issued_by instanceof WP_User ) {
						throw new InvalidArgumentException( 'Invalid `issued_by` option.' );
					}

					$refund->set_issued_by( $issued_by );
				} elseif ( is_user_logged_in() ) {
					$refund->set_issued_by( wp_get_current_user() );
				}

				return $refund;
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