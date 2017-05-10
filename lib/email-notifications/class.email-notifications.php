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
class IT_Exchange_Email_Notifications implements IT_Exchange_Email_Sender_Aware {

	/** @deprecated 2.0.0 */
	public $transaction_id;

	/** @deprecated 2.0.0 */
	public $customer_id;

	/** @deprecated 2.0.0 */
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
	public function __construct( IT_Exchange_Email_Sender $sender = null, IT_Exchange_Email_Tag_Replacer $replacer = null ) {

		$this->replacer = $replacer ?: new IT_Exchange_Email_Curly_Tag_Replacer();
		$this->sender   = $sender ?: new IT_Exchange_Email_Null_Sender();

		add_action( 'it_exchange_send_email_notification', array( $this, 'it_exchange_send_email_notification' ), 20, 4 );

		// Send emails on successfull transaction
		add_action( 'it_exchange_add_transaction_success', array( $this, 'send_purchase_emails' ), 20 );
		add_action( 'it_exchange_add_child_transaction_success', array( $this, 'on_child_transaction' ), 20 );

		// Send emails when admin requests a resend
		add_action( 'admin_init', array( $this, 'handle_resend_confirmation_email_requests' ) );

		// Resends email notifications when status is changed from one that's not cleared for delivery to one that is cleared
		add_action( 'it_exchange_update_transaction_status', array(	$this, 'resend_if_transaction_status_gets_cleared_for_delivery'	), 10, 3 );
	}

	/**
	 * Register a notification.
	 *
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * @since 2.0.0
	 *
	 * @return IT_Exchange_Email_Notification[]
	 */
	public function get_notifications() {
		return $this->notifications;
	}

	/**
	 * Get all the email notification groups.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_groups() {

		$groups = array();

		foreach ( $this->get_notifications() as $notification ) {
			$groups[ $notification->get_group() ] = $notification->get_group();
		}

		$groups = array_filter( $groups );
		natcasesort( $groups );

		return array_values( $groups );
	}

	/**
	 * Get the sender.
	 *
	 * @since 2.0.0
	 *
	 * @return IT_Exchange_Email_Sender
	 */
	public function get_sender() {
		return $this->sender;
	}

	/**
	 * Set the email sender to be used.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Email_Sender $sender
	 */
	public function set_sender( IT_Exchange_Email_Sender $sender ) {
		$this->sender = $sender;
	}

	/**
	 * Get the tag replacer.
	 *
	 * @since 2.0.0
	 *
	 * @return IT_Exchange_Email_Tag_Replacer
	 */
	public function get_replacer() {
		return $this->replacer;
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
	 * @since 2.0.0
	 *
	 * @param int    $customer_id
	 * @param string $subject
	 * @param string $content
	 * @param bool   $transaction_id
	 */
	public function it_exchange_send_email_notification( $customer_id, $subject, $content, $transaction_id = false ) {

		$transaction_id = apply_filters( 'it_exchange_send_email_notification_transaction_id', $transaction_id );
		$transaction    = $transaction_id ? it_exchange_get_transaction( $transaction_id ) : null;
		$customer       = it_exchange_get_customer( $customer_id );

		$recipient = new IT_Exchange_Email_Recipient_Customer( $customer );

		$email = new IT_Exchange_Simple_Email( $subject, $content, $recipient, array(
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
	public function handle_resend_confirmation_email_requests() {
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

		$url = remove_query_arg( array( 'it-exchange-customer-transaction-action', '_wpnonce' ) );

		// Resend w/o admin notification
		$sent = $this->send_purchase_emails( $transaction, false );

		if ( $sent ) {
			it_exchange_add_message( 'notice', __( 'Confirmation email resent', 'it-l10n-ithemes-exchange' ) );
			it_exchange_redirect( $url, 'admin-confirmation-email-resend-success' );
		} else {
			it_exchange_log( 'Failed to resend transaction #{txn_id} receipt.', array(
				'txn_id' => $transaction->ID,
				'_group' => 'email',
			) );
			it_exchange_redirect( $url, 'admin-confirmation-email-resend-failed' );
		}
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
	 * @return bool
	 */
	public function send_purchase_emails( $transaction, $send_admin_email = true ) {

		$transaction = it_exchange_get_transaction( $transaction );

		if ( ! $transaction ) {
			return false;
		}

		$r = true;

		/**
		 * Determine whether purchase emails should be sent to a customer.
		 *
		 * @since 1.29.0
		 *
		 * @param bool                            $send
		 * @param IT_Exchange_Email_Notifications $this
		 */
		if ( apply_filters( 'it_exchange_send_purchase_email_to_customer', true, $this ) ) {

			$notification = $this->get_notification( $transaction->has_parent() ? 'renewal-receipt' : 'receipt' );

			if ( $notification && $notification->is_active() ) {
				$recipient = new IT_Exchange_Email_Recipient_Transaction( $transaction );

				$email = new IT_Exchange_Email( $recipient, $notification, array(
					'transaction' => $transaction,
					'customer'    => $transaction->get_customer()
				) );

				$r = $r && it_exchange_send_email( $email );

				if ( $r ) {
					it_exchange_log( 'Sent receipt for transaction #{txn_id} to customer.', ITE_Log_Levels::DEBUG, array(
						'txn_id' => $transaction->ID,
						'_group' => 'email',
					) );
				}
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
		if ( $send_admin_email ) {

			$notification = $this->get_notification( 'admin-order' );

			if ( $notification && $notification->is_active() ) {

				foreach ( $notification->get_emails() as $email ) {
					$recipient = new IT_Exchange_Email_Recipient_Email( $email );

					$email = new IT_Exchange_Email( $recipient, $notification, array(
						'transaction' => $transaction,
						'customer'    => $transaction->get_customer()
					) );

					$r = $r && it_exchange_send_email( $email );

					if ( $r ) {
						it_exchange_log( 'Sent receipt for transaction #{txn_id} to admin {email}.', ITE_Log_Levels::DEBUG, array(
							'txn_id' => $transaction->ID,
							'email'  => $email,
							'_group' => 'email',
						) );
					}
				}
			}
		}

		return $r;
	}

	/**
     * Send a receipt to the customer when a renewal payment is made.
     *
     * @since 2.0.0
     *
	 * @param int $transaction_id
	 */
	public function on_child_transaction( $transaction_id ) {
	    $this->send_purchase_emails( $transaction_id, false );
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
	public function resend_if_transaction_status_gets_cleared_for_delivery( $transaction, $old_status, $old_status_cleared ) {
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
	 * @since      0.4.0
	 *
	 * @deprecated 2.0.0
	 *
	 * @return string HTML header
	 */
	public function body_header() {

		_deprecated_function( __METHOD__, '2.0.0' );

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
	 * @since      0.4.0
	 *
	 * @deprecated 2.0.0
	 *
	 * @return string HTML footer
	 */
	public function body_footer() {

		_deprecated_function( __METHOD__, '2.0.0' );

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
	 * @since 2.0.0
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return bool|mixed
	 */
	public function __call( $name, $arguments ) {

		if ( method_exists( $this->replacer, $name ) ) {

			$new_name = str_replace( 'it_exchange_replace_', '', $name );
			$new_name = str_replace( '_tag', '', $new_name );

			_deprecated_function( __CLASS__ . '::' . $name, '2.0.0', "IT_Exchange_Email_Tag_Replacer::$new_name" );

			/** @var IT_Exchange_Email_Notifications $notifications */
			$notifications = $arguments[0];

			$context = array(
				'transaction' => it_exchange_get_transaction( $notifications->transaction_id ),
				'customer'    => $notifications->user
			);

			$arguments = array_pad( $arguments, 3, array() );

			$arguments[0] = $context;
			$arguments[1] = array_merge( $arguments[1], $arguments[2] );

			return call_user_func_array( array( IT_Exchange_Email_Register_Default_Tags::get_instance(), $new_name ), $arguments );
		}

		return false;
	}
}
