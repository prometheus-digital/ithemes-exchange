<?php

/**
 * Redirect if the current user doesn't have permission to view the current post
 *
 * @since 0.3.8
 * @return void
*/
function it_cart_buddy_protected_content_maybe_redirect_singular_object() {
	global $post;

	// Abandon if this isn't a singular page
	if ( ! is_singular() )
		return;

	// Abandon if this post isn't protected based on role or product
	if ( ! it_cart_buddy_protected_content_is_object_protected( $post->ID ) )
		return;

	// Abandon if current user can view current object
	if ( it_cart_buddy_protected_content_current_user_can_access_object( $post->ID ) )
		return;

	// Check to see if this object requires a redirect and send them on their way if it does
	if ( 'redirect' == it_cart_buddy_protected_content_get_options( $post->ID, 'unauthorized_singular_action' ) ) {
		wp_redirect( it_cart_buddy_protected_content_get_options( $post->ID, 'unauthorized_singular_redirect_url' ) );
		die();
	}
}
add_action( 'template_redirect', 'it_cart_buddy_protected_content_maybe_redirect_singular_object' );

/**
 * Is the passed ID marked as being complety protected
 *
 * @since 0.3.8
 * @return boolean
*/
function it_cart_buddy_protected_content_is_object_protected( $post_id ) {
	$options = get_post_meta( $post_id, '_it_cart_buddy_protected_content_options', true );
	return ! empty( $options['is_protected'] ) && ( 'products' == $options['is_protected'] || 'wp_roles' == $options['is_protected'] );
}

/**
 * Can the current user access the passed post_type object
 *
 * @since 0.3.8
 * @param integer $post_id id for row in wp_posts table
 * @return boolean
*/
function it_cart_buddy_protected_content_current_user_can_access_object( $post_id ) {

	// Return true if object isn't protected
	if ( ! it_cart_buddy_protected_content_is_object_protected( $post_id ) )
		return true;

	// Return false if object is protected but user isn't logged into the site
	if ( ! $current_user = it_cart_buddy_get_current_customer() )
		return false;
	
	// Return false if it is a product based protection and user hasn't purchased correct product(s)
	if ( ! it_cart_buddy_protected_content_current_user_can_access_content_based_on_purchases( $post_id ) )
		return false;

	// Return false if it is a wp_roles based protection and the user hasn't purchase correct products(s)
	if ( ! it_cart_buddy_protected_content_current_user_can_access_content_based_on_wp_roles( $post_id ) )
		return false;

	// Return false if there is a time restraint on viewing
	if ( ! it_cart_buddy_protected_content_current_user_can_access_content_based_on_current_date( $post_id ) )
		return false;

	// Return true if we've made it this far
	return true;
}

/**
 * Does the current user have permission to view content based on purchase history?
 *
 * Will check for purchase of product IDs
 *
 * @since 0.3.8
 * @return boolean
*/
function it_cart_buddy_protected_content_current_user_can_access_content_based_on_purchases( $object_id ) {

	// Return false if current user isn't logged into the site
	if ( ! $current_user = it_cart_buddy_get_current_customer() )
		return false;

	// Return true if object isn't being protected based on product purchases
	if ( 'products' != it_cart_buddy_protected_content_get_options( $object_id, 'is_protected' ) ) 
		return true;

	// Return false if object was marked as protected but no products were checked
	if ( ! $required_products = it_cart_buddy_protected_content_get_required_products_for_protected_object( $object_id ) )
		return false;

	// Get User products. Return false if there is no purchase history
	if ( ! $purchase_history = $current_user->get_purchase_history() )
		return false;

	// Do we need all or any of the products
	$all_any = it_cart_buddy_protected_content_get_options( $object_id, 'all_any_products' );

	// At this point we have confirmation that the current user has made transactions in the past and that we have an array of required products
	// Now we're going to loop through purchased products and compare them to required products.
	// Additionally, We're going to let individual product-type addons tell us if they need more fine-tuned checks
	// An example may be expired subscriptions, etc.
	foreach( $purchase_history as $product_id => $product_purchases ) {

		// Skip this purchase if its not a required product
		if ( ! in_array( $product_id, $required_products ) ) {
			unset( $purchase_history[$product_id] );
			continue;
		}

		// Loop through all purchases of this product to see if customer has a vailid purchase of it.
		if ( ! it_cart_buddy_protected_content_user_has_valid_purchase_of_product( $current_user->id, $product_purchases ) ) {
			// If false and $any_all is set to 'all', return false because they're missing a valid purchase of a required product
			if ( 'all' == $all_any )
				return false;

			// If $all_any is set to 'any', just remove it from the purchase history
			unset( $purchase_history[$product_id] );
		}
	}

	// If purchase history has been emptied b/c of invalid purchases, return false
	if ( empty ( $purchase_history ) )
		return false;

	// Finally, confirm current user has all/any valid purchases of required products depending on all/any setting
	foreach( $required_products as $product ) {
		if ( ! isset( $purchase_history[$product] ) && 'all' == $all_any )
			return false;

		if ( isset( $purchase_history[$product] ) && 'any' == $all_any )
			return true;
	}

	// Return true
	return true;
}

/**
 * Does the current user have permission to view content based on WP role?
 *
 * Will check for purchase of product IDs
 *
 * @since 0.3.8
 * @return boolean
*/
function it_cart_buddy_protected_content_current_user_can_access_content_based_on_wp_roles( $object_id ) {
	if ( 'wp_roles' != it_cart_buddy_protected_content_get_options( $object_id, 'is_protected' ) )
		return true;

	foreach( (array) it_cart_buddy_protected_content_get_options( $object_id, 'wp_roles' ) as $role ) {
		if ( current_user_can( $role ) )
			return true;
	}
	return false;
}

/**
 * Return an array of products required to have been purchased in order to view a protected post_type object
 *
 * @since 0.3.8
 * @param integer $post_id the id of a row from the wp_posts table
 * @return mixed an array or false
*/
function it_cart_buddy_protected_content_get_required_products_for_protected_object( $post_id ) {
	$products = it_cart_buddy_protected_content_get_options($post_id, 'selected_products' );
	return empty( $products ) ? false : $products;
}

/**
 * Does the user have a valid purchase of the passed product?
 *
 * $product_purchases is an array of all purchases this customer has made of this product
 * Allow product types to hook in and determine if a specific purchase is still valid
 * We're going to assume its valid unless the product filter tells us that it isn't
 *
 * @since 0.3.8
 * @param integer $user_id
 * @param an array of products purchased by the customer.
 * @param string $any_all does the user need to have purchased all or any of the the products
 * @return boolean
*/
function it_cart_buddy_protected_content_user_has_valid_purchase_of_product( $user_id, $product_purchases ) {
	foreach( (array) $product_purchases as $purchase) {
		$product_type     = it_cart_buddy_get_product_type( $purchase['product_id'] );
		$product_is_valid = apply_filters( 'it_cart_buddy_protected_content_is_purchased_product_valid-' . $product_type, true, $user_id, $purchase );

		// Exit the foreach once we find a valid product
		if ( $product_is_valid )
			return true;
	}
	return false;
}

/**
 * This will check to see if there are any time constraints on viewing the content and return true or false
 *
 * True if it is customer is able to view. False if not.
 *
 * @since 0.3.8
 * @param integer $post_id
 * @return boolean
*/
function it_cart_buddy_protected_content_current_user_can_access_content_based_on_current_date( $post_id ) {
	
	switch ( it_cart_buddy_protected_content_get_options( $post_id, 'when_protected' ) ) {
		/**
		NOT CURRENTLY AN OPTION
		case 'duration-period' :
			return it_cart_buddy_protected_content_current_user_can_access_content_based_on_purchase_duration( $post_id );
			break;
		**/
		case 'time-period' :
			return it_cart_buddy_protected_content_current_user_can_access_content_based_on_time_period( $post_id );
			break;
		case 'never' :
		default :
			return true;
	}
}

/**
 * Return true or false based on the specified time period for the post object
 *
 * @since 0.3.8
 * @param integer $post_id
 * @return boolean
*/
function it_cart_buddy_protected_content_current_user_can_access_content_based_on_time_period( $post_id ) {
	if ( 'time-period' != it_cart_buddy_protected_content_get_options( $post_id, 'when_protected' ) )
		return true;

	$start = (boolean) it_cart_buddy_protected_content_get_options( $post_id, 'when_time_period_start' );
	$end   = (boolean) it_cart_buddy_protected_content_get_options( $post_id, 'when_time_period_end' );
	$now   = current_time( 'timestamp' );

	$time_start = mysql2date( 'U', it_cart_buddy_protected_content_get_options( $post_id, 'when_time_period_start_date' ) . '00:01:01' );
	$time_end   = mysql2date( 'U', it_cart_buddy_protected_content_get_options( $post_id, 'when_time_period_end_date' ) . '23:59:00' );

	if ( ! $start || ( $start && '' == it_cart_buddy_protected_content_get_options( $post_id, 'when_time_period_start_date' ) ) )
		$start_valid = true;
	else
		$start_valid = $time_start < $now;

	if ( ! $end || ( $end && '' == it_cart_buddy_protected_content_get_options( $post_id, 'when_time_period_end_date' ) ) )
		$end_valid = true;
	else
		$end_valid = $time_end > $now;

	return $start_valid && $end_valid;
}

/**
 * Returns true or false based on the duration since purchase of a product and the protected content options
 *
 * NOT CURRENTLY USED
 *
 * @since 
 * @return boolean
*/
function DISABLED_MAYBE_LATER_it_cart_buddy_protected_content_current_user_can_access_content_based_on_purchase_duration( $post_id ) {
	// Return true if this content doesn't have any purchase duration time limits
	if ( 'duration-period' != it_cart_buddy_protected_content_get_options( $post_id, 'when_protected' ) )
		return true;

	// We need products to proceed
	$required_products = it_cart_buddy_protected_content_get_options( $post_id, 'selected_products' );
	if ( empty( $required_products ) )
		return false;

	die();
	$start = (boolean) it_cart_buddy_protected_content_get_options( $post_id, 'when_duration_start' );
	$end   = (boolean) it_cart_buddy_protected_content_get_options( $post_id, 'when_duration_end' );
	$now   = current_time( 'timestamp' );

	if ( ! $start && ! $end )
		return true;

	if ( $start ) {
		$start_quantity = (int) it_cart_buddy_protected_content_get_options( $post_id, 'when_duration_start_quantity' );
		$start_units    = it_cart_buddy_protected_content_get_options( $post_id, 'when_duration_start_units' );
		$start_delay    = strtotime( $start_quantity . ' ' . $start_units, '0' );
	}

	if ( $end ) {
		$end_quantity = (int) it_cart_buddy_protected_content_get_options( $post_id, 'when_duration_end_quantity' );
		$end_units    = it_cart_buddy_protected_content_get_options( $post_id, 'when_duration_end_units' );
		$end_delay    = strtotime( $end_quantity . ' ' . $end_units, '0' );
	}

	die();
}

/**
 * Return all protected content options or just one key from the options
 *
 * @since 0.3.8
 * @param integer $post_id WP Post id
 * @param string $option_key key being requested from protected content options
 * @return mixed false, single value, or array of values
*/
function it_cart_buddy_protected_content_get_options( $post_id, $option_key=false ) {
	if ( false === ( $options = get_post_meta( $post_id, '_it_cart_buddy_protected_content_options', true ) ) )
		return false;

	if ( $option_key )
		return empty( $options[$option_key] ) ? false : $options[$option_key];

	return $options;
}

/**
 * Returns the protected content based on the content options
 *
 * @since 0.3.8
 * @param $post_id
 * @return mixed content
*/
function it_cart_buddy_protected_content_get_protected_content( $post_id ) {
	$options = it_cart_buddy_protected_content_get_options( $post_id );

	if ( is_singular() ) {
		$action = $options['unauthorized_singular_action'];
		if ( 'excerpt' == $action ) {
			return get_the_excerpt();
		} else if ( 'custom_message' == $action ) {
			return $options['unauthorized_singular_custom_message'];
		}
	} else if ( is_search() ) {
		$action = $options['unauthorized_search_action'];
		if ( 'excerpt' == $action ) {
			return get_the_excerpt();
		} else if ( 'custom_message' == $action ) {
			return $options['unauthorized_search_custom_message'];
		}
	} else if ( is_feed() ) {
		$action = $options['unauthorized_feed_action'];
		if ( 'excerpt' == $action ) {
			return get_the_excerpt();
		} else if ( 'custom_message' == $action ) {
			return $options['unauthorized_feed_custom_message'];
		}
	} else {
		$action = $options['unauthorized_archive_action'];
		if ( 'excerpt' == $action ) {
			return get_the_excerpt();
		} else if ( 'custom_message' == $action ) {
			return $options['unauthorized_archive_custom_message'];
		}
	}
}

/**
 * Hides content based on protected content options
 *
 * @since 0.3.8
*/
function it_cart_buddy_protected_content_filter_content_and_excerpt( $existing ) {
	global $post;

	// Abandon if this post isn't protected based on role or product
	if ( ! it_cart_buddy_protected_content_is_object_protected( $post->ID ) )
		return $existing;

	// Abandon if current user can view current object
	if ( it_cart_buddy_protected_content_current_user_can_access_object( $post->ID ) )
		return $existing;

	// Get content based on protected content options
	$content = it_cart_buddy_protected_content_get_protected_content( $post->ID );
	return empty( $content ) ? $existing : $content;
}
add_filter( 'the_excerpt', 'it_cart_buddy_protected_content_filter_content_and_excerpt' );
add_filter( 'the_content', 'it_cart_buddy_protected_content_filter_content_and_excerpt' );
