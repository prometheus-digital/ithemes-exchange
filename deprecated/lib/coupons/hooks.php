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