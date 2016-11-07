<?php
/**
 * Redirect purchase request handler.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Redirect_Purchase_Request_Handler
 */
abstract class ITE_Redirect_Purchase_Request_Handler extends ITE_Purchase_Request_Handler {

	/**
	 * @inheritDoc
	 */
	public function __construct( \ITE_Gateway $gateway, \ITE_Gateway_Request_Factory $factory ) {
		parent::__construct( $gateway, $factory );

		add_action( 'init', array( $this, 'maybe_redirect' ), 20 );
	}

	/**
	 * @inheritDoc
	 */
	protected function get_form_action() {

		if ( it_exchange_is_multi_item_cart_allowed() ) {
			return it_exchange_get_page_url( 'checkout' );
		} else {
			return get_permalink( it_exchange_get_the_product_id() );
		}
	}

	/**
	 * Maybe perform a redirect to an external payment gateway.
	 *
	 * @since 1.36
	 * @throws \InvalidArgumentException
	 * @throws \UnexpectedValueException
	 */
	public function maybe_redirect() {

		if ( ! isset( $_POST["{$this->gateway->get_slug()}_purchase"] ) ) {
			return;
		}

		if ( isset( $_POST['auto_return'] ) ) {
			return;
		}

		$nonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';

		if ( isset( $_POST['cart_id'] ) ) {

			$cart_id = $_POST['cart_id'];

			$cart = it_exchange_get_cart( $cart_id );

			if ( ! $cart ) {
				throw new InvalidArgumentException( "No cart found for {$cart_id}." );
			}

			if ( empty( $_POST['cart_auth'] ) || ! $cart->validate_auth_secret( $_POST['cart_auth'] ) ) {
				throw new InvalidArgumentException( "Invalid cart auth for {$cart_id}." );
			}

		} else {
			$cart = it_exchange_get_current_cart();
		}

		$this->redirect( $this->factory->make( 'purchase', array( 'cart' => $cart, 'nonce' => $nonce ) ) );
	}

	/**
	 * Perform the redirect to an external gateway for payment.
	 *
	 * @since 1.36
	 */
	protected function redirect( ITE_Gateway_Purchase_Request_Interface $request ) {
		wp_redirect( $this->get_redirect_url( $request ) );
		die();
	}

	/**
	 * @inheritDoc
	 */
	public function get_data_for_REST( ITE_Gateway_Purchase_Request_Interface $request ) {
		return array(
			'method' => 'redirect',
			'url'    => $this->get_redirect_url( $request ),
			'auth'   => $request->get_cart()->generate_auth_secret(),
		);
	}

	/**
	 * Get the redirect URL.
	 *
	 * @since 1.36
	 *
	 * @param ITE_Gateway_Purchase_Request_Interface $request
	 *
	 * @return string
	 */
	public abstract function get_redirect_url( ITE_Gateway_Purchase_Request_Interface $request );
}