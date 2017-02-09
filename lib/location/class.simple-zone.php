<?php
/**
 * Simple Zone class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Simple_Zone
 */
class ITE_Simple_Zone implements ITE_Zone, IteratorAggregate, ArrayAccess {

	/** @var array */
	private $zone = array();

	/**
	 * ITE_Simple_Zone constructor.
	 *
	 * @param array $zone
	 */
	public function __construct( array $zone ) { $this->zone = $zone; }

	/**
	 * @inheritDoc
	 */
	public function getIterator() { return new ArrayIterator( $this->zone ); }

	/**
	 * @inheritDoc
	 */
	public function offsetExists( $offset ) {
		return isset( $this->zone[ $offset ] );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet( $offset ) {
		return isset( $this->zone[ $offset ] ) ? $this->zone[ $offset ] : null;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet( $offset, $value ) {
		$this->zone[ $offset ] = $value;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset( $offset ) {
		unset( $this->zone[ $offset ] );
	}

	/**
	 * @inheritDoc
	 */
	public function contains( ITE_Location $location, $upper_bound = '' ) {

		$priority  = array( 'country', 'state', 'zip', 'city' );
		$precision = $this->get_precision();

		foreach ( $priority as $i => $field ) {

			if ( ! isset( $this[ $field ] ) ) {
				continue;
			}

			$value = $this[ $field ];
			$other = $location[ $field ];

			if ( ! $this->check_field_equality( $value, $other ) ) {
				return false;
			}

			if ( $upper_bound === $field ) {
				return true;
			}

			// We've reached this zone's limit.
			if ( $field === $precision ) {
				return true;
			}
		}

		return true;
	}

	/**
	 * Check two fields for equality.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value
	 * @param mixed $other
	 *
	 * @return bool
	 */
	private function check_field_equality( $value, $other ) {

		if ( $value === self::WILD || $other === self::WILD ) {
			return true;
		}

		if ( is_array( $value ) && is_scalar( $other ) && in_array( $other, $value, true ) ) {
			return true;
		}

		if ( $value instanceof Traversable && is_scalar( $other ) && in_array( $other, iterator_to_array( $value ), true ) ) {
			return true;
		}

		if ( is_array( $other ) && is_scalar( $other ) && in_array( $value, $other, true ) ) {
			return true;
		}

		if ( $other instanceof Traversable && is_scalar( $other ) && in_array( $value, iterator_to_array( $other ), true ) ) {
			return true;
		}

		return $value === $other;
	}

	/**
	 * @inheritDoc
	 */
	public function mask( ITE_Location $location ) {

		$masked = new ITE_In_Memory_Address( $location->to_array() );

		foreach ( $masked as $field => $value ) {
			if ( empty( $this[ $field ] ) ) {
				$masked[ $field ] = self::WILD;
			}
		}

		return $masked;
	}

	/**
	 * @inheritDoc
	 */
	public function get_precision() {

		$priority = array( 'country', 'state', 'zip', 'city' );
		$last     = '';

		foreach ( $priority as $field ) {
			if ( isset( $this[ $field ] ) && $this[ $field ] !== ITE_Location::WILD ) {
				$last = $field;
			}
		}

		if ( $last ) {
			return $last;
		}

		throw new UnexpectedValueException( 'Unable to determine zone precision.' );
	}
}
