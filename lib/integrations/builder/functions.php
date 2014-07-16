<?php
/**
 * Functions for integration with Builder.
 *
 * @package IT_Exchange
*/

/**
 * This unsets the views added by Exchange's custom
 * post types.
 *
 * @package IT_Exchange
 * @since 1.4.0
 * @author Justin Kopepasah
 * @var $views
*/
function it_exchange_remove_default_exchange_builder_views( $views ) {
	foreach ( $views as $view => $data ) {
		if ( false !== strpos( $view, 'it_exchange_' ) )
			unset( $views[$view] );
	}
	return $views;
}
add_filter( 'builder_get_available_views', 'it_exchange_remove_default_exchange_builder_views', 100 );

/**
 * Builder theme callback to determine if this is any Exchange view
 *
 * @since 1.6.2
 * @return boolean
*/
function it_exchange_is_exchange_builder_view() {

	// Grab all registered exchange views
	$registered_exchange_views = it_exchange_add_new_builder_views( array() );

	// Remove this view
	unset( $registered_exchange_views['it_exchange_is_exchange_builder_view'] );

	// Convert to keys
	$registered_exchange_views =  array_keys( $registered_exchange_views );

	// Loop through views and if we find that we're on one, return true
	foreach( $registered_exchange_views as $callback ) {
		if ( function_exists( $callback ) && is_callable( $callback ) ) {
			if ( call_user_func( $callback ) )
				return true;
		}
	}

	// If we made it this far, return false
	return false;
}

/**
 * Builder theme callback to determine if this a product view
 *
 * @package IT_Exchange
 * @since 1.4.0
 * @author Justin Kopepasah
*/
function it_exchange_is_product_builder_view() {
    return it_exchange_is_page( 'product' );
}

/**
 * Builder theme callback to determine if this a storeview
 *
 * @package IT_Exchange
 * @since 1.4.0
 * @author Justin Kopepasah
*/
function it_exchange_is_store_builder_view() {
    return it_exchange_is_page( 'store' );
}

/**
 * Builder theme callback to determine if this a transaction view
 *
 * @package IT_Exchange
 * @since 1.4.0
 * @author Justin Kopepasah
*/
function it_exchange_is_transaction_builder_view() {
    return it_exchange_is_page( 'transaction' );
}

/**
 * Builder theme callback to determine if this a registration view
 *
 * @package IT_Exchange
 * @since 1.4.0
 * @author Justin Kopepasah
*/
function it_exchange_is_registration_builder_view() {
    return it_exchange_is_page( 'registration' );
}

/**
 * Builder theme callback to determine if this a account view
 *
 * @package IT_Exchange
 * @since 1.4.0
 * @author Justin Kopepasah
*/
function it_exchange_is_account_builder_view() {
    return it_exchange_is_page( 'account' );
}

/**
 * Builder theme callback to determine if this a profile view
 *
 * @package IT_Exchange
 * @since 1.4.0
 * @author Justin Kopepasah
*/
function it_exchange_is_profile_builder_view() {
    return it_exchange_is_page( 'profile' );
}

/**
 * Builder theme callback to determine if this a downloads view
 *
 * @package IT_Exchange
 * @since 1.4.0
 * @author Justin Kopepasah
*/
function it_exchange_is_downloads_builder_view() {
    return it_exchange_is_page( 'downloads' );
}

/**
 * Builder theme callback to determine if this a purchases view
 *
 * @package IT_Exchange
 * @since 1.4.0
 * @author Justin Kopepasah
*/
function it_exchange_is_purchases_builder_view() {
    return it_exchange_is_page( 'purchases' );
}

/**
 * Builder theme callback to determine if this a login view
 *
 * @package IT_Exchange
 * @since 1.4.0
 * @author Justin Kopepasah
*/
function it_exchange_is_login_builder_view() {
    return it_exchange_is_page( 'login' );
}

/**
 * Builder theme callback to determine if this a logout view
 *
 * @package IT_Exchange
 * @since 1.4.0
 * @author Justin Kopepasah
*/
function it_exchange_is_logout_builder_view() {
    return it_exchange_is_page( 'logout' );
}

/**
 * Builder theme callback to determine if this a confirmation view
 *
 * @package IT_Exchange
 * @since 1.4.0
 * @author Justin Kopepasah
*/
function it_exchange_is_confirmation_builder_view() {
    return it_exchange_is_page( 'confirmation' );
}

/**
 * Builder theme callback to determine if this a cart view
 *
 * @package IT_Exchange
 * @since 1.4.0
 * @author Justin Kopepasah
*/
function it_exchange_is_cart_builder_view() {
    return it_exchange_is_page( 'cart' );
}

/**
 * Builder theme callback to determine if this a checkout view
 *
 * @package IT_Exchange
 * @since 1.4.0
 * @author Justin Kopepasah
*/
function it_exchange_is_checkout_builder_view() {
    return it_exchange_is_page( 'checkout' );
}

/**
 * Builder theme callback to determine if this a specfic product type singular view
 *
 * @package IT_Exchange
 * @since 1.6.2
 * @return boolean
*/
function it_exchange_is_builder_product_type_view( $type ) {
	if ( ! it_exchange_is_page( 'product' ) )
		return false;

	return ( it_exchange_get_product_type() == $type );
}

/**
 * Builder theme callback to determine if this a Cateogry Taxonomy view
 *
 * @package IT_Exchange
 * @since CHANGEME
*/
function it_exchange_is_product_category_builder_view() {
	return is_tax( 'it_exchange_category' ) && ! empty( $GLOBALS['wp_query']->query['it_exchange_category'] );
}

/**
 * Builder theme callback to determine if this a Cateogry Taxonomy view
 *
 * @package IT_Exchange
 * @since CHANGEME
*/
function it_exchange_is_product_tag_builder_view() {
	return is_tax( 'it_exchange_tag' ) && ! empty( $GLOBALS['wp_query']->query['it_exchange_tag'] );
}

/**
 * Add the views to Builder's list of available views.
 *
 * @package IT_Exchange
 * @since 1.4.0
 * @author Justin Kopepasah
 * @var $views
*/
function it_exchange_add_new_builder_views( $views ) {

	// Basic Exchange Views
	$exchange_views = array(
		'it_exchange_is_product_builder_view' => array(
			'name'        => _x( 'Exchange - Product', 'view', 'LION' ),
			'priority'    => '40',
			'description' => __( 'Any Exchange product.', 'LION' ),
		),
		'it_exchange_is_store_builder_view' => array(
			'name'        => _x( 'Exchange - Store', 'view', 'LION' ),
			'priority'    => '30',
			'description' => __( 'The Exchange store page.', 'LION' ),
		),
		'it_exchange_is_transaction_builder_view' => array(
			'name'        => _x( 'Exchange - Transaction', 'view', 'LION' ),
			'priority'    => '30',
			'description' => __( 'The Exchange transactions page.', 'LION' ),
		),
		'it_exchange_is_registration_builder_view' => array(
			'name'        => _x( 'Exchange - Registration', 'view', 'LION' ),
			'priority'    => '30',
			'description' => __( 'The Exchange registration page.', 'LION' ),
		),
		'it_exchange_is_account_builder_view' => array(
			'name'        => _x( 'Exchange - Account', 'view', 'LION' ),
			'priority'    => '30',
			'description' => __( 'The Exchange customer account page.', 'LION' ),
		),
		'it_exchange_is_profile_builder_view' => array(
			'name'        => _x( 'Exchange - Profile', 'view', 'LION' ),
			'priority'    => '30',
			'description' => __( 'The Exchange customer profile page.', 'LION' ),
		),
		'it_exchange_is_downloads_builder_view' => array(
			'name'        => _x( 'Exchange - Downloads', 'view', 'LION' ),
			'priority'    => '30',
			'description' => __( 'The Exchange customer downloads page.', 'LION' ),
		),
		'it_exchange_is_purchases_builder_view' => array(
			'name'        => _x( 'Exchange - Purchases', 'view', 'LION' ),
			'priority'    => '30',
			'description' => __( 'The Exchange customer purchases page.', 'LION' ),
		),
		'it_exchange_is_login_builder_view' => array(
			'name'        => _x( 'Exchange - Login', 'view', 'LION' ),
			'priority'    => '30',
			'description' => __( 'The Exchange login page.', 'LION' ),
		),
		'it_exchange_is_logout_builder_view' => array(
			'name'        => _x( 'Exchange - Logout', 'view', 'LION' ),
			'priority'    => '30',
			'description' => __( 'The Exchange logout page.', 'LION' ),
		),
		'it_exchange_is_confirmation_builder_view' => array(
			'name'        => _x( 'Exchange - Confirmation', 'view', 'LION' ),
			'priority'    => '30',
			'description' => __( 'The Exchange purchase confirmation page.', 'LION' ),
		),
		'it_exchange_is_exchange_builder_view' => array(
			'name'        => _x( 'Exchange - Global', 'view', 'LION' ),
			'priority'    => '30',
			'description' => __( 'Any page generated by iThemes Exchange.', 'LION' ),
		),
	);

	$multi_item_views = array(
		'it_exchange_is_cart_builder_view' => array(
			'name'        => _x( 'Exchange - Cart', 'view', 'LION' ),
			'priority'    => '30',
			'description' => __( 'The Exchange cart page.', 'LION' ),
		),
		'it_exchange_is_checkout_builder_view' => array(
			'name'        => _x( 'Exchange - Checkout', 'view', 'LION' ),
			'priority'    => '30',
			'description' => __( 'The Exchange checkout page.', 'LION' ),
		),
	);

	// Merge in core Exchange views
	$views = array_merge( $views, $exchange_views );

	// Merge in Multi-Item Cart Views
	if ( it_exchange_is_multi_item_cart_allowed() )
		$views = array_merge( $multi_item_views, $views );

	// Product Type views
	$product_type_views = array();
	foreach( it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) ) as $type ) {
		$title = empty( $type['labels']['singular_name'] ) ? $type['name'] : $type['labels']['singular_name'];
		$product_type_views['it_exchange_is_builder_product_type_view|' . $type['slug']] = array(
			'name' => 'Exchange Product Type - ' . $title,
			'priority' => '41',
			'description' => sprintf( __( 'All %s product type single product views', 'LION' ), $title ),
		);
	}

	// Merge in Product Type Views
	$views = array_merge( $views, $product_type_views );

	// Taxonomy Views
	if ( it_exchange_is_addon_enabled( 'taxonomy-type-category' ) ) {
		$tax_views['it_exchange_is_product_category_builder_view'] = array(
			'name'        => _x( 'Exchange - Product Categories', 'view', 'LION' ),
			'priority'    => 30,
			'description' => __( 'The archive view for the Categories taxonomy', 'LION' ),
		);
		$views = array_merge( $views, $tax_views );
		unset( $tax_views );
	}
	if ( it_exchange_is_addon_enabled( 'taxonomy-type-tag' ) ) {
		$tax_views['it_exchange_is_product_tag_builder_view'] = array(
			'name'        => _x( 'Exchange - Product Tags', 'view', 'LION' ),
			'priority'    => 30,
			'description' => __( 'The archive view for the Tags taxonomy', 'LION' ),
		);
		$views = array_merge( $views, $tax_views );
		unset( $tax_views );
	}


    return $views;
}
add_filter( 'builder_get_available_views', 'it_exchange_add_new_builder_views', 100 );
