<?php
/**
 * Fire deprecated hooks an contains deprecated cart API methods.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Save the billing address to the customer's profile when the address for the cart is updated.
 *
 * @since 1.36.0
 *
 * @param \ITE_Cart $cart
 */
function ite_save_main_billing_address_on_current_update( ITE_Cart $cart ) {

	if ( $cart->is_current() && $cart->get_customer() && is_numeric( $cart->get_customer()->ID ) ) {
		it_exchange_save_customer_billing_address( $cart->get_billing_address()->to_array(), $cart->get_customer()->ID );
	}
}

add_action( 'it_exchange_set_cart_billing_address', 'ite_save_main_billing_address_on_current_update' );

/**
 * Save the shipping address to the customer's profile when the address for the cart is updated.
 *
 * @since 1.36.0
 *
 * @param \ITE_Cart $cart
 */
function ite_save_main_shipping_address_on_current_update( ITE_Cart $cart ) {

	if ( $cart->is_current() && $cart->get_customer() && is_numeric( $cart->get_customer()->ID ) ) {
		it_exchange_save_shipping_address( $cart->get_shipping_address()->to_array(), $cart->get_customer()->ID );
	}
}

add_action( 'it_exchange_set_cart_shipping_address', 'ite_save_main_shipping_address_on_current_update' );

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
		do_action_deprecated( 'it_exchange_cart_prouduct_count_updated', array( $item->get_id() ), '1.36.0' );
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

	do_action_deprecated( 'it_exchange_add_cart_product', array( $item->bc() ), '1.36.0' );
	do_action_deprecated( 'it_exchange_product_added_to_cart', array( $item->get_product()->ID ), '1.36.0' );
}

add_action( 'it_exchange_add_product_to_cart', 'ite_fire_deprecated_add_cart_product_hook', 10, 2 );

/**
 * Fire the deprecated update cart product hooks.
 *
 * @since 1.36
 *
 * @param \ITE_Cart_Product         $item
 * @param \ITE_Line_Item            $old
 * @param \ITE_Line_Item_Repository $repo
 */
function ite_fire_deprecated_update_cart_product_hook( ITE_Cart_Product $item, ITE_Line_Item $old = null, ITE_Line_Item_Repository $repo ) {

	if ( ! $old ) {
		return;
	}

	if ( ! $repo instanceof ITE_Line_Item_Session_Repository ) {
		return;
	}

	do_action_deprecated( 'it_exchange_update_cart_product', array(
		$item->get_id(),
		$item->bc(),
		it_exchange_get_session()->get_session_data( 'products' )
	), '1.36.0' );
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

	do_action_deprecated( 'it_exchange_delete_cart_product', array(
		$product->get_id(),
		it_exchange_get_session_data( 'products' )
	), '1.36.0' );
}

add_action( 'it_exchange_remove_product_from_cart', 'ite_fire_deprecated_delete_cart_product_hook', 10, 2 );

/**
 * Fire deprecated empty cart hook.
 *
 * @since 1.36
 *
 * @param \ITE_Cart $cart
 */
function ite_fire_deprecated_empty_cart_hook( ITE_Cart $cart ) {

	if ( $cart->is_current() ) {
		do_action_deprecated( 'it_exchange_before_empty_shopping_cart', array( it_exchange_get_session_data() ), '1.36.0' );
	}
}

add_action( 'it_exchange_empty_cart', 'ite_fire_deprecated_empty_cart_hook' );

/**
 * Fire deprecated emptied cart hook.
 *
 * @since 1.36
 *
 * @param \ITE_Cart $cart
 */
function ite_fire_deprecated_emptied_cart_hook( ITE_Cart $cart ) {

	if ( $cart->is_current() ) {
		do_action_deprecated( 'it_exchange_empty_shopping_cart', array(), '1.36.0' );
	}
}

add_action( 'it_exchange_emptied_cart', 'ite_fire_deprecated_emptied_cart_hook' );

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
	_deprecated_function( __FUNCTION__, '1.36.0' );

	if ( $cart_product_id && $product ) {

		if ( empty( $product['product_id'] ) ) {
			return;
		}

		$item = new ITE_Cart_Product( $cart_product_id, new ITE_Array_Parameter_Bag(
			array_merge( array(
				'count'           => 1,
				'product_name'    => get_the_title( $product['product_id'] ),
				'itemized_data'   => array(),
				'additional_data' => array(),
				'product_cart_id' => $cart_product_id,
				'itemized_hash'   => '',
			), $product )
		), new ITE_Array_Parameter_Bag() );

		it_exchange_get_current_cart()->add_item( $item );
	}

	do_action_deprecated( 'it_exchange_add_cart_product', array( $product ), '1.36.0' );
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
	_deprecated_function( __FUNCTION__, '1.36.0' );

	if ( ! empty( $cart_product_id ) && ! empty( $product ) ) {
		$products = it_exchange_get_session_data( 'products' );
		if ( isset( $products[ $cart_product_id ] ) ) {

			foreach ( $product as $key => $value ) {
				$products[ $cart_product_id ][ $key ] = $value;
			}

			it_exchange_update_session_data( 'products', $products );
		} else {
			it_exchange_add_cart_product( $cart_product_id, $product );
		}
		do_action_deprecated( 'it_exchange_update_cart_product', array(
			$cart_product_id,
			$product,
			$products
		), '1.36.0' );
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
	_deprecated_function( __FUNCTION__, '1.36.0', 'ITE_Cart::remove_item()' );

	$products = it_exchange_get_session_data( 'products' );
	if ( isset( $products[ $cart_product_id ] ) ) {
		unset( $products[ $cart_product_id ] );
		it_exchange_update_session_data( 'products', $products );
	}
	do_action_deprecated( 'it_exchange_delete_cart_product', array( $cart_product_id, $products ), '1.36.0' );
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
 * @return array|false
 */
function it_exchange_get_cart_product( $id, $options = array() ) {
	if ( ! $products = it_exchange_get_cart_products( $options ) ) {
		return false;
	}

	if ( empty( $products[ $id ] ) ) {
		return false;
	}

	return apply_filters_deprecated( 'it_exchange_get_cart_product', array(
		$products[ $id ],
		$id,
		$options
	), '1.36.0' );
}
