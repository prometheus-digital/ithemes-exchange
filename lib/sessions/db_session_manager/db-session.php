<?php
/**
 * WordPress session managment.
 *
 * ############### WP Session Manager ##########
 * ## This is a lightly modified version of WP Session Manager by Eric Mann
 * ## Author: http://twitter.com/ericmann
 * ## Donate link: http://jumping-duck.com/wordpress/plugins
 * ## Github link: https://github.com/ericmann/wp-session-manager
 * ## Requires at least: WordPress 3.4.2
 * ## License: GPLv2 or later License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * #############################################
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
 * @internal
 *
 * @return int
 */
function it_exchange_db_session_cache_expire() {
	$it_exchange_db_session = IT_Exchange_DB_Sessions::get_instance();

	return $it_exchange_db_session->cache_expiration();
}

/**
 * Alias of it_exchange_db_session_write_close()
 *
 * @internal
 */
function it_exchange_db_session_commit() {
	it_exchange_db_session_write_close();
}

/**
 * Load a JSON-encoded string into the current session.
 *
 * @internal
 *
 * @param string $data
 * 
 * @return bool
 */
function it_exchange_db_session_decode( $data ) {
	$it_exchange_db_session = IT_Exchange_DB_Sessions::get_instance();

	return $it_exchange_db_session->json_in( $data );
}

/**
 * Encode the current session's data as a JSON string.
 *
 * @internal
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
 * @internal
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
 * @internal
 *
 * @return bool
 */
function it_exchange_db_session_start() {
	$it_exchange_db_session = IT_Exchange_DB_Sessions::get_instance();
	do_action( 'it_exchange_db_session_start' );

	return $it_exchange_db_session->session_started();
}
add_action( 'init', 'it_exchange_db_session_start', -1 );

/**
 * Return the current session status.
 *
 * @internal
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
 *
 * @internal
 */
function it_exchange_db_session_unset() {
	$it_exchange_db_session = IT_Exchange_DB_Sessions::get_instance();

	$it_exchange_db_session->reset();
}

/**
 * Write session data and end session
 *
 * @internal
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
 *
 * @internal
 */
function it_exchange_db_session_cleanup() {
	global $wpdb;
	
	if ( defined( 'WP_SETUP_CONFIG' ) ) {
		return;
	}
	
	if ( ! defined( 'WP_INSTALLING' ) ) {

		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}ite_sessions WHERE expires_at < %s AND purchased_at IS NULL", current_time( 'mysql', true )
		) );

		$week_ago = time() - ( DAY_IN_SECONDS * 7 );
		$week_ago = gmdate( 'Y-m-d H:i:s', $week_ago );
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}ite_sessions WHERE purchaed_at < %s OR expires_at < %s", $week_ago, $week_ago
		) );
	}

	// Allow other plugins to hook in to the garbage collection process.
	do_action( 'it_exchange_db_session_cleanup' );
}
add_action( 'it_exchange_db_session_garbage_collection', 'it_exchange_db_session_cleanup' );

/**
 * Clean up ALL sessions by removing data and their expiration entries from
 * the WordPress options table.
 *
 * This method probably shouldn't be called in a production environment
 *
 * @internal
 */
function it_exchange_db_delete_all_sessions() {
	global $wpdb;

	if ( defined( 'WP_SETUP_CONFIG' ) ) {
		return;
	}

	if ( ! defined( 'WP_INSTALLING' ) ) {
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}ite_sessions" );
	}

	// Allow other plugins to hook in to the garbage collection process.
	do_action( 'it_exchange_db_session_cleanup' );
}

/**
 * Register the garbage collector as a twice daily event.
 *
 * @internal
 */
function it_exchange_db_session_register_garbage_collection() {
	if ( ! wp_next_scheduled( 'it_exchange_db_session_garbage_collection' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'it_exchange_db_session_garbage_collection' );
	}
}
add_action( 'wp', 'it_exchange_db_session_register_garbage_collection' );
