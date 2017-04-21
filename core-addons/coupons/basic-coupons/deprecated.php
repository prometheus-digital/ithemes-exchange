<?php
/**
 * Deprecated basic coupons code.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Reduces coupon quanity by 1 if coupon was applied to transaction and coupon is tracking usage
 *
 * @since 1.0.2
 *
 * @param integer $transaction_id
 *
 * @return void
 */
function it_exchange_basic_coupons_modify_coupon_quantity_on_transaction( $transaction_id ) {

	_deprecated_function(
		'it_exchange_basic_coupons_modify_coupon_quantity_on_transaction', '1.33',
		'IT_Exchange_Cart_Coupon::modify_quantity_available'
	);

	if ( ! $transaction = it_exchange_get_transaction( $transaction_id ) ) {
		return false;
	}

	if ( ! $coupons = it_exchange_get_transaction_coupons( $transaction ) ) {
		return;
	}

	// Do we have a cart coupon?
	if ( isset( $coupons['cart'] ) && ! empty( $coupons['cart'] ) ) {
		$coupon = reset( $coupons['cart'] );

		// Does this coupon have unlimited quantity
		if ( ! $limited = get_post_meta( $coupon['id'], '_it-basic-limit-quantity', true ) ) {
			return;
		}

		// Does this coupon have a quantity?
		if ( ! $quantity = get_post_meta( $coupon['id'], '_it-basic-quantity', true ) ) {
			return;
		}

		// Decrease quantity by one
		$quantity = absint( $quantity );
		$quantity --;

		update_post_meta( $coupon['id'], '_it-basic-quantity', $quantity );
	}
}

/**
 * Track the customer's use of this coupon on checkout
 *
 * @since 1.9.2
 *
 * @param integer $transaction_id
 *
 * @return void
 */
function it_exchange_basic_coupons_bump_for_customer_on_checkout( $transaction_id ) {

	_deprecated_function(
		'it_exchange_basic_coupons_bump_for_customer_on_checkout', '1.33',
		'IT_Exchange_Cart_Coupon::bump_customer_coupon_frequency'
	);

	if ( ! $transaction = it_exchange_get_transaction( $transaction_id ) ) {
		return false;
	}

	if ( ! $coupons = it_exchange_get_transaction_coupons( $transaction ) ) {
		return;
	}

	// Do we have a cart coupon?
	if ( isset( $coupons['cart'] ) && ! empty( $coupons['cart'] ) ) {
		$coupon = reset( $coupons['cart'] );

		$coupon_id   = $coupon['id'];
		$customer_id = $transaction->customer_id;
		it_exchange_basic_coupons_bump_customer_coupon_frequency( $coupon_id, $customer_id );
	}
}

/**
 * Modify the cart total to reflect coupons
 *
 * @since      0.4.0
 *
 * @deprecated 2.0.0
 *
 * @param float $total
 *
 * @return float
 */
function it_exchange_basic_coupons_apply_discount_to_cart_total( $total ) {

	_deprecated_function( __FUNCTION__, '2.0.0' );

	$total_discount = it_exchange_get_total_coupons_discount( 'cart', array( 'format_price' => false ) );
	$total          = $total - $total_discount;

	return $total;
}

/**
 * Increments coupon use for a specific coupon for a user
 *
 * @since      1.9.2
 *
 * @deprecated 1.33 This function does not handle transient transactions, and makes reversing usage impossible.
 *
 * @param int      $coupon_id   the coupon code.
 * @param int|bool $customer_id The customer id to update. If false, the current customer will be used.
 *
 * @return array
 */
function it_exchange_basic_coupons_bump_customer_coupon_frequency( $coupon_id, $customer_id = false ) {

	_deprecated_function(
		'it_exchange_basic_coupons_bump_customer_coupon_frequency', '1.33',
		'IT_Exchange_Cart_Coupon::bump_customer_coupon_frequency' );

	$customer_id    = empty( $customer_id ) ? it_exchange_get_current_customer_id() : $customer_id;
	$coupon_history = it_exchange_basic_coupons_get_customer_coupon_frequency( false, $customer_id );

	if ( empty( $coupon_history[ $coupon_id ] ) ) {
		$coupon_history[ $coupon_id ] = array( date_i18n( 'U' ) );
	} else {
		$coupon_history[ $coupon_id ][] = date_i18n( 'U' );
	}

	if ( function_exists( 'it_exchange_doing_guest_checkout' ) && it_exchange_doing_guest_checkout() ) {
		update_option( '_it_exchange_basic_coupon_history_' . $customer_id, $coupon_history );
	} else {
		update_user_meta( $customer_id, '_it_exchagne_basic_coupon_history', $coupon_history );
	}
}

/**
 * Register the cart coupon type
 *
 * @since      0.4.0
 *
 * @deprecated 2.0.0
 *
 * @return void
 */
function it_exchange_basic_coupons_register_coupon_type() {

	_deprecated_function( __FUNCTION__, '2.0.0', 'ITE_Coupon_Types::register' );

	it_exchange_register_coupon_type( 'cart', 'IT_Exchange_Cart_Coupon' );
}