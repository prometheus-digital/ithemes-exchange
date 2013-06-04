<?php
/*
 * Plugin Name: iThemes Exchange
 * Version: 0.3.11
 * Description: Turns your WordPress site into a Lean, Mean Selling Machine!
 * Plugin URI: http://ithemes.com/purchase/ithemes-exchange/
 * Author: iThemes
 * Author URI: http://ithemes.com
 
 * Installation:
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 *
*/

require( plugin_dir_path( __FILE__ ) . 'lib/sessions/class.session.php' );

/**
 * Exchange main class.
 *
 * @package IT_Exchange
 * @since 0.1.0
*/
class IT_Exchange {

	var $_version         = '0.3.10';
	var $_updater         = '1.0.8';
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

	/**
	 * This function registers the plugin's vesion of the iThemes updater class
	 *
	 * @since 0.1.0
	 * @return void
	*/
	function upgrader_register() {
		$GLOBALS['pb_classes_upgrade_registration_list'][$this->_slug] = $this->_updater;
	}

	/**
	 * Look through all registered version of upgrade classes and use the latest version
	 *
	 * @since 0.1.0
	 * @return void
	*/
	function upgrader_select() {
		if ( ! isset( $GLOBALS[ 'pb_classes_upgrade_registration_list' ] ) ) {
			//Fallback - Just include this class
			require( $this->_plugin_path . 'lib/updater/updater.php' );
			return;
		}

		//Go through each global and find the highest updater version and the plugin slug
		$updater_version = 0;
		$plugin_slug = '';
		foreach ( $GLOBALS['pb_classes_upgrade_registration_list'] as $var => $version ) {
			if ( version_compare( $version, $updater_version, '>=' ) ) {
				$updater_version = $version;
				$plugin_slug = $var;
			}
		}

		//If the slugs match, load this version
		if ( $this->_slug == $plugin_slug ) {
			require( $this->_plugin_path . 'lib/updater/updater.php' );
		}
	}

	/**
	 * Initiates our upgrade class
	 *
	 * @since 0.1.0
	 * @return void
	*/
	function upgrader_instantiate() {
		
		$pb_product = strtolower( $this->_slug );
		$pb_product = str_replace( 'ithemes-', '', $pb_product );
		$pb_product = str_replace( 'pluginbuddy-', '', $pb_product );
		$pb_product = str_replace( 'pluginbuddy_', '', $pb_product );
		$pb_product = str_replace( 'pb_thumbsup', '', $pb_product );
		$pb_product = str_replace( 'it_', '', $pb_product );
		$pb_product = str_replace( 'it-', '', $pb_product );
		
		$args = array(
			'parent' => $this, 
			'remote_url' => 'http://updater2.ithemes.com/index.php',
			'version' => $this->_version,
			'plugin_slug' => $this->_slug,
			'plugin_path' => $this->_plugin_base,
			'plugin_url' => $this->_plugin_url,
			'product' => $pb_product,
			'time' => 43200,
			'return_format' => 'json',
			'method' => 'POST',
			'upgrade_action' => 'check'
		);

		$this->_pluginbuddy_upgrader = new iThemesPluginUpgrade( $args );
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
