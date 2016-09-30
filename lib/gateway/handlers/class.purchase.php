<?php
/**
 * Abstract purchase request handler class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Purchase_Request_Handler
 */
abstract class ITE_Purchase_Request_Handler implements ITE_Gateway_Request_Handler {

	/**
	 * @var ITE_Gateway
	 */
	protected $gateway;

	/**
	 * @var \ITE_Gateway_Request_Factory
	 */
	protected $factory;

	/**
	 * ITE_Purchase_Request_Handler constructor.
	 *
	 * @param ITE_Gateway                 $gateway
	 * @param ITE_Gateway_Request_Factory $factory
	 */
	public function __construct( ITE_Gateway $gateway, ITE_Gateway_Request_Factory $factory ) {
		$this->gateway = $gateway;
		$this->factory = $factory;

		$self = $this;

		add_filter(
			"it_exchange_get_{$gateway->get_slug()}_make_payment_button",
			function () use ( $self, $factory ) {
				try {
					return $self->render_payment_button( $factory->make( 'purchase' ) );
				} catch ( Exception $e ) {
					return '';
				}
			}
		);

		add_filter(
			"it_exchange_do_transaction_{$gateway->get_slug()}",
			function ( $_, $transaction_object ) use ( $self, $factory ) {
				if ( ! isset( $transaction_object->cart_id ) ) {
					return $_;
				}

				$cart = it_exchange_get_cart( $transaction_object->cart_id );

				if ( ! $cart ) {
					return $_;
				}

				$nonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';
				/** @noinspection ExceptionsAnnotatingAndHandlingInspection */
				$txn = $self->handle( $factory->make( 'purchase', array(
					'cart'         => $cart,
					'nonce'        => $nonce,
					'http_request' => $_REQUEST,
					'token'        => empty( $_REQUEST['purchase_token'] ) ? '' : (int) $_REQUEST['purchase_token'],
					'tokenize'     => empty( $_REQUEST['to_tokenize'] ) ? '' : $_REQUEST['to_tokenize']
				) ) );

				return $txn ? $txn->ID : false;
			},
			10, 2
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function can_handle( $request_name ) { return $request_name === ITE_Gateway_Purchase_Request::get_name(); }

	/**
	 * Get the gateway for this handler.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Gateway
	 */
	public function get_gateway() {
		return $this->gateway;
	}

	/**
	 * Render a payment button.
	 *
	 * @param \ITE_Gateway_Purchase_Request $request
	 *
	 * @return string
	 */
	public function render_payment_button( ITE_Gateway_Purchase_Request $request ) {

		$action     = esc_attr( $this->get_form_action() );
		$label      = esc_attr( $this->get_payment_button_label() );
		$field_name = esc_attr( it_exchange_get_field_name( 'transaction_method' ) );

		return <<<HTML
<form method="POST" action="{$action}" id="{$this->get_gateway()->get_slug()}-purchase-form">
	<input type="submit" class="it-exchange-purchase-button it-exchange-purchase-button-{$this->gateway->get_slug()}" 
	name="{$this->gateway->get_slug()}_purchase" value="{$label}">
	<input type="hidden" name="{$field_name}" value="{$this->gateway->get_slug()}">
	{$this->get_nonce_field()}
	{$this->get_html_before_form_end( $request )}
</form>
HTML;
	}

	/**
	 * Get the label for the payment button.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_payment_button_label() {
		return it_exchange_get_transaction_method_name_from_slug( $this->get_gateway()->get_slug() );
	}

	/**
	 * Get the form action URL.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	protected function get_form_action() { return it_exchange_get_page_url( 'transaction' ); }

	/**
	 * Get the action of the nonce.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	protected function get_nonce_action() { return $this->gateway->get_slug() . '-purchase'; }

	/**
	 * Get a nonce.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_nonce() { return wp_create_nonce( $this->get_nonce_action() ); }

	/**
	 * Output the payment button nonce.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	protected function get_nonce_field() { return wp_nonce_field( $this->get_nonce_action(), '_wpnonce', false, false ); }

	/**
	 * Get HTML to be rendered before the form is closed.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Gateway_Purchase_Request $request
	 *
	 * @return string
	 */
	protected function get_html_before_form_end( ITE_Gateway_Purchase_Request $request ) { return ''; }

	/**
	 * Get the data for REST API Purchase endpoint.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Gateway_Purchase_Request $request
	 *
	 * @return array
	 */
	public function get_data_for_REST( ITE_Gateway_Purchase_Request $request ) {
		return array( 'method' => 'REST' );
	}
}