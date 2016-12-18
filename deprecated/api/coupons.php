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
 * @param array $options
 *
 * @return IT_Exchange_Coupon[] an array of posts from our coupon post type
*/
function it_exchange_get_coupons( $options=array() ) {
	$defaults = array(
		'post_type' => 'it_exchange_coupon',
	);
	$args = wp_parse_args( $options, $defaults );
	$args['meta_query'] = empty( $args['meta_query'] ) ? array() : $args['meta_query'];

	// Add filter to only retreive coupons added by a specific add-on
	if ( ! empty( $args['added_by'] ) ) {
		$meta_query = array(
			'key'   => '_it_exchange_added_by',
			'value' => $options['addon-slug'],
		);
		$args['meta_query'] = array_merge( $args['meta_query'], $meta_query );
	}

	if ( $coupons = get_posts( $args ) ) {
		foreach( $coupons as $key => $coupon ) {
			$coupons[$key] = it_exchange_get_coupon( $coupon );
		}
	}

	return apply_filters( 'it_exchange_get_coupons', $coupons, $options );
}

/**
 * Retreives a coupon object by passing it the WP post object or post id
 *
 * @since 0.4.0
 * @since 1.33 Add $type parameter.
 *
 * @param WP_Post|int|IT_Exchange_Coupon $post post object or post id
 * @param string                         $type Coupon type. If empty, will try to infer.
 *
 * @return IT_Exchange_Coupon|bool object for passed post
*/
function it_exchange_get_coupon( $post, $type = '' ) {

	try {
		if ( $post instanceof IT_Exchange_Coupon ) {
			$coupon = $post;
		} else {

			if ( ! $type ) {
				$ID = $post instanceof WP_Post ? $post->ID : $post;

				if ( get_post_meta( $ID, '_it-basic-code', true ) ) {
					$type = 'cart';
				}
			}

			// if invalid type, will fall back to IT_Exchange_Coupon
			$class = it_exchange_get_coupon_type_class( $type );

			/** @var IT_Exchange_Coupon $coupon */
			$coupon = new $class( $post );
		}
	}
	catch ( Exception $e ) {
		return false;
	}

	if ( $coupon->get_ID() )
		return apply_filters( 'it_exchange_get_coupon', $coupon, $post );

	return false;
}

/**
 * Get a coupon from its code and type.
 *
 * @since 1.33
 *
 * @param string $code
 * @param string $type
 *
 * @return IT_Exchange_Coupon|null
 */
function it_exchange_get_coupon_from_code( $code, $type ) {

	/**
	 * Filter the coupon corresponding to a certain code.
	 *
	 * @since 1.33
	 *
	 * @param IT_Exchange_Coupon|null $coupon
	 * @pparam string                 $code
	 */
	return apply_filters( 'it_exchange_get_' . $type . '_coupon_from_code', null, $code );
}

/**
 * Adds a coupon post_type to WP
 *
 * @since 0.4.0
 * @param array $args same args passed to wp_insert_post plus any additional needed
 * @param object|bool $deprecated deprecated
 *
 * @return mixed post id or false
*/
function it_exchange_add_coupon( $args=array(), $deprecated = false ) {

	if ( $deprecated !== false ) {
		_deprecated_argument( 'it_exchange_add_coupon', '1.33' );
	}

	$defaults = array(
		'post_type'   => 'it_exchange_coupon',
		'post_status' => 'publish',
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
		do_action( 'it_exchange_add_coupon_success', $coupon_id, $deprecated );
		return $coupon_id;
	}
	do_action( 'it_exchange_add_coupon_failed', $args );
	return false;
}

/**
 * Register a coupon type if it doesn't already exist
 *
 * Add-ons should call this.
 *
 * @since 0.4.0
 * @since 1.33 Add $class parameter.
 *
 * @param string $type type of coupon
 * @param string $class Model class used to instantiate coupon objects.
 *
 * @throws Exception If invalid coupon class.
*/
function it_exchange_register_coupon_type( $type, $class = 'IT_Exchange_Coupon' ) {

	if ( $class !== 'IT_Exchange_Coupon' && ! is_subclass_of( $class, 'IT_Exchange_Coupon' ) ) {
		throw new Exception( 'Invalid coupon class. Class must extend IT_Exchange_Coupon' );
	}

	if ( empty( $GLOBALS['it_exchange']['coupon_types'] ) ) {
		$GLOBALS['it_exchange']['coupon_types'] = array();
	}

	if ( empty( $GLOBALS['it_exchange']['coupon_types_meta'] ) ) {
		$GLOBALS['it_exchange']['coupon_types_meta'] = array();
	}

	if ( ! in_array( $type, $GLOBALS['it_exchange']['coupon_types'] ) ) {
		$GLOBALS['it_exchange']['coupon_types'][] = $type;
		$GLOBALS['it_exchange']['coupon_types_meta'][$type] = array(
			'class' => $class
		);
	}

	/**
	 * Fires when a coupon type is registered.
	 *
	 * @since 1.33 Add $class parameter.
	 *
	 * @param string $type
	 * @param string $class
	 */
	do_action( 'it_exchange_register_coupon_type', $type, $class );
}

/**
 * Returns a list of all registered coupon types
 *
 * @since 0.4.0
 *
 * @return array
*/
function it_exchange_get_coupon_types() {

	$coupon_types = empty( $GLOBALS['it_exchange']['coupon_types'] ) ? array() : (array) $GLOBALS['it_exchange']['coupon_types'];

	return apply_filters( 'it_exchange_get_coupon_types', $coupon_types );

}

/**
 * Get the model class used for a certain coupon type.
 *
 * @since 1.33
 *
 * @param string $type
 *
 * @return string
 */
function it_exchange_get_coupon_type_class( $type ) {

	if ( empty( $GLOBALS['it_exchange']['coupon_types_meta'][$type] ) ) {
		return 'IT_Exchange_Coupon';
	}

	if ( empty( $GLOBALS['it_exchange']['coupon_types_meta'][$type]['class'] ) ) {
		return 'IT_Exchange_Coupon';
	}

	return $GLOBALS['it_exchange']['coupon_types_meta'][$type]['class'];
}

/**
 * Dow we support a specific type of coupon
 *
 * @since 0.4.0
 *
 * @param string $type the type of coupon
 *
 * @return boolean
*/
function it_exchange_supports_coupon_type( $type ) {
	$types = it_exchange_get_coupon_types();
	return (bool) in_array( $type, $types );
}

/**
 * Return the currently applied coupons
 *
 * We're going to ask the add-ons for this info.
 *
 * @since 0.4.0
 *
 * @param string|bool $type Coupon type to check for. If false, all coupons will be returned.
 *
 * @return IT_Exchange_Coupon[]
*/
function it_exchange_get_applied_coupons( $type=false ) {

	// Get all if type not set
	if ( ! $type ) {
		$applied = array();
		foreach( it_exchange_get_coupon_types() as $type ) {
			if ( $coupons = it_exchange_get_applied_coupons( $type ) )
				$applied = array_merge( $applied, array( $type => $coupons ) );
		}
		return empty( $applied ) ? array() : $applied;
	}

	// If type was set, return just the applied coupons for the type
	return apply_filters( 'it_exchange_get_applied_' . $type . '_coupons', array() );
}

/**
 * Are we accepting any more of the passed coupon type
 *
 * We're going to ask the add-ons for this info. Default is no.
 *
 * @since 0.4.0
 *
 * @param string $type the type of coupon to check for
 *
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
 *
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
 * @param string $type
 * @param string $code
 * @param array  $options
 *
 * @return string
*/
function it_exchange_get_remove_coupon_html( $type, $code, $options = array() ) {
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
 *
 * @return boolean
*/
function it_exchange_apply_coupon( $type, $code, $options=array() ) {
	$options['code'] = $code;
	return apply_filters( 'it_exchange_apply_coupon_to_' . $type, false, $options );
}

/**
 * Remove a coupon
 *
 * We're going to ask the add-ons to do this for us.
 *
 * @since 0.4.0
 *
 * @param string $type the type of coupon to check for
 * @param string $code the coupon code
 * @param array $options
 *
 * @return boolean
*/
function it_exchange_remove_coupon( $type, $code, $options=array() ) {
	$options['code'] = $code;
	return apply_filters( 'it_exchange_remove_coupon_for_' . $type, false, $options );
}

/**
 * Returns the total discount for all applied coupons combined
 *
 * @since 0.4.0
 *
 * @param string|bool $type the type of coupon to check for
 * @param array $options
 *
 * @return int
*/
function it_exchange_get_total_coupons_discount( $type=false, $options=array() ) {
	$defaults = array(
		'format_price' => true,
	);
	$options = ITUtility::merge_defaults( $options, $defaults );

	// Get all if type not set
	if ( ! $type ) {
		$total = 0;
		foreach( it_exchange_get_coupon_types() as $type ) {
			if ( $discount = it_exchange_get_total_coupons_discount( $type, array( 'format_price' => false ) ) ) {
				$total += $discount;
			}
		}

		if ( $options['format_price'] )
			$total = it_exchange_format_price( $total );
		return empty( $total ) ? false : $total;
	}

	return apply_filters( 'it_exchange_get_total_discount_for_' . $type, false, $options );
}

/**
 * Get coupon discount method.
 *
 * Will return false if coupon addon doesn't provide this data
 *
 * @since 0.4.0
 *
 * @deprecated 1.33
 *
 * @param integer $coupon_id the coupon id
 * @param array   $options optional.
 *
 * @return string|bool
*/
function it_exchange_get_coupon_discount_method( $coupon_id, $options=array() ) {

	_deprecated_function( 'it_exchange_get_coupon_discount_method', '1.33' );

	$options['id'] = $coupon_id;

	return apply_filters( 'it_exchange_get_coupon_discount_method', false, $options );
}

/**
 * Get coupon discount label
 *
 * ie: $10.00 / 10%
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Coupon $coupon id or object
 * @param array $options optional
 *
 * @return string
*/
function it_exchange_get_coupon_discount_label( $coupon, $options=array() ) {
	if ( ! $coupon = it_exchange_get_coupon( $coupon ) )
		return '';
	$options['coupon'] = $coupon;
	return apply_filters( 'it_exchange_get_coupon_discount_label', '', $options );
}

/**
 * Returns a summary of the coupon details.
 *
 * We rely on the add-on to give us this data since different add-ons may store the data different.
 *
 * @since 0.4.0
 *
 * @param string $type the slug of the add-on responsible for creating the coupon
 * @param mixed $transaction_coupon
 *
 * @return string
*/
function it_exchange_get_transaction_coupon_summary( $type, $transaction_coupon ) {
	return apply_filters( 'it_exchange_get_transaction_' . $type . '_coupon_summary', __( 'Coupon Data not found:', 'it-l10n-ithemes-exchange' ) . ' ' . $type, $transaction_coupon );
}
