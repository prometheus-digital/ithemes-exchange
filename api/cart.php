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
 * Get the current cart.
 *
 * This is cached in a static variable.
 *
 * @since 1.36
 *
 * @return \ITE_Cart
 */
function it_exchange_get_current_cart() {

	static $cart = null;

	if ( $cart === null || true ) {
		$cart = new \ITE_Cart(
			new ITE_Line_Item_Session_Repository( it_exchange_get_session(), new ITE_Line_Item_Repository_Events() ) 
		);
		$cart->add_cart_validator( new ITE_Multi_Item_Cart_Validator() );
		$cart->add_item_validator( new ITE_Multi_Item_Product_Validator() );
	}

	return $cart;
}

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
 * Checks if the current product being viewed is in the cart
 *
 * @since 0.4.10
 *
 * @return bool true if in cart|false if not
*/
function it_exchange_is_current_product_in_cart() {

	$product_id = false;
	$product    = it_exchange_get_the_product_id();

	if ( ! empty( $product ) ) {
		$product_id = $product;
	} else if ( ! empty( $_GET['sw-product'] ) ) {
		$product_id = $_GET['sw-product'];
	}

	$in_cart = it_exchange_is_product_in_cart( $product_id );

	return apply_filters( 'it_exchange_is_current_product_in_cart',
		$in_cart, $product_id, $product,  it_exchange_get_session_data( 'products' ) );
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

	$in_cart = false;

	foreach ( it_exchange_get_current_cart()->get_items( 'product' ) as $item ) {
		if ( $item->get_product()->ID == $product_id ) {
			$in_cart = true;

			break;
		}
	}

	return apply_filters( 'it_exchange_is_product_in_cart', $in_cart, $product_id,  it_exchange_get_session_data( 'products' ) );
}

/**
 * Adds a product to the shopping cart based on the product_id
 *
 * @since 0.3.7
 * @since 1.35 Add $return_cart_id parameter.
 *
 * @param string $product_id a valid wp post id with an iThemes Exchange product post_typp
 * @param int $quantity (optional) how many?
 * @param bool $return_cart_id
 *
 * @return boolean|string Cart ID if $return_cart_id is true.
*/
function it_exchange_add_product_to_shopping_cart( $product_id, $quantity = 1, $return_cart_id = false ) {

	if ( ! $product_id ) {
		return false;
	}

	if ( ! $product = it_exchange_get_product( $product_id ) ) {
		return false;
	}

	$quantity = max( 1, intval( $quantity ) );

	if ( ! it_exchange_get_cart_id() ) {
		it_exchange_create_cart_id();
	}

	$item = new ITE_Cart_Product( $product, $quantity );

	// Deprecated hook. Use IT_Exchange_Cart_Product::set_itemized_data()
	$itemized_data = apply_filters( 'it_exchange_add_itemized_data_to_cart_product', array(), $product_id );
	$itemized_data = maybe_unserialize( $itemized_data );

	foreach ( $itemized_data as $key => $value ) {
		$item->set_itemized_data( $key, $value );
	}

	// Deprecated hook. Use IT_Exchange_Cart_Product::set_additional_data()
	$additional_data = apply_filters( 'it_exchange_add_additional_data_to_cart_product', array(), $product_id );
	$additional_data = maybe_unserialize( $additional_data );

	foreach ( $additional_data as $key => $value ) {
		$item->set_additional_data( $key, $value );
	}

	if ( ! it_exchange_get_current_cart()->add_item( $item ) ) {
		return false;
	}

	return $return_cart_id ? $item->get_id() : true;
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

	if ( ! is_numeric( $quantity ) || $quantity < 1 ) {
		return it_exchange_get_current_cart()->remove_item( 'product', $cart_product_id );
	}

	$item = it_exchange_get_current_cart()->get_item( 'product', $cart_product_id );

	if ( ! $item ) {
		return false;
	}

	if ( $add_to_existing ) {
		$item->set_quantity( $quantity + $item->get_quantity() );
	} else {
		$item->set_quantity( $quantity );
	}

	return $item->persist( it_exchange_get_current_cart()->get_repository() );
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
	it_exchange_get_current_cart()->empty_cart();
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
function it_exchange_cache_customer_cart( $customer_id = false ) {
	// Grab the current customer
	$customer = ! $customer_id ? it_exchange_get_current_customer() : it_exchange_get_customer( $customer_id );

	// Abort if we don't have a logged in customer
	if ( ! $customer || ! is_numeric( $customer->id ) || $customer->id <= 0 ) {
		return;
	}

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
 * @return array|false
*/
function it_exchange_get_cached_customer_cart( $customer_id = false ) {
	// Grab the current customer
	$customer = ! $customer_id ? it_exchange_get_current_customer() : it_exchange_get_customer( $customer_id );

	// Abort if we don't have a logged in customer
	if ( ! $customer || ! is_numeric( $customer->id ) || $customer->id <= 0 ) {
		return false;
	}

	// Grab the data
	$cart = get_user_meta( $customer->id, '_it_exchange_cached_cart', true );

	if ( ! is_array( $cart ) ) {
		$cart = array();
	}

	return apply_filters( 'it_exchange_get_chached_customer_cart', $cart, $customer->id );
}

/**
 * Add a session ID to the list of active customer cart sessions
 *
 * @since 1.9.0
 *
 * @param int|bool $customer_id Pass false to retrieve the current customer's ID.
 *
 * @return void|false
*/
function it_exchange_add_current_session_to_customer_active_carts( $customer_id = false ) {

	if ( ! $customer_id ) {
		$customer_id = it_exchange_get_current_customer_id();
	}

	// Grab the current customer
	$customer = it_exchange_get_customer( $customer_id );

	// Abort if we don't have a logged in customer
	if ( ! $customer || ! is_numeric( $customer->id ) || $customer->id <= 0 ) {
		return false;
	}

	if ( ! empty( $_GLOBALS['it_exchange']['logging_out_user'] ) ) {
		return false;
	}

	// Get the current customer's session ID
	$current_session_string  = it_exchange_get_session_id();
	$current_session_parts   = explode( '||', $current_session_string );

	if ( ! empty( $current_session_parts[0] ) ) {
		$current_session_id = $current_session_parts[0];
	} else {
		return false;
	}

	if ( ! empty( $current_session_parts[1] ) ) {
		$current_session_expires = $current_session_parts[1];
	} else {
		return false;
	}

	if ( ! $current_session_id || $current_session_expires ) {
		return false;
	}

	// Get all active carts for customer (across devices / browsers )
	$active_carts = it_exchange_get_active_carts_for_customer( false, $customer->id );

	// Add or update current session data to active sessions
	if ( ! isset( $active_carts[$current_session_id] ) || ( isset( $active_carts[$current_session_id] ) && $active_carts[$current_session_id] < time() ) ) {
		$active_carts[$current_session_id] = $current_session_expires;
		update_user_meta( $customer->id, '_it_exchange_active_user_carts', $active_carts );
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
 * @since 1.9.0
 *
 * @param bool     $include_current_cart defaults to false
 * @param int|null $customer_id optional. uses current customer id if null
 *
 * @return array
*/
function it_exchange_get_active_carts_for_customer( $include_current_cart=false, $customer_id=null ) {
	// Get the customer
	$customer = null === $customer_id ? it_exchange_get_current_customer() : it_exchange_get_customer( $customer_id );

	// Abort if we don't have a logged in customer
	if ( ! $customer || ! is_numeric( $customer->id ) || $customer->id <= 0 ) {
		return apply_filters( 'it_exchange_get_active_carts_for_customer', array(), $customer_id );
	}

	// Get current session ID
	$current_session_string = it_exchange_get_session_id();
	$current_session_parts  = explode( '||', $current_session_string );
	$current_session_id     = empty( $current_session_parts[0] ) ? false : $current_session_parts[0];
	$current_session_exp    = empty( $current_session_parts[1] ) ? false : $current_session_parts[1];

	// Grab saved active sessions from user meta
	$active_carts = get_user_meta( $customer->id, '_it_exchange_active_user_carts', true );

	// If active_carts is false, this is probably the first call with no previously active carts, so add the current one.
	if ( ! is_array( $active_carts ) || count( $active_carts ) === 0 ) {
		$active_carts = array( $current_session_id => $current_session_exp );
	}

	// Current time
	$time = time();

	// Loop through active sessions
	foreach( (array) $active_carts as $session_id => $expires ) {
		// Remove expired carts
		if ( $time > $expires ) {
			unset( $active_carts[ $session_id ] );
		}
	}

	// Remove current cart if not needed
	if ( ! $include_current_cart && $current_session_id ) {
		unset( $active_carts[ $current_session_id ] );
	}

	return apply_filters( 'it_exchange_get_active_carts_for_customer', $active_carts, $customer_id );
}

/**
 * Loads a cached cart into active session
 *
 * @since 1.9.0
 *
 * @deprecated 1.36.0
 *
 * @param $user_login string
 * @param $user       WP_User
 *
 * @return void|false
*/
function it_exchange_merge_cached_customer_cart_into_current_session( $user_login, $user ) {
	// Grab the current customer
	$customer = it_exchange_get_customer( $user->ID );

	// Abort if we don't have a logged in customer
	if ( ! $customer || ! is_numeric( $customer->id ) || $customer->id <= 0 ) {
		return false;
	}

	try {
		it_exchange_get_current_cart()->merge(
			new ITE_Cart( ITE_Line_Item_Cached_Session_Repository::from_customer( $customer ) )
		);
	} catch ( UnexpectedValueException $e ) {

	}


	// This is a new customer session after loggin in so add this session to active carts
	it_exchange_add_current_session_to_customer_active_carts( $customer->id );

	// If there are items in the cart, cache and sync
	if ( it_exchange_get_current_cart()->get_items() ) {
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
 * @param \ITE_Cart|null $cart
 *
 * @return boolean
*/
function it_exchange_is_multi_item_cart_allowed( \ITE_Cart $cart = null ) {
	return apply_filters( 'it_exchange_multi_item_cart_allowed', false, $cart ? $cart : it_exchange_get_current_cart() );
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
 * @return int
*/
function it_exchange_get_cart_product_quantity( $product ) {

	if ( empty( $product['product_cart_id'] ) ) {
		return 0;
	}

	$item = it_exchange_get_current_cart()->get_item( 'product', $product['product_cart_id'] );

	if ( ! $item ) {
		return 0;
	}

	return apply_filters( 'it_exchange_get_cart_product_quantity', $item->get_quantity(), $item->get_data_to_save() );
}

/**
 * Returns the quantity for a cart product.
 *
 * Caution: This will return unexpected results for variants
 * if more than one variant of a product is in the cart.
 *
 * @since 0.4.4
 *
 * @param int $product_id
 *
 * @return int
*/
function it_exchange_get_cart_product_quantity_by_product_id( $product_id ) {
	$products = it_exchange_get_current_cart()->get_items( 'product' );

	foreach ( $products as $product ) {
		if ( $product->get_product()->ID == $product_id ) {
			return $product->get_quantity();
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

	$products = it_exchange_get_current_cart()->get_items( 'product' );
	$count    = 0;

	if ( $true_count ) {
		foreach( $products as $product ) {
			if ( ! $product->get_quantity() || ! $product->get_product() ) {
				continue;
			}

			if ( ! empty( $feature ) && ! $product->get_product()->has_feature( $feature ) ) {
				continue;
			}

			$count += $product->get_quantity();
		}

		return absint( $count );
	} else {
		foreach( $products as $product ) {
			if ( ! empty( $feature ) && ! $product->get_product()->has_feature( $feature ) ) {
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

	$weight   = 0;
	$products = it_exchange_get_current_cart()->get_items('product');

	foreach( $products as $product ) {
        $pm     = get_post_meta( $product->get_product()->ID, '_it_exchange_core_weight', true );
		$weight += empty( $pm['weight'] ) ? 0 : ( $pm['weight'] * $product->get_quantity() );
	}

	return is_numeric( $weight ) ? $weight : 0;
}

/**
 * Returns the base_price for the cart product
 *
 * Other add-ons may modify this on the fly based on the product's itemized_data and additional_data arrays
 *
 * @since 0.3.7
 *
 * @deprecated 1.36.0
 *
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
 *
 * @param array $product cart product
 * @param bool $format
 *
 * @return int|string subtotal
*/
function it_exchange_get_cart_product_subtotal( $product, $format=true ) {

	$item = it_exchange_get_current_cart()->get_item( 'product', $product['product_cart_id'] );

	if ( ! $item || ! $item->get_quantity() ) {
		$subtotal = 0;
	} else {
		$subtotal = $item->get_amount() * $item->get_quantity();
		$subtotal = apply_filters( 'it_exchange_get_cart_product_subtotal', $subtotal, $item->get_data_to_save() );
	}

	return $format ? it_exchange_format_price( $subtotal ) : $subtotal;
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
	$items    = it_exchange_get_current_cart()->get_items( 'product' );

	if ( ! $items ) {
		return 0;
	}

	foreach( $items as $item ) {
		if ( empty( $options['feature'] ) || $item->get_product()->get_feature( $options['feature'] ) ) {
			$subtotal += it_exchange_get_cart_product_subtotal( array( 'product_cart_id' => $item->get_id() ), false );
		}
	}

	$subtotal = apply_filters( 'it_exchange_get_cart_subtotal', $subtotal, $options );

	return $format ? it_exchange_format_price( $subtotal ) : $subtotal;
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

	$total = it_exchange_get_cart_subtotal( false, $options );
	$total += it_exchange_get_current_cart()->get_items( '', true )->without( 'product' )->filter( function ( ITE_Line_Item $item ) {
		return $item->is_summary_only();
	} )->total();

	$total = apply_filters( 'it_exchange_get_cart_total', $total );
	$total = max( 0, $total );

	return $format ? it_exchange_format_price( $total ) : $total;
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
	$items       = it_exchange_get_current_cart()->get_items( 'product' );

	if ( ! $items ) {
		return '';
	}

	foreach ( $items as $item ) {
		$string = it_exchange_get_cart_product_title( array( 'product_id' => $item->get_product()->ID ) );

		if (  1 < $count = it_exchange_get_cart_product_quantity( array( 'product_cart_id' => $item->get_product()->ID ) ) ) {
			$string .= ' (' . $count . ')';
		}

		$description[] = apply_filters( 'it_exchange_get_cart_description_for_product', $string, $item->get_data_to_save() );
	}

	return apply_filters( 'it_exchange_get_cart_description', implode( ', ', $description ), $description, $options );
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