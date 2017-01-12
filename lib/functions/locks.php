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
 * Helper to execute a function when a lock is able to be acquired.
 *
 * @since 2.0.0
 *
 * @param string   $name     Unique identifier for the lock.
 * @param int      $length   Number of seconds the lock is needed.
 * @param callable $callable Function to be called after the lock is aquired.
 * @param int      $tries    How many wait tries before throwing an exception.
 *
 * @return mixed
 * @throws IT_Exchange_Locking_Exception If unable to acquire a lock within the specified number of tries.
 */
function it_exchange_wait_for_lock( $name, $length, $callable, $tries = 10 ) {

	do {
		try {
			it_exchange_lock( $name, $length );
			$r = call_user_func( $callable );
			it_exchange_release_lock( $name );

			return $r;
		} catch ( IT_Exchange_Locking_Exception $e ) {
			$tries--;
			sleep( $length / 2 );
		}
	} while ( $tries >= 0 );

	$seconds = 10 * ( $length / 2 );

	throw new IT_Exchange_Locking_Exception( "Unable to acquire locks within {$seconds} seconds." );
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
