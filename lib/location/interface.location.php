<?php
/**
 * Location Interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Location
 *
 * To retrieve parts of the location, use ArrayAccess.
 *
 * For example.
 *
 * $country = $location['country']; // US
 * $line_1  = $location['address1']; // 1720 S. Kelly Ave.
 *
 * $location['state'] = 'OK';
 */
interface ITE_Location extends ArrayAccess, IteratorAggregate {

	/**
	 * Wildcard Match
	 *
	 * @var string
	 */
	const WILD = '*';

	/**
	 * Whether this location contains another location.
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
	 * Whether this location is equal to another location.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Location $location
	 *
	 * @return bool
	 */
	public function equals( ITE_Location $location );

	/**
	 * Convert the location to an array.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $whitelisted_only Only return the whitelisted ( default ) fields.
	 *
	 * @return array
	 */
	public function to_array( $whitelisted_only = false );
}
