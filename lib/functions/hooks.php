<?php
/**
 * Hooks.
 *
 * @since   2.0.0
 * @license GPLv2
 */

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

		if ( ( 1 == $settings['enable-gallery-zoom'] ) ) {
			array_push( $script_deps, 'jquery-zoom' );
		}

		if ( ( 1 == $settings['enable-gallery-popup'] ) ) {
			array_push( $script_deps, 'jquery-colorbox' );
		}

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
	if ( it_exchange_is_page( 'checkout' ) ) {

		// Enqueue purchase dialog JS on checkout screen
		wp_enqueue_script( 'exchange-purchase-dialog', IT_Exchange::$url . '/lib/purchase-dialog/js/exchange-purchase-dialog.js',
			array( 'jquery', 'detect-credit-card-type', 'jquery.payment' ), false, true
		);

		// Register select to autocomplte
		wp_enqueue_style( 'it-exchange-autocomplete-style' );

		// General Checkout
		wp_enqueue_script( 'it-exchange-checkout-page', IT_Exchange::$url . '/lib/assets/js/checkout-page.js',
			array( 'it-exchange-event-manager', 'jquery', 'it-exchange-common', ), false, true
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
	if ( ! apply_filters( 'it_exchange_disable_frontend_stylesheet', false ) ) {
		wp_enqueue_style( 'it-exchange-public-css', IT_Exchange::$url . '/lib/assets/styles/exchange.css' );
	}

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
	wp_register_script( 'detect-credit-card-type', IT_Exchange::$url . '/lib/assets/js/detect-credit-card-type.js', array(
		'jquery',
		'jquery.payment'
	), false, true );

	// Detect CC Type
	wp_register_script( 'it-exchange-event-manager', IT_Exchange::$url . '/lib/assets/js/event-manager.js', array(), false, true );

	wp_register_script( 'jquery.payment', IT_Exchange::$url . '/lib/assets/js/jquery.payment.min.js', array( 'jquery' ), '1.3.2', true );
	wp_register_script( 'backbonedeep', IT_Exchange::$url . '/lib/admin/js/backbone.modeldeep.min.js', array( 'backbone' ), '2.0.1', true );
	wp_register_script( 'backbone.paginator', IT_Exchange::$url . '/lib/admin/js/backbone.paginator.min.js', array( 'backbone' ), '2.0.5', true );
	wp_register_script( 'backbone.fetch-cache', IT_Exchange::$url . '/lib/admin/js/backbone.fetch-cache.min.js', array( 'backbone' ), '2.0.1', true );
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
		'dateFormat'   => it_exchange_convert_php_to_moment( get_option( 'date_format' ) ),
		'timeFormat'   => it_exchange_convert_php_to_moment( get_option( 'time_format' ) ),
		'symbol'       => $currency,
		'symbolPos'    => $settings['currency-symbol-position'],
		'decimals'     => 2,
		'thousandsSep' => $settings['currency-thousands-separator'],
		'decimalsSep'  => $settings['currency-decimals-separator'],
		'restNonce'    => wp_create_nonce( 'wp_rest' ),
		'restUrl'      => rest_url( 'it_exchange/v1/' ),
		'wpRestUrl'    => rest_url( 'wp/v2' ),
		'currentUser'  => (int) get_current_user_id(),
		'baseCountry'  => $settings['company-base-country'],
		'i18n'         => array(
			'unknownError' => __( 'An unknown error occurred.', 'it-l10n-ithemes-exchange' ),
		)
	) );

	$rest_libs = array(
		'backbone',
		'underscore',
		'it-exchange-common',
		'wp-util',
		'ithemes-momentjs',
		'backbonedeep',
		'wp-backbone',
		'backbone.paginator',
		'backbone.fetch-cache',
		'it-exchange-event-manager'
	);
	wp_register_script( 'it-exchange-rest', IT_Exchange::$url . '/lib/assets/js/rest.js', $rest_libs, IT_Exchange::VERSION );

	$config = array(
		'i18n'      => array(
			'visualCC'     => array(
				'name'   => _x( 'Name', 'Credit Card Holder Name', 'it-l10n-ithemes-exchange' ),
				'number' => _x( 'Number', 'Credit Card Number', 'it-l10n-ithemes-exchange' ),
			),
			'checkout'     => array(
				'completePurchase'   => __( 'Complete Purchase', 'it-l10n-ithemes-exchange' ),
				'purchased'          => __( 'Purchased!', 'it-l10n-ithemes-exchange' ),
				'cancel'             => __( 'Cancel', 'it-l10n-ithemes-exchange' ),
				'haveCoupon'         => __( 'Have a coupon?', 'it-10n-ithemes-exchange' ),
				'addCoupon'          => _x( 'Add', 'Add coupon', 'it-l10n-ithemes-exchange' ),
				'couponCode'         => __( 'Coupon Code', 'it-l10n-ithemes-exchange' ),
				'otherPaymentMethod' => __( 'Use other payment method', 'it-l10n-ithemes-exchange' ),
				'paymentSummary'     => __( 'Payment Summary', 'it-l10n-ithemes-exchange' ),
				'total'              => __( 'Total', 'it-l10n-ithemes-exchange' ),
				'date'               => __( 'Date', 'it-l10n-ithemes-exchange' ),
				'method'             => __( 'Method', 'it-l10n-ithemes-exchange' ),
				'order'              => __( 'Order', 'it-l10n-ithemes-exchange' ),
				'viewDetails'        => __( 'View Details', 'it-l10n-ithemes-exchange' ),
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
			'address'      => array(
				'save'      => __( 'Save', 'it-l10n-ithemes-exchange' ),
				'cancel'    => __( 'Cancel', 'it-l10n-ithemes-exchange' ),
				'firstName' => __( 'First Name', 'it-l10n-ithemes-exchange' ),
				'lastName'  => __( 'Last Name', 'it-l10n-ithemes-exchange' ),
				'address1'  => __( 'Address 1', 'it-l10n-ithemes-exchange' ),
				'address2'  => __( 'Address 2', 'it-l10n-ithemes-exchange' ),
				'country'   => __( 'Country', 'it-l10n-ithemes-exchange' ),
				'city'      => __( 'City', 'it-l10n-ithemes-exchange' ),
				'zip'       => __( 'Zip', 'it-l10n-ithemes-exchange' ),
				'state'     => __( 'State', 'it-l10n-ithemes-exchange' ),
				'label'     => __( 'Label', 'it-l10n-ithemes-exchange' ),
			),
		),
		'imageRoot' => IT_Exchange::$url . '/lib/assets/styles/images/'
	);

	if ( apply_filters( 'it_exchange_preload_cart_item_types', it_exchange_is_page( 'checkout' ) ) ) {
		$serializer = new \iThemes\Exchange\REST\Route\v1\Cart\TypeSerializer();

		foreach ( ITE_Line_Item_Types::shows_in_rest() as $type ) {
			$config['cartItemTypes'][] = $serializer->serialize( $type );
		}
	}

	wp_localize_script( 'it-exchange-rest', 'ITExchangeRESTConfig', $config );

	$js_tokenizers = array();

	foreach ( ITE_Gateways::handles( 'tokenize' ) as $gateway ) {
		$handler = $gateway->get_handler_by_request_name( 'tokenize' ) ?: $gateway->get_handler_by_request_name( 'purchase' );

		if ( ! $handler instanceof ITE_Gateway_JS_Tokenize_Handler || ! $handler->is_js_tokenizer_configured() ) {
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
	 * @param $preload            bool|array True to preload all schemas. False to preload no schemas. An array of schema
	 *                            document titles to only preload a selected amount of schemas.
	 */
	$preload = apply_filters( 'it_exchange_preload_schemas', $preload );

	if ( $preload === false || $preload === null ) {
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
	$child_theme_functions  = get_stylesheet_directory() . '/exchange/functions.php';

	// Parent theme
	if ( is_file( $parent_theme_functions ) ) {
		include_once( $parent_theme_functions );
	}

	// Child theme or primary theme if not parent
	if ( is_file( $child_theme_functions ) ) {
		include_once( $child_theme_functions );
	}
}

add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_load_theme_functions_for_exchange' );

/**
 * Hook for processing webhooks from services like PayPal IPN, Stripe, etc.
 *
 * @since 0.4.0
 */
function it_exchange_process_webhooks() {

	if ( is_admin() ) {
		return;
	}

	if ( function_exists( 'wp_doing_ajax' ) ) {
		if ( wp_doing_ajax() ) {
			return;
		}
	} elseif ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		return;
	}

	$doing = it_exchange_doing_webhook();

	if ( ! $doing ) {
		return;
	}

	$param = it_exchange_get_webhook( $doing );

	try {
		it_exchange_set_time_limit();
		do_action( 'it_exchange_webhook_' . $param, $_REQUEST );
	} catch ( IT_Exchange_Locking_Exception $e ) {
		status_header( 500 );
		it_exchange_log( 'Unable to acquire while processing {gateway} webhook: {exception}', ITE_Log_Levels::WARNING, array(
		  'exception' => $e,
          'gateway'   => $doing,
          '_group'    => 'webhook',
        ) );
		die();
	}

	die();
}

add_action( 'wp', 'it_exchange_process_webhooks' );
add_filter( 'pre_handle_404', 'it_exchange_doing_webhook' );

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

	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG || ! current_user_can( 'administrator' ) ) {
		return;
	}

	// Never check this by default.
	$form->set_option( 'reset-exchange', 0 );
	?>
    <tr valign="top">
        <th scope="row"><strong><?php _e( 'Dangerous Settings', 'it-l10n-ithemes-exchange' ); ?></strong></th>
        <td></td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="reset-exchange"><?php _e( 'Reset Exchange', 'it-l10n-ithemes-exchange' ) ?></label>
        </th>
        <td>
			<?php $form->add_check_box( 'reset-exchange' ); ?>
            <label for="reset-exchange"><?php _e( 'Reset ALL data', 'it-l10n-ithemes-exchange' ) ?></label><br/>
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
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
		return;
	}

	// Don't do anything if we're not on the settings page
	if ( empty( $GLOBALS['pagenow'] ) || 'admin.php' != $GLOBALS['pagenow'] || empty( $_GET['page'] ) || 'it-exchange-settings' != $_GET['page'] ) {
		return;
	}

	// Don't do anything if the nonce doesn't validate
	$nonce = empty( $_POST['_wpnonce'] ) ? false : $_POST['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'exchange-general-settings' ) ) {
		return;
	}

	// Don't do anything if the checkbox wasnt' checked
	$data = ITForm::get_post_data();
	if ( empty( $data['reset-exchange'] ) ) {
		return;
	}

	// Use Post stati rather than 'any' for post type to include trashed and other non-searchable stati
	$stati = array_keys( get_post_stati() );

	// Delete all Products
	if ( ! apply_filters( 'it_exchange_preserve_products_on_reset', false ) ) {
		while ( $products = it_exchange_get_products( array( 'posts_per_page' => 20, 'post_status' => $stati ) ) ) {
			foreach ( $products as $product ) {
				wp_delete_post( $product->ID, true );
			}
		}
	}
	// Delete all Transactions
	if ( ! apply_filters( 'it_exchange_preserve_transactions_on_reset', false ) ) {
		while ( $transactions = it_exchange_get_transactions( array(
			'posts_per_page' => 20,
			'post_status'    => $stati
		) ) ) {
			foreach ( $transactions as $transaction ) {
				wp_delete_post( $transaction->ID, true );
			}
		}
	}
	// Delete all Coupons
	if ( ! apply_filters( 'it_exchange_preserve_coupons_on_reset', false ) ) {
		while ( $coupons = it_exchange_get_coupons( array( 'posts_per_page' => 20, 'post_status' => $stati ) ) ) {
			foreach ( $coupons as $coupon ) {
				wp_delete_post( $coupon->ID, true );
			}
		}
	}
	// Delete all Downloads (post types, not files uploaded to WP Media Library)
	if ( ! apply_filters( 'it_exchange_preserve_products_on_reset', false ) ) {
		while ( $downloads = get_posts( array( 'post_type' => 'it_exchange_download', 'post_status' => $stati ) ) ) {
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
	foreach ( $settings_keys as $option ) {
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
		                   ' ' . __( 'You can turn off registration and allow guest checkouts in Exchange / Add-ons / Digital Downloads Settings.', 'it-l10n-ithemes-exchange' ),
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
	$atts     = shortcode_atts( $defaults, $atts );

	// Don't return anything if page type is not WordPress
	if ( 'wordpress' != it_exchange_get_page_type( $atts['page'] ) ) {
		return '';
	}

	if ( empty( $atts['page'] ) ) {
		return false;
	}

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
		'show'        => false,
		'avatar_size' => 128,
	);
	$atts     = shortcode_atts( $defaults, $atts );

	$whitelist = array(
		'first-name',
		'last-name',
		'username',
		'email',
		'avatar',
		'site-name',
	);
	$whitelist = apply_filters( 'it_exchange_customer_shortcode_tag_list', $whitelist );

	if ( empty( $atts['show'] ) || ! in_array( $atts['show'], (array) $whitelist ) ) {
		return '';
	}

	$options = array(
		'format' => 'field-value',
	);
	if ( 'avatar' == $atts['show'] ) {
		$options['size'] = $atts['avatar_size'];
	}

	$output = it_exchange( 'customer', 'get-' . $atts['show'], $options );

	if ( empty( $output ) ) {
		//fallbacks if we have empty $output
		switch ( $atts['show'] ) {
			case 'first-name':
				$output = it_exchange( 'customer', 'get-username', array( 'format' => 'field-value' ) );
				break;
		}
	}

	return $output;
}

add_shortcode( 'it_exchange_customer', 'it_exchange_add_customer_shortcode' );

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
	if ( apply_filters( 'it_exchange_billing_address_purchase_requirement_enabled', false ) ) {
		it_exchange_register_purchase_requirement( 'billing-address', $properties );
	}
}

add_action( 'init', 'it_exchange_register_default_purchase_requirements' );

/**
 * The default checkout mode for the page
 *
 * @since 1.6.0
 *
 * @return string
 */
function it_exchange_get_default_content_checkout_mode() {
	$settings     = it_exchange_get_option( 'settings_general' );
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
	foreach ( (array) it_exchange_get_purchase_requirements() as $slug => $properties ) {
		$sw_template = empty( $properties['sw-template-part'] ) ? false : $properties['sw-template-part'];
		if ( empty( $existing[ $sw_template ] ) ) {
			$existing[] = $sw_template;
		}
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
	if ( false === ( $notification = it_exchange_get_next_purchase_requirement_property( 'notification' ) ) ) {
		return;
	}

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
	if ( false === ( $message = it_exchange_get_next_purchase_requirement_property( 'notification' ) ) ) {
		return $elements;
	}

	// Locate the transaction-methods key in elements array (if it exists)
	$index = array_search( 'transaction-methods', $elements );
	if ( false === $index ) {
		return $elements;
	}

	// Remove transaction-methods
	unset( $elements[ $index ] );

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
	if ( ! apply_filters( 'it_exchange_billing_address_purchase_requirement_enabled', false ) ) {
		return $loops;
	}

	// Set index to end of array.
	$index = array_search( 'discounts', $loops );
	$index = ( false === $index ) ? array_search( 'totals-taxes', $loops ) : $index;
	$index = ( false === $index ) ? count( $loops ) - 1 : $index;

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
	if ( empty( $_POST['ite_action_ajax'] ) || 'ite-country-states-update' != $_POST['ite_action_ajax'] ) {
		return;
	}

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
 * This flushes the rewrite rules for us on activation
 *
 * @since 0.4.0
 *
 * @return void
 */
function it_exchange_flush_rewrite_rules() {
	if ( get_option( '_it-exchange-flush-rewrites', false ) ) {
		delete_option( '_it-exchange-flush-rewrites' );
		it_exchange_get_pages( true );
		flush_rewrite_rules();

		it_exchange_log( 'Flushed rewrite rules', ITE_Log_Levels::INFO );
	}
}

add_action( 'admin_init', 'it_exchange_flush_rewrite_rules', 99 );

/**
 * Force rewrite rule update on upgrade
 *
 * @since 1.4.0
 *
 * @return void
 */
function it_exchange_force_rewrite_flush_on_upgrade() {
	add_option( '_it-exchange-flush-rewrites', true );
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
 * Drop cached cart and active cart user meta upon upgrading to v2.
 *
 * @since 2.0.0
 *
 * @param array $versions
 */
function it_exchange_delete_cached_cart_user_meta_on_upgrade( $versions ) {

	if ( version_compare( '2.0.0', $versions['previous'], '>=' ) ) {
		return;
	}

	delete_metadata( 'user', 0, '_it_exchange_active_user_carts', '', true );
	delete_metadata( 'user', 0, '_it_exchange_cached_cart', '', true );
}

add_action( 'it_exchange_version_updated', 'it_exchange_delete_cached_cart_user_meta_on_upgrade' );

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
 * Log in the user from account/log-in if in compatibility mode.
 *
 * @since 2.0.0
 */
function it_exchange_process_compatibility_mode_login() {

	if ( ! apply_filters( 'it_exchange_login_page_use_compatibility_mode', true ) ) {
		return;
	}

	if ( ! isset( $_GET['it_exchange_login'] ) ) {
	    return;
    }

	if ( isset( $_POST['pwd'], $_POST['log'] ) ) {
		$errors = wp_signon();

		if ( is_wp_error( $errors ) ) {
			foreach ( $errors->get_error_messages() as $message ) {
				it_exchange_add_message( 'error', $message );
			}
		} else {
			if ( isset( $_REQUEST['redirect_to'] ) ) {
				wp_safe_redirect( $_REQUEST['redirect_to'] );
			} else {
				wp_redirect( it_exchange_get_page_url( 'account' ) );
			}

			die();
		}
	}

}

add_action( 'init', 'it_exchange_process_compatibility_mode_login' );

/**
 * Redirects to Exchange Login page if login fails
 *
 * Technically, we're hijacking a filter to use it for an action.
 *
 * @since 1.6.0
 *
 * @param  WP_Error $error instance of WP_Error
 *
 * @return mixed
 */
function it_exchange_redirect_to_correct_login_form_on_error( $error ) {
	if ( empty( $error ) || ! is_wp_error( $error ) || ( empty( $error->errors ) && empty( $_POST ) ) ) {
		return $error;
	}

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
 * Blocks access to Download iThemes Exchange attachments
 *
 * @since 1.7.18
 *
 * @return void
 */
function it_exchange_block_attachments() {
	if ( ! is_attachment() ) {
		return;
	}

	$uri = wp_get_attachment_url( get_the_ID() );

	$args    = array(
		'post_type'  => 'it_exchange_download',
		'meta_query' => array(
			array(
				'key'     => '_it-exchange-download-info',
				'value'   => $uri,
				'compare' => 'LIKE',
			)
		),
	);
	$results = get_posts( $args );

	if ( empty( $results ) ) {
		return;
	}

	wp_die(
		__( 'You do not have permission to view this file.', 'it-l10n-ithemes-exchange' ),
		__( 'Error', 'it-l10n-ithemes-exchange' ),
		array( 'response' => 403, 'back_link' => true )
	);
}

add_action( 'template_redirect', 'it_exchange_block_attachments' );

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
		$text             = _n( '%s Product', '%s Products', $product_counts->publish );
		$text             = sprintf( $text, number_format_i18n( $product_counts->publish ) );
		$post_type_object = get_post_type_object( 'it_exchange_prod' );

		if ( $post_type_object && current_user_can( $post_type_object->cap->edit_posts ) ) {
			$elements[] = sprintf( '<a class="it-exchange-glance-products" href="edit.php?post_type=%1$s">%2$s</a>', 'it_exchange_prod', $text );
		} else {
			$elements[] = sprintf( '<span class="it-exchange-glance-products">%2$s</span>', 'it_exchange_prod', $text );
		}
	}

	$txn_counts = wp_count_posts( 'it_exchange_tran' );

	if ( $txn_counts && $txn_counts->publish ) {
		$text             = _n( '%s Transaction', '%s Transactions', $txn_counts->publish );
		$text             = sprintf( $text, number_format_i18n( $txn_counts->publish ) );
		$post_type_object = get_post_type_object( 'it_exchange_tran' );

		if ( $post_type_object && current_user_can( $post_type_object->cap->edit_posts ) ) {
			$elements[] = sprintf( '<a class="it-exchange-glance-transactions" href="edit.php?post_type=%1$s">%2$s</a>', 'it_exchange_tran', $text );
		} else {
			$elements[] = sprintf( '<span class="it-exchange-glance-transactions">%2$s</span>', 'it_exchange_tran', $text );
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
	if ( $show_nag ) {
		$show_nag = ( true == get_user_meta( $current_user->ID, '_it_exchange_dismiss_sync_nag', true ) ) ? false : true;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		$show_nag = false;
	}

	if ( ! empty( $show_nag ) && ! empty( $_GET ) ) {
		$more_info_url = 'http://ithemes.com/2014/06/24/track-sales-sync-new-ithemes-exchange-integration/';
		$dismiss_url   = add_query_arg( array( 'it-exchange-dismiss-sync-integration-nag' => 1 ) ); // escaped before printed
		include( dirname( dirname( __FILE__ ) ) . '/admin/views/notices/ithemes-sync-integration.php' );
	}
}

add_action( 'admin_notices', 'it_exchange_show_ithemes_sync_integration_nag' );

/**
 * Before a payment gateway is disabled, add a notice recommending the gateway not be disabled.
 *
 * @param string $add_on
 */
function it_exchange_add_on_before_disable_payment_gateways( $add_on ) {

	if ( empty( $_GET['page'] ) || 'it-exchange-setup' === $_GET['page'] ) {
		return;
	}

	if ( ! empty( $_GET['remove-gateway'] ) && 'yes' === $_GET['remove-gateway'] ) {
		return;
	}

	$message = __( 'Deactivating a payment gateway can cause customers to lose access to any membership products they have purchased using this payment gateway.', 'LION' );
	$message .= ' ' . __( 'Are you sure you want to proceed? %s | %s', 'LION' );

	switch ( $add_on ) {
		case 'offline-payments':
		case 'paypal-standard':
		case 'paypal-standard-secure':
		case 'zero-sum-checkout':
			$title   = __( 'Payment Gateway Warning', 'LION' );
			$yes     = '<a href="' . esc_url( add_query_arg( 'remove-gateway', 'yes' ) ) . '">' . __( 'Yes', 'LION' ) . '</a>';
			$no      = '<a href="javascript:history.back()">' . __( 'No', 'LION' ) . '</a>';
			$message = '<p>' . sprintf( $message, $yes, $no ) . '</p>';
			$args    = array(
				'response'  => 200,
				'back_link' => false,
			);
			wp_die( $message, $title, $args );
	}
}

add_action( 'it_exchange_add_on_before_disable', 'it_exchange_add_on_before_disable_payment_gateways' );

// Commit session changes at the end of the request.
add_action( 'shutdown', 'it_exchange_commit_session' );

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

		$now              = time();
		$expired_sessions = array();

		foreach ( $expiration_keys as $expiration ) {
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
 * Enqueue Account -> Profile JS.
 *
 * @since 2.0.0
 */
function it_exchange_enqueue_profile_js() {

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

	$address_serializer  = new \iThemes\Exchange\REST\Route\v1\Customer\Address\Serializer();
	$customer_serializer = new \iThemes\Exchange\REST\Route\v1\Customer\Serializer();

	$token_serializer = new \iThemes\Exchange\REST\Route\v1\Customer\Token\Serializer();
	$token_schema     = $token_serializer->get_schema();

	$tokens = array();

	foreach ( $customer->get_tokens( array( 'status' => 'all' ) ) as $token ) {
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

add_action( 'wp_enqueue_scripts', 'it_exchange_enqueue_profile_js' );

/**
 * Print the manage tokens and addresses container.
 *
 * @since 2.0.0
 */
function it_exchange_print_profile_js_container() {
	echo '<div class="it-exchange-customer-addresses-container"></div>';
	echo '<div class="it-exchange-manage-tokens-container"></div>';
}

add_action( 'it_exchange_content_profile_after_form', 'it_exchange_print_profile_js_container' );

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