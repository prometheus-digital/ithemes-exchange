<?php
/**
 * Zero Sum Checkout Gateway.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Zero_Sum_Checkout_Gateway
 */
class ITE_Zero_Sum_Checkout_Gateway extends ITE_Gateway {

	/** @var ITE_Gateway_Request_Handler[] */
	private $handlers = array();

	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct();

		$this->handlers[] = new ITE_Zero_Sum_Checkout_Purchase_Handler( $this, new ITE_Gateway_Request_Factory() );
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() { return __( 'Zero Sum Checkout', 'it-l10n-ithemes-exchange' ); }

	/**
	 * @inheritDoc
	 */
	public function get_slug() { return 'zero-sum-checkout'; }

	/**
	 * @inheritDoc
	 */
	public function get_addon() { return it_exchange_get_addon( $this->get_slug() ); }

	/**
	 * @inheritDoc
	 */
	public function get_handlers() { return $this->handlers; }

	/**
	 * @inheritDoc
	 */
	public function get_statuses() {
		return array(
			'Completed' => array(
				'label'      => 'Completed',
				'selectable' => false,
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function is_sandbox_mode() { return false; }

	/**
	 * @inheritDoc
	 */
	public function get_webhook_param() { return ''; }

	/**
	 * @inheritDoc
	 */
	public function get_settings_fields() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_settings_name() { return ''; }
}