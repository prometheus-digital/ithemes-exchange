<?php
/**
 * Contains the Mailjet sender class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Mailjet_Sender
 */
class IT_Exchange_Email_Mailjet_Sender implements IT_Exchange_Email_Sender {

	const URL = 'https://api.mailjet.com/v3/';

	/**
	 * @var string
	 */
	private $public_key = '';

	/**
	 * @var string
	 */
	private $private_key = '';

	/**
	 * @var IT_Exchange_Email_Tag_Replacer
	 */
	private $replacer;

	/**
	 * @var WP_Http
	 */
	private $http;

	/**
	 * @var array
	 */
	private $config = array();

	/**
	 * IT_Exchange_Email_Mailjet_Sender constructor.
	 *
	 * @param IT_Exchange_Email_Tag_Replacer $replacer
	 * @param WP_Http                        $http
	 * @param array                          $config
	 */
	public function __construct( IT_Exchange_Email_Tag_Replacer $replacer, WP_Http $http, array $config = array() ) {
		$this->replacer = $replacer;
		$this->http     = $http;

		$config = ITUtility::merge_defaults( $config, array(
			'public'       => '',
			'private'      => '',
			'Mj-trackopen' => true,
		) );

		$this->public_key  = $config['public'];
		$this->private_key = $config['private'];

		unset( $config['public'], $config['private'] );

		$this->config = $config;
	}

	/**
	 * Send the email.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Sendable $email
	 *
	 * @return bool
	 * @throws IT_Exchange_Email_Delivery_Exception
	 */
	public function send( IT_Exchange_Sendable $email ) {
		return $this->make_api_request( $this->convert_sendable_to_api_format( $email ), $email );
	}

	/**
	 * Bulk send emails.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Sendable[] $emails
	 *
	 * @return bool
	 * @throws InvalidArgumentException If a given email does not implement IT_Exchange_Sendable
	 * @throws IT_Exchange_Email_Delivery_Exception
	 */
	public function bulk_send( array $emails ) {

		$messages = array();

		foreach ( $emails as $email ) {
			if ( ! $email instanceof IT_Exchange_Sendable ) {
				throw new InvalidArgumentException( '$email must implement IT_Exchange_Sendable interface.' );
			}

			$messages[] = $this->convert_sendable_to_api_format( $email );
		}

		$data = array(
			'Messages' => $messages
		);

		return $this->make_api_request( $data, reset( $emails ) );
	}

	/**
	 * Convert a sendable object to the API format.
	 *
	 * @since 1.36
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

		$context = array_merge( array( 'recipient' => $sendable->get_recipient() ), $sendable->get_context() );

		$message = $this->replacer->replace( shortcode_unautop( wpautop( $sendable->get_body() ) ), $context );
		$html    = $sendable->get_template()->get_html( array_merge( array( 'message' => $message ), $context ) );

		$api_format = array(
			'FromEmail' => $this->get_from_address(),
			'To'        => $sendable->get_recipient()->get_email(),
			'Subject'   => $this->replacer->replace( $sendable->get_subject(), $context ),
			'Html-part' => $html
		);

		if ( $sendable->get_ccs() ) {
			$api_format['Cc'] = implode( ',', array_map( array( $this, '_map_address' ), $sendable->get_ccs() ) );
		}

		if ( $sendable->get_bccs() ) {
			$api_format['Bcc'] = implode( ',', array_map( array( $this, '_map_address' ), $sendable->get_bccs() ) );
		}

		$api_format = array_merge( $api_format, $this->config );

		/**
		 * Filter the email data after it is prepared for Mailjet.
		 *
		 * @since 1.36
		 *
		 * @param array                $api_format
		 * @param IT_Exchange_Sendable $sendable
		 */
		$api_format = apply_filters( 'it_exchange_send_email_notification_mailjet_api_format', $api_format, $sendable );

		return $api_format;
	}

	/**
	 * Perform an API request.
	 *
	 * @since 1.36
	 *
	 * @param array                     $data
	 * @param IT_Exchange_Sendable|null $sendable
	 *
	 * @return bool
	 * @throws IT_Exchange_Email_Delivery_Exception
	 */
	protected function make_api_request( $data, IT_Exchange_Sendable $sendable = null ) {

		$headers = array(
			'Content-type'  => 'application/json',
			'Authorization' => 'Basic ' . base64_encode( "{$this->public_key}:{$this->private_key}" )
		);

		$response = $this->http->post( self::URL . 'send', array(
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
			case 201:
				return true;
			case 401:
			case 403:
				throw new IT_Exchange_Email_Delivery_Exception( 'Invalid Mailjet credentials.', $sendable );
			case 500:
				throw new IT_Exchange_Email_Delivery_Exception( 'A temporary problem occurred with Mailjet\'s servers. Try again later.', $sendable );
			default:

				$error_info    = empty( $body['ErrorInfo'] ) ? '' : $body['ErrorInfo'];
				$error_message = empty( $body['ErrorMessage'] ) ? '' : $body['ErrorMessage'] . '.';
				$error_code    = empty( $body['StatusCode'] ) ? '' : $body['StatusCode'] . ':';

				if ( empty( $error_info ) && empty( $error_message ) && empty( $error_code ) ) {
					throw new IT_Exchange_Email_Delivery_Exception( 'An unexpected error occurred with Mailjet.', $sendable );
				}

				throw new IT_Exchange_Email_Delivery_Exception( "$error_code:$error_info $error_message", $sendable );
		}
	}


	/**
	 * Convert a recipient to an address line.
	 *
	 * @since 1.36
	 *
	 * @internal
	 *
	 * @param IT_Exchange_Email_Recipient $recipient
	 *
	 * @return string
	 */
	public function _map_address( IT_Exchange_Email_Recipient $recipient ) {

		if ( $recipient->get_full_name() ) {
			return "{$recipient->get_full_name()} <{$recipient->get_email()}>";
		} else {
			return $recipient->get_email();
		}
	}

	/**
	 * Get the from email address.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	protected function get_from_address() {

		$settings = it_exchange_get_option( 'settings_email' );

		// Edge case where sale is made before admin visits email settings.
		if ( empty( $settings['receipt-email-address'] ) && ! isset( $GLOBALS['IT_Exchange_Admin'] ) ) {

			include_once( IT_Exchange::$dir . 'lib/admin/class.admin.php' );

			add_filter( 'it_storage_get_defaults_exchange_settings_email', array(
				'IT_Exchange_Admin',
				'set_email_settings_defaults'
			) );

			$settings = it_exchange_get_option( 'settings_email', true );
		}

		if ( empty( $settings['receipt-email-name'] ) ) {
			return $settings['receipt-email-address'];
		}

		return $settings['receipt-email-name'] . '<' . $settings['receipt-email-address'] . '>';
	}

}