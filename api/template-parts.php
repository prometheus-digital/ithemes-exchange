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
        'titles',
        'amounts',
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
function it_exchange_get_cart_actions() {
    $actions = array(
		'apply-coupon',
        'update',
        'empty',
        'checkout',
    );  
    $actions = apply_filters( 'it_exchange_get_content_cart_actions', $actions );
    return (array) $actions;
}

/*****************************************
 * lib/templats/content-confirmation.php *
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
