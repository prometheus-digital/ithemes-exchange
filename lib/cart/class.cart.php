<?php
/**
 * Shopping cart class. 
 * @since 0.3.8
 * @package IT_Exchange
*/
class IT_Exchange_Shopping_Cart {

	/**
	 * Class constructor.
	 *
	 * Hooks default filters and actions for cart
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function IT_Exchange_Shopping_Cart() {
		add_filter( 'template_redirect', array( $this, 'register_cart_error_messages' ) );
		add_filter( 'template_redirect', array( $this, 'register_cart_notice_messages' ) );
		add_action( 'template_redirect', array( $this, 'handle_it_exchange_cart_function' ) );
	}
	
	/**
	 * Handles $_REQUESTs and submits them to the cart for processing
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function handle_it_exchange_cart_function() {
		
		$this->redirect_checkout_if_empty_cart(); //if on checkout but have empty cart, redirect
		
		// Grab action and process it.
		if ( isset( $_REQUEST['it-exchange-action'] ) ) {
			call_user_func( array( $this, 'handle_' . esc_attr( $_REQUEST['it-exchange-action'] ) . '_request' ) );
			return;
		}

		// Possibly Handle Remove Product Request
		$remove_from_cart_var = it_exchange_get_field_name( 'remove_product_from_cart' );
		if ( ! empty( $_REQUEST[$remove_from_cart_var] ) ) {
			$this->handle_remove_product_from_cart_request();
			return;
		}

		// Possibly Handle Update Cart Request
		$update_cart_var = it_exchange_get_field_name( 'update_cart_action' );
		if ( ! empty( $_REQUEST[$update_cart_var] ) ) {
			$this->handle_update_cart_request();
			return;
		}

		// Possibly Handle Proceed to checkout
		$proceed_var = it_exchange_get_field_name( 'proceed_to_checkout' );
		if ( ! empty( $_REQUEST[$proceed_var] ) ) {
			$this->proceed_to_checkout();
			return;
		}
		
		// Possibly Handle Empty Cart request
		$empty_var = it_exchange_get_field_name( 'empty_cart' );
		if ( ! empty( $_REQUEST[$empty_var] ) ) {
			$this->handle_empty_shopping_cart_request();
			return;
		}
	}

	/**
	 * Listens for $_REQUESTs to add a product to the cart and processes
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function handle_add_product_to_cart_request() {

		$add_to_cart_var = it_exchange_get_field_name( 'add_product_to_cart' );
		$product_id = empty( $_REQUEST[$add_to_cart_var] ) ? 0 : $_REQUEST[$add_to_cart_var];
		$product    = it_exchange_get_product( $product_id );
		$quantity_var    = it_exchange_get_field_name( 'product_purchase_quantity' );
		$requested_quantity = empty( $_REQUEST[$quantity_var] ) ? 1 : absint( $_REQUEST[$quantity_var] );

		// Vefify legit product
		if ( ! $product )
			$error = 'bad-product';

		// Verify nonce
		$nonce_var = apply_filters( 'it_exchange_add_product_to_cart_nonce_var', '_wpnonce' );
		if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it-exchange-add-product-to-cart-' . $product_id ) )
			$error = 'product-not-added-to-cart';

		// Add product
		if ( empty( $error ) && it_exchange_add_product_to_shopping_cart( $product_id, $requested_quantity ) ) {
			$url = add_query_arg( array( it_exchange_get_field_name( 'alert_message' ) => 'product-added-to-cart' ) );
			wp_redirect( $url );
			die();
		}

		$error_var = it_exchange_get_field_name( 'error_message' );
		$error = empty( $error ) ? 'product-not-added-to-cart' : $error;
		$url  = add_query_arg( array( $error_var => $error ), $cart );
		wp_redirect( $url );
		die();
	}

	/**
	 * Empty the iThemes Exchange shopping cart
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function handle_empty_shopping_cart_request() {
		// Verify nonce
		$nonce_var   = apply_filters( 'it_exchange_cart_action_nonce_var', '_wpnonce' );
		$error_var   = it_exchange_get_field_name( 'error_message' );
		$message_var = it_exchange_get_field_name( 'alert_message' );
		$cart        = it_exchange_get_page_url( 'cart' );
		if ( empty( $_REQUEST[$nonce_var] ) 
				|| ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it-exchange-cart-action-' . session_id() ) 
				|| ! it_exchange_empty_shopping_cart()
		) {
			$url  = add_query_arg( array( $error_var => 'cart-not-emptied' ), $cart );
			wp_redirect( $url );
			die();
		} else {
			$url = remove_query_arg( $error_var, $cart );
			$url = add_query_arg( array( $message_var => 'cart-emptied' ), $url );
			wp_redirect( $url );
			die();
		}
	}

	/**
	 * Removes a single product from the shopping cart
	 *
	 * This listens for REQUESTS to remove a product from the cart, verifies the request, and passes it along to the correct function
	 *
	 * @todo Move to /lib/framework dir
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function handle_remove_product_from_cart_request() {
		die('remove_product_from_cart');
		$var        = it_exchange_get_field_name( 'remove_product_from_cart' );
		$product_id = empty( $_REQUEST[$var] ) ? false : $_REQUEST[$var];
		$cart_url   = it_exchange_get_page_url( 'cart' );

		// Verify nonce
		$nonce_var = apply_filters( 'it_exchange_remove_product_from_cart_nonce_var', '_wpnonce' );
		if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it-exchange-remove-product-from-cart-' . $product_id ) || ! it_exchange_remove_product_from_shopping_cart( $product_id ) ) {
			$var = it_exchange_get_field_name( 'error_message' );
			$url  = add_query_arg( array( $var => 'product-not-removed' ), $cart_url );
			wp_redirect( $url );
			die();
		}

		$var = it_exchange_get_field_name( 'alert_message' );
		$url = add_query_arg( array( $var => 'product-removed' ), $cart_url );
		wp_redirect( $url );
		die();
	}

	/**
	 * Listens for the REQUEST to update the shopping cart, verifies it, and calls the correct function
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function handle_update_cart_request() {
		// Verify nonce
		$nonce_var = apply_filters( 'it_exchange_cart_action_nonce_var', '_wpnonce' );
		if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it-exchange-cart-action-' . session_id() ) ) {
			$var = it_exchange_get_field_name( 'error_message' );
			$cart = it_exchange_get_page_url( 'cart' );
			$url  = add_query_arg( array( $var => 'cart-not-updated' ), $cart );
			wp_redirect( $url );
			die();
		}

		// Are we updating any quantities
		$var_name = it_exchange_get_field_name( 'product_purchase_quantity' );
		if ( ! empty( $_REQUEST[$var_name] ) ) {
			foreach( (array) $_REQUEST[$var_name] as $cart_product_id => $quantity ) {
				it_exchange_update_cart_product_quantity( $cart_product_id, $quantity, false );
			}
		}

		do_action( 'it_exchange_update_cart' );

		$message_var = it_exchange_get_field_name( 'alert_message' );
		if ( ! empty ( $message_var ) ) {
			$page = it_exchange_get_page_url( 'cart' );
			$url = add_query_arg( array( $message_var => 'cart-updated' ), $page );
			wp_redirect( $url );
			die();
		}
	}

	/**
	 * Advances the user to the checkout screen after updating the cart
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function proceed_to_checkout() {
		die('checkout');

		// Update cart info
		do_action( 'it_exchange_update_cart', false );

		// Redirect to Checkout
		if ( $checkout = it_exchange_get_page_url( 'checkout' ) ) {
			wp_redirect( $checkout );
			die();
		}
	}

	/**
	 * Process checkout
	 *
	 * Formats data and hands it off to the appropriate tranaction method
	 *
	 * @since 0.3.8
	 * @return boolean 
	*/
	function handle_purchase_cart_request() {

		// Verify nonce
		$nonce_var = apply_filters( 'it_exchange_checkout_action_nonce_var', '_wpnonce' );
		if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it-exchange-checkout-action-' . session_id() ) ) {
			it_exchange_notify_failed_transaction( 'failed-transaction' );
			return false;
		}

		// Verify products exist
		$products = it_exchange_get_cart_products();
		if ( count( $products ) < 1 ) {
			do_action( 'it_exchange_error-no_products_to_purchase' );
			it_exchange_notify_failed_transaction( 'no-products-in-cart' );
			return false;
		}

		// Verify transaction method exists
		$method_var = it_exchange_get_field_name( 'transaction_method' );
		$requested_transaction_method = empty( $_REQUEST[$method_var] ) ? false : $_REQUEST[$method_var];
		$enabled_addons = it_exchange_get_enabled_addons( array( 'category' => 'transaction-methods' ) );
		if ( ! $requested_transaction_method || empty( $enabled_addons[$requested_transaction_method] ) ) {
			do_action( 'it_exchange_error_bad_transaction_method_at_purchase', $requested_transaction_method );
			it_exchange_notify_failed_transaction( 'bad-transaction-method' );
			return false;
		}

		// Verify cart total is a positive number
		$cart_total = number_format( it_exchange_get_cart_total(), 2);
		if ( $cart_total < 0.01 ) {
			do_action( 'it_exchange_error_negative_cart_total_on_checkout', $cart_total );
			it_exchange_notify_failed_transaction( 'negative-cart-total' );
			return false;
		}

		// Add subtotal to each product
		foreach( $products as $key => $product ) {
			$products[$key]['product_baseline'] = it_exchange_get_cart_product_base_price( $product );
			$products[$key]['product_subtotal'] = it_exchange_get_cart_product_subtotal( $product );
			$products[$key]['product_name']     = it_exchange_get_cart_product_title( $product );
		}

		// Package it up and send it to the transaction method add-on
		$transaction_object = new stdClass();
		$transaction_object->products = $products;
		$transaction_object->data     = it_exchange_get_cart_data();
		$transaction_object->total    = $cart_total;

		// Setup actions for success / failure
		add_action( 'it_exchange_add_transaction_success_' . $requested_transaction_method, 'it_exchange_empty_shopping_cart' );
		add_action( 'it_exchange_add_transaction_success_' . $requested_transaction_method, 'it_exchange_do_confirmation_redirect' );
		add_action( 'it_exchange_add_transaction_failed_' . $requested_transaction_method, 'it_exchange_notify_failed_transaction' );

		// Do the transaction
		it_exchange_do_transaction( $requested_transaction_method, $transaction_object );

		// If we made it this far, the transaction failed or the transaction-method add-on did not hook into success/fail actions
		it_exchange_notify_failed_transaction();
	}

	/**
	 * Redirect from checkout to cart if there are no items in the cart
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function redirect_checkout_if_empty_cart() {
		$cart     = it_exchange_get_page_url( 'cart' );
		$checkout = it_exchange_get_page_id( 'checkout' );

		if ( empty( $checkout ) || ! is_page( $checkout ) ) 
			return;

		$products = it_exchange_get_cart_products();
		if ( empty( $products ) ){
			wp_redirect( $cart );
			die();
		}   
	}

	/**
	 * Add errors if needed
	 *
	 * @since 0.3.8
	 * @return array
	*/
	function register_cart_error_messages() {
		$errors['bad-transaction-method'] = __( 'Please select a payment method', 'LION' );
		$errors['failed-transaction']     = __( 'There was an error processing your transaction. Please try again.', 'LION' );
		$errors['negative-cart-total']    = __( 'The cart total must be greater than 0 for you to checkout. Please try again.', 'LION' );
		$errors['no-products-in-cart']    = __( 'You cannot checkout without any items in your cart.', 'LION' );
		$errors['product-not-removed']    = __( 'Product not removed from cart. Please try again.', 'LION' );
		$errors['cart-not-emptied']       = __( 'There was an error emptying your cart. Please try again.', 'LION' );
		$errors['cart-not-updated']       = __( 'There was an error updating your cart. Please try again.', 'LION' );

		foreach( $errors as $var => $error ) {
			if ( ! empty( $_REQUEST[$var] ) ) {
				it_exchange_add_error( $error );
			}
		}
	}

	/**
	 * Register notice messages used with the cart
	 *
	 * @since 0.4.0
	 * @return array
	*/
	function register_cart_notice_messages() {
		$notices['cart-updated']          = __( 'Cart Updated.', 'LION' );
		$notices['cart-emptied']          = __( 'Cart Emptied', 'LION' );
		$notices['product-removed']       = __( 'Product removed from cart.', 'LION' );
		$notices['product-added-to-cart'] = __( 'Product added to cart', 'LION' );

		foreach( $notices as $var => $notice ) {
			if ( ! empty( $_REQUEST[$var] ) )
				it_exchange_add_notice( $notice );
		}
	}
}

if ( ! is_admin() ) {
	$IT_Exchange_Shopping_Cart = new IT_Exchange_Shopping_Cart();
}
