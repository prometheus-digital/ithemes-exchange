<?php
/**
 * This file holds the class for an iThemes Exchange Coupon
 *
 * @package IT_Exchange
 * @since   0.4.0
 */

/**
 * Merges a WP Post with iThemes Exchange Coupon data
 *
 * @since 0.4.0
 */
class IT_Exchange_Coupon implements ArrayAccess, Countable, Iterator {

	// WP Post Type Properties
	var $ID;
	var $post_author;
	var $post_date;
	var $post_date_gmt;
	var $post_content;
	var $post_title;
	var $post_excerpt;
	var $post_status;
	var $comment_status;
	var $ping_status;
	var $post_password;
	var $post_name;
	var $to_ping;
	var $pinged;
	var $post_modified;
	var $post_modified_gmt;
	var $post_content_filtered;
	var $post_parent;
	var $guid;
	var $menu_order;
	var $post_type;
	var $post_mime_type;
	var $comment_count;

	/**
	 * @param array $coupon_data any custom data registered by the coupon addon
	 *
	 * @since 0.4.0
	 *
	 * @internal
	 */
	var $coupon_data = array();

	/**
	 * Coupon code property. Use get_code() instead.
	 *
	 * @deprecated 1.33
	 *
	 * @var string
	 */
	var $code;

	/**
	 * Constructor. Loads post data and coupon data
	 *
	 * @since 0.4.0
	 *
	 * @param mixed $post wp post id or post object. optional.
	 *
	 * @throws Exception
	 */
	public function __construct( $post = false ) {

		// If not an object, try to grab the WP object
		if ( ! is_object( $post ) ) {
			$post = get_post( (int) $post );
		}

		// Ensure that $post is a WP_Post object
		if ( is_object( $post ) && ! $post instanceof WP_Post ) {
			$post = false;
		}

		// Ensure this is a coupon post type
		if ( 'it_exchange_coupon' != get_post_type( $post ) ) {
			$post = false;
		}

		// Return a WP Error if we don't have the $post object by this point
		if ( ! $post ) {
			throw new Exception( __( 'The IT_Exchange_Coupon class must have a WP post object or ID passed to its constructor', 'it-l10n-ithemes-exchange' ) );
		}

		// Grab the $post object vars and populate this objects vars
		foreach ( (array) get_object_vars( $post ) as $var => $value ) {
			$this->$var = $value;
		}

		/**
		 * Allows for additional properties to be set on the coupon object.
		 *
		 * Custom coupon types should register their coupon type with their custom class.
		 *
		 * @deprecated 1.33
		 *
		 * @param array   $properties
		 * @param WP_Post $post
		 */
		$additional_properties = apply_filters( 'it_exchange_coupon_additional_data', array(), $post );
		foreach ( $additional_properties as $key => $value ) {
			$this->coupon_data[ $key ] = $value;
			$this->$key                = $value;
		}

		$this->coupon_data['ID']    = $this->ID;
		$this->coupon_data['title'] = $this->post_title;

		reset( $this->coupon_data );
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Exchange_Coupon() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * The __toString method allows a class to decide how it will react when it is converted to a string.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->get_code();
	}

	/**
	 * Get the Coupon ID.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_ID() {
		return $this->ID;
	}

	/**
	 * Get the coupon code.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_code() {
		return $this->code;
	}

	/**
	 * Return the current element
	 *
	 * @since 1.33
	 *
	 * @return mixed Can return any type.
	 */
	public function current() {
		return current( $this->coupon_data );
	}

	/**
	 * Move forward to next element
	 *
	 * @since 1.33
	 *
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		next( $this->coupon_data );
	}

	/**
	 * Return the key of the current element
	 *
	 * @since 1.33
	 *
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key() {
		return key( $this->coupon_data );
	}

	/**
	 * Checks if current position is valid
	 *
	 * @since 1.33
	 *
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid() {
		return key( $this->coupon_data ) !== null;
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @since 1.33
	 */
	public function rewind() {
		reset( $this->coupon_data );
	}

	/**
	 * Set a custom property.
	 *
	 * @since 1.33
	 *
	 * @param string $offset
	 * @param mixed  $value
	 */
	public function offsetSet( $offset, $value ) {
		if ( is_null( $offset ) ) {
			$this->coupon_data[] = $value;
		} else {
			$this->coupon_data[ $offset ] = $value;
		}
	}

	/**
	 * Check if a custom property exists.
	 *
	 * @since 1.33
	 *
	 * @param string $offset
	 *
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return isset( $this->coupon_data[ $offset ] );
	}

	/**
	 * Unset a custom property.
	 *
	 * @since 1.33
	 *
	 * @param string $offset
	 */
	public function offsetUnset( $offset ) {
		unset( $this->coupon_data[ $offset ] );
	}

	/**
	 * Retrieve a custom property.
	 *
	 * @since 1.33
	 *
	 * @param string $offset
	 *
	 * @return mixed|null
	 */
	public function offsetGet( $offset ) {
		return isset( $this->coupon_data[ $offset ] ) ? $this->coupon_data[ $offset ] : null;
	}

	/**
	 * Get the total custom properties registered.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->coupon_data );
	}
}
