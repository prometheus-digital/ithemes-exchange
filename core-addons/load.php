<?php
/**
 * Registers all add-ons shipped with Cart Buddy
 *
 * @since 0.2.0
 * @uses apply_filters()
 * @uses it_cart_buddy_register_add_on()
 * @return void
*/
function it_cart_buddy_register_core_addons() {

	// An array of add-ons provided by Cart Buddy
	$add_ons = array(
		// Manual Payments
		'manual-payments' => array(
			'name'        => __( 'Manual Payments', 'LION' ),
			'description' => __( 'Use this Transaction Method to take payments offline.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/transaction-methods/manual-payments/init.php',
			'options'     => array( 
				'category' => 'transaction-methods',
				'supports' => apply_filters( 'it_cart_buddy_register_manual_payments_default_features', array(
					'transaction_status' => array(
						'key'       => '_it_cart_buddy_transaction_status',
						'componant' => 'post_meta',
						'options'   => array(
							'pending'    => _x( 'Pending', 'Transaction Status', 'LION' ),
							'paid'		 => _x( 'Paid', 'Transaction Status', 'LION' ),
							'refunded'   => _x( 'Refunded', 'Transaction Status', 'LION' ),
							'voided'     => _x( 'Voided', 'Transaction Status', 'LION' ),
						),
						'default'   => 'pending',
					)
				) ),
				'settings-callback' => 'it_cart_buddy_manual_payments_settings_callback',
			),
		),
		// PayPal Standard Transaction Method
		'paypal-standard'   => array(
			'name'        => __( 'PayPal Standard', 'LION' ),
			'description' => __( 'Process Transactions with the PayPal Standard gateway.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/transaction-methods/paypal-standard/init.php',
			'options'     => array( 
				'category' => 'transaction-methods',
				'supports' => apply_filters( 'it_cart_buddy_register_paypal_standard_default_features', array() ),
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
				'supports' => apply_filters( 'it_cart_buddy_register_stripe_default_features', array() ),
			),
		),
		// Digital Download Product Types
		'digital-downloads-product-type' => array(
			'name'        => __( 'Digital Downloads', 'LION' ),
			'description' => __( 'This adds an product type for distributing digital downloads through Cart Buddy.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-types/digital-downloads/init.php',
			'options'     => array( 
				'category' => 'product-type',
				'labels'   => array(
					'singular_name' => __( 'Digital Download', 'LION' ),
				),
				'supports' => apply_filters( 'it_cart_buddy_register_digital_downloads_default_features', array(
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
				'supports' => apply_filters( 'it_cart_buddy_register_digital_downloads_default_features', array(
				) ),
			),
		),
		// Default Customer Management interface
		'default-customer-management' => array(
			'name'        => __( 'Default Customer Managment', 'LION' ),
			'description' => __( 'This is the default Customer Management add-on. It handles registration and profile data.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/customer-management/default/init.php',
			'options'     => array( 'category' => 'customer-management' ),
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
		// Digital Downloads
		'digital-downloads' => array(
			'name'        => __( 'Digital Downloads', 'LION' ),
			'description' => __( 'This add-on will allow files to be added to products. Once a product is purchase, the customer has access to the files.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-features/digital-downloads/init.php',
			'options'     => array( 'category' => 'product-features' ),
		),
		// Base Price
		'protected-content' => array(
			'name'        => __( 'Protected Content', 'LION' ),
			'description' => __( 'This add-on will allow you to protect site content based on purchased products.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-features/protected-content/init.php',
			'options'     => array( 'category' => 'product-features' ),
		),
		// Product description (post_content)
		'product-description' => array(
			'name'        => __( 'Product Description', 'LION' ),
			'description' => __( 'The description of the product. Maps to post_content.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-features/product-description/init.php',
			'options'     => array( 'category' => 'product-features' ),
		),
		// Product title (post_title)
		'product-title' => array(
			'name'        => __( 'Product Title', 'LION' ),
			'description' => __( 'Enables the WordPress post Title for products.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-features/product-title/init.php',
			'options'     => array( 'category' => 'product-features' ),
		),
		// Product Author
		'wp-author' => array(
			'name'        => __( 'WP Post Author', 'LION' ),
			'description' => __( 'Enables the WordPress post author field for products.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-features/wp-author/init.php',
			'options'     => array( 'category' => 'product-features' ),
		),
		// Featured Image
		'wp-featured-image' => array(
			'name'        => __( 'WP Featured Image', 'LION' ),
			'description' => __( 'Enables the WordPress featured image functionality for products.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-features/wp-featured-image/init.php',
			'options'     => array( 'category' => 'product-features' ),
		),
		// Excerpt
		'wp-excerpt' => array(
			'name'        => __( 'WP Excerpts for Products', 'LION' ),
			'description' => __( 'Enables the WordPress excerpt metabox for products.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-features/wp-excerpt/init.php',
			'options'     => array( 'category' => 'product-features' ),
		),
		// Custom Fields 
		'wp-custom-fields' => array(
			'name'        => __( 'WP Custom Fields', 'LION' ),
			'description' => __( 'Enables the WordPress custom fields metabox for products.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-features/wp-custom-fields/init.php',
			'options'     => array( 'category' => 'product-features' ),
		),
		// Product Trackbacks 
		'wp-trackbacks' => array(
			'name'        => __( 'WP Trackbacks for Products', 'LION' ),
			'description' => __( 'Enables the WordPress comments metabox for products.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-features/wp-trackbacks/init.php',
			'options'     => array( 'category' => 'product-features' ),
		),
		// Product Comments 
		'wp-comments' => array(
			'name'        => __( 'WP Comments for Products', 'LION' ),
			'description' => __( 'Enables the WordPress comments metabox for products.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-features/wp-comments/init.php',
			'options'     => array( 'category' => 'product-features' ),
		),
		// Revisions 
		'wp-revisions' => array(
			'name'        => __( 'WP Revisions for Products', 'LION' ),
			'description' => __( 'Enables the WordPress revisions metabox for products.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-features/wp-revisions/init.php',
			'options'     => array( 'category' => 'product-features' ),
		),
		// Post Formats
		'wp-post-formats' => array(
			'name'        => __( 'WP Post Formats', 'LION' ),
			'description' => __( 'Enables the WordPress post formats interface for products.', 'LION' ),
			'file'        => dirname( __FILE__ ) . '/product-features/wp-post-formats/init.php',
			'options'     => array( 'category' => 'product-features' ),
		),
	);
	$add_ons = apply_filters( 'it_cart_buddy_core_addons', $add_ons );

	// Loop through add-ons and register each one individually
	foreach( (array) $add_ons as $slug => $params ) {
		$name        = empty( $params['name'] )        ? false   : $params['name'];
		$description = empty( $params['description'] ) ? ''      : $params['description'];
		$file        = empty( $params['file'] )        ? false   : $params['file'];
		$options     = empty( $params['options'] )     ? array() : (array) $params['options'];

		it_cart_buddy_register_addon( $slug, $name, $description, $file, $options );
	}
}
add_action( 'it_libraries_loaded', 'it_cart_buddy_register_core_addons' );

/**
 * Register's Core Cart Buddy Add-on Categories
 *
 * @since 0.2.0
 * @uses it_cart_buddy_register_add_on_category()
 * @return void
*/
function it_cart_buddy_register_core_addon_categories() {
	// An array of our core add-on categories
	$cats = array(
		'product-type' => array(
			'name'        => __( 'Product Type', 'LION' ),
			'description' => __( 'Add-ons responsible for the differing types of products available in Cart Buddy.', 'LION' ),
			'options'     => array(
			),
		),
		'transaction-methods' => array(
			'name'        => __( 'Transaction Methods', 'LION' ),
			'description' => __( 'Add-ons that create transactions. eg: Stripe, PayPal.', 'LION' ),
			'options'     => array(
				'supports' => apply_filters( 'it_cart_buddy_register_transaction_method_supports', array(
					'title' => array(
						'key'       => 'post_title',
						'componant' => 'post_type_support',
						'default'   => false,
					),
					'transaction_status' => array(
						'key'       => '_it_cart_buddy_transaction_status',
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
			'description' => __( 'Add-ons that provide a UI for the Cart Buddy Cart API.', 'LION' ),
			'options'     => array(),
		),
		'customer-management' => array(
			'name'        => __( 'Customer Management', 'LION' ),
			'description' => __( 'Add-ons that provide a UI for the Cart Buddy Customer Management.', 'LION' ),
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
	$cats = apply_filters( 'it_cart_buddy_core_addon_categories', $cats );

	// Loop through categories and register each one individually
	foreach( (array) $cats as $slug => $params ) {
		$name        = empty( $params['name'] )        ? false   : $params['name'];
		$description = empty( $params['description'] ) ? ''      : $params['description'];
		$options     = empty( $params['options'] )     ? array() : (array) $params['options'];

		it_cart_buddy_register_addon_category( $slug, $name, $description, $options );
	}
}
add_action( 'it_libraries_loaded', 'it_cart_buddy_register_core_addon_categories' );
