<?php

/**
 * Redirect if the current user doesn't have permission to view the current post
 *
 * @since 0.3.8
 * @return void
*/
function it_cart_buddy_protected_content_redirect_if_protected() {
	global $post;

	if ( ! is_singular() )
		return;

	if ( ! it_cart_buddy_protected_content_is_object_protected( $post->ID ) )
		return;

	if ( it_cart_buddy_protected_content_current_user_can_access_object( $post->ID ) )
		return;

	$redirect_to  = get_post_meta( $post->ID, '_it_cart_buddy_protected_content_addon_post_protected_redirect', true );
	wp_redirect( $redirect_to );
	die();
}
add_action( 'template_redirect', 'it_cart_buddy_protected_content_redirect_if_protected' );

/**
 * Is the passed ID marked as being complety protected
 *
 * @since 0.3.8
 * @return boolean
*/
function it_cart_buddy_protected_content_is_object_protected( $post_id ) {
	return (boolean) get_post_meta( $post_id, '_it_cart_buddy_protected_content_addon_post_is_protected', true );
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
	
	// Return false if object was marked as protected but no products were checked
	if ( ! $required_products = it_cart_buddy_protected_content_get_required_products_for_protected_object( $post_id ) )
		return false;

	// Get User products. Return false if there is no purchase history
	if ( ! $purchase_history = $current_user->get_purchase_history() )
		return false;

	// Loop through required products and make sure it is in purchase history
	$user_purchased_required_products = it_cart_buddy_protected_content_required_products_in_purchase_history( $required_products, $purchase_history );
	if ( empty( $user_purchased_required_products ) )
		return false;

	// At this point we have confirmation that the current user has purchased a required product in the past.
	// We're going to let individual product-type plugins tell us if they need more fine-tuned checks
	foreach( $user_purchased_required_products as $product_id => $product_purchases ) {
		if ( ! it_cart_buddy_protected_content_user_has_valid_purchase_of_product( $current_user->id, $product_purchases ) )
			unset( $user_purchased_required_products[$key] );
	}
	if ( empty ( $user_purchased_required_products ) )
		return false;

	// Return true
	return true;
}

/**
 * Return an array of products required to have been purchased in order to view a protected post_type object
 *
 * @since 0.3.8
 * @param integer $post_id the id of a row from the wp_posts table
 * @return mixed an array or false
*/
function it_cart_buddy_protected_content_get_required_products_for_protected_object( $post_id ) {
	$products = get_post_meta( $post_id, '_it_cart_buddy_protected_content_addon_selected_products', true );
	return empty( $products ) ? false : $products;
}

/**
 * Is one of the required products in the purchase history
 *
 * @since 0.3.8
 * @param array $required_products
 * @param array $purchase_history
*/
function it_cart_buddy_protected_content_required_products_in_purchase_history( $required_products, $purchase_history ) {
	$user_purchased_required_products = array();
	foreach( $required_products as $product ) {
		if ( isset( $purchase_history[$product] ) )
			$user_purchased_required_products[$product] = $purchase_history[$product];
	}
	return $user_purchased_required_products;
}

/**
 * Return true or false. Does the user have a valid purchase of one or more of the required products
 *
 * Allow product types to hook in and determine if a specific purchase is still valid
 *
 * @since 0.3.8
 * @param integer $user_id
 * @param an array of products purchased by the customer.
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
