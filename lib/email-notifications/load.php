<?php
/**
 * Load the email notifications component.
 *
 * @since   1.36
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/class.email-notifications.php';

require_once dirname( __FILE__ ) . '/class.customizer.php';

require_once dirname( __FILE__ ) . '/class.email-notification.php';
require_once dirname( __FILE__ ) . '/class.admin-email-notification.php';
require_once dirname( __FILE__ ) . '/class.customer-email-notification.php';

require_once dirname( __FILE__ ) . '/class.email-template.php';
require_once dirname( __FILE__ ) . '/class.email.php';
require_once dirname( __FILE__ ) . '/interface.email-recipient.php';
require_once dirname( __FILE__ ) . '/class.email-recipient-transaction.php';

require_once dirname( __FILE__ ) . '/class.delivery-exception.php';
require_once dirname( __FILE__ ) . '/interface.sender.php';
require_once dirname( __FILE__ ) . '/class.wp-mail-sender.php';
require_once dirname( __FILE__ ) . '/class.email-tag-replacer.php';

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

		$replacer = new IT_Exchange_Email_Tag_Replacer();
		$sender   = new IT_Exchange_WP_Mail_Sender( $replacer );

		$notifications = new IT_Exchange_Email_Notifications( $sender, $replacer );
	}

	return $notifications;
}

$GLOBALS[' IT_Exchange_Email_Notifications'] = it_exchange_email_notifications();

/**
 * Register email notifications.
 *
 * @since 1.36
 */
function it_exchange_register_email_notifications() {

	it_exchange_email_notifications()
		->register_notification( new IT_Exchange_Admin_Email_Notification(
			__( 'Admin Order Notification', 'it-l10n-ithemes-exchange' ), 'admin-order-notification', null, array(
				'subject' => sprintf( __( 'You made a sale! Yabba Dabba Doo! %s', 'it-l10n-ithemes-exchange' ), '[it_exchange_email show=receipt_id]' ),
				'body'    => sprintf( __( 'Your friend %s just bought all this awesomeness from your store!

Order: %s

%s', 'it-l10n-ithemes-exchange' ), '[it_exchange_email show=fullname]', '[it_exchange_email show=receipt_id]', '[it_exchange_email show=order_table]' ),
			)
		) )
		->register_notification( new IT_Exchange_Customer_Email_Notification(
			__( 'New Order', 'it-l10n-ithemes-exchange' ), 'new-order', new IT_Exchange_Email_Template( 'receipt' ), array(
				'subject' => sprintf( __( 'Receipt for Purchase: %s', 'it-l10n-ithemes-exchange' ), '[it_exchange_email show=receipt_id]' ),
				'body'    => sprintf( __( "Hello %s,

Thank you for your order. Your order's details are below.

%s", 'it-l10n-ithemes-exchange' ), '[it_exchange_email show=name]', '[it_exchange_email show=download_list]'
				),
			)
		) );
}

add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_register_email_notifications' );