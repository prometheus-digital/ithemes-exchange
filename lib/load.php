<?php
/**
 * This file loads the iThemes Exchange Framework
 *
 * @since   0.2.0
 * @package IT_Exchange
 */

// Init DB sessions
require_once __DIR__ . '/sessions/load.php';

require_once __DIR__ . '/../api/contracts/load.php';
require_once __DIR__ . '/optional-features/load.php';

require_once __DIR__ . '/logging/load.php';

// IT Classes
require_once __DIR__ . '/classes/load.php';

//Util
require_once __DIR__ . '/util/load.php';

// Object Type API.
require_once __DIR__ . '/objects/load.php';

// Other Functions
require_once __DIR__ . '/functions/functions.php';
require_once __DIR__ . '/functions/hooks.php';

// Locking
require_once __DIR__ . '/functions/locks.php';

// IT Fonts
require_once __DIR__ . '/icon-fonts/load.php';

// Admin Functionality
require_once __DIR__ . '/admin/class.admin.php';

// Capabilities
require_once __DIR__ . '/capabilities/load.php';

// Products
require_once __DIR__ . '/products/load.php';

// Product Features
require_once __DIR__ . '/product-features/load.php';

require_once __DIR__ . '/gateway/load.php';
require_once __DIR__ . '/tokens/load.php';
require_once __DIR__ . '/purchase-dialog/purchase-dialog.php';

// Cart
require_once __DIR__ . '/cart/load.php';

// Tax
require_once __DIR__ . '/tax/load.php';

// Location
require_once __DIR__ . '/location/load.php';

// Transaction Module
require_once __DIR__ . '/transactions/load.php';

// Refunds Module
require_once __DIR__ . '/refunds/load.php';

// Template Functions
require_once __DIR__ . '/functions/template-functions.php';

// Integrations
require_once __DIR__ . '/integrations/builder/init.php';

// Customers
require_once __DIR__ . '/customers/load.php';

// Pages
require_once __DIR__ . '/pages/class.pages.php';
require_once __DIR__ . '/pages/class.customize.php';
require_once __DIR__ . '/pages/class.nav-menus.php';

// Super Widget
require_once __DIR__ . '/super-widget/class.super-widget.php';

// Coupons
require_once __DIR__ . '/coupons/load.php';

// Email Notifications
require_once __DIR__ . '/email-notifications/load.php';

// Shipping
require_once __DIR__ . '/shipping/class.shipping.php';

// Shortcodes
require_once __DIR__ . '/shortcodes/shortcodes.php';

// Upgrades
require_once __DIR__ . '/upgrades/load.php';

// Deprecated Features
require_once __DIR__ . '/deprecated/init.php';

require_once __DIR__ . '/settings/class.form.php';
require_once __DIR__ . '/settings/class.controller.php';

require_once __DIR__ . '/REST/load.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/cli/load.php';
}