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
 * Returns an array of cart item columns, filterable by add-ons
 *
 * @since 1.1.0 
 *
 * @return array
*/
function it_exchange_get_cart_item_columns() {
    $columns = array(
        'featured-image',
        'title',
        'quantity',
        'subtotal',
        'remove',
    );
    $columns = apply_filters( 'it_exchange_get_cart_item_columns', $columns );
    return (array) $columns;
}

/**
 * Returns an array of cart coupon columns, filterable by add-ons
 *
 * @since 1.1.0 
 *
 * @return array
*/
function it_exchange_get_cart_coupon_columns() {
    $columns = array(
    );
    $columns = apply_filters( 'it_exchange_get_cart_coupon_columns', $columns );
    return (array) $columns;
}

/**
 * Returns an array of cart coupon columns, filterable by add-ons
 *
 * @since 1.1.0
 *
 * @return array
*/
function it_exchange_get_cart_totals_columns() {
    $columns = array(
        'titles',
        'amounts',
    );  
    $columns = apply_filters( 'it_exchange_get_cart_totals_columns', $columns );
    return (array) $columns;
}

/**
 * Returns an array of cart coupon column rows, filterable by add-ons
 *
 * @since 1.1.0 
 *
 * @return array
*/
function it_exchange_get_cart_totals_column_rows() {
    $columns = array(
        'subtotal',
        'savings',
        'total',
    );  
    $columns = apply_filters( 'it_exchange_get_cart_totals_column_rows', $columns );
    return (array) $columns;
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
        'update',
        'empty',
        'checkout',
    );  
    $actions = apply_filters( 'it_exchange_get_cart_actions', $actions );
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
