<?php

/**
 * Exchange main class.
 *
 * @package IT_Exchange
 * @since 0.1.0
*/
class IT_Exchange {

	var $_version         = '1.37.0';
	var $_wp_minimum      = '3.5';
	var $_slug            = 'ithemes-exchange';
	var $_name            = 'iThemes Exchange';
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
		$plugins_dir = dirname( dirname( $this->_plugin_path ) );

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

/**
 * Display a dismissible notice as to why v2 isn't loaded.
 */
function it_exchange_v2_admin_notice() {

	$reason = array();

	if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
		$reason[] = __( 'please upgrade your PHP version to 5.3 or higher.', 'it-l10n-ithemes-exchange' );
	}

	$addons = it_exchange_get_addons_not_v2_ready();

	if ( $addons ) {
		$reason[] = sprintf(
			__( 'the following add-ons must be updated to v2 or later: %s.', 'it-l10n-ithemes-exchange' ),
			implode( ', ', wp_list_pluck( $addons, 'name' ) )
		);
	}

	if ( ! $reason ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( get_user_setting( 'it_exchange_hide_v2_notice' ) ) {
		return;
	}

	if ( count( $addons ) === 1 ) {
		$message = sprintf(
			__( 'You are currently running version %s of iThemes Exchange. To upgrade to version 2 %s' ),
			$GLOBALS['IT_Exchange']->_version,
			$reason[0]
		);
	} elseif ( count( $addons ) === 2 ) {
		$message = sprintf(
			__( 'You are currently running version %s of iThemes Exchange. To upgrade to version 2 %s and %s' ),
			$GLOBALS['IT_Exchange']->_version,
			$reason[0],
			$reason[1]
		);
	} else {
		return;
	}

	?>

	<div class="notice notice-info is-dismissible it-exchange-v2">
		<p><?php echo $message; ?></p>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$( document ).on( 'click', '.it-exchange-v2 .notice-dismiss', function() {
				setUserSetting( 'it_exchange_hide_v2_notice', true );
			} );
		});
	</script>
<?php

}

add_action( 'admin_notices', 'it_exchange_v2_admin_notice' );

/**
 * Override the displayed plugin version.
 *
 * @param array $plugins
 *
 * @return array
 */
function it_exchange_override_plugin_version( $plugins ) {

	$plugins['ithemes-exchange/init.php']['Version'] = $GLOBALS['IT_Exchange']->_version;

	return $plugins;
}

add_filter( 'all_plugins', 'it_exchange_override_plugin_version' );

/**
 * Override the update plugins transient to remove updates that aren't for the deprecated version.
 *
 * This works be checking the latest copy of the readme.txt for the plugin. It looks for a string like
 * Deprecated: 1.36.2
 *
 * This represents the latest version of the deprecated code. If this plugins current version is gte to the latest,
 * then we move the update to the no updates list so the user isn't alerted to plugin updates that don't apply to them.
 * Else if the plugins current version is lt the latest, we modify the displayed Upgrade to version to the deprecated one.
 *
 * @param object $value
 *
 * @return object
 */
function it_exchange_pre_set_update_plugins_transient( $value ) {

	if ( ! isset( $value->response['ithemes-exchange/init.php'] ) ) {
		return $value;
	}

	$data        = $value->response['ithemes-exchange/init.php'];
	$new_version = $data->new_version;

	$response = wp_safe_remote_get( "https://plugins.svn.wordpress.org/ithemes-exchange/tags/$new_version/readme.txt" );

	if ( is_wp_error( $response ) ) {
		return $value;
	}

	if ( ! $response ) {
		return $value;
	}

	$readme = wp_remote_retrieve_body( $response );

	preg_match( '/Deprecated: ((\d|.)+)/', $readme, $matches );

	if ( ! $matches || ! isset( $matches[1] ) ) {
		return $value;
	}

	$current_version        = $GLOBALS['IT_Exchange']->_version;
	$new_deprecated_version = $matches[1];

	if ( version_compare( $current_version, $new_deprecated_version, '<' ) ) {
		$value->response['ithemes-exchange/init.php']->new_version = $new_deprecated_version;

		return $value;
	}

	// This is an update for the main package. Move it to the no_update list.
	$data->new_version = $current_version;
	unset( $value->response['ithemes-exchange/init.php'] );
	$value->no_update['ithemes-exchange/init.php'] = $data;

	return $value;
}

add_filter( 'pre_set_site_transient_update_plugins', 'it_exchange_pre_set_update_plugins_transient' );

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
