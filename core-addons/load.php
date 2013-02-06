<?php
/**
 * Registers all add-ons shipped with Cart Buddy
 *
 * @since 0.2.0
 * @uses apply_filters()
 * @uses it_cart_buddy_register_add_on()
 * @return void
*/
function it_cart_buddy_register_core_add_ons() {

	// An array of add-ons provided by Cart Buddy
	$add_ons = array(
		// Manual Payments
		'manual-payments' => array(
			'name'        => __( 'Manual Payments', 'LION' ),
			'description' => __( 'Use this Transaction Method to take payments offline.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/transaction-methods/manual-payments/init.php',
			'options'     => array( 'category' => 'transaction-methods' ),
		),
		// PayPal Standard Transaction Method
		'paypal-standard'   => array(
			'name'        => __( 'PayPal Standard', 'LION' ),
			'description' => __( 'Process Transactions with the PayPal Standard gateway.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/transaction-methods/paypal-standard/init.php',
			'options'     => array( 'category' => 'transaction-methods' ),
		),
		// Stripe Transaction Method
		'stripe'   => array(
			'name'        => __( 'Stripe', 'LION' ),
			'description' => __( 'Process Transactions with the Stripe payment gateway.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/transaction-methods/stripe/init.php',
			'options'     => array( 'category' => 'transaction-methods' ),
		),
		// Digital Download Product Types
		'digital-downloads' => array(
			'name'        => __( 'Digital Downloads', 'LION' ),
			'description' => __( 'This adds an product type for distributing digital downloads through Cart Buddy.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-types/digital-downloads/init.php',
			'options'     => array( 
				'category' => 'product-type',
				'labels'   => array(
					'singular_name' => __( 'Digital Download', 'LION' ),
				),
			),
		),
		// Membership Levels
		'memberships' => array(
			'name'        => __( 'Memberships', 'LION' ),
			'description' => __( 'Create different levels of access to your site.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-types/memberships/init.php',
			'options'     => array( 
				'category' => 'product-type',
				'labels'   => array(
					'singular_name' => __( 'Membership', 'LION' ),
				),
			),
		),
		// Default Shopping Cart UI 
		'default-shopping-cart' => array(
			'name'        => __( 'Default Shopping Cart', 'LION' ),
			'description' => __( 'This is the default Shopping Cart UI. It is the visual front to Cart Buddy\'s Cart API', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/shopping-carts/default/init.php',
			'options'     => array( 'category' => 'shopping-carts' ),
		),
		// Product Type admin Metabox
		'product-type-metabox' => array(
			'name'        => __( 'Product Type Metabox', 'LION' ),
			'description' => __( 'Gives admins the ability to change a Product Type after creation of the Product via a metabox', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/admin/product-type-metabox/init.php',
			'options'     => array( 'category' => 'admin' ),
		),
	);
	$add_ons = apply_filters( 'it_cart_buddy_core_add_ons', $add_ons );

	// Loop through add-ons and register each one individually
	foreach( (array) $add_ons as $slug => $params ) {
		$name        = empty( $params['name'] )        ? false   : $params['name'];
		$description = empty( $params['description'] ) ? ''      : $params['description'];
		$file        = empty( $params['file'] )        ? false   : $params['file'];
		$options     = empty( $params['options'] )     ? array() : (array) $params['options'];

		it_cart_buddy_register_add_on( $slug, $name, $description, $file, $options );
	}
}
add_action( 'plugins_loaded', 'it_cart_buddy_register_core_add_ons' );

/**
 * Register's Core Cart Buddy Add-on Categories
 *
 * @since 0.2.0
 * @uses it_cart_buddy_register_add_on_category()
 * @return void
*/
function it_cart_buddy_register_core_add_on_categories() {
	// An array of our core add-on categories
	$cats = array(
		'product-type' => array(
			'name'        => __( 'Product Type', 'LION' ),
			'description' => __( 'Add-ons responsible for the differing types of products available in Cart Buddy.', 'LION' ),
			'options'     => array(),
		),
		'transaction-methods' => array(
			'name'        => __( 'Transaction Methods', 'LION' ),
			'description' => __( 'Add-ons that create transactions. eg: Stripe, PayPal.', 'LION' ),
			'options'     => array(),
		),
		'shopping-carts' => array(
			'name'        => __( 'Shopping Cart UIs', 'LION' ),
			'description' => __( 'Add-ons that provide a UI for the Cart Buddy Cart API.', 'LION' ),
			'options'     => array(),
		),
		'admin' => array(
			'name'        => __( 'Admin Add-ons', 'LION' ),
			'description' => __( 'Add-ons that create general purpose admin functionality. eg: Reports, Export.', 'LION' ),
			'options'     => array(),
		),
		'front-end' => array(
			'name'        => __( 'Front End Add-ons', 'LION' ),
			'description' => __( 'Add-ons that create general purpose frontend functionality. eg: Widgets, Shortcodes.', 'LION' ),
			'options'     => array(),
		),
		'other' => array(
			'name'        => __( 'Other', 'LION' ),
			'description' => __( 'Add-ons that don\'t fit in any other add-on category.', 'LION' ),
			'options'     => array(),
		),
	);
	$cats = apply_filters( 'it_cart_buddy_core_add_on_categories', $cats );

	// Loop through categories and register each one individually
	foreach( (array) $cats as $slug => $params ) {
		$name        = empty( $params['name'] )        ? false   : $params['name'];
		$description = empty( $params['description'] ) ? ''      : $params['description'];
		$options     = empty( $params['options'] )     ? array() : (array) $params['options'];

		it_cart_buddy_register_add_on_category( $slug, $name, $description, $options );
	}
}
add_action( 'plugins_loaded', 'it_cart_buddy_register_core_add_on_categories' );
