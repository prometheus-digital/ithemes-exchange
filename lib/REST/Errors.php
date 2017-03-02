<?php
/**
 * Errors factory.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

/**
 * Class Errors
 *
 * @package iThemes\Exchange\REST
 */
class Errors {

	/**
	 * Error when the user cannot view the object under a certain context like 'edit'.
	 *
	 * @since 2.0.0
	 *
	 * @param string $context
	 *
	 * @return \WP_Error
	 */
	public static function forbidden_context( $context ) {
		return new \WP_Error(
			'it_exchange_rest_forbidden_context',
			sprintf(
				__( 'Sorry, you are not allowed to view this object in a %s context.', 'it-l10n-ithemes-exchange' ),
				$context
			),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	/**
	 * When an object cannot be found. Either an invalid ID, or invalid ID for a given route depth.
	 *
	 * @since 2.0.0
	 *
	 * @return \WP_Error
	 */
	public static function not_found() {
		return new \WP_Error(
			'it_exchange_rest_not_found',
			__( 'Sorry, an object by that id cannot be found.', 'it-l10n-ithemes-exchange' ),
			array( 'status' => \WP_Http::NOT_FOUND )
		);
	}

	/**
	 * When the user does not have permission to view the object.
	 *
	 * @since 2.0.0
	 *
	 * @return \WP_Error
	 */
	public static function cannot_view() {
		return new \WP_Error(
			'it_exchange_rest_cannot_view',
			__( 'Sorry, you are not allowed to view this object.', 'it-l10n-ithemes-exchange' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	/**
	 * When the user does not have permission to edit the object.
	 *
	 * @since 2.0.0
	 *
	 * @return \WP_Error
	 */
	public static function cannot_edit() {
		return new \WP_Error(
			'it_exchange_rest_cannot_edit',
			__( 'Sorry, you are not allowed to edit this object.', 'it-l10n-ithemes-exchange' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	/**
	 * When the user does not have permission to create object.
	 *
	 * @since 2.0.0
	 *
	 * @return \WP_Error
	 */
	public static function cannot_create() {
		return new \WP_Error(
			'it_exchange_rest_cannot_create',
			__( 'Sorry, you are not allowed to create objects of this type.', 'it-l10n-ithemes-exchange' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	/**
	 * When the user does not have permission to delete the object.
	 *
	 * @since 2.0.0
	 *
	 * @return \WP_Error
	 */
	public static function cannot_delete() {
		return new \WP_Error(
			'it_exchange_rest_cannot_edit',
			__( 'Sorry, you are not allowed to delete this object.', 'it-l10n-ithemes-exchange' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	/**
	 * When the user cannot list objects.
	 *
	 * @since 2.0.0
	 *
	 * @param string $message Custom error message. If empty, defaults to a general error message.
	 *
	 * @return \WP_Error
	 */
	public static function cannot_list( $message = '' ) {
		return new \WP_Error(
			'it_exchange_rest_cannot_list',
			$message ?: __( 'Sorry, you are not allowed to list objects of this type.', 'it-l10n-ithemes-exchange' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	/**
	 * When the user does not have permission to use a query var.
	 *
	 * @since 2.0.0
	 *
	 * @param string $var
	 *
	 * @return \WP_Error
	 */
	public static function cannot_use_query_var( $var ) {
		return new \WP_Error(
			'it_exchange_rest_forbidden_query_var',
			sprintf( __( "Sorry, you are not allowed to query objects by '%s'.", 'it-l10n-ithemes-exchange' ), $var ),
			array( 'status' => rest_authorization_required_code(), 'query_var' => $var )
		);
	}

	/**
	 * When the user is using the given query var improperly.
	 *
	 * @since 2.0.0
	 *
	 * @param string $var
	 *
	 * @return \WP_Error
	 */
	public static function invalid_query_var_usage( $var ) {
		return new \WP_Error(
			'it_exchange_rest_invalid_query_var',
			sprintf( __( "Sorry, the '%s' query arg is invalid.", 'it-l10n-ithemes-exchange' ), $var ),
			array( 'status' => \WP_Http::BAD_REQUEST, 'query_var' => $var )
		);
	}
}