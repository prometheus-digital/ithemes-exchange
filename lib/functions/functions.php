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
 * @since  0.4.0
 *
 * @param string $string  The natural language value
 * @param array  $is_true A list strings that are true
 *
 * @return bool The boolean value of the provided text
 */
function it_exchange_str_true( $string, $is_true = array( 'yes', 'y', 'true', '1', 'on', 'open' ) ) {

	if ( is_array( $string ) ) {
		return false;
	}

	if ( is_bool( $string ) ) {
		return $string;
	}

	return in_array( strtolower( $string ), $is_true );
}

/**
 * Parses tag option strings or arrays
 *
 * @author Jonathan Davis from Shopp
 * @since  0.4.0
 *
 * @param string|array $options URL-compatible query string or associative array of tag options
 *
 * @return array API-ready options list
 */
function it_exchange_parse_options( $options ) {
	// Set empty array
	$paramset = array();

	// If options is empty, return empty array
	if ( empty( $options ) ) {
		return $paramset;
	}

	// If options is string, convert to array ($paramset) via parse_str
	if ( is_string( $options ) ) {
		parse_str( $options, $paramset );
	} else {
		$paramset = $options;
	}

	// Passed options are now an array ($paramset). Reset $options variable
	$options = array();

	// Clean keys and values
	foreach ( array_keys( $paramset ) as $key ) {
		$options[ strtolower( $key ) ] = $paramset[ $key ];
	}

	// Strip slashes
	if ( get_magic_quotes_gpc() ) {
		$options = stripslashes_deep( $options );
	}

	return $options;
}

/**
 * Used to set admin menu capabilities
 *
 * @since 1.12.0
 *
 * @param string $context    the context of where it's being used
 * @param string $capability the incoming capability
 *
 * @return string
 */
function it_exchange_get_admin_menu_capability( $context = '', $capability = 'manage_options' ) {

	// Allow addons to filter
	$capability = apply_filters( 'it_exchange_admin_menu_capability', $capability, $context );

	// Clean and return
	return empty( $capability ) || ! is_string( $capability ) ? false : $capability;
}

/**
 * Formats a price based on settings
 *
 * @since 0.4.0
 * @todo  possibly get this working with LC_MONETARY and money_format()
 *
 * @param mixed $price
 * @param bool  $show_symbol
 *
 * @return string
 */
function it_exchange_format_price( $price, $show_symbol = true ) {

	if ( ! is_numeric( $price ) ) {
		$price = 0;
	}

	$before   = $after = '';
	$settings = it_exchange_get_option( 'settings_general' );
	$currency = it_exchange_get_currency_symbol( $settings['default-currency'] );

	if ( $show_symbol ) {
		if ( 'after' === $settings['currency-symbol-position'] ) {
			$after = $currency;
		} else {
			$before = $currency;
		}
	}

	if ( $price < 0 ) {
		$before = '−' . $before;
		$price  = abs( $price );
	}

	$out = $before;
	$out .= number_format( $price, 2, $settings['currency-decimals-separator'], $settings['currency-thousands-separator'] );
	$out .= $after;

	return $out;
}

/**
 * Preload REST schemas.
 *
 * @since 2.0.0
 *
 * @param bool|string|string[] $schemas,... If true, all schemas will be preloaded. If false, no schemas will be
 *                                          preloaded. Otherwise, a list of schemas identified by their title can be
 *                                          provided. If no arguments given, will return the current schema state.
 *
 * @return array|bool|null
 */
function it_exchange_preload_schemas( $schemas = null ) {

	static $_schemas = null;

	if ( func_num_args() === 0 ) {
		return $_schemas;
	}

	if ( $schemas === true ) {
		$_schemas = true;
	} elseif ( $schemas === false ) {
		$_schemas = false;
	} elseif ( $_schemas !== true && $schemas ) {
		$_schemas = is_array( $_schemas ) ? $_schemas : array();
		$_schemas = array_merge( $_schemas, is_array( $schemas ) ? $schemas : func_get_args() );
	}

	return $_schemas;
}

/**
 * Add inline script data.
 *
 * Intended as a fallback for wp_add_inline_script().
 *
 * @since 2.0.0
 *
 * @param string $handle
 * @param string $data
 */
function it_exchange_add_inline_script( $handle, $data ) {

	if ( function_exists( 'wp_add_inline_script' ) ) {
		wp_add_inline_script( $handle, $data );
	} else {

		if ( ! isset( $GLOBALS['it_exchange']['inline-scripts'] ) ) {
			$GLOBALS['it_exchange']['inline-scripts'] = array();
		}

		$GLOBALS['it_exchange']['inline-scripts'][ $handle ][] = $data;
	}
}

/**
 * Returns rewrites for core pages
 *
 * @since 0.4.4
 *
 * @param string $page
 *
 * @return array|false
 */
function it_exchange_get_core_page_rewrites( $page ) {
	$slug = it_exchange_get_page_slug( $page );
	switch ( $page ) {
		case 'store' :
			$rewrites = array(
				$slug . '$' => 'index.php?' . $slug . '=1',
			);

			return $rewrites;
		case 'account' :

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account = get_page( it_exchange_get_page_wpid( 'account' ) );
				$slug    = $account->post_name;
			}

			$rewrites = array(
				$slug . '/([^/]+)/?$' => 'index.php?' . $slug . '=$matches[1]',//&' . $profile_slug . '=1',
				$slug . '$'           => 'index.php?' . $slug . '=1',//&' . $profile_slug . '=1',
			);

			return $rewrites;
		case 'profile' :
			$account_slug = it_exchange_get_page_slug( 'account' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account      = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account->post_name;
			}

			$rewrites = array(
				$account_slug . '/([^/]+)/' . $slug => 'index.php?' . $account_slug . '=$matches[1]&' . $slug . '=1',
				$account_slug . '/' . $slug . '$'   => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
			);

			return $rewrites;
		case 'registration' :
			$account_slug = it_exchange_get_page_slug( 'account' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account      = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account->post_name;
			}

			$rewrites = array(
				$account_slug . '/' . $slug . '$' => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
			);

			return $rewrites;
		case 'login' :
			$account_slug = it_exchange_get_page_slug( 'account' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account      = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account->post_name;
			}

			$rewrites = array(
				$account_slug . '/' . $slug . '$' => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
			);

			return $rewrites;
		case 'logout' :
			$account_slug = it_exchange_get_page_slug( 'account' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account      = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account->post_name;
			}

			$rewrites = array(
				$account_slug . '/' . $slug . '$' => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
			);

			return $rewrites;
		case 'purchases' :
			$account_slug = it_exchange_get_page_slug( 'account' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account      = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account->post_name;
			}

			$paginate = '(?:/(\d*))?';

			$rewrites = array(
				$account_slug . '/([^/]+)/' . $slug . $paginate . '$' => 'index.php?' . $account_slug . '=$matches[1]&' . $slug . '=1&page=$matches[2]',
				$account_slug . '/' . $slug . $paginate . '$'         => 'index.php?' . $account_slug . '=1&' . $slug . '=1&page=$matches[1]',
			);

			return $rewrites;
		case 'downloads' :
			$account_slug = it_exchange_get_page_slug( 'account' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account      = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account->post_name;
			}

			$rewrites = array(
				$account_slug . '/([^/]+)/' . $slug . '$' => 'index.php?' . $account_slug . '=$matches[1]&' . $slug . '=1',
				$account_slug . '/' . $slug . '$'         => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
			);

			return $rewrites;
		case 'confirmation' :
			$rewrites = array(
				$slug . '/([^/]+)/?$' => 'index.php?' . $slug . '=$matches[1]',
			);

			return $rewrites;
		case 'transaction' :
			$rewrites = array(
				$slug => 'index.php?' . $slug . '=1',
			);

			return $rewrites;
	}

	return false;
}

/**
 * Returns URL for core pages
 *
 * @since 0.4.4
 *
 * @param string $page
 *
 * @return string
 */
function it_exchange_get_core_page_urls( $page ) {
	$slug       = it_exchange_get_page_slug( $page );
	$permalinks = (boolean) get_option( 'permalink_structure' );
	$base       = trailingslashit( get_home_url() );

	// Processes Super Widget links
	if ( it_exchange_in_superwidget() && $slug != 'transaction' && $page != 'confirmation' ) {
		// Get current URL without exchange query args
		$url = it_exchange_clean_query_args();

		return add_query_arg( 'ite-sw-state', $slug, $url );
	}

	switch ( $page ) {
		// Store
		case 'store' :
		case 'confirmation' :
		case 'transaction' :
			if ( $permalinks ) {
				return trailingslashit( $base . $slug );
			} else {
				return add_query_arg( array( $slug => 1 ), $base );
			}
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
			if ( $permalinks ) {
				$base = trailingslashit( $base . $account_slug );
			} else {
				$base = add_query_arg( array( $account_slug => 1 ), $base );
			}

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
			} elseif ( $permalinks ) {
				return trailingslashit( $base . $slug );
			} else {
				return add_query_arg( array( $slug => 1 ), $base );
			}
	}
}

/**
 * Adds date retraints to query posts.
 *
 * This function isn't applied to any queries by default. Certain functions like those in the basic_reporting addon add
 * it as a filter and remove it.
 *
 * @since 0.4.9
 *
 * @param string $where the where clause of the query
 *
 * @return string
 */
function it_exchange_filter_where_clause_for_all_queries( $where = '' ) {

	// If this filter has been added, we expect one of the following two GLOBALS to have been set
	$start_date = empty( $GLOBALS['it_exchange']['where_start'] ) ? false : $GLOBALS['it_exchange']['where_start'];
	$end_date   = empty( $GLOBALS['it_exchange']['where_end'] ) ? false : $GLOBALS['it_exchange']['where_end'];

	// Return without doing anything if neither start or end are set
	if ( ! $start_date && ! $end_date ) {
		return $where;
	}

	if ( $start_date ) {
		$where .= $GLOBALS['wpdb']->prepare( ' AND post_date >= %s', $start_date );
	}

	if ( $end_date ) {
		$where .= $GLOBALS['wpdb']->prepare( ' AND post_date <= %s', $end_date );
	}

	return $where;
}

/**
 * Check if the billing address purchase requirement is complete.
 *
 * @since 2.0.0
 *
 * @return bool
 */
function it_exchange_billing_address_purchase_requirement_complete() {
	$cart = it_exchange_get_current_cart();

	$billing = $cart->get_billing_address();

	if ( ! $billing ) {
		return false;
	}

	if ( empty( $billing['address1'] ) ) {
		return false;
	}

	return true;
}

/**
 * The default checkout mode for the superwidget
 *
 * @since 1.6.0
 *
 * @return string
 */
function it_exchange_get_default_sw_checkout_mode() {
	$settings = it_exchange_get_option( 'settings_general' );

	$default_mode = empty( $settings['checkout-reg-form'] ) ? 'registration' : $settings['checkout-reg-form'];
	$default_mode = apply_filters( 'it_exchange_get_default_sw_checkout_mode', $default_mode );

	add_filter( 'it_exchange_is_sw_' . $default_mode . '_checkout_mode', '__return_true' );

	return $default_mode;
}

/**
 * Add custom image sizes to use in themes and admin.
 *
 * @since 1.6.0
 *
 * @return void
 */
function it_exchange_add_image_sizes() {
	$image_sizes = array(
		'large' => array(
			'width'  => 1000,
			'height' => 1000,
			'crop'   => false
		),
		'thumb' => array(
			'width'  => 150,
			'height' => 150,
			'crop'   => true
		),
	);

	foreach ( $image_sizes as $name => $data ) {
		add_image_size( 'it-exchange-' . $name, $data['width'], $data['height'], $data['crop'] );
	}
}

/**
 * Prints a tooltip in the admin
 *
 * @since 1.7.9
 *
 * @param string  $text      The HTML for the tooltip. Can be a plaintext string or HTML
 * @param boolean $echo      Echo the tooltip? defaults to true
 * @param string  $indicator The character used to indicate a tooltip is avaialable. Defaults to 'i'
 *
 * @return string
 */
function it_exchange_admin_tooltip( $text, $echo = true, $indicator = 'i' ) {

	$esc     = esc_attr( $text );
	$tooltip = "<span class='it-exchange-tip' data-tip-content='$esc' title='$esc'>$indicator</span>";
	$tooltip = apply_filters( 'it_exchange_admin_tooltip', $tooltip, $text, $indicator );

	if ( true === $echo ) {
		echo $tooltip;
	}

	return $tooltip;
}

/**
 * Get the requested cart and check the auth value.
 *
 * @since 2.0.0
 *
 * @param string $cart_var
 * @param string $auth_var
 *
 * @return ITE_Cart|null
 */
function it_exchange_get_requested_cart_and_check_auth( $cart_var = 'cart_id', $auth_var = 'cart_auth' ) {

	if ( empty( $_REQUEST[ $cart_var ] ) ) {
		return null;
	}

	$cart_id = trim( $_REQUEST[ $cart_var ] );
	$cart    = it_exchange_get_cart( $cart_id );

	if ( ! $cart || ! isset( $_REQUEST[ $auth_var ] ) || ! $cart->validate_auth_secret( $_REQUEST[ $auth_var ] ) ) {
		throw new UnexpectedValueException( __( 'Invalid cart authentication.', 'it-l10n-ithemes-exchange' ) );
	}

	if ( ! $cart->is_guest() ) {
		wp_set_current_user( $cart->get_customer()->ID );
	}

	return $cart;
}

/**
 * Get all core tables.
 *
 * @since 2.0.0
 *
 * @return \IronBound\DB\Table\Table[]
 */
function it_exchange_get_tables() {
	return array(
		\IronBound\DB\Manager::get( 'ite-transactions' ),
		\IronBound\DB\Manager::get( 'ite-address' ),
		\IronBound\DB\Manager::get( 'ite-line-items' ),
		\IronBound\DB\Manager::get( 'ite-line-items-meta' ),
		\IronBound\DB\Manager::get( 'ite-refunds' ),
		\IronBound\DB\Manager::get( 'ite-refunds-meta' ),
		\IronBound\DB\Manager::get( 'ite-payment-tokens' ),
		\IronBound\DB\Manager::get( 'ite-payment-tokens-meta' ),
		\IronBound\DB\Manager::get( 'ite-sessions' ),
		//\IronBound\DB\Manager::get( 'ite-logs' ),
	);
}

/**
 * Add a product to the database.
 *
 * Simple wrapper around `wp_insert_post()` and product feature APIs.
 *
 * @param array $args
 *
 * @return int|WP_Error|false
 */
function it_exchange_add_product( $args = array() ) {
	$defaults = array(
		'status' => 'publish',
	);
	$defaults = apply_filters( 'it_exchange_add_product_defaults', $defaults );

	$args = ITUtility::merge_defaults( $args, $defaults );

	// Convert $args to insert post args
	$post_args                = array();
	$post_args['post_status'] = $args['status'];
	$post_args['post_type']   = 'it_exchange_prod';
	$post_args['post_title']  = empty( $args['title'] ) ? '' : $args['title'];

	if ( it_exchange_product_type_supports_feature( $args['type'], 'extended-description' ) && ! empty( $args['extended-description'] ) ) {
		$post_args['post_content'] = $args['extended-description'];
	} else {
		$post_args['post_content'] = '';
	}

	if ( ! empty( $args['post_meta'] ) ) {
		$post_args['meta_input'] = $args['post_meta'];
	}

	if ( ! empty( $args['tax_input'] ) ) {
		$post_args['tax_input'] = $args['tax_input'];
	}

	// Insert Post and get ID
	if ( $product_id = wp_insert_post( $post_args ) ) {
		update_post_meta( $product_id, '_it_exchange_product_type', $args['type'] );
		update_post_meta( $product_id, '_it-exchange-visibility', empty( $args['show_in_store'] ) ? 'hidden' : 'visible' );

		$type = $args['type'];

		// Product Images from URLs
		if ( ! empty( $args['images-from-urls'] ) && is_array( $args['images-from-urls'] ) ) {
			foreach ( $args['images-from-urls'] as $url => $description ) {
				it_exchange_add_remote_image_to_product_images( $url, $product_id, $description );
			}

			unset( $args['images-from-url'] );
		}

		unset( $args['status'], $args['extended-description'], $args['type'], $args['post_meta'], $args['tax_input'] );

		foreach ( $args as $key => $value ) {
			if ( it_exchange_product_type_supports_feature( $type, $key ) ) {
				it_exchange_update_product_feature( $product_id, $key, $value );
			}
		}

		return $product_id;
	}

	return false;
}

/**
 * Download and import an image to the product's images by URL.
 *
 * @param string $url        URL of the image to add.
 * @param int    $product_id Product ID to add the image to.
 * @param string $desc       Description of the image.
 *
 * @return int|WP_Error
 */
function it_exchange_add_remote_image_to_product_images( $url, $product_id, $desc = '' ) {
	$tmp = download_url( $url );

	// Set variables for storage
	// fix file filename for query strings
	preg_match( '/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches );
	$file_array['name']     = basename( $matches[0] );
	$file_array['tmp_name'] = $tmp;

	// If error storing temporarily, unlink
	if ( is_wp_error( $tmp ) ) {
		@unlink( $file_array['tmp_name'] );
		$file_array['tmp_name'] = '';
	}

	// do the validation and storage stuff
	$id = media_handle_sideload( $file_array, $product_id, $desc );

	// If error storing permanently, unlink
	if ( is_wp_error( $id ) ) {
		@unlink( $file_array['tmp_name'] );

		return $id;
	}

	$product_images = it_exchange_get_product_feature( $product_id, 'product-images' );
	if ( empty( $product_images ) || ! is_array( $product_images ) ) {
		$product_images = array( $id );
	} else {
		$product_images[] = $id;
	}
	it_exchange_update_product_feature( $product_id, 'product-images', $product_images );
	@unlink( $file_array['temp_name'] );

	return $id;
}

/**
 * Get System Info.
 *
 * @since 2.0.0
 *
 * @return array
 */
function it_exchange_get_system_info() {

	/** @var $wpdb wpdb */
	global $wpdb;

	$info = array();

	$info['Site Info'] = array(
		'Site URL'  => site_url(),
		'Home URL'  => home_url(),
		'Multisite' => is_multisite() ? 'Yes' : 'No'
	);

	$wp_config = array(
		'Version'       => get_bloginfo( 'version' ),
		'Language'      => defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US',
		'Permalink'     => get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default',
		'Theme'         => wp_get_theme()->Name . ' ' . wp_get_theme()->Version,
		'Show on Front' => get_option( 'show_on_front' )
	);

	if ( get_option( 'show_on_front' ) == 'page' ) {
		$front_page_id = get_option( 'page_on_front' );
		$blog_page_id  = get_option( 'page_for_posts' );

		$wp_config['Page On Front']  = $front_page_id ? get_the_title( $front_page_id ) . " (#$front_page_id)" : 'Unset';
		$wp_config['Page For Posts'] = $blog_page_id ? get_the_title( $blog_page_id ) . " (#$blog_page_id)" : 'Unset';
	}

	$wp_config['ABSPATH']            = ABSPATH;
	$wp_config['Table Prefix']       = 'Length: ' . strlen( $wpdb->prefix ) . ' Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'Too long' : 'Acceptable' );
	$wp_config['WP_DEBUG']           = defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set';
	$wp_config['WP_DEBUG_LOG']       = defined( 'WP_DEBUG_LOG' ) ? WP_DEBUG_LOG ? 'Enabled' : 'Disabled' : 'Not set';
	$wp_config['SCRIPT_DEBUG']       = defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG ? 'Enabled' : 'Disabled' : 'Not set';
	$wp_config['Object Cache']       = wp_using_ext_object_cache() ? 'Yes' : 'No';
	$wp_config['Memory Limit']       = WP_MEMORY_LIMIT;
	$info['WordPress Configuration'] = $wp_config;

	$versions = get_option( 'it-exchange-versions' );
	$settings = it_exchange_get_option( 'settings_general' );

	$upgrader  = it_exchange_make_upgrader();
	$completed = array();

	foreach ( $upgrader->get_upgrades() as $upgrade ) {
		if ( $upgrader->is_upgrade_completed( $upgrade ) ) {
			$completed[] = $upgrade->get_name();
		}
	}

	$addons = wp_list_pluck( it_exchange_get_enabled_addons(), 'name' );

	$tables_installed = $tables_uninstalled = array();

	foreach ( it_exchange_get_tables() as $table ) {
		if ( \IronBound\DB\Manager::is_table_installed( $table ) ) {
			$tables_installed[] = $table->get_slug();
		} else {
			$tables_uninstalled[] = $table->get_slug();
		}
	}

	$info['iThemes Exchange'] = array(
		'Version'             => IT_Exchange::VERSION,
		'Previous'            => empty( $versions ) || empty( $versions['previous'] ) ? '' : $versions['previous'],
		'Currency Code'       => $settings['default-currency'],
		'Currency Symbol'     => it_exchange_get_currency_symbol( $settings['default-currency'] ),
		'Currency Position'   => ucfirst( $settings['currency-symbol-position'] ),
		'Thousands Separator' => $settings['currency-thousands-separator'],
		'Decimals Separator'  => $settings['currency-decimals-separator'],
		'Registration'        => $settings['site-registration'] == 'it' ? 'Exchange' : 'WordPress',
		'Completed Upgrades'  => implode( ', ', $completed ),
		'Installed Tables'    => implode( ', ', $tables_installed ),
		'Missing Tables'      => $tables_uninstalled ? implode( ', ', $tables_uninstalled ) : 'None',
		'Add-ons'             => implode( ', ', $addons )
	);

	$pages = it_exchange_get_pages();

	$info['Pages']['Compat Mode'] = it_exchange_is_pages_compat_mode() ? 'Yes' : 'No';

	foreach ( $pages as $page => $data ) {

		if ( $data['type'] == 'wordpress' ) {
			$detail = "WordPress (#{$data['wpid']})";
		} elseif ( $data['type'] == 'exchange' ) {
			$detail = 'Exchange';
		} elseif ( $data['type'] == 'disabled' ) {
			$detail = 'Disabled';
		} else {
			$detail = '';
		}

		if ( $detail ) {
			$info['Pages'][ $page ] = $detail;
		}
	}

	$plugins        = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	foreach ( $plugins as $plugin_path => $plugin ) {

		if ( ! in_array( $plugin_path, $active_plugins ) ) {
			continue;
		}

		$info['Active Plugins'][ $plugin['Name'] ] = $plugin['Version'];
	}

	foreach ( get_mu_plugins() as $plugin ) {
		$info['MU Plugins'][ $plugin['Name'] ] = $plugin['Version'];
	}

	if ( is_multisite() ) {
		$plugins        = wp_get_active_network_plugins();
		$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

		foreach ( $plugins as $plugin_path ) {

			$plugin_base = plugin_basename( $plugin_path );

			if ( ! array_key_exists( $plugin_base, $active_plugins ) ) {
				continue;
			}

			$plugin = get_plugin_data( $plugin_path );

			$info['Network Active Plugins'][ $plugin['Name'] ] = $plugin['Version'];
		}
	}

	$info['Webserver Configuration'] = array(
		'PHP Version'    => PHP_VERSION,
		'MySQL Version'  => $wpdb->db_version(),
		'Use MySQLi'     => $wpdb->use_mysqli ? 'Yes' : 'No',
		'Webserver Info' => $_SERVER['SERVER_SOFTWARE'],
		'Host'           => it_exchange_get_host()
	);

	$info['PHP Configuration'] = array(
		'Safe Mode'           => ini_get( 'safe_mode' ) ? 'Enabled' : 'Disabled',
		'Memory Limit'        => ini_get( 'memory_limit' ),
		'Upload Max Size'     => ini_get( 'upload_max_filesize' ),
		'Post Max Size'       => ini_get( 'post_max_size' ),
		'Upload Max Filesize' => ini_get( 'upload_max_filesize' ),
		'Time Limit'          => ini_get( 'max_execution_time' ),
		'Max Input Vars'      => ini_get( 'max_input_vars' ),
		'Display Errors'      => ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'
	);

	$info['PHP Extensions'] = array(
		'cURL'        => function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported',
		'fsockopen'   => function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported',
		'SOAP Client' => class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed',
		'Suhosin'     => extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed'
	);

	return $info;
}

/**
 * Get user host
 *
 * Returns the webhost this site is using if possible.
 *
 * Credit goes to Easy Digital Downloads
 *
 * @since 2.0.0
 *
 * @return string
 */
function it_exchange_get_host() {

	if ( defined( 'WPE_APIKEY' ) ) {
		$host = 'WP Engine';
	} elseif ( defined( 'PAGELYBIN' ) ) {
		$host = 'Pagely';
	} elseif ( DB_HOST == 'localhost:/tmp/mysql5.sock' ) {
		$host = 'ICDSoft';
	} elseif ( DB_HOST == 'mysqlv5' ) {
		$host = 'NetworkSolutions';
	} elseif ( strpos( DB_HOST, 'ipagemysql.com' ) !== false ) {
		$host = 'iPage';
	} elseif ( strpos( DB_HOST, 'ipowermysql.com' ) !== false ) {
		$host = 'IPower';
	} elseif ( strpos( DB_HOST, '.gridserver.com' ) !== false ) {
		$host = 'MediaTemple Grid';
	} elseif ( strpos( DB_HOST, '.pair.com' ) !== false ) {
		$host = 'pair Networks';
	} elseif ( strpos( DB_HOST, '.stabletransit.com' ) !== false ) {
		$host = 'Rackspace Cloud';
	} elseif ( strpos( DB_HOST, '.sysfix.eu' ) !== false ) {
		$host = 'SysFix.eu Power Hosting';
	} elseif ( isset( $_SERVER['SERVER_NAME'] ) && strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) !== false ) {
		$host = 'Flywheel';
	} else {
		// Adding a general fallback for data gathering
		$host = 'DBH/' . DB_HOST . ', SRV/' . isset( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : '';
	}

	return $host;
}

/**
 * Wrapper for set_time_limit to see if it is enabled.
 *
 * @author WooCommerce GPLv2
 *
 * @since  2.0.0
 *
 * @param int $limit
 */
function it_exchange_set_time_limit( $limit = 0 ) {
	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( $limit );
	}
}

if ( ! function_exists( 'it_exchange_dropdown_taxonomies' ) ) {

	/**
	 * Print a dropdown taxonomy select.
	 *
	 * @param string $args
	 *
	 * @return string
	 */
	function it_exchange_dropdown_taxonomies( $args = '' ) {
		$defaults = array(
			'show_option_all'  => '',
			'show_option_none' => '',
			'orderby'          => 'id',
			'order'            => 'ASC',
			'show_count'       => 0,
			'hide_empty'       => 1,
			'child_of'         => 0,
			'exclude'          => '',
			'echo'             => 1,
			'selected'         => 0,
			'hierarchical'     => 0,
			'name'             => '',
			'id'               => '',
			'class'            => 'postform',
			'depth'            => 0,
			'tab_index'        => 0,
			'taxonomy'         => 'category',
			'hide_if_empty'    => false
		);

		$defaults['selected'] = ( is_tax() ) ? get_query_var( 'term' ) : 0;

		$r = wp_parse_args( $args, $defaults );

		if ( ! isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
			$r['pad_counts'] = true;
		}

		extract( $r );

		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 ) {
			$tab_index_attribute = " tabindex=\"$tab_index\"";
		}

		$terms = get_terms( $taxonomy, $r );

		// Avoid clashes with the 'name' param of get_terms().
		$get_terms_args = $r;
		unset( $get_terms_args['name'] );
		$terms = get_terms( $r['taxonomy'], $get_terms_args );

		$name  = esc_attr( $name );
		$class = esc_attr( $class );
		$id    = $id ? esc_attr( $id ) : $name;

		if ( ! $r['hide_if_empty'] || ! empty( $terms ) ) {
			$output = "<select name='$name' id='$id' class='$class' $tab_index_attribute>\n";
		} else {
			$output = '';
		}

		if ( empty( $terms ) && ! $r['hide_if_empty'] && ! empty( $show_option_none ) ) {
			$show_option_none = apply_filters( 'list_cats', $show_option_none );
			$output .= "\t<option value='-1' selected='selected'>$show_option_none</option>\n";
		}

		if ( ! empty( $terms ) ) {

			if ( $show_option_all ) {
				$show_option_all = apply_filters( 'list_cats', $show_option_all );
				$selected        = ( '0' === strval( $r['selected'] ) ) ? " selected='selected'" : '';
				$output .= "\t<option value='0'$selected>$show_option_all</option>\n";
			}

			if ( $show_option_none ) {
				$show_option_none = apply_filters( 'list_cats', $show_option_none );
				$selected         = ( '-1' === strval( $r['selected'] ) ) ? " selected='selected'" : '';
				$output .= "\t<option value='-1'$selected>$show_option_none</option>\n";
			}

			if ( $hierarchical ) {
				$depth = $r['depth'];
			}  // Walk the full depth.
			else {
				$depth = - 1;
			} // Flat.

			$output .= it_exchange_walk_product_category_dropdown_tree( $terms, $depth, $r );
		}

		if ( ! $r['hide_if_empty'] || ! empty( $terms ) ) {
			$output .= "</select>\n";
		}

		$output = apply_filters( 'wp_dropdown_cats', $output );

		if ( $echo ) {
			echo $output;
		}

		return $output;
	}
}


/**
 * Retrieve HTML dropdown (select) content for category list.
 *
 * @uses  Walker_CategoryDropdown to create HTML dropdown content.
 * @since 1.7.9
 * @see   Walker_CategoryDropdown::walk() for parameters and return description.
 */
function it_exchange_walk_product_category_dropdown_tree() {
	$args = func_get_args();
	// the user's options are the third parameter
	if ( empty( $args[2]['walker'] ) || ! is_a( $args[2]['walker'], 'Walker' ) ) {
		$walker = new Walker_ProductCategoryDropdown;
	} else {
		$walker = $args[2]['walker'];
	}

	return call_user_func_array( array( &$walker, 'walk' ), $args );
}

/**
 * Create HTML dropdown list of IT Exchange Product Categories.
 *
 * @since 1.7.9
 * @uses  Walker
 */
class Walker_ProductCategoryDropdown extends Walker {
	/**
	 * @see   Walker::$tree_type
	 * @since 1.7.9
	 * @var string
	 */
	var $tree_type = 'category';

	/**
	 * @see   Walker::$db_fields
	 * @since 1.7.9
	 * @todo  Decouple this
	 * @var array
	 */
	var $db_fields = array( 'parent' => 'parent', 'id' => 'term_id' );

	/**
	 * Start the element output.
	 *
	 * @see   Walker::start_el()
	 * @since 1.7.9
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category Category data object.
	 * @param int    $depth    Depth of category. Used for padding.
	 * @param array  $args     Uses 'selected' and 'show_count' keys, if they exist. @see wp_dropdown_categories()
	 */
	function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		$pad = str_repeat( '&nbsp;', $depth * 3 );

		$cat_name = apply_filters( 'list_cats', $category->name, $category );
		$output .= "\t<option class=\"level-$depth\" value=\"" . $category->slug . "\"";
		if ( $category->slug === $args['selected'] ) {
			$output .= ' selected="selected"';
		}
		$output .= '>';
		$output .= $pad . $cat_name;
		if ( $args['show_count'] ) {
			$output .= '&nbsp;&nbsp;(' . $category->count . ')';
		}
		$output .= "</option>\n";
	}
}

/* ------- WP Compat Functions */

if ( ! function_exists( 'apply_filters_deprecated' ) ) {
	function apply_filters_deprecated( $tag, $args, $version, $replacement = false, $message = null ) {
		if ( ! has_filter( $tag ) ) {
			return $args[0];
		}

		_deprecated_hook( $tag, $version, $replacement, $message );

		return apply_filters_ref_array( $tag, $args );
	}
}

if ( ! function_exists( 'do_action_deprecated' ) ) {
	function do_action_deprecated( $tag, $args, $version, $replacement = false, $message = null ) {
		if ( ! has_action( $tag ) ) {
			return;
		}

		_deprecated_hook( $tag, $version, $replacement, $message );

		do_action_ref_array( $tag, $args );
	}
}

if ( ! function_exists( '_deprecated_hook' ) ) {
	function _deprecated_hook( $hook, $version, $replacement = null, $message = null ) {

		do_action( 'deprecated_hook_run', $hook, $replacement, $version, $message );

		if ( WP_DEBUG && apply_filters( 'deprecated_hook_trigger_error', true ) ) {
			$message = empty( $message ) ? '' : ' ' . $message;
			if ( ! is_null( $replacement ) ) {
				trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.' ), $hook, $version, $replacement ) . $message );
			} else {
				trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.' ), $hook, $version ) . $message );
			}
		}
	}
}

if ( ! function_exists( 'rest_authorization_required_code' ) ) {
	function rest_authorization_required_code() { return is_user_logged_in() ? 403 : 401; }
}