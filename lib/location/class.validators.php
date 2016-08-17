<?php
/**
 * Location Validators registry.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Location_Validators
 */
final class ITE_Location_Validators {

	/** @var ITE_Location_Validator[] */
	private static $validators = array();

	/**
	 * Add a validator.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Location_Validator $validator
	 */
	public static function add( ITE_Location_Validator $validator ) {
		self::$validators[ $validator->get_name() ] = $validator;
	}

	/**
	 * Remove a validator.
	 *
	 * @since 1.36.0
	 *
	 * @param string $name
	 */
	public static function remove( $name ) {
		unset( self::$validators[ $name ] );
	}

	/**
	 * Get all validators.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Location_Validator[]
	 */
	public static function all() {
		return array_values( self::$validators );
	}
}