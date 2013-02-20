<?php
/**
 * Loads APIs for Cart Buddy
 *
 * @package IT_Cart_Buddy
 * @since 0.2.0
*/

// Contains functions for registering / retreiving Add-ons, Add-on categories, and Add-on sets
include( $this->_plugin_path . '/api/add-ons.php' );

// API functions for Product Type Add-ons
include( $this->_plugin_path . '/api/product-types.php' );

// API functions for Transaction Method Add-ons
include( $this->_plugin_path . '/api/transaction-methods.php' );

// Sessions
include( $this->_plugin_path . '/api/sessions.php' );

// Storage
include( $this->_plugin_path . '/api/storage.php' );

if ( is_admin() ) {
	// Admin
	include( $this->_plugin_path . '/api/admin.php' );
}
