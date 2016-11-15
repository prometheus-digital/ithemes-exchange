<?php
/**
 * Contains deprecated functions and filters.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Back-compat filter
 *
 * @since 2.0.0
 *
 * @param string            $subject
 * @param IT_Exchange_Email $email
 *
 * @return string
 */
function it_exchange_email_receipt_subject_back_compat( $subject, IT_Exchange_Email $email ) {

	if ( has_filter( 'it_exchange_send_purchase_emails_subject' ) ) {

		it_exchange_deprecated_filter( 'it_exchange_send_purchase_emails_subject', 'it_exchange_email_receipt_subject' );

		$settings    = it_exchange_get_option( 'settings_email' );
		$bc          = it_exchange_email_notifications();
		$context     = $email->get_context();
		$transaction = empty( $context['transaction'] ) ? null : it_exchange_get_transaction( $context['transaction'] );

		$subject = apply_filters( 'it_exchange_send_purchase_emails_subject', $subject, $transaction, $settings, $bc );
	}

	return $subject;
}

add_filter( 'it_exchange_email_receipt_subject', 'it_exchange_email_receipt_subject_back_compat', 10, 2 );

/**
 * Back-compat filter
 *
 * @since 2.0.0
 *
 * @param string            $body
 * @param IT_Exchange_Email $email
 *
 * @return string
 */
function it_exchange_email_receipt_body_back_compat( $body, IT_Exchange_Email $email ) {

	if ( has_filter( 'it_exchange_send_purchase_emails_body' ) ) {

		it_exchange_deprecated_filter( 'it_exchange_send_purchase_emails_body', 'it_exchange_email_receipt_body' );

		$settings    = it_exchange_get_option( 'settings_email' );
		$bc          = it_exchange_email_notifications();
		$context     = $email->get_context();
		$transaction = empty( $context['transaction'] ) ? null : it_exchange_get_transaction( $context['transaction'] );

		$body = apply_filters( 'it_exchange_send_purchase_emails_body', $body, $transaction, $settings, $bc );
	}

	return $body;
}

add_filter( 'it_exchange_email_receipt_body', 'it_exchange_email_receipt_body_back_compat', 10, 2 );

/**
 * Back-compat filter.
 *
 * @since 2.0.0
 *
 * @param array $headers
 *
 * @return array
 */
function it_exchange_email_headers_back_compat( $headers ) {

	if ( has_filter( 'it_exchange_send_email_notification_headers' ) ) {

		it_exchange_deprecated_filter( 'it_exchange_send_email_notification_headers', 'it_exchange_send_email_notification_wp_mail_headers' );

		$headers = apply_filters( 'it_exchange_send_email_notification_headers', $headers );
	}

	return $headers;
}

add_filter( 'it_exchange_send_email_notification_wp_mail_headers', 'it_exchange_email_headers_back_compat' );

/**
 * Back-compat filter.
 *
 * @since 2.0.0
 *
 * @param array $body
 *
 * @return array
 */
function it_exchange_email_body_back_compat( $body ) {

	if ( has_filter( 'it_exchange_send_email_notification_body' ) ) {

		it_exchange_deprecated_filter( 'it_exchange_send_email_notification_body', 'it_exchange_send_email_notification_wp_mail_body' );

		$body = apply_filters( 'it_exchange_send_email_notification_body', $body );
	}

	return $body;
}

add_filter( 'it_exchange_send_email_notification_wp_mail_body', 'it_exchange_email_body_back_compat' );
