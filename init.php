<?php
/*
 * Plugin Name: ExchangeWP
 * Version: 1.36.6
 * Text Domain: it-l10n-ithemes-exchange
 * Description: Easily sell your digital goods with ExchangeWP, simple ecommerce for WordPress
 * Plugin URI: https://exchangewp.com
 * Author: ExchangeWP
 * Author URI: https://exchangewp.com
 *
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

	var $_version         = '1.36.6';
	var $_wp_minimum      = '3.5';
	var $_slug            = 'ithemes-exchange';
	var $_name            = 'ExchangeWP';
	var $_series          = '';

	var $_plugin_path     = '';
	var $_plugin_url      = '';
	var $_plugin_base     = '';

	/**
	 * Setup the plugin
	 *
	 * Class Constructor. Sets up the environment and then loads admin or enqueues active bar.
	 *
	 * @uses IT_Exchange::set_plugin_locations()
	 * @uses IT_Exchange::set_textdomain()
	 * @uses IT_Exchange::init_exchange()
	 * @since 0.1.0
	*/
	function __construct() {
		// Setup Plugin
		$this->set_plugin_locations();
		$this->set_textdomain();

		if ( file_exists( $this->_plugin_path . 'vendor/autoload.php' ) ) {
			include( $this->_plugin_path . 'vendor/autoload.php' );
		}

		// Load supporting libraries
		require( $this->_plugin_path . 'lib/load.php' );
		require( $this->_plugin_path . 'api/load.php' );
		require( $this->_plugin_path . 'core-addons/load.php' );

		// Set version
		$GLOBALS['it_exchange']['version'] = $this->_version;
		if ( is_admin() ) {
			$versions         = get_option( 'it-exchange-versions', false );
			$current_version  = empty( $versions['current'] ) ? false: $versions['current'];
			$previous_version = empty( $versions['previous'] ) ? false: $versions['previous'];
			if ( $this->_version !== $current_version ) {
				$versions = array(
					'current'  => $this->_version,
					'previous' => $current_version,
				);
				update_option( 'it-exchange-versions', $versions );
				do_action( 'it_exchange_version_updated', $versions );
			}
		}

		do_action( 'it_exchange_loaded' );
		add_action( 'it_libraries_loaded', array( $this, 'addons_init' ) );
		add_action( 'it_libraries_loaded', array( $this, 'remove_dev_dirs' ) );
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Exchange() {
		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Defines where the plugin lives on the server
	 *
	 * @uses WP_PLUGIN_DIR
	 * @uses ABSPATH
	 * @since 0.1.0
	 * @return void
	*/
	function set_plugin_locations() {
		$this->_plugin_path = plugin_dir_path( __FILE__ );
		$this->_plugin_url  = plugins_url( '', __FILE__ );
		$this->_plugin_base = plugin_basename( __FILE__ );
	}

	/**
	 * Returns IT Exchange Plugin Path
	 *
	 * @since 1.1.5
	 * @return void
	*/
	public function get_plugin_path() {
		return $this->_plugin_path;
	}

	/**
	 * Loads the translation data for WordPress
	 *
	 * @uses load_plugin_textdomain()
	 * @since 0.1.0
	 * @return void
	*/
	function set_textdomain() {
		$plugin_name = dirname( $this->_plugin_base );
		$locale      = apply_filters( 'plugin_locale', get_locale(), $plugin_name );
		$dir         = trailingslashit( WP_LANG_DIR . '/plugins/' . $plugin_name );

		load_textdomain( 'it-l10n-ithemes-exchange', $dir . 'it-l10n-ithemes-exchange-' . $locale . '.mo' );
		load_plugin_textdomain( 'it-l10n-ithemes-exchange', false, $plugin_name . '/lang/' );
	}

	/**
	 * Includes files for enabled add-ons
	 *
	 * @since 0.3.1
	 * @return void
	*/
	function addons_init() {
		// Add action for third party addons to register addons with.
		do_action( 'it_exchange_register_addons' );

		$enabled_addons = array();

		// Init all previously enabled addons
		if ( $enabled_addons = it_exchange_get_enabled_addons() ) {
			foreach( (array) $enabled_addons as $slug => $params ) {
				if ( ! empty( $params['file'] ) && is_file( $params['file'] ) ) {
					include( $params['file'] );
				} else {
					it_exchange_disable_addon( $slug );
					if ( is_admin() ) {
						wp_safe_redirect('admin.php?page=it-exchange-addons&message=addon-auto-disabled-' . $slug );
						die();
					}
				}
			}
		}

		// Get addons
		$registered = it_exchange_get_addons();

		// Auto enable all 3rd party addons
		foreach( $registered as $slug => $params ) {
			if ( ! it_exchange_is_core_addon( $slug ) && ! isset( $enabled_addons[$slug] ) && ! empty( $params['options']['auto-enable'] ) ) {
				it_exchange_enable_addon( $slug );
			}
		}
		do_action( 'it_exchange_enabled_addons_loaded' );
	}

	function remove_dev_dirs() {
		$plugins_dir = dirname( $this->_plugin_path );

		$dirs = array(
			$this->_plugin_path . '/vendor/phpunit',
			$plugins_dir . '/exchange-addon-membership/vendor/phpunit',
		);

		foreach ( $dirs as $dir ) {
			if ( is_dir( $dir ) ) {
				it_classes_load( 'it-file-utility.php' );
				ITFileUtility::delete_directory( $dir );
			}
		}
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
	global $IT_Exchange;
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
    add_option('_it-exchange-flush-rewrites', true );

	if ( ! get_option( 'it-exchange-versions', false ) ) {

		// if this is a new install, mark all our upgrades as completed

		require_once plugin_dir_path( __FILE__ ) . 'lib/upgrades/load.php';

		$upgrader = it_exchange_make_upgrader();

		foreach ( $upgrader->get_upgrades() as $upgrade ) {
			$upgrader->complete( $upgrade );
		}
	}

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
	if ( ! is_network_admin() ) {
		if ( false !== get_option( '_it-exchange-register-activation-hook', false ) ) {
		    delete_option('_it-exchange-register-activation-hook');
			wp_safe_redirect('admin.php?page=it-exchange-setup' );
		}
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
		it_exchange_get_pages( true );
		flush_rewrite_rules();
	}
}
add_action( 'admin_init', 'it_exchange_flush_rewrite_rules', 99 );

/**
 * This setups up the updater functionality
 *
 * @since 1.36.6
 *
 * @return void
*/
// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'EDD_SAMPLE_STORE_URL', 'https://exchangewp.com' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

// the name of your product. This should match the download name in EDD exactly
define( 'EDD_SAMPLE_ITEM_NAME', 'ExchangeWP' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

// the name of the settings page for the license input to be displayed
define( 'EDD_SAMPLE_PLUGIN_LICENSE_PAGE', 'exchangewp-license' );

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

function edd_sl_sample_plugin_updater() {

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'edd_sample_license_key' ) );

	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater( EDD_SAMPLE_STORE_URL, __FILE__, array(
			'version' 	=> '1.0', 				// current version number
			'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
			'item_name' => EDD_SAMPLE_ITEM_NAME, 	// name of this plugin
			'author' 	  => 'AJ Morris',  // author of this plugin
			'beta'		  => false
		)
	);

}
add_action( 'admin_init', 'edd_sl_sample_plugin_updater', 0 );


/************************************
* the code below is just a standard
* options page. Substitute with
* your own.
*************************************/

function edd_sample_license_menu() {
	// add_plugins_page( 'Plugin License', 'Plugin License', 'manage_options', EDD_SAMPLE_PLUGIN_LICENSE_PAGE, 'edd_sample_license_page' );
}
add_action('admin_menu', 'edd_sample_license_menu');

function edd_sample_license_page() {
	$license = get_option( 'edd_sample_license_key' );
	$status  = get_option( 'edd_sample_license_status' );
	?>
	<div class="wrap">
		<h2><?php _e('Plugin License Options'); ?></h2>
		<form method="post" action="options.php">

			<?php settings_fields('edd_sample_license'); ?>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e('License Key'); ?>
						</th>
						<td>
							<input id="edd_sample_license_key" name="edd_sample_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
							<label class="description" for="edd_sample_license_key"><?php _e('Enter your license key'); ?></label>
						</td>
					</tr>
					<?php if( false !== $license ) { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e('Activate License'); ?>
							</th>
							<td>
								<?php if( $status !== false && $status == 'valid' ) { ?>
									<span style="color:green;"><?php _e('active'); ?></span>
									<?php wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ); ?>
									<input type="submit" class="button-secondary" name="edd_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
								<?php } else {
									wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ); ?>
									<input type="submit" class="button-secondary" name="edd_license_activate" value="<?php _e('Activate License'); ?>"/>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php submit_button(); ?>

		</form>
	<?php
}

function edd_sample_register_option() {
	// creates our settings in the options table
	register_setting('edd_sample_license', 'edd_sample_license_key', 'edd_sanitize_license' );
}
add_action('admin_init', 'edd_sample_register_option');

function edd_sanitize_license( $new ) {
	$old = get_option( 'edd_sample_license_key' );
	if( $old && $old != $new ) {
		delete_option( 'edd_sample_license_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}



/************************************
* this illustrates how to activate
* a license key
*************************************/

function edd_sample_activate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['edd_license_activate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'edd_sample_nonce', 'edd_sample_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'edd_sample_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( EDD_SAMPLE_ITEM_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( EDD_SAMPLE_STORE_URL, array( 'timeout' => 30, 'sslverify' => false, 'body' => $api_params ) );

		// print_r($response);
		// var_dump($response);
		// die;

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				switch( $license_data->error ) {

					case 'expired' :

						$message = sprintf(
							__( 'Your license key expired on %s.' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;

					case 'revoked' :

						$message = __( 'Your license key has been disabled.' );
						break;

					case 'missing' :

						$message = __( 'Invalid license.' );
						break;

					case 'invalid' :
					case 'site_inactive' :

						$message = __( 'Your license is not active for this URL.' );
						break;

					case 'item_name_mismatch' :

						$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), EDD_SAMPLE_ITEM_NAME );
						break;

					case 'no_activations_left':

						$message = __( 'Your license key has reached its activation limit.' );
						break;

					default :

						$message = __( 'An error occurred, please try again.' );
						break;
				}

			}

		}

		// Check if anything passed on a message constituting a failure
		if ( ! empty( $message ) ) {
			$base_url = admin_url( 'plugins.php?page=' . EDD_SAMPLE_PLUGIN_LICENSE_PAGE );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// $license_data->license will be either "valid" or "invalid"

		update_option( 'edd_sample_license_status', $license_data->license );
		wp_redirect( admin_url( 'plugins.php?page=' . EDD_SAMPLE_PLUGIN_LICENSE_PAGE ) );
		exit();
	}
}
add_action('admin_init', 'edd_sample_activate_license');


/***********************************************
* Illustrates how to deactivate a license key.
* This will decrease the site count
***********************************************/

function edd_sample_deactivate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['edd_license_deactivate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'edd_sample_nonce', 'edd_sample_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'edd_sample_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( EDD_SAMPLE_ITEM_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( EDD_SAMPLE_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

			$base_url = admin_url( 'plugins.php?page=' . EDD_SAMPLE_PLUGIN_LICENSE_PAGE );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' ) {
			delete_option( 'edd_sample_license_status' );
		}

		wp_redirect( admin_url( 'plugins.php?page=' . EDD_SAMPLE_PLUGIN_LICENSE_PAGE ) );
		exit();

	}
}
add_action('admin_init', 'edd_sample_deactivate_license');


/************************************
* this illustrates how to check if
* a license key is still valid
* the updater does this for you,
* so this is only needed if you
* want to do something custom
*************************************/

function edd_sample_check_license() {

	global $wp_version;

	$license = trim( get_option( 'edd_sample_license_key' ) );

	$api_params = array(
		'edd_action' => 'check_license',
		'license' => $license,
		'item_name' => urlencode( EDD_SAMPLE_ITEM_NAME ),
		'url'       => home_url()
	);

	// Call the custom API.
	$response = wp_remote_post( EDD_SAMPLE_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	if ( is_wp_error( $response ) )
		return false;

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if( $license_data->license == 'valid' ) {
		echo 'valid'; exit;
		// this license is still valid
	} else {
		echo 'invalid'; exit;
		// this license is no longer valid
	}
}

/**
 * This is a means of catching errors from the activation method above and displaying it to the customer
 */
function edd_sample_admin_notices() {
	if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

		switch( $_GET['sl_activation'] ) {

			case 'false':
				$message = urldecode( $_GET['message'] );
				?>
				<div class="error">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;

			case 'true':
			default:
				// Developers can put a custom success message here for when activation is successful if they way.
				break;

		}
	}
}
add_action( 'admin_notices', 'edd_sample_admin_notices' );


//include( plugin_dir_path( __FILE__ ) . 'update.php' );

// Init DB sessions
require( plugin_dir_path( __FILE__ ) . 'lib/sessions/load.php' );

/**
 * Register all sync verbs
 *
 * @param Ithemes_Sync_API $api An instnance of the iThemes Sync API
 *
 * @since 1.9.2
 *
 * @return void
 */
function it_exchange_register_sync_verbs( $api ) {
	$verbs = array(
		'it-exchange-get-overview' => 'Ithemes_Sync_Verb_Ithemes_Exchange_Get_Overview',
	);
	foreach( $verbs as $name => $class ) {
		$api->register( $name, $class, plugin_dir_path( __FILE__ ) . "lib/integrations/ithemes-sync/$name.php" );
	}
}
add_action( 'ithemes_sync_register_verbs', 'it_exchange_register_sync_verbs' );

if ( ! function_exists( '_deprecated_constructor' ) ) {
	function _deprecated_constructor( $class, $version ) {

		/**
		 * Fires when a deprecated constructor is called.
		 *
		 * @since 4.3.0
		 *
		 * @param string $class   The class containing the deprecated constructor.
		 * @param string $version The version of WordPress that deprecated the function.
		 */
		do_action( 'deprecated_constructor_run', $class, $version );

		/**
		 * Filter whether to trigger an error for deprecated functions.
		 *
		 * `WP_DEBUG` must be true in addition to the filter evaluating to true.
		 *
		 * @since 4.3.0
		 *
		 * @param bool $trigger Whether to trigger the error for deprecated functions. Default true.
		 */
		if ( WP_DEBUG && apply_filters( 'deprecated_constructor_trigger_error', true ) ) {
			if ( function_exists( '__' ) ) {
				trigger_error( sprintf( __( 'The called constructor method for %1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.' ), $class, $version, '<pre>__construct()</pre>' ) );
			} else {
				trigger_error( sprintf( 'The called constructor method for %1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', $class, $version, '<pre>__construct()</pre>' ) );
			}
		}

	}
}
