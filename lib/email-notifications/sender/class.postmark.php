<?php
/**
 * Contains the Postmark email sender.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Postmark_Sender
 */
class IT_Exchange_Email_Postmark_Sender implements IT_Exchange_Email_Sender {

	const URL = 'https://api.postmarkapp.com/';

	/**
	 * @var string
	 */
	private $server_token;

	/**
	 * @var IT_Exchange_Email_Middleware_Handler
	 */
	private $middleware;

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @var WP_Http
	 */
	private $http;

	/**
	 * IT_Exchange_Email_Postmark_Sender constructor.
	 *
	 * @param IT_Exchange_Email_Middleware_Handler $middleware
	 * @param WP_Http                              $http
	 * @param array                                $config Additional configuration options.
	 */
	public function __construct( IT_Exchange_Email_Middleware_Handler $middleware, WP_Http $http, array $config = array() ) {
		$this->middleware = $middleware;
		$this->http       = $http;

		$config = ITUtility::merge_defaults( $config, array(
			'server-token' => '',
			'TrackOpens'   => true,
		) );

		$this->server_token = $config['server-token'];

		unset( $config['server-token'] );

		$this->config = $config;
	}

	/**
	 * Send the email.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Sendable $email
	 *
	 * @return bool
	 * @throws IT_Exchange_Email_Delivery_Exception
	 */
	public function send( IT_Exchange_Sendable $email ) {
		return $this->make_api_request( 'email', $this->convert_sendable_to_api_format( $email ), $email );
	}

	/**
	 * Bulk send emails.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Sendable[] $emails
	 *
	 * @return bool
	 * @throws InvalidArgumentException If a given email does not implement IT_Exchange_Sendable
	 * @throws IT_Exchange_Email_Delivery_Exception
	 */
	public function bulk_send( array $emails ) {

		$data = array();

		foreach ( $emails as $email ) {
			if ( ! $email instanceof IT_Exchange_Sendable ) {
				throw new InvalidArgumentException( '$email must implement IT_Exchange_Sendable interface.' );
			}

			$data[] = $this->convert_sendable_to_api_format( $email );
		}

		return $this->make_api_request( 'email/batch', $data, reset( $emails ) );
	}

	/**
	 * Convert a sendable object to the API format.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Sendable $sendable
	 *
	 * @return array
	 * @throws IT_Exchange_Email_Delivery_Exception
	 */
	protected function convert_sendable_to_api_format( IT_Exchange_Sendable $sendable ) {

		if ( count( $sendable->get_ccs() ) > 50 ) {
			throw new IT_Exchange_Email_Delivery_Exception( 'A maximum of 50 Cc addresses are supported.' );
		}

		if ( count( $sendable->get_bccs() ) > 50 ) {
			throw new IT_Exchange_Email_Delivery_Exception( 'A maximum of 50 Bcc addresses are supported.' );
		}

		$sendable = new IT_Exchange_Sendable_Mutable_Wrapper( $sendable );
		$this->middleware->handle( $sendable );

		$html = $sendable->get_template()->get_html( array_merge( array( 'message' => $sendable->get_body() ), $sendable->get_context() ) );

		$api_format = array(
			'From'     => $this->get_from_address(),
			'To'       => $sendable->get_recipient()->get_email(),
			'Subject'  => $sendable->get_subject(),
			'HtmlBody' => $html
		);

		if ( $sendable->get_ccs() ) {
			$api_format['Cc'] = implode( ',', array_map( array( $this, '_map_address' ), $sendable->get_ccs() ) );
		}

		if ( $sendable->get_bccs() ) {
			$api_format['Bcc'] = implode( ',', array_map( array( $this, '_map_address' ), $sendable->get_bccs() ) );
		}

		if ( $sendable->get_original() instanceof IT_Exchange_Email ) {
			$api_format['Tag'] = $sendable->get_original()->get_notification()->get_name();
		}

		$api_format = array_merge( $api_format, $this->config );

		/**
		 * Filter the email data after it is prepared for Postmark.
		 *
		 * @since 2.0.0
		 *
		 * @param array                $api_format
		 * @param IT_Exchange_Sendable $sendable
		 */
		$api_format = apply_filters( 'it_exchange_send_email_notification_postmark_api_format', $api_format, $sendable );

		return $api_format;
	}

	/**
	 * Make an API request to Postmark.
	 *
	 * @since 2.0.0
	 *
	 * @param string               $endpoint
	 * @param array                $data
	 * @param IT_Exchange_Sendable $sendable Mainly used to provide extra context to the delivery exception.
	 *
	 * @return bool
	 * @throws IT_Exchange_Email_Delivery_Exception
	 */
	protected function make_api_request( $endpoint, $data, IT_Exchange_Sendable $sendable = null ) {

		$headers = array(
			'Content-Type'            => 'application/json',
			'Accept'                  => 'application/json',
			'X-Postmark-Server-Token' => $this->server_token
		);

		$response = $this->http->post( self::URL . $endpoint, array(
			'reject_unsafe_urls' => true,
			'headers'            => $headers,
			'body'               => wp_json_encode( $data )
		) );

		if ( is_wp_error( $response ) ) {
			throw new IT_Exchange_Email_Delivery_Exception( $response->get_error_message(), $sendable );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$body = json_decode( $body, true );

		switch ( $code ) {
			case 200:
				return true;
			case 401:
				throw new IT_Exchange_Email_Delivery_Exception( 'Invalid Postmark server token provided.', $sendable );
			case 422:

				$message = '';

				if ( ! empty( $body['ErrorCode'] ) ) {
					$message = $body['ErrorCode'];
				}

				if ( ! empty( $body['Message'] ) ) {
					if ( empty( $message ) ) {
						$message = $body['Message'];
					} else {
						$message .= ": {$body['Message']}";
					}
				}

				if ( ! empty( $message ) ) {
					throw new IT_Exchange_Email_Delivery_Exception( $message, $sendable );
				}

				throw new IT_Exchange_Email_Delivery_Exception( 'Invalid data sent to Postmark.', $sendable );
			case 500:
			case 503:
				throw new IT_Exchange_Email_Delivery_Exception( 'A temporary problem occurred with Postmark\'s servers. Try again later.' );
		}

		return ( ! empty( $body['Message'] ) && $body['Message'] === 'Ok' );
	}

	/**
	 * Map the address from the recipient object.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Email_Recipient $recipient
	 *
	 * @return string
	 */
	public function _map_address( IT_Exchange_Email_Recipient $recipient ) {
		return $recipient->get_email();
	}

	/**
	 * Get the from email address.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_from_address() {

		$settings = it_exchange_get_option( 'settings_email' );

		// Edge case where sale is made before admin visits email settings.
		if ( empty( $settings['receipt-email-name'] ) && ! isset( $GLOBALS['IT_Exchange_Admin'] ) ) {

			include_once( IT_Exchange::$dir . 'lib/admin/class.admin.php' );

			add_filter( 'it_storage_get_defaults_exchange_settings_email', array(
				'IT_Exchange_Admin',
				'set_email_settings_defaults'
			) );

			$settings = it_exchange_get_option( 'settings_email', true );
		}

		return $settings['receipt-email-address'];
	}

}
