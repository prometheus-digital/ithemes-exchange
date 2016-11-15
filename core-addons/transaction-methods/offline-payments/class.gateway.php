<?php
/**
 * Offline Payments Gateway class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Gateway_Offline_Payments
 */
class ITE_Gateway_Offline_Payments extends ITE_Gateway {

	/** @var ITE_Gateway_Request_Handler[] */
	private $handlers = array();

	/**
	 * ITE_Gateway_Offline_Payments constructor.
	 */
	public function __construct() {

		parent::__construct();

		$this->handlers[] = new ITE_Offline_Payments_Purchase_Request_Handler( $this, new ITE_Gateway_Request_Factory() );
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Offline Payments', 'it-l10n-ithemes-exchange' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'offline-payments';
	}

	/**
	 * @inheritDoc
	 */
	public function get_addon() {
		return it_exchange_get_addon( 'offline-payments' );
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
	public function is_sandbox_mode() { return false; }

	/**
	 * @inheritDoc
	 */
	public function get_webhook_param() { return ''; }

	/**
	 * @inheritDoc
	 */
	public function get_wizard_settings() {

		$fields = array(
			'preamble',
			'offline-payments-title',
			'offline-payments-instructions',
			'offline-payments-default-status',
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
		return array(
			array(
				'type' => 'html',
				'slug' => 'preamble',
				'html' =>
					'<p>' . __( 'Offline payments allow customers to purchase products from your site using check or cash. Transactions can be set as pending until you receive payment.', 'it-l10n-ithemes-exchange' ) .
					/* translators: %1$s opening link %2$s closing link */
					'</p><p>' .
					sprintf(
						__( 'Video: %1$s Setting up Offline Payments in Exchange %2$s', 'it-l10n-ithemes-exchange' ),
						'<a href="http://ithemes.com/tutorials/using-offline-payments-in-exchange/" target="_blank">', '</a>'
					) . '</p>',
			),
			array(
				'type'    => 'text_box',
				'label'   => __( 'Payment option name', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'offline-payments-title',
				'options' => array( 'class' => 'normal-text' ),
				'tooltip' => __( 'What would you like to title this payment option? eg: Check', 'it-l10n-ithemes-exchange' ),
			),
			array(
				'type'    => 'text_area',
				'label'   => __( 'Instructions after purchase', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'offline-payments-instructions',
				'options' => array(
					'cols'  => 50,
					'rows'  => 5,
					'class' => 'normal-text'
				),
				'tooltip' => __( 'This will be the notification customers see after using this method of payment.', 'it-l10n-ithemes-exchange' ),
			),
			array(
				'type'    => 'drop_down',
				'label'   => __( 'Default payment status', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'offline-payments-default-status',
				'options' => it_exchange_offline_payments_get_default_status_options(),
				'tooltip' => __( 'This is the default payment status applied to all offline payment transactions.', 'it-l10n-ithemes-exchange' ),
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_settings_name() { return 'addon_offline_payments'; }
}
