<?php
/**
 * Contains the class or the email notifications object
 * @since   0.4.0
 * @package IT_Exchange
 */

/**
 * The IT_Exchange_Email_Notifications class is for sending out email notification using wp_mail()
 *
 * @since 0.4.0
 */
class IT_Exchange_Email_Notifications {

	public $transaction_id;
	public $customer_id;
	public $user;

	/**
	 * @var IT_Exchange_Email_Sender
	 */
	private $sender;

	/**
	 * @var IT_Exchange_Email_Tag_Replacer
	 */
	private $replacer;

	/**
	 * @var IT_Exchange_Email_Notification[]
	 */
	private $notifications = array();

	/**
	 * Constructor. Sets up the class
	 *
	 * @since 0.4.0
	 *
	 * @param IT_Exchange_Email_Sender       $sender
	 * @param IT_Exchange_Email_Tag_Replacer $replacer
	 */
	function __construct( IT_Exchange_Email_Sender $sender = null, IT_Exchange_Email_Tag_Replacer $replacer = null ) {

		$this->replacer = $replacer ? $replacer : new IT_Exchange_Email_Shortcode_Tag_Replacer();
		$this->sender   = $sender ? $sender : new IT_Exchange_WP_Mail_Sender( $this->replacer );

		add_action( 'it_exchange_send_email_notification', array(
			$this,
			'it_exchange_send_email_notification'
		), 20, 4 );

		// Send emails on successfull transaction
		add_action( 'it_exchange_add_transaction_success', array( $this, 'send_purchase_emails' ), 20 );

		// Send emails when admin requests a resend
		add_action( 'admin_init', array( $this, 'handle_resend_confirmation_email_requests' ) );

		// Resends email notifications when status is changed from one that's not cleared for delivery to one that is cleared
		add_action( 'it_exchange_update_transaction_status', array(
			$this,
			'resend_if_transaction_status_gets_cleared_for_delivery'
		), 10, 3 );
	}

	/**
	 * Register a notification.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Notification $notification
	 *
	 * @return self
	 */
	public function register_notification( IT_Exchange_Email_Notification $notification ) {
		$this->notifications[ $notification->get_slug() ] = $notification;

		return $this;
	}

	/**
	 * Get a notification.
	 *
	 * @since 1.36
	 *
	 * @param string $slug
	 *
	 * @return IT_Exchange_Email_Notification|null
	 */
	public function get_notification( $slug ) {
		return isset( $this->notifications[ $slug ] ) ? $this->notifications[ $slug ] : null;
	}

	/**
	 * Retrieve all registered notifications.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Email_Notification[]
	 */
	public function get_notifications() {
		return $this->notifications;
	}

	/**
	 * Get the sender.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Email_Sender
	 */
	public function get_sender() {
		return $this->sender;
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Exchange_Email_Notifications() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Send an email notification.
	 *
	 * @since 1.36
	 *
	 * @param int    $customer_id
	 * @param string $subject
	 * @param string $content
	 * @param bool   $transaction_id
	 */
	function it_exchange_send_email_notification( $customer_id, $subject, $content, $transaction_id = false ) {

		$transaction_id = apply_filters( 'it_exchange_send_email_notification_transaction_id', $transaction_id );
		$transaction    = $transaction_id ? it_exchange_get_transaction( $transaction_id ) : null;
		$customer       = it_exchange_get_customer( $customer_id );

		$recipient    = new IT_Exchange_Email_Recipient_Customer( $customer );
		$notification = new IT_Exchange_Customer_Email_Notification( 'custom', 'Custom', new IT_Exchange_Email_Template( null ), array(
			'subject' => $subject,
			'body'    => $content
		) );

		$email = new IT_Exchange_Email( $recipient, $notification, array(
			'customer'    => $customer,
			'transaction' => $transaction
		) );

		$this->sender->send( $email );
	}

	/**
	 * Listens for the resend email request and passes along to send_purchase_emails
	 *
	 * @since 0.4.0
	 *
	 * @return void
	 */
	function handle_resend_confirmation_email_requests() {
		// Abort if not requested
		if ( empty( $_GET['it-exchange-customer-transaction-action'] ) || $_GET['it-exchange-customer-transaction-action'] != 'resend' ) {
			return;
		}

		// Abort if no transaction or invalid transaction was passed
		$transaction = it_exchange_get_transaction( $_GET['id'] );
		if ( empty( $transaction->ID ) ) {
			it_exchange_add_message( 'error', __( 'Invalid transaction. Confirmation email not sent.', 'it-l10n-ithemes-exchange' ) );
			$url = remove_query_arg( array( 'it-exchange-customer-transaction-action', '_wpnonce' ) );
			it_exchange_redirect( $url, 'admin-confirmation-email-resend-failed' );
			die();
		}

		// Abort if nonce is bad
		$nonce = empty( $_GET['_wpnonce'] ) ? false : $_GET['_wpnonce'];
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'it-exchange-resend-confirmation-' . $transaction->ID ) ) {
			it_exchange_add_message( 'error', __( 'Confirmation Email not sent. Please try again.', 'it-l10n-ithemes-exchange' ) );
			$url = remove_query_arg( array( 'it-exchange-customer-transaction-action', '_wpnonce' ) );
			it_exchange_redirect( $url, 'admin-confirmation-email-resend-failed' );
			die();
		}

		// Abort if user doesn't have permission
		if ( ! current_user_can( 'administrator' ) ) {
			it_exchange_add_message( 'error', __( 'You do not have permission to resend confirmation emails.', 'it-l10n-ithemes-exchange' ) );
			$url = remove_query_arg( array( 'it-exchange-customer-transaction-action', '_wpnonce' ) );
			it_exchange_redirect( $url, 'admin-confirmation-email-resend-failed' );
			die();
		}

		// Resend w/o admin notification
		$this->send_purchase_emails( $transaction, false );
		it_exchange_add_message( 'notice', __( 'Confirmation email resent', 'it-l10n-ithemes-exchange' ) );
		$url = remove_query_arg( array( 'it-exchange-customer-transaction-action', '_wpnonce' ) );
		it_exchange_redirect( $url, 'admin-confirmation-email-resend-success' );
		die();
	}

	/**
	 * Process the transaction and send appropriate emails
	 *
	 * @since 0.4.0
	 *
	 * @param mixed $transaction ID or object
	 * @param bool  $send_admin_email
	 *
	 * @return void
	 */
	function send_purchase_emails( $transaction, $send_admin_email = true ) {

		$transaction = it_exchange_get_transaction( $transaction );

		if ( empty( $transaction->ID ) ) {
			return;
		}

		/**
		 * Determine whether purchase emails should be sent to a customer.
		 *
		 * @since 1.29.0
		 *
		 * @param bool                            $send
		 * @param IT_Exchange_Email_Notifications $this
		 */
		if ( apply_filters( 'it_exchange_send_purchase_email_to_customer', true, $this ) ) {

			$notification = $this->get_notification( 'receipt' );

			if ( $notification && $notification->is_active() ) {
				$recipient = new IT_Exchange_Email_Recipient_Transaction( $transaction );

				$email = new IT_Exchange_Email( $recipient, $notification, array(
					'transaction' => $transaction,
					'customer'    => it_exchange_get_transaction_customer( $transaction )
				) );
				$this->sender->send( $email );
			}
		}

		/**
		 * Determine whether purchase emails should be sent to the site owner.
		 *
		 * @since 1.29.0
		 *
		 * @param bool                            $send_admin_email
		 * @param IT_Exchange_Email_Notifications $this
		 */
		$send_admin_email = apply_filters( 'it_exchange_send_purchase_email_to_admin', $send_admin_email, $this );

		// Send admin notification if param is true and email is provided in settings
		if ( $send_admin_email && ! empty( $settings['notification-email-address'] ) ) {

			$notification = $this->get_notification( 'admin-order' );

			if ( $notification && $notification->is_active() ) {

				foreach ( $notification->get_emails() as $email ) {
					$recipient = new IT_Exchange_Email_Recipient_Email( $email );

					$email = new IT_Exchange_Email( $recipient, $notification, array(
						'transaction' => $transaction,
						'customer'    => it_exchange_get_transaction_customer( $transaction )
					) );
					$this->sender->send( $email );
				}
			}
		}
	}

	/**
	 * Resends the email to the customer if the transaction status was changed from not cleared for delivery to cleared.
	 *
	 * @since 0.4.11
	 *
	 * @param object  $transaction        the transaction object
	 * @param string  $old_status         the status it was just changed from
	 * @param boolean $old_status_cleared was the old status cleared for delivery?
	 *
	 * @return void
	 */
	function resend_if_transaction_status_gets_cleared_for_delivery( $transaction, $old_status, $old_status_cleared ) {
		// Using ->ID here so that get_transaction forces a reload and doesn't use the old object with the old status
		$new_status         = it_exchange_get_transaction_status( $transaction->ID );
		$new_status_cleared = it_exchange_transaction_is_cleared_for_delivery( $transaction->ID );

		if ( ( $new_status != $old_status ) && ! $old_status_cleared && $new_status_cleared ) {
			$this->send_purchase_emails( $transaction, false );
		}
	}

	/**
	 * Returns Email HTML header
	 *
	 * @since 0.4.0
	 *
	 * @return string HTML header
	 */
	function body_header() {
		$data = empty( $GLOBALS['it_exchange']['email-confirmation-data'] ) ? false : $GLOBALS['it_exchange']['email-confirmation-data'];
		ob_start();
		?>
		<html>
		<head>
			<meta http-equiv="Content-type" content="text/html; charset=utf-8">
		</head>
		<body>
		<?php

		$output = ob_get_clean();

		return apply_filters( 'it_exchange_email_notification_body_header', $output, $data );

	}

	/**
	 * Returns Email HTML footer
	 *
	 * @since 0.4.0
	 *
	 * @return string HTML footer
	 */
	function body_footer() {
		$data = empty( $GLOBALS['it_exchange']['email-confirmation-data'] ) ? false : $GLOBALS['it_exchange']['email-confirmation-data'];
		ob_start();
		?>
		</body>
		</html>
		<?php

		$output = ob_get_clean();

		return apply_filters( 'it_exchange_email_notification_body_footer', $output, $data );

	}

	/**
	 * Back-compat magic method.
	 *
	 * @since 1.36
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return bool|mixed
	 */
	public function __call( $name, $arguments ) {

		if ( method_exists( $this->replacer, $name ) ) {

			$new_name = str_replace( 'it_exchange_', '', $name );

			_deprecated_function( __CLASS__ . '::' . $name, '1.36', "IT_Exchange_Email_Tag_Replacer::$new_name" );

			/** @var IT_Exchange_Email_Notifications $notifications */
			$notifications = $arguments[0];

			$context = array(
				'transaction' => it_exchange_get_transaction( $notifications->transaction_id ),
				'customer'    => $notifications->user
			);

			$arguments[0] = $context;

			return call_user_func_array( array( $this->replacer, $new_name ), $arguments );
		}

		return false;
	}
}
