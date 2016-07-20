<?php
/**
 * Hooks that drive coupons.
 *
 * @since   1.33
 * @license GPLv2
 */

/**
 * When a transaction is cancelled, voided, or otherwise is no longer cleared,
 * rever the use of the coupon.
 *
 * @since 1.33
 *
 * @param IT_Exchange_Transaction $transaction
 * @param string                  $old_status
 * @param bool                    $old_cleared
 */
function it_exchange_revert_coupon_use_on_cancellation( IT_Exchange_Transaction $transaction, $old_status, $old_cleared ) {

	if ( $old_cleared && ! it_exchange_transaction_is_cleared_for_delivery( $transaction ) ) {

		$coupons = $transaction->get_coupons();

		if ( empty( $coupons['cart'] ) ) {
			return;
		}

		foreach ( $coupons['cart'] as $coupon_data ) {
			$id = $coupon_data['id'];

			/** @var IT_Exchange_Cart_Coupon $coupon */
			$coupon = it_exchange_get_coupon( $id, 'cart' );

			$coupon->unuse_coupon( $transaction->cart_details );
		}
	}
}

add_action( 'it_exchange_update_transaction_status', 'it_exchange_revert_coupon_use_on_cancellation', 10, 3 );

/**
 * Reapply coupons to the cart if an item is added to the cart.
 *
 * @since 1.36.0
 *
 * @param \ITE_Line_Item $item
 * @param \ITE_Cart      $cart
 */
function it_exchange_reapply_coupons_on_add_item( ITE_Line_Item $item, ITE_Cart $cart ) {

	if ( $item->get_type() === 'coupon' ) {
		return;
	}

	$coupons = $cart->get_items( 'coupon', true )->unique( function ( ITE_Coupon_Line_Item $item ) {
		return $item->get_coupon()->get_type() . $item->get_coupon()->get_code();
	} );

	$cart->get_items( 'coupon', true )->delete();

	/** @var ITE_Coupon_Line_Item $coupon */
	foreach ( $coupons as $coupon ) {
		$cart->add_item( ITE_Coupon_Line_Item::create( $coupon->get_coupon() ) );
	}
}

add_action( 'it_exchange_add_line_item_to_cart', 'it_exchange_reapply_coupons_on_add_item', 10, 2 );

/**
 * Reapply coupons to the cart if an item is removed from the cart.
 *
 * @since 1.36.0
 *
 * @param \ITE_Line_Item $item
 * @param \ITE_Cart      $cart
 */
function it_exchange_reapply_coupons_on_remove_item( ITE_Line_Item $item, ITE_Cart $cart ) {

	$coupons = $cart->get_items( 'coupon', true )->unique( function ( ITE_Coupon_Line_Item $item ) {
		return $item->get_coupon()->get_type() . $item->get_coupon()->get_code();
	} );

	remove_action( 'it_exchange_remove_line_item_from_cart', 'it_exchange_reapply_coupons_on_remove_item', 10, 2 );

	$coupons->delete();

	/** @var ITE_Coupon_Line_Item $coupon */
	foreach ( $coupons as $coupon ) {
		$cart->add_item( ITE_Coupon_Line_Item::create( $coupon->get_coupon() ) );
	}

	add_action( 'it_exchange_remove_line_item_from_cart', 'it_exchange_reapply_coupons_on_remove_item', 10, 2 );
}

//add_action( 'it_exchange_remove_line_item_from_cart', 'it_exchange_reapply_coupons_on_remove_item', 10, 2 );