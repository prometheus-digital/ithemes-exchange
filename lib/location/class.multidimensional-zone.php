<?php
/**
 * Multidimensional Zone
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Multidimensional_Zone
 *
 * Multidimensional zones are used for targeting multiple locations.
 *
 * A single zone object could be used to target all customers in Canada that have rates varying by state.
 *
 * A multidimensional zone object would be used to target all customers in all countries in the EU with rates varying
 * be each state within each EU county as well.
 *
 * ## Construction ##
 *
 * The constructor is very particular for the Multidimensional zone. It relies on the priority of fields
 * which is country -> state -> zip -> city.
 *
 * ## Examples ##
 *
 * $zone = new ITE_Multidimensional_Zone( [
 *      'US' => new ITE_Simple_Zone( [ 'state' => [ 'AL', 'AK', 'AZ' ... ] ] ),
 *      'CA' => new ITE_Simple_Zone( [ 'state' => [ 'AB', 'BC', 'MB' ... ] ] ),
 * ] );
 *
 * $zone = new ITE_Multidimensional_Zone( [
 *      'US' => new ITE_Multidimensional_Zone( [
 *          'AL' => new ITE_Simple_Zone( [ 'zip' => [ 35006, 35007 ] ] ),
 *          'AK' => new ITE_Simple_Zone( [ 'zip' => [ 99501, 99502 ] ] ),
 *          'AZ' => new ITE_Simple_Zone( [ 'zip' => [ 85001, 85002 ] ] ),
 *      ] )
 * ] );
 */
class ITE_Multidimensional_Zone implements ITE_Zone {

	/** @var string */
	private $field = 'country';

	/** @var array */
	private $zones = array();

	/** @var array */
	private static $priority = array( 'country', 'state', 'zip', 'city' );

	/**
	 * ITE_Multidimensional_Zone constructor.
	 *
	 * @param array $zones
	 */
	public function __construct( array $zones ) {

		$last_field = 0;

		foreach ( $zones as $field_value => $zone ) {
			$this->process_zone( $field_value, $zone, $last_field );
		}

		$this->zones = $zones;
	}

	/**
	 * Process a zone in the constructor.
	 *
	 * @since 1.36.0
	 *
	 * @param string       $value
	 * @param ITE_Location $zone
	 * @param int          $last_field
	 * @param array        $processed
	 */
	private function process_zone( $value, $zone, $last_field, $processed = array() ) {

		$last_field += 1;

		if ( $zone instanceof ITE_Multidimensional_Zone ) {
			$zone->field = self::$priority[ $last_field ];

			$processed = array_merge( $processed, array( $value ) );

			foreach ( $zone->zones as $zone_value => $child_zone ) {
				$child_processed = array_merge( $processed, array( $zone_value ) );
				$this->process_zone( $zone_value, $child_zone, $last_field, $child_processed );
			}

			$processed[] = $value;
		} elseif ( $zone instanceof ITE_Simple_Zone ) {
			for ( $i = 0; $i <= $last_field; $i ++ ) {
				if ( isset( $processed[ $i ] ) ) {
					$zone[ self::$priority[ $i ] ] = $processed[ $i ];
				}
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function contains( ITE_Location $location, $upper_bound = '' ) {

		$other = $location[ $this->field ];

		if ( ! isset( $this->zones[ $other ] ) ) {
			return false;
		}

		if ( $this->field === $upper_bound ) {
			return true;
		}

		$next_zone = $this->zones[ $other ];

		return $next_zone->contains( $location, $upper_bound );
	}

	/**
	 * @inheritDoc
	 */
	public function mask( ITE_Location $location ) {

		$zone = $this;

		do {
			$zone = reset( $zone->zones );
		} while ( ! $zone instanceof ITE_Simple_Zone );

		return $zone->mask( $location );
	}
}
