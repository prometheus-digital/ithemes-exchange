<?php
/*
 * Plugin Name: iThemes Exchange
 * Version: 0.4.12
 * Description: Turns your WordPress site into a Lean, Mean Selling Machine!
 * Plugin URI: http://ithemes.com/purchase/ithemes-exchange/
 * Author: iThemes
 * Author URI: http://ithemes.com
 * iThemes Package: ithemes-exchange
 
 * Installation:
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 *
*/

/**
 * Exchange main class.
 *
 * @package IT_Exchange
 * @since 0.1.0
*/
class IT_Exchange {

	var $_version         = '0.4.12';
	var $_wp_minimum      = '3.5';
	var $_slug            = 'it-exchange';
	var $_name            = 'iThemes Exchange';
	var $_series          = '';

	var $_plugin_path     = '';
	var $_plugin_url      = '';
	var $_plugin_base     = '';

	/**
	 * Setup the plugin
	 *
	 * Class Constructor. Sets up the environment and then loads admin or enqueues active bar
	 *
	 * @uses IT_Exchange::set_plugin_locations()
	 * @uses IT_Exchange::set_textdomain()
	 * @uses IT_Exchange::init_exchange()
	 * @since 0.1.0
	 * @return void
	*/
	function IT_Exchange() {
		// Setup Plugin
		$this->set_plugin_locations();
		$this->set_textdomain();

		// Load supporting libraries
		require( $this->_plugin_path . 'lib/load.php' );
		require( $this->_plugin_path . 'api/load.php' );
		require( $this->_plugin_path . 'core-addons/load.php' );

		do_action( 'it_exchange_loaded' );
		add_action( 'it_libraries_loaded', array( $this, 'addons_init' ) );
	}

	/**
	 * Defines where the plugin lives on the server
	 *
	 * @uses WP_PLUGIN_DIR
	 * @uses ABSPATH
	 * @uses site_url()
	 * @since 0.1.0
	 * @return void
	*/
	function set_plugin_locations() {
		$this->_plugin_path = plugin_dir_path( __FILE__ );
		$this->_plugin_url  = plugins_url( '', __FILE__ );
		$this->_plugin_base = plugin_basename( __FILE__  );
	}

	/**
	 * Loads the translation data for WordPress
	 *
	 * @uses load_plugin_textdomain()
	 * @since 0.1.0
	 * @return void
	*/
	function set_textdomain() {
		load_plugin_textdomain( 'LION', false, dirname( $this->_plugin_base ) . '/lang/' );
	}

	/**
	 * Includes files for enabled add-ons
	 *
	 * @since 0.3.1
	 * @return void
	*/
	function addons_init() {
		$registered = it_exchange_get_addons();

		// Auto enable all 3rd party addons
		foreach( $registered as $slug => $params ) {
			if ( ! it_exchange_is_core_addon( $slug ) )
				it_exchange_enable_addon( $slug );
		}

		// Init all enabled addons
		if ( $addons = it_exchange_get_enabled_addons() ) {
			foreach( (array) $addons as $slug => $params ) {
				if ( ! empty( $params['file'] ) && is_file( $params['file'] ) ) {
					include( $params['file'] );
				} else {
					it_exchange_disable_addon( $slug );
					if ( is_admin() ) {
						wp_safe_redirect('admin.php?page=it-exchange-addons&message=addon-auto-disabled-' . $addon );
						die();
					}
				}
			}
		}
		do_action( 'it_exchange_enabled_addons_loaded' );
	}
}

/**
 * Loads Exchange after plugins have been enabled
 *
 * @since 0.4.0
 *
 * @return void
*/
function load_it_exchange() {	
	// Init plugin
	$IT_Exchange = new IT_Exchange();
}
add_action( 'plugins_loaded', 'load_it_exchange' );

/**
 * Sets up options to perform after activation
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_activation_hook() {
    add_option('_it-exchange-register-activation-hook', true);
    add_option('_it-exchange-flush-rewrites', true);
}
register_activation_hook( __FILE__, 'it_exchange_activation_hook' );

/**
 * Redirect users to the IT Exchange Setup page upon activation.
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_register_activation_hook() {
	if ( false !== get_option( '_it-exchange-register-activation-hook', false ) ) {
        delete_option('_it-exchange-register-activation-hook');
		wp_safe_redirect('admin.php?page=it-exchange-setup' );
    }
}
add_action( 'admin_init', 'it_exchange_register_activation_hook' );

/**
 * This flushes the rewrite rules for us on activation
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_flush_rewrite_rules() {
	if ( false !== get_option( '_it-exchange-flush-rewrites', false ) ) {
		delete_option( '_it-exchange-flush-rewrites' );
		flush_rewrite_rules();
	}
}
add_action( 'admin_init', 'it_exchange_flush_rewrite_rules', 99 );

/**
 * Register Exchange with the iThemes Updater
 *
 * @param object $updater instance of the updater object
 * @return void
*/
function ithemes_ithemes_exchange_updater_register( $updater ) { 
	$updater->register( 'ithemes-exchange', __FILE__ );
}
add_action( 'ithemes_updater_register', 'ithemes_ithemes_exchange_updater_register' );
require( dirname( __FILE__ ) . '/lib/updater/load.php' );
require( plugin_dir_path( __FILE__ ) . 'lib/sessions/class.session.php' );
