<?php
/**
 * Customer order notes core-addon.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Register the purchase requirement for order notes.
 *
 * @since 1.34
 */
function it_exchange_customer_order_notes_register_purchase_requirement() {

	it_exchange_register_purchase_requirement( 'customer-order-notes', array(
		'priority'               => 6,
		'requirement-met'        => '__return_true', // order notes are always optional
		'sw-template-part'       => 'customer-order-note',
		'checkout-template-part' => 'customer-order-note'
	) );
}

add_action( 'init', 'it_exchange_customer_order_notes_register_purchase_requirement' );

/**
 * Whitelist our SW state.
 *
 * @since 1.34
 *
 * @param array $states
 *
 * @return array
 */
function it_exchange_customer_order_notes_add_sw_state( $states ) {
	$states[] = 'customer-order-note';

	return $states;
}

add_filter( 'it_exchange_super_widget_valid_states', 'it_exchange_customer_order_notes_add_sw_state' );

/**
 * Add Billing Address to the super-widget-checkout totals loop
 *
 * @since 1.34
 *
 * @param array $loops list of existing elements
 *
 * @return array
 */
function it_exchange_add_customer_order_note_to_sw_template_totals_loops( $loops ) {

	// Set index to end of array.
	$index = array_search( 'discounts', $loops );
	$index = ( false === $index ) ? array_search( 'totals-taxes', $loops ) : $index;
	$index = ( false === $index ) ? count( $loops ) - 1 : $index;

	array_splice( $loops, $index, 0, 'customer-order-note' );

	return $loops;
}

add_filter( 'it_exchange_get_super-widget-checkout_after-cart-items_loops', 'it_exchange_add_customer_order_note_to_sw_template_totals_loops', 5 );

/**
 * Store the customer order note from the SW.
 *
 * @since 1.34
 */
function it_exchange_customer_order_notes_sw_save_note() {

	it_exchange_customer_order_notes_store_current_note( wp_strip_all_tags( stripslashes( $_POST['note'] ) ) );

	return true;
}

add_action( 'it_exchange_processing_super_widget_ajax_customer-order-note', 'it_exchange_customer_order_notes_sw_save_note' );

/**
 * Save the customer order note from the checkout screen.
 *
 * @since 1.34
 */
function it_exchange_customer_order_notes_checkout_save_note() {

	if ( ! empty( $_POST['it-exchange-edit-customer-order-note'] ) ) {
		$note = isset( $_POST['it-exchange-customer-order-note'] ) ? $_POST['it-exchange-customer-order-note'] : '';

		it_exchange_customer_order_notes_store_current_note( wp_strip_all_tags( stripslashes( $note ) ) );
	}
}

add_action( 'init', 'it_exchange_customer_order_notes_checkout_save_note' );

/**
 * Get the current order note from the session.
 *
 * @since 1.34
 *
 * @return string
 */
function it_exchange_customer_order_notes_get_current_note() {

	$data = it_exchange_get_session_data( 'customer-order-note' );

	if ( empty( $data ) || ! is_array( $data ) || empty( $data['note'] ) ) {
		return '';
	}

	return $data['note'];
}

/**
 * Store the order note in the session.
 *
 * @since 1.34
 *
 * @param string $note
 */
function it_exchange_customer_order_notes_store_current_note( $note ) {
	it_exchange_update_session_data( 'customer-order-note', array(
		'note' => $note
	) );
}

/**
 * Store the customer order notes to the transaction object.
 *
 * @since 1.34
 *
 * @param stdClass $object
 * @param ITE_Cart $cart
 *
 * @return stdClass
 */
function it_exchange_customer_order_notes_txn_object( $object, ITE_Cart $cart = null ) {

	if ( $cart && ! $cart->is_current() ) {
		return $object;
	}

	$object->customer_order_notes = it_exchange_customer_order_notes_get_current_note();

	return $object;
}

add_filter( 'it_exchange_generate_transaction_object', 'it_exchange_customer_order_notes_txn_object', 10, 2 );

/**
 * When the cart is emptied, clear the customer-order-note session data.
 *
 * @since 1.34
 *        
 * @param \ITE_Cart $cart
 */
function it_exchange_customer_order_notes_empty_cart( ITE_Cart $cart ) {
	if ( $cart->is_current() ) {
		it_exchange_clear_session_data( 'customer-order-note' );
	}
}

add_action( 'it_exchange_empty_cart', 'it_exchange_customer_order_notes_empty_cart' );

/**
 * Create the activity item when the transaction is created.
 *
 * @since 1.34
 *
 * @param int $transaction_id
 */
function it_exchange_customer_order_notes_create_activity( $transaction_id ) {

	$transaction = it_exchange_get_transaction( $transaction_id );

	$cart_object = $transaction->cart_details;

	if ( ! empty( $cart_object->customer_order_notes ) ) {
		$note = $cart_object->customer_order_notes;

		$builder = new IT_Exchange_Txn_Activity_Builder( $transaction, 'note' );
		$builder->set_actor( new IT_Exchange_Txn_Activity_Customer_Actor(
			it_exchange_get_transaction_customer( $transaction )
		) );
		$builder->set_description( $note );
		$builder->build( it_exchange_get_txn_activity_factory() );
	}
}

add_action( 'it_exchange_add_transaction_success', 'it_exchange_customer_order_notes_create_activity' );

/**
 * Register the customer order notes email tag replacement.
 *
 * @since 2.0.0
 *
 * @param IT_Exchange_Email_Tag_Replacer $replacer
 */
function it_exchange_customer_order_notes_register_tag( IT_Exchange_Email_Tag_Replacer $replacer ) {

	$tag = new IT_Exchange_Email_Tag_Base(
		'order_notes', __( 'Customer Order Notes', 'it-l10n-ithemes-exchange' ),
		__( 'The customer\'s order notes, if any.', 'it-l10n-ithemes-exchange' ),
		'it_exchange_customer_order_notes_replace_email_tag'
	);

	$tag->add_required_context( 'transaction' );
	$tag->add_available_for( 'admin-order' );

	$replacer->add_tag( $tag );
}

add_action( 'it_exchange_email_notifications_register_tags', 'it_exchange_customer_order_notes_register_tag' );

/**
 * Replace our 'order_notes' email tag.
 *
 * @since 1.34
 *
 * @param array $context
 *
 * @return string
 */
function it_exchange_customer_order_notes_replace_email_tag( $context ) {

	$transaction = $context['transaction'];

	if ( ! empty ( $transaction->cart_details->customer_order_notes ) ) {
		return $transaction->cart_details->customer_order_notes;
	}

	return '';
}
