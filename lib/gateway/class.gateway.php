<?php
/**
 * Gateway API class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Gateway
 */
abstract class ITE_Gateway {

	const SSL_REQUIRED = 'required';
	const SSL_SUGGESTED = 'suggested';
	const SSL_NONE = 'none';

	/**
	 * ITE_Gateway constructor.
	 */
	public function __construct() {

	}

	/**
	 * Get the name of the gateway.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public abstract function get_name();

	/**
	 * Get the gateway slug.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public abstract function get_slug();

	/**
	 * Get the add-on slug.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public abstract function get_addon();

	/**
	 * Get the request handlers this gateway provides.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Gateway_Request_Handler[]
	 */
	public abstract function get_handlers();

	/**
	 * Get the handler for a given request.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Gateway_Request $request
	 *
	 * @return \ITE_Gateway_Request_Handler|null
	 */
	public function get_handler_for( ITE_Gateway_Request $request ) {
		foreach ( $this->get_handlers() as $handler ) {
			if ( $handler::can_handle( $request::get_name() ) ) {
				return $handler;
			}
		}

		return null;
	}

	/**
	 * Get a handler by name.
	 *
	 * ::get_handler_for() should be the preferred method to use to retreive a handler.
	 *
	 * @since 2.0.0
	 *
	 * @param string $request_name
	 *
	 * @return \ITE_Gateway_Request_Handler|null
	 */
	public function get_handler_by_request_name( $request_name ) {
		foreach ( $this->get_handlers() as $handler ) {
			if ( $handler::can_handle( $request_name ) ) {
				return $handler;
			}
		}

		return null;
	}

	/**
	 * Can the gateway handle a given request.
	 *
	 * @since 2.0.0
	 *
	 * @param string $request_name
	 *
	 * @return bool
	 */
	final public function can_handle( $request_name ) {
		foreach ( $this->get_handlers() as $handler ) {
			if ( $handler::can_handle( $request_name ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Is the gateway in sandbox mode.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public abstract function is_sandbox_mode();

	/**
	 * Does this gateway require the cart after the purchase has been made.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function requires_cart_after_purchase() { return false; }

	/**
	 * Get the available transaction statuses.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_statuses() {
		return array();
	}

	/**
	 * Get the webhook param name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public abstract function get_webhook_param();

	/**
	 * Get the SSL mode of the gateway.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_ssl_mode() { return self::SSL_NONE; }

	/**
	 * Does this gateway reduce the currency options available.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_currency_support_limited() { return false; }

	/**
	 * Get supported currencies.
	 *
	 * @since 2.0.0
	 *
	 * @return array A list of upper-case currency codes.
	 */
	public function get_supported_currencies() { return array(); }

	/**
	 * Get settings fields configuration.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public abstract function get_settings_fields();

	/**
	 * Get the settings form controller.
	 *
	 * @since 2.0.0
	 *
	 * @return IT_Exchange_Admin_Settings_Form
	 */
	public function get_settings_form() {
		return new IT_Exchange_Admin_Settings_Form( array(
			'form-fields' => $this->get_settings_fields(),
			'prefix'      => $this->get_settings_name(),
		) );
	}

	/**
	 * Get the name of the settings key for `it_exchange_get_option()`.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public abstract function get_settings_name();

	/**
	 * Get the settings that should be displayed in the wizard.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_wizard_settings() {	return array(); }

	/**
	 * Retrieve the settings controller for this gateway.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Settings_Controller
	 */
	public function settings() {
		return new ITE_Settings_Controller( $this->get_settings_name() );
	}
}
