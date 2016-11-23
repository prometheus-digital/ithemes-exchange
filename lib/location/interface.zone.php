<?php
/**
 * Zone interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Zone
 */
interface ITE_Zone {

	/**
	 * Wildcard Match
	 *
	 * @var string
	 */
	const WILD = '*';

	/**
	 * Whether this zone contains a location.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Location $location
	 * @param string        $upper_bound Specify the upper bound that must match. For example, passing 'state'
	 *                                   requires the country and state to match for the method to return true.
	 *
	 * @return bool
	 */
	public function contains( ITE_Location $location, $upper_bound = '' );

	/**
	 * Mask a location based on this zone.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Location $location
	 *
	 * @return \ITE_Location Masked location. This should be a NEW object.
	 */
	public function mask( ITE_Location $location );

	/**
	 * Get the precision of this zone.
	 *
	 * For example 'city' if rates vary all the way down to city level.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_precision();
}
