<?php
/**
 * Webhook Request.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Webhook_Gateway_Request
 */
class ITE_Webhook_Gateway_Request implements ITE_Gateway_Request {

	/** @var array */
	private $webhook_data;

	/**
	 * ITE_Webhook_Gateway_Request constructor.
	 *
	 * @param array $webhook_data
	 */
	public function __construct( array $webhook_data ) { $this->webhook_data = $webhook_data; }

	/**
	 * Get webhook data.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_webhook_data() {
		return $this->webhook_data;
	}

	/**
	 * Get the raw post data.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_raw_post_data() {

		if ( ! isset( $HTTP_RAW_POST_DATA ) ) {
			$HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
		}

		return $HTTP_RAW_POST_DATA;
	}

	/**
	 * @inheritDoc
	 */
	public function get_customer() { return null; }

	/**
	 * @inheritDoc
	 */
	public static function get_name() { return 'webhook'; }
}
