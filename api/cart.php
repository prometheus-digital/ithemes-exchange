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
 * @since 2.0.0
 *
 * @param bool $create_if_not_started
 *
 * @return \ITE_Cart|null
 */
function it_exchange_get_current_cart( $create_if_not_started = true ) {

	/** @var ITE_Cart $cart */
	static $cart = null;

	$cart_id = it_exchange_get_cart_id( $create_if_not_started );

	if ( $cart_id && ( $cart === null || ! $cart->get_id() ) ) {
		$cart = new \ITE_Cart(
			new ITE_Line_Item_Session_Repository( it_exchange_get_session(), new ITE_Line_Item_Repository_Events() ),
			$cart_id
		);
	}

	if ( $cart && ! $cart->get_id() ) {
		$cart = null;
	}

	return $cart;
}

/**
 * Get a cart by id.
 *
 * @since 2.0.0
 *
 * @param string $cart_id
 *
 * @return \ITE_Cart|null
 */
function it_exchange_get_cart( $cart_id ) {

	if ( ! $cart_id ) {
		return null;
	}

	if ( it_exchange_get_cart_id() === $cart_id ) {
		return it_exchange_get_current_cart( false );
	}

	try {
		$repo = ITE_Line_Item_Cached_Session_Repository::from_cart_id( $cart_id );
	} catch ( InvalidArgumentException $e ) {
		return null;
	}

	return new ITE_Cart( $repo, $cart_id, $repo->get_customer() );
}

/**
 * Helper function to create a cart and backing session.
 *
 * @since 2.0.0
 *
 * @param IT_Exchange_Customer $user
 * @param bool                 $is_main
 * @param DateTime|null        $expires
 *
 * @return ITE_Cart|null
 */
function it_exchange_create_cart_and_session( IT_Exchange_Customer $user, $is_main = true, \DateTime $expires = null ) {

	$session = \ITE_Session_Model::create( array(
		'ID'         => it_exchange_create_unique_hash(),
		'customer'   => $user->get_ID(),
		'is_main'    => $is_main,
		'expires_at' => $expires,
	) );

	$repo = \ITE_Line_Item_Cached_Session_Repository::from_session_id( $user, $session->ID );
	$cart = \ITE_Cart::create( $repo, $user );

	if ( ! $cart ) {
		return null;
	}

	$session->cart_id = $cart->get_id();
	$session->data    = array_merge( $session->data, array( 'cart_id' => $cart->get_id() ) );
	$session->save();

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
	$cart = it_exchange_get_current_cart( false );


	if ( $cart ) {
		foreach ( $cart->get_items( 'product' ) as $item ) {
			if ( $item->get_product()->ID == $product_id ) {
				$in_cart = true;

				break;
			}
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

	$quantity = max( 1, (int) $quantity );

	$item = ITE_Cart_Product::create( $product, $quantity );

	// Deprecated hook. Use ITE_Cart_Product::set_itemized_data()
	$itemized_data = apply_filters( 'it_exchange_add_itemized_data_to_cart_product', array(), $product_id );
	$itemized_data = maybe_unserialize( $itemized_data );

	foreach ( $itemized_data as $key => $value ) {
		$item->set_itemized_data( $key, $value );
	}

	// Deprecated hook. Use ITE_Cart_Product::set_additional_data()
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

	return it_exchange_get_current_cart()->get_repository()->save( $item );
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
	if ( ! $product || ! $product->supports_feature( 'purchase-quantity' ) ) {
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
 * Get a customer's cached cart if they are logged in
 *
 * @since 1.9.0
 * @since 2.0.0 Introduce `$session_only` parameter.
 *
 * @param int|bool $customer_id The id of an exchange customer
 * @param bool $session_only    Only return the session data not an \ITE_Cart object.
 *
 * @return array|ITE_Cart|false
*/
function it_exchange_get_cached_customer_cart( $customer_id = false, $session_only = true ) {
	// Grab the current customer
	$customer = ! $customer_id ? it_exchange_get_current_customer() : it_exchange_get_customer( $customer_id );

	// Abort if we don't have a logged in customer
	if ( ! $customer || ! is_numeric( $customer->id ) || $customer->id <= 0 ) {
		return false;
	}

	try {
		$repository = ITE_Line_Item_Cached_Session_Repository::from_customer( $customer );
	} catch ( InvalidArgumentException $e ) {
		return false;
	}

	if ( $session_only ) {

		// Grab the data
		$cart = $repository->get_model()->data;

		return apply_filters( 'it_exchange_get_chached_customer_cart', $cart, $customer->id );
	} else {
		return new \ITE_Cart( $repository, $repository->get_cart_id(), $customer );
	}
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

	$active_carts = ITE_Session_Model::query()
		->where( 'customer','=', $customer->id )
		->and_where( 'expires_at', '<', current_time( 'mysql', true ) )
		->select_single( 'expires_at' )
		->results();

	if ( ! $include_current_cart && $session_id = it_exchange_get_session_id( true ) ) {
		unset( $active_carts[ $session_id ] );
	}

	$active_carts = array_map( function( $expires ) {
		return strtotime( $expires );
	}, $active_carts->toArray() );

	return apply_filters( 'it_exchange_get_active_carts_for_customer', $active_carts, $customer_id );
}

/**
 * Loads a cached cart into active session
 *
 * @since 1.9.0
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
		$repository = ITE_Line_Item_Cached_Session_Repository::from_customer( $customer );
		it_exchange_get_current_cart()->merge( new \ITE_Cart(
			$repository, $repository->get_cart_id(), $customer
		) );

		$model = $repository->get_model();

		if ( $model ) {
			// We delete the cached session so we only have one session per-customer
			$model->delete();
		}
	} catch ( InvalidArgumentException $e ) {

	}

	it_exchange_cache_customer_cart( $customer->id );
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
	return apply_filters( 'it_exchange_multi_item_cart_allowed', false, $cart ?: it_exchange_get_current_cart( false ) );
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
 * @return string|false product title
*/
function it_exchange_get_cart_product_title( $product ) {

	if ( empty( $product['product_cart_id'] ) ) {
		return false;
	}

	$item = it_exchange_get_current_cart()->get_item( 'product', $product['product_cart_id'] );

	return $item ? $item->get_name() : false;
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

	return $item->get_quantity();
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
 * @param ITE_Cart $cart
 *
 * @return int
*/
function it_exchange_get_cart_product_quantity_by_product_id( $product_id, ITE_Cart $cart = null ) {

	$cart     = $cart ?: it_exchange_get_current_cart();
	$products = $cart->get_items( 'product' );

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
 * @param \ITE_Cart   $cart
 *
 * @return integer
*/
function it_exchange_get_cart_products_count( $true_count = false, $feature = false, ITE_Cart $cart = null ) {

	$cart = $cart ?: it_exchange_get_current_cart( false );

	if ( ! $cart ) {
		return 0;
	}

	$products = $cart->get_items( 'product' );
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
 * @param \ITE_Cart $cart
 *
 * @return float
*/
function it_exchange_get_cart_weight( ITE_Cart $cart = null ) {

	$weight   = 0;
	$cart     = $cart ?: it_exchange_get_current_cart();
	$products = $cart->get_items('product');

	foreach( $products as $product ) {
        $pm     = get_post_meta( $product->get_product()->ID, '_it_exchange_core_weight', true );
		$weight += empty( $pm['weight'] ) ? 0 : ( $pm['weight'] * $product->get_quantity() );
	}

	return is_numeric( $weight ) ? $weight : 0;
}

/**
 * Get the shipping method for any line item.
 *
 * @since 2.0.0
 *
 * @param \ITE_Line_Item $item
 *
 * @return \IT_Exchange_Shipping_Method|null
 */
function it_exchange_get_shipping_method_for_item( ITE_Line_Item $item ) {

	if ( ! $item instanceof ITE_Aggregate_Line_Item ) {
		return null;
	}

	$shipping = $item->get_line_items()->with_only( 'shipping' )->first();

	if ( $shipping instanceof ITE_Shipping_Line_Item ) {
		return $shipping->get_method();
	}

	return null;
}

/**
 * Determine if a cart is eligible for using multiple shipping methods.
 *
 * @since 2.0.0
 *
 * @param \ITE_Cart|null $cart
 *
 * @return bool
 */
function it_exchange_cart_is_eligible_for_multiple_shipping_methods( ITE_Cart $cart = null ) {

	$cart = $cart ?: it_exchange_get_current_cart();

	$items_with_shipping = $cart->get_items('product')->filter( function( ITE_Cart_Product $product ) {
		return $product->get_product()->has_feature( 'shipping' );
	} );

	if ( $items_with_shipping->count() === 1 ) {
		return false;
	}

	$available_methods = it_exchange_get_available_shipping_methods_for_cart( true, $cart );

	if ( count( $available_methods ) === 0 ) {
		return true;
	}

	$eligible = count( it_exchange_get_available_shipping_methods_for_cart_products( $cart ) ) > 1;

	if ( $eligible ) {
		return apply_filters( 'it_exchange_shipping_method_form_multiple_shipping_methods_allowed', $eligible );
	}

	return false;
}

/**
 * Returns the base_price for the cart product
 *
 * Other add-ons may modify this on the fly based on the product's itemized_data and additional_data arrays
 *
 * @since 0.3.7
 *
 * @deprecated 2.0.0
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
		$subtotal = $item->get_total();
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
 *      @type \ITE_Cart $cart
 *      @type bool      $use_cached_customer_cart If contains a customer ID, we grab cart
 *                                                data from the cached cart
 *      @type string    $feature                  Limit to products with this feature.
 * }
 *
 * @return mixed subtotal of cart
*/
function it_exchange_get_cart_subtotal( $format = true, $options = array() ) {

	if ( ! empty( $options['use_cached_customer_cart'] ) ) {
		$cart = it_exchange_get_cached_customer_cart( $options['use_cached_customer_cart'], false );
	} elseif ( ! empty( $options['cart'] ) ) {
		$cart = $options['cart'];
	} else {
		$cart = it_exchange_get_current_cart();
	}

	if ( ! $cart instanceof ITE_Cart ) {
		return $format ? it_exchange_format_price( 0 ) : 0;
	}

	$subtotal = $cart->get_subtotal( $options );

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
 *      @type \ITE_Cart $cart
 *      @type mixed     $use_cached_customer_cart If contains a customer ID, we grab cart
 *                                                data from the cached cart
 * }
 *
 * @return mixed total of cart
*/
function it_exchange_get_cart_total( $format = true, $options = array() ) {

	if ( ! empty( $options['use_cached_customer_cart'] ) ) {
		$cart = it_exchange_get_cached_customer_cart( $options['use_cached_customer_cart'], false );
	}
	elseif ( ! empty( $options['cart'] ) ) {
		$cart = $options['cart'];
	} else {
		$cart = it_exchange_get_current_cart();
	}

	if ( ! $cart instanceof ITE_Cart ) {
		return 0;
	}

	$total = $cart->get_total();

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
 *      @type \ITE_Cart $cart
 *      @type mixed     $use_cached_customer_cart If contains a customer ID, we grab cart
 *                                            data from the cached cart
 * }
 *
 * @return string description
*/
function it_exchange_get_cart_description( $options = array() ) {

	if ( ! empty( $options['use_cached_customer_cart'] ) ) {
		$cart = it_exchange_get_cached_customer_cart( $options['use_cached_customer_cart'], false );
	}
	elseif ( ! empty( $options['cart'] ) ) {
		$cart = $options['cart'];
	} else {
		$cart = it_exchange_get_current_cart();
	}

	if ( ! $cart instanceof ITE_Cart ) {
		return '';
	}

	$description = array();
	$items       = $cart->get_items()->non_summary_only();

	if ( ! $items->count() ) {
		return '';
	}

	foreach ( $items as $item ) {
		$string = $item->get_name();

		if ( 1 < $count = $item->get_quantity() ) {
			$string .= ' (' . $count . ')';
		}

		if ( $item instanceof ITE_Cart_Product ) {
			$string = apply_filters( 'it_exchange_get_cart_description_for_product', $string, $item->bc() );
		}

		/**
		 * Filter the description for an item.
		 *
		 * @since 2.0.0
		 *
		 * @param string         $string
		 * @param \ITE_Line_Item $item
		 */
		$string = apply_filters( 'it_exchange_get_cart_description_for_item', $string, $item );

		$description[] = $string;
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
	$address = it_exchange_get_current_cart()->get_shipping_address();

	if ( $address === null ) {
		return array();
	} else {
		return $address->to_array();
	}
}

/**
 * Returns the billing address values for the cart
 *
 * @since 1.3.0
 *
 * @return array
*/
function it_exchange_get_cart_billing_address() {

	$address = it_exchange_get_current_cart()->get_billing_address();

	if ( $address === null ) {
		return array();
	} else {
		return $address->to_array();
	}
}

/**
 * Get the available transaction methods for a cart.
 *
 * @since 2.0.0
 *
 * @param ITE_Cart|null $cart
 *
 * @return ITE_Gateway[]
 */
function it_exchange_get_available_transaction_methods_for_cart( ITE_Cart $cart = null ) {

	$cart = $cart ?: it_exchange_get_current_cart();

	$methods = array();

	foreach ( ITE_Gateways::accepting() as $gateway ) {
		/** @var ITE_Purchase_Request_Handler $handler */
		if ( ( $handler = $gateway->get_handler_by_request_name( 'purchase' ) ) && $handler->can_handle_cart( $cart ) ) {
			$methods[] = $gateway;
		}
	}

	/**
	 * Filter the available transaction methods for a given cart.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Gateway[] $methods
	 * @param \ITE_Cart      $cart
	 */
	return apply_filters( 'it_exchange_available_transaction_methods_for_cart', $methods, $cart );
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
	return apply_filters( 'it_exchange_create_cart_id', it_exchange_create_unique_hash() );
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
		return ITE_Cart::create()->get_id();
	}

	it_exchange_update_cart_data( 'cart_id', $id );

	return $id;
}

/**
 * Get a cart id from the session
 *
 * @since 1.10.0
 *
 * @param bool $generate Generate a cart ID is one does not exist.
 *
 * @return string|false returns the ID
*/
function it_exchange_get_cart_id( $generate = false ) {
	$id = it_exchange_get_cart_data( 'cart_id' );

	// Expects ID to be a single item array
	if ( empty( $id[0] ) ) {
		$id = $generate ? it_exchange_update_cart_id() : false;
	} else {
		$id = $id[0];
	}
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
 * @since 1.35.7 Move to api/cart instead of Guest Checkout add-on.
 *
 * @return boolean
 */
function it_exchange_doing_guest_checkout() {
	$data = it_exchange_get_cart_data( 'guest-checkout' );
	return ! empty( $data[0] );
}

/**
 * Get the featured image for a product cart item.
 *
 * @since 1.36.0
 *
 * @param ITE_Cart_Product $item
 * @param string|array     $size
 *
 * @return string
 */
function it_exchange_get_product_cart_item_featured_image_url( ITE_Cart_Product $item, $size = 'thumb' ) {

	$product = $item->get_product();

	if ( ! $product ) {
		return '';
	}

	$product_id = $product->ID;
	$itemized   = $item->get_itemized_data();

	if ( ( it_exchange_product_supports_feature( $product_id, 'product-images' ) && it_exchange_product_has_feature( $product_id, 'product-images' ) ) ) {

		if ( ! empty( $itemized['it_variant_combo_hash'] ) ) {
			$combo_hash = $itemized['it_variant_combo_hash'];
		}

		$images_located = false;

		if ( isset( $combo_hash ) && function_exists( 'it_exchange_variants_addon_get_product_feature_controller' ) ) {

			$variant_combos_data = it_exchange_get_variant_combo_attributes_from_hash( $product_id, $combo_hash );
			$combos_array        = empty( $variant_combos_data['combo'] ) ? array() : $variant_combos_data['combo'];
			$alt_hashes          = it_exchange_addon_get_selected_variant_alts( $combos_array, $product_id );

			$controller = it_exchange_variants_addon_get_product_feature_controller( $product_id, 'product-images', array( 'setting' => 'variants' ) );

			if ( $variant_combos_data['hash'] == $combo_hash ) {
				if ( ! empty( $controller->post_meta[ $combo_hash ]['value'] ) ) {
					$product_images = $controller->post_meta[ $combo_hash ]['value'];
					$images_located = true;
				}
			}
			// Look for alt hashes if direct match was not found
			if ( ! $images_located && ! empty( $alt_hashes ) ) {
				foreach ( $alt_hashes as $alt_hash ) {
					if ( ! empty( $controller->post_meta[ $alt_hash ]['value'] ) ) {
						$product_images = $controller->post_meta[ $alt_hash ]['value'];
						$images_located = true;
					}
				}
			}
		}

		if ( ! $images_located || ! isset( $product_images ) ) {
			$product_images = it_exchange_get_product_feature( $product_id, 'product-images' );
		}

		$feature_image = array(
			'id'    => $product_images[0],
			'thumb' => wp_get_attachment_thumb_url( $product_images[0] ),
			'large' => wp_get_attachment_url( $product_images[0] ),
		);

		if ( is_array( $size ) ) {
			$img_src = wp_get_attachment_image_url( $product_images[0], $size );
		} elseif ( 'thumb' === $size ) {
			$img_src = $feature_image['thumb'];
		} else {
			$img_src = $feature_image['large'];
		}

		return $img_src;
	}

	return '';
}