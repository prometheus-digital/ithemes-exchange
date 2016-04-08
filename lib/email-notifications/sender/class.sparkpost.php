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

		$data = array(
			'options'    => $this->config,
			'recipients' => array(
				$this->build_recipient_attributes( $email )
			),
			'content'    => $this->build_inline_content_attributes( $email )
		);

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

		foreach ( $emails as $email ) {
			$recipients[] = $this->build_recipient_attributes( $email );
		}

		/** @var IT_Exchange_Sendable_Mutable_Wrapper $email */
		$email = reset( $emails );
		$email->override_body( $this->replacer->transform_tags_to_format( '{{{', '}}}', $email->get_body() ) );

		$data = array(
			'options'    => (object) $this->config,
			'recipients' => $recipients,
			'content'    => $this->build_inline_content_attributes( $email ) // we can use any email we want
		);

		return $this->make_api_request( $data );
	}

	/**
	 * Build recipient attributes from a sendable object.
	 *
	 * @sicne 1.36
	 *
	 * @param IT_Exchange_Sendable $sendable
	 *
	 * @return array
	 */
	protected function build_recipient_attributes( IT_Exchange_Sendable $sendable ) {

		$content = $sendable->get_subject() . $sendable->get_body();

		$substitutions = $this->replacer->get_replacement_map( $content, $sendable->get_context() );

		$attributes = array(
			'address' => (object) array(
				'email' => $sendable->get_recipient()->get_email(),
				'name'  => $sendable->get_recipient()->get_full_name()
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
	 *
	 * @return array
	 */
	protected function build_inline_content_attributes( IT_Exchange_Sendable $sendable ) {

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
}