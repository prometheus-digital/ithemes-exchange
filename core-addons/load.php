<?php
/**
 * Registers all add-ons shipped with iThemes Exchange
 *
 * @since 0.2.0
 * @uses apply_filters()
 * @uses it_exchange_register_add_on()
 * @return void
*/
function it_exchange_register_core_addons() {

	// An array of add-ons provided by iThemes Exchange
	$add_ons = array(
		// Manual Payments
		'manual-payments' => array(
			'name'        => __( 'Manual Payments', 'LION' ),
			'description' => __( 'Use this Transaction Method to take payments offline.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/transaction-methods/manual-payments/init.php',
			'options'     => array( 
				'category' => 'transaction-methods',
				'supports' => apply_filters( 'it_exchange_register_manual_payments_default_features', array(
					'transaction_status' => array(
						'key'       => '_it_exchange_transaction_status',
						'componant' => 'post_meta',
						'options'   => array(
							'pending'    => _x( 'Pending', 'Transaction Status', 'LION' ),
							'paid'		 => _x( 'Paid', 'Transaction Status', 'LION' ),
							'refunded'   => _x( 'Refunded', 'Transaction Status', 'LION' ),
							'voided'     => _x( 'Voided', 'Transaction Status', 'LION' ),
						),
						'default'   => 'pending',
					),
					'transaction_cart' => array(
						'key'       => '_it_exchange_transaction_cart',
						'componant' => 'post_meta',
						'default'   => false,
					),
				) ),
				'settings-callback' => 'it_exchange_manual_payments_settings_callback',
			),
		),
		// PayPal Standard Transaction Method
		'paypal-standard'   => array(
			'name'        => __( 'PayPal Standard', 'LION' ),
			'description' => __( 'Process Transactions with the PayPal Standard gateway.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/transaction-methods/paypal-standard/init.php',
			'options'     => array( 
				'category' => 'transaction-methods',
				'supports' => apply_filters( 'it_exchange_register_paypal_standard_default_features', array() ),
			),
			'options'     => array( 'category' => 'transaction-methods' ),
		),
		// Stripe Transaction Method
		'stripe'   => array(
			'name'        => __( 'Stripe', 'LION' ),
			'description' => __( 'Process Transactions with the Stripe payment gateway.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/transaction-methods/stripe/init.php',
			'options'     => array( 
				'category' => 'transaction-methods',
				'supports' => apply_filters( 'it_exchange_register_stripe_default_features', array() ),
			),
		),
		// Digital Download Product Types
		'digital-downloads-product-type' => array(
			'name'        => __( 'Digital Downloads', 'LION' ),
			'description' => __( 'This adds an product type for distributing digital downloads through iThemes Exchange.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-types/digital-downloads/init.php',
			'options'     => array( 
				'category' => 'product-type',
				'labels'   => array(
					'singular_name' => __( 'Digital Download', 'LION' ),
				),
				'supports' => apply_filters( 'it_exchange_register_digital_downloads_default_features', array(
				) ),
			),
		),
		// Membership Levels
		'memberships-product-type' => array(
			'name'        => __( 'Membership Levels', 'LION' ),
			'description' => __( 'Create different levels of access to your site.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-types/memberships/init.php',
			'options'     => array( 
				'category' => 'product-type',
				'labels'   => array(
					'singular_name' => __( 'Membership Level', 'LION' ),
				),
				'supports' => apply_filters( 'it_exchange_register_digital_downloads_default_features', array(
				) ),
			),
		),
		// Product Type admin Metabox
		'product-type-metabox' => array(
			'name'        => __( 'Product Type Metabox', 'LION' ),
			'description' => __( 'Gives admins the ability to change a Product Type after creation of the Product via a metabox', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/admin/product-type-metabox/init.php',
			'options'     => array( 'category' => 'admin' ),
		),
		// Transaction Status admin Metabox
		'transaction-status-metabox' => array(
			'name'        => __( 'Transaction Status Metabox', 'LION' ),
			'description' => __( 'Gives admins the ability to change a Transaction Status via a metabox after creation of the Transaction', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/admin/transaction-status-metabox/init.php',
			'options'     => array( 'category' => 'admin' ),
		),
		// Protected Content
		'protected-content' => array(
			'name'        => __( 'Protected Content', 'LION' ),
			'description' => __( 'This add-on will allow you to protect site content based on a number of conditions.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-features/protected-content/init.php',
			'options'     => array( 'category' => 'product-features' ),
		),
	);
	$add_ons = apply_filters( 'it_exchange_core_addons', $add_ons );

	// Loop through add-ons and register each one individually
	foreach( (array) $add_ons as $slug => $params ) {
		$name        = empty( $params['name'] )        ? false   : $params['name'];
		$description = empty( $params['description'] ) ? ''      : $params['description'];
		$file        = empty( $params['file'] )        ? false   : $params['file'];
		$options     = empty( $params['options'] )     ? array() : (array) $params['options'];

		it_exchange_register_addon( $slug, $name, $description, $file, $options );
	}
}
add_action( 'it_libraries_loaded', 'it_exchange_register_core_addons' );

/**
 * Register's Core iThemes Exchange Add-on Categories
 *
 * @since 0.2.0
 * @uses it_exchange_register_add_on_category()
 * @return void
*/
function it_exchange_register_core_addon_categories() {
	// An array of our core add-on categories
	$cats = array(
		'product-type' => array(
			'name'        => __( 'Product Type', 'LION' ),
			'description' => __( 'Add-ons responsible for the differing types of products available in iThemes Exchange.', 'LION' ),
			'options'     => array(
			),
		),
		'transaction-methods' => array(
			'name'        => __( 'Transaction Methods', 'LION' ),
			'description' => __( 'Add-ons that create transactions. eg: Stripe, PayPal.', 'LION' ),
			'options'     => array(
				'supports' => apply_filters( 'it_exchange_register_transaction_method_supports', array(
					'title' => array(
						'key'       => 'post_title',
						'componant' => 'post_type_support',
						'default'   => false,
					),
					'transaction_status' => array(
						'key'       => '_it_exchange_transaction_status',
						'componant' => 'post_meta',
						'options'   => array(
							'pending'    => _x( 'Pending', 'Transaction Status', 'LION' ),
							'authorized' => _x( 'Authorized', 'Transaction Status', 'LION' ),
							'paid'		 => _x( 'Paid', 'Transaction Status', 'LION' ),
							'refunded'   => _x( 'Refunded', 'Transaction Status', 'LION' ),
							'voided'     => _x( 'Voided', 'Transaction Status', 'LION' ),
						),
						'default'   => 'pending',
					)
				) ),
			),
		),
		'shopping-carts' => array(
			'name'        => __( 'Shopping Cart UIs', 'LION' ),
			'description' => __( 'Add-ons that provide a UI for the iThemes Exchange Cart API.', 'LION' ),
			'options'     => array(),
		),
		'customer-management' => array(
			'name'        => __( 'Customer Management', 'LION' ),
			'description' => __( 'Add-ons that provide a UI for the iThemes Exchange Customer Management.', 'LION' ),
			'options'     => array(),
		),
		'admin' => array(
			'name'        => __( 'Admin Add-ons', 'LION' ),
			'description' => __( 'Add-ons that create general purpose admin functionality. eg: Reports, Export.', 'LION' ),
			'options'     => array(),
		),
		'product-features' => array(
			'name'        => __( 'Product Features', 'LION' ),
			'description' => __( 'Add-ons that provide optional features to product type add-ons', 'LION' ),
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
	$cats = apply_filters( 'it_exchange_core_addon_categories', $cats );

	// Loop through categories and register each one individually
	foreach( (array) $cats as $slug => $params ) {
		$name        = empty( $params['name'] )        ? false   : $params['name'];
		$description = empty( $params['description'] ) ? ''      : $params['description'];
		$options     = empty( $params['options'] )     ? array() : (array) $params['options'];

		it_exchange_register_addon_category( $slug, $name, $description, $options );
	}
}
add_action( 'it_libraries_loaded', 'it_exchange_register_core_addon_categories' );
