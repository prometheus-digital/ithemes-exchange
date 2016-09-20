<?php
/**
 * PayPal Standard Gateway.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_PayPal_Standard_Gateway
 */
class ITE_PayPal_Standard_Gateway extends ITE_Gateway {

	/** @var ITE_Gateway_Request_Handler[] */
	private $handlers;

	/**
	 * ITE_PayPal_Standard_Gateway constructor.
	 */
	public function __construct() {
		$this->handlers[] = new ITE_PayPal_Standard_Purchase_Handler( $this, new ITE_Gateway_Request_Factory() );
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() { return __( 'PayPal Standard', 'it-l10n-ithemes-exchange' ); }

	/**
	 * @inheritDoc
	 */
	public function get_slug() { return 'paypal-standard'; }

	/**
	 * @inheritDoc
	 */
	public function get_addon() { return it_exchange_get_addon( 'paypal-standard' ); }

	/**
	 * @inheritDoc
	 */
	public function get_handlers() { return $this->handlers; }

	/**
	 * @inheritDoc
	 */
	public function is_sandbox_mode() { return false; }

	/**
	 * @inheritDoc
	 */
	public function get_webhook_param() {

		/**
		 * Filter the PayPal Standard webhook param.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param
		 */
		return apply_filters( 'it_exchange_paypal-standard_webhook', 'it_exchange_paypal-standard' );
	}

	/**
	 * @inheritDoc
	 */
	protected function get_settings_fields() { return array(); }

	/**
	 * @inheritDoc
	 */
	protected function get_settings_name() { return 'addon_paypal_standard'; }
}