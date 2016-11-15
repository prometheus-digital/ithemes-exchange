<?php
/**
 * Contains the customer email notification.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Customer_Email_Notification
 */
class IT_Exchange_Customer_Email_Notification extends IT_Exchange_Email_Notification {

	/**
	 * Get the notification type.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	public function get_type( $label = false ) {
		return $label ? __( 'Customer', 'it-l10n-ithemes-exchange' ) : 'customer';
	}
}
