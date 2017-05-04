<?php
/**
 * Deprecated Guest Checkout functions.
 *
 * @since 2.0.0
 * @license GPLv2
 */

/**
 * Return true on has_transaction (for confirmation screen) if conditionals match
 *
 * Conditionals:
 * - We're doing a guest checkout
 * - Transaction was a guest checkout transaction
 * - Current guest has same email as one used in the transaction
 *
 * @since 1.6.0
 *
 * @param boolean $has_transaction the value coming in from the WP filter
 * @param integer $transaction_id  the transaction ID
 * @param mixed   $user_id         normally the WP user ID but could be something different if changed by an add-on
 *
 * @return bool
 */
function it_exchange_guest_checkout_guest_has_transaction( $has_transaction, $transaction_id, $user_id ) {

	_deprecated_function( __FUNCTION__, '2.0.0' );

	return $has_transaction;
}

/**
 * Returns the customer email for a guest transaction
 *
 * @since 1.6.0
 *
 * @param string $email the email passed through from the WP filter
 * @param mixed  $transaction the id or the object
 *
 * @return string
 */
function it_exchange_get_guest_checkout_transaction_email( $email, $transaction ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	return $email;
}

/**
 * Flags the user as someone who registered as a guest
 *
 * @since 1.6.0
 *
 * @param  object $data        custoemr data
 * @param  int    $customer_id the wp customer_id
 * @return object
 */
function it_exchange_guest_checkout_set_customer_data( $data, $customer_id ) {

	_deprecated_function( __FUNCTION__, '2.0.0' );

	return $data;
}

/**
 * Removes guest checkout transactions from User Purchases
 *
 * If a registerd user checkouts as a guest rather than logging in, the transaction
 * is still attached to them but we don't want to show it to them in their front end profile.
 *
 * @since 1.6.0
 *
 * @param array $args wp post args used for the post query
 *
 * @return array
 */
function it_exchange_guest_checkout_filter_frontend_purchases( $args ) {

	_deprecated_function( __FUNCTION__, '2.0.0' );

	return $args;
}

/**
 * Modifies the Transaction Customer data when dealing with a guest checkout
 *
 * @since 1.6.0
 *
 * @param IT_Exchange_Customer    $customer the customer object
 * @param IT_Exchange_Transaction $transaction
 *
 * @return IT_Exchange_Customer
 */
function it_exchange_guest_checkout_modify_transaction_customer( $customer, $transaction ) {

	_deprecated_function( __FUNCTION__, '2.0.0' );

	if ( ! $transaction->is_guest_purchase() ) {
		return $customer;
	}

	if ( ! $transaction->customer_email ) {
		return $customer;
	}

	$customer = it_exchange_guest_checkout_generate_guest_user_object( $transaction->customer_email, true );

	$customer->wp_user = new stdClass();
	$customer->wp_user->display_name = sprintf( __( 'Guest (%s)', 'it-l10n-ithemes-exchange' ), $customer->ID );

	return $customer;
}

/**
 * Filter email for sending if its false and we're transaction was a guest checkout
 *
 * @since 1.7.12
 *
 * @param string $to_email the email address we're sending it to
 * @param object $transaction the transaction object
 * @return string
 */
function it_exchange_guest_checkout_modify_confirmation_email_address( $to_email, $transaction ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	return $to_email;
}
