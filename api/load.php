<?php
/**
 * Loads APIs for iThemes Exchange
 *
 * @package IT_Exchange
 * @todo perhaps only load addons that are enabled?
 * @since 0.2.0
*/

if ( is_admin() ) {
	// Admin only
} else {
	// Frontend only
	include( $this->_plugin_path . 'api/theme.php' );
}

// Contains functions for registering / retreiving Add-ons, Add-on categories, and Add-on sets
include( $this->_plugin_path . 'api/addons.php' );

// Product Features
include( $this->_plugin_path . 'api/product-features.php' );

// Register and retreive form actions
include( $this->_plugin_path . 'api/misc.php' );

// Product Type Add-ons
include( $this->_plugin_path . 'api/products.php' );

// Transaction Add-ons
include( $this->_plugin_path . 'api/transactions.php' );

// Sessions
include( $this->_plugin_path . 'api/sessions.php' );

// Storage
include( $this->_plugin_path . 'api/storage.php' );

// Shopping Cart API
include( $this->_plugin_path . 'api/cart.php' );

// Customers
include( $this->_plugin_path . 'api/customers.php' );

// Messages
include( $this->_plugin_path . 'api/messages.php' );

// Coupons
include( $this->_plugin_path . 'api/coupons.php' );

// Downloads
include( $this->_plugin_path . 'api/downloads.php' );
