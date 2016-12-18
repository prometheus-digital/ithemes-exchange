<?php
/*
 * Plugin Name: iThemes Exchange
 * Version: 2.0.0
 * Text Domain: it-l10n-ithemes-exchange
 * Description: Easily sell your digital goods with iThemes Exchange, simple ecommerce for WordPress
 * Plugin URI: http://ithemes.com/exchange/
 * Author: iThemes
 * Author URI: http://ithemes.com
 *
 * Installation:
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 *
*/

/**
 * Whether to load deprecated code.
 *
 * @since 2.0.0
 *
 * @return bool
 */
function it_exchange_load_deprecated() {
	return version_compare( PHP_VERSION, '5.3', '<' ) || get_option( 'it_exchange_load_deprecated', false ) ||
	       ( defined( 'IT_EXCHANGE_LOAD_DEPRECATED' ) && IT_EXCHANGE_LOAD_DEPRECATED );
}

if ( it_exchange_load_deprecated() ) {
	require_once dirname( __FILE__ ) . '/deprecated/init.php';
} else {
	require_once dirname( __FILE__ ) . '/ithemes-exchange.php';
}