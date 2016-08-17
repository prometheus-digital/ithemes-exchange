<?php
/**
 * In-memory Address.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_In_Memory_Address
 */
class ITE_In_Memory_Address implements ITE_Location {

	/**
	 * @var array
	 */
	private $address = array();

	/**
	 * ITE_In_Memory_Address constructor.
	 *
	 * @param array $address
	 */
	public function __construct( array $address = array() ) {
		$this->address = ITUtility::merge_defaults( $address, array(
			'first-name'   => '',
			'last-name'    => '',
			'company-name' => '',
			'address1'     => '',
			'address2'     => '',
			'city'         => '',
			'state'        => '',
			'zip'          => '',
			'country'      => '',
			'email'        => '',
			'phone'        => '',
		) );
	}

	/**
	 * @inheritDoc
	 */
	public function getIterator() {
		return new ArrayIterator( $this->address );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists( $offset ) {
		return isset( $this->address[ $offset ] );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet( $offset ) {
		return isset( $this->address[ $offset ] ) ? $this->address[ $offset ] : null;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet( $offset, $value ) {
		$this->address[ $offset ] = $value;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset( $offset ) {
		unset( $this->address[ $offset ] );
	}

	/**
	 * @inheritDoc
	 */
	public function contains( ITE_Location $location, $upper_bound = '' ) {

		$priority = array( 'country', 'state', 'zip', 'city' );

		foreach ( $priority as $field ) {
			if ( $this[ $field ] !== $location[ $field ] && $location[ $field ] !== self::WILD && $this[ $field ] !== self::WILD ) {
				return false;
			}

			if ( $upper_bound === $field ) {
				return true;
			}
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function equals( ITE_Location $location ) {

		foreach ( $this as $field => $value ) {

			if ( $value === self::WILD ) {
				continue;
			}

			if ( ! isset( $location[ $field ] ) ) {
				return false;
			}

			if ( $location[ $field ] === self::WILD ) {
				continue;
			}

			if ( $value !== $location[$field] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function to_array() {
		return $this->address;
	}
}