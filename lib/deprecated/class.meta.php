<?php
/**
 * Deprecated Meta.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Deprecated_Meta
 *
 * This class can be used to change meta keys.
 *
 * For example:
 *
 * $handler = new IT_Exchange_Deprecated_Meta();
 * $handler->add( 'old_meta_key', 'new_meta_key', '1.4.0', true );
 *
 * This will intercept calls to the metadata API and substitute the new meta key.
 * Additionally, meta keys will be replaced in the `meta_query` argument to `WP_Query()`.
 *
 * If the fourth parameter, `$warn` is set to true, the user will be notified if the are using a deprecated
 * meta key. This issues a notice similar to `_deprecated_function()`.
 */
class IT_Exchange_Deprecated_Meta {

	/**
	 * Map of deprecated meta keys to their replacement meta keys.
	 *
	 * @var array
	 */
	private $deprecated_to_replacement = array();

	/**
	 * Map of replacement meta keys to their deprecated meta keys.
	 *
	 * @var array
	 */
	private $replacement_to_deprecated = array();

	/**
	 * Additional info about a deprecation.
	 *
	 * Keyed by deprecated meta key.
	 *
	 * @var array
	 */
	private $info = array();

	/**
	 * Meta Type.
	 *
	 * @var string
	 */
	private $type;

	/**
	 * IT_Exchange_Deprecated_Meta constructor.
	 *
	 * @param string $type
	 */
	public function __construct( $type = 'post' ) {

		$this->type = $type;

		if ( $type === 'post' ) {
			add_action( 'pre_get_posts', array( $this, 'replace_meta_keys_in_wp_query' ) );
		} elseif ( $type === 'user' ) {
			add_action( 'pre_get_users', array( $this, 'replace_meta_keys_in_wp_user_query' ) );
		}

		add_filter( "add_{$type}_metadata", array( $this, 'add_meta_handler' ), 10, 5 );
		add_filter( "update_{$type}_metadata", array( $this, 'update_meta_handler' ), 10, 5 );
		add_filter( "delete_{$type}_metadata", array( $this, 'delete_meta_handler' ), 10, 5 );
		add_filter( "get_{$type}_metadata", array( $this, 'get_meta_handler' ), 10, 4 );
	}

	/**
	 * Add a deprecated meta key to be handled.
	 *
	 * @since 2.0.0
	 *
	 * @param string $deprecated  Deprecated meta key.
	 * @param string $replacement Replacement meta key.
	 * @param string $version     Version the deprecation happened.
	 * @param bool   $warn        Whether to trigger a notice when a deprecated meta key is used.
	 *
	 * @return $this
	 */
	public function add( $deprecated, $replacement, $version, $warn = false ) {

		$this->deprecated_to_replacement[ $deprecated ]  = $replacement;
		$this->replacement_to_deprecated[ $replacement ] = $deprecated;

		$this->info[ $deprecated ] = array( 'version' => $version, 'warn' => $warn );

		return $this;
	}

	/**
	 * Replace all deprecated meta keys in WP_Query.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Query $query
	 */
	public function replace_meta_keys_in_wp_query( WP_Query $query ) {

		if ( $query->get( 'meta_key' ) && $this->is_deprecated( $query->get( 'meta_key' ) ) ) {
			$this->warn( $query->get( 'meta_key' ), 'WP_Query.meta_key' );
			$query->set( 'meta_key', $this->replacement( $query->get( 'meta_key' ) ) );
		}

		if ( ! $query->get( 'meta_query' ) ) {
			return;
		}

		$meta_query = $query->get( 'meta_query' );

		$replaced = $this->do_replacement( $meta_query, 'WP_Query' );

		$query->set( 'meta_query', $replaced );
	}

	/**
	 * Replace meta keys in WP_USer_Query.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_User_Query $query
	 */
	public function replace_meta_keys_in_wp_user_query( WP_User_Query $query ) {

		if ( $query->get( 'meta_key' ) && $this->is_deprecated( $query->get( 'meta_key' ) ) ) {
			$this->warn( $query->get( 'meta_key' ), 'WP_User_Query.meta_key' );
			$query->set( 'meta_key', $this->replacement( $query->get( 'meta_key' ) ) );
		}

		if ( ! $query->get( 'meta_query' ) ) {
			return;
		}

		$meta_query = $query->get( 'meta_query' );

		$replaced = $this->do_replacement( $meta_query, 'WP_User_Query' );

		$query->set( 'meta_query', $replaced );
	}

	/**
	 * Do the replacements of meta queries.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $meta_query
	 * @param string $context
	 *
	 * @return array
	 */
	private function do_replacement( $meta_query, $context ) {
		foreach ( $meta_query as $key => $value ) {
			if ( $key === 'relation' ) {
				continue;
			}

			if ( ! is_array( $value ) ) {
				// something wrong with user-input, bail.

				continue;
			}

			if ( isset( $value['key'] ) ) {
				if ( $this->is_deprecated( $value['key'] ) ) {

					$deprecated = $value['key'];

					$this->warn( $deprecated, "$context.meta_query" );

					$meta_query[ $key ]['key'] = $this->replacement( $deprecated );
				}
			} else {
				$meta_query[ $key ] = $this->do_replacement( $value, $context );
			}
		}

		return $meta_query;
	}

	public function add_meta_handler( $value, $object_id, $meta_key, $meta_value, $unique ) {

		if ( ! $this->is_deprecated( $meta_key ) ) {
			return $value;
		}

		$this->warn( $meta_key, "add_{$this->type}_meta" );
		$replace = $this->replacement( $meta_key );

		return add_metadata( $this->type, $object_id, wp_slash( $replace ), wp_slash( $meta_value ), $unique );
	}

	public function update_meta_handler( $value, $object_id, $meta_key, $meta_value, $prev_value ) {

		if ( ! $this->is_deprecated( $meta_key ) ) {
			return $value;
		}

		$this->warn( $meta_key, "update_{$this->type}_meta" );
		$replace = $this->replacement( $meta_key );

		return update_metadata( $this->type, $object_id, wp_slash( $replace ), wp_slash( $meta_value ), $prev_value );
	}

	public function delete_meta_handler( $value, $object_id, $meta_key, $meta_value, $delete_all ) {

		if ( $delete_all ) {
			return $value;
		}

		if ( ! $this->is_deprecated( $meta_key ) ) {
			return $value;
		}

		$this->warn( $meta_key, "delete_{$this->type}_meta" );
		$replace = $this->replacement( $meta_key );

		return delete_metadata( $this->type, $object_id, wp_slash( $replace ), wp_slash( $meta_value ), $delete_all );
	}

	public function get_meta_handler( $value, $object_id, $meta_key, $single ) {

		if ( ! $this->is_deprecated( $meta_key ) ) {
			return $value;
		}

		$this->warn( $meta_key, "get_{$this->type}_meta" );
		$replace = $this->replacement( $meta_key );

		return get_metadata( $this->type, $object_id, wp_slash( $replace ), $single );
	}

	/**
	 * Check if a given meta key is deprecated.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function is_deprecated( $key ) {
		return isset( $this->deprecated_to_replacement[ $key ] );
	}

	/**
	 * Get the replacement meta key.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	protected function replacement( $key ) {
		return $this->deprecated_to_replacement[ $key ];
	}

	/**
	 * Warn a user about using a deprecated meta key.
	 *
	 * @since 2.0.0
	 *
	 * @param string $deprecated Deprecated meta key.
	 * @param string $context    Contextualize where the deprecated meta key was used. Ex: 'add_post_meta()'
	 */
	private function warn( $deprecated, $context = '' ) {

		if ( ! $this->info[ $deprecated ]['warn'] ) {
			return;
		}

		$replacement = $this->replacement( $deprecated );
		$version     = $this->info[ $deprecated ]['version'];

		/**
		 * Fires when a deprecated meta key is is used.
		 *
		 * @since 2.0.0
		 *
		 * @param string $deprecated  The meta key that was used.
		 * @param string $replacement The meta key that should be used as a replacement.
		 * @param string $version     The version of Exchange or add-on that deprecated the meta key used.
		 * @param string $context     The context where the deprecated meta key was used.
		 */
		do_action( 'it_exchange_deprecated_meta_run', $deprecated, $replacement, $version, $context );

		if ( $context ) {
			$context = sprintf( __( 'Used in %s', 'it-l10n-ithemes-exchange' ), $context );
		}

		/**
		 * Filters whether to trigger deprecated meta key errors.
		 *
		 * @since 2.0.0
		 *
		 * @param bool $trigger Whether to trigger deprecated meta key errors.
		 *                      Requires `WP_DEBUG` to be defined true.
		 */
		if ( ! WP_DEBUG || ! apply_filters( 'it_exchange_deprecated_meta_run_trigger_error', true ) ) {
			return;
		}

		if ( ! empty( $replacement ) ) {
			$message = __( 'Meta key %1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'it-l10n-ithemes-exchange' );

			if ( $context ) {
				$message .= ' ' . $context;
			}

			trigger_error( sprintf( $message, $deprecated, $version, $replacement ) );
		} else {
			$message = __( 'Meta key %1$s is <strong>deprecated</strong> since version %2$s with no alternative available.', 'it-l10n-ithemes-exchange' );

			if ( $context ) {
				$message .= ' ' . $context;
			}

			trigger_error( sprintf( $message, $deprecated, $version ) );
		}
	}
}
