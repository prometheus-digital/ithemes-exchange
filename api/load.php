<?php
/**
 * Loads APIs for Cart Buddy
 *
 * @package IT_Cart_Buddy
 * @since 0.2.0
*/

if ( is_admin() ) {
	// Admin only
	include( $this->_plugin_path . 'api/admin/init.php' );
} else {
	// Frontend only
}

// Contains functions for registering / retreiving Add-ons, Add-on categories, and Add-on sets
include( $this->_plugin_path . 'api/add-ons/init.php' );

// Product Features
include( $this->_plugin_path . 'api/product-features/init.php' );

// Register and retreive form actions
include( $this->_plugin_path . 'api/form-actions/init.php' );

// Product Type Add-ons
include( $this->_plugin_path . 'api/products/init.php' );

// Transaction Method Add-ons
include( $this->_plugin_path . 'api/transaction-methods/init.php' );

// Sessions
include( $this->_plugin_path . 'api/sessions/init.php' );

// Storage
include( $this->_plugin_path . 'api/storage/init.php' );

// Shopping Cart API
include( $this->_plugin_path . 'api/cart/init.php' );

// Customers
include( $this->_plugin_path . 'api/customers/init.php' );

// Errors
include( $this->_plugin_path . 'api/errors/init.php' );

// Alerts 
include( $this->_plugin_path . 'api/alerts/init.php' );
