<?php
/**
 * Includes all of our product features
 *
 * @since   0.4.0
 * @package IT_Exchange
 */

require_once dirname( __FILE__ ) . '/interface.php';
require_once dirname( __FILE__ ) . '/class.registry.php';

// Abstract Class for Product Features
require_once dirname( __FILE__ ) . '/class.abstract.php';

// Product Feature: Title
require_once dirname( __FILE__ ) . '/class.title.php';

// Product Feature: Base Price
require_once dirname( __FILE__ ) . '/class.base-price.php';

// Product Feature: Product Description
require_once dirname( __FILE__ ) . '/class.description.php';

// Product Feature: Downloads
require_once dirname( __FILE__ ) . '/class.downloads.php';

// Product Feature: Shipping
require_once dirname( __FILE__ ) . '/class.shipping.php';

// Product Feature: Product Images
require_once dirname( __FILE__ ) . '/class.product-images.php';

// Product Feature: Purchase Message
require_once dirname( __FILE__ ) . '/class.purchase-message.php';

// Product Feature: Product Availability
require_once dirname( __FILE__ ) . '/class.product-availability.php';

// Product Feature: Quantity
require_once dirname( __FILE__ ) . '/class.purchase-quantity.php';

// Product Feature: Inventory
require_once dirname( __FILE__ ) . '/class.inventory.php';

// Product Feature: Product Order
require_once dirname( __FILE__ ) . '/class.product-order.php';

// Product Features: WP Post Type Supports as Product Features
require_once dirname( __FILE__ ) . '/class.wp-post-supports.php';

/**
 * Load the Sale Price product feature.
 *
 * @since 1.32.0
 */
require_once dirname( __FILE__ ) . '/class.sale-price.php';
