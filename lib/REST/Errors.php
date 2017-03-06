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

	/**
	 * When a given line item type does not support viewing items.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type
	 *
	 * @return \WP_Error
	 */
	public static function view_line_item_not_supported( $type ) {
		return new \WP_Error(
			'it_exchange_rest_view_line_item_not_supported',
			sprintf( __( "Sorry, the '%s' line item type does not support reading.", 'it-l10n-ithemes-exchange' ), $type ),
			array( 'status' => \WP_Http::METHOD_NOT_ALLOWED )
		);
	}

	/**
	 * When a given line item type does not support creating items.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type
	 *
	 * @return \WP_Error
	 */
	public static function create_line_item_not_supported( $type ) {
		return new \WP_Error(
			'it_exchange_rest_create_line_item_not_supported',
			sprintf( __( "Sorry, the '%s' line item type does not support creation.", 'it-l10n-ithemes-exchange' ), $type ),
			array( 'status' => \WP_Http::METHOD_NOT_ALLOWED )
		);
	}

	/**
	 * When a given line item type does not support editing items.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type
	 *
	 * @return \WP_Error
	 */
	public static function edit_line_item_not_supported( $type ) {
		return new \WP_Error(
			'it_exchange_rest_edit_line_item_not_supported',
			sprintf( __( "Sorry, the '%s' line item type does not support editing.", 'it-l10n-ithemes-exchange' ), $type ),
			array( 'status' => \WP_Http::METHOD_NOT_ALLOWED )
		);
	}

	/**
	 * When a given line item type does not support deleting items.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type
	 *
	 * @return \WP_Error
	 */
	public static function delete_line_item_not_supported( $type ) {
		return new \WP_Error(
			'it_exchange_rest_delete_line_item_not_supported',
			sprintf( __( "Sorry, the '%s' line item type does not support deletion.", 'it-l10n-ithemes-exchange' ), $type ),
			array( 'status' => \WP_Http::METHOD_NOT_ALLOWED )
		);
	}
}