<?php
/**
 * Contains the Sparkpost email sender.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_SparkPost_Sender
 */
class IT_Exchange_Email_SparkPost_Sender implements IT_Exchange_Email_Sender {

	const URL = 'https://api.sparkpost.com/api/v1/';

	/**
	 * @var string
	 */
	private $api_key;

	/**
	 * @var IT_Exchange_Email_Middleware_Handler
	 */
	private $middleware;

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
	 * IT_Exchange_Email_SparkPost_Sender constructor.
	 *
	 * @param IT_Exchange_Email_Middleware_Handler $middleware
	 * @param IT_Exchange_Email_Tag_Replacer       $replacer
	 * @param WP_Http                              $http
	 * @param array                                $config
	 */
	public function __construct( IT_Exchange_Email_Middleware_Handler $middleware, IT_Exchange_Email_Tag_Replacer $replacer, WP_Http $http, array $config ) {
		$this->middleware = $middleware;
		$this->replacer   = $replacer;
		$this->http       = $http;

		$config = ITUtility::merge_defaults( $config, array(
			'api-key'       => '',
			'open_tracking' => true,
			'transactional' => true
		) );

		$this->api_key = $config['api-key'];

		unset( $config['api-key'] );

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

		$email = new IT_Exchange_Sendable_Mutable_Wrapper( $email );

		if ( ! $this->middleware->handle( $email ) ) {
			return false;
		}

		$substitutions = $this->replacer->get_replacement_map( $email->get_subject() . $email->get_body(), $email->get_context() );

		$recipients   = array();
		$recipients[] = $this->build_recipient_attributes( $email->get_recipient(), $substitutions );

		$ccs = array();

		foreach ( $email->get_ccs() as $cc ) {
			$ccs[]        = $cc->get_email();
			$recipients[] = $this->build_recipient_attributes( $cc, $substitutions, $email->get_recipient()->get_email() );
		}

		foreach ( $email->get_bccs() as $bcc ) {
			$recipients[] = $this->build_recipient_attributes( $bcc, $substitutions, $email->get_recipient()->get_email() );
		}

		$headers = array();

		if ( ! empty( $ccs ) ) {
			$headers['CC'] = implode( ',', $ccs );
		}

		$data = array(
			'options'    => $this->get_config(),
			'recipients' => $recipients,
			'content'    => $this->build_inline_content_attributes( $email, $headers )
		);

		/**
		 * Filter the data passed to SparkPost for sending a single email.
		 *
		 * @since 1.36
		 *
		 * @param array                                $data
		 * @param IT_Exchange_Sendable_Mutable_Wrapper $email
		 */
		$data = apply_filters( 'it_exchange_send_email_notification_sparkpost_api_format_single', $data, $email );

		return $this->make_api_request( $data, $email );
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

		$individual_send = array();
		$groups          = array();

		foreach ( $emails as $email ) {

			// HTML templates likely have individualized content, so they must be sent individually
			if ( $email->get_template()->get_name() ) {
				$individual_send[] = $email;
			}

			$this->middleware->skip( 'replacer' );

			$email = new IT_Exchange_Sendable_Mutable_Wrapper( $email );

			if ( $this->middleware->handle( $email ) ) {
				$groups[ $email->get_subject() . $email->get_body() ][] = $email;
			}
		}

		foreach ( $individual_send as $send ) {
			$this->send( $send );
		}

		foreach ( $groups as $group ) {
			$this->send_group( $group );
		}

		return true;
	}

	/**
	 * Send a group of emails.
	 *
	 * All emails must be the same, and have all of the differentiating factors contained
	 * within the email tags, not the template HTML.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Sendable_Mutable_Wrapper[] $emails
	 *
	 * @return bool
	 * @throws IT_Exchange_Email_Delivery_Exception
	 */
	protected function send_group( array $emails ) {

		if ( empty( $emails ) ) {
			return false;
		}

		$recipients = array();
		$ccs        = array();

		foreach ( $emails as $email ) {

			$skip = false;

			$email_ccs        = array();
			$email_recipients = array();

			$substitutions      = $this->replacer->get_replacement_map( $email->get_subject() . $email->get_body(), $email->get_context() );
			$email_recipients[] = $this->build_recipient_attributes( $email->get_recipient(), $substitutions );

			foreach ( $email->get_ccs() as $cc ) {
				$email_ccs[]        = $cc->get_email();
				$email_recipients[] = $this->build_recipient_attributes( $cc, $substitutions, $email->get_recipient() );
			}

			foreach ( $email->get_bccs() as $bcc ) {
				if ( in_array( $bcc->get_email(), $email_ccs ) || in_array( $bcc->get_email(), $ccs ) ) {
					// if there is already a CC with this email address, we need to send this email separately
					$this->send( $email );

					$skip = true;
					break;
				} else {
					$email_recipients[] = $this->build_recipient_attributes( $bcc, $substitutions, $email->get_recipient() );
				}
			}

			if ( $skip ) {
				$this->send( $email );
			} else {
				$recipients = array_merge( $recipients, $email_recipients );
				$ccs        = array_merge( $ccs, $email_ccs );
			}
		}

		/** @var IT_Exchange_Sendable_Mutable_Wrapper $email */
		$email = reset( $emails );
		$email->override_body( $this->replacer->transform_tags_to_format( '{{{', '}}}', $email->get_body() ) );

		$headers = array();

		if ( ! empty( $ccs ) ) {
			$headers['CC'] = implode( ',', $ccs );
		}

		$data = array(
			'options'    => $this->get_config(),
			'recipients' => $recipients,
			'content'    => $this->build_inline_content_attributes( $email, $headers ) // we can use any email we want
		);

		/**
		 * Filter the data passed to SparkPost for group sending.
		 *
		 * @since 1.36
		 *
		 * @param array                                  $data
		 * @param IT_Exchange_Sendable_Mutable_Wrapper[] $emails
		 * @param IT_Exchange_Sendable_Mutable_Wrapper   $email
		 */
		$data = apply_filters( 'it_exchange_send_email_notification_sparkpost_api_format_group', $data, $emails, $email );

		return $this->make_api_request( $data );
	}

	/**
	 * Build recipient attributes from a sendable object.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Recipient $recipient
	 * @param array                       $substitutions
	 * @param string                      $header_to
	 *
	 * @return array
	 */
	protected function build_recipient_attributes( IT_Exchange_Email_Recipient $recipient, $substitutions = array(), $header_to = '' ) {

		$attributes = array(
			'address' => (object) array(
				'email'     => $recipient->get_email(),
				'name'      => $recipient->get_full_name(),
				'header_to' => $header_to
			)
		);

		if ( ! empty( $substitutions ) ) {
			$attributes['substitution_data'] = (object) $substitutions;
		}

		return $attributes;
	}

	/**
	 * Build inline content attributes from a sendable object.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Sendable $sendable
	 * @param array                $headers
	 *
	 * @return array
	 */
	protected function build_inline_content_attributes( IT_Exchange_Sendable $sendable, $headers = array() ) {

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

		$html = $sendable->get_template()->get_html( array_merge( array( 'message' => $sendable->get_body() ), $sendable->get_context() ) );

		return (object) array(
			'html'    => $html,
			'subject' => $sendable->get_subject(),
			'headers' => $headers,
			'from'    => (object) array(
				'name'  => $settings['receipt-email-name'],
				'email' => $settings['receipt-email-address']
			)
		);
	}

	/**
	 * Make an API request to SparkPost.
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
			'Content-Type'  => 'application/json',
			'Authorization' => $this->api_key,
		);

		$response = $this->http->post( self::URL . 'transmissions/', array(
			'reject_unsafe_urls' => true,
			'headers'            => $headers,
			'body'               => wp_json_encode( $data ),
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
			case 403:
				throw new IT_Exchange_Email_Delivery_Exception( 'Invalid SparkPost API credentials.', $sendable );
			case 429:
				throw new IT_Exchange_Email_Delivery_Exception( 'SparkPost API Rate Limit Reached.', $sendable );
			case 500:
				throw new IT_Exchange_Email_Delivery_Exception( 'An unexpected problem occured with SparkPost\'s servers. Try again later.' );
			default:

				$message = '';

				if ( isset( $body['errors'] ) ) {
					foreach ( $body['errors'] as $error ) {
						$message .= $error['code'] . ': ' . $error['message'] . '. ';
					}
				}

				throw new IT_Exchange_Email_Delivery_Exception( "Error from SparkPost: $message" );
		}
	}

	/**
	 * Get the configuration.
	 *
	 * @since 1.36
	 *
	 * @return stdClass
	 */
	protected function get_config() {

		/**
		 * Filter the SparkPost API Configuration.
		 *
		 * @since 1.36
		 *
		 * @param stdClass $config
		 */
		return apply_filters( 'it_exchange_send_email_notification_sparkpost_api_config', (object) $this->config );
	}
}