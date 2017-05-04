<?php
/**
 * Deprecated API functions.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Return the transaction ID provided by the gateway (transaction method)
 *
 * @since 0.4.0
 *
 * @deprecated 2.0.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return string|void
 */
function it_exchange_get_gateway_id_for_transaction( $transaction ) {

	_deprecated_function( __FUNCTION__, '2.0.0', 'it_exchange_get_transaction_method_id' );

	if ( ! $transaction = it_exchange_get_transaction( $transaction ) )
		return;

	$gateway_transaction_id = $transaction->get_method_id();
	return apply_filters( 'it_exchange_get_gateway_id_for_transaction', $gateway_transaction_id, $transaction );
}

/**
 * Updates a transaction
 *
 * @since 0.3.3
 *
 * @deprecated 1.35
 *
 * @param array $args transaction args. Must include ID of a valid transaction post
 *
 * @return WP_Post transaction object
 */
function it_exchange_update_transaction( $args ) {

	_deprecated_function( 'it_exchange_update_transaction', '1.35' );

	$id = empty( $args['id'] ) ? false : $args['id'];
	$id = ( empty( $id ) && ! empty( $args['ID'] ) ) ? $args['ID']: $id;

	if ( 'it_exchange_tran' != get_post_type( $id ) )
		return false;

	$args['ID'] = $id;

	$result = wp_update_post( $args );
	$transaction_method = it_exchange_get_transaction_method( $id );

	do_action( 'it_exchange_update_transaction', $args );
	do_action( 'it_exchange_update_transaction_' . $transaction_method, $args );

	if ( ! empty( $args['_it_exchange_transaction_status'] ) )
		it_exchange_update_transaction_status( $id, $args['_it_exchange_transaction_status'] );

	return $result;
}

/**
 * Add a transient transaction
 *
 * @since 0.4.20
 *
 * @deprecated
 *
 * @param string $method name of method that created the transient
 * @param string $temp_id temporary transaction ID created by the transient
 * @param int|bool $customer_id ID of current customer
 * @param stdClass $transaction_object Object used to pass to transaction methods
 *
 * @return bool true or false depending on success
 */
function it_exchange_add_transient_transaction( $method, $temp_id, $customer_id = false, $transaction_object ) {

	_deprecated_function( 'it_exchange_add_transient_transaction', '1.31', 'it_exchange_update_transient_transaction' );

	return it_exchange_update_transient_transaction( $method, $temp_id, $customer_id, $transaction_object );
}

/**
 * Is the product visible based on start and end availability dates
 *
 * @since 0.4.0
 *
 * @deprecated 1.35
 *
 * @param int|bool $product_id Product ID
 *
 * @return boolean
 */
function it_exchange_is_product_visible( $product_id=false ) {

	_deprecated_function( 'it_exchange_is_product_visible', '1.35', 'it_exchange_is_product_available' );

	if ( ! it_exchange_get_product( $product_id ) )
		return false;

	// Check it has visibility
	if ( it_exchange( 'product', 'has-visibility' ) ) {
		if ( 'hidden' === get_post_meta( $product_id, '_it-exchange-visibility', true ) )
			return apply_filters( 'it_exchange_is_product_visible', false, $product_id );
	}

	return apply_filters( 'it_exchange_is_product_visible', true, $product_id );
}

/**
 * Is cart address valid?
 *
 * @since 1.4.0
 *
 * @return boolean
 */
function it_exchange_is_shipping_address_valid() {

	_deprecated_function( __FUNCTION__, '2.0.0' );

	$cart_address  = it_exchange_get_cart_data( 'shipping-address' );
	$cart_customer = empty( $cart_address['customer'] ) ? 0 : $cart_address['customer'];
	$customer_id   = it_exchange_get_current_customer_id();
	$customer_id   = empty( $customer_id ) ? $cart_customer : $customer_id;

	return (boolean) get_user_meta( $customer_id, 'it_exchange_shipping_address', true );
}

/**
 * Get coupon discount method.
 *
 * Will return false if coupon addon doesn't provide this data
 *
 * @since      0.4.0
 *
 * @deprecated 1.33
 *
 * @param integer $coupon_id the coupon id
 * @param array   $options   optional.
 *
 * @return string|bool
 */
function it_exchange_get_coupon_discount_method( $coupon_id, $options = array() ) {

	_deprecated_function( 'it_exchange_get_coupon_discount_method', '1.33' );

	$options['id'] = $coupon_id;

	return apply_filters( 'it_exchange_get_coupon_discount_method', false, $options );
}

/**
 * Returns currency data
 *
 * Deprecated in 1.2.0.
 *
 * @since      0.3.4
 *
 * @deprecated 1.2.0 Use it_exchange_get_data_set( 'currencies' );
 * @return array
 */
function it_exchange_get_currency_options() {

	_deprecated_function( __FUNCTION__, '1.2.0', 'it_exchange_get_data_set' );

	return it_exchange_get_data_set( 'currencies' );
}

/**
 * Add sale information to the base price in the IT_Theme_API_Product class.
 *
 * @since 1.32.0
 * @deprecated 2.0.0 Moved sale price formatting to Theme API directly. Bypassing issues with repeated information
 *             due to the filter order.
 *
 * @param string $price
 * @param int    $product_id
 *
 * @return string
 */
function it_exchange_add_sale_information_to_base_price_theme( $price, $product_id ) {

	_deprecated_function( __FUNCTION__, '2.0.0' );

	if ( it_exchange_is_product_sale_active( $product_id ) ) {

		$sale_price = it_exchange_get_product_feature( $product_id, 'sale-price' );

		if ( empty( $sale_price ) || $sale_price === 0.00 ) {
			$sale_price = __( 'Free', 'it-l10n-ithemes-exchange' );
		} else {
			$sale_price = it_exchange_format_price( $sale_price );
		}

		remove_filter( 'it_exchange_api_theme_product_base_price', 'it_exchange_add_sale_information_to_base_price_theme', 20 );
		$sale_price = apply_filters( 'it_exchange_api_theme_product_base_price', $sale_price, $product_id );
		add_filter( 'it_exchange_api_theme_product_base_price', 'it_exchange_add_sale_information_to_base_price_theme', 20, 2 );

		$price = "<del>$price</del>&nbsp;";
		$price .= "<ins>$sale_price</ins>";
	}

	return $price;
}