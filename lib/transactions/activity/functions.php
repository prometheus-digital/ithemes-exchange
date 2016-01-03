<?php
/**
 * Transaction Activity Functions and Hooks
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Register the txn activity post type.
 *
 * @since 1.34
 */
function it_exchange_register_txn_activity_post_type() {

	register_post_type( 'ite_txn_activity', array(
		'public' => false
	) );
}

add_action( 'init', 'it_exchange_register_txn_activity_post_type' );

/**
 * Register the txn activity taxonomy type.
 *
 * @since 1.34
 */
function it_exchange_register_txn_activity_taxonomy() {

	register_taxonomy( 'ite_txn_activity_type', 'ite_txn_activity', array(
		'public' => false
	) );
}

add_action( 'init', 'it_exchange_register_txn_activity_taxonomy' );

/**
 * Get the txn activity factory.
 *
 * @since 1.34
 *
 * @return IT_Exchange_Txn_Activity_Factory
 */
function it_exchange_get_txn_activity_factory() {

	$factory = new IT_Exchange_Txn_Activity_Factory(
		'ite_txn_activity', 'ite_txn_activity_type', it_exchange_get_txn_activity_actor_factory()
	);
	$factory->register( 'note', array( 'IT_Exchange_Txn_Note_Activity', 'make' ) );

	return $factory;
}

/**
 * Get the txn activity actor factory.
 *
 * @since 1.34
 *
 * @return IT_Exchange_Txn_Activity_Actor_Factory
 */
function it_exchange_get_txn_activity_actor_factory() {

	$factory = new IT_Exchange_Txn_Activity_Actor_Factory();
	$factory->register( 'site', 'IT_Exchange_Txn_Activity_Site_Actor' );
	$factory->register( 'customer', array( 'IT_Exchange_Txn_Activity_Customer_Actor', 'make' ) );
	$factory->register( 'user', array( 'IT_Exchange_Txn_Activity_User_Actor', 'make' ) );

	return $factory;
}

/**
 * Get a transaction activity object.
 *
 * @since 1.34
 *
 * @param int|IT_Exchange_Txn_Activity $ID
 *
 * @return IT_Exchange_Txn_Activity
 */
function it_exchange_get_txn_activity( $ID ) {

	if ( ! $ID instanceof IT_Exchange_Txn_Activity ) {
		$activity = it_exchange_get_txn_activity_factory()->make( $ID );
	} else {
		$activity = $ID;
	}

	/**
	 * Filters the transaction activity object.
	 *
	 * If the object is overwritten, it must have the same class as
	 * or be a subclass of the original object.
	 *
	 * @since 1.34
	 *
	 * @param IT_Exchange_Txn_Activity $activity
	 */
	$filtered = apply_filters( 'it_exchange_get_txn_activity', $activity );

	if ( get_class( $filtered ) !== get_class( $activity ) && ! is_subclass_of( $filtered, get_class( $activity ) ) ) {
		throw new UnexpectedValueException( 'Invalid txn activity object returned from filter.' );
	}

	return $filtered;
}