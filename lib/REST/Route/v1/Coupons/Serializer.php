<?php
/**
 * Coupon Serializer.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Coupons;

/**
 * Class Serializer
 *
 * @package iThemes\Exchange\REST\Route\v1\Coupons
 */
class Serializer {

	/** @var \ITE_Coupon_Type */
	private $type;

	/** @var callable */
	private $serialize_callback;

	/**
	 * Serializer constructor.
	 *
	 * @param \ITE_Coupon_Type $type
	 * @param callable         $serialize_callback
	 */
	public function __construct( \ITE_Coupon_Type $type, $serialize_callback = null ) {
		$this->type               = $type;
		$this->serialize_callback = $serialize_callback;
	}

	/**
	 * Serialize a coupon.
	 *
	 * @since 2.0.0
	 *
	 * @param \IT_Exchange_Coupon $coupon
	 *
	 * @return array
	 */
	public function serialize( \IT_Exchange_Coupon $coupon ) {
		if ( $this->serialize_callback ) {
			return call_user_func( $this->serialize_callback, $coupon );
		}

		return array();
	}

	/**
	 * Get the schema for this coupon type.
	 *
	 * @since 2.0.0
	 *
	 * @return array|null
	 */
	public function get_schema() { return $this->type->get_schema(); }
}