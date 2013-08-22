<?php
/**
 * Includes all of our product features
 * @since 0.4.0
 * @package IT_Exchange
*/

// Product Feature: Title
require( $this->_plugin_path . 'lib/product-features/class.title.php' );

// Product Feature: Base Price
require( $this->_plugin_path . 'lib/product-features/class.base-price.php' );

// Product Feature: Recurring Payments
require( $this->_plugin_path . 'lib/product-features/class.recurring-payments.php' );

// Product Feature: Product Description
require( $this->_plugin_path . 'lib/product-features/class.description.php' );

// Product Feature: Downloads
require( $this->_plugin_path . 'lib/product-features/class.downloads.php' );

// Product Feature: Product Images 
require( $this->_plugin_path . 'lib/product-features/class.product-images.php' );

// Product Feature: Purchase Message 
require( $this->_plugin_path . 'lib/product-features/class.purchase-message.php' );

// Product Feature: Product Availability 
require( $this->_plugin_path . 'lib/product-features/class.product-availability.php' );

// Product Feature: Quantity
require( $this->_plugin_path . 'lib/product-features/class.purchase-quantity.php' );

// Product Feature: Inventory
require( $this->_plugin_path . 'lib/product-features/class.inventory.php' );

// Product Features: WP Post Type Supports as Product Features
require( $this->_plugin_path . 'lib/product-features/class.wp-post-supports.php' );

// Product Features: WP Post Type Supports as Duplicate Products Feature
require( $this->_plugin_path . 'lib/product-features/class.duplicate-products.php' );
