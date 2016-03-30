<?php
/**
 * Load the email notifications component.
 *
 * @since   1.36
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/deprecated.php';


require_once dirname( __FILE__ ) . '/class.customizer.php';
require_once dirname( __FILE__ ) . '/class.customize-active-callback.php';

require_once dirname( __FILE__ ) . '/notifications/class.email-notification.php';
require_once dirname( __FILE__ ) . '/notifications/class.admin-email-notification.php';
require_once dirname( __FILE__ ) . '/notifications/class.customer-email-notification.php';

require_once dirname( __FILE__ ) . '/class.email-template.php';
require_once dirname( __FILE__ ) . '/sendable/interface.sender-aware.php';
require_once dirname( __FILE__ ) . '/sendable/interface.sendable.php';
require_once dirname( __FILE__ ) . '/sendable/class.email.php';
require_once dirname( __FILE__ ) . '/sendable/class.simple-email.php';

require_once dirname( __FILE__ ) . '/recipients/interface.email-recipient.php';
require_once dirname( __FILE__ ) . '/recipients/class.email-recipient-transaction.php';
require_once dirname( __FILE__ ) . '/recipients/class.email-recipient-customer.php';
require_once dirname( __FILE__ ) . '/recipients/class.email-recipient-email.php';

require_once dirname( __FILE__ ) . '/sender/interface.php';
require_once dirname( __FILE__ ) . '/sender/class.null.php';
require_once dirname( __FILE__ ) . '/sender/class.wp-mail.php';
require_once dirname( __FILE__ ) . '/sender/class.postmark.php';
require_once dirname( __FILE__ ) . '/sender/class.exception.php';

require_once dirname( __FILE__ ) . '/tag-replacers/interface.php';
require_once dirname( __FILE__ ) . '/tag-replacers/class.base.php';
require_once dirname( __FILE__ ) . '/tag-replacers/class.shortcode.php';

require_once dirname( __FILE__ ) . '/tag/interface.php';
require_once dirname( __FILE__ ) . '/tag/class.base.php';
require_once dirname( __FILE__ ) . '/tag/load.php';

require_once dirname( __FILE__ ) . '/class.email-notifications.php';

new IT_Exchange_Email_Customizer();

/**
 * Retrieve the email notifications object.
 *
 * @since 1.36
 *
 * @return IT_Exchange_Email_Notifications
 */
function it_exchange_email_notifications() {

	static $notifications = null;

	if ( ! $notifications ) {

		$replacer = new IT_Exchange_Email_Shortcode_Tag_Replacer();

		/**
		 * Filter the tag replacer.
		 *
		 * The return value must implement IT_Exchange_Email_Tag_Replacer.
		 *
		 * @since 1.36
		 *
		 * @param IT_Exchange_Email_Tag_Replacer $replacer
		 */
		$filtered = apply_filters( 'it_exchange_email_notifications_tag_replacer', $replacer );

		if ( $filtered instanceof IT_Exchange_Email_Tag_Replacer ) {
			$replacer = $filtered;
		}

		/**
		 * Fires when replacement tags should be registered.
		 *
		 * @since 1.36
		 *
		 * @param IT_Exchange_Email_Tag_Replacer $replacer
		 */
		do_action( 'it_exchange_email_notifications_register_tags', $replacer );

		if ( defined( 'IT_EXCHANGE_DISABLE_EMAILS' ) && IT_EXCHANGE_DISABLE_EMAILS ) {
			$sender = new IT_Exchange_Email_Null_Sender();
		} else {
			$sender = new IT_Exchange_Email_Postmark_Sender( 'a9dd15f4-b367-4670-8c28-6f68833b21f7', $replacer );
		}

		/**
		 * Filter the sender object.
		 *
		 * The return value must implement IT_Exchange_Email_Sender
		 *
		 * @since 1.36
		 *
		 * @param IT_Exchange_Email_Sender       $sender
		 * @param IT_Exchange_Email_Tag_Replacer $replacer
		 */
		$filtered = apply_filters( 'it_exchange_email_notifications_sender', $sender, $replacer );

		if ( $filtered instanceof IT_Exchange_Email_Sender ) {
			$sender = $filtered;
		}

		$notifications = new IT_Exchange_Email_Notifications( $sender, $replacer );
	}

	return $notifications;
}

/**
 * Register email notifications.
 *
 * @since 1.36
 */
function it_exchange_register_email_notifications() {

	$notifications = it_exchange_email_notifications();

	$GLOBALS['IT_Exchange_Email_Notifications'] = $notifications;

	$notifications
		->register_notification( new IT_Exchange_Admin_Email_Notification(
			__( 'Admin Order Notification', 'it-l10n-ithemes-exchange' ), 'admin-order', null, array(
				'defaults' => array(
					'subject' => sprintf( __( 'You made a sale! Yabba Dabba Doo! %s', 'it-l10n-ithemes-exchange' ), '[it_exchange_email show=receipt_id]' ),
					'body'    => sprintf( __( "Your friend %s just bought all this awesomeness from your store! \r\n\r\n Order: %s \r\n\r\n %s", 'it-l10n-ithemes-exchange' ),
						'[it_exchange_email show=fullname]', '[it_exchange_email show=receipt_id]', '[it_exchange_email show=order_table]' ),
				),
				'group'    => __( 'Core', 'it-l10n-ithemes-exchange' )
			)
		) )
		->register_notification( new IT_Exchange_Customer_Email_Notification(
			__( 'Purchase Receipt', 'it-l10n-ithemes-exchange' ), 'receipt', new IT_Exchange_Email_Template( 'receipt' ), array(
				'defaults'    => array(
					'subject' => sprintf( __( 'Receipt for Purchase: %s', 'it-l10n-ithemes-exchange' ), '[it_exchange_email show=receipt_id]' ),
					'body'    => sprintf( __( "Hello %s, \r\n\r\n Thank you for your order. Your order's details are below.", 'it-l10n-ithemes-exchange' ), '[it_exchange_email show=name]' ),
				),
				'group'       => __( 'Core', 'it-l10n-ithemes-exchange' ),
				'description' =>
					__( "The customer's shipping and billing address, as well as the cart details, payment method, download links, total and purchase date are already included in the template.",
						'it-l10n-ithemes-exchange' )
			)
		) )
		->register_notification( new IT_Exchange_Customer_Email_Notification(
			__( 'New Public Transaction Activity', 'it-l10n-ithemes-exchange' ), 'customer-order-note', new IT_Exchange_Email_Template( 'order-note' ), array(
				'defaults' => array(
					'subject' => sprintf( __( 'New note about your order %s', 'it-l10n-ithemes-exchange' ), '[it_exchange_email show=receipt_id]' ),
					'body'    => sprintf( __( "Hello %s, \r\n\r\n A new note has been added to your order.", 'it-l10n-ithemes-exchange' ), '[it_exchange_email show=name]' )
				),
				'group'    => __( 'Core', 'it-l10n-ithemes-exchange' ),
			)
		) );

	/**
	 * Fires when add-ons should register additional email notifications.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Notifications $notifications
	 */
	do_action( 'it_exchange_register_email_notifications', $notifications );
}

add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_register_email_notifications' );