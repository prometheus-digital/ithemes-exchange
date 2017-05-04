<?php
/**
 * Loads APIs for iThemes Exchange
 *
 * @package IT_Exchange
 * @since   0.2.0
 */

require_once __DIR__ . '/theme.php' ;

// Contains functions for registering / retreiving Add-ons, Add-on categories, and Add-on sets
require_once __DIR__ . '/addons.php' ;

// Product Features
require_once __DIR__ . '/product-features.php' ;

// Register and retreive form actions
require_once __DIR__ . '/misc.php' ;

// Product Type Add-ons
require_once __DIR__ . '/products.php' ;

// Transaction Add-ons
require_once __DIR__ . '/transactions.php' ;
require_once __DIR__ . '/webhooks.php' ;

// Sessions
require_once __DIR__ . '/sessions.php' ;

// Storage
require_once __DIR__ . '/storage.php' ;

// Shopping Cart API
require_once __DIR__ . '/cart.php' ;

// Customers
require_once __DIR__ . '/customers.php' ;

// Messages
require_once __DIR__ . '/messages.php' ;

// Coupons
require_once __DIR__ . '/coupons.php' ;

// Downloads
require_once __DIR__ . '/downloads.php' ;

// Pages
require_once __DIR__ . '/pages.php' ;

// Template Parts
require_once __DIR__ . '/template-parts.php' ;

// Data Sets
require_once __DIR__ . '/data-sets.php' ;

// Purchase Dialogs
require_once __DIR__ . '/purchase-dialogs.php' ;

// Shipping API
require_once __DIR__ . '/shipping.php' ;
require_once __DIR__ . '/shipping-features.php' ;

require_once __DIR__ . '/sales.php' ;
require_once __DIR__ . '/gateways.php' ;
require_once __DIR__ . '/logging.php' ;