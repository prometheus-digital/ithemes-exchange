<?php
/**
 * Interface for 3rd Party add-ons to implement Coupons
 *
 * @package IT_Exchange
 * @since   0.4.0
 */

/**
 * Returns a list of coupons
 *
 * Options can be sent through to be used with WP's get_posts() funciton.
 *
 * @since 0.4.0
 *
 * @param array $options Options to customize the coupons return.
 * @param int   $total   Is set to the total number of coupons matching the given filters ignoring pagination.
 *
 * @return IT_Exchange_Coupon[]
 */
function it_exchange_get_coupons( $options = array(), &$total = null ) {

	$args = wp_parse_args( $options, array(
		'numberposts'      => 5,
		'orderby'          => 'date',
		'order'            => 'DESC',
		'include'          => array(),
		'exclude'          => array(),
		'meta_key'         => '',
		'meta_value'       => '',
		'post_type'        => 'it_exchange_coupon',
		'suppress_filters' => true,
	) );

	$args['meta_query'] = empty( $args['meta_query'] ) ? array() : $args['meta_query'];

	if ( ! empty( $args['coupon_type'] ) ) {
		$meta_query           = array(
			'key'   => '_it_exchange_coupon_type',
			'value' => $args['coupon_type'],
		);
		$args['meta_query'][] = $meta_query;
		unset( $args['coupon_type'] ); //remove this so it doesn't conflict with the meta query
	}

	if ( empty( $args['post_status'] ) ) {
		$args['post_status'] = 'publish';
	}

	if ( ! empty( $args['numberposts'] ) && empty( $args['posts_per_page'] ) ) {
		$args['posts_per_page'] = $args['numberposts'];
	}

	if ( ! empty( $args['include'] ) ) {
		$incposts               = wp_parse_id_list( $args['include'] );
		$args['posts_per_page'] = count( $incposts );  // only the number of posts included
		$args['post__in']       = $incposts;
	} elseif ( ! empty( $args['exclude'] ) ) {
		$args['post__not_in'] = wp_parse_id_list( $args['exclude'] );
	}

	$args['ignore_sticky_posts'] = true;
	$args['no_found_rows']       = true;

	if ( isset( $args['paged'] ) ) {
		unset( $args['no_found_rows'] );
	}

	if ( func_num_args() === 2 ) {
		unset( $args['no_found_rows'] );
	}

	$query   = new WP_Query( $args );
	$coupons = array();

	foreach ( $query->get_posts() as $key => $post ) {
		$coupon = it_exchange_get_coupon( $post );

		if ( $coupon ) {
			$coupons[ $key ] = $coupon;
		}
	}

	if ( func_num_args() === 2 ) {
		$total = $query->found_posts;
	}

	/**
	 * Filter the coupons matching this query.
	 *
	 * @since 1.0.0
	 *
	 * @param IT_Exchange_Coupon[] $coupons
	 * @param array                $args
	 */
	return apply_filters( 'it_exchange_get_coupons', $coupons, $args );
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
 * @return IT_Exchange_Coupon|false object for passed post
 */
function it_exchange_get_coupon( $post, $type = '' ) {

	if ( ! $post ) {
		return false;
	}

	try {
		if ( $post instanceof IT_Exchange_Coupon ) {
			$coupon = $post;
		} else {

			$post  = get_post( $post );
			$_type = get_post_meta( $post->ID, '_it_exchange_coupon_type', true );
			$type  = $type ?: $_type;

			if ( ! $type ) {
				if ( get_post_meta( $post->ID, '_it-basic-code', true ) ) {
					$type = 'cart';
				}
			}

			// if invalid type, will fall back to IT_Exchange_Coupon
			$class = it_exchange_get_coupon_type_class( $type );

			if ( $type && $class !== 'IT_Exchange_Coupon' && ! $_type ) {
				update_post_meta( $post->ID, '_it_exchange_coupon_type', $type );
			}

			/** @var IT_Exchange_Coupon $coupon */
			$coupon = new $class( $post );
		}
	} catch ( Exception $e ) {
		return false;
	}

	if ( $coupon->get_ID() ) {
		/**
		 * Filter the coupon object.
		 *
		 * @since 1.0.0
		 *
		 * @param IT_Exchange_Coupon $coupon
		 * @param WP_Post            $post
		 */
		return apply_filters( 'it_exchange_get_coupon', $coupon, $post );
	}

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
	 * @since  1.33
	 *
	 * @param IT_Exchange_Coupon|null $coupon
	 *
	 * @pparam string                 $code
	 */
	return apply_filters( 'it_exchange_get_' . $type . '_coupon_from_code', null, $code );
}

/**
 * Adds a coupon post_type to WP
 *
 * @since 0.4.0
 *
 * @param array       $args       same args passed to wp_insert_post plus any additional needed
 * @param object|bool $deprecated deprecated
 *
 * @return mixed post id or false
 */
function it_exchange_add_coupon( $args = array(), $deprecated = false ) {

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
	if ( empty( $args['post_title'] ) ) {
		return false;
	}

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
 * @param string $type  type of coupon
 * @param string $class Model class used to instantiate coupon objects.
 * @param array  $args  Additional arguments.
 *
 * @throws Exception If invalid coupon class.
 */
function it_exchange_register_coupon_type( $type, $class = 'IT_Exchange_Coupon', array $args = array() ) {
	ITE_Coupon_Types::register( new ITE_Coupon_Type( $type, array( 'class' => $class ) ) );
}

/**
 * Returns a list of all registered coupon types
 *
 * @since 0.4.0
 *
 * @return string[]
 */
function it_exchange_get_coupon_types() {

	$types = array_map( function ( ITE_Coupon_Type $type ) { return $type->get_type(); }, ITE_Coupon_Types::all() );

	/**
	 * Filter the registered coupon types.
	 *
	 * @since      1.0.0
	 * @deprecated 2.0.0
	 *
	 * @param string[] $types
	 */
	return apply_filters_deprecated( 'it_exchange_get_coupon_types', array( $types ), '2.0.0' );
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

	if ( ! ITE_Coupon_Types::has( $type ) ) {
		return 'IT_Exchange_Coupon';
	}

	return ITE_Coupon_Types::get( $type )->get_class();
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

	return (bool) in_array( $type, $types, true );
}

/**
 * Return the currently applied coupons
 *
 * We're going to ask the add-ons for this info.
 *
 * @since 0.4.0
 *
 * @param string|bool    $type Coupon type to check for. If false, all coupons will be returned.
 * @param \ITE_Cart|null $cart Cart to retrieve coupons from.
 *
 * @return IT_Exchange_Coupon[]
 */
function it_exchange_get_applied_coupons( $type = false, ITE_Cart $cart = null ) {

	$cart = $cart ?: it_exchange_get_current_cart();

	// Get all if type not set
	if ( ! $type ) {
		$applied = array();
		foreach ( it_exchange_get_coupon_types() as $type ) {
			if ( $coupons = it_exchange_get_applied_coupons( $type ) ) {
				$applied = array_merge( $applied, array( $type => $coupons ) );
			}
		}

		return empty( $applied ) ? array() : $applied;
	}

	// If type was set, return just the applied coupons for the type
	return apply_filters( 'it_exchange_get_applied_' . $type . '_coupons', array(), $cart );
}

/**
 * Are we accepting any more of the passed coupon type
 *
 * We're going to ask the add-ons for this info. Default is no.
 *
 * @since 0.4.0
 * @since 2.0.0 Added $cart parameter.
 *
 * @param string         $type The type of coupon to check for
 * @param \ITE_Cart|null $cart The cart to check against.
 *
 * @return boolean
 */
function it_exchange_accepting_coupon_type( $type, ITE_Cart $cart = null ) {

	$cart = $cart ?: it_exchange_get_current_cart( false );

	if ( ! $cart ) {
		return false;
	}

	/**
	 * Filter whether the cart is accepting coupons of a given type.
	 *
	 * @since 0.4.0
	 * @since 2.0.0 Added $cart parameter.
	 *
	 * @param bool      $accepting
	 * @param \ITE_Cart $cart
	 */
	return (bool) apply_filters( 'it_exchange_accepting_' . $type . '_coupons', false, $cart );
}

/**
 * Retreive the field for applying a coupon type
 *
 * We're going to ask the add-ons for this info. Default is an empty string
 *
 * @since 0.4.0
 *
 * @param string $type the type of coupon to check for
 * @param array  $options
 *
 * @return boolean
 */
function it_exchange_get_coupon_type_apply_field( $type, $options = array() ) {
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
 * @param array  $options
 *
 * @return boolean
 */
function it_exchange_apply_coupon( $type, $code, $options = array() ) {

	$cart = empty( $options['cart'] ) ? it_exchange_get_current_cart() : $options['cart'];

	$options['code'] = $code;
	$valid           = false;

	if ( ( $coupon = it_exchange_get_coupon_from_code( $code, $type ) ) && $coupon->get_type() ) {
		try {
			$item = ITE_Coupon_Line_Item::create( $coupon );
			$cart->add_item( $item );
			$valid = true;
		} catch ( Exception $e ) {

			$cart->get_feedback()->add_error(
				$e->getMessage(),
				isset( $item ) ? $item : null
			);

			if ( $cart->is_current() ) {
				it_exchange_add_message( 'error', $e->getMessage() );
			}

			return false;
		}
	}

	return apply_filters( 'it_exchange_apply_coupon_to_' . $type, $valid, $options, $cart );
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
 * @param array  $options
 *
 * @return boolean
 */
function it_exchange_remove_coupon( $type, $code, $options = array() ) {

	$cart = empty( $options['cart'] ) ? it_exchange_get_current_cart() : $options['cart'];

	$options['code'] = $code;

	if ( ( $coupon = it_exchange_get_coupon_from_code( $code, $type ) ) && $coupon->get_type() ) {
		try {
			$item = ITE_Coupon_Line_Item::create( $coupon );

			if ( ! $cart->remove_item( 'coupon', $item->get_id() ) ) {
				$cart->get_feedback()->add_error(
					__( 'Sorry, the coupon could not be removed.', 'it-l10n-ithemes-exchange' ),
					$item
				);
			}
		} catch ( Exception $e ) {
			$cart->get_feedback()->add_error( $e->getMessage() );

			return false;
		}
	}

	return apply_filters( 'it_exchange_remove_coupon_for_' . $type, false, $options );
}

/**
 * Returns the total discount for all applied coupons combined
 *
 * @since 0.4.0
 *
 * @param string|bool $type the type of coupon to check for
 * @param array       $options
 *
 * @return int
 */
function it_exchange_get_total_coupons_discount( $type = false, $options = array() ) {
	$defaults = array(
		'format_price' => true,
	);
	$options  = ITUtility::merge_defaults( $options, $defaults );

	// Get all if type not set
	if ( ! $type ) {
		$total = 0;
		foreach ( it_exchange_get_coupon_types() as $type ) {
			if ( $discount = it_exchange_get_total_coupons_discount( $type, array( 'format_price' => false ) ) ) {
				$total += $discount;
			}
		}

		if ( $options['format_price'] ) {
			$total = it_exchange_format_price( $total );
		}

		return empty( $total ) ? false : $total;
	}

	return apply_filters( 'it_exchange_get_total_discount_for_' . $type, false, $options );
}

/**
 * Get coupon discount method.
 *
 * Will return false if coupon addon doesn't provide this data
 *
 * @since      0.4.0
 *
 * @deprecated 1.33
 *
 * @param integer $coupon_id the coupon id
 * @param array   $options   optional.
 *
 * @return string|bool
 */
function it_exchange_get_coupon_discount_method( $coupon_id, $options = array() ) {

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
 * @param WP_Post|int|IT_Exchange_Coupon $coupon  id or object
 * @param array                          $options optional
 *
 * @return string
 */
function it_exchange_get_coupon_discount_label( $coupon, $options = array() ) {

	if ( ! $coupon = it_exchange_get_coupon( $coupon ) ) {
		return '';
	}

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
 * @param mixed  $transaction_coupon
 *
 * @return string
 */
function it_exchange_get_transaction_coupon_summary( $type, $transaction_coupon ) {
	$summary = __( 'Coupon Data not found:', 'it-l10n-ithemes-exchange' );

	return apply_filters( 'it_exchange_get_transaction_' . $type . '_coupon_summary', $summary . ' ' . $type, $transaction_coupon );
}
