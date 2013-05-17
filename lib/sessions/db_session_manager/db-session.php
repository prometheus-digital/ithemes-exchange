<?php
/**
 * WordPress session managment.
 *
 * Standardizes WordPress session data and uses either database transients or in-memory caching
 * for storing user session information.
 *
 * @subpackage Session
 * @since 0.4.0
 */

/**
 * Return the current cache expire setting.
 *
 * @return int
 */
function it_exchange_db_session_cache_expire() {
	$it_exchange_db_session = IT_Exchange_DB_Sessions::get_instance();

	return $it_exchange_db_session->cache_expiration();
}

/**
 * Alias of it_exchange_db_session_write_close()
 */
function it_exchange_db_session_commit() {
	it_exchange_db_session_write_close();
}

/**
 * Load a JSON-encoded string into the current session.
 *
 * @param string $data
 */
function it_exchange_db_session_decode( $data ) {
	$it_exchange_db_session = IT_Exchange_DB_Sessions::get_instance();

	return $it_exchange_db_session->json_in( $data );
}

/**
 * Encode the current session's data as a JSON string.
 *
 * @return string
 */
function it_exchange_db_session_encode() {
	$it_exchange_db_session = IT_Exchange_DB_Sessions::get_instance();

	return $it_exchange_db_session->json_out();
}

/**
 * Regenerate the session ID.
 *
 * @param bool $delete_old_session
 *
 * @return bool
 */
function it_exchange_db_session_regenerate_id( $delete_old_session = false ) {
	$it_exchange_db_session = IT_Exchange_DB_Sessions::get_instance();

	$it_exchange_db_session->regenerate_id( $delete_old_session );

	return true;
}

/**
 * Start new or resume existing session.
 *
 * Resumes an existing session based on a value sent by the _it_exchange_db_session cookie.
 *
 * @return bool
 */
function it_exchange_db_session_start() {
	$it_exchange_db_session = IT_Exchange_DB_Sessions::get_instance();
	do_action( 'it_exchange_db_session_start' );

	return $it_exchange_db_session->session_started();
}
add_action( 'plugins_loaded', 'it_exchange_db_session_start' );

/**
 * Return the current session status.
 *
 * @return int
 */
function it_exchange_db_session_status() {
	$it_exchange_db_session = IT_Exchange_DB_Sessions::get_instance();

	if ( $it_exchange_db_session->session_started() ) {
		return PHP_SESSION_ACTIVE;
	}

	return PHP_SESSION_NONE;
}

/**
 * Unset all session variables.
 */
function it_exchange_db_session_unset() {
	$it_exchange_db_session = IT_Exchange_DB_Sessions::get_instance();

	$it_exchange_db_session->reset();
}

/**
 * Write session data and end session
 */
function it_exchange_db_session_write_close() {
	$it_exchange_db_session = IT_Exchange_DB_Sessions::get_instance();

	$it_exchange_db_session->write_data();
	do_action( 'it_exchange_db_session_commit' );
}
add_action( 'shutdown', 'it_exchange_db_session_write_close' );

/**
 * Clean up expired sessions by removing data and their expiration entries from
 * the WordPress options table.
 *
 * This method should never be called directly and should instead be triggered as part
 * of a scheduled task or cron job.
 */
function it_exchange_db_session_cleanup() {
	global $wpdb;

	if ( defined( 'WP_SETUP_CONFIG' ) ) {
		return;
	}

	if ( ! defined( 'WP_INSTALLING' ) ) {
		$expiration_keys = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '_it_exchange_db_session_expires_%'" );

		foreach( $expiration_keys as $expiration ) {
			// If the session has expired
			if ( time() > intval( $expiration->option_value ) ) {
				// Get the session ID
				$session_id = substr( $expiration->option_name, 20 );

				delete_option( $expiration->option_name );
				delete_option( "_it_exchange_db_session_{$session_id}" );
			}
		}
	}

	// Allow other plugins to hook in to the garbage collection process.
	do_action( 'it_exchange_db_session_cleanup' );
}
add_action( 'it_exchange_db_session_garbage_collection', 'it_exchange_db_session_cleanup' );

/**
 * Register the garbage collector as a twice daily event.
 */
function it_exchange_db_session_register_garbage_collection() {
	if ( ! wp_next_scheduled( 'it_exchange_db_session_garbage_collection' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'it_exchange_db_session_garbage_collection' );
	}
}
add_action( 'wp', 'it_exchange_db_session_register_garbage_collection' );