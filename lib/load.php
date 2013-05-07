<?php
/**
 * This file loads the iThemes Exchange Framework
 *
 * @since 0.2.0
 * @package IT_Exchange
*/

// IT Classes
require( 'classes/load.php' );

// Admin Functionality
require( $this->_plugin_path . 'lib/admin/class.admin.php' );

// Product Post Type
require( $this->_plugin_path . 'lib/products/class.products-post-type.php' );

// Product Object
require( $this->_plugin_path . 'lib/products/class.product.php' );

// Product Feature: Title
require( $this->_plugin_path . 'lib/product-features/class.title.php' );

// Product Feature: Base Price
require( $this->_plugin_path . 'lib/product-features/class.base-price.php' );

// Product Feature: Visibility
require( $this->_plugin_path . 'lib/product-features/class.visibility.php' );

// Product Feature: Product Description
require( $this->_plugin_path . 'lib/product-features/class.description.php' );

// Product Feature: Product Images 
require( $this->_plugin_path . 'lib/product-features/class.product-images.php' );

// Product Feature: Downloads
require( $this->_plugin_path . 'lib/product-features/class.downloads.php' );

// Product Feature: Purchase Message 
require( $this->_plugin_path . 'lib/product-features/class.purchase-message.php' );

// Product Feature: Product Availability 
require( $this->_plugin_path . 'lib/product-features/class.product-availability.php' );

// Product Feature: Quantity
require( $this->_plugin_path . 'lib/product-features/class.quantity.php' );

// Product Feature: Inventory
require( $this->_plugin_path . 'lib/product-features/class.inventory.php' );

// Product Features: WP Post Type Supports as Product Features
require( $this->_plugin_path . 'lib/product-features/class.wp-post-supports.php' );

// Transaction Post Type
require( $this->_plugin_path . 'lib/transactions/class.transactions-post-type.php' );

// Transaction Object
require( $this->_plugin_path . 'lib/transactions/class.transaction.php' );

// Template Functions
require( $this->_plugin_path . 'lib/functions/template-functions.php' );

// Other Functions
require( $this->_plugin_path . 'lib/functions/functions.php' );

// Customer Class
require( $this->_plugin_path . 'lib/customers/class.customer.php' );

// API
require( $this->_plugin_path . 'lib/api/class.api.php' );

// Router
require( $this->_plugin_path . 'lib/routes/class.router.php' );

// Super Widget
require( $this->_plugin_path . 'lib/super-widget/class.super-widget.php' );

// Sessions
if ( ! is_admin() ) {
	require( $this->_plugin_path . 'lib/sessions/class.session.php' );
	require( $this->_plugin_path . 'lib/cart/class.cart.php' );
} else {
	require( $this->_plugin_path . 'lib/routes/class.nav-menus.php' );
}

