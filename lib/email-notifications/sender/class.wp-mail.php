<?php
/**
 * WP Mail sender.
 *
 * @since   1.36
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
	private $replacer;

	/**
	 * IT_Exchange_WP_Mail_Sender constructor.
	 *
	 * @param IT_Exchange_Email_Tag_Replacer $replacer
	 */
	public function __construct( IT_Exchange_Email_Tag_Replacer $replacer ) {
		$this->replacer = $replacer;
	}

	/**
	 * Send the email.
	 *
	 * @since 1.36
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

		$this->email = $email;
		$context     = array_merge( array( 'recipient' => $email->get_recipient() ), $email->get_context() );
		$content     = $email->get_body();

		$headers = apply_filters( 'it_exchange_send_email_notification_wp_mail_headers', $headers );
		$content = apply_filters( 'it_exchange_send_email_notification_wp_mail_body', $content );

		$to      = $email->get_recipient()->get_email();
		$subject = $this->replacer->replace( $email->get_subject(), $context );
		$message = $this->replacer->replace( shortcode_unautop( wpautop( $content ) ), $context );
		$body    = $email->get_template()->get_html( array_merge( array( 'message' => $message ), $context ) );

		add_action( 'wp_mail_failed', array( $this, 'wp_mail_failed' ) );
		$res = wp_mail( $to, strip_tags( $subject ), $body, $headers );
		remove_action( 'wp_mail_failed', array( $this, 'wp_mail_failed' ) );

		return $res;
	}

	/**
	 * Bulk send emails.
	 *
	 * @since 1.36
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
	 * @since 1.36
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
	 * @since 1.36
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