<?php
/**
 * Interface for 3rd Party add-ons to implement Coupons
 * @package IT_Exchange
 * @since 0.4.0
*/

/**
 * Returns a list of coupons
 *
 * Options can be sent through to be used with WP's get_posts() funciton.
 * @since 0.4.0
 *
 * @return array an array of posts from our coupon post type
*/
function it_exchange_get_coupons( $options=array() ) {
    $defaults = array(
        'post_type' => 'it_exchange_coupon',
    );  

    $options = wp_parse_args( $options, $defaults );

	// Add filter to only retreive coupons added by a specific add-on
    if ( ! empty( $options['added_by'] ) ) { 
        $meta_query = empty( $options['meta_query'] ) ? array() : $options['meta_query'];
        $meta_query[] = array( 
            'key'   => '_it_exchange_added_by',
            'value' => $options['addon-slug'],
        );  
        $options['meta_query'] = $meta_query;
    }   

    if ( $coupons = get_posts( $options ) ) { 
        foreach( $coupons as $key => $coupon ) { 
            $coupons[$key] = it_exchange_get_coupon( $coupon );
        }   
        return $coupons;
    }   

    return array();
}

/**
 * Retreives a coupon object by passing it the WP post object or post id
 *
 * @since 0.4.0
 * @param mixed $post post object or post id
 * @rturn object IT_Exchange_Coupon object for passed post
*/
function it_exchange_get_coupon( $post ) {
	$coupon = new IT_Exchange_Coupon( $post );
	if ( $coupon->ID )
		return $coupon;
	return false;
}

/**
 * Adds a coupon post_type to WP
 *
 * @since 0.4.0
 * @param array $args same args passed to wp_insert_post plus any additional needed
 * @param object $cart_object passed cart object
 * @return mixed post id or false
*/
function it_exchange_add_coupon( $args=array(), $cart_object=false ) { 
	$defaults = array(
		'post_type'          => 'it_exchange_coupon',
		'post_status'        => 'publish',
	);  

	$post_meta = empty( $args['post_meta'] ) ? array() : $args['post_meta'];
	unset( $args['post_meta'] );
	$args = wp_parse_args( $args, $defaults );

	// If we don't have a title, return false
	if ( empty( $args['post_title'] ) ) 
		return false;

	if ( $coupon_id = wp_insert_post( $args ) ) { 
		foreach ( (array) $post_meta as $key => $value ) {
			update_post_meta( $coupon_id, $key, $value );
		}
		do_action( 'it_exchange_add_coupon_success', $coupon_id, $cart_object );
		return $coupon_id;
	}   
	do_action( 'it_exchange_add_coupon_failed', $args );
	return false;
}

/**
 * Dow we support a specific type of coupon
 *
 * Ask the addon
 *
 * @since 0.4.0
 *
 * @param string $type the type of coupon
 * @return boolean
*/
function it_exchange_supports_coupon_type( $type ) {
	return (boolean) apply_filters( 'it_exchange_supports_' . $type . '_coupons', false );
}

/**
 * Return the currently applied coupons
 *
 * We're going to ask the add-ons for this info. Default is no.
 *
 * @since 0.4.0
 *
 * @param string $type the type of coupon to check for
 * @return boolean
*/
function it_exchange_get_applied_coupons( $type ) {
	return apply_filters( 'it_exchange_get_applied_' . $type . '_coupons', false );
}

/**
 * Are we accepting any more of the passed coupon type
 *
 * We're going to ask the add-ons for this info. Default is no.
 *
 * @since 0.4.0
 *
 * @param string $type the type of coupon to check for
 * @return boolean
*/
function it_exchange_accepting_coupon_type( $type ) {
	return (boolean) apply_filters( 'it_exchange_accepting_' . $type . '_coupons', false );
}

/**
 * Retreive the field for applying a coupon type
 *
 * We're going to ask the add-ons for this info. Default is an empty string
 *
 * @since 0.4.0
 *
 * @param string $type the type of coupon to check for
 * @param array $options
 * @return boolean
*/
function it_exchange_get_coupon_type_apply_field( $type, $options=array() ) {
	return apply_filters( 'it_exchange_apply_' . $type . '_coupon_field', '', $options );
}

/**
 * Generates the remove a coupon that has been applied
 *
 * @since 0.4.0
 *
 * @return string
*/
function it_exchange_get_remove_coupon_html( $type, $code, $options=array() ) {
	$options['code'] = $code;
	return apply_filters( 'it_exchange_remove_' . $type . '_coupon_html', '', $code, $options );
}

/**
 * Apply a coupon
 *
 * We're going to ask the add-ons to do this for us.
 *
 * @since 0.4.0
 *
 * @param string $type the type of coupon to check for
 * @param string $code the coupon code
 * @param array $options
 * @return boolean
*/
function it_exchange_apply_coupon( $type, $code, $options=array() ) {
	$options['code'] = $code;
	return apply_filters( 'it_exchange_apply_coupon_to_' . $type, 'false', $options );
}
