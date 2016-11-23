<?php
/**
 * Cart Meta registry.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Cart_Meta_Registry
 */
class ITE_Cart_Meta_Registry {

	/**
	 * @var ITE_Cart_Meta[]
	 */
	private static $meta = array();

	/**
	 * Register metadata.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Cart_Meta $meta
	 */
	public static function register( ITE_Cart_Meta $meta ) {
		static::$meta[ $meta->get_key() ] = $meta;
	}

	/**
	 * Get meta key.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 *
	 * @return ITE_Cart_Meta|null
	 */
	public static function get( $key ) {
		return isset( static::$meta[ $key ] ) ? static::$meta[ $key ] : null;
	}

	/**
	 * Get all meta values from the registry.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Cart_Meta[]
	 */
	public static function all() {
		return array_values( static::$meta );
	}

	/**
	 * Get all meta that is editable in REST.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Cart_Meta[]
	 */
	public static function editable_in_rest() {
		$editable = array();

		foreach ( static::all() as $entry ) {
			if ( $entry->editable_in_rest() ) {
				$editable[] = $entry;
			}
		}

		return $editable;
	}
}
