<?php
/**
 * This file includes all of the calls that allow add-ons to interact with the template API
 * @since 1.1.0
 * @package IT_Exchange
*/

/*********************************
 * lib/templats/content-cart.php *
*********************************/

/**
 * Returns an array of cart item details, filterable by add-ons
 *
 * @since 1.1.0 
 *
 * @return array
*/
function it_exchange_get_content_cart_item_details() {
	$details = array(
		'featured-image',
		'title',
		'quantity',
		'subtotal',
		'remove',
	);
	$details = apply_filters( 'it_exchange_get_content_cart_item_details', $details );
	return (array) $details;
}

/**
 * Returns an array of cart coupon details, filterable by add-ons
 *
 * @since 1.1.0 
 *
 * @return array
*/
function it_exchange_get_content_cart_coupon_details() {
	$details = array(
		'code',
		'discount',
		'remove',
	);
	$details = apply_filters( 'it_exchange_get_content_cart_coupon_details', $details );
	return (array) $details;
}

/**
 * Returns an array of cart coupon details, filterable by add-ons
 *
 * @since 1.1.0
 *
 * @return array
*/
function it_exchange_get_content_cart_totals_details() {
	$details = array(
		'subtotal',
		'savings',
		'total',
	);  
	$details = apply_filters( 'it_exchange_get_content_cart_totals_details', $details );
	return (array) $details;
}

/**
 * Returns an array of cart actions, filterable by add-ons
 *
 * @since 1.1.0 
 *
 * @return array
*/
function it_exchange_get_content_cart_action_details() {
	$actions = array(
		'apply-coupon',
		'update',
		'empty',
		'checkout',
	);
	$actions = apply_filters( 'it_exchange_get_content_cart_action_details', $actions );
	return (array) $actions;
}

/*****************************************
 * lib/templates/content-confirmation.php *
*****************************************/

/**
 * Returns an array of elements used in the confirmation transaction meta template
 *
 * @since 1.1.0
 *
 * @return array
*/ 
function it_exchange_get_confirmation_template_transaction_meta_elements() {
	$elements = array(
		'date',
		'status',
		'total',
		'instructions',
	);  
	$elements = apply_filters( 'it_exchange_get_confirmation_template_transaction_meta_elements', $elements );
	return (array) $elements;
}

/*****************************************
 * lib/templates/content-login.php *
*****************************************/

/**
 * Returns an array of fields used in the content-login template part
 *
 * @since 1.1.0
 *
 * @return array
*/ 
function it_exchange_get_content_login_field_details() {
	$details = array(
		'username',
		'password',
		'rememberme',
	);  
	$details = apply_filters( 'it_exchange_get_content_login_field_details', $details );
	return (array) $details;
}

/**
 * Returns an array of actions used in the content-login template part
 *
 * @since 1.1.0
 *
 * @return array
*/ 
function it_exchange_get_content_login_action_details() {
	$details = array(
		'login-button',
		'recover',
		'register',
	);  
	$details = apply_filters( 'it_exchange_get_content_login_action_details', $details );
	return (array) $details;
}

/*****************************************
 * lib/templates/content-registration.php *
*****************************************/

/**
 * Returns an array of fields used in the content-registration template part
 *
 * @since 1.1.0
 *
 * @return array
*/ 
function it_exchange_get_content_registration_field_details() {
	$details = array(
		'username',
		'first-name',
		'last-name',
		'email',
		'password1',
		'password2',
    );  
    $details = apply_filters( 'it_exchange_get_content_registration_field_details', $details );
    return (array) $details;
}

/**
 * Returns an array of profile actions, filterable by add-ons
 *
 * @since 1.1.0 
 *
 * @return array
*/
function it_exchange_get_content_registration_actions() {
    $actions = array(
		'save',
		'login',
    );  
    $actions = apply_filters( 'it_exchange_get_content_registration_actions', $actions );
    return (array) $actions;
}

/*****************************************
 * lib/templates/content-profile.php *
*****************************************/

/**
 * Returns an array of fields used in the content-profile template part
 *
 * @since 1.1.0
 *
 * @return array
*/ 
function it_exchange_get_content_profile_field_details() {
    $details = array(
        'first-name',
        'last-name',
        'email',
        'website',
		'password1',
		'password2',
    );  
    $details = apply_filters( 'it_exchange_get_content_profile_field_details', $details );
    return (array) $details;
}

/**
 * Returns an array of profile actions, filterable by add-ons
 *
 * @since 1.1.0 
 *
 * @return array
*/
function it_exchange_get_content_profile_actions() {
    $actions = array(
		'save',
    );  
    $actions = apply_filters( 'it_exchange_get_content_profile_actions', $actions );
    return (array) $actions;
}

/*****************************************
 * lib/templates/content-product.php *
*****************************************/

/**
 * Returns an array of product features used in a content-product template part loop
 *
 * @since 1.1.0
 *
 * @param array $details an array of product_features. eg: array( 'base-price, 'description', 'super-widget' )
 * @return array
*/ 
function it_exchange_get_content_product_feature_details( $details=array() ) {

	// Allow add-ons to filter
    $details = apply_filters( 'it_exchange_get_content_product_feature_details', $details );

    return (array) $details;
}

/*****************************************
 * lib/templates/store-product.php *
*****************************************/

/**
 * Returns an array of product features used in a store-product template part loop
 *
 * @since 1.1.0
 *
 * @param array $details an array of product_features. eg: array( 'title', 'base-price, 'permalink' )
 * @return array
*/ 
function it_exchange_get_store_product_feature_details( $details=array() ) {

	// Allow add-ons to filter
    $details = apply_filters( 'it_exchange_get_store_product_feature_details', $details );

    return (array) $details;
}

/*****************************************
 * lib/templates/content-purchases.php *
*****************************************/

/**
 * Returns an array of product features used in a content-purchases template part loop
 *
 * @since 1.1.0
 *
 * @param array $details an array of product_features. eg: array( 'base-price, 'description', 'super-widget' )
 * @return array
*/ 
function it_exchange_get_content_purchases_feature_details( $details=array() ) {

	// Allow add-ons to filter
    $details = apply_filters( 'it_exchange_get_content_product_feature_details', $details );

    return (array) $details;
}

/*****************************************
 * lib/templates/super-widget-cart.php *
*****************************************/

/**
 * Returns an array of product features used in a super-widget-cart-item template part loop
 *
 * @since 1.1.0
 *
 * @param array $details an array of product_features. eg: array( 'title', 'base-price, 'permalink' )
 * @return array
*/ 
function it_exchange_get_super_widget_cart_item_details( $details=array() ) {

	// Allow add-ons to filter
    $details = apply_filters( 'it_exchange_get_super_widget_cart_item_details', $details );

    return (array) $details;
}
