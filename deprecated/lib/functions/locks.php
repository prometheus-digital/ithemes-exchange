<?php
/**
 * Internal locking API.
 *
 * @internal
 * @since   1.32.1
 * @license GPlv2
 */

/**
 * Create a lock.
 *
 * @since 1.32.1
 *
 * @param string $name   Name of the lock. Should be unique to the operation. Already prefixed for exchange.
 * @param int    $length How long to lock for in seconds.
 *
 * @throws IT_Exchange_Locking_Exception If the lock already exists.
 */
function it_exchange_lock( $name, $length ) {

	/** @var wpdb $wpdb */
	global $wpdb;

	$suppress = $wpdb->suppress_errors();

	$result = $wpdb->query( $wpdb->prepare(
		"INSERT IGNORE INTO `$wpdb->options` ( `option_name`, `option_value`, `autoload` ) VALUES (%s, %s, 'no') /* LOCK */",
		_it_exchange_get_lock_option( $name ), time() + $length ) );

	$wpdb->suppress_errors( $suppress );

	// a lock by this name already exists
	if ( $wpdb->last_error || ! $result ) {

		$requested_completion_time = get_option( _it_exchange_get_lock_option( $name ) );

		if ( time() > $requested_completion_time ) {

			// if the lock has expired reset the lock
			update_option( _it_exchange_get_lock_option( $name ), time() + $length );

			return;
		}

		throw new IT_Exchange_Locking_Exception( "A lock already exists for $name" );
	}
}

/**
 * Release a lock.
 *
 * Make sure to call this after completing your task.
 *
 * @since 1.32.1
 *
 * @param string $name
 *
 * @return bool
 */
function it_exchange_release_lock( $name ) {
	return delete_option( _it_exchange_get_lock_option( $name ) );
}

/**
 * Rerieve the option name for a given lock name.
 *
 * @internal
 *
 * @since 1.32.1
 *
 * @param string $name
 *
 * @return string
 */
function _it_exchange_get_lock_option( $name ) {
	return "exchange-lock-{$name}";
}

/**
 * Class IT_Exchange_Locking_Exception
 */
class IT_Exchange_Locking_Exception extends Exception {
}