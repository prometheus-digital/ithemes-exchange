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

	/** @var array */
	private $headers;

	/**
	 * ITE_Webhook_Gateway_Request constructor.
	 *
	 * @param array $webhook_data
	 * @param array $headers
	 */
	public function __construct( array $webhook_data, array $headers = array() ) {
		$this->webhook_data = $webhook_data;
		$this->headers      = $headers;
	}

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
	 * Get a header value.
	 *
	 * @since 2.0.0
	 *
	 * @param string $header
	 *
	 * @return string|null
	 */
	public function get_header( $header ) {

		if ( isset( $this->headers[ 'HTTP_' . $header ] ) ) {
			return $this->headers[ 'HTTP_' . $header ];
		}

		return null;
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
