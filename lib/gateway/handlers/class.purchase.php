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

		add_filter(
			"it_exchange_get_{$gateway->get_slug()}_make_payment_button",
			array( $this, 'render_payment_button' )
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function can_handle( $request_name ) { return $request_name === ITE_Gateway_Purchase_Request::get_name(); }

	/**
	 * @inheritDoc
	 */
	public function render_payment_button() {

		$action = esc_attr( $this->get_form_action() );
		$label  = esc_attr( $this->get_payment_button_label() );

		return <<<HTML
<form method="POST" action="{$action}">
	<input type="submit" class="it-exchange-purchase-button it-exchange-purchase-button-{$this->gateway->get_slug()}" 
	name="{$this->gateway->get_slug()}_purchase" value="{$label}">	
	{$this->get_nonce_field()}
	{$this->get_html_before_form_end()}
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
	protected function get_payment_button_label() {
		return sprintf( __( 'Pay by %s', 'it-l10n-ithemes-exchange' ), $this->gateway->get_name() );
	}

	/**
	 * Get the action of the nonce.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	protected function get_nonce_action() { return $this->gateway->get_slug() . '-purchase'; }

	/**
	 * Get the form action URL.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	protected function get_form_action() { return it_exchange_get_page_url( 'transaction' ); }

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
	 * @return string
	 */
	protected function get_html_before_form_end() { return ''; }
}