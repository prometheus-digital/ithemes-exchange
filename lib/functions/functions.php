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

	// jQuery Zoom
	wp_register_script( 'jquery-zoom', ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/js/jquery.zoom.min.js' ), array( 'jquery' ), false, true );
	
	// Frontend Product JS
	if ( is_singular( 'it_exchange_prod' ) ) {
		wp_enqueue_script( 'it-exchange-product-public-js', ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/js/exchange-product.js' ), array( 'jquery-zoom' ), false, true );
	}
	
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
		'type'			=> 'exchange',
		'menu'			=> false,
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
		'type'			=> 'exchange',
		'menu'			=> true,
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
		'type'			=> 'exchange',
		'menu'			=> false,
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
		'type'			=> 'exchange',
		'menu'			=> true,
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
		'type'			=> 'exchange',
		'menu'			=> true,
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
		'type'			=> 'exchange',
		'menu'			=> true,
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
		'type'			=> 'exchange',
		'menu'			=> true,
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
		'type'			=> 'exchange',
		'menu'			=> true,
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
		'type'			=> 'exchange',
		'menu'			=> true,
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
		'type'			=> 'exchange',
		'menu'			=> true,
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
		'type'			=> 'exchange',
		'menu'			=> false,
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
	if ( it_exchange_in_superwidget() && $slug != 'transaction' ) { 
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

	return it_exchange_get_template_part( 'content', $atts['page'] );
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
