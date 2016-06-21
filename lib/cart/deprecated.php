<?php
/**
 * Fire deprecated hooks an contains deprecated cart API methods.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Fire the deprecated quantity hook.
 *
 * @since 1.36
 *
 * @param \ITE_Line_Item            $item
 * @param \ITE_Line_Item|null       $old
 * @param \ITE_Line_Item_Repository $repo
 */
function ite_fire_deprecated_quantity_hook( ITE_Line_Item $item, ITE_Line_Item $old = null, ITE_Line_Item_Repository $repo ) {

	if ( ! $repo instanceof ITE_Line_Item_Session_Repository || $repo instanceof ITE_Line_Item_Cached_Session_Repository ) {
		return;
	}

	if ( ! $old ) {
		return;
	}

	if ( $item->get_quantity() != $old->get_quantity() ) {
		do_action( 'it_exchange_cart_prouduct_count_updated', $item->get_id() );
	}
}

add_action( 'it_exchange_save_product_item', 'ite_fire_deprecated_quantity_hook', 10, 3 );

/**
 * Fire the deprecated add to cart hooks.
 *
 * @since 1.36
 *
 * @param \ITE_Cart_Product $item
 * @param \ITE_Cart         $cart
 */
function ite_fire_deprecated_add_cart_product_hook( ITE_Cart_Product $item, ITE_Cart $cart ) {

	if ( ! $cart->is_current() ) {
		return;
	}

	do_action( 'it_exchange_add_cart_product', $item->get_data_to_save() );
	do_action( 'it_exchange_product_added_to_cart', $item->get_product()->ID );
}

add_action( 'it_exchange_add_product_to_cart', 'ite_fire_deprecated_add_cart_product_hook', 10, 2 );

/**
 * Fire the deprecated update cart product hooks.
 *
 * @since 1.36
 *
 * @param \ITE_Line_Item            $item
 * @param \ITE_Line_Item            $old
 * @param \ITE_Line_Item_Repository $repo
 */
function ite_fire_deprecated_update_cart_product_hook( ITE_Line_Item $item, ITE_Line_Item $old = null, ITE_Line_Item_Repository $repo ) {

	if ( ! $old ) {
		return;
	}

	if ( ! $repo instanceof ITE_Line_Item_Session_Repository ) {
		return;
	}

	do_action( 'it_exchange_update_cart_product',
		$item->get_id(), $item->get_data_to_save(), it_exchange_get_session()->get_session_data( 'products' ) );
}

add_action( 'it_exchange_save_product_item', 'ite_fire_deprecated_update_cart_product_hook', 10, 3 );

/**
 * Fire deprecated delete cart product hook.
 *
 * @since 1.36
 *
 * @param \ITE_Cart_Product $product
 * @param \ITE_Cart         $cart
 */
function ite_fire_deprecated_delete_cart_product_hook( ITE_Cart_Product $product, ITE_Cart $cart ) {

	if ( ! $cart->is_current() ) {
		return;
	}

	do_action( 'it_exchange_delete_cart_product', $product->get_id(), it_exchange_get_session_data( 'products' ) );
}

add_action( 'it_exchange_remove_product_from_cart', 'ite_fire_deprecated_delete_cart_product_hook', 10, 2 );

/* === Deprecate API Functions === */

/**
 * Returns an array of all products in the cart
 *
 * @since      0.3.7
 *
 * @deprecated 1.36.0
 *
 * @param  array $options
 *
 * @return array
 */
function it_exchange_get_cart_products( $options = array() ) {
	if ( empty( $options['use_cached_customer_cart'] ) ) {
		$products = it_exchange_get_session_data( 'products' );
	} else {
		$cart     = it_exchange_get_cached_customer_cart( $options['use_cached_customer_cart'] );
		$products = empty( $cart['products'] ) ? array() : $cart['products'];
	}

	$products = ( empty( $products ) || ! is_array( $products ) ) ? array() : $products;

	return array_filter( $products );
}

/**
 * Inserts product into the cart session
 *
 * @since      0.4.0
 *
 * @deprecated 1.36.0
 *
 * @param string $cart_product_id
 * @param array  $product Cart product data
 *
 * @return void
 */
function it_exchange_add_cart_product( $cart_product_id, $product ) {
	if ( ! empty( $cart_product_id ) && ! empty( $product ) ) {
		it_exchange_add_session_data( 'products', array( $cart_product_id => $product ) );
	}
	do_action( 'it_exchange_add_cart_product', $product );
}

/**
 * Updates product into the cart session
 *
 * @since      0.4.0
 *
 * @deprecated 1.36.0
 *
 * @param string $cart_product_id
 * @param array  $product Cart product data. This must be the entire new data, not a partial diff.
 *
 * @return void
 */
function it_exchange_update_cart_product( $cart_product_id, $product ) {
	if ( ! empty( $cart_product_id ) && ! empty( $product ) ) {
		$products = it_exchange_get_session_data( 'products' );
		if ( isset( $products[ $cart_product_id ] ) ) {
			$products[ $cart_product_id ] = $product;
			it_exchange_update_session_data( 'products', $products );
		} else {
			it_exchange_add_cart_product( $cart_product_id, $product );
		}
		do_action( 'it_exchange_update_cart_product', $cart_product_id, $product, $products );
	}
}

/**
 * Deletes product from the cart session
 *
 * @since      0.4.0
 *
 * @deprecated 1.36.0
 *
 * @param string $cart_product_id
 *
 * @return void
 */
function it_exchange_delete_cart_product( $cart_product_id ) {
	$products = it_exchange_get_session_data( 'products' );
	if ( isset( $products[ $cart_product_id ] ) ) {
		unset( $products[ $cart_product_id ] );
		it_exchange_update_session_data( 'products', $products );
	}
	do_action( 'it_exchange_delete_cart_product', $cart_product_id, $products );
}

/**
 * Returns a specific product from the cart.
 *
 * The returned data is not an iThemes Exchange Product object. It is a cart-product
 *
 * @since      0.3.7
 *
 * @deprecated 1.36.0
 *
 * @param mixed  $id id for the cart's product data
 * @param  array $options
 *
 * @return array|bool
 */
function it_exchange_get_cart_product( $id, $options = array() ) {
	if ( ! $products = it_exchange_get_cart_products( $options ) ) {
		return false;
	}

	if ( empty( $products[ $id ] ) ) {
		return false;
	}

	return apply_filters( 'it_exchange_get_cart_product', $products[ $id ], $id, $options );
}