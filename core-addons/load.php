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
		// Offline
		'offline-payments' => array(
			'name'              => __( 'Offline Payments', 'LION' ),
			'description'       => __( 'Use this Transaction Method to take payments offline.', 'LION' ),
			'author'            => 'iThemes',
			'author_url'        => 'http://ithemes.com',
			'file'              => dirname( __FILE__ ) . '/transaction-methods/offline-payments/init.php',
			'category'          => 'transaction-methods',
			'tag'               => 'core',
			'supports'          => apply_filters( 'it_exchange_register_offline_payments_default_features', array(
				'transaction_status' => array(
					'key'       => '_it_exchange_transaction_status',
					'componant' => 'post_meta',
					'options'   => array(
						'pending'  => _x( 'Pending', 'Transaction Status', 'LION' ),
						'paid'     => _x( 'Paid', 'Transaction Status', 'LION' ),
						'refunded' => _x( 'Refunded', 'Transaction Status', 'LION' ),
						'voided'   => _x( 'Voided', 'Transaction Status', 'LION' ),
					),
					'default'   => 'pending',
				),
				'transaction_cart' => array(
					'key'       => '_it_exchange_transaction_cart',
					'componant' => 'post_meta',
					'default'   => false,
				),
			) ),
			'settings-callback' => 'it_exchange_offline_payments_settings_callback',
		),
		// PayPal Standard Transaction Method
		'paypal-standard' => array(
			'name'              => __( 'PayPal Standard', 'LION' ),
			'description'       => __( 'Process Transactions with the PayPal Standard gateway.', 'LION' ),
			'author'            => 'iThemes',
			'author_url'        => 'http://ithemes.com',
			'icon'              => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/transaction-methods/paypal-standard/paypalstd.png' ),
			'file'              => dirname( __FILE__ ) . '/transaction-methods/paypal-standard/init.php',
			'category'          => 'transaction-methods',
			'tag'               => 'core',
			'supports'          => apply_filters( 'it_exchange_register_paypal_standard_default_features', array() ),
			'settings-callback' => 'it_exchange_paypal_standard_settings_callback',
		),
		// Stripe Transaction Method
		'stripe'          => array(
			'name'              => __( 'Stripe', 'LION' ),
			'description'       => __( 'Process Transactions with the Stripe payment gateway.', 'LION' ),
			'author'            => 'iThemes',
			'author_url'        => 'http://ithemes.com',
			'icon'              => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/transaction-methods/stripe/stripe.png' ),
			'file'              => dirname( __FILE__ ) . '/transaction-methods/stripe/init.php',
			'category'          => 'transaction-methods',
			'supports'          => apply_filters( 'it_exchange_register_stripe_default_features', array() ),
			'settings-callback' => 'it_exchange_stripe_addon_settings_callback',
		),
		// Digital Download Product Types
		'digital-downloads-product-type' => array(
			'name'        => __( 'Digital Downloads', 'LION' ),
			'description' => __( 'This adds an product type for distributing digital downloads through iThemes Exchange.', 'LION' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/product-types/digital-downloads/init.php',
			'category'    => 'product-type',
			'tag'         => 'core',
			'labels'      => array(
				'singular_name' => __( 'Digital Download', 'LION' ),
			),
			'supports'    => apply_filters( 'it_exchange_register_digital_downloads_default_features', array(
				'inventory'     => false,
			) ),
		),
		// REMOVE BOOKS HEREHEREHERE
		// Books Product Types
		'books-product-type' => array(
			'name'        => __( 'Books', 'LION' ),
			'description' => __( 'This adds an product type for distributing books through iThemes Exchange.', 'LION' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/product-types/books/init.php',
			'category'    => 'product-type',
			'tag'         => 'core',
			'labels'      => array(
				'singular_name' => __( 'Book', 'LION' ),
			),
			'supports'    => apply_filters( 'it_exchange_register_book_default_features', array(
				'inventory'     => false,
			) ),
		),
		// Featured Product Widget
		'featured-product-widget' => array(
			'name'        => __( 'Featured Product Widget', 'LION' ),
			'description' => __( 'Creates a WordPress widget to display featured products in WordPress sidebars.', 'LION' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/widgets/featured-product-widget/init.php',
			'category'    => 'widgets',
			'tag'         => 'core',
		),
		// Cart Summary Widget
		'cart-summary-widget' => array(
			'name'        => __( 'Cart Summary Widget', 'LION' ),
			'description' => __( 'Creates a WordPress widget to display the cart summary in WordPress sidebars.', 'LION' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/widgets/cart-summary-widget/init.php',
			'category'    => 'widgets',
			'tag'         => 'core',
		),
		// Multi item cart
		'multi-item-cart-option' => array(
			'name'        => __( 'Multi-item Cart', 'LION' ),
			'description' => __( 'Enabling this add-on allows your customers to purchase multiple products with one transaction. There are no settings for this add-on.', 'LION' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/admin/multi-item-cart/init.php',
			'category'    => 'admin',
			'tag'         => 'core',
			'supports'    => apply_filters( 'it_exchange_register_multi_item_cart_default_features', array(
			) ),
		),
		// Basic Coupons
		'it-basic-coupons' => array(
			'name'        => __( 'Basic Coupons', 'LION' ),
			'description' => __( 'This add-on allows you to generate basic coupons that apply to all products in your store.', 'LION' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/coupons/basic-coupons/init.php',
			'category'    => 'coupons',
			'tag'         => 'core',
			'supports'    => apply_filters( 'it_exchange_register_basic_coupons_default_features', array(
			) ),
		),
		// Transaction Status admin Metabox
		// Transaction Status admin Metabox
		'transaction-status-metabox' => array(
			'name'        => __( 'Transaction Status Metabox', 'LION' ),
			'description' => __( 'Gives admins the ability to change a Transaction Status via a metabox after creation of the Transaction', 'LION' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/admin/transaction-status-metabox/init.php',
			'category'    => 'admin',
			'tag'         => 'core',
		),
		// Category Taxonomy
		'category-taxonomy-type' => array(
			'name'        => __( 'Product Categories', 'LION' ),
			'description' => __( 'This adds a category taxonomy for all products in iThemes Exchange.', 'LION' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/product-features/categories/init.php',
			'category'    => 'taxonomy-type',
			'tag'         => 'core',
			'labels'      => array(
				'singular_name' => __( 'Product Category', 'LION' ),
			),
		),
		// Category Taxonomy
		'tag-taxonomy-type' => array(
			'name'        => __( 'Product Tags', 'LION' ),
			'description' => __( 'This adds a tag taxonomy for all products in iThemes Exchange.', 'LION' ),
			'author'      => 'iThemes',
			'author_url'  => 'http://ithemes.com',
			'file'        => dirname( __FILE__ ) . '/product-features/tags/init.php',
			'category'    => 'taxonomy-type',
			'tag'         => 'core',
			'labels'      => array(
				'singular_name' => __( 'Product Tag', 'LION' ),
			),
		),
	);
	$add_ons = apply_filters( 'it_exchange_core_addons', $add_ons );

	// Loop through add-ons and register each one individually
	foreach( (array) $add_ons as $slug => $params )
		it_exchange_register_addon( $slug, $params );
	
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
							'paid'       => _x( 'Paid', 'Transaction Status', 'LION' ),
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
