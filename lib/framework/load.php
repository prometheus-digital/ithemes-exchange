<?php
/**
 * This file loads the Cart Buddy Framework
 *
 * @since 0.2.0
 * @package IT_Cart_Buddy
*/

// Admin Functionality
require( $this->_plugin_path . '/lib/framework/class.admin.php' );

// Product Post Type
require( $this->_plugin_path . '/lib/framework/class.products-post-type.php' );

// Product Object
require( $this->_plugin_path . '/lib/framework/class.product.php' );

// Transaction Post Type
require( $this->_plugin_path . '/lib/framework/class.transactions-post-type.php' );

// Transaction Object
require( $this->_plugin_path . '/lib/framework/class.transaction.php' );

// Cart
require( $this->_plugin_path . '/lib/framework/cart/init.php' );

// Template Functions
require( $this->_plugin_path . '/lib/framework/template-functions.php' );

// Sessions
if ( ! is_admin() )
	require( $this->_plugin_path . '/lib/framework/class.session.php' );

