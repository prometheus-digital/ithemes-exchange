<?php
/**
 * API Functions for Product Type Add-ons
 * @package IT_Exchange
 * @since 0.3.1
*/

/**
 * Checks if current post is an Exchange Product
 *
 * @since 1.3.0
 *
 * @return boolean
*/
function it_exchange_is_product( $post=false ) {
	if ( ! $post )
		global $post;

	$product = it_exchange_get_product( $post );
	if ( is_object( $product ) )
		return true;

	return false;
}

/**
 * Grabs the product type of a product
 *
 * @since 0.3.1
 *
 * @param IT_Exchange_Product|int|WP_Post $post
 *
 * @return string the product type
*/
function it_exchange_get_product_type( $post = 0 ) {
	if ( ! $post )
		global $post;

	// Return value from IT_Exchange_Product if we are able to locate it
	$product = it_exchange_get_product( $post );
	if ( is_object( $product ) && ! empty ( $product->product_type ) )
		return apply_filters( 'it_exchange_get_product_type', $product->product_type, $post );

	// Return query arg if is present
	if ( ! empty ( $_GET['it-exchange-product-type'] ) )
		return apply_filters( 'it_exchange_get_product_type', $_GET['it-exchange-product-type'], $post );

	return apply_filters( 'it_exchange_get_product_type', false, $post );
}

/**
 * Returns the name for a registered product-type
 *
 * @since 0.3.2
 * @param string $product_type  slug for the product-type
 *
 * @return string
*/
function it_exchange_get_product_type_name( $product_type ) {
	if ( $addon = it_exchange_get_addon( $product_type ) )
		return apply_filters( 'it_exchange_get_product_type_name', $addon['name'], $product_type );

	return apply_filters( 'it_exchange_get_product_type_name', false, $product_type );
}

/**
 * Returns the options array for a registered product-type
 *
 * @since 0.3.2
 * @param string $product_type  slug for the product-type
 *
 * @return array
*/
function it_exchange_get_product_type_options( $product_type ) {
	if ( $addon = it_exchange_get_addon( $product_type ) )
		return apply_filters( 'it_exchange_get_product_type_options', $addon['options'], $product_type );

	return apply_filters( 'it_exchange_get_product_type_options', false, $product_type );
}

/**
 * Retreives a product object by passing it the WP post object or post id
 *
 * @since 0.3.2
 *
 * @param WP_Post|int|IT_Exchange_Product $post  post object or post id
 *
 * @return IT_Exchange_Product|bool Product object, or false on error.
*/
function it_exchange_get_product( $post ) {

	if ( $post instanceof IT_Exchange_Product ) {
		$product = $post;
	} else {
		try {
			$factory = new IT_Exchange_Product_Factory();
			$product = $factory->make( $post );
		}
		catch ( Exception $e ) {
			return false;
		}
	}

	if ( $product->ID )
		return apply_filters( 'it_exchange_get_product', $product, $post );

	return apply_filters( 'it_exchange_get_product', false, $post );
}

/**
 * Get IT_Exchange_Products
 *
 * @since 0.3.3
 *
 * @param array $args
 * @param int   $total
 *
 * @return IT_Exchange_Product[]  an array of IT_Exchange_Product objects
*/
function it_exchange_get_products( $args = array(), &$total = null ) {
	$defaults = array(
		'numberposts' => 5, 'orderby' => 'date',
		'order' => 'DESC', 'include' => array(),
		'exclude' => array(), 'meta_key' => '',
		'meta_value' =>'', 'post_type' => 'it_exchange_prod',
		'suppress_filters' => true,
		'show_hidden' => false,
		'only_on_sale' => false,
	);
	$args = wp_parse_args( $args, $defaults );
	$args['meta_query'] = empty( $args['meta_query'] ) ? array() : $args['meta_query'];

	if ( ! empty( $args['product_type'] ) ) {
		$meta_query = array(
			'key'   => '_it_exchange_product_type',
			'value' => $args['product_type'],
		);
		$args['meta_query'][] = $meta_query;
		unset( $args['product_type'] ); //remove this so it doesn't conflict with the meta query
	} else { //we only want to get enabled product-type products
		$meta_query = array(
			'key'   => '_it_exchange_product_type',
			'compare' => 'IN',
			'value' => array_keys( it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) ) ),
		);
		$args['meta_query'][] = $meta_query;
	}

	if ( ! $args['show_hidden'] ) {
		$meta_query = array(
			'key'     => '_it-exchange-visibility',
			'value'   => 'hidden',
			'compare' => 'NOT LIKE',
		);
		$args['meta_query'][] = $meta_query;
	}

	if ( $args['only_on_sale'] ) {
		$args['meta_query'][] = array(
			'key'       => '_it_exchange_sale_price',
			'compare'   => 'EXISTS'
		);
	}
	if ( empty( $args['post_status'] ) ) {
		$args['post_status'] = 'publish';
	}

	if ( ! empty( $args['numberposts'] ) && empty( $args['posts_per_page'] ) ) {
		$args['posts_per_page'] = $args['numberposts'];
	}

	if ( ! empty( $args['include'] ) ) {
		$incposts = wp_parse_id_list( $args['include'] );
		$args['posts_per_page'] = count( $incposts );  // only the number of posts included
		$args['post__in'] = $incposts;
	} elseif ( ! empty( $args['exclude'] ) ) {
		$args['post__not_in'] = wp_parse_id_list( $args['exclude'] );
	}

	$args['ignore_sticky_posts'] = true;
	$args['no_found_rows'] = true;

	if ( isset( $args['paged'] ) ) {
		unset( $args['no_found_rows'] );
	}

	if ( func_num_args() === 2 ) {
		unset( $args['no_found_rows'] );
	}

	$query    = new WP_Query( $args );
	$products = array();

	foreach( $query->get_posts() as $key => $product ) {

		$product = it_exchange_get_product( $product );

		$products[ $key ] = $product;

		if ( ! it_exchange_is_product_sale_active( $product ) && $args['only_on_sale'] ) {
			unset( $products[$key] );
		}
	}

	if ( func_num_args() === 2 ) {
		$total = $query->found_posts;
	}

	return apply_filters( 'it_exchange_get_products', $products, $args );
}

/**
 * Sets a global for the current product's id
 *
 * Looks for paramater. If passed param is a vailid product id, it sets that.
 * If passed param is false or not a product id, it looks for global $post.
 * If global $post and passed param are not product ids, it is set to false
 *
 * @since 0.3.8
 * @param int|bool $product_id
 *
 * @return void
*/
function it_exchange_set_the_product_id( $product_id=false ) {

	if ( $product = it_exchange_get_product( $product_id ) )
		$GLOBALS['it_exchange']['product_id'] = $product->ID;
	else
		$GLOBALS['it_exchange']['product_id'] = false;

	do_action( 'it_exchange_set_the_product_id', $product_id );
}

/**
 * Set the global Exchange Product for use in loops
 *
 * @since 1.4.0
 *
 * @param int|WP_Post|IT_Exchange_Product $product
 *
 * @return boolean true if set, false if no product was found/set
*/
function it_exchange_set_product( $product ) {

	$product = it_exchange_get_product( $product );

	if ( $product instanceof IT_Exchange_Product ) {
		$GLOBALS['it_exchange']['product'] = $product;
		it_exchange_set_the_product_id( $product->ID );
		return true;
	}

	$GLOBALS['it_exchange']['product'] = false;
	it_exchange_set_the_product_id( false );

	return false;
}

/**
 * Returns the global for the current product's id
 *
 * @since 0.3.8
 *
 * @return int|bool product id or false
*/
function it_exchange_get_the_product_id() {
	$product_id = empty( $GLOBALS['it_exchange']['product_id'] ) ? false : $GLOBALS['it_exchange']['product_id'];
	return apply_filters( 'it_exchange_get_the_product_id', $product_id );
}

/**
 * Is the product available based on start and end availability dates
 *
 * @since 0.4.0
 *
 * @param int|IT_Exchange_Product $product_id Product ID
 *
 * @return boolean
*/
function it_exchange_is_product_available( $product_id = 0 ) {

	if ( ! $p = it_exchange_get_product( $product_id ) ) {
		return false;
	}

	$past_start_date = true;
	$before_end_date = true;
	$now_start = strtotime( date( 'Y-m-d 00:00:00' ) );
	$now_end = strtotime( date( 'Y-m-d 23:59:59' ) );

	// Check start time
	if ( $p->supports_feature( 'availability', array( 'type' => 'start' ) ) && $p->has_feature( 'availability', array( 'type' => 'start' ) ) ) {
		$start_date = strtotime( $p->get_feature( 'availability', array( 'type' => 'start' ) ) . ' 00:00:00' );

		if ( $now_start < $start_date ) {
			$past_start_date = false;
		}
	}

	// Check end time
	if ( $p->supports_feature( 'availability', array( 'type' => 'end' ) ) && $p->has_feature( 'availability', array( 'type' => 'end' ) ) ) {
		$end_date = strtotime( $p->get_feature( 'availability', array( 'type' => 'end' ) ) . ' 23:59:59' );

		if ( $now_end > $end_date ) {
			$before_end_date = false;
		}
	}

	return $past_start_date && $before_end_date;
}
/**
 * Returns an array of all transactions for a product
 *
 * @since 0.4.2
 *
 * @param IT_Exchange_Product $product                   The product to retrieve purchases for.
 * @param string              $type                      Return value. Accepts 'objects', 'ids', or 'count'.
 * @param bool                $only_cleared_for_delivery Only return transactions that have been cleared. Defaults true.
 *
 * @return IT_Exchange_Transaction[]|int[]|int
*/
function it_exchange_get_transactions_for_product( $product, $type = 'objects', $only_cleared_for_delivery = true ) {

	if ( ! $product = it_exchange_get_product( $product ) ) {
		return $type === 'count' ? 0 : array();
	}

	if ( $type === 'objects' ){
		$return = 'object';
	} elseif ( $type === 'ids' ) {
		$return = 'ID';
	} else {
		$return = $type;
	}

	$args = array(
		'return_value' => $return,
		'items'        => array(
			'product' => $product->ID
		)
	);

	if ( $only_cleared_for_delivery ) {
		$args['cleared'] = true;
	}

	$query = new ITE_Transaction_Query( $args );

	if ( $type === 'count' ) {
		$count = $query->results();

		return apply_filters( 'it_exchange_get_transactions_count_for_product', $count, $product, $type );
	}

	$transactions = array_values( $query->results() );

	return apply_filters( 'it_exchange_get_transactions_for_product', $transactions, $product, $type );
}
