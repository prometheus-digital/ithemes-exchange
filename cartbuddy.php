<?php
/*
 * Plugin Name: Cart Buddy by iThemes
 * Version: 0.2
 * Description: Turns your WordPress site into a Lean, Mean Selling Machine!
 * Plugin URI: http://ithemes.com/purchase/cartbuddy/
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
 * CartBuddy main class.
 *
 * @package IT_CartBuddy
 * @since 0.1
*/
if ( ! class_exists( 'IT_Cart_Buddy' ) ) {

	class IT_Cart_Buddy {

		var $_version         = '0.2';
		var $_updater         = '1.0.8';
		var $_wp_minimum      = '3.5';
		var $_slug            = 'cart_buddy';
		var $_name            = 'Cart Buddy';
		var $_series          = '';

		var $_plugin_path     = '';
		var $_plugin_rel_path = '';
		var $_plugin_url      = '';
		var $_self_link       = '';
		var $_plugin_base     = '';

		/**
		 * Setup the plugin
		 *
		 * Class Constructor. Sets up the environment and then loads admin or enqueues active bar
		 *
		 * @uses IT_CartBuddy::set_plugin_locations()
		 * @uses IT_CartBuddy::set_textdomain()
		 * @uses IT_CartBuddy::init_cartbuddy()
		 * @since 0.1
		 * @return void
		*/
		function IT_Cart_Buddy() {
			// Setup Plugin
			$this->set_plugin_locations();
			$this->set_textdomain();

            // Load supporting libraries
            require( $this->_plugin_path . '/lib/classes/load.php' );
			require( $this->_plugin_path . '/lib/framework/load.php' );
			require( $this->_plugin_path . '/api/load.php' );
			require( $this->_plugin_path . '/core-addons/load.php' );
		}

		/**
		 * Defines where the plugin lives on the server
		 *
		 * @uses WP_PLUGIN_DIR
		 * @uses ABSPATH
		 * @uses site_url()
		 * @since 0.1
		 * @return void
		*/
		function set_plugin_locations() {
			$this->_plugin_path          = WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) );
			$this->_plugin_relative_path = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_plugin_path ) ), '\\\/' );
			$this->_plugin_url           = site_url() . '/' . $this->_plugin_relative_path;

			// Adjust URL for HTTPS if needed
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' )
				$this->_plugin_url = str_replace( 'http://', 'https://', $this->_plugin_url );

			$this->_self_link   = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_slug;
			$this->_plugin_base = plugin_basename( __FILE__  );
		}

		/**
		 * Loads the translation data for WordPress
		 *
		 * @uses load_plugin_textdomain()
		 * @since 0.1
		 * @return void
		*/
		function set_textdomain() {
			load_plugin_textdomain( 'LION', false, dirname( $this->_plugin_base ) . '/lang/' );
		}

		/**
		 * This function registers the plugin's vesion of the iThemes updater class
		 *
		 * @since 0.1
		 * @return void
		*/
		function upgrader_register() {
			$GLOBALS['pb_classes_upgrade_registration_list'][$this->_slug] = $this->_updater;
		}

		/**
		 * Look through all registered version of upgrade classes and use the latest version
		 *
		 * @since 0.1
		 * @return void
		*/
		function upgrader_select() {
			if ( ! isset( $GLOBALS[ 'pb_classes_upgrade_registration_list' ] ) ) {
				//Fallback - Just include this class
				require( $this->_plugin_path . '/lib/updater/updater.php' );
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
				require( $this->_plugin_path . '/lib/updater/updater.php' );
			}
		}

		/**
		 * Initiates our upgrade class
		 *
		 * @since 0.1
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
				'plugin_path' => plugin_basename( __FILE__ ),
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
}

// Init plugin
$IT_Cart_Buddy = new IT_Cart_Buddy();
