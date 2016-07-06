<?php
/**
 * Gateway API class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Gateway
 */
abstract class ITE_Gateway {

	/**
	 * Get the name of the gateway.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public abstract function get_name();

	/**
	 * Get the gateway slug.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public abstract function get_slug();

	/**
	 * Get the request handlers this gateway provides.
	 *
	 * @since 1.36
	 *
	 * @return ITE_Gateway_Request_Handler[]
	 */
	public abstract function get_handlers();

	/**
	 * Is the gateway in sandbox mode.
	 *
	 * @since 1.36
	 *
	 * @return bool
	 */
	public abstract function is_sandbox_mode();

	/**
	 * Get the webhook param name.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public abstract function get_webhook_param();

	/**
	 * Get settings fields configuration.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	protected abstract function get_settings_fields();

	/**
	 * Get the settings form controller.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Admin_Settings_Form
	 */
	public function get_settings_form() {
		return new IT_Exchange_Admin_Settings_Form( array(
			'form-fields' => $this->get_settings_fields()
		) );
	}

	/**
	 * Get the name of the settings key for `it_exchange_get_option()`.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	protected abstract function get_settings_name();

	/**
	 * Retrieve the settings controller for this gateway.
	 *
	 * @since 1.36
	 *
	 * @return ITE_Settings_Controller
	 */
	public function settings() {
		return new ITE_Settings_Controller( $this->get_settings_name() );
	}
}