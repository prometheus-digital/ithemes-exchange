<?php
/**
 * Loads APIs for Cart Buddy
 *
 * @package IT_Cart_Buddy
 * @since 0.2.0
*/

if ( is_admin() ) {
	// Admin only
	include( $this->_plugin_path . '/api/admin.php' );
} else {
	// Frontend only
}

// Contains functions for registering / retreiving Add-ons, Add-on categories, and Add-on sets
include( $this->_plugin_path . '/api/add-ons.php' );

// Product Features
include( $this->_plugin_path . '/api/product-features.php' );

// Register and retreive orm actions
include( $this->_plugin_path . '/api/form-actions.php' );

// API functions for Product Type Add-ons
include( $this->_plugin_path . '/api/products.php' );

// API functions for Transaction Method Add-ons
include( $this->_plugin_path . '/api/transaction-methods/init.php' );

// Sessions
include( $this->_plugin_path . '/api/sessions.php' );

// Storage
include( $this->_plugin_path . '/api/storage.php' );

// Shopping Cart API
include( $this->_plugin_path . '/api/cart/init.php' );

// Customers
include( $this->_plugin_path . '/api/customers.php' );

// Errors
include( $this->_plugin_path . '/api/errors.php' );

// Alerts 
include( $this->_plugin_path . '/api/alerts.php' );
