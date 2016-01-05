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
	$index = ( false === $index ) ? array_search( 'totals-taxes-simple', $loops ) : $index;
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

	die( 1 );
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
		return 'My Default';
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
