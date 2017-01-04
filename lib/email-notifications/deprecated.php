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

	$settings    = it_exchange_get_option( 'settings_email' );
	$bc          = it_exchange_email_notifications();
	$context     = $email->get_context();
	$transaction = empty( $context['transaction'] ) ? null : it_exchange_get_transaction( $context['transaction'] );

	$subject = apply_filters_deprecated(
		'it_exchange_send_purchase_emails_subject',
		array( $subject, $transaction, $settings, $bc ),
		'2.0.0',
		'it_exchange_email_receipt_subject'
	);

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

	$settings    = it_exchange_get_option( 'settings_email' );
	$bc          = it_exchange_email_notifications();
	$context     = $email->get_context();
	$transaction = empty( $context['transaction'] ) ? null : it_exchange_get_transaction( $context['transaction'] );

	$body = apply_filters_deprecated(
		'it_exchange_send_purchase_emails_body',
		array( $body, $transaction, $settings, $bc ),
		'2.0.0',
		'it_exchange_email_receipt_body'
	);

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

	$headers = apply_filters_deprecated(
		'it_exchange_send_email_notification_headers',
		array( $headers ),
		'2.0.0',
		'it_exchange_send_email_notification_wp_mail_headers'
	);

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

	$body = apply_filters_deprecated(
		'it_exchange_send_email_notification_body',
		array( $body ),
		'2.0.0',
		'it_exchange_send_email_notification_wp_mail_body'
	);

	return $body;
}

add_filter( 'it_exchange_send_email_notification_wp_mail_body', 'it_exchange_email_body_back_compat' );
