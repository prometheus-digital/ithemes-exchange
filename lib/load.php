<?php
/**
 * This file loads the iThemes Exchange Framework
 *
 * @since   0.2.0
 * @package IT_Exchange
 */

// Init DB sessions
require_once dirname( __FILE__ ) . '/sessions/load.php';

require_once dirname( __FILE__ ) . '/../api/contracts/load.php';

// IT Classes
require_once dirname( __FILE__ ) . '/classes/load.php';

//Util
require_once dirname( __FILE__ ) . '/util/load.php';

// Object Type API.
require_once dirname( __FILE__ ) . '/objects/load.php';

// Other Functions
require_once dirname( __FILE__ ) . '/functions/functions.php';

// Locking
require_once dirname( __FILE__ ) . '/functions/locks.php';

// IT Fonts
require_once dirname( __FILE__ ) . '/icon-fonts/load.php';

// Admin Functionality
require_once dirname( __FILE__ ) . '/admin/class.admin.php';

// Capabilities
require_once dirname( __FILE__ ) . '/capabilities/load.php';

// Products
require_once dirname( __FILE__ ) . '/products/load.php';

// Product Features
require_once dirname( __FILE__ ) . '/product-features/load.php';

require_once dirname( __FILE__ ) . '/gateway/load.php';
require_once dirname( __FILE__ ) . '/tokens/load.php';
require_once dirname( __FILE__ ) . '/purchase-dialog/purchase-dialog.php';

// Cart
require_once dirname( __FILE__ ) . '/cart/load.php';

// Tax
require_once dirname( __FILE__ ) . '/tax/load.php';

// Location
require_once dirname( __FILE__ ) . '/location/load.php';

// Activity
require_once dirname( __FILE__ ) . '/activity/load.php';

// Transaction Module
require_once dirname( __FILE__ ) . '/transactions/load.php';

// Refunds Module
require_once dirname( __FILE__ ) . '/refunds/load.php';

// Template Functions
require_once dirname( __FILE__ ) . '/functions/template-functions.php';

// Integrations
require_once dirname( __FILE__ ) . '/integrations/builder/init.php';

// Customers
require_once dirname( __FILE__ ) . '/customers/load.php';

// Pages
require_once dirname( __FILE__ ) . '/pages/class.pages.php';
require_once dirname( __FILE__ ) . '/pages/class.customize.php';
require_once dirname( __FILE__ ) . '/pages/class.nav-menus.php';

// Super Widget
require_once dirname( __FILE__ ) . '/super-widget/class.super-widget.php';

// Coupons
require_once dirname( __FILE__ ) . '/coupons/load.php';

// Email Notifications
require_once dirname( __FILE__ ) . '/email-notifications/load.php';

// Shipping
require_once dirname( __FILE__ ) . '/shipping/class.shipping.php';

// Shortcodes
require_once dirname( __FILE__ ) . '/shortcodes/shortcodes.php';

// Upgrades
require_once dirname( __FILE__ ) . '/upgrades/load.php';

// Deprecated Features
require_once dirname( __FILE__ ) . '/deprecated/init.php';

require_once dirname( __FILE__ ) . '/settings/class.form.php';
require_once dirname( __FILE__ ) . '/settings/class.controller.php';

require_once dirname( __FILE__ ) . '/REST/load.php';