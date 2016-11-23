<?php
/**
 * WP Mail sender.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_WP_Mail_Sender
 */
class IT_Exchange_WP_Mail_Sender implements IT_Exchange_Email_Sender {

	/**
	 * @var IT_Exchange_Sendable
	 */
	private $email;

	/**
	 * @var IT_Exchange_Email_Tag_Replacer
	 */
	private $middleware;

	/**
	 * IT_Exchange_WP_Mail_Sender constructor.
	 *
	 * @param IT_Exchange_Email_Middleware_Handler $middleware
	 */
	public function __construct( IT_Exchange_Email_Middleware_Handler $middleware ) {
		$this->middleware = $middleware;
	}

	/**
	 * Send the email.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Sendable $email
	 *
	 * @return bool
	 * @throws IT_Exchange_Email_Delivery_Exception By proxy.
	 */
	public function send( IT_Exchange_Sendable $email ) {

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

		$headers = array();

		$headers[] = 'From: ' . $settings['receipt-email-name'] . ' <' . $settings['receipt-email-address'] . '>';
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-Type: text/html';
		$headers[] = 'charset=utf-8';

		foreach ( $email->get_ccs() as $cc ) {
			$headers[] = $this->header_from_recipient( $cc, 'Cc' );
		}

		foreach ( $email->get_bccs() as $bcc ) {
			$headers[] = $this->header_from_recipient( $bcc, 'Bcc' );
		}

		$email = new IT_Exchange_Sendable_Mutable_Wrapper( $email );
		$this->middleware->handle( $email );

		$to      = $email->get_recipient()->get_email();
		$subject = $email->get_subject();
		$message = $email->get_body();
		$body    = $email->get_template()->get_html( array_merge( array( 'message' => $message ), $email->get_context() ) );

		$headers = apply_filters( 'it_exchange_send_email_notification_wp_mail_headers', $headers );
		$body    = apply_filters( 'it_exchange_send_email_notification_wp_mail_body', $body );

		$this->email = $email;
		add_action( 'wp_mail_failed', array( $this, 'wp_mail_failed' ) );
		$res = wp_mail( $to, strip_tags( $subject ), $body, $headers );
		remove_action( 'wp_mail_failed', array( $this, 'wp_mail_failed' ) );

		return $res;
	}

	/**
	 * Bulk send emails.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Sendable[] $emails
	 *
	 * @return bool
	 */
	public function bulk_send( array $emails ) {

		$ret = true;

		foreach ( $emails as $email ) {
			if ( ! $email instanceof IT_Exchange_Sendable ) {
				throw new InvalidArgumentException( '$email must implement IT_Exchange_Sendable interface.' );
			}

			$ret = $ret && $this->send( $email );
		}

		return $ret;
	}

	/**
	 * Generate a header from a recipient.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Email_Recipient $recipient
	 * @param string                      $type Either Cc or Bcc
	 *
	 * @return string
	 */
	protected function header_from_recipient( IT_Exchange_Email_Recipient $recipient, $type = '' ) {

		$header = "$type: ";

		if ( $recipient->get_full_name() ) {
			$header .= "{$recipient->get_full_name()} <{$recipient->get_email()}>";
		} else {
			$header .= $recipient->get_email();
		}

		return $header;
	}

	/**
	 * Fires whenever wp mail fails.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Error $error
	 *
	 * @throws IT_Exchange_Email_Delivery_Exception
	 */
	public function wp_mail_failed( $error ) {

		remove_action( 'wp_mail_failed', array( $this, 'wp_mail_failed' ) );

		throw new IT_Exchange_Email_Delivery_Exception( $error->get_error_message(), $this->email );
	}
}
