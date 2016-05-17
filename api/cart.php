<?php
/**
 * This file contains functions intended for theme developers to interact with the active shopping cart plugin
 *
 * The active shopping cart plugin should add the needed hooks below within its codebase.
 *
 * @since 0.3.7
 * @package IT_Exchange
*/

/**
 * Returns an array of all data in the cart
 *
 * @since 0.3.7
 *
 * @param  string|bool $key the identifying string for the data being requested
 * @param  array  $options {
 *      An array of possible options passed to the function
 *
 *      @type mixed $use_cached_customer_cart If contains a customer ID, we grab cart
 *                                            data from the cached cart
 * }
 *
 * @return array
*/
function it_exchange_get_cart_data( $key = false, $options=array() ) {
	// Grab cart data from the session or from the cached cart based on options
	if ( empty( $options['use_cached_customer_cart'] ) ) {
		$data = it_exchange_get_session_data( $key );
	} else {
		$data = it_exchange_get_cached_customer_cart( $options['use_cached_customer_cart'] );
	}

	// Pass through filter and return
	return apply_filters( 'it_exchange_get_cart_data', $data, $key, $options );
}

/**
 * Updates the data
 *
 * @since 0.4.0
 *
 * @param string $key
 * @param array  $data Data will always be converted to an array before storage.
 *
 * @return void
*/
function it_exchange_update_cart_data( $key, $data ) {
	it_exchange_update_session_data( $key, $data );
	do_action( 'it_exchange_update_cart_data', $data, $key );
}

/**
 * Removes cart data by key
 *
 * @since 0.4.0
*/
function it_exchange_remove_cart_data( $key ) {
	it_exchange_clear_session_data( $key );
	do_action( 'it_exchange_remove_cart_data', $key );
}

/**
 * Returns an array of all products in the cart
 *
 * @since 0.3.7
 *
 * @param  array $options {
 *      An array of possible options passed to the function
 *
 *      @type mixed $use_cached_customer_cart If contains a customer ID, we grab cart
 *                                            products from the cached cart
 * }
 *
 * @return array
*/
function it_exchange_get_cart_products( $options=array() ) {
	if ( empty( $options['use_cached_customer_cart'] ) ) {
		$products = it_exchange_get_session_data( 'products' );
	} else {
		$cart = it_exchange_get_cached_customer_cart( $options['use_cached_customer_cart'] );
		$products = empty( $cart['products'] ) ? array() : $cart['products'];
	}

	$products = ( empty( $products ) || ! is_array( $products ) ) ? array() : $products;

	return array_filter( $products );
}

/**
 * Inserts product into the cart session
 *
 * @since 0.4.0
 *
 * @param string $cart_product_id
 * @param array  $product Cart product data
 *
 * @return void
*/
function it_exchange_add_cart_product( $cart_product_id, $product ) {
	if ( !empty( $cart_product_id ) && !empty( $product ) ) {
		it_exchange_add_session_data( 'products', array( $cart_product_id => $product ) );
	}
	do_action( 'it_exchange_add_cart_product', $product );
}

/**
 * Updates product into the cart session
 *
 * @since 0.4.0
 *
 * @param string $cart_product_id
 * @param array  $product Cart product data. This must be the entire new data, not a partial diff.
 *
 * @return void
*/
function it_exchange_update_cart_product( $cart_product_id, $product ) {
	if ( !empty( $cart_product_id ) && !empty( $product ) ) {
		$products = it_exchange_get_session_data( 'products' );
		if ( isset( $products[$cart_product_id] ) ) {
			$products[$cart_product_id] = $product;
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
 * @since 0.4.0
 *
 * @param string $cart_product_id
 *
 * @return void
*/
function it_exchange_delete_cart_product( $cart_product_id ) {
	$products = it_exchange_get_session_data( 'products' );
	if ( isset( $products[$cart_product_id] ) ) {
		unset( $products[$cart_product_id] );
		it_exchange_update_session_data( 'products', $products );
	}
	do_action( 'it_exchange_delete_cart_product', $cart_product_id, $products );
}

/**
 * Returns a specific product from the cart.
 *
 * The returned data is not an iThemes Exchange Product object. It is a cart-product
 *
 * @since 0.3.7
 *
 * @param mixed $id id for the cart's product data
 * @param  array $options {
 *      An array of possible options passed to the function
 *
 *      @type mixed $use_cached_customer_cart If contains a customer ID, we grab cart
 *                                            products from the cached cart
 * }
 *
 * @return array|bool
*/
function it_exchange_get_cart_product( $id, $options=array() ) {
	if ( ! $products = it_exchange_get_cart_products( $options ) )
		return false;

	if ( empty( $products[$id] ) )
		return false;

	return apply_filters( 'it_exchange_get_cart_product', $products[$id], $id, $options );
}

/**
 * Checks if the current product being viewed is in the cart
 *
 * @since 0.4.10
 *
 * @return bool true if in cart|false if not
*/
function it_exchange_is_current_product_in_cart() {
	$product_id    = false;
	$in_cart       = false;
	$cart_products = it_exchange_get_cart_products();
	$product       = it_exchange_get_the_product_id();

	if ( ! empty( $product ) ) {
		$product_id = $product;
	} else if ( ! empty( $_GET['sw-product'] ) ) {
		$product_id = $_GET['sw-product'];
	}

	$in_cart = it_exchange_is_product_in_cart( $product_id );

	$in_cart = apply_filters( 'it_exchange_is_current_product_in_cart', $in_cart, $product_id, $product, $cart_products );
	return $in_cart;
}

/**
 * Check if a product is in the cart.
 *
 * @since 1.32
 *
 * @param int $product_id
 *
 * @return bool
 */
function it_exchange_is_product_in_cart( $product_id ) {

	$in_cart       = false;
	$cart_products = it_exchange_get_cart_products();

	foreach( $cart_products as $cart_product ) {
		if ( ! empty( $cart_product['product_id'] ) && $product_id == $cart_product['product_id'] ) {
			$in_cart = true;

			break;
		}
	}

	return apply_filters( 'it_exchange_is_product_in_cart', $in_cart, $product_id, $cart_products );
}

/**
 * Adds a product to the shopping cart based on the product_id
 *
 * @since 0.3.7
 * @since 1.35 Add $return_cart_id parameter.
 *
 *
 * @param string $product_id a valid wp post id with an iThemes Exchange product post_typp
 * @param int $quantity (optional) how many?
 * @param bool $return_cart_id
 *
 * @return boolean|string Cart ID if $return_cart_id is true.
*/
function it_exchange_add_product_to_shopping_cart( $product_id, $quantity = 1, $return_cart_id = false ) {

	if ( ! $product_id )
		return false;

	if ( ! $product = it_exchange_get_product( $product_id ) )
		return false;

	$quantity = absint( (int) $quantity );
	if ( $quantity < 1 )
		$quantity = 1; //we're going to assume they want at least 1 item

	/**
	 * The default shopping cart organizes products in the cart by product_id and a hash of 'itemized_data'.
	 * Any data like product variants or pricing mods that should separate products in the cart can be passed through this filter.
	*/
	$itemized_data = apply_filters( 'it_exchange_add_itemized_data_to_cart_product', array(), $product_id );

	if ( ! is_serialized( $itemized_data ) )
		$itemized_data = maybe_serialize( $itemized_data );
	$itemized_hash = md5( $itemized_data );

	/**
	 * Any data that needs to be stored in the cart for this product but that should not trigger a new itemized row in the cart
	*/
	$additional_data = apply_filters( 'it_exchange_add_additional_data_to_cart_product', array(), $product_id );
	if ( ! is_serialized( $additional_data ) )
		$additional_data = maybe_serialize( $additional_data );

	// Grab existing session products
	$session_products = it_exchange_get_cart_products();

	// Grab the cart ID or set it to false if no products exist
	$existing_cart_id = empty( $session_products ) ? false : it_exchange_get_cart_id();

	/**
	 * If multi-item carts are allowed, don't do antying here.
	 * If multi-item carts are NOT allowed and this is a different item, empty the cart before proceeding.
	 * If item being added to cart is already in cart, preserve that item so that quanity will be bumpped.
	*/
	$multi_item_product_allowed = it_exchange_is_multi_item_product_allowed( $product_id );
	if ( ! it_exchange_is_multi_item_cart_allowed() || ! $multi_item_product_allowed ) {
		if ( ! empty( $session_products ) ) {
			// Preserve the current item being added if its already in the cart
			if ( ! empty( $session_products[$product_id . '-' . $itemized_hash] ) )
				$preserve_for_quantity_bump = $session_products[$product_id . '-' . $itemized_hash];

			// Empty the cart to ensure only one item
			it_exchange_empty_shopping_cart();

			// Add the existing item back if found
			if ( ! empty( $preserve_for_quantity_bump ) )
				it_exchange_add_cart_product( $preserve_for_quantity_bump['product_cart_id'], $preserve_for_quantity_bump );

			// Reset the session products
			$session_products = it_exchange_get_cart_products();
		}
	}

	// If product is in cart already, bump the quanity. Otherwise, add it to the cart
	if ( ! empty ($session_products[$product_id . '-' . $itemized_hash] ) ) {
		$res = it_exchange_update_cart_product_quantity( $product_id . '-' . $itemized_hash, $quantity );

		return $return_cart_id ? $product_id . '-' . $itemized_hash : $res;
	} else {

		// If we don't support purchase quanity, quantity will always be 1
		if ( $product->supports_feature( 'purchase-quantity' ) && $multi_item_product_allowed ) {

			$max_purchase_quantity = it_exchange_get_max_product_quantity_allowed( $product );

			$max_purchase_quantity = apply_filters( 'it_exchange_max_purchase_quantity_cart_check', $max_purchase_quantity,
				$product_id, $itemized_data, $additional_data, $itemized_hash
			);

			if ( $max_purchase_quantity !== '' && $quantity > $max_purchase_quantity ) {
				$count = $max_purchase_quantity;
			} else {
				$count = $quantity;
			}

		} else {
			$count = 1;
		}

		if ( $count < 1 ) {
			$count = 1;
		}

		$product = array(
			'product_cart_id' => $product_id . '-' . $itemized_hash,
			'product_id'      => $product_id,
			'itemized_data'   => $itemized_data,
			'additional_data' => $additional_data,
			'itemized_hash'   => $itemized_hash,
			'count'           => $count,
		);

		// Actually add product to the cart
		it_exchange_add_cart_product( $product_id . '-' . $itemized_hash, $product );

		// If no unique cart ID exists, create one.
		it_exchange_update_cart_id( $existing_cart_id );

		do_action( 'it_exchange_product_added_to_cart', $product_id );

		return $return_cart_id ? $product_id . '-' . $itemized_hash : true;
	}
}

/**
 * Updates the quantity for a specific cart item
 *
 * @since 0.4.0

 * @param int $cart_product_id the product ID prepended to the itemized hash by a hyphen
 * @param int $quantity the incoming quantity
 * @param boolean $add_to_existing if set to false, it replaces the existing.
 *
 * @return bool|void
*/
function it_exchange_update_cart_product_quantity( $cart_product_id, $quantity, $add_to_existing = true ) {
	// Get cart products
	$cart_products = it_exchange_get_cart_products();

	// Update Quantity
	if ( ! empty( $cart_products[$cart_product_id] ) && is_numeric( $quantity ) ) {
		$cart_product = $cart_products[$cart_product_id];
		if ( empty( $quantity ) || $quantity < 1 ) {
			it_exchange_delete_cart_product( $cart_product_id );
		} else {

			// If we don't support purchase quanity, quanity will always be 1
			if ( it_exchange_product_supports_feature( $cart_product['product_id'], 'purchase-quantity' ) && it_exchange_is_multi_item_product_allowed( $cart_product['product_id'] ) ) {

				if ( ! $add_to_existing ) {
					$cart_product['count'] = 0;
				}

				$max_purchase_quantity = it_exchange_get_max_product_quantity_allowed( $cart_product['product_id'], $cart_product_id );

				$new_count = $cart_product['count'] + $quantity;

				if ( $max_purchase_quantity !== '' && $new_count > $max_purchase_quantity ) {
					$new_count = $max_purchase_quantity;
				}

				$cart_product['count'] = $new_count;
			} else {
				$cart_product['count'] = 1;
			}

			it_exchange_update_cart_product( $cart_product_id, $cart_product );
			do_action( 'it_exchange_cart_prouduct_count_updated', $cart_product_id );
			return true;
		}
	}
}

/**
 * Get the max product quantity that is allowed to be purchased.
 *
 * @since 1.35
 *
 * @param int|WP_Post|IT_Exchange_Product $product
 * @param string                          $cart_product_id
 *
 * @return int|string Empty string if max product quantity allowed is unlimited.
 */
function it_exchange_get_max_product_quantity_allowed( $product, $cart_product_id = '' ) {

	$product = it_exchange_get_product( $product );

	// If we don't support purchase quanity, quantity will always be 1
	if ( ! $product->supports_feature( 'purchase-quantity' ) ) {
		// This filter is documented in api/cart.php
		return apply_filters( 'it_exchange_get_max_product_quantity_allowed', 1, $product, $cart_product_id );
	}

	// Get max quantity setting
	$max_purchase_quantity = $product->get_feature( 'purchase-quantity' );
	$max_purchase_quantity = trim( $max_purchase_quantity );

	$supports_inventory = $product->supports_feature( 'inventory' );
	$inventory = $product->get_feature( 'inventory' );

	if ( $supports_inventory && $max_purchase_quantity === '' ) {
		$max_purchase_quantity = $inventory;
	} else if ( $supports_inventory && $inventory && (int) $max_purchase_quantity > 0 && (int) $max_purchase_quantity > $inventory ) {
		$max_purchase_quantity = $inventory;
	}

	if ( $inventory && $max_purchase_quantity > $inventory ) {
		$allowed = $inventory;
	} else {
		$allowed = $max_purchase_quantity;
	}

	/**
	 * Filter the maximum product quantity allowed to be purchased.
	 * 
	 * @since 1.35.7
	 *        
	 * @param int                 $allowed          Maximum quantity allowed.
	 * @param IT_Exchange_Product $product          Product being purchased.
	 * @param string              $cart_product_id  Cart product ID. May be empty if new purchase request.
	 */
	return apply_filters( 'it_exchange_get_max_product_quantity_allowed', $allowed, $product, $cart_product_id );
}

/**
 * Empties the cart
 *
 * @since 0.3.7
 *
 * @return void
*/
function it_exchange_empty_shopping_cart() {
	do_action( 'it_exchange_before_empty_shopping_cart', it_exchange_get_session_data() );
	it_exchange_clear_session_data( 'products' );
	do_action( 'it_exchange_empty_shopping_cart' );
}

/**
 * Caches the user's cart in user meta if they are logged in
 *
 * @since 1.9.0
 *
 * @param int|bool $customer_id
 *
 * @return void
*/
function it_exchange_cache_customer_cart( $customer_id=false ) {
	// Grab the current customer
	$customer = empty( $customer_id ) ? it_exchange_get_current_customer() : it_exchange_get_customer( $customer_id );

	// Abort if we don't have a logged in customer
	if ( empty( $customer->id ) )
		return;

	$cart_data = it_exchange_get_cart_data();

	update_user_meta( $customer->id, '_it_exchange_cached_cart', $cart_data );

	do_action( 'it_exchange_cache_customer_cart', $customer, $cart_data );
}

/**
 * Get a customer's cached cart if they are logged in
 *
 * @since 1.9.0
 *
 * @param  int|bool $customer_id the id of an exchange customer
 *
 * @return array|bool
*/
function it_exchange_get_cached_customer_cart( $customer_id=false ) {
	// Grab the current customer
	$customer = empty( $customer_id ) ? it_exchange_get_current_customer() : it_exchange_get_customer( $customer_id );

	// Abort if we don't have a logged in customer
	if ( empty( $customer->id ) )
		return false;

	// Grab the data
	$cart = get_user_meta( $customer->id, '_it_exchange_cached_cart', true );

	return apply_filters( 'it_exchange_get_chached_customer_cart', $cart, $customer->id );
}

/**
 * Add a session ID to the list of active customer cart sessions
 *
 * @since 1.9.0
 *
 * @return void|bool
*/
function it_exchange_add_current_session_to_customer_active_carts( $customer_id=false ) {

	$customer_id = empty( $customer_id ) ? it_exchange_get_current_customer_id() : $customer_id;

	// Grab the current customer
	$customer = it_exchange_get_customer( $customer_id );

	// Abort if we don't have a logged in customer
	if ( empty( $customer->id ) )
		return false;

	// Get the current customer's session ID
	$current_session_string  = it_exchange_get_session_id();
	$current_session_parts   = explode( '||', $current_session_string );
	$current_session_id      = empty( $current_session_parts[0] ) ? false : $current_session_parts[0];
	$current_session_expires = empty( $current_session_parts[1] ) ? false : $current_session_parts[1];

	// Get all active carts for customer (across devices / browsers )
	$active_carts = it_exchange_get_active_carts_for_customer( false, $customer->id );

	// Add or update current session data to active sessions
	if ( ! isset( $active_carts[$current_session_id] ) || ( isset( $active_carts[$current_session_id] ) && $active_carts[$current_session_id] < time() ) ) {
		$active_carts[$current_session_id] = $current_session_expires;

		// Update user meta
		if ( empty( $_GLOBALS['it_exchange']['logging_out_user'] ) ) {
			update_user_meta( $customer->id, '_it_exchange_active_user_carts', $active_carts );
		}
	}

}

/**
 * Remove session from a customer's active carts
 *
 * @since 1.9.0
 *
 * @return void
*/
function it_exchange_remove_current_session_from_customer_active_carts() {
	// This works because it doesn't return the current cart in the list of active carts
	$active_carts = it_exchange_get_active_carts_for_customer();
	update_user_meta( it_exchange_get_current_customer_id(), '_it_exchange_active_user_carts', $active_carts );
}

/**
 * Grabs current active Users carts
 *
 * @since @1.9.0
 *
 * @param boolean $include_current_cart defaults to false
 * @param int $customer_id optional. uses current customer id if null
 *
 * @return array
*/
function it_exchange_get_active_carts_for_customer( $include_current_cart=false, $customer_id=null ) {
	// Get the customer
	$customer = is_null( $customer_id ) ? it_exchange_get_current_customer() : it_exchange_get_customer( $customer_id );

	// Abort if we don't have a logged in customer
	if ( empty( $customer->id ) )
		return apply_filters( 'it_exchange_get_active_carts_for_customer', array(), $customer_id );

	// Get current session ID
	$current_session_string = it_exchange_get_session_id();
	$current_session_parts  = explode( '||', $current_session_string );
	$current_session_id     = empty( $current_session_parts[0] ) ? false : $current_session_parts[0];
	$current_session_exp    = empty( $current_session_parts[1] ) ? false : $current_session_parts[1];

	// Grab saved active sessions from user meta
	$active_carts = get_user_meta( $customer->id, '_it_exchange_active_user_carts', true );

	// If active_carts is false, this is probably the first call with no previously active carts, so add the current one.
	if ( empty( $active_carts ) )
		$active_carts = array( $current_session_id => $current_session_exp );

	// Current time
	$time = time();

	// Loop through active sessions
	foreach( (array) $active_carts as $session_id => $expires ) {
		// Remove expired carts
		if ( $time > $expires )
			unset( $active_carts[$session_id] );
	}

	// Remove current cart if not needed
	if ( empty( $include_current_cart ) && isset( $active_carts[$current_session_id] ) )
		unset( $active_carts[$current_session_id] );

	return apply_filters( 'it_exchange_get_active_carts_for_customer', $active_carts, $customer_id );
}

/**
 * Loads a cached cart into active session
 *
 * @since 1.9.0
 *
 * @param $user_login string
 * @param $user WP_User
 *
 * @return bool|void
*/
function it_exchange_merge_cached_customer_cart_into_current_session( $user_login, $user ) {
	// Grab the current customer
	$customer = it_exchange_get_customer( $user->ID );

	// Abort if we don't have a logged in customer
	if ( empty( $customer->id ) )
		return false;

	// Current Cart Products prior to merge
	$current_products = it_exchange_get_cart_products();

	// Grab cached cart data and insert into current sessio
	$cached_cart = it_exchange_get_cached_customer_cart( $customer->id );

	/**
	 * Loop through data. Override non-product data.
	 * If product exists in current cart, bump the quantity
	*/
	foreach( (array) $cached_cart as $key => $data ) {
		if ( 'products' != $key || empty( $current_products ) ) {
			it_exchange_update_cart_data( $key, $data );
		} else {
			foreach( (array) $data as $product_id => $product_data ) {
				if ( ! empty( $current_products[$product_id]['count'] ) ) {
					$data[$product_id]['count'] = $current_products[$product_id]['count'];
					unset( $current_products[$product_id] );
				}
			}
			// If current products hasn't been absored into cached cart, tack it to cached cart and load cached cart into current session
			if ( is_array( $current_products ) && ! empty ( $current_products ) ) {
				foreach( $current_products as $product_id => $product_atts ) {
					$data[$product_id] = $product_atts;
				}
			}

			it_exchange_update_cart_data( 'products', $data );
		}
	}

	// This is a new customer session after loggin in so add this session to active carts
	it_exchange_add_current_session_to_customer_active_carts( $customer->id );

	// If there are items in the cart, cache and sync
	if ( it_exchange_get_cart_products() ) {
		it_exchange_cache_customer_cart( $customer->id );
		it_exchange_sync_current_cart_with_all_active_customer_carts();
	}
}

/**
 * Syncs the current cart with all other active carts
 *
 * @since 1.9.0
 *
 * @return void
*/
function it_exchange_sync_current_cart_with_all_active_customer_carts() {
	$active_carts      = it_exchange_get_active_carts_for_customer();
	$current_cart_data = it_exchange_get_cart_data();

	// Sync across browsers and devices
    foreach( (array) $active_carts as $session_id => $expiration ) {
        update_option( '_it_exchange_db_session_' . $session_id, $current_cart_data );
    }
}

/**
 * Are multi item carts allowed?
 *
 * Default is no. Addons must tell us yes as well as provide any pages needed for a cart / checkout / etc.
 *
 * @since 0.4.0
 *
 * @return boolean
*/
function it_exchange_is_multi_item_cart_allowed() {
	return apply_filters( 'it_exchange_multi_item_cart_allowed', false );
}

/**
 * Is this product allowed to be added to a multi-item cart?
 *
 * Default is true.
 *
 * @since 1.3.0
 *
 * @param $product_id int
 *
 * @return boolean
*/
function it_exchange_is_multi_item_product_allowed( $product_id ) {
	return apply_filters( 'it_exchange_multi_item_product_allowed', true, $product_id );
}

/**
 * Returns the title for a cart product
 *
 * Other add-ons may need to modify the DB title to reflect variants / etc
 *
 * @since 0.3.7
 * @param array $product cart product
 *
 * @return string product title
*/
function it_exchange_get_cart_product_title( $product ) {
	if ( ! $db_product = it_exchange_get_product( $product['product_id'] ) )
		return false;

	$title = get_the_title( $db_product->ID );
	return apply_filters( 'it_exchange_get_cart_product_title', $title, $product );
}

/**
 * Returns the quantity for a cart product
 *
 * @since 0.3.7
 * @param array $product cart product
 *
 * @return integer quantity
*/
function it_exchange_get_cart_product_quantity( $product ) {
	$count = empty( $product['count'] ) ? 0 : $product['count'];
	return apply_filters( 'it_exchange_get_cart_product_quantity', $count, $product );
}

/**
 * Returns the quantity for a cart product.
 *
 * Caution: This will return unexpected results for variants
 * if more than one variant of a product is in the cart.
 *
 * @since 0.4.4
 *
*@param int $product_id
 *
 * @return integer quantity
*/
function it_exchange_get_cart_product_quantity_by_product_id( $product_id ) {
	$products = it_exchange_get_cart_products();

	foreach ( $products as $product ) {
		if ( !empty( $product['product_id'] ) && $product['product_id'] == $product_id ) {
			return $product['count'];
		}
	}

	return 0;
}

/**
 * Returns the number of items in the cart
 * Now including quantity for individual items w/ true_count flag
 *
 * @since 0.4.0
 *
 * @param bool $true_count Whether or not to traverse cart products to get true count of items
 * @param bool|string $feature only include products with this feature
 * @return integer
*/
function it_exchange_get_cart_products_count( $true_count=false, $feature=false ) {
	$products = it_exchange_get_cart_products();
	$count = 0;
	if ( $true_count ) {
		foreach( $products as $product ) {
			if ( empty( $product['product_id'] ) || empty( $product['count'] ) ) {
				continue;
			}

			if ( ! empty( $feature ) && ! it_exchange_product_has_feature( $product['product_id'], $feature ) ) {
				continue;
			}

			$count += $product['count'];
		}
		return absint( $count );
	} else {
		foreach( $products as $product ) {
			if ( ! empty( $feature ) && ! it_exchange_product_has_feature( $product['product_id'], $feature ) ) {
				continue;
			}
			$count++;
		}
		return absint( $count );
	}
}

/**
 * Return total weight for cart
 *
 * @since 1.11.0
 *
 * @return float
*/
function it_exchange_get_cart_weight() {
	$weight = 0;
	$products = it_exchange_get_cart_products();
	if ( !empty( $products ) ) {
		foreach( $products as $product ) {
	        $pm = get_post_meta( $product['product_id'], '_it_exchange_core_weight', true );
			$weight += empty( $pm['weight'] ) ? 0 : ( $pm['weight'] * $product['count'] );
		}
	}
	return is_numeric( $weight ) ? $weight : 0;
}

/**
 * Returns the base_price for the cart product
 *
 * Other add-ons may modify this on the fly based on the product's itemized_data and additional_data arrays
 *
 * @since 0.3.7
 * @param array $product cart product
 * @param bool $format
 *
 * @return integer|string price
*/
function it_exchange_get_cart_product_base_price( $product, $format=true ) {
	if ( empty( $product['product_id'] ) || ! ( $db_product = it_exchange_get_product( $product['product_id'] ) ) )
		return false;

	// Get the price from the DB
	$db_base_price = it_exchange_get_product_feature( $db_product->ID, 'base-price' );

	if ( $format )
		$db_base_price = it_exchange_format_price( $db_base_price );

	return apply_filters( 'it_exchange_get_cart_product_base_price', $db_base_price, $product, $format );
}

/**
 * Returns the subtotal for a cart product
 *
 * Base price multiplied by quantity and then passed through a filter
 *
 * @since 0.3.7
 * @param array $product cart product
 * @param bool $format
 *
 * @return int|string subtotal
*/
function it_exchange_get_cart_product_subtotal( $product, $format=true ) {
	if ( empty( $product['count'] ) ) {
		$subtotal_price = 0;
	} else {
		$base_price = it_exchange_get_cart_product_base_price( $product, false );
		$subtotal_price = apply_filters( 'it_exchange_get_cart_product_subtotal', $base_price * $product['count'], $product );
	}

	if ( $format )
		$subtotal_price = it_exchange_format_price( $subtotal_price );

	return $subtotal_price;
}

/**
 * Returns the cart subtotal
 *
 * @since 0.3.7
 * @since 1.33 Add support for limiting subtotal to products with a feature.

 * @param boolean $format should we format the price
 * @param  array  $options {
 *      An array of possible options passed to the function
 *
 *      @type bool $use_cached_customer_cart If contains a customer ID, we grab cart
 *                                            data from the cached cart
 *      @type string $feature                Limit to products with this feature.
 * }
 *
 * @return mixed subtotal of cart
*/
function it_exchange_get_cart_subtotal( $format=true, $options=array() ) {
	$subtotal = 0;
	if ( ! $products = it_exchange_get_cart_products( $options ) )
		return 0;

	foreach( (array) $products as $product ) {

		if ( empty( $options['feature'] ) || it_exchange_product_has_feature( $product['product_id'], $options['feature'] ) ) {
			$subtotal += it_exchange_get_cart_product_subtotal( $product, false );
		}
	}
	$subtotal = apply_filters( 'it_exchange_get_cart_subtotal', $subtotal, $options );

	if ( $format )
		$subtotal = it_exchange_format_price( $subtotal );

	return $subtotal;
}

/**
 * Returns the cart total
 *
 * The cart total is essentially going to be the sub_total plus whatever modifications other add-ons make to it.
 * eg: taxes, shipping, discounts, etc.
 *
 * @since 0.3.7
 *
 * @param boolean $format should we format the price
 * @param  array  $options {
 *      An array of possible options passed to the function
 *
 *      @type mixed $use_cached_customer_cart If contains a customer ID, we grab cart
 *                                            data from the cached cart
 * }
 *
 * @return mixed total of cart
*/
function it_exchange_get_cart_total( $format=true, $options=array() ) {
	$total = apply_filters( 'it_exchange_get_cart_total', it_exchange_get_cart_subtotal( false, $options ) );

	if ( 0 > $total )
		$total = 0;

	if ( $format )
		$total = it_exchange_format_price( $total );

	return $total;
}

/**
 * Returns the cart description
 *
 * The cart description is essentailly going to be a list of all products being purchased
 *
 * @since 0.4.0
 *
 * @param  array  $options {
 *      An array of possible options passed to the function
 *
 *      @type mixed $use_cached_customer_cart If contains a customer ID, we grab cart
 *                                            data from the cached cart
 * }
 *
 * @return string description
*/
function it_exchange_get_cart_description( $options=array() ) {
	$description = array();
	if ( ! $products = it_exchange_get_cart_products( $options ) )
		return 0;

	foreach( (array) $products as $product ) {
		$string = it_exchange_get_cart_product_title( $product );
		if (  1 < $count = it_exchange_get_cart_product_quantity( $product ) )
			$string .= ' (' . $count . ')';
		$description[] = apply_filters( 'it_exchange_get_cart_description_for_product', $string, $product );
	}
	$description = apply_filters( 'it_exchange_get_cart_description', implode( ', ', $description ), $description, $options );

	return $description;
}

/**
 * Redirect to confirmation page after successfull transaction
 *
 * @since 0.3.7
 * @param integer $transaction_id the transaction id
 *
 * @return void
*/
function it_exchange_do_confirmation_redirect( $transaction_id ) {
	$confirmation_url = it_exchange_get_page_url( 'confirmation' );
	$transaction_var  = it_exchange_get_field_name( 'transaction_id' );
	$confirmation_url = add_query_arg( array( $transaction_var => $transaction_id ), $confirmation_url );

	$redirect_options = array( 'transaction_id' => $transaction_id, 'transaction_var' => $transaction_var );
	it_exchange_redirect( $confirmation_url, 'confirmation-redirect', $redirect_options );
	die();
}

/**
 * Returns the nonce field for the cart
 *
 * @since 0.4.0
 *
 * @return string
*/
function it_exchange_get_cart_nonce_field() {
	$var = apply_filters( 'it_exchange_cart_action_nonce_var', '_wpnonce' );
	return wp_nonce_field( 'it-exchange-cart-action-' . it_exchange_get_session_id(), $var, true, false );
}

/**
 * Returns the shipping address values for the cart
 *
 * @since 1.4.0
 *
 * @return array
*/
function it_exchange_get_cart_shipping_address() {

	// If user is logged in, grab their data
	$customer = it_exchange_get_current_customer();
	$customer_data = empty( $customer->data ) ? new stdClass() : $customer->data;

	// Default values for first time use.
	$defaults = array(
		'first-name'   => empty( $customer_data->first_name ) ? '' : $customer_data->first_name,
		'last-name'    => empty( $customer_data->last_name ) ? '' : $customer_data->last_name,
		'company-name' => '',
		'address1'     => '',
		'address2'     => '',
		'city'         => '',
		'state'        => '',
		'zip'          => '',
		'country'      => '',
		'email'        => empty( $customer_data->user_email ) ? '' : $customer_data->user_email,
		'phone'        => '',
	);

	// See if the customer has a shipping address saved. If so, overwrite defaults with saved shipping address
	if ( ! empty( $customer_data->shipping_address ) )
		$defaults = ITUtility::merge_defaults( $customer_data->shipping_address, $defaults );

	// If data exists in the session, use that as the most recent
	$session_data = it_exchange_get_cart_data( 'shipping-address' );

	$cart_shipping = ITUtility::merge_defaults( $session_data, $defaults );

	// If shipping error and form was submitted, use POST values as most recent
	if ( ! empty( $_REQUEST['it-exchange-update-shipping-address'] ) && ! empty( $GLOBALS['it_exchange']['shipping-address-error'] ) ) {
		$keys = array_keys( $defaults );
		$post_shipping = array();
		foreach( $keys as $key ) {
			$post_shipping[$key] = empty( $_REQUEST['it-exchange-shipping-address-' . $key] ) ? '' : $_REQUEST['it-exchange-shipping-address-' . $key];
		}
		$cart_shipping = ITUtility::merge_defaults( $post_shipping, $cart_shipping );
	}

	return apply_filters( 'it_exchange_get_cart_shipping_address', $cart_shipping );
}

/**
 * Returns the billing address values for the cart
 *
 * @since 1.3.0
 *
 * @return array
*/
function it_exchange_get_cart_billing_address() {

	// If user is logged in, grab their data
	$customer = it_exchange_get_current_customer();
	$customer_data = empty( $customer->data ) ? new stdClass() : $customer->data;

	// Default values for first time use.
	$defaults = array(
		'first-name'   => empty( $customer_data->first_name ) ? '' : $customer_data->first_name,
		'last-name'    => empty( $customer_data->last_name ) ? '' : $customer_data->last_name,
		'company-name' => '',
		'address1'     => '',
		'address2'     => '',
		'city'         => '',
		'state'        => '',
		'zip'          => '',
		'country'      => '',
		'email'        => empty( $customer_data->user_email ) ? '' : $customer_data->user_email,
		'phone'        => '',
	);

	// See if the customer has a billing address saved. If so, overwrite defaults with saved billing address
	if ( ! empty( $customer_data->billing_address ) )
		$defaults = ITUtility::merge_defaults( $customer_data->billing_address, $defaults );

	// If data exists in the session, use that as the most recent
	$session_data = it_exchange_get_cart_data( 'billing-address' );

	$cart_billing = ITUtility::merge_defaults( $session_data, $defaults );

	// If billing error and form was submitted, use POST values as most recent
	if ( ! empty( $_REQUEST['it-exchange-update-billing-address'] ) && ! empty( $GLOBALS['it_exchange']['billing-address-error'] ) ) {
		$keys = array_keys( $defaults );
		$post_billing = array();
		foreach( $keys as $key ) {
			$post_billing[$key] = empty( $_REQUEST['it-exchange-billing-address-' . $key] ) ? '' : $_REQUEST['it-exchange-billing-address-' . $key];
		}
		$cart_billing = ITUtility::merge_defaults( $post_billing, $cart_billing );
	}

	return apply_filters( 'it_exchange_get_cart_billing_address', $cart_billing );
}

/**
 * Generates and returns a unique Cart ID to share across sessions
 *
 * Wrapper to it_exchange_get_unique_hash but allows filter for cart id
 *
 * @since 1.10.0
 *
 * @return string
*/
function it_exchange_create_cart_id() {
	$cart_id = it_exchange_create_unique_hash();
	$cart_id = apply_filters( 'it_exchange_create_cart_id', $cart_id );
	return $cart_id;
}

/**
 * Add a cart id to a cart
 *
 * Called by core whenever a product is added to an empty cart
 *
 * @since 1.10.0
 *
 * @param string|bool $id the id you want to set. false by default.
 *
 * @return string returns the ID
*/
function it_exchange_update_cart_id( $id = false ) {

	if ( empty( $id ) ) {
		$id = it_exchange_create_cart_id();
	}

	it_exchange_update_cart_data( 'cart_id', $id );

	return $id;
}

/**
 * Get a cart id from the session
 *
 * @since 1.10.0
 *
 * @return string returns the ID
*/
function it_exchange_get_cart_id() {
	$id = it_exchange_get_cart_data( 'cart_id' );

	// Expects ID to be a single item array
	$id = empty( $id[0] ) ? false : $id[0];
	return $id;
}

/**
 * Delete a cart id from the session
 *
 * @since 1.10.0
 *
 * @return void
*/
function it_exchange_remove_cart_id() {
	it_exchange_remove_cart_data( 'cart_id' );
}

/**
 * Are we doing guest checkout?
 *
 * @since 1.6.0
 * @since 1.35.7 Move
 *
 * @return boolean
 */
function it_exchange_doing_guest_checkout() {
	$data = it_exchange_get_cart_data( 'guest-checkout' );
	return ! empty( $data[0] );
}