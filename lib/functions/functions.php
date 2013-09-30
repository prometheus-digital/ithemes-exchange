<?php
/**
 * Evaluates natural language strings to boolean equivalent
 *
 * Used primarily for handling boolean text provided in it_exchange() function options.
 * All values defined as true will return true, anything else is false.
 *
 * Boolean values will be passed through.
 *
 * @author Jonathan Davis from Shopp
 * @since 0.4.0
 *
 * @param string $string The natural language value
 * @param array $istrue A list strings that are true
 * @return boolean The boolean value of the provided text
*/
function it_exchange_str_true ( $string, $istrue = array('yes', 'y', 'true','1','on','open') ) {
    if (is_array($string)) return false;
    if (is_bool($string)) return $string;
    return in_array(strtolower($string),$istrue);
}

/**
 * Parses tag option strings or arrays
 *
 * @author Jonathan Davis from Shopp
 * @since 0.4.0
 *
 * @param string|array $options URL-compatible query string or associative array of tag options
 * @return array API-ready options list 
*/
function it_exchange_parse_options( $options ) {
	// Set empty array
	$paramset = array();

	// If options is empty, return empty array
	if ( empty( $options ) )
		return $paramset;

	// If options is string, convert to array ($paramset) via parse_str
	if ( is_string( $options) )
		parse_str( $options, $paramset );
	else 
		$paramset = $options;

	// Passed options are now an array ($paramset). Reset $options variable
	$options = array();

	// Clean keys and values
	foreach ( array_keys($paramset) as $key )
		$options[ strtolower($key) ] = $paramset[$key];

	// Strip slashes
	if ( get_magic_quotes_gpc() )
		$options = stripslashes_deep( $options );

	return $options;
}

/**
 * Formats a price based on settings
 *
 * @since 0.4.0
 * @todo possibly get this working with LC_MONETARY and money_format()
 * @return string
*/
function it_exchange_format_price( $price, $show_symbol = true ) {
	if ( ! is_numeric( $price ) )
		$price = 0;
	
	$before = $after = '';
	$settings = it_exchange_get_option( 'settings_general' );
	$currency = it_exchange_get_currency_symbol( $settings['default-currency'] );
	
	if ( $show_symbol ) {
		if ( 'after' === $settings['currency-symbol-position'] )
			$after = $currency;
		else
			$before = $currency;
	}
			
	return $before . number_format( $price, 2, $settings['currency-decimals-separator'], $settings['currency-thousands-separator'] ) . $after;
}

/**
 * Loads the frontend CSS on all exchange pages
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_load_public_scripts( $current_view ) {

	$purchase_requirements = (array) it_exchange_get_purchase_requirements();
	$purchase_requirements = array_keys( $purchase_requirements );

	// jQuery Zoom
	wp_register_script( 'jquery-zoom', ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/js/jquery.zoom.min.js' ), array( 'jquery' ), false, true );
	
	// Detect CC Type
	wp_register_script( 'detect-credit-card-type', ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/js/detect-credit-card-type.js' ), array( 'jquery' ), false, true );

	// Frontend Product JS
	if ( is_singular( 'it_exchange_prod' ) ) {
		wp_enqueue_script( 'it-exchange-product-public-js', ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/js/exchange-product.js' ), array( 'jquery-zoom' ), false, true );
	}

	// ****** CHECKOUT SPECIFIC SCRIPTS ******* 
	if ( it_exchange_is_page( 'checkout' )  ) {

		// General Checkout
		$script = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/js/checkout-page.js' );
		wp_enqueue_script( 'it-exchange-checkout-page', $script, array( 'jquery' ), false, true );
		
		// Load Logged In purchase requirement JS if not logged in and on checkout page.
		if ( in_array( 'logged-in', $purchase_requirements ) && ! is_user_logged_in() ) {
			$script = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/js/logged-in-purchase-requirement.js' );
			wp_enqueue_script( 'it-exchange-logged-in-purchase-requirement', $script, array( 'jquery' ), false, true );
		}

		// Load Shipping Address purchase requirement JS if not logged in and on checkout page.
		if ( in_array( 'billing-address', $purchase_requirements ) ) {
			$script = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/js/billing-address-purchase-requirement.js' );
			wp_enqueue_script( 'it-exchange-billing-address-purchase-requirement', $script, array( 'jquery', 'it-exchange-country-states-sync' ), false, true );
		}

		// Load country / state field sync if on checkout page
		wp_enqueue_script( 'it-exchange-country-states-sync', ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/js/country-states-sync.js' ), array( 'jquery' ), false, true );

	} // ****** END CHECKOUT SPECIFIC SCRIPTS *******

	// Frontend Style 
	if ( ! apply_filters( 'it_exchange_disable_frontend_stylesheet', false ) )
		wp_enqueue_style( 'it-exchange-public-css', ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/styles/exchange.css' ) );

	// Parent theme /exchange/style.css if it exists
	$parent_theme_css = get_template_directory() . '/exchange/style.css';
    if ( is_file( $parent_theme_css ) )
		wp_enqueue_style( 'it-exchange-parent-theme-css', ITUtility::get_url_from_file( $parent_theme_css ) );

	// Child theme /exchange/style.css if it exists
	$child_theme_css = get_stylesheet_directory() . '/exchange/style.css';
    if ( is_file( $child_theme_css ) && ( $parent_theme_css != $child_theme_css || ! is_file( $parent_theme_css ) ) )
		wp_enqueue_style( 'it-exchange-child-theme-css', ITUtility::get_url_from_file( $child_theme_css ) );
}
add_action( 'wp_enqueue_scripts', 'it_exchange_load_public_scripts' );

/**
 * Loads functions.php in theme if it exists
 *
 * @since 1.2.0
 *
 * @return void
*/
function it_exchange_load_theme_functions_for_exchange() {
	$parent_theme_functions = get_template_directory() . '/exchange/functions.php';
	$child_theme_functions = get_stylesheet_directory() . '/exchange/functions.php';

	// Parent theme
	if ( is_file( $parent_theme_functions ) )
		include_once( $parent_theme_functions );

	// Child theme or primary theme if not parent
	if ( is_file( $child_theme_functions ) )
		include_once( $child_theme_functions );
}
add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_load_theme_functions_for_exchange' );

/**
 * Hook for processing webhooks from services like PayPal IPN, Stripe, etc.
 *
 * @since 0.4.0
*/
function it_exchange_process_webhooks() {

	// Grab registered webhooks
    $webhooks = it_exchange_get_webhooks();

	// Loop through them and init callbacks
    foreach( $webhooks as $key => $param ) { 
    
        if ( ! empty( $_REQUEST[$param] ) ) 
            do_action( 'it_exchange_webhook_' . $param, $_REQUEST );
    
    }   
    do_action( 'it_exchange_webhooks_processed' );
}
add_action( 'wp', 'it_exchange_process_webhooks' );

/**
 * Add reset exchange button to settings page if WP_Debug is on
 *
 * @since 0.4.2
 *
 * @param object $form the ITForm object for the settings form
 * @return void
*/
function it_exchange_add_plugin_reset_checkbox_to_settings( $form ) {
	if ( it_exchange_has_messages( 'notice' ) ) {
		foreach ( it_exchange_get_messages( 'notice' ) as $notice ) {
			ITUtility::show_status_message( $notice );
		}
	}

	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG || ! current_user_can( 'administrator' ) )
		return;

	// Never check this by default.
	$form->set_option( 'reset-exchange', 0 );
	?>
	<tr valign="top">
		<th scope="row"><strong><?php _e( 'Dangerous Settings', 'LION' ); ?></strong></th>
		<td></td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="reset-exchange"><?php _e( 'Reset Exchange', 'LION' ) ?></label></th>
		<td>
			<?php $form->add_check_box( 'reset-exchange' ); ?>
			<label for="reset-exchange"><?php _e( 'Reset ALL data', 'LION' ) ?></label><br />
			<span class="description"><?php _e( 'Checking this box will rest ALL settings and DELETE ALL DATA.', 'LION' ); ?></span>
		</td>
	</tr>
	<?php
}
add_action( 'it_exchange_general_settings_table_bottom', 'it_exchange_add_plugin_reset_checkbox_to_settings' );

/**
 * This function resets Exchange
 *
 * Deletes all Products
 * Deletes all transactions
 * Deletes all core settings
 * Fires a hook so that addons can do the same.
 *
 * @since 0.4.2
 * @return void
*/
function it_exchange_reset_everything() {

	// Don't do anything if WP_DEBUG isn't true
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG )
		return;

	// Don't do anything if we're not on the settings page
	if ( empty( $GLOBALS['pagenow'] ) || 'admin.php' != $GLOBALS['pagenow'] || empty( $_GET['page'] ) || 'it-exchange-settings' != $_GET['page'] )
		return;

	// Don't do anything if the nonce doesn't validate
	$nonce = empty( $_POST['_wpnonce'] ) ? false : $_POST['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'exchange-general-settings' ) )
		return;

	// Don't do anything if the checkbox wasnt' checked
	$data = ITForm::get_post_data();
	if ( empty( $data['reset-exchange'] ) )
		return;

	// Use Post stati rather than 'any' for post type to include trashed and other non-searchable stati
	$stati = array_keys( get_post_stati() );

	// Delete all Products 
	if ( ! apply_filters( 'it_exchange_preserve_products_on_reset', false ) ) {
		while( $products = it_exchange_get_products( array( 'posts_per_page' => 20, 'post_status' => $stati ) ) ) {
			foreach ( $products as $product ) {
				wp_delete_post( $product->ID, true );
			}
		}
	}
	// Delete all Transactions
	if ( ! apply_filters( 'it_exchange_preserve_transactions_on_reset', false ) ) {
		while( $transactions = it_exchange_get_transactions( array( 'posts_per_page' => 20, 'post_status' => $stati ) ) ) {
			foreach ( $transactions as $transaction ) {
				wp_delete_post( $transaction->ID, true );
			}
		}
	}
	// Delete all Coupons
	if ( ! apply_filters( 'it_exchange_preserve_coupons_on_reset', false ) ) {
		while( $coupons = it_exchange_get_coupons( array( 'posts_per_page' => 20, 'post_status' => $stati ) ) ) {
			foreach ( $coupons as $coupon ) {
				wp_delete_post( $coupon->ID, true );
			}
		}
	}
	// Delete all Downloads (post types, not files uploaded to WP Media Library)
	if ( ! apply_filters( 'it_exchange_preserve_products_on_reset', false ) ) {
		while( $downloads = get_posts( array( 'post_type' => 'it_exchange_download', 'post_status' => $stati ) ) ) {
			foreach ( $downloads as $download ) {
				wp_delete_post( $download->ID, true );
			}
		}
		// Delete all session data for everyone. This is inside the check for product preserves on purpose
		it_exchange_db_delete_all_sessions();
	}

	// Delete all core settings
	$settings_keys = array(
		'it-storage-exchange_addon_offline_payments',
		'it-storage-exchange_addon_paypal_standard',
		'it-storage-exchange_addon_stripe',
		'it-storage-exchange_addon_zero_sum_checkout',
		'it-storage-exchange_enabled_add_ons',
		'it-storage-exchange_settings_email',
		'it-storage-exchange_settings_general',
		'it-storage-exchange_settings_pages',
		'it-exchange-hide-wizard-nag',
	);
	$settings_keys = apply_filters( 'it_exchange_reset_all_settings_keys', $settings_keys );
	foreach( $settings_keys as $option ) {
		delete_option( $option );
	}

	do_action( 'it_exchange_reset_exchange' );
	
	// Log message and redirect
	it_exchange_add_message( 'notice', __( 'Exchange has been reset. All data has been deleted.', 'LION' ) );
	wp_safe_redirect( add_query_arg( 'page', 'it-exchange-settings', trailingslashit( get_admin_url() ) . 'admin.php' ) );
	die();
}
add_action( 'admin_init', 'it_exchange_reset_everything' );

/**
 * Register core pages
 *
 * @since 0.4.4
 *
 * @return void
*/
function it_exchange_register_core_pages() {
	// Product
	$options = array(
		'slug'          => 'product',
		'name'          => __( 'Product', 'LION' ),
		'rewrite-rules' => false, //array( 10, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Product Base', 'LION' ),
		'type'          => 'exchange',
		'menu'          => false,
		'optional'      => false,
	);
	it_exchange_register_page( 'product', $options );

	// Store
	$options = array(
		'slug'          => 'store',
		'name'          => __( 'Store', 'LION' ),
		'rewrite-rules' => array( 230, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Store Page', 'LION' ),
		'tip'           => __( 'Where all your products are shown in one place', 'LION' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
	);
	it_exchange_register_page( 'store', $options );

	// Transaction
	$options = array(
		'slug'          => 'transaction',
		'name'          => __( 'Transaction', 'LION' ),
		'rewrite-rules' => array( 210, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Transaction', 'LION' ),
		'type'          => 'exchange',
		'menu'          => false,
		'optional'      => false,
	);
	it_exchange_register_page( 'transaction', $options );

	// Customer Registration
	$options = array(
		'slug'          => 'registration',
		'name'          => __( 'Registration', 'LION' ),
		'rewrite-rules' => array( 105, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Customer Registration', 'LION' ),
		'tip'           => __( 'Where customers register to login, download, etc.  You can turn off registration and allow guest checkouts in Exchange / Add-ons / Digital Downloads Settings.', 'LION' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
	);
	it_exchange_register_page( 'registration', $options );

	// Account
	$options = array(
		'slug'          => 'account',
		'name'          => __( 'Account', 'LION' ),
		'rewrite-rules' => array( 135, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Account Page', 'LION' ),
		'tip'           => __( 'Customers get an account when they buy something, so they can login and download their purchases. This is the main landing page for customers after they log in.', 'LION' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => false,
	);
	it_exchange_register_page( 'account', $options );

	// Profile
	$options = array(
		'slug'          => 'profile',
		'name'          => __( 'Profile', 'LION' ),
		'rewrite-rules' => array( 130, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Profile Page', 'LION' ),
		'tip'           => __( 'Private details about your customers that they can change.', 'LION' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
	);
	it_exchange_register_page( 'profile', $options );

	// Downloads
	$options = array(
		'slug'          => 'downloads',
		'name'          => __( 'Downloads', 'LION' ),
		'rewrite-rules' => array( 125, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Customer Downloads', 'LION' ),
		'tip'           => __( 'Page where the customer can find all of their available downloads.', 'LION' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
	);
	it_exchange_register_page( 'downloads', $options );

	// Purchases
	$options = array(
		'slug'          => 'purchases',
		'name'          => __( 'Purchases', 'LION' ),
		'rewrite-rules' => array( 120, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Purchases', 'LION' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
	);
	it_exchange_register_page( 'purchases', $options );

	// Log In
	$options = array(
		'slug'          => 'log-in',
		'name'          => __( 'Log In', 'LION' ),
		'rewrite-rules' => array( 110, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Customer Log In', 'LION' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
	);
	it_exchange_register_page( 'login', $options );

	// Log Out
	$options = array(
		'slug'          => 'log-out',
		'name'          => __( 'Log Out', 'LION' ),
		'rewrite-rules' => array( 115, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Customer Log Out', 'LION' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
	);
	it_exchange_register_page( 'logout', $options );

	// Confirmation
	$options = array(
		'slug'          => 'confirmation',
		'name'          => __( 'Thank you', 'LION' ),
		'rewrite-rules' => array( 205, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Purchase Confirmation', 'LION' ),
		'type'          => 'exchange',
		'menu'          => false,
		'optional'      => false,
	);
	it_exchange_register_page( 'confirmation', $options );
}
add_action( 'it_libraries_loaded', 'it_exchange_register_core_pages' );

/**
 * Returns rewrites for core pages
 *
 * @since 0.4.4
 *
 * @param string page
 * @return array
*/
function it_exchange_get_core_page_rewrites( $page ) {
	$slug = it_exchange_get_page_slug( $page );
	switch( $page ) {
		case 'store' :
			$rewrites = array(
				$slug => 'index.php?' . $slug . '=1',
			);
			return $rewrites;
			break;
		case 'account' :
			$profile_slug = it_exchange_get_page_slug( 'profile' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account = get_page( it_exchange_get_page_wpid( 'account' ) );
				$slug = $account->post_name;
			}

			$rewrites = array(
				$slug . '/([^/]+)/?$' => 'index.php?' . $slug . '=$matches[1]&' . $profile_slug . '=1',
				$slug => 'index.php?' . $slug . '=1&' . $profile_slug . '=1',
			);
			return $rewrites;
			break;
		case 'profile' :
			$account_slug = it_exchange_get_page_slug( 'account' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account->post_name;
			}

			$rewrites = array(
				$account_slug  . '/([^/]+)/' . $slug  => 'index.php?' . $account_slug . '=$matches[1]&' . $slug . '=1',
				$account_slug . '/' . $slug => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
			);
			return $rewrites;
			break;
		case 'registration' :
			$account_slug = it_exchange_get_page_slug( 'account' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account->post_name;
			}

			$rewrites = array(
				$account_slug  . '/' . $slug => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
			);
			return $rewrites;
			break;
		case 'login' :
			$account_slug = it_exchange_get_page_slug( 'account' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account->post_name;
			}

			$rewrites = array(
				$account_slug . '/' . $slug => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
			);
			return $rewrites;
			break;
		case 'logout' :
			$account_slug = it_exchange_get_page_slug( 'account' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account->post_name;
			}

			$rewrites = array(
				$account_slug . '/' . $slug => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
			);
			return $rewrites;
			break;
		case 'purchases' :
			$account_slug = it_exchange_get_page_slug( 'account' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account->post_name;
			}

			$rewrites = array(
				$account_slug  . '/([^/]+)/' . $slug => 'index.php?' . $account_slug . '=$matches[1]&' . $slug . '=1',
				$account_slug . '/' . $slug => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
			);
			return $rewrites;
			break;
		case 'downloads' :
			$account_slug = it_exchange_get_page_slug( 'account' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account->post_name;
			}

			$rewrites = array(
				$account_slug  . '/([^/]+)/' . $slug => 'index.php?' . $account_slug . '=$matches[1]&' . $slug . '=1',
				$account_slug . '/' . $slug => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
			);
			return $rewrites;
			break;
		case 'confirmation' :
			$rewrites = array(
				$slug . '/([^/]+)/?$' => 'index.php?' . $slug . '=$matches[1]',
			);
			return $rewrites;
			break;
		case 'transaction' :
			$rewrites = array(
				$slug  => 'index.php?' . $slug . '=1',
			);
			return $rewrites;
			break;
	}
	return false;
}

/**
 * Returns URL for core pages
 *
 * @since 0.4.4
 *
 * @param string page
 * @return array
*/
function it_exchange_get_core_page_urls( $page ) {
    $slug       = it_exchange_get_page_slug( $page );
    $permalinks = (boolean) get_option( 'permalink_structure' );
    $base       = trailingslashit( get_home_url() );

	// Proccess superwidget links
	if ( it_exchange_in_superwidget() && $slug != 'transaction' && $page != 'confirmation' ) {
		// Get current URL without exchange query args
		$url = it_exchange_clean_query_args();
		return add_query_arg( 'ite-sw-state', $slug, $url );
	}

	switch ( $page ) {
		// Store
		case 'store' :
			if ( $permalinks )
				return trailingslashit( $base . $slug );
			else
				return add_query_arg( array( $slug => 1 ), $base );
			break;
		// Anything that is a subpage of store
		case 'confirmation' :
		case 'transaction' :
			if ( $permalinks )
				return trailingslashit( $base . $slug );
			else
				return add_query_arg( array( $slug => 1 ), $base );
			break;
		// Anything else
		default :

			// Account Slug
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account_page = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account_page->post_name;
			} else {
				$account_slug = it_exchange_get_page_slug( 'account' );
			}

			// Replace account value with name if user is logged in
			if ( $permalinks )
				$base = trailingslashit( $base . $account_slug );
			else
				$base = add_query_arg( array( $account_slug => 1 ), $base );

			$account_name = get_query_var( 'account' );
			if ( $account_name && '1' != $account_name && ( 'login' != $page && 'logout' != $page ) ) {
				if ( $permalinks ) {
					$base = trailingslashit( $base . $account_name );
				} else {
					$base = remove_query_arg( $account_slug, $base );
					$base = add_query_arg( array( $account_slug => $account_name ), $base );
				}
			}

			if ( 'account' == $page ) {
				return $base;
			} else {
				if ( $permalinks )
					return trailingslashit( $base . $slug );
				else
					return add_query_arg( array( $slug => 1 ), $base );
			}
			break;
    }
}

/**
 * Creates a shortcode that returns content template parts for pages
 *
 * @since 0.4.8
 *
 * @param array $atts attributes passed in via shortcode arguments
 * @return string the template part
*/
function it_exchange_add_page_shortcode( $atts ) {
	$defaults = array(
		'page' => false,
	);
	$atts = shortcode_atts( $defaults, $atts );

	if ( 'account' == $atts['page'] )
		$atts['page'] = 'profile';

	if ( empty( $atts['page'] ) )
		return false;

	ob_start();
	it_exchange_get_template_part( 'content', $atts['page'] );
	return ob_get_clean();
}
add_shortcode( 'it-exchange-page', 'it_exchange_add_page_shortcode' );

/**
 * Adds date retraints to query posts.
 *
 * This function isn't applied to any queries by default. Certain functions like those in the basic_reporting addon add it as a filter and remove it.
 *
 * @since 0.4.9
 *
 * @param string $where the where clause of the query
 * @return string
*/
function it_exchange_filter_where_clause_for_all_queries( $where='' ) {

	// If this filter has been added, we expect one of the following two GLOBALS to have been set
	$start_date = empty( $GLOBALS['it_exchange']['where_start'] ) ? false : $GLOBALS['it_exchange']['where_start'];
	$end_date   = empty( $GLOBALS['it_exchange']['where_end'] ) ? false : $GLOBALS['it_exchange']['where_end'];

	// Return without doing anything if neither start or end are set
	if ( ! $start_date && ! $end_date )
		return $where;

	if ( $start_date )
		$where .= $GLOBALS['wpdb']->prepare( ' AND post_date >= %s', $start_date );
	
	if ( $end_date )
		$where .= $GLOBALS['wpdb']->prepare( ' AND post_date <= %s', $end_date );

	return $where;
}

/**
 * Clear the sessions when multi-item carts are enabled
 *
 * @todo replace when we introduce on enable an on diable hooks
 *
 * @since 0.4.11
 *
 * @param string $addon name of addon being enabled.
 * @return void
*/
function it_exchange_clear_sessions_when_multi_item_cart_is_enabled( $addon_slug ) {
	if ( 'multi-item-cart-option' == $addon_slug['slug'] )
		it_exchange_db_delete_all_sessions();
}
add_action( 'it_exchange_add_on_enabled', 'it_exchange_clear_sessions_when_multi_item_cart_is_enabled' );

/**
 * Registers our default purchase requirements
 *
 * @since 1.2.0
*/
function it_exchange_register_default_purchase_requirements() {

	// Link vars
	$login      = __( 'Log in', 'LION' );
	$register   = __( 'register', 'LION' );
	$cart       = __( 'edit your cart', 'LION' );
	$login_link = '<a href="' . it_exchange_get_page_url( 'login' ) . '" class="it-exchange-login-requirement-login">';
	$reg_link   = '<a href="' . it_exchange_get_page_url( 'registration' ) . '" class="it-exchange-login-requirement-registration">';
	$cart_link  = '<a href="' . it_exchange_get_page_url( 'cart' ) . '">';
	$close_link = '</a>';

	// User must be logged-in to checkout
	$properties = array(
		'priority'               => 1,
		'requirement-met'        => 'is_user_logged_in',
		'sw-template-part'       => apply_filters( 'it_exchange_sw_template_part_for_logged_in_purchase_requirement', 'registration' ),
		'checkout-template-part' => 'logged-in',
		'notification'           => sprintf( __( 'You must be logged in to complete your purchase. %s' . $login . '%s, %s' . $register . '%s or %s' . $cart . '%s', 'LION' ), $login_link, $close_link, $reg_link, $close_link, $cart_link, $close_link ),
	);
	it_exchange_register_purchase_requirement( 'logged-in', $properties );

	// Billing Address Purchase Requirement
	$properties = array(
		'priority'               => 5.11,
		'requirement-met'        => 'it_exchange_get_customer_billing_address',
		'sw-template-part'       => apply_filters( 'it_exchange_sw_template_part_for_logged_in_purchase_requirement', 'billing-address' ),
		'checkout-template-part' => 'billing-address',
		'notification'           => __( 'We need a billing address before you can checkout', 'LION' ),
	);
	// Only init the billing address if an add-on asks for it
	if ( apply_filters( 'it_exchange_billing_address_purchase_requirement_enabled', false ) )
		it_exchange_register_purchase_requirement( 'billing-address', $properties );
}
add_action( 'init', 'it_exchange_register_default_purchase_requirements' );

/**
 * Registers any purchase requirements Super Widget template parts as valid
 *
 * @since 1.2.0
 *
 * @param array $existing The existing valid template parts
 * @reutrn array
*/
function it_exchange_register_valid_sw_states_for_purchase_reqs( $existing ) {
	foreach( (array) it_exchange_get_purchase_requirements() as $slug => $properties ) {
		$sw_template = empty( $properties['sw-template-part'] ) ? false : $properties['sw-template-part'];
		if ( empty( $existing[$sw_template] ) )
			$existing[] = $sw_template;
	}
	return $existing;
}
add_filter( 'it_exchange_super_widget_valid_states', 'it_exchange_register_valid_sw_states_for_purchase_reqs' );

/**
 * Add purchase requiremnt notification to chekcout page if needed.
 *
 * @since 1.2.0
 *
 * @return void
*/
function it_exchange_add_purchase_requirement_notification() {
	if ( false === ( $notification = it_exchange_get_next_purchase_requirement_property( 'notification' ) ) )
		return;

    do_action( 'it_exchange_content_checkout_before_purchase_requirements_notification_element' );
	?>
    <div class="it-exchange-checkout-purchase-requirements-notification">
        <?php _e( $notification ); ?>
    </div>
    <?php
	do_action( 'it_exchange_content_checkout_actions_after_purchase_requirements_notification_element' );
}
add_action( 'it_exchange_content_checkout_after_purchase_requirements', 'it_exchange_add_purchase_requirement_notification' );

/**
 * Rmove purchase options if purchase requirements haven't been met
 *
 * @since 1.2.0
 *
 * @reutnr void
*/
function it_exchange_disable_purchase_options_on_checkout_page( $elements ) {
	if ( false === ( $message = it_exchange_get_next_purchase_requirement_property( 'notification' ) ) )
		return $elements;

	// Locate the transaction-methods key in elements array (if it exists)
	$index = array_search( 'transaction-methods', $elements );
	if ( false === $index )
		return $elements;

	// Remove transaction-methods
	unset( $elements[$index] );
	return $elements;
}
add_filter( 'it_exchange_get_content_checkout_actions_elements', 'it_exchange_disable_purchase_options_on_checkout_page' );

/**
 * Add Billing Address to the super-widget-checkout totals loop
 *
 * @since 1.3.0
 *
 * @param array $loops list of existing elements
 * @return array
*/
function it_exchange_add_billing_address_to_sw_template_totals_loops( $loops ) { 
	
	// Abandon if not doing billing
	if ( ! apply_filters( 'it_exchange_billing_address_purchase_requirement_enabled', false ) )
		return $loops;

	// Set index to end of array.
	$index = count($loops) -1 ;

	array_splice( $loops, $index, 0, 'billing-address' );
	return $loops;
}
add_filter( 'it_exchange_get_super-widget-checkout_after-cart-items_loops', 'it_exchange_add_billing_address_to_sw_template_totals_loops' );

/**
 * Clear Billing Address when the cart is emptied or a user logs out.
 *
 * @since 1.3.0
 *
 * @return void
*/
function it_exchange_clear_billing_on_cart_empty() {
    it_exchange_remove_cart_data( 'billing-address' );
}
add_action( 'it_exchange_empty_shopping_cart', 'it_exchange_clear_billing_on_cart_empty' );
add_action( 'wp_logout', 'it_exchange_clear_billing_on_cart_empty' );

/**  
 * AJAX callback for Country / State drop downs
 *
 * @since 1.3.0
 *
 * @return void
*/
function print_country_states_ajax() {
	if ( empty( $_POST['ite_action_ajax'] ) || 'ite-country-states-update' != $_POST['ite_action_ajax'] )
		return;

	define( 'DOING_AJAX', true );
	
	$base_country  = empty( $_POST['ite_base_country_ajax'] ) ? 'US' : $_POST['ite_base_country_ajax'];
	$base_state    = empty( $_POST['ite_base_state_ajax'] ) ? '' : $_POST['ite_base_state_ajax'];
	$template_part = empty( $_POST['ite_template_part_ajax'] ) ? '' : $_POST['ite_template_part_ajax'];
	$admin_prefix  = empty( $_POST['ite_admin_prefix_ajax'] ) ? false : $_POST['ite_admin_prefix_ajax'];

	if ( $admin_prefix && 'false' != $admin_prefix ) {
		do_action( 'it_exchange_admin_country_states_sync_for_' . $admin_prefix );
		die( __( 'Coding Error: Please hook into the following action, print your field based on $_POST vars and die():<br /> "it_exchange_admin_country_states_sync_for_' . $admin_prefix . '"' ) );
	} else {
		it_exchange_get_template_part( $template_part );
	}
	die();
}
add_action( 'init', 'print_country_states_ajax' );

/**
 * Prints a homeURL var in JS
 *
 * @since 1.3.0
*/
function it_exchange_print_home_url_in_js() {
	?>
	<script type="text/javascript">
		var itExchangeAjaxCountryStatesAjaxURL = '<?php echo esc_js( trailingslashit( get_site_url() ) ); ?>';
	</script>
	<?php
}
add_action( 'wp_head', 'it_exchange_print_home_url_in_js' );

/************************************
 * THE FOLLOWING API METHODS AREN'T READY
 * FOR PRIMETIME YET SO THEY LIVE HERE FOR NOW.
 * USE WITH CAUTION
 *************************************/
function it_exchange_add_product( $args=array() ) {
	$defaults = array(
		'status' => 'publish',
	);
	$defaults = apply_filters( 'it_exchange_add_product_defaults', $defaults );

	$args = ITUtility::merge_defaults( $args, $defaults );

	// Convert $args to insert post args
	$post_args = array();
	$post_args['post_status']  = $args['status'];
	$post_args['post_type']    = 'it_exchange_prod';
	$post_args['post_title']   = empty( $args['title'] ) ? '' : $args['title'];
	$post_args['post_content'] = ( it_exchange_product_type_supports_feature( $args['type'], 'extended-description' ) && ! empty( $args['extended-description'] ) ) ? $args['extended-description'] : '';

	// Insert Post and get ID
	if ( $product_id = wp_insert_post( $post_args ) ) {
		update_post_meta( $product_id, '_it_exchange_product_type', $args['type'] );
		update_post_meta( $product_id, '_it-exchange-visibility', empty( $args['show_in_store'] ) ? 'hidden' : 'visible' );

		$type = $args['type'];

		// Product Images from URLs
		if ( ! empty( $args['images-from-urls'] ) && is_array( $args['images-from-urls'] ) ) {
			foreach( $args['images-from-urls'] as $url => $description ) {
				it_exchange_add_remote_image_to_product_images( $url, $product_id, $description );
			}
			unset( $args['images-from-url'] );
		}

		unset( $args['status'] );
		unset( $args['extended-description'] );
		unset( $args['type'] );

		foreach( $args as $key => $value ) {
			if ( it_exchange_product_type_supports_feature( $type, $key ) )
				it_exchange_update_product_feature( $product_id, $key, $value );
		}
		return $product_id;
	}
	return false;
}

function it_exchange_add_remote_image_to_product_images( $url, $product_id, $desc='' ) { 
	$tmp = download_url( $url );

	// Set variables for storage
	// fix file filename for query strings
	preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches);
	$file_array['name'] = basename($matches[0]);
	$file_array['tmp_name'] = $tmp;

	// If error storing temporarily, unlink
	if ( is_wp_error( $tmp ) ) {
		@unlink($file_array['tmp_name']);
		$file_array['tmp_name'] = '';
	}

	// do the validation and storage stuff
	$id = media_handle_sideload( $file_array, $product_id, $desc );

	// If error storing permanently, unlink
	if ( is_wp_error($id) ) {
		@unlink($file_array['tmp_name']);
		return $id;
	}

	$product_images = it_exchange_get_product_feature( $product_id, 'product-images' );
	if ( empty( $product_images ) || ! is_array( $product_images ) )
		$product_images = array( $id );
	else
		$product_images[] = $id;
	it_exchange_update_product_feature( $product_id, 'product-images', $product_images );
	@unlink( $file_array['temp_name'] );
	return $id;
}
/************************************
 * THE PREVIOUS API METHODS AREN'T READY
 * FOR PRIMETIME YET SO THEY LIVE HERE FOR NOW.
 * USE WITH CAUTION
 *************************************/
