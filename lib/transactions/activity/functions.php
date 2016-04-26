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

add_action( 'init', 'it_exchange_register_txn_activity_post_type', 0 );

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

add_action( 'init', 'it_exchange_register_txn_activity_taxonomy', 0 );

/**
 * Send a public note to a customer via email.
 *
 * @since 1.34
 *
 * @param IT_Exchange_Txn_Activity $activity
 */
function it_exchange_send_public_note_to_customer( IT_Exchange_Txn_Activity $activity ) {

	if ( ! $activity->is_public() ) {
		return;
	}

	if ( $activity->has_actor() && $activity->get_actor() instanceof IT_Exchange_Txn_Activity_Customer_Actor ) {
		return;
	}

	$subject = sprintf( __( 'New note about your order %s', 'it-l10n-ithemes-exchange' ),
		$activity->get_transaction()->get_order_number()
	);

	$message = <<<NOWDOC

Hello [it_exchange_email show=name],

A new note has been added to your order:

<blockquote>{$activity->get_description()}</blockquote>

For your reference, your order's details are below.
Order: [it_exchange_email show=receipt_id]

[it_exchange_email show=order_table]

NOWDOC;

	$subject = apply_filters( 'it_exchange_send_public_note_to_customer_subject', $subject, $activity );
	$message = apply_filters( 'it_exchange_send_public_note_to_customer_message', $message, $activity );

	do_action( 'it_exchange_send_email_notification',
		it_exchange_get_transaction_customer_id( $activity->get_transaction() ),
		$subject, $message, $activity->get_transaction()->ID
	);
}

add_action( 'it_exchange_build_txn_activity', 'it_exchange_send_public_note_to_customer' );

/**
 * Add a note when a transaction's status is changed.
 *
 * @since 1.34
 *
 * @param IT_Exchange_Transaction $transaction
 * @param string                  $old_status
 */
function it_exchange_add_note_on_status_change( $transaction, $old_status ) {

	$old_status_label = it_exchange_get_transaction_status_label( $transaction, array(
		'status' => $old_status
	) );

	$new_status_label = it_exchange_get_transaction_status_label( $transaction );

	$message = sprintf( __( 'Status changed from %s to %s.', 'it-l10n-ithemes-exchange' ),
		$old_status_label, $new_status_label
	);

	$builder = new IT_Exchange_Txn_Activity_Builder( $transaction, 'status' );
	$builder->set_description( $message );

	if ( is_user_logged_in() ) {
		$actor = new IT_Exchange_Txn_Activity_User_Actor( wp_get_current_user() );
	} elseif ( ( $wh = it_exchange_doing_webhook() ) && ( $addon = it_exchange_get_addon( $wh ) ) ) {
		$actor = new IT_Exchange_Txn_Activity_Gateway_Actor( $addon );
	} else {
		$actor = new IT_Exchange_Txn_Activity_Site_Actor();
	}

	$builder->set_actor( $actor );
	$builder->build( it_exchange_get_txn_activity_factory() );

	if ( $transaction->post_parent ) {

		$parent = $transaction->post_parent;
		$parent = it_exchange_get_transaction( $parent );

		$builder = new IT_Exchange_Txn_Activity_Builder( $parent, 'renewal' );
		$builder->set_child( $transaction );
		$builder->set_actor( new IT_Exchange_Txn_Activity_Gateway_Actor( it_exchange_get_addon(
			it_exchange_get_transaction_method( $parent )
		) ) );
		$builder->build( it_exchange_get_txn_activity_factory() );
	}
}

add_action( 'it_exchange_update_transaction_status', 'it_exchange_add_note_on_status_change', 9, 2 );

/**
 * Add a renewal note when a child transaction is created.
 *
 * @since 1.34
 *
 * @param int $transaction_id
 */
function it_exchange_add_activity_on_renewal( $transaction_id ) {

	$parent = get_post_meta( $transaction_id, '_it_exchange_parent_tx_id', true );
	$parent = it_exchange_get_transaction( $parent );

	$builder = new IT_Exchange_Txn_Activity_Builder( $parent, 'renewal' );
	$builder->set_child( it_exchange_get_transaction( $transaction_id ) );
	$builder->set_actor( new IT_Exchange_Txn_Activity_Gateway_Actor( it_exchange_get_addon(
		it_exchange_get_transaction_method( $parent )
	) ) );
	$builder->build( it_exchange_get_txn_activity_factory() );
}

add_action( 'it_exchange_add_child_transaction_success', 'it_exchange_add_activity_on_renewal' );

/**
 * Add activity whenever the method ID changes.
 *
 * @since 1.35.3
 *
 * @param int    $mid
 * @param int    $object_id
 * @param string $meta_key
 * @param mixed  $meta_value
 */
function it_exchange_add_activity_on_method_id_change( $mid, $object_id, $meta_key, $meta_value ) {

	if ( $meta_key !== '_it_exchange_transaction_method_id' ) {
		return;
	}

	$txn  = it_exchange_get_transaction( $object_id );
	$prev = it_exchange_get_transaction_method_id( $object_id );

	if ( ! $txn ) {
		return;
	}

	$meta_value = "<code>$meta_value</code>";

	if ( ! $prev ) {
		$message = sprintf( __( 'Method ID set to %s.', 'it-l10n-ithemes-exchange' ), $meta_value );
	} else {
		$prev    = "<code>$prev</code>";
		$message = sprintf( __( 'Method ID updated to %s from %s.', 'it-l10n-ithemes-exchange' ), $meta_value, $prev );
	}

	$builder = new IT_Exchange_Txn_Activity_Builder( $txn, 'note' );
	$builder->set_description( $message );

	if ( is_user_logged_in() ) {
		$actor = new IT_Exchange_Txn_Activity_User_Actor( wp_get_current_user() );
	} elseif ( ( $wh = it_exchange_doing_webhook() ) && ( $addon = it_exchange_get_addon( $wh ) ) ) {
		$actor = new IT_Exchange_Txn_Activity_Gateway_Actor( $addon );
	} else {
		$actor = new IT_Exchange_Txn_Activity_Site_Actor();
	}

	$builder->set_actor( $actor );
	$builder->build( it_exchange_get_txn_activity_factory() );
}

add_action( 'update_post_meta', 'it_exchange_add_activity_on_method_id_change', 10, 4 );

/**
 * Get the txn activity factory.
 *
 * @since 1.34
 *
 * @return IT_Exchange_Txn_Activity_Factory
 */
function it_exchange_get_txn_activity_factory() {

	if ( ! did_action( 'init' ) ) {
		return null;
	}

	$factory = new IT_Exchange_Txn_Activity_Factory(
		'ite_txn_activity', 'ite_txn_activity_type', it_exchange_get_txn_activity_actor_factory()
	);
	$factory->register( 'note', __( 'Notes', 'it-l10n-ithemes-exchange' ), array(
		'IT_Exchange_Txn_Note_Activity',
		'make'
	) );
	$factory->register( 'renewal', __( 'Renewals', 'it-l10n-ithemes-exchange' ), array(
		'IT_Exchange_Txn_Renewal_Activity',
		'make'
	) );
	$factory->register( 'status', __( 'Order Status', 'it-l10n-ithemes-exchange' ), array(
		'IT_Exchange_Txn_Status_Activity',
		'make'
	) );

	/**
	 * Fires when an activity factory is made.
	 *
	 * @since 1.35.5
	 *
	 * @param IT_Exchange_Txn_Activity_Factory $factory
	 */
	do_action( 'it_exchange_get_txn_activity_factory', $factory );

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
	$factory->register( 'gateway', array( 'IT_Exchange_Txn_Activity_Gateway_Actor', 'make' ) );

	/**
	 * Fires when an activity actor factory is made.
	 *
	 * @since 1.35.5
	 *
	 * @param IT_Exchange_Txn_Activity_Actor_Factory $factory
	 */
	do_action( 'it_exchange_get_txn_activity_actor_factory', $factory );

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

	if ( ! empty( $activity ) && get_class( $filtered ) !== get_class( $activity ) && ! is_subclass_of( $filtered, get_class( $activity ) ) ) {
		throw new UnexpectedValueException( 'Invalid txn activity object returned from filter.' );
	}

	return $filtered;
}