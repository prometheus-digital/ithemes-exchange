<?php
/**
 * This file holds the class for an iThemes Exchange Coupon
 *
 * @package IT_Exchange
 * @since 0.3.2
*/

/**
 * Merges a WP Post with iThemes Exchange Coupon data
 *
 * @since 0.3.2
*/
class IT_Exchange_Coupon {

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
	 * @param array $coupon_data  any custom data registered by the coupon addon
	 * @since 0.3.2
	*/
	var $coupon_data = array();

	/**
	 * Constructor. Loads post data and coupon data
	 *
	 * @since 0.3.2
	 * @param mixed $post  wp post id or post object. optional.
	 * @return void
	*/
	function IT_Exchange_Coupon( $post=false ) {
		
		// If not an object, try to grab the WP object
		if ( ! is_object( $post ) )
			$post = get_post( (int) $post );

		// Ensure that $post is a WP_Post object
		if ( is_object( $post ) && 'WP_Post' != get_class( $post ) )
			$post = false;

		// Ensure this is a coupon post type
		if ( 'it_exchange_coupon' != get_post_type( $post ) )
			$post = false;

		// Return a WP Error if we don't have the $post object by this point
		if ( ! $post )
			return new WP_Error( 'it-exchange-coupon-not-a-wp-post', __( 'The IT_Exchange_Coupon class must have a WP post object or ID passed to its constructor', 'LION' ) );

		// Grab the $post object vars and populate this objects vars
		foreach( (array) get_object_vars( $post ) as $var => $value ) {
			$this->$var = $value;
		}

		// Set additional properties
		$additional_properties = apply_filters( 'it_excahnge_coupon_additional_data', array() );
		foreach( $additional_properties as $key => $value ) {
			$this->$key = $value;
		}
	}

	/**
	 * Sets the coupon_data property from appropriate assoicated post_meta
	 *
	 * @ since 0.3.2
	 * @return void
	*/
	function set_coupon_supports_and_data() {
		// Get coupon-type options
		if ( $coupon_type_options = it_exchange_get_coupon_type_options( $this->coupon_type ) ) {
			if ( ! empty( $coupon_type_options['supports'] ) ) {
				foreach( $coupon_type_options['supports'] as $feature => $params ) {
					// Set the coupon_supports array
					$this->coupon_supports[$feature] = $params;

					// Set the coupon data via a filter.
					$value = apply_filters( 'it_exchange_set_coupon_data_for_' . $params['componant'] . '_componant', false, $this->ID, $params );

					// Set to default if it exists
					$default = empty( $coupon_type_options['supports'][$feature]['default'] ) ? false : $coupon_type_options['supports'][$feature]['default'];
					if ( empty( $value ) )
						$this->coupon_data[$params['key']] = $default;
					else
						$this->coupon_data[$params['key']] = $value;
				}
			}
		}
	}

	/**
	 * Sets supported feature values for post_meta componant
	 *
	 * @since 0.3.7
	 * @param string existing value
	 * @param integer coupon id
	 * @param array params for supports array registered with the add-on
	 * @return mixed value of post_meta
	*/
	function set_feature_value_for_post_meta_componant( $existing, $coupon, $params ) {

		// Return if someone else beat us to it.
		if ( ! empty( $existing ) )
			return $existing;

		// Set coupon_data to post_meta value or feature devault
		if ( $value = get_post_meta( $coupon, $params['key'], true ) )
			return $value;

		return false;
	}
}
