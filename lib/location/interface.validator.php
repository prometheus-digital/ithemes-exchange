<?php
/**
 * Location Validator interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Location_Validator
 */
interface ITE_Location_Validator {

	/**
	 * Get the name of this validator.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public static function get_name();

	/**
	 * Validate a location.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Location $location
	 *
	 * @return bool
	 */
	public function validate( ITE_Location $location );

	/**
	 * Validate a location for a given cart.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Location $location
	 * @param \ITE_Cart     $cart
	 *
	 * @return bool
	 */
	public function validate_for_cart( ITE_Location $location, ITE_Cart $cart );

	/**
	 * Get the zone this validator can validate locations in.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Zone|null
	 */
	public function can_validate();
}
