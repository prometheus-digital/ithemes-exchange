<?php
/**
 * PayPal Standard Gateway.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_PayPal_Standard_Gateway
 */
class ITE_PayPal_Standard_Gateway extends ITE_Gateway {

	/** @var ITE_Gateway_Request_Handler[] */
	private $handlers;

	/** @var array */
	private $fields = array();

	/**
	 * ITE_PayPal_Standard_Gateway constructor.
	 */
	public function __construct() {
		parent::__construct();

		$factory = new ITE_Gateway_Request_Factory();

		$this->handlers[] = new ITE_PayPal_Standard_Purchase_Handler( $this, $factory );
		$this->handlers[] = new ITE_PayPal_Standard_Webhook_Handler( $this, $factory );
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
	public function is_sandbox_mode() { return $this->settings()->get( 'sandbox-mode' ); }

	/**
	 * @inheritDoc
	 */
	public function requires_cart_after_purchase() { return true; }

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
	public function get_ssl_mode() { return self::SSL_SUGGESTED; }

	/**
	 * @inheritDoc
	 */
	public function get_wizard_settings() {

		$fields = array(
			'preamble',
			'purchase-button-label',
			'live-email-address',
		);

		$wizard = array();

		foreach ( $this->get_settings_fields() as $field ) {
			if ( in_array( $field['slug'], $fields ) ) {
				$wizard[] = $field;
			}
		}

		return $wizard;
	}

	/**
	 * @inheritDoc
	 */
	public function get_settings_fields() {

		if ( $this->fields ) {
			return $this->fields;
		}

		$this->fields = array(
			array(
				'type' => 'html',
				'slug' => 'preamble',
				'html' =>
					'<p>' .
					__( 'This is the simple and fast version to get PayPal setup for your store. You might use this version just to get your store going, but we highly suggest you switch to the PayPal Standard Secure option.', 'it-l10n-ithemes-exchange' ) . ' ' .
					__( 'To get PayPal set up for use with Exchange, you\'ll need to add the following information from your PayPal account.', 'it-l10n-ithemes-exchange' ) .
					'<p>' .
					sprintf(
						__( 'Video: %1$s Setting up PayPal Standard Basic in Exchange %2$s', 'it-l10n-ithemes-exchange' ),
						'<a href="http://ithemes.com/tutorials/setting-up-paypal-standard-basic/" target="_blank">', '</a>'
					) . '</p><p>' .
					sprintf(
						__( 'Don\'t have a PayPal account yet? %1$sGo set one up here%2$s.', 'it-l10n-ithemes-exchange' ),
						'<a href="http://paypal.com" target="_blank">', '</a>'
					) . '</p>',
			),
			array(
				'type'    => 'text_box',
				'label'   => __( 'Purchase Button Label', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'purchase-button-label',
				'tooltip' => __( 'This is the text inside the button your customers will press to purchase with PayPal Standard.', 'it-l10n-ithemes-exchange' ),
				'default' => __( 'Pay with PayPal', 'it-l10n-ithemes-exchange' ),
			),
			array(
				'type'    => 'email',
				'label'   => __( 'PayPal Email Address', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'live-email-address',
				'tooltip' => __( 'We need this to tie payments to your account.', 'it-l10n-ithemes-exchange' )
			),
			array(
				'type'  => 'check_box',
				'label' => __( 'Enable PayPal Sandbox Mode', 'it-l10n-ithemes-exchange' ),
				'slug'  => 'sandbox-mode',
			),
			array(
				'type'    => 'email',
				'label'   => __( 'PayPal Sandbox Email Address', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'test-email-address',
				'tooltip' => __( 'We need this to tie payments to your account.', 'it-l10n-ithemes-exchange' ),
				'show_if' => array( 'field' => 'sandbox-mode', 'value' => true, 'compare' => '=' ),
			),
		);

		return $this->fields;
	}

	/**
	 * @inheritDoc
	 */
	public function get_settings_name() { return 'addon_paypal_standard'; }

	/**
	 * @inheritDoc
	 */
	public function supports_feature( ITE_Optionally_Supported_Feature $feature ) {

		switch ( $feature->get_feature_slug() ) {
			case 'recurring-payments':
			case 'one-time-fee':
				return true;
		}

		return parent::supports_feature( $feature );
	}

	/**
	 * @inheritDoc
	 */
	public function supports_feature_and_detail( ITE_Optionally_Supported_Feature $feature, $slug, $detail ) {

		switch ( $feature->get_feature_slug() ) {
			case 'one-time-fee':
				switch ( $slug ) {
					case 'discount':
						return true;
					default:
						return false;
				}
			case 'recurring-payments':
				switch ( $slug ) {
					case 'profile':

						/** @var $detail IT_Exchange_Recurring_Profile */
						switch ( $detail->get_interval_type() ) {
							case IT_Exchange_Recurring_Profile::TYPE_DAY:
								return $detail->get_interval_count() <= 90;
							case IT_Exchange_Recurring_Profile::TYPE_WEEK:
								return $detail->get_interval_count() <= 52;
							case IT_Exchange_Recurring_Profile::TYPE_MONTH:
								return $detail->get_interval_count() <= 24;
							case IT_Exchange_Recurring_Profile::TYPE_YEAR:
								return $detail->get_interval_count() <= 5;
							default:
								return false;
						}
					case 'trial-profile':

						/** @var $detail IT_Exchange_Recurring_Profile */
						switch ( $detail->get_interval_type() ) {
							case IT_Exchange_Recurring_Profile::TYPE_DAY:
								return $detail->get_interval_count() <= 90;
							case IT_Exchange_Recurring_Profile::TYPE_WEEK:
								return $detail->get_interval_count() <= 52;
							case IT_Exchange_Recurring_Profile::TYPE_MONTH:
								return $detail->get_interval_count() <= 24;
							case IT_Exchange_Recurring_Profile::TYPE_YEAR:
								return $detail->get_interval_count() <= 5;
							default:
								return false;
						}

					case 'auto-renew':
					case 'trial':
						return true;
					default:
						return false;
				}
		}

		return parent::supports_feature( $feature );
	}
}
