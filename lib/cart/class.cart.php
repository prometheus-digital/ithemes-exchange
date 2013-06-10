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
		add_action( 'template_redirect', array( $this, 'handle_it_exchange_cart_function' ) );
		add_filter( 'it_exchange_process_transaction', array( $this, 'handle_purchase_cart_request' ) );
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
	 * Listens for $_REQUESTs to buy a product now
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function handle_buy_now_request() {

		$buy_now_var = it_exchange_get_field_name( 'buy_now' );
		$product_id = empty( $_REQUEST[$buy_now_var] ) ? 0 : $_REQUEST[$buy_now_var];
		$product    = it_exchange_get_product( $product_id );
		$quantity_var    = it_exchange_get_field_name( 'product_purchase_quantity' );
		$requested_quantity = empty( $_REQUEST[$quantity_var] ) ? 1 : absint( $_REQUEST[$quantity_var] );
		$cart = it_exchange_get_page_url( 'cart' );

		// Vefify legit product
		if ( ! $product )
			$error = 'bad-product';

		// Verify nonce
		$nonce_var = apply_filters( 'it_exchange_purchase_product_nonce_var', '_wpnonce' );
		if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it-exchange-purchase-product-' . $product_id ) )
			$error = 'product-not-added-to-cart';

		// Add product
		if ( empty( $error ) && it_exchange_add_product_to_shopping_cart( $product_id, $requested_quantity ) ) {
			$sw_state = is_user_logged_in() ? 'checkout' : 'login';
			// Get current URL without exchange query args
			$url = clean_it_exchange_query_args();
			$url = ( it_exchange_is_multi_item_cart_allowed() && it_exchange_get_page_url( 'checkout' ) ) ? it_exchange_get_page_url( 'checkout' ) : add_query_arg( 'ite-sw-state', $sw_state, $url ); 
			wp_redirect( $url );
			die();
		}

		$error = empty( $error ) ? 'product-not-added-to-cart' : $error;
		it_exchange_add_message( 'error', __( 'Product not added to cart', 'LION' ) );
		wp_redirect( $url );
		die();
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
		$cart = it_exchange_get_page_url( 'cart' );

		// Vefify legit product
		if ( ! $product )
			$error = 'bad-product';

		// Verify nonce
		$nonce_var = apply_filters( 'it_exchange_purchase_product_nonce_var', '_wpnonce' );
		if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it-exchange-purchase-product-' . $product_id ) )
			$error = 'product-not-added-to-cart';

		// Add product
		if ( empty( $error ) && it_exchange_add_product_to_shopping_cart( $product_id, $requested_quantity ) ) {
			$sw_state = is_user_logged_in() ? 'cart' : 'login';
			// Get current URL without exchange query args
			$url = clean_it_exchange_query_args();
			$url = ( it_exchange_is_multi_item_cart_allowed() && it_exchange_get_page_url( 'cart' ) ) ? it_exchange_get_page_url( 'cart' ) : add_query_arg( 'ite-sw-state', $sw_state, $url ); 
			it_exchange_add_message( 'notice', __( 'Product added to cart', 'LION' ) );
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

		if ( it_exchange_is_multi_item_cart_allowed() )
			$cart = it_exchange_get_page_url( 'cart' );
		else
			$cart = clean_it_exchange_query_args();

		if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it-exchange-cart-action-' . session_id() ) ) {
			$url = add_query_arg( array( $error_var => 'cart-not-emptied' ), $cart );
			$url = remove_query_arg( it_exchange_get_field_name( 'empty_cart' ), $url );
			wp_redirect( $url );
			die();
		}

		it_exchange_empty_shopping_cart();

		$url = remove_query_arg( $error_var, $cart );
		$url = add_query_arg( array( $message_var => 'cart-emptied' ), $url );
		$url = remove_query_arg( it_exchange_get_field_name( 'empty_cart' ), $cart );
		wp_redirect( $url );
		die();
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
		$var             = it_exchange_get_field_name( 'remove_product_from_cart' );
		$car_product_ids = empty( $_REQUEST[$var] ) ? array() : $_REQUEST[$var];

		// Base URL
		if ( it_exchange_is_multi_item_cart_allowed() )
			$cart_url = it_exchange_get_page_url( 'cart' );
		else
			$cart_url = clean_it_exchange_query_args();

		// Verify nonce
		$nonce_var = apply_filters( 'it_exchange_remove_product_from_cart_nonce_var', '_wpnonce' );
		if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it-exchange-cart-action-' . session_id() ) ) {
			$var = it_exchange_get_field_name( 'error_message' );
			$url  = add_query_arg( array( $var => 'product-not-removed' ), $cart_url );
			wp_redirect( $url );
			die();
		}

		foreach( (array) $car_product_ids as $car_product_id ) {
			it_exchange_delete_cart_product( $car_product_id );
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
	function handle_update_cart_request( $redirect=true ) {
		// Verify nonce
		$nonce_var = apply_filters( 'it_exchange_cart_action_nonce_var', '_wpnonce' );
		if ( it_exchange_is_multi_item_cart_allowed() ) {
			$cart = it_exchange_get_page_url( 'cart' );
		} else {
			$cart = clean_it_exchange_query_args( array( it_exchange_get_field_name( 'sw_cart_focus' ) ) );
			if ( it_exchange_in_superwidget() )
				$cart = add_query_arg( 'ite-sw-state', 'cart', $cart );
		}
		if ( empty( $_REQUEST[$nonce_var] ) || ! wp_verify_nonce( $_REQUEST[$nonce_var], 'it-exchange-cart-action-' . session_id() ) ) {
			$var = it_exchange_get_field_name( 'error_message' );

			$url = add_query_arg( array( $var => 'cart-not-updated' ), $cart );
			$url = remove_query_arg( it_exchange_get_field_name( 'empty_cart' ), $url );
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
		if ( ! empty ( $message_var ) && $redirect ) {
			$url = remove_query_arg( $message_var, $cart );
			$url = add_query_arg( array( $message_var => 'cart-updated' ), $url );
			$url = remove_query_arg( it_exchange_get_field_name( 'empty_cart' ), $url );
			
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

		// Update cart info before redirecting. 
		$this->handle_update_cart_request( false );

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
	 * @param bool $status
	 * @return boolean 
	*/
	function handle_purchase_cart_request( $status ) {
		
		if ( $status ) //if this has been modified as true already, return.
			return $status;

		// Verify products exist
		$products = it_exchange_get_cart_products();
		if ( count( $products ) < 1 ) {
			do_action( 'it_exchange_error-no_products_to_purchase' );
			it_exchange_add_message( 'error', $this->get_cart_message( 'no-products-in-cart' ) );
			return false;
		}
		
		// Verify transaction method exists
		$method_var = it_exchange_get_field_name( 'transaction_method' );
		$requested_transaction_method = empty( $_REQUEST[$method_var] ) ? false : $_REQUEST[$method_var];
		$enabled_addons = it_exchange_get_enabled_addons( array( 'category' => 'transaction-methods' ) );
		if ( ! $requested_transaction_method || empty( $enabled_addons[$requested_transaction_method] ) ) {
			do_action( 'it_exchange_error_bad_transaction_method_at_purchase', $requested_transaction_method );
			it_exchange_add_message( 'error', $this->get_cart_message( 'bad-transaction-method' ) );
			return false;
		}

		// Verify cart total is a positive number
		$cart_total = number_format( it_exchange_get_cart_total( false ), 2, '.', '' );
		if ( number_format( $cart_total, 2, '', '' ) < 0 ) {
			do_action( 'it_exchange_error_negative_cart_total_on_checkout', $cart_total );
			it_exchange_add_message( 'error', $this->get_cart_message( 'negative-cart-total' ) );
			return false;
		}

		// Grab default currency
		$settings = it_exchange_get_option( 'settings_general' );
		$currency = $settings['default-currency'];
		unset( $settings );

		// Add totals to each product
		foreach( $products as $key => $product ) {
			$products[$key]['product_base_price'] = it_exchange_get_cart_product_base_price( $product, false );
			$products[$key]['product_subtotal'] = it_exchange_get_cart_product_subtotal( $product, false );
			$products[$key]['product_name']     = it_exchange_get_cart_product_title( $product );
		}

		// Package it up and send it to the transaction method add-on
		$transaction_object = new stdClass();
		$transaction_object->total                  = $cart_total;
		$transaction_object->currency               = $currency;
		$transaction_object->description            = it_exchange_get_cart_description();
		$transaction_object->products               = $products;
		$transaction_object->coupons                = it_exchange_get_applied_coupons();
		$transaction_object->coupons_total_discount = it_exchange_get_total_coupons_discount( 'cart', array( 'format_price' => false ));

		$transaction_object = apply_filters( 'it_exchange_transaction_object', $transaction_object, $requested_transaction_method );

		// Do the transaction
		return it_exchange_do_transaction( $requested_transaction_method, $transaction_object );
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
	 * Gets message for given key
	 *
	 * @since 0.4.0
	 * @param string $key
	 * @return string
	*/
	function get_cart_message( $key ) {
	
		$message = $this->default_cart_messages();
		
		return ( !empty( $message[$key] ) ) ? $message[$key] : __( 'Unknown error. Please try again.', 'LION' );;
		
	}

	/**
	 * Sets up default messages
	 *
	 * @since 0.4.0
	 * @return array
	*/
	function default_cart_messages() {
		$messages['bad-transaction-method'] = __( 'Please select a payment method', 'LION' );
		$messages['failed-transaction']     = __( 'There was an error processing your transaction. Please try again.', 'LION' );
		$messages['negative-cart-total']    = __( 'The cart total must be greater than 0 for you to checkout. Please try again.', 'LION' );
		$messages['no-products-in-cart']    = __( 'You cannot checkout without any items in your cart.', 'LION' );
		$messages['product-not-removed']    = __( 'Product not removed from cart. Please try again.', 'LION' );
		$messages['cart-not-emptied']       = __( 'There was an error emptying your cart. Please try again.', 'LION' );
		$messages['cart-not-updated']       = __( 'There was an error updating your cart. Please try again.', 'LION' );
		$messages['cart-updated']          = __( 'Cart Updated.', 'LION' );
		$messages['cart-emptied']          = __( 'Cart Emptied', 'LION' );
		$messages['product-removed']       = __( 'Product removed from cart.', 'LION' );
		$messages['product-added-to-cart'] = __( 'Product added to cart', 'LION' );
		
		return apply_filters( 'it_exchange_default_cart_messages', $messages );
	}
}

if ( ! is_admin() ) {
	$IT_Exchange_Shopping_Cart = new IT_Exchange_Shopping_Cart();
}
