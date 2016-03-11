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
	 * @var IT_Exchange_Email
	 */
	private $email;

	/**
	 * Send the email.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email $email
	 *
	 * @return bool
	 * @throws IT_Exchange_Email_Delivery_Exception By proxy.
	 */
	public function send( IT_Exchange_Email $email ) {

		$settings = it_exchange_get_option( 'settings_email' );

		// Edge case where sale is made before admin visits email settings.
		if ( empty( $settings['receipt-email-name'] ) && ! isset( $IT_Exchange_Admin ) ) {

			include_once( dirname( dirname( __FILE__ ) ) . '/admin/class.admin.php' );

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

		$this->email  = $email;
		$notification = $email->get_notification();

		add_action( 'wp_mail_failed', array( $this, 'wp_mail_failed' ) );
		$res = wp_mail( $email->get_recipient()->get_email(), $notification->get_subject(), $notification->get_body(), $headers );
		remove_action( 'wp_mail_failed', array( $this, 'wp_mail_failed' ) );

		return $res;
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