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
        'post_type' => 'it_exchange_coupons',
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
