<?php
/**
 * Live Gateway class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Test_Gateway_Live
 */
class IT_Exchange_Test_Gateway_Live extends ITE_Gateway {

	public $handlers = array();

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return 'Test Gateway Live';
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'test-gateway-live';
	}

	/**
	 * @inheritDoc
	 */
	public function get_addon() {
		return array();
	}

	/**
	 * @inheritDoc
	 */
	public function get_handlers() {
		return $this->handlers;
	}

	/**
	 * @inheritDoc
	 */
	public function is_sandbox_mode() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function get_webhook_param() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function get_settings_fields() {
		return array();
	}

	/**
	 * @inheritDoc
	 */
	public function get_settings_name() {
		return 'test_gateway_live';
	}
}
