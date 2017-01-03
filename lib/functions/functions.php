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
 * Used to set admin menu capabilities
 *
 * @since 1.12.0
 *
 * @param string $context the context of where it's being used
 * @param string $capability the incoming capability
 * @return string
*/
function it_exchange_get_admin_menu_capability( $context='', $capability='manage_options' ) {

	// Allow addons to filter
	$capability =  apply_filters( 'it_exchange_admin_menu_capability', $capability, $context );

	// Clean and return
	return empty( $capability ) || ! is_string( $capability ) ? false : $capability;
}

/**
 * Formats a price based on settings
 *
 * @since 0.4.0
 * @todo possibly get this working with LC_MONETARY and money_format()
 *
 * @param mixed $price
 * @param bool $show_symbol
 *
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

	if ( $price < 0 ) {
		$before = '&minus;' . $before;
		$price  = abs( $price );
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

	$settings = it_exchange_get_option( 'settings_general' );

	wp_register_style( 'it-exchange-icon-fonts', IT_Exchange::$url . '/lib/assets/styles/exchange-fonts.css' );
	wp_register_style( 'jquery.contextMenu', IT_Exchange::$url . '/lib/assets/styles/jquery.contextMenu.min.css', '2.4.1', true );

	// Frontend Product JS
	if ( is_singular( 'it_exchange_prod' ) || IT_Exchange_SW_Shortcode::has_shortcode() ) {
		$script_deps = array();

		if ( ( 1 == $settings['enable-gallery-zoom'] ) )
			array_push( $script_deps, 'jquery-zoom' );

		if ( ( 1 == $settings['enable-gallery-popup'] ) )
			array_push( $script_deps, 'jquery-colorbox' );

		wp_enqueue_script( 'it-exchange-product-public-js', IT_Exchange::$url . '/lib/assets/js/exchange-product.js', $script_deps, false, true );
		wp_enqueue_style( 'it-exchange-icon-fonts' );

		wp_enqueue_script( 'exchange-purchase-dialog', IT_Exchange::$url . '/lib/purchase-dialog/js/exchange-purchase-dialog.js',
			array( 'jquery', 'detect-credit-card-type', 'jquery.payment' ), false, true
		);
	}

	if ( it_exchange_is_page( 'product' ) ) {
		wp_enqueue_script( 'exchange-purchase-dialog', IT_Exchange::$url . '/lib/purchase-dialog/js/exchange-purchase-dialog.js',
			array( 'jquery', 'detect-credit-card-type', 'jquery.payment' ), false, true
		);
	}

	// ****** CHECKOUT SPECIFIC SCRIPTS *******
	if ( it_exchange_is_page( 'checkout' )  ) {

		// Enqueue purchase dialog JS on checkout screen
		wp_enqueue_script( 'exchange-purchase-dialog', IT_Exchange::$url . '/lib/purchase-dialog/js/exchange-purchase-dialog.js',
			array( 'jquery', 'detect-credit-card-type', 'jquery.payment' ), false, true
		);

		// Register select to autocomplte
		wp_enqueue_style( 'it-exchange-autocomplete-style' );

		// General Checkout
		wp_enqueue_script( 'it-exchange-checkout-page', IT_Exchange::$url . '/lib/assets/js/checkout-page.js',
			array( 'it-exchange-event-manager', 'jquery' ), false, true
		);

		// Load Logged In purchase requirement JS if not logged in and on checkout page.
		if ( in_array( 'logged-in', $purchase_requirements ) && ! is_user_logged_in() ) {
			wp_enqueue_script( 'it-exchange-logged-in-purchase-requirement', IT_Exchange::$url . '/lib/assets/js/logged-in-purchase-requirement.js',
				array( 'jquery' ), false, true
			);
		}

		// Load Billing Address purchase requirement JS if not logged in and on checkout page.
		if ( in_array( 'billing-address', $purchase_requirements ) ) {
			wp_enqueue_script( 'it-exchange-billing-address-purchase-requirement',
				IT_Exchange::$url . '/lib/assets/js/billing-address-purchase-requirement.js',
				array( 'jquery', 'it-exchange-country-states-sync' ), false, true
			);
		}

		// Load country / state field sync if on checkout page
		wp_enqueue_script( 'it-exchange-country-states-sync', IT_Exchange::$url . '/lib/assets/js/country-states-sync.js',
			array( 'jquery', 'jquery-ui-autocomplete', 'jquery-select-to-autocomplete' ), false, true
		);

	} // ****** END CHECKOUT SPECIFIC SCRIPTS *******

	// Frontend Style
	if ( ! apply_filters( 'it_exchange_disable_frontend_stylesheet', false ) )
		wp_enqueue_style( 'it-exchange-public-css', IT_Exchange::$url. '/lib/assets/styles/exchange.css' );

	// Parent theme /exchange/style.css if it exists
	$parent_theme_css = get_template_directory() . '/exchange/style.css';

	if ( is_file( $parent_theme_css ) ) {
		wp_enqueue_style( 'it-exchange-parent-theme-css', ITUtility::get_url_from_file( $parent_theme_css ) );
	}

	// Child theme /exchange/style.css if it exists
	$child_theme_css = get_stylesheet_directory() . '/exchange/style.css';

	if ( is_file( $child_theme_css ) && ( $parent_theme_css != $child_theme_css || ! is_file( $parent_theme_css ) ) ) {
		wp_enqueue_style( 'it-exchange-child-theme-css', ITUtility::get_url_from_file( $child_theme_css ) );
	}
}
add_action( 'wp_enqueue_scripts', 'it_exchange_load_public_scripts' );

/**
 * Registers generic scripts we might want to use in plugins/addons
 *
 * @since 1.7.0
 *
 * @return void
*/
function it_exchange_register_scripts() {
	// jQuery Zoom
	wp_register_script( 'jquery-zoom', IT_Exchange::$url . '/lib/assets/js/jquery.zoom.min.js', array( 'jquery' ), false, true );

	// jQuery Colorbox
	wp_register_script( 'jquery-colorbox', IT_Exchange::$url . '/lib/assets/js/jquery.colorbox.min.js', array( 'jquery' ), false, true );

	// Detect CC Type
	wp_register_script( 'detect-credit-card-type', IT_Exchange::$url . '/lib/assets/js/detect-credit-card-type.js', array( 'jquery', 'jquery.payment' ), false, true );

	// Detect CC Type
	wp_register_script( 'it-exchange-event-manager', IT_Exchange::$url . '/lib/assets/js/event-manager.js', array(), false, true );

	wp_register_script( 'jquery.payment', IT_Exchange::$url . '/lib/assets/js/jquery.payment.min.js', array( 'jquery' ), '1.3.2', true );
	wp_register_script( 'backbonedeep', IT_Exchange::$url . '/lib/admin/js/backbone.modeldeep.min.js', array( 'backbone' ), '2.0.1', true );
	wp_register_script( 'backbone.paginator', IT_Exchange::$url . '/lib/admin/js/backbone.paginator.min.js', array( 'backbone' ), '2.0.5', true );
	wp_register_script( 'ithemes-momentjs', IT_Exchange::$url . '/lib/admin/js/moment.min.js', array(), '2.11.0', true );
	wp_register_script( 'jquery.contextMenu', IT_Exchange::$url . '/lib/assets/js/jquery.contextMenu.min.js', array( 'jquery-ui-position' ), '2.4.1', true );

	// Select to Autocomplete
	wp_register_script( 'jquery-select-to-autocomplete', IT_Exchange::$url . '/lib/assets/js/jquery.select-to-autocomplete.min.js',
		array( 'jquery', 'jquery-ui-autocomplete' )
	);
	wp_register_style( 'it-exchange-autocomplete-style', IT_Exchange::$url . '/lib/assets/styles/autocomplete.css' );

	$settings = it_exchange_get_option( 'settings_general' );
	$currency = it_exchange_get_currency_symbol( $settings['default-currency'] );

	wp_register_script( 'it-exchange-common', IT_Exchange::$url . '/lib/assets/js/common.js' );
	wp_localize_script( 'it-exchange-common', 'EXCHANGE_CONFIG', array(
		'dateFormat'    => it_exchange_convert_php_to_moment( get_option( 'date_format' ) ),
		'timeFormat'    => it_exchange_convert_php_to_moment( get_option( 'time_format' ) ),
		'symbol'        => $currency,
		'symbolPos'     => $settings['currency-symbol-position'],
		'decimals'      => 2,
		'thousandsSep'  => $settings['currency-thousands-separator'],
		'decimalsSep'   => $settings['currency-decimals-separator'],
		'restNonce'     => wp_create_nonce( 'wp_rest' ),
		'restUrl'       => rest_url( 'it_exchange/v1/' ),
		'currentUser'   => get_current_user_id(),
		'baseCountry'   => $settings['company-base-country'],
		'i18n' => array(
			'unknownError' => __( 'An unknown error occurred.', 'it-l10n-ithemes-exchange' ),
		)
	) );

	$rest_libs = array(
		'backbone', 'underscore', 'it-exchange-common', 'wp-util', 'ithemes-momentjs', 'backbonedeep',
		'wp-backbone', 'backbone.paginator'
	);
	wp_register_script( 'it-exchange-rest', IT_Exchange::$url . '/lib/assets/js/rest.js', $rest_libs, IT_Exchange::VERSION	);

	$config = array(
		'i18n' => array(
			'visualCC' => array(
				'name'   => _x( 'Name', 'Credit Card Holder Name', 'it-l10n-ithemes-exchange' ),
				'number' => _x( 'Number', 'Credit Card Number', 'it-l10n-ithemes-exchange' ),
			),
			'checkout' => array(
				'completePurchase' => __( 'Complete Purchase', 'it-l10n-ithemes-exchange' ),
				'purchased'        => __( 'Purchased!', 'it-l10n-ithemes-exchange' ),
				'cancel'           => __( 'Cancel', 'it-l10n-ithemes-exchange' ),
				'haveCoupon'       => __( 'Have a coupon?', 'it-10n-ithemes-exchange' ),
				'addCoupon'        => _x( 'Add', 'Add coupon', 'it-l10n-ithemes-exchange' ),
				'couponCode'       => __( 'Coupon Code', 'it-l10n-ithemes-exchange' ),
			),
			'paymentToken' => array(
				'addNew'          => _x( 'Add New', 'Add new payment source, like a credit card.', 'it-l10n-ithemes-exchange' ),
				'manageTokens'    => __( 'Manage Payment Methods', 'it-l10n-ithemes-exchange' ),
				'noTokens'        => __( 'No saved payment methods found.', 'it-l10n-ithemes-exchange' ),
				'edit'            => __( 'Edit', 'it-l10n-ithemes-exchange' ),
				'save'            => __( 'Save', 'it-l10n-ithemes-exchange' ),
				'cancel'          => __( 'Cancel', 'it-l10n-ithemes-exchange' ),
				'makePrimary'     => __( 'Make primary payment method.', 'it-l10n-ithemes-exchange' ),
				'labelLabel'      => __( 'Label', 'it-l10n-ithemes-exchange' ),
				'labelExpiration' => __( 'Expiration', 'it-l10n-ithemes-exchange' ),
			),
			'address' => array(
				'save'            => __( 'Save', 'it-l10n-ithemes-exchange' ),
				'cancel'          => __( 'Cancel', 'it-l10n-ithemes-exchange' ),
				'firstName'       => __( 'First Name', 'it-l10n-ithemes-exchange' ),
				'lastName'        => __( 'Last Name', 'it-l10n-ithemes-exchange' ),
				'address1'        => __( 'Address 1', 'it-l10n-ithemes-exchange' ),
				'address2'        => __( 'Address 2', 'it-l10n-ithemes-exchange' ),
				'country'         => __( 'Country', 'it-l10n-ithemes-exchange' ),
				'city'            => __( 'City', 'it-l10n-ithemes-exchange' ),
				'zip'             => __( 'Zip', 'it-l10n-ithemes-exchange' ),
				'state'           => __( 'State', 'it-l10n-ithemes-exchange' ),
				'label'           => __( 'Label', 'it-l10n-ithemes-exchange' ),
			),
		),
		'imageRoot' => IT_Exchange::$url . '/lib/assets/styles/images/'
	);

	if ( apply_filters( 'it_exchange_preload_cart_item_types', it_exchange_is_page( 'checkout' ) ) ) {
		$serializer = new \iThemes\Exchange\REST\Route\Cart\TypeSerializer();

		foreach ( ITE_Line_Item_Types::shows_in_rest() as $type ) {
			$config['cartItemTypes'][] = $serializer->serialize( $type );
		}
	}

	wp_localize_script( 'it-exchange-rest', 'ITExchangeRESTConfig', $config	);

	$js_tokenizers = array();

	foreach ( ITE_Gateways::handles( 'tokenize' ) as $gateway ) {
		$handler = $gateway->get_handler_by_request_name( 'tokenize' );

		if ( ! $handler instanceof ITE_Gateway_JS_Tokenize_Handler ) {
			continue;
		}

		$js_tokenizers[ $gateway->get_slug() ] = array(
			'fn' => $handler->get_tokenize_js_function()
		);
	}

	$scripts = wp_scripts();

	$data = $scripts->get_data( 'it-exchange-rest', 'data' ) ?: '';

	ob_start();

	?>
		var ITExchangeTokenizers = {};

		<?php foreach ( $js_tokenizers as $gateway => $tokenizer ) : ?>
			ITExchangeTokenizers.<?php echo esc_js( $gateway ); ?> = { fn: <?php echo $tokenizer['fn']; ?> };
		<?php endforeach; ?>
	<?php

	$data .= ob_get_clean();

	$scripts->add_data( 'it-exchange-rest', 'data', $data );
}
add_action( 'wp_enqueue_scripts', 'it_exchange_register_scripts', 1 );
add_action( 'admin_enqueue_scripts', 'it_exchange_register_scripts', 1 );

/**
 * Preload REST schemas.
 *
 * @since 2.0.0
 *
 * @param bool|string|string[] $schemas,... If true, all schemas will be preloaded. If false, no schemas will be preloaded.
 *                                          Otherwise, a list of schemas identified by their title can be provided.
 *                                          If no arguments given, will return a the current schema state.
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
 * Maybe preload REST schemas.
 *
 * @since 2.0.0
 */
function it_exchange_maybe_preload_schemas() {

	$preload = it_exchange_preload_schemas();

	/**
	 * Filter the schemas to preload.
	 *
	 * @since 2.0.0
	 *
	 * @param $preload bool|array True to preload all schemas. False to preload no schemas. An array of schema
	 *                            document titles to only preload a selected amount of schemas.
	 */
	$preload = apply_filters( 'it_exchange_preload_schemas', $preload );

	if ( $preload === false ) {
		return;
	}

	$manager = \iThemes\Exchange\REST\get_rest_manager();

	// This action is documented in lib/REST/load.php
	do_action( 'it_exchange_register_rest_routes', $manager );

	if ( $preload === true ) {
		$schemas = $manager->get_schemas();
	} else {
		$schemas = $manager->get_schemas( $preload );
	}

	wp_localize_script( 'it-exchange-rest', 'ITExchangeRESTSchemas', $schemas );
}

add_action( 'wp_enqueue_scripts', 'it_exchange_maybe_preload_schemas', 99 );
add_action( 'admin_enqueue_scripts', 'it_exchange_maybe_preload_schemas', 99 );

/**
 * Register additional REST backbone libs if the main lib is enqueued.
 *
 * @since 2.0.0
 */
function it_exchange_register_additional_rest_backbone_libs() {

	if ( ! wp_script_is( 'it-exchange-rest' ) ) {
		return;
	}

	/**
	 * Filter the dependencies for the REST backbone lib.
	 *
	 * This can be used to provide models and collections for an add-on.
	 *
	 * @since 2.0.0
	 *
	 * @param array $rest_libs
	 */
	$rest_libs = apply_filters( 'it_exchange_rest_backbone_addon_libs', array() );

	$scripts = wp_scripts();

	foreach ( $rest_libs as $addon => $src ) {
		wp_enqueue_script( "it-exchange-rest-{$addon}", $src, array( 'it-exchange-rest' ) );
	}
}

add_action( 'wp_print_footer_scripts', 'it_exchange_register_additional_rest_backbone_libs', 0 );
add_action( 'wp_print_scripts', 'it_exchange_register_additional_rest_backbone_libs', 0 );

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
 * Print inline scripts.
 *
 * @since 2.0.0
 */
function it_exchange_print_inline_scripts() {

	if ( function_exists( 'wp_add_inline_script' ) ) {
		return;
	}

	if ( ! isset( $GLOBALS['it_exchange']['inline-scripts'] ) ) {
		return;
	}

	foreach ( $GLOBALS['it_exchange']['inline-scripts'] as $handle => $scripts ) {

		if ( ! wp_script_is( $handle, 'done' ) ) {
			continue;
		}

		foreach ( $scripts as $script ) {
			printf( "<script type='text/javascript'>\n%s\n</script>\n", $script );
		}
	}
}

add_action( 'admin_footer', 'it_exchange_print_inline_scripts', 100 );
add_action( 'wp_footer', 'it_exchange_print_inline_scripts', 100 );

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
	$webhooks_processed = false;
	// Loop through them and init callbacks
    foreach( $webhooks as $key => $param ) {
		if ( ! empty( $_REQUEST[$param] ) ) {
			$webhooks_processed = true;
			$request_scheme = is_ssl() ? 'https://' : 'http://';
			$requested_webhook_url = untrailingslashit( $request_scheme . $_SERVER['HTTP_HOST'] ) . $_SERVER['REQUEST_URI']; //REQUEST_URI includes the slash
			$parsed_requested_webhook_url = parse_url( $requested_webhook_url );
			$required_webhook_url = add_query_arg( $param, '1', trailingslashit( get_home_url() ) ); //add the slash to make sure we match
			$parsed_required_webhook_url = parse_url( $required_webhook_url );
			$webhook_diff = array_diff_assoc( $parsed_requested_webhook_url, $parsed_required_webhook_url );

			if ( empty( $webhook_diff ) ) { //No differences in the requested webhook and the required webhook

				try {
					do_action( 'it_exchange_webhook_' . $param, $_REQUEST );
				} catch ( IT_Exchange_Locking_Exception $e ) {
					status_header( 500 );
					error_log( "Locking exception during webooks: {$e->getMessage()}" );
					die();
				}
			} else {
				wp_die(
					sprintf(
						__( 'Invalid webhook request for this site. The webhook request should be: %s', 'it-l10n-ithemes-exchange' ),
						$required_webhook_url
					),
					__( 'iThemes Exchange Webhook Process Error', 'it-l10n-ithemes-exchange' ),
					array( 'response' => 400 )
				);
			}

			break; //we can stop processing here... no need to continue the foreach since we can only handle one webhook at a time
		}
	}
	if ( $webhooks_processed ) {
		do_action( 'it_exchange_webhooks_processed' );
		wp_die(
			__( 'iThemes Exchange webhook process Complete', 'it-l10n-ithemes-exchange' ),
			__( 'iThemes Exchange Webhook Process Complete', 'it-l10n-ithemes-exchange' ),
			array( 'response' => 200 )
		);
	}
}
add_action( 'wp', 'it_exchange_process_webhooks' );

/**
 * Add reset exchange button to settings page if WP_Debug is on
 *
 * @since 0.4.2
 *
 * @param ITForm $form the ITForm object for the settings form
 *
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
		<th scope="row"><strong><?php _e( 'Dangerous Settings', 'it-l10n-ithemes-exchange' ); ?></strong></th>
		<td></td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="reset-exchange"><?php _e( 'Reset Exchange', 'it-l10n-ithemes-exchange' ) ?></label></th>
		<td>
			<?php $form->add_check_box( 'reset-exchange' ); ?>
			<label for="reset-exchange"><?php _e( 'Reset ALL data', 'it-l10n-ithemes-exchange' ) ?></label><br />
			<span class="description"><?php _e( 'Checking this box will reset ALL settings and DELETE ALL DATA.', 'it-l10n-ithemes-exchange' ); ?></span>
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
	it_exchange_add_message( 'notice', __( 'Exchange has been reset. All data has been deleted.', 'it-l10n-ithemes-exchange' ) );
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
		'name'          => __( 'Product', 'it-l10n-ithemes-exchange' ),
		'rewrite-rules' => false, //array( 10, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Product Base', 'it-l10n-ithemes-exchange' ),
		'type'          => 'exchange',
		'menu'          => false,
		'optional'      => false,
	);
	it_exchange_register_page( 'product', $options );

	// Store
	$options = array(
		'slug'          => 'store',
		'name'          => __( 'Store', 'it-l10n-ithemes-exchange' ),
		'rewrite-rules' => array( 230, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Store Page', 'it-l10n-ithemes-exchange' ),
		'tip'           => __( 'Where all your products are shown in one place', 'it-l10n-ithemes-exchange' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
	);
	it_exchange_register_page( 'store', $options );

	// Transaction
	$options = array(
		'slug'          => 'transaction',
		'name'          => __( 'Transaction', 'it-l10n-ithemes-exchange' ),
		'rewrite-rules' => array( 210, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Transaction', 'it-l10n-ithemes-exchange' ),
		'type'          => 'exchange',
		'menu'          => false,
		'optional'      => false,
	);
	it_exchange_register_page( 'transaction', $options );

	// Customer Registration
	$options = array(
		'slug'          => 'registration',
		'name'          => __( 'Registration', 'it-l10n-ithemes-exchange' ),
		'rewrite-rules' => array( 105, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Customer Registration', 'it-l10n-ithemes-exchange' ),
		'tip'           => __( 'Where customers register to login, download, etc. ', 'it-l10n-ithemes-exchange' ) .
		' ' . __('You can turn off registration and allow guest checkouts in Exchange / Add-ons / Digital Downloads Settings.', 'it-l10n-ithemes-exchange'),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
	);
	it_exchange_register_page( 'registration', $options );

	// Account
	$options = array(
		'slug'          => 'account',
		'name'          => __( 'Account', 'it-l10n-ithemes-exchange' ),
		'rewrite-rules' => array( 135, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Account Page', 'it-l10n-ithemes-exchange' ),
		'tip'           => __( 'This is the main landing page for customers after they log in to their account.', 'it-l10n-ithemes-exchange' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => false,
	);
	it_exchange_register_page( 'account', $options );

	// Profile
	$options = array(
		'slug'          => 'profile',
		'name'          => __( 'Profile', 'it-l10n-ithemes-exchange' ),
		'rewrite-rules' => array( 130, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Profile Page', 'it-l10n-ithemes-exchange' ),
		'tip'           => __( 'Private details about your customers that they can change.', 'it-l10n-ithemes-exchange' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
	);
	it_exchange_register_page( 'profile', $options );

	// Downloads
	$options = array(
		'slug'          => 'downloads',
		'name'          => __( 'Downloads', 'it-l10n-ithemes-exchange' ),
		'rewrite-rules' => array( 125, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Customer Downloads', 'it-l10n-ithemes-exchange' ),
		'tip'           => __( 'Page where the customer can find all of their available downloads.', 'it-l10n-ithemes-exchange' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
	);
	it_exchange_register_page( 'downloads', $options );

	// Purchases
	$options = array(
		'slug'          => 'purchases',
		'name'          => __( 'Purchases', 'it-l10n-ithemes-exchange' ),
		'rewrite-rules' => array( 120, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Purchases', 'it-l10n-ithemes-exchange' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
	);
	it_exchange_register_page( 'purchases', $options );

	// Log In
	$options = array(
		'slug'          => 'log-in',
		'name'          => __( 'Log In', 'it-l10n-ithemes-exchange' ),
		'rewrite-rules' => array( 110, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Customer Log In', 'it-l10n-ithemes-exchange' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
	);
	it_exchange_register_page( 'login', $options );

	// Log Out
	$options = array(
		'slug'          => 'log-out',
		'name'          => __( 'Log Out', 'it-l10n-ithemes-exchange' ),
		'rewrite-rules' => array( 115, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Customer Log Out', 'it-l10n-ithemes-exchange' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
	);
	it_exchange_register_page( 'logout', $options );

	// Confirmation
	$options = array(
		'slug'          => 'confirmation',
		'name'          => __( 'Thank you', 'it-l10n-ithemes-exchange' ),
		'rewrite-rules' => array( 205, 'it_exchange_get_core_page_rewrites' ),
		'url'           => 'it_exchange_get_core_page_urls',
		'settings-name' => __( 'Purchase Confirmation', 'it-l10n-ithemes-exchange' ),
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
 * @param string $page
 *
 * @return array
*/
function it_exchange_get_core_page_rewrites( $page ) {
	$slug = it_exchange_get_page_slug( $page );
	switch( $page ) {
		case 'store' :
			$rewrites = array(
				$slug . '$' => 'index.php?' . $slug . '=1',
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
				$slug . '/([^/]+)/?$' => 'index.php?' . $slug . '=$matches[1]',//&' . $profile_slug . '=1',
				$slug . '$' => 'index.php?' . $slug . '=1',//&' . $profile_slug . '=1',
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
				$account_slug . '/' . $slug . '$' => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
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
				$account_slug  . '/' . $slug . '$' => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
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
				$account_slug . '/' . $slug . '$' => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
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
				$account_slug . '/' . $slug . '$' => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
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

			$paginate = '(?:/(\d*))?';

			$rewrites = array(
				$account_slug  . '/([^/]+)/' . $slug . $paginate . '$' => 'index.php?' . $account_slug . '=$matches[1]&' . $slug . '=1&page=$matches[2]',
				$account_slug . '/' . $slug . $paginate . '$' => 'index.php?' . $account_slug . '=1&' . $slug . '=1&page=$matches[1]',
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
				$account_slug  . '/([^/]+)/' . $slug . '$' => 'index.php?' . $account_slug . '=$matches[1]&' . $slug . '=1',
				$account_slug . '/' . $slug . '$' => 'index.php?' . $account_slug . '=1&' . $slug . '=1',
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
 * @param string $page
 *
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
 *
 * @return string the template part
*/
function it_exchange_add_page_shortcode( $atts ) {
	$defaults = array(
		'page' => false,
	);
	$atts = shortcode_atts( $defaults, $atts );

	// Don't return anything if page type is not WordPress
	if ( 'wordpress' != it_exchange_get_page_type( $atts['page'] ) )
		return '';

	if ( empty( $atts['page'] ) )
		return false;

	$page = $atts['page'];

	if ( $page === 'confirmation' && $GLOBALS['IT_Exchange_Pages']->request_email_for_confirmation ) {
		$page = 'confirmation-email-form';
	}

	ob_start();
	it_exchange_get_template_part( 'content', $page );
	return ob_get_clean();
}
add_shortcode( 'it-exchange-page', 'it_exchange_add_page_shortcode' );

/**
 * Creates a shortcode that returns customer information
 *
 * @since 1.4.0
 *
 * @param array $atts attributes passed in via shortcode arguments
 *
 * @return string the template part
*/
function it_exchange_add_customer_shortcode( $atts ) {
	$defaults = array(
		'show' => false,
		'avatar_size' => 128,
	);
	$atts = shortcode_atts( $defaults, $atts );

	$whitelist = array(
		'first-name', 'last-name', 'username', 'email', 'avatar', 'site-name',
	);
	$whitelist = apply_filters( 'it_exchange_customer_shortcode_tag_list', $whitelist );

	if ( empty( $atts['show'] ) || ! in_array( $atts['show'], (array) $whitelist ) )
		return '';

	$options = array(
		'format' => 'field-value',
	);
	if ( 'avatar' == $atts['show'] )
		$options['size'] = $atts['avatar_size'];

	$output = it_exchange( 'customer', 'get-' . $atts['show'], $options );

	if ( empty( $output ) ) {
		//fallbacks if we have empty $output
		switch( $atts['show'] ) {
			case 'first-name':
					$output = it_exchange( 'customer', 'get-username', array( 'format' => 'field-value' ) );
				break;
		}
	}

	return $output;
}
add_shortcode( 'it_exchange_customer', 'it_exchange_add_customer_shortcode' );

/**
 * Adds date retraints to query posts.
 *
 * This function isn't applied to any queries by default. Certain functions like those in the basic_reporting addon add it as a filter and remove it.
 *
 * @since 0.4.9
 *
 * @param string $where the where clause of the query
 *
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
 * @param array $addon_slug name of addon being enabled.
 *
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
	$login      = __( 'Log in', 'it-l10n-ithemes-exchange' );
	$register   = __( 'register', 'it-l10n-ithemes-exchange' );
	$cart       = __( 'edit your cart', 'it-l10n-ithemes-exchange' );
	$login_link = '<a href="' . it_exchange_get_page_url( 'login' ) . '" class="it-exchange-login-requirement-login">';
	$reg_link   = '<a href="' . it_exchange_get_page_url( 'registration' ) . '" class="it-exchange-login-requirement-registration">';
	$cart_link  = '<a href="' . it_exchange_get_page_url( 'cart' ) . '">';
	$close_link = '</a>';

	$message = __( 'You must be logged in to complete your purchase.', 'it-l10n-ithemes-exchange' ) . ' ' . sprintf(
		__( '%1$sLog in%2$s, %3$sRegister%4$s, or %5$sedit your car%6$st.', 'it-l10n-ithemes-exchange' ),
		$login_link, $close_link,
		$reg_link, $close_link,
		$cart_link, $close_link
	);

	// User must be logged-in to checkout
	$properties = array(
		'priority'               => 1,
		'requirement-met'        => 'is_user_logged_in',
		'sw-template-part'       => it_exchange_get_default_sw_checkout_mode(),
		'checkout-template-part' => 'logged-in',
		'notification'           => $message,
	);

	it_exchange_register_purchase_requirement( 'logged-in', $properties );

	// Billing Address Purchase Requirement
	$properties = array(
		'priority'               => 5.11,
		'requirement-met'        => 'it_exchange_billing_address_purchase_requirement_complete',
		'sw-template-part'       => apply_filters( 'it_exchange_sw_template_part_for_logged_in_purchase_requirement', 'billing-address' ),
		'checkout-template-part' => 'billing-address',
		'notification'           => __( 'We need a billing address before you can checkout', 'it-l10n-ithemes-exchange' ),
	);
	// Only init the billing address if an add-on asks for it
	if ( apply_filters( 'it_exchange_billing_address_purchase_requirement_enabled', false ) )
		it_exchange_register_purchase_requirement( 'billing-address', $properties );
}
add_action( 'init', 'it_exchange_register_default_purchase_requirements' );

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

	if ( ! $billing['address1'] ) {
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
 * The default checkout mode for the page
 *
 * @since 1.6.0
 *
 * @return string
*/
function it_exchange_get_default_content_checkout_mode() {
	$settings = it_exchange_get_option( 'settings_general' );
	$default_mode = empty( $settings['checkout-reg-form'] ) ? 'registration' : $settings['checkout-reg-form'];
	$default_mode = apply_filters( 'it_exchange_get_default_content_checkout_mode', $default_mode );
	add_filter( 'it_exchange_is_content_' . $default_mode . '_checkout_mode', '__return_true' );
	return $default_mode;
}
add_action( 'template_redirect', 'it_exchange_get_default_content_checkout_mode' );

/**
 * Registers any purchase requirements Super Widget template parts as valid
 *
 * @since 1.2.0
 *
 * @param array $existing The existing valid template parts
 *
 * @return array
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
 * Remove purchase options if purchase requirements haven't been met
 *
 * @since 1.2.0
 *
 * @param array $elements
 *
 * @return array
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
 *
 * @return array
*/
function it_exchange_add_billing_address_to_sw_template_totals_loops( $loops ) {

	// Abandon if not doing billing
	if ( ! apply_filters( 'it_exchange_billing_address_purchase_requirement_enabled', false ) )
		return $loops;

	// Set index to end of array.
	$index = array_search( 'discounts', $loops );
	$index = ( false === $index ) ? array_search( 'totals-taxes', $loops ) : $index;
	$index = ( false === $index ) ? count($loops) -1 : $index;

	array_splice( $loops, $index, 0, 'billing-address' );
	return $loops;
}
add_filter( 'it_exchange_get_super-widget-checkout_after-cart-items_loops', 'it_exchange_add_billing_address_to_sw_template_totals_loops' );

/**
 * Clear Billing Address when the cart is emptied or a user logs out.
 *
 * @since 1.3.0
 *        
 * @param \ITE_Cart $cart
 *
 * @return void
*/
function it_exchange_clear_billing_on_cart_empty( ITE_Cart $cart ) {
	
	if ( $cart->is_current() ) {
		it_exchange_remove_cart_data( 'billing-address' );
	}
}
add_action( 'it_exchange_empty_cart', 'it_exchange_clear_billing_on_cart_empty' );

/**
 * Clear the billing address when a user logs out.
 *
 * @since 2.0.0
 */
function it_exchange_clear_billing_on_logout() {
	it_exchange_remove_cart_data( 'billing-address' );
}

add_action( 'wp_logout', 'it_exchange_clear_billing_on_logout' );

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
		var itExchangeAjaxCountryStatesAjaxURL = '<?php echo esc_js( trailingslashit( get_home_url() ) ); ?>';
	</script>
	<?php
}
add_action( 'wp_head', 'it_exchange_print_home_url_in_js' );

/**
 * Force rewrite rule update on upgrade
 *
 * @since 1.4.0
 *
 * @return void
*/
function it_exchange_force_rewrite_flush_on_upgrade() {
	add_option('_it-exchange-flush-rewrites', true );
}
add_action( 'it_exchange_version_updated', 'it_exchange_force_rewrite_flush_on_upgrade' );

/**
 * Force rewrite rule update on upgrade
 *
 * @since 1.8.1
 *
 * @param array $versions old and new versions. not used here
 *
 * @return void
*/
function it_exchange_clean_duplicate_user_post_meta( $versions ) {
	if ( version_compare( '1.8.1', $versions['previous'], '>' ) ) {
		global $wpdb;

		$wpdb->query( 
			"
			DELETE n1 
			FROM $wpdb->postmeta n1, $wpdb->postmeta n2 
			WHERE n1.post_id = n2.post_id 
			AND n1.meta_key = '_it_exchange_transaction_id' 
			AND n1.meta_value = n2.meta_value 
			AND n1.meta_id > n2.meta_id
			"
		);

		$wpdb->query(
			"
			DELETE n1 
			FROM $wpdb->usermeta n1, $wpdb->usermeta n2 
			WHERE n1.user_id = n2.user_id 
			AND n1.meta_key = '_it_exchange_transaction_id' 
			AND n1.meta_value = n2.meta_value 
			AND n1.umeta_id > n2.umeta_id
			"
		);
	}
}
add_action( 'it_exchange_version_updated', 'it_exchange_clean_duplicate_user_post_meta' );

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
/*
NOTE Tableing this for now until we write a way to regenerate images for users.
add_action( 'init', 'it_exchange_add_image_sizes' );
*/

/**
 * Change the content_width global if we are viewing
 * an Exchange product page.
 *
 * NOTE The function is temporary until we add the image
 * sizes function above.
 *
 * @since 1.5
 *
 * @return void
 * @var $content_width
*/
function it_exchange_set_content_width_on_product_pages() {
	if ( it_exchange_is_page( 'product' ) ) {
		global $content_width;
		$content_width = 1024;
	}
}
add_action( 'template_redirect', 'it_exchange_set_content_width_on_product_pages', 100 );

/**
 * Redirects to Exchange Login page if login fails
 *
 * Technically, we're hijacking a filter to use it for an action.
 *
 * @since 1.6.0
 *
 * @param  WP_Error $error instance of WP_Error
 * @return mixed
*/
function it_exchange_redirect_to_correct_login_form_on_error( $error ) {
	if ( empty( $error ) || ! is_wp_error( $error ) || ( empty( $error->errors ) && empty( $_POST ) ) )
		return $error;

	$wp_referer       = wp_get_referer();
	$exchange_pages[] = it_exchange_get_page_url( 'login' );
	$exchange_pages[] = it_exchange_get_page_url( 'checkout' );

	if ( in_array( $wp_referer, $exchange_pages ) ) {
		if ( empty( $error->errors ) && empty( $_POST['log'] ) && empty( $_POST['pwd'] ) ) {
			it_exchange_add_message( 'error', __( 'Please provide a username and password', 'it-l10n-ithemes-exchange' ) );
		} else {
			it_exchange_add_message( 'error', $error->get_error_message() );
		}

		$url_target = ( $wp_referer == $exchange_pages[1] ) ? 'checkout' : 'login';
		it_exchange_redirect( $wp_referer, 'login-failed-from-' . $url_target );
		die();
	}
	return $error;
}
add_filter( 'wp_login_errors', 'it_exchange_redirect_to_correct_login_form_on_error', 99 );

/**
 * Prints a tooltip in the admin
 *
 * @since 1.7.9
 *
 * @param string  $text       the HTML for the tooltip. Can be a plaintext string or HTML
 * @param boolean $echo       echo the tooltip? defaults to true
 * @param string  $indicator  the character used to indicate a tooltip is avaialable. Defaults to 'i'
 *
 * @return string
*/
function it_exchange_admin_tooltip( $text, $echo=true, $indicator='i' ) {
	$esc = esc_attr( $text );
	$tooltip = "<span class='it-exchange-tip' data-tip-content='$esc' title='$esc'>$indicator</span>";
	$tooltip = apply_filters( 'it_exchange_admin_tooltip', $tooltip, $text, $indicator );

	if ( true === $echo )
		echo $tooltip;

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

	if ( ! empty( $_REQUEST[ $cart_var ] ) ) {

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

	return null;
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
	);
}

/**
 * Blocks access to Download iThemes Exchange attachments
 *
 * @since 1.7.18
 *
 * @return void
 */
function it_exchange_block_attachments() {
	if ( ! is_attachment() )
		return;

	$uri = wp_get_attachment_url( get_the_ID() );

	$args = array(
		'post_type' => 'it_exchange_download',
		'meta_query' => array(
			array(
				'key' => '_it-exchange-download-info',
				'value' => $uri,
				'compare' => 'LIKE',
			)
		),
	);
	$results = get_posts( $args );

	if ( empty( $results ) )
		return;

	wp_die(
		__( 'You do not have permission to view this file.', 'it-l10n-ithemes-exchange' ),
		__( 'Error', 'it-l10n-ithemes-exchange' ),
		array( 'response' => 403, 'back_link' => true )
	);
}
add_action( 'template_redirect', 'it_exchange_block_attachments' );

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
			foreach( $args['images-from-urls'] as $url => $description ) {
				it_exchange_add_remote_image_to_product_images( $url, $product_id, $description );
			}
			unset( $args['images-from-url'] );
		}

		unset( $args['status'] );
		unset( $args['extended-description'] );
		unset( $args['type'] );
		unset( $args['post_meta'] );
		unset( $args['tax_input'] );

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


if ( !function_exists( 'it_exchange_dropdown_taxonomies' ) ) {

	function it_exchange_dropdown_taxonomies( $args = '' ) {
	    $defaults = array(
	        'show_option_all' => '', 'show_option_none' => '',
	        'orderby' => 'id', 'order' => 'ASC',
	        'show_count' => 0,
	        'hide_empty' => 1, 'child_of' => 0,
	        'exclude' => '', 'echo' => 1,
	        'selected' => 0, 'hierarchical' => 0,
	        'name' => '', 'id' => '',
	        'class' => 'postform', 'depth' => 0,
	        'tab_index' => 0, 'taxonomy' => 'category',
	        'hide_if_empty' => false
	    );

	    $defaults['selected'] = ( is_tax() ) ? get_query_var( 'term' ) : 0;

	    $r = wp_parse_args( $args, $defaults );

	    if ( !isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
	        $r['pad_counts'] = true;
	    }

	    extract( $r );

	    $tab_index_attribute = '';
	    if ( (int) $tab_index > 0 )
	        $tab_index_attribute = " tabindex=\"$tab_index\"";

	    $terms = get_terms( $taxonomy, $r );

		 // Avoid clashes with the 'name' param of get_terms().
		$get_terms_args = $r;
		unset( $get_terms_args['name'] );
		$terms = get_terms( $r['taxonomy'], $get_terms_args );

	    $name = esc_attr( $name );
	    $class = esc_attr( $class );
	    $id = $id ? esc_attr( $id ) : $name;

	    if ( ! $r['hide_if_empty'] || ! empty($terms) )
	        $output = "<select name='$name' id='$id' class='$class' $tab_index_attribute>\n";
	    else
	        $output = '';

	    if ( empty($terms) && ! $r['hide_if_empty'] && !empty($show_option_none) ) {
	        $show_option_none = apply_filters( 'list_cats', $show_option_none );
	        $output .= "\t<option value='-1' selected='selected'>$show_option_none</option>\n";
	    }

	    if ( ! empty( $terms ) ) {

	        if ( $show_option_all ) {
	            $show_option_all = apply_filters( 'list_cats', $show_option_all );
	            $selected = ( '0' === strval($r['selected']) ) ? " selected='selected'" : '';
	            $output .= "\t<option value='0'$selected>$show_option_all</option>\n";
	        }

	        if ( $show_option_none ) {
	            $show_option_none = apply_filters( 'list_cats', $show_option_none );
	            $selected = ( '-1' === strval($r['selected']) ) ? " selected='selected'" : '';
	            $output .= "\t<option value='-1'$selected>$show_option_none</option>\n";
	        }

	        if ( $hierarchical )
	            $depth = $r['depth'];  // Walk the full depth.
	        else
	            $depth = -1; // Flat.

	        $output .= it_exchange_walk_product_category_dropdown_tree( $terms, $depth, $r );
	    }

	    if ( ! $r['hide_if_empty'] || ! empty($terms) )
	        $output .= "</select>\n";

	    $output = apply_filters( 'wp_dropdown_cats', $output );

	    if ( $echo )
	        echo $output;

	    return $output;
	}

}

/**
 * Add At a Glance dashboard stats for products
 *
 * @since 1.7.27
 *
 * @param $elements array
 *
 * @return array
*/
function it_exchange_at_a_glance( $elements ) {
	$product_counts = wp_count_posts( 'it_exchange_prod' );
	if ( $product_counts && $product_counts->publish ) {
		$text = _n( '%s Product', '%s Products', $product_counts->publish );
		$text = sprintf( $text, number_format_i18n( $product_counts->publish ) );
		$post_type_object = get_post_type_object( 'it_exchange_prod' );
		if ( $post_type_object && current_user_can( $post_type_object->cap->edit_posts ) ) {
			$elements[] = sprintf( '<a class="it-exchange-glance-products" href="edit.php?post_type=%1$s">%2$s</a>', 'it_exchange_prod', $text );
		} else {
			$elements = sprintf( '<span class="it-exchange-glance-products">%2$s</span>', 'it_exchange_prod', $text );
		}
	}
	return $elements;
}
add_filter( 'dashboard_glance_items', 'it_exchange_at_a_glance' );

/**
 * Adds notification about Sync Integration for users with admin rights
 *
 * @since 1.10.0
 *
 * @return void
*/
function it_exchange_show_ithemes_sync_integration_nag() {
	$show_nag = true;

	// Get current user
	$current_user = wp_get_current_user();

	// If nag is being dismissed, dismiss it.
    if ( ! empty( $_GET['it-exchange-dismiss-sync-integration-nag'] ) ) {
		update_user_meta( $current_user->ID, '_it_exchange_dismiss_sync_nag', true );
		$show_nag = false;
	}

	// Check for dismissed tag
	if ( $show_nag )
		$show_nag = ( true == get_user_meta( $current_user->ID, '_it_exchange_dismiss_sync_nag', true ) ) ? false : true;

	if ( ! current_user_can( 'manage_options' ) )
		$show_nag = false;

    if ( ! empty( $show_nag ) && ! empty( $_GET ) ) {
        $more_info_url   = 'http://ithemes.com/2014/06/24/track-sales-sync-new-ithemes-exchange-integration/';
        $dismiss_url = add_query_arg( array( 'it-exchange-dismiss-sync-integration-nag' => 1 ) ); // escaped before printed
        include( dirname( dirname( __FILE__ ) ) . '/admin/views/notices/ithemes-sync-integration.php' );
    }
}
add_action( 'admin_notices', 'it_exchange_show_ithemes_sync_integration_nag' );

/**
 * This should only run once on update.
 *
 * It fixes a problem we had with carts. We have to clear
 * all users' cached carts and reset all sessions to clear bad data.
 *
 * @todo Remove this function after December, 2014
 *
 * @since 1.10.2
 *
 * @return void
*/
function it_exchange_fix_bad_data_in_carts( $versions ) {
	global $wpdb;

	// Abandon if already run
	delete_option( 'it_exchange_bad_cart_data_fixed' );
	if ( version_compare( $versions['previous'], '1.11.16', '>=' ) ) {
		return;
	}

	// Reset everyone's current sessions by deleting them.
	it_exchange_db_delete_all_sessions();

	// Delete all the active carts for all users
	$q = $wpdb->prepare( 'DELETE FROM ' . $wpdb->usermeta . ' WHERE meta_key = %s', '_it_exchange_active_user_carts' );
	$wpdb->query( $q );

	// Delete all cached carts
	$q = $wpdb->prepare( 'DELETE FROM ' . $wpdb->usermeta . ' WHERE meta_key = %s', '_it_exchange_cached_cart' );
	$wpdb->query( $q );
}
add_action( 'it_exchange_version_updated', 'it_exchange_fix_bad_data_in_carts' );

/**
 * Retrieve HTML dropdown (select) content for category list.
 *
 * @uses Walker_CategoryDropdown to create HTML dropdown content.
 * @since 1.7.9
 * @see Walker_CategoryDropdown::walk() for parameters and return description.
 */
function it_exchange_walk_product_category_dropdown_tree() {
    $args = func_get_args();
    // the user's options are the third parameter
    if ( empty($args[2]['walker']) || !is_a($args[2]['walker'], 'Walker') )
        $walker = new Walker_ProductCategoryDropdown;
    else
        $walker = $args[2]['walker'];

    return call_user_func_array(array( &$walker, 'walk' ), $args );
}

/**
 * Create HTML dropdown list of IT Exchange Product Categories.
 *
 * @since 1.7.9
 * @uses Walker
 */
class Walker_ProductCategoryDropdown extends Walker {
    /**
     * @see Walker::$tree_type
     * @since 1.7.9
     * @var string
     */
    var $tree_type = 'category';

    /**
     * @see Walker::$db_fields
     * @since 1.7.9
     * @todo Decouple this
     * @var array
     */
    var $db_fields = array ('parent' => 'parent', 'id' => 'term_id');

    /**
     * Start the element output.
     *
     * @see Walker::start_el()
     * @since 1.7.9
     *
     * @param string $output   Passed by reference. Used to append additional content.
     * @param object $category Category data object.
     * @param int    $depth    Depth of category. Used for padding.
     * @param array  $args     Uses 'selected' and 'show_count' keys, if they exist. @see wp_dropdown_categories()
     */
    function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
        $pad = str_repeat('&nbsp;', $depth * 3);

        $cat_name = apply_filters('list_cats', $category->name, $category);
        $output .= "\t<option class=\"level-$depth\" value=\"".$category->slug."\"";
        if ( $category->slug === $args['selected'] ) {
            $output .= ' selected="selected"';
		}
        $output .= '>';
        $output .= $pad.$cat_name;
        if ( $args['show_count'] )
            $output .= '&nbsp;&nbsp;('. $category->count .')';
        $output .= "</option>\n";
    }
}
/************************************
 * THE PREVIOUS API METHODS AREN'T READY
 * FOR PRIMETIME YET SO THEY LIVE HERE FOR NOW.
 * USE WITH CAUTION
 *************************************/

function it_exchange_add_on_before_disable_payment_gateways( $add_on ) {

	$message = __( 'Deactivating a payment gateway can cause customers to lose access to any membership products they have purchased using this payment gateway.', 'LION' );
	$message .= ' ' . __( 'Are you sure you want to proceed? %s | %s', 'LION' );

	if ( !empty( $_GET['page'] ) && 'it-exchange-setup' !== $_GET['page'] ) {
		if ( empty( $_GET['remove-gateway'] ) || 'yes' !== $_GET['remove-gateway'] ) {
			switch( $add_on ) {
				case 'offline-payments':
				case 'paypal-standard':
				case 'paypal-standard-secure':
				case 'zero-sum-checkout':
					$title = __( 'Payment Gateway Warning', 'LION' );
					$yes = '<a href="' . esc_url( add_query_arg( 'remove-gateway', 'yes' ) ) . '">' . __( 'Yes', 'LION' ) . '</a>';
					$no  = '<a href="javascript:history.back()">' . __( 'No', 'LION' ) . '</a>';
					$message = '<p>' . sprintf( $message, $yes, $no ) . '</p>';
					$args = array(
						'response'  => 200,
						'back_link' => false,
					);
					wp_die( $message, $title, $args );
			}
		}
	}
}
add_action( 'it_exchange_add_on_before_disable', 'it_exchange_add_on_before_disable_payment_gateways' );

/**
 * Setup schedule for delete transient transactions (moved away from WP transient API)
 *
 * @since CHANGEME
 *
 * @return void
*/
function it_exchange_transient_transactions_garbage_collection() {
	if ( ! wp_next_scheduled( 'it_exchange_trans_txn_garbage_collection' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'it_exchange_trans_txn_garbage_collection' );
	}

	if ( ! wp_next_scheduled( 'it_exchange_delete_upgrade_logs' ) ) {
		wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', 'it_exchange_delete_upgrade_logs' );
	}
}
add_action( 'wp', 'it_exchange_transient_transactions_garbage_collection' );

/**
 * Delete all expired transients in a single query
 *
 * @since CHANGEME
 *
 * @return void
*/
function it_exchange_trans_txn_cleanup() {
	global $wpdb;

	if ( defined( 'WP_SETUP_CONFIG' ) ) {
		return;
	}

	if ( ! defined( 'WP_INSTALLING' ) ) {

		$expiration_keys = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'ite_temp_tnx_expires_%'" );

		$now = time();
		$expired_sessions = array();

		foreach( $expiration_keys as $expiration ) {
			// If the session has expired
			if ( $now > intval( $expiration->option_value ) ) {
				// Get the Method and Temp ID by parsing the option_name
				$temp_txn_name = substr( $expiration->option_name, 21 );
				list( $method, $temp_id ) = explode( '_', $temp_txn_name, 2 );

				$expired_transients[] = 'ite_temp_tnx_expires_' . $method . '_' . $temp_id;
				$expired_transients[] = 'ite_temp_tnx_' . $method . '_' . $temp_id;
			}
		}

		// Delete all expired transients in a single query
		if ( ! empty( $expired_transients ) ) {
			$formatted = implode( ', ', array_fill( 0, count( $expired_transients ), '%s' ) );
			$query     = $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name IN ($formatted)", $expired_transients );
			$wpdb->query( $query );
		}
	}

	// Allow other plugins to hook in to the garbage collection process.
	do_action( 'it_exchange_trans_txn_cleanup' );
}
add_action( 'it_exchange_trans_txn_garbage_collection', 'it_exchange_trans_txn_cleanup' );

/**
 * Enqueue Manage Tokens JS.
 *
 * @since 2.0.0
 */
function it_exchange_enqueue_manage_tokens_js() {

	if ( ! it_exchange_is_page( 'profile' ) ) {
		return;
	}

	$customer = it_exchange_get_current_customer();

	if ( ! $customer ) {
		return;
	}

	it_exchange_preload_schemas( 'payment-token', 'customer', 'address' );

	wp_enqueue_script(
		'it-exchange-profile',
		IT_Exchange::$url . '/lib/assets/js/profile.js',
		array( 'it-exchange-rest', 'jquery.payment', 'jquery-select-to-autocomplete', 'jquery.contextMenu' )
	);

	it_exchange_add_inline_script( 'it-exchange-profile', include IT_Exchange::$dir . 'lib/assets/templates/manage-tokens.html' );
	it_exchange_add_inline_script( 'it-exchange-profile', include IT_Exchange::$dir . 'lib/assets/templates/address-form.html' );
	it_exchange_add_inline_script( 'it-exchange-profile', include IT_Exchange::$dir . 'lib/assets/templates/customer-addresses.html' );

	$filter = new \iThemes\Exchange\REST\Helpers\ContextFilterer();

	$address_serializer  = new \iThemes\Exchange\REST\Route\Customer\Address\Serializer();
	$customer_serializer = new \iThemes\Exchange\REST\Route\Customer\Serializer();

	$token_serializer = new \iThemes\Exchange\REST\Route\Customer\Token\Serializer();
	$token_schema     = $token_serializer->get_schema();

	$tokens = array();

	foreach ( $customer->get_tokens() as $token ) {
		$tokens[] = $filter->filter( $token_serializer->serialize( $token ), 'edit', $token_schema );
	}

	$settings = it_exchange_get_option( 'settings_general' );
	$country  = $settings['company-base-country'];

	wp_localize_script( 'it-exchange-profile', 'ITExchangeProfileConfig', array(
		'tokens'      => $tokens,
		'customer'    => $customer_serializer->serialize( $customer, 'edit' ),
		'billing'     => ( $b = $customer->get_billing_address( true ) ) ? $address_serializer->serialize( $b ) : null,
		'shipping'    => ( $s = $customer->get_shipping_address( true ) ) ? $address_serializer->serialize( $s ) : null,
		'baseCountry' => $country,
		'i18n'        => array(
			'manageAddresses' => __( 'Manage Addresses', 'it-l10n-ithemes-exchange' ),
			'billingLabel'    => __( 'Billing', 'it-l10n-ithemes-exchange' ),
			'shippingLabel'   => __( 'Shipping', 'it-l10n-ithemes-exchange' ),
			'edit'            => __( 'Edit Address', 'it-l10n-ithemes-exchange' ),
			'save'            => __( 'Save Address', 'it-l10n-ithemes-exchange' ),
			'cancel'          => __( 'Cancel', 'it-l10n-ithemes-exchange' ),
			'delete'          => __( 'Delete Address', 'it-l10n-ithemes-exchange' ),
			'makePrimary'     => __( 'Make Address Primary', 'it-l10n-ithemes-exchange' ),
			'addNew'          => __( 'Add New', 'it-l10n-ithemes-exchange' ),
			'firstName'       => __( 'First Name', 'it-l10n-ithemes-exchange' ),
			'lastName'        => __( 'Last Name', 'it-l10n-ithemes-exchange' ),
			'address1'        => __( 'Address 1', 'it-l10n-ithemes-exchange' ),
			'address2'        => __( 'Address 2', 'it-l10n-ithemes-exchange' ),
			'country'         => __( 'Country', 'it-l10n-ithemes-exchange' ),
			'city'            => __( 'City', 'it-l10n-ithemes-exchange' ),
			'zip'             => __( 'Zip', 'it-l10n-ithemes-exchange' ),
			'state'           => __( 'State', 'it-l10n-ithemes-exchange' ),
			'label'           => __( 'Label', 'it-l10n-ithemes-exchange' ),
		)
	) );

	wp_enqueue_style( 'it-exchange-autocomplete-style' );
	wp_enqueue_style( 'jquery.contextMenu' );
}

add_action( 'wp_enqueue_scripts', 'it_exchange_enqueue_manage_tokens_js' );

/**
 * Print manage tokens container.
 *
 * @since 2.0.0
 */
function it_exchange_print_manage_tokens_container() {
	echo '<div class="it-exchange-customer-addresses-container"></div>';
	echo '<div class="it-exchange-manage-tokens-container"></div>';
}

add_action( 'it_exchange_content_profile_after_form', 'it_exchange_print_manage_tokens_container' );

/**
 * Delete upgrade logs older than 7 days.
 *
 * @since 2.0.0
 *
 * @param int $days_old
 */
function it_exchange_delete_upgrade_logs( $days_old = 7 ) {

	it_classes_load( 'it-file-utility.php' );

	$dir = ITFileUtility::get_writable_directory( array(
		'name'             => 'it-exchange-upgrade',
		'require_existing' => true,
	) );

	if ( is_wp_error( $dir ) ) {
		return;
	}

	$files = ITFileUtility::get_flat_file_listing( $dir, true );

	if ( is_wp_error( $files ) || empty( $files ) ) {
		return;
	}

	foreach ( $files as $file ) {
		$modified = filemtime( $file );

		if ( $modified === false ) {
			continue;
		}

		if ( time() - $modified > DAY_IN_SECONDS * $days_old ) {
			@unlink( $file );
		}
	}

}

add_action( 'it_exchange_delete_upgrade_logs', 'it_exchange_delete_upgrade_logs' );

/**
 * Mark a filter as deprecated and inform when it has been used.
 *
 * @since 2.0.0
 *
 * @param string $filter      The Filter that was called.
 * @param string $version     The version of WordPress that deprecated the function.
 * @param string $replacement Optional. The function that should have been called. Default null.
 */
function it_exchange_deprecated_filter( $filter, $version, $replacement = null ) {

	/**
	 * Fires when a deprecated filter is called.
	 *
	 * @since 2.0.0
	 *
	 * @param string $filter    The function that was called.
	 * @param string $replacement The function that should have been called.
	 * @param string $version     The version of WordPress that deprecated the function.
	 */
	do_action( 'it_exchange_deprecated_filter_run', $filter, $replacement, $version );

	/**
	 * Filter whether to trigger an error for deprecated filters.
	 *
	 * @since 2.5.0
	 *
	 * @param bool $trigger Whether to trigger the error for deprecated functions. Default true.
	 */
	if ( WP_DEBUG && apply_filters( 'it_exchange_deprecated_filter_trigger_error', true ) ) {
		if ( function_exists( '__' ) ) {
			if ( ! is_null( $replacement ) )
				trigger_error( sprintf( __('The %1$s filter is <strong>deprecated</strong> since version %2$s! Use %3$s instead.'), $filter, $version, $replacement ) );
			else
				trigger_error( sprintf( __('The %1$s filter is <strong>deprecated</strong> since version %2$s with no alternative available.'), $filter, $version ) );
		} else {
			if ( ! is_null( $replacement ) )
				trigger_error( sprintf( 'The %1$s filter is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', $filter, $version, $replacement ) );
			else
				trigger_error( sprintf( 'The %1$s filter is <strong>deprecated</strong> since version %2$s with no alternative available.', $filter, $version ) );
		}
	}
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

	/** @var $IT_Exchange IT_Exchange */
	global $IT_Exchange;

	$info = array();

	$info['Site Info'] = array(
		'Site URL'  => site_url(),
		'Home URL'  => home_url(),
		'Multisite' => is_multisite() ? 'Yes' : 'No'
	);

	$wp_config =  array(
		'Version'       => get_bloginfo( 'version' ),
		'Language'      => defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US',
		'Permalink'     => get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default',
		'Theme'         => wp_get_theme()->Name . ' ' . wp_get_theme()->Version,
		'Show on Front' => get_option( 'show_on_front' )
	);

	if ( get_option( 'show_on_front' ) == 'page' ) {
		$front_page_id  = get_option( 'page_on_front' );
		$blog_page_id   = get_option( 'page_for_posts' );

		$wp_config['Page On Front'] = $front_page_id ? get_the_title( $front_page_id ) . " (#$front_page_id)" : 'Unset';
		$wp_config['Page For Posts'] = $blog_page_id ? get_the_title( $blog_page_id ) . " (#$blog_page_id)" : 'Unset';
	}

	$wp_config['ABSPATH']       = ABSPATH;
	$wp_config['Table Prefix']  = 'Length: ' . strlen( $wpdb->prefix ) . ' Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'Too long' : 'Acceptable' );
	$wp_config['WP_DEBUG']      = defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set';
	$wp_config['WP_DEBUG_LOG']  = defined( 'WP_DEBUG_LOG' ) ? WP_DEBUG_LOG ? 'Enabled' : 'Disabled' : 'Not set';
	$wp_config['SCRIPT_DEBUG']  = defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG ? 'Enabled' : 'Disabled' : 'Not set';
	$wp_config['Object Cache']  = wp_using_ext_object_cache() ? 'Yes' : 'No';
	$wp_config['Memory Limit']  = WP_MEMORY_LIMIT;
	$info['WordPress Configuration'] = $wp_config;

	$versions = get_option( 'it-exchange-versions' );
	$settings = it_exchange_get_option( 'settings_general' );

	$upgrader = it_exchange_make_upgrader();
	$completed = array();

	foreach ( $upgrader->get_upgrades() as $upgrade ) {
		if ( $upgrader->is_upgrade_completed( $upgrade ) ) {
			$completed[] = $upgrade->get_name();
		}
	}

	$addons = array();

	foreach ( it_exchange_get_enabled_addons() as $addon ) {
		$addons[] = $addon['name'];
	}

	$tables_installed = $tables_uninstalled = array();

	foreach ( it_exchange_get_tables() as $table ) {
		if ( \IronBound\DB\Manager::is_table_installed( $table ) ) {
			$tables_installed[] = $table->get_slug();
		} else {
			$tables_uninstalled[] = $table->get_slug();
		}
	}

	$info['iThemes Exchange'] = array(
		'Version'               => IT_Exchange::VERSION,
		'Previous'              => empty( $versions ) || empty( $versions['previous'] ) ? '' : $versions['previous'],
		'Currency Code'         => $settings['default-currency'],
		'Currency Symbol'       => it_exchange_get_currency_symbol( $settings['default-currency'] ),
		'Currency Position'     => ucfirst( $settings['currency-symbol-position'] ),
		'Thousands Separator'   => $settings['currency-thousands-separator'],
		'Decimals Separator'    => $settings['currency-decimals-separator'],
		'Registration'          => $settings['site-registration'] == 'it' ? 'Exchange' : 'WordPress',
		'Completed Upgrades'    => implode( ', ', $completed ),
		'Installed Tables'      => implode( ', ', $tables_installed ),
		'Missing Tables'        => $tables_uninstalled ? implode( ', ', $tables_uninstalled ) : 'None',
		'Add-ons'               => implode( ', ', $addons )
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
		'PHP Version'       => PHP_VERSION,
		'MySQL Version'     => $wpdb->db_version(),
		'Use MySQLi'        => $wpdb->use_mysqli ? 'Yes' : 'No',
		'Webserver Info'    => $_SERVER['SERVER_SOFTWARE'],
		'Host'              => it_exchange_get_host()
	);

	$info['PHP Configuration'] = array(
		'Safe Mode'             => ini_get( 'safe_mode' ) ? 'Enabled' : 'Disabled',
		'Memory Limit'          => ini_get( 'memory_limit' ),
		'Upload Max Size'       => ini_get( 'upload_max_filesize' ),
		'Post Max Size'         => ini_get( 'post_max_size' ),
		'Upload Max Filesize'   => ini_get( 'upload_max_filesize' ),
		'Time Limit'            => ini_get( 'max_execution_time' ),
		'Max Input Vars'        => ini_get( 'max_input_vars' ),
		'Display Errors'        => ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'
	);

	$info['PHP Extensions'] = array(
		'cURL'          => function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported',
		'fsockopen'     => function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported',
		'SOAP Client'   => class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed',
		'Suhosin'       => extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed'
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
	function rest_authorization_required_code() {
		return is_user_logged_in() ? 403 : 401;
	}
}