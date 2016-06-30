<?php
/**
 * Load the cart module.
 *
 * @since   1.36
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/deprecated.php';

require_once dirname( __FILE__ ) . '/class.customer-cart.php';
require_once dirname( __FILE__ ) . '/class.parameter-bag.php';
require_once dirname( __FILE__ ) . '/class.shopping-cart.php';
require_once dirname( __FILE__ ) . '/interface.cart-validator.php';
require_once dirname( __FILE__ ) . '/interface.line-item-validator.php';
require_once dirname( __FILE__ ) . '/interface.line-item.php';

require_once dirname( __FILE__ ) . '/line-items/class.repository-events.php';
require_once dirname( __FILE__ ) . '/line-items/abstract.repository.php';
require_once dirname( __FILE__ ) . '/line-items/class.session-repository.php';
require_once dirname( __FILE__ ) . '/line-items/class.cached-session-repository.php';
require_once dirname( __FILE__ ) . '/line-items/interface.repository-aware.php';

require_once dirname( __FILE__ ) . '/line-items/interface.aggregatable.php';
require_once dirname( __FILE__ ) . '/line-items/interface.aggregate.php';
require_once dirname( __FILE__ ) . '/line-items/interface.tax.php';
require_once dirname( __FILE__ ) . '/line-items/interface.taxable.php';
require_once dirname( __FILE__ ) . '/line-items/interface.shipping.php';
require_once dirname( __FILE__ ) . '/line-items/interface.discountable.php';

require_once dirname( __FILE__ ) . '/line-items/class.cart-product.php';
require_once dirname( __FILE__ ) . '/line-items/class.simple-tax.php';
require_once dirname( __FILE__ ) . '/line-items/class.base-shipping.php';
require_once dirname( __FILE__ ) . '/line-items/class.coupon.php';
require_once dirname( __FILE__ ) . '/line-items/class.collection.php';

require_once dirname( __FILE__ ) . '/validators/class.multi-item-cart.php';
require_once dirname( __FILE__ ) . '/validators/class.multi-item-product.php';