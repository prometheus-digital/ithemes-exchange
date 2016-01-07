<?php
/**
 * Contains iThemes Security Pro integration.
 *
 * @since   1.34
 * @license GPLv2
 */

if ( ! class_exists( 'ITSEC_Recaptcha' ) ) {
	return;
}

/**
 * Should the login page be protected with recaptcha.
 *
 * @since 1.34
 *
 * @return bool
 */
function it_exchange_security_recaptcha_login() {

	$settings = get_site_option( 'itsec_recaptcha' );

	return ! empty( $settings['login'] );
}

/**
 * Should the registration page be protected with recpatcha.
 *
 * @since 1.34
 *
 * @return bool
 */
function it_exchange_security_recaptcha_registration() {

	$settings = get_site_option( 'itsec_recaptcha' );

	return ! empty( $settings['register'] );
}

/**
 * Add the recaptcha to the registration SW state and page.
 *
 * @since 1.34
 */
function it_exchange_security_add_recaptcha_to_registration() {

	if ( ! it_exchange_security_recaptcha_registration() ) {
		return;
	}

	ITSEC_Recaptcha::show_field();
}

add_action( 'it_exchange_super_widget_registration_end_fields_loop', 'it_exchange_security_add_recaptcha_to_registration' );
add_action( 'it_exchange_content_registration_end_fields_loop', 'it_exchange_security_add_recaptcha_to_registration' );

/**
 * Validate the recpatcha on the SW registration screen.
 *
 * @since 1.34
 *
 * @param WP_Error $errors
 *
 * @return WP_Error
 */
function it_exchange_security_validate_recaptcha_registration( $errors ) {

	if ( ! it_exchange_security_recaptcha_registration() ) {
		return $errors;
	}

	$success = ITSEC_Recaptcha::validate_captcha();

	switch ( $success ) {

		case - 1:
			return new WP_Error( 'recaptcha_error',
				__( 'You must verify you are indeed a human to login to this site', 'it-l10n-ithemes-exchange' )
			);
			break;
		case 0:
			return new WP_Error( 'recaptcha_error',
				__( 'The captcha response you submitted does not appear to be valid. Please try again.', 'it-l10n-ithemes-exchange' )
			);
			break;
		case - 2:
			return new WP_Error( 'recaptcha_error',
				__( 'We cannot verify that you are indeed human. Please try again.', 'it-l10n-ithemes-exchange' )
			);
			break;
	}

	return $errors;
}

add_filter( 'it_exchange_register_user_errors', 'it_exchange_security_validate_recaptcha_registration' );