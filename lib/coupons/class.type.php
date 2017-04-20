<?php
/**
 * Coupon Type class.
 *
 * @since   2.0.0
 * @license GPLv2
 */
use iThemes\Exchange\REST\Route\v1\Coupons\Serializer;

/**
 * Class ITE_Coupon_Type
 */
class ITE_Coupon_Type {

	/** @var string */
	private $type;

	/** @var string */
	private $class = 'IT_Exchange_Coupon';

	/** @var array|callable|null */
	private $schema;

	/** @var Serializer */
	private $rest_serializer;

	/** @var callable */
	private $update_from_rest;

	/**
	 * ITE_Coupon_Type constructor.
	 *
	 * @param       $type
	 * @param array $args
	 *
	 * @throws Exception
	 */
	public function __construct( $type, array $args = array() ) {
		$this->type = $type;

		$base = 'IT_Exchange_Coupon';

		if ( empty( $args['class'] ) || ( $args['class'] !== $base && ! is_subclass_of( $args['class'], $base ) ) ) {
			throw new Exception( 'Invalid coupon class. Class must extend IT_Exchange_Coupon' );
		}

		$this->class = $args['class'];

		if ( isset( $args['schema'] ) ) {
			if ( is_array( $args['schema'] ) || is_callable( $args['schmea'] ) ) {
				$this->schema = $args['schema'];
			}
		}

		if ( isset( $args['rest_serializer'] ) ) {
			if ( $args['rest_serializer'] instanceof Serializer ) {
				$this->rest_serializer = $args['rest_serializer'];
			} elseif ( $args['rest_serializer'] instanceof Closure ) {
				$this->rest_serializer = new Serializer( $this, $args['rest_serializer'] );
			} else {
				throw new InvalidArgumentException( sprintf(
					'Invalid data type for rest_serializer. Expected Item_Serializer, received %s.',
					is_object( $args['rest_serializer'] ) ? get_class( $args['rest_serializer'] ) : gettype( $args['rest_serializer'] )
				) );
			}
		}

		if ( isset( $args['update_from_rest'] ) ) {
			if ( ! is_callable( $args['update_from_rest'] ) ) {
				throw new InvalidArgumentException( 'update_from_rest parameter is not callable.' );
			}

			$this->update_from_rest = $args['update_from_rest'];
		}
	}

	/**
	 * Get the type of this coupon.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Get the PHP class name for this coupon type.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_class() {
		return $this->class;
	}

	/**
	 * Get the serializer for the coupon.
	 *
	 * @since 2.0.0
	 *
	 * @return Serializer
	 */
	public function get_rest_serializer() {
		return $this->rest_serializer;
	}

	/**
	 * Update a coupon from a REST request.
	 *
	 * This can be either a PUT or POST request.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 *
	 * @return IT_Exchange_Coupon|WP_Error
	 */
	public function update_from_rest( \iThemes\Exchange\REST\Request $request ) {
		return call_user_func( $this->update_from_rest, $request );
	}

	/**
	 * Get the schema for this coupon type.
	 *
	 * @since 2.0.0
	 *
	 * @return array|null
	 */
	public function get_schema() {

		if ( is_callable( $this->schema ) ) {
			return call_user_func( $this->schema );
		}

		return $this->schema;
	}
}