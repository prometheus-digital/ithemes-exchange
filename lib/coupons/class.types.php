<?php
/**
 * Coupon Types registry.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Coupon_Types
 */
class ITE_Coupon_Types {

	/**
	 * @var ITE_Coupon_Type[]
	 */
	private static $types = array();

	/**
	 * Register a coupon type.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Coupon_Type $type
	 *
	 * @return bool
	 */
	public static function register( ITE_Coupon_Type $type ) {

		if ( isset( static::$types[ $type->get_type() ] ) ) {
			return false;
		}

		static::$types[ $type->get_type() ] = $type;

		if ( empty( $GLOBALS['it_exchange']['coupon_types'] ) ) {
			$GLOBALS['it_exchange']['coupon_types'] = array();
		}

		if ( empty( $GLOBALS['it_exchange']['coupon_types_meta'] ) ) {
			$GLOBALS['it_exchange']['coupon_types_meta'] = array();
		}

		$slug = $type->get_type();

		if ( ! in_array( $slug, $GLOBALS['it_exchange']['coupon_types'], true ) ) {
			$GLOBALS['it_exchange']['coupon_types'][]             = $slug;
			$GLOBALS['it_exchange']['coupon_types_meta'][ $slug ] = array(
				'class' => $type->get_class()
			);
		}

		/**
		 * Fires when a coupon type is registered.
		 *
		 * @since 1.33 Add $class parameter.
		 * @since 2.0.0 Add the $type parameter.
		 *
		 * @param string          $slug
		 * @param string          $class
		 * @param ITE_Coupon_Type $type
		 */
		do_action( 'it_exchange_register_coupon_type', $slug, $type->get_class(), $type );

		return true;
	}

	/**
	 * Get the registered coupon type for a given slug.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type
	 *
	 * @return ITE_Coupon_Type|null
	 */
	public static function get( $type ) {
		return isset( static::$types[ $type ] ) ? static::$types[ $type ] : null;
	}

	/**
	 * Is a coupon type with the given slug registered.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function has( $type ) {
		return isset( static::$types[ $type ] );
	}

	/**
	 * Get all coupon types.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Coupon_Type[]
	 */
	public static function all() {
		return array_values( static::$types );
	}

	/**
	 * Get all the coupons that are viewable/editable in REST.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Coupon_Type[]
	 */
	public static function in_rest() {
		return array_filter( static::all(), function ( ITE_Coupon_Type $type ) {
			return (bool) $type->get_rest_serializer();
		} );
	}

	/**
	 * Clear all coupon types.
	 *
	 * @since 2.0.0
	 */
	public static function clear() {
		static::$types = array();
	}
}