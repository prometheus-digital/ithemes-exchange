<?php
/**
 * Parameter Bag backed by meta storage.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Meta_Parameter_Bag
 */
class ITE_Meta_Parameter_Bag implements ITE_Parameter_Bag {

	/**
	 * Object ID.
	 *
	 * @var int
	 */
	private $ID;

	/**
	 * Meta Type.
	 *
	 * @var string
	 */
	private $type;

	/**
	 * @var string
	 */
	private $prefix;

	/**
	 * ITE_Meta_Parameter_Bag constructor.
	 *
	 * @param int    $ID     Object ID.
	 * @param string $type   Metadata Type. Example 'post' or 'user'.
	 * @param string $prefix Value to be prepended to all params before passing to the metadata APIs.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $ID, $type, $prefix = '' ) {

		if ( ! is_numeric( $ID ) || $ID <= 0 ) {
			throw new InvalidArgumentException( '$ID >= 0' );
		}

		if ( ! is_string( $type ) || trim( $type ) === '' ) {
			throw new InvalidArgumentException( '$type must be non-empty string.' );
		}

		$this->ID     = $ID;
		$this->type   = trim( $type );
		$this->prefix = trim( $prefix );
	}

	/**
	 * @inheritDoc
	 */
	public function get_params() {

		$meta = get_metadata( $this->type, $this->ID );

		if ( ! is_array( $meta ) ) {
			throw new UnexpectedValueException( "get_metadata($this->type, $this->ID) returned non-array value." );
		}

		$params = array();

		foreach ( $meta as $key => $value ) {

			$param = $this->to_param( $key );

			if ( $param === false ) {
				continue;
			}

			$params[ $param ] = $value[0];
		}

		return $params;
	}

	/**
	 * @inheritDoc
	 */
	public function has_param( $param ) {
		return metadata_exists( $this->type, $this->ID, $this->to_key( $param ) );
	}

	/**
	 * @inheritDoc
	 */
	public function get_param( $param ) {
		if ( ! $this->has_param( $param ) ) {
			throw new OutOfBoundsException( "Parameter {$param} does not exist." );
		}

		return get_metadata( $this->type, $this->ID, $this->to_key( $param ), true );
	}

	/**
	 * @inheritDoc
	 */
	public function set_param( $param, $value ) {
		return (bool) update_metadata( $this->type, $this->ID, $this->to_key( $param ), $value );
	}

	/**
	 * @inheritDoc
	 */
	public function remove_param( $param ) {
		return delete_metadata( $this->type, $this->ID, $this->to_key( $param ) );
	}

	/**
	 * Convert a parameter to a key.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 *
	 * @return string|false
	 */
	private function to_param( $key ) {
		if ( ! $this->prefix ) {
			return $key;
		}

		$pos = strpos( $key, $this->prefix );

		if ( $pos !== 0 ) {
			return false;
		}

		return substr( $key, strlen( $this->prefix ) );
	}

	/**
	 * Transform a param into a meta key.
	 *
	 * @since 2.0.0
	 *
	 * @param string $param
	 *
	 * @return string
	 */
	private function to_key( $param ) {
		return "{$this->prefix}{$param}";
	}
}
