<?php
/**
 * API Functions for Transaction Method Add-ons
 *
 * In addition to the functions found below, iThemes Exchange offers the following actions related to transactions
 * - it_exchange_save_transaction_unvalidated                       // Runs every time a transaction is saved.
 * - it_exchange_save_transaction_unavalidated-[txn-method] // Runs every time a specific transaction method is saved.
 * - it_exchange_save_transaction                           // Runs every time a transaction is saved if not an
 * autosave and if user has permission to save post
 * - it_exchange_save_transaction-[txn-method]             // Runs every time a specific transaction method is saved if
 * not an autosave and if user has permission to save transaction
 *
 * @package IT_Exchange
 * @since 0.3.3
 */


/**
 * Grabs the transaction method of a transaction
 *
 * @since 0.3.3
 * @param WP_Post|int|IT_Exchange_Transaction|bool $transaction ID or IT_Exchange_Transaction object
 *
 * @return string the transaction method
 */
function it_exchange_get_transaction_method( $transaction = false ) {

	if ( $transaction instanceof IT_Exchange_Transaction )
		return $transaction->get_method();

	if ( ! $transaction && ! empty( $GLOBALS['post'] ) ) {
		$transaction = $GLOBALS['post'];
	}

	// Return value from IT_Exchange_Transaction if we are able to locate it
	$transaction = it_exchange_get_transaction( $transaction );

	if ( is_object( $transaction ) && $transaction->get_method() ) {
		return $transaction->get_method();
	}

	// Return query arg if is present
	if ( ! empty ( $_GET['transaction-method'] ) ) {
		return apply_filters( 'it_exchange_get_transaction_method', $_GET['transaction-method'], $transaction );
	}

	return apply_filters( 'it_exchange_get_transaction_method', false, $transaction );
}

/**
 * Retreives a transaction object by passing it the WP post object or post id
 *
 * @since 0.3.3
 * @param WP_Post|int|IT_Exchange_Transaction $post  post object or post id
 *
 * @return IT_Exchange_Transaction|bool IT_Exchange_Transaction object for passed post
 */
function it_exchange_get_transaction( $post ) {
	if ( $post instanceof IT_Exchange_Transaction )
		return apply_filters( 'it_exchange_get_transaction', $post );

	try {

		$ID = is_object( $post ) ? $post->ID : (int) $post;

		$transaction = IT_Exchange_Transaction::get( $ID );
	} catch ( Exception $e ) {
		return false;
	}

	return apply_filters( 'it_exchange_get_transaction', $transaction );
}

/**
 * Get IT_Exchange_Transactions
 *
 * @since 0.3.3
 *
 * @param array $args
 * @param int   $total
 *
 * @return IT_Exchange_Transaction[]  an array of IT_Exchange_Transaction objects
 */
function it_exchange_get_transactions( $args=array(), &$total = null ) {
	$defaults = array(
		'numberposts' => 5, 'orderby' => 'date',
		'order' => 'DESC', 'include' => array(),
		'exclude' => array(), 'meta_key' => '',
		'meta_value' =>'', 'post_type' => 'it_exchange_tran',
		'suppress_filters' => true
	);

	$args = wp_parse_args( $args, $defaults );
	$args['meta_query'] = empty( $args['meta_query'] ) ? array() : $args['meta_query'];

	// Fold in transaction_method
	if ( ! empty( $args['transaction_method'] ) ) {
		$meta_query = array(
			'key'   => '_it_exchange_transaction_method',
			'value' => $args['transaction_method'],
		);
		$args['meta_query'][] = $meta_query;
	}

	// Fold in transaction_status
	if ( ! empty( $args['transaction_status'] ) ) {
		$meta_query = array(
			'key'   => '_it_exchange_transaction_status',
			'value' => $args['transaction_status'],
		);
		$args['meta_query'][] = $meta_query;
	}

	// Fold in customer
	if ( ! empty( $args['customer_id'] ) ) {
		$meta_query = array(
			'key'   => '_it_exchange_customer_id',
			'value' => $args['customer_id'],
			'type'  => 'NUMERIC',
		);
		$args['meta_query'][] = $meta_query;
	}

	$args = apply_filters( 'it_exchange_get_transactions_get_posts_args', $args );

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

	$query = ITE_Transaction_Query::from_wp_args( $args );

	if ( $query ) {

		if ( empty( $args['no_found_rows'] ) ) {
			$total = $query->total();
		}

		$results = $query->results();

		if ( empty( $args['fields'] ) || $args['fields'] !== 'id=>parent' ) {
			$results = array_values( $results );
		}

		return apply_filters( 'it_exchange_get_transactions', $results, $args );
	}

	$query = new WP_Query( $args );

	if ( $transactions = $query->get_posts() ) {
		if ( empty( $args['fields'] ) || ( $args['fields'] !== 'ids' && $args['fields'] !== 'id=>parent' ) ) {
			foreach ( $transactions as $key => $transaction ) {
				$transactions[ $key ] = it_exchange_get_transaction( $transaction );
			}
		}
	}

	if ( empty( $args['no_found_rows'] ) ) {
		$total = $query->found_posts;
	}

	return apply_filters( 'it_exchange_get_transactions', $transactions, $args );
}

/**
 * Get a transaction by method ID.
 *
 * @since 1.33
 *
 * @param string $method
 * @param string $method_id
 *
 * @return IT_Exchange_Transaction|null
 */
function it_exchange_get_transaction_by_method_id( $method, $method_id ) {
	return IT_Exchange_Transaction::query()->where( array(
		'method' => $method,
		'method_id' => $method_id
	) )->take( 1 )->first();
}

/**
 * Get a transaction by its cart ID.
 *
 * @since 1.32.1
 *
 * @param string $cart_id
 *
 * @return IT_Exchange_Transaction|null
 */
function it_exchange_get_transaction_by_cart_id( $cart_id ) {
	return IT_Exchange_Transaction::query()->where( 'cart_id', '=', $cart_id )->take( 1 )->first();
}

/**
 * Generates the transaction object used by the transaction methods
 *
 * @since 0.4.20
 *
 * @param \ITE_Cart|null $cart
 *
 * @return stdClass|false Transaction object not an IT_Exchange_Transaction
 */
function it_exchange_generate_transaction_object( ITE_Cart $cart = null ) {

	// Verify products exist
	$cart          = $cart ? $cart : it_exchange_get_current_cart();
	$cart_products = $cart->get_items( 'product' );

	if ( $cart->get_items()->count() < 1 ) {
		do_action( 'it_exchange_error-no_products_to_purchase' );
		it_exchange_add_message(
			'error', __( 'You cannot checkout without any items in your cart.', 'it-l10n-ithemes-exchange' )
		);

		return false;
	}

	// Verify cart total is a positive number
	$cart_total    = number_format( it_exchange_get_cart_total( false, array( 'cart' => $cart ) ), 2, '.', '' );
	$cart_sub_total = number_format( it_exchange_get_cart_subtotal( false, array( 'cart' => $cart ) ), 2, '.', '' );

	if ( $cart_total < 0 ) {
		do_action( 'it_exchange_error_negative_cart_total_on_checkout', $cart_total );
		it_exchange_add_message(
			'error',
			__( 'The cart total must be at least $0 for you to checkout. Please try again.', 'it-l10n-ithemes-exchange' )
		);

		return false;
	}

	// Grab default currency
	$settings = it_exchange_get_option( 'settings_general' );
	$currency = $settings['default-currency'];
	unset( $settings );

	$products = array();

	foreach ( $cart_products as $cart_product ) {
		$products[ $cart_product->get_id() ] = array_merge( $cart_product->bc(), array(
			'product_base_price' => $cart_product->get_amount(),
			'product_subtotal'   => $cart_product->get_total(),
			'product_name'       => $cart_product->get_name(),
		) );
	}

	foreach( $products as $key => $product ) {
		$products = apply_filters( 'it_exchange_generate_transaction_object_products', $products, $key, $product );
	}

	// Package it up and send it to the transaction method add-on
	$transaction_object = new stdClass();
	$transaction_object->cart_id                = $cart->get_id();
	$transaction_object->customer_id            = $cart->get_customer() ? $cart->get_customer()->ID : 0;
	$transaction_object->total                  = $cart_total;
	$transaction_object->sub_total              = $cart_sub_total;
	$transaction_object->currency               = $currency;
	$transaction_object->description            = it_exchange_get_cart_description();
	$transaction_object->products               = $products;

	$coupons = array();

	/** @var IT_Exchange_Coupon[] $coupon_objects */
	$coupon_objects = array();

	foreach ( $cart->get_items( 'coupon' ) as $coupon_line_item ) {
		/** @var IT_Exchange_Coupon $coupon */
		$coupon = $coupon_line_item->get_coupon();
		$type   = $coupon->get_type();
		if ( $coupon instanceof IT_Exchange_Coupon ) {
			$coupon_objects[] = $coupon;
			$coupons[$type][] = $coupon->get_data_for_transaction_object();
		} else {
			$coupons[$type][] = $coupon;
		}
	}

	$transaction_object->coupons                = $coupons;
	$transaction_object->coupons_total_discount = $cart->calculate_total( 'coupon' );
	$transaction_object->customer_ip            = it_exchange_get_ip();

	// Back-compat
	$taxes       = $cart->calculate_total( 'tax' );
	$raw_taxes   = apply_filters( 'it_exchange_set_transaction_objet_cart_taxes_raw', 0 );
	$taxes      += $raw_taxes;

	// Tack on Tax information
	$transaction_object->taxes_formated         = it_exchange_format_price( $taxes );
	$transaction_object->taxes_raw              = $taxes;

	// Tack on Shipping and Billing address
	$transaction_object->shipping_address = $cart->get_shipping_address() ? $cart->get_shipping_address()->to_array() : array();

	if ( apply_filters( 'it_exchange_billing_address_purchase_requirement_enabled', false ) ) {
		$transaction_object->billing_address = $cart->get_billing_address()->to_array();
	} else {
		$transaction_object->billing_address = false;
	}

	/** @var ITE_Shipping_Line_Item[]|ITE_Line_Item_Collection $shipping_methods */
	$shipping_methods = $cart->get_items( 'shipping', true )->filter( function( ITE_Shipping_Line_Item $shipping ) {
		return (bool) $shipping->get_aggregate();
	} )->unique( function( ITE_Shipping_Line_Item $shipping ) {
		return $shipping->get_method()->slug;
	} );

	if ( $shipping_methods->count() === 1 ) {
		$shipping_method = $shipping_methods->first()->get_method()->slug;
	} elseif ( $shipping_methods->count() > 1 ) {
		$shipping_method = 'multiple-methods';
	} else {
		$shipping_method = false;
	}

	if ( $shipping_method === 'multiple-methods' ) {
		$multiple_methods = array();

		foreach ( $shipping_methods as $method ) {
			if ( $method->get_aggregate() instanceof ITE_Cart_Product ) {
				$multiple_methods[ $method->get_aggregate()->get_id() ] = $method->get_method()->slug;
			}
		}
	} else {
		$multiple_methods = false;
	}

	// Shipping Method and total
	$transaction_object->shipping_method       = $shipping_method;
	$transaction_object->shipping_method_multi = $multiple_methods;
	$transaction_object->shipping_total        = it_exchange_convert_to_database_number( $cart->calculate_total( 'shipping' ) );

	$transaction_object = apply_filters( 'it_exchange_generate_transaction_object', $transaction_object );

	foreach ( $coupon_objects as $coupon_object ) {
		$coupon_object->use_coupon( $transaction_object );
	}

	return $transaction_object;
}

/**
 * Update a transient transaction, default expiry set to 4 hours
 *
 * @since CHANGEME
 * @param string $method name of method that created the transient
 * @param string $temp_id temporary transaction ID created by the transient
 * @param int|bool $customer_id ID of current customer
 * @param stdClass $transaction_object Object used to pass to transaction methods
 * @param string|bool $transaction_id Transaction ID of real transaction or false if no real transaction created yet
 *
 * @return bool true or false depending on success
 */
function it_exchange_update_transient_transaction( $method, $temp_id, $customer_id = false, $transaction_object, $transaction_id = false ) {

	$expires = current_time( 'timestamp' ) + apply_filters( 'it_exchange_transient_transaction_expiry', 60 * 60 * 4 );

    update_option( 'ite_temp_tnx_expires_' . $method . '_' . $temp_id, $expires, false );
    update_option( 'ite_temp_tnx_' . $method . '_' . $temp_id, array(
		    'customer_id' => $customer_id,
		    'transaction_object' => $transaction_object,
		    'transaction_id' => $transaction_id
    ), false );
    return true;
}

/**
 * Add a transient transaction
 *
 * @since 0.4.20
 *
 * @deprecated
 *
 * @param string $method name of method that created the transient
 * @param string $temp_id temporary transaction ID created by the transient
 * @param int|bool $customer_id ID of current customer
 * @param stdClass $transaction_object Object used to pass to transaction methods
 *
 * @return bool true or false depending on success
 */
function it_exchange_add_transient_transaction( $method, $temp_id, $customer_id = false, $transaction_object ) {

	_deprecated_function( 'it_exchange_add_transient_transaction', '1.31', 'it_exchange_update_transient_transaction' );

	return it_exchange_update_transient_transaction( $method, $temp_id, $customer_id, $transaction_object );
}

/**
 * Gets a transient transaction
 *
 * @since 0.4.20
 * @param string $method name of method that created the transient
 * @param string $temp_id temporary transaction ID created by the transient
 *
 * @return array|bool of customer_id and transaction_object False if expired.
 */
function it_exchange_get_transient_transaction( $method, $temp_id ) {
	$expires = get_option( 'ite_temp_tnx_expires_' . $method . '_' . $temp_id, false );
	$txn_details = get_option( 'ite_temp_tnx_' . $method . '_' . $temp_id, false );
	$now = current_time( 'timestamp' );
	if ( !empty( $txn_details ) && $now > intval( $expires ) ) {
		it_exchange_delete_transient_transaction( $method, $temp_id );
		return false;
	}
	return $txn_details;
}

/**
 * Deletes a transient transaction
 *
 * @since 0.4.20
 * @param string $method name of method that created the transient
 * @param string $temp_id temporary transaction ID created by the transient
 *
 * @return bool true or false depending on success
 */
function it_exchange_delete_transient_transaction( $method, $temp_id ) {
	delete_option( 'ite_temp_tnx_expires_' . $method . '_' . $temp_id );
	delete_option( 'ite_temp_tnx_' . $method . '_' . $temp_id );
	return true;
}

/**
 * Adds a transaction post_type to WP
 *
 * @since 0.3.3
 *
 * @param string $method Transaction method (e.g. paypal, stripe, etc)
 * @param string $method_id ID from transaction method
 * @param string $status Transaction status
 * @param IT_Exchange_Customer|int|ITE_Cart $customer_or_cart Customer or Cart
 * @param stdClass|ITE_Cart $cart_object Cart or Transaction object {@see it_exchange_generate_transaction_object()}
 * @param array $args same args passed to wp_insert_post plus any additional needed
 *
 * @return int|false Transaction ID or false on failure
 */
function it_exchange_add_transaction( $method, $method_id, $status = 'pending', $customer_or_cart = 0, $cart_object = null, $args = array() ) {
	$defaults = array(
		'post_type'          => 'it_exchange_tran',
		'post_status'        => 'publish',
		'payment_token'      => 0,
	);
	$args = wp_parse_args( $args, $defaults );

	/** @var ITE_Gateway_Card $card */
	$card          = empty( $args['card'] ) ? null : $args['card'];
	$payment_token = empty( $args['payment_token'] ) ? 0 : $args['payment_token'];

	unset( $args['payment_token'], $args['card'] );

	if ( $customer_or_cart instanceof ITE_Cart ) {
		$cart_object = $customer_or_cart;
		$customer    = $customer_or_cart->get_customer();
	} else {
		$customer = $customer_or_cart;
	}

	if ( $cart_object instanceof ITE_Cart ) {
		$cart        = $cart_object;
		$cart_object = it_exchange_generate_transaction_object( $cart );
	} else {
		$cart = it_exchange_get_current_cart( false );
	}

	if ( $customer ) {
		$customer = it_exchange_get_customer( $customer );
	} elseif ( $cart ) {
		$customer = $cart->get_customer();
	} else {
		$customer = it_exchange_get_current_customer();
	}

	if ( ! $cart_object ) {
		throw new InvalidArgumentException( 'Either a \ITE_Cart or cart object must be provided.' );
	}

	if ( it_exchange_get_transaction_by_method_id( $method, $method_id ) ) {

		do_action( 'it_exchange_add_transaction_failed', $method, $method_id, $status, $customer, $cart_object, $args );

		return apply_filters( 'it_exchange_add_transaction', false, $method, $method_id, $status, $customer, $cart_object, $args );
	}

	// If we don't have a title, create one
	if ( empty( $args['post_title'] ) )
		$args['post_title'] = $method . '-' . $method_id . '-' . date_i18n( 'Y-m-d-H:i:s' );

	if ( $transaction_id = wp_insert_post( $args ) ) {

		$customer_ip = ! empty( $cart_object->customer_ip ) ? $cart_object->customer_ip : it_exchange_get_ip();

		update_post_meta( $transaction_id, '_it_exchange_customer_ip', $customer_ip );
		update_post_meta( $transaction_id, '_it_exchange_cart_object', $cart_object );

		if ( $customer instanceof IT_Exchange_Guest_Customer ) {
			update_post_meta( $transaction_id, '_it-exchange-is-guest-checkout', 1 );
		}

		$hash = it_exchange_generate_transaction_hash( $transaction_id, $customer ? $customer->id  : false );

		/** @var mixed $cart_object */
		if ( isset( $cart_object->cart_id ) ) {
			$cart_id = $cart_object->cart_id;
		} elseif ( $cart ) {
			$cart_id = $cart->get_id();
		} else {
			$cart_id = null;
		}

		$gateway = ITE_Gateways::get( $method );

		if ( $gateway && $gateway->is_sandbox_mode() ) {
			$mode = IT_Exchange_Transaction::P_MODE_SANDBOX;
		} elseif ( $gateway && ! $gateway->is_sandbox_mode() ) {
			$mode = IT_Exchange_Transaction::P_MODE_LIVE;
		} else {
			$mode = '';
		}

		$purchase_args = array(
			'ID'            => $transaction_id,
			'status'        => $status,
			'method'        => $method,
			'method_id'     => $method_id,
			'cart_id'       => $cart_id,
			'total'         => isset( $cart_object->total ) ? $cart_object->total : 0,
			'subtotal'      => isset( $cart_object->sub_total ) ? $cart_object->sub_total : 0,
			'order_date'    => get_post( $transaction_id )->post_date_gmt,
			'hash'          => $hash,
			'purchase_mode' => $mode,
		);

		if ( $payment_token ) {
			$purchase_args['payment_token'] = $payment_token;
		}

		if ( $card ) {
			$purchase_args['card_redacted'] = $card->get_redacted_number();
			$purchase_args['card_month']    = $card->get_expiration_month();
			$purchase_args['card_year']     = $card->get_expiration_year();
		}

		if ( $customer ) {
			if ( is_numeric( $customer->id ) ) {
				$purchase_args['customer_id'] = $customer->id;
			}

			$purchase_args['customer_email'] = $customer->get_email();
		}

		$transaction = IT_Exchange_Transaction::create( $purchase_args );

		if ( $customer instanceof IT_Exchange_Customer ) {
			$customer->add_transaction_to_user( $transaction_id );
		}

		$repo = new ITE_Line_Item_Transaction_Repository( new ITE_Line_Item_Repository_Events(), $transaction );

		if ( $cart ) {
			/** @var ITE_Cart_Product $product */
			foreach ( $cart->get_items( 'product' ) as $product ) {
				$product->get_product()->add_transaction_to_product( $transaction_id );
			}

			$cart->get_items()->flatten()->freeze();
			$cart->with_new_repository( $repo );
		} elseif ( ! empty( $cart_object->products ) ) {
			foreach ( $cart_object->products as $product ) {
				$product = new IT_Exchange_Product( $product['product_id'] );
				$product->add_transaction_to_product( $transaction_id );
			}
		}

		if ( ! $cart && ! empty( $cart_object->billing_address ) && is_array( $cart_object->billing_address ) ) {
			$repo->set_billing_address( new ITE_In_Memory_Address( $cart_object->billing_address ) );
		}

		if ( ! $cart && ! empty( $cart_object->shipping_address ) && is_array( $cart_object->shipping_address ) ) {
			$repo->set_shipping_address( new ITE_In_Memory_Address( $cart_object->shipping_address ) );
		}

		if ( $transaction->is_cleared_for_delivery() ) {
			$transaction->set_attribute( 'cleared', true );
			$transaction->save();
		}

		if ( ! $cart ) {
			_deprecated_argument(
				__FUNCTION__, 'cart_object',
				__( '$cart_object must be instance of \ITE_Cart or current cart must be available.', 'it-l10n-ithemes-exchange' )
			);
			$transaction->convert_cart_object();
		}

		do_action( 'it_exchange_add_transaction_success', $transaction_id, $cart );

		$r = apply_filters( 'it_exchange_add_transaction', $transaction_id, $method, $method_id, $status, $customer, $cart_object, $args );

		if ( $cart ) {
			if ( ( $g = ITE_Gateways::get( $method ) ) && ! $g->requires_cart_after_purchase() ) {
				if ( $cart->get_repository() instanceof ITE_Line_Item_Session_Repository ) {
					$model = ITE_Session_Model::from_cart_id( $cart->get_id() );

					if ( $model ) {
						$model->delete();
					}
				}
			}

			$cart->destroy();
		}

		return $r;
	}

	do_action( 'it_exchange_add_transaction_failed', $method, $method_id, $status, $customer, $cart_object, $args );

	return apply_filters( 'it_exchange_add_transaction', false, $method, $method_id, $status, $customer, $cart_object, $args);
}

/**
 * Adds a transaction post_type to WP
 * Slimmed down "child" of a parent transaction
 *
 * @since 1.3.0
 * @param string $method Transaction method (e.g. paypal, stripe, etc)
 * @param string $method_id ID from transaction method
 * @param string $status Transaction status
 * @param int|ITE_Cart $customer_or_cart Customer ID
 * @param int $parent_tx_id Parent Transaction ID
 * @param stdClass|array $txn_object_or_args really just a dummy array to store the price information
 * @param array $args same args passed to wp_insert_post plus any additional needed
 *
 * @return int|bool post id or false
 */
function it_exchange_add_child_transaction( $method, $method_id, $status = 'pending', $customer_or_cart, $parent_tx_id, $txn_object_or_args = null, $args = array() ) {
	$defaults = array(
		'post_type'          => 'it_exchange_tran',
		'post_status'        => 'publish',
	);
	$args = wp_parse_args( $args, $defaults );

	if ( $customer_or_cart instanceof ITE_Cart ) {
		$cart        = $customer_or_cart;
		$customer    = $customer_or_cart->get_customer();
		$txn_object_or_args = it_exchange_generate_transaction_object( $customer_or_cart );
	} else {
		$customer = it_exchange_get_customer( $customer_or_cart );
		$cart     = null;
	}

	$customer_id = $customer ? $customer->get_ID() : 0;

	if ( is_array( $txn_object_or_args ) ) {
		$args = $txn_object_or_args;
	}

	/** @var ITE_Gateway_Card $card */
	$card          = empty( $args['card'] ) ? null : $args['card'];
	$payment_token = empty( $args['payment_token'] ) ? 0 : $args['payment_token'];

	unset( $args['payment_token'], $args['card'] );

	// If we don't have a title, create one
	if ( empty( $args['post_title'] ) )
		$args['post_title'] = $method . '-' . $method_id . '-' . date_i18n( 'Y-m-d-H:i:s' );

	$args['post_parent'] = $parent_tx_id;

	if ( $transaction_id = wp_insert_post( $args ) ) {

		update_post_meta( $transaction_id, '_it_exchange_cart_object', $txn_object_or_args );

		$gateway = ITE_Gateways::get( $method );

		if ( $gateway && $gateway->is_sandbox_mode() ) {
			$mode = IT_Exchange_Transaction::P_MODE_SANDBOX;
		} elseif ( $gateway && ! $gateway->is_sandbox_mode() ) {
			$mode = IT_Exchange_Transaction::P_MODE_LIVE;
		} else {
			$mode = '';
		}

		$purchase_args = array(
			'ID'            => $transaction_id,
			'status'        => $status,
			'method'        => $method,
			'method_id'     => $method_id,
			'cart_id'       => isset( $txn_object_or_args->cart_id ) ? $txn_object_or_args->cart_id : '',
			'total'         => isset( $txn_object_or_args->total ) ? $txn_object_or_args->total : 0,
			'subtotal'      => isset( $txn_object_or_args->sub_total ) ? $txn_object_or_args->sub_total : 0,
			'order_date'    => current_time( 'mysql', true ),
			'parent'        => $parent_tx_id,
			'hash'          => it_exchange_generate_transaction_hash( $transaction_id, $customer_id ),
			'mode'          => $mode,
		);

		if ( $customer instanceof IT_Exchange_Guest_Customer ) {
			$purchase_args['customer_email'] = $customer->get_email();
		} elseif ( $customer ) {
			$purchase_args['customer_id'] = $customer_id;
		}

		if ( $payment_token ) {
			$purchase_args['payment_token'] = $payment_token;
		}

		if ( $card ) {
			$purchase_args['card_redacted'] = $card->get_redacted_number();
			$purchase_args['card_month']    = $card->get_expiration_month();
			$purchase_args['card_year']     = $card->get_expiration_year();
		}

		$transaction = IT_Exchange_Transaction::create( $purchase_args );

		if ( $cart ) {
			$cart->get_items()->flatten()->freeze();
			$cart->with_new_repository( new ITE_Line_Item_Transaction_Repository( new ITE_Line_Item_Repository_Events(), $transaction ) );
		}

		do_action( 'it_exchange_add_child_transaction_success', $transaction_id );

		$r = apply_filters(
			'it_exchange_add_child_transaction', $transaction_id, $method, $method_id, $status, $customer_id, $parent_tx_id, $txn_object_or_args, $args
		);

		if ( $cart ) {
			if ( ( $g = ITE_Gateways::get( $method ) ) && ! $g->requires_cart_after_purchase() ) {
				if ( $cart->get_repository() instanceof ITE_Line_Item_Session_Repository ) {
					$model = ITE_Session_Model::from_cart_id( $cart->get_id() );

					if ( $model ) {
						$model->delete();
					}
				}
			}

			$cart->destroy();
		}

		return $r;
	}

	do_action( 'it_exchange_add_child_transaction_failed', $method, $method_id, $status, $customer_id, $parent_tx_id, $txn_object_or_args, $args );

	return apply_filters( 'it_exchange_add_child_transaction', false, $method, $method_id, $status, $customer_id, $parent_tx_id, $txn_object_or_args, $args );
}

/**
 * Generates a unique transaction ID for receipts
 *
 * @since 0.4.0
 *
 * @param integer   $transaction_id the wp_post ID for the transaction
 * @param integer  $customer_id the wp_users ID for the customer
 *
 * @return string
 */
function it_exchange_generate_transaction_hash( $transaction_id, $customer_id ) {

	// Targeted hash
	$hash = wp_hash( time() . $transaction_id . $customer_id );

	if ( it_exchange_get_transaction_id_from_hash( $hash ) ) {
		$hash = it_exchange_generate_transaction_hash( $transaction_id, $customer_id );
	}

	return apply_filters( 'it_exchange_generate_transaction_hash', $hash, $transaction_id, $customer_id );
}

/**
 * Return the transaction ID provided by the gateway (transaction method)
 *
 * @since 0.4.0
 *
 * @deprecated 2.0.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return string|void
 */
function it_exchange_get_gateway_id_for_transaction( $transaction ) {

	_deprecated_function( __FUNCTION__, '2.0.0', 'it_exchange_get_transaction_method_id' );

	if ( ! $transaction = it_exchange_get_transaction( $transaction ) )
		return;

	$gateway_transaction_id = $transaction->get_method_id();
	return apply_filters( 'it_exchange_get_gateway_id_for_transaction', $gateway_transaction_id, $transaction );
}

/**
 * Returns a transaction ID based on the hash
 *
 * @since 0.4.0
 *
 * @param string $hash
 *
 * @return integer transaction id
 */
function it_exchange_get_transaction_id_from_hash( $hash ) {

	$transaction = IT_Exchange_Transaction::query()->where( 'hash', '=', $hash )->take( 1 )->first();

	$ID = $transaction ? $transaction->ID : false;

	if ( ! $ID ) {
		global $wpdb;

		$ID = $wpdb->get_var( $wpdb->prepare(
			"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s LIMIT 1;", '_it_exchange_transaction_hash', $hash
		) );
	}

	return apply_filters( 'it_exchange_get_transaction_id_from_hash', $ID, $hash );
}

/**
 * Returns the transaction hash from an ID
 *
 * @since 0.4.0
 *
 * @param integer $id transaction_id
 *
 * @return string|bool ID or false
 */
function it_exchange_get_transaction_hash( $id ) {

	if ( ! $transaction = it_exchange_get_transaction( $id ) ) {
		return false;
	}

	return apply_filters( 'it_exchange_get_transaction_hash', $transaction->hash, $id );
}

/**
 * Updates a transaction
 *
 * @since 0.3.3
 *
 * @deprecated 1.35
 *
 * @param array $args transaction args. Must include ID of a valid transaction post
 *
 * @return WP_Post transaction object
 */
function it_exchange_update_transaction( $args ) {

	_deprecated_function( 'it_exchange_update_transaction', '1.35' );

	$id = empty( $args['id'] ) ? false : $args['id'];
	$id = ( empty( $id ) && ! empty( $args['ID'] ) ) ? $args['ID']: $id;

	if ( 'it_exchange_tran' != get_post_type( $id ) )
		return false;

	$args['ID'] = $id;

	$result = wp_update_post( $args );
	$transaction_method = it_exchange_get_transaction_method( $id );

	do_action( 'it_exchange_update_transaction', $args );
	do_action( 'it_exchange_update_transaction_' . $transaction_method, $args );

	if ( ! empty( $args['_it_exchange_transaction_status'] ) )
		it_exchange_update_transaction_status( $id, $args['_it_exchange_transaction_status'] );

	return $result;
}

/**
 * Updates the transaction status of a transaction
 *
 * @since 0.3.3
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction the transaction id or object
 * @param string $status the new transaction status
 *
 * @return string|false
 */
function it_exchange_update_transaction_status( $transaction, $status ) {

	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		$transaction->update_status( $status );

		return $transaction->get_status();
	}

	return false;
}

/**
 * Returns the transaction status for a specific transaction
 *
 * @since 0.3.3
 * @param WP_Post|int|IT_Exchange_Transaction $transaction the transaction id or object
 *
 * @return string|false the transaction status
 */
function it_exchange_get_transaction_status( $transaction ) {

	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		return $transaction->get_status();
	}

	return false;
}

/**
 * Grab a list of all possible transactions stati
 *
 * @since 0.4.11
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction transaction id or object
 *
 * @return array
 */
function it_exchange_get_status_options_for_transaction( $transaction ) {

	if ( ! $method = it_exchange_get_transaction_method( $transaction ) ) {
		return array();
	}

	return apply_filters( 'it_exchange_get_status_options_for_' . $method . '_transaction', array(), $transaction );
}

/**
 * Return the default transaction status for a transaction
 *
 * Leans on transaction methods to do the work
 *
 * @since 0.4.11
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction id or object
 *
 * @return string|bool Status or false
 */
function it_exchange_get_default_transaction_status( $transaction ) {

	if ( ! $method = it_exchange_get_transaction_method( $transaction ) ) {
		return false;
	}

	return apply_filters( 'it_exchange_get_default_transaction_status_for_' . $method, false );
}

/**
 * Returns the label for a transaction status (provided by addon)
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction transaction object or ID
 * @param array $options
 *
 * @return string
 */
function it_exchange_get_transaction_status_label( $transaction, $options=array() ){
	$transaction = it_exchange_get_transaction( $transaction );

	if ( ! $transaction ) {
		return '';
	}

	$defaults = array(
		'status' => it_exchange_get_transaction_status( $transaction ),
	);
	$options = ITUtility::merge_defaults( $options, $defaults );

	return apply_filters( 'it_exchange_transaction_status_label_' . $transaction->get_method(), $options['status'], $options );
}

/**
 * Returns the instructions for a transaction instructions (provided by addon)
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction transaction object or ID
 *
 * @return string
 */
function it_exchange_get_transaction_instructions( $transaction ) {

	$transaction = it_exchange_get_transaction( $transaction );

	if ( $transaction ) {
		return apply_filters( 'it_exchange_transaction_instructions_' . $transaction->get_method(), '' );
	}

	return '';
}

/**
 * Return the transaction date
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param string|bool  $format php date format
 * @param boolean $gmt return the gmt date?
 *
 * @return string date
 */
function it_exchange_get_transaction_date( $transaction, $format = false, $gmt = false ) {
	$format = empty( $format ) ? get_option( 'date_format' ) : $format;

	// Try to locate the IT_Exchange_Transaction object from the var
	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		$date      = $transaction->get_date( $gmt );
		$formatted = date_i18n( $format, strtotime( $date ), $gmt );

		return apply_filters( 'it_exchange_get_transaction_date', $formatted, $transaction, $format, $gmt );
	}

	return apply_filters( 'it_exchange_get_transaction_date', false, $transaction, $format, $gmt );
}

/**
 * Return the transaction subtotal
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param bool $format_currency Format the price or just the raw value
 *
 * @return string date
 */
function it_exchange_get_transaction_subtotal( $transaction, $format_currency = true ) {

	// Try to locate the IT_Exchange_Transaction object from the var
	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		$subtotal = $transaction->get_subtotal();

		return $format_currency ? it_exchange_format_price( $subtotal ) : $subtotal;
	}

	return apply_filters( 'it_exchange_get_transaction_subtotal', false, $transaction, $format_currency );
}

/**
 * Return the transaction total
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param boolean $format_currency format the price?
 * @param boolean $subtract_refunds if refunds are present, subtract the difference?
 *
 * @return string total
 */
function it_exchange_get_transaction_total( $transaction, $format_currency = true, $subtract_refunds = true ) {
	// Try to locate the IT_Exchange_Transaction object from the var
	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		$total = $transaction->get_total( $subtract_refunds );

		return $format_currency ? it_exchange_format_price( $total ) : $total;
	}

	return apply_filters( 'it_exchange_get_transaction_total', false, $transaction, $format_currency, $subtract_refunds );
}

/**
 * Return the currency used in the transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction   $transaction ID or object
 *
 * @return string|bool Currency
 */
function it_exchange_get_transaction_currency( $transaction ) {

	// Try to locate the IT_Exchange_Transaction object from the var
	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		return $transaction->get_currency();
	}

	return apply_filters( 'it_exchange_get_transaction_currency', false, $transaction );
}

/**
 * Returns an array of all coupons applied to a given transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction   $transaction ID or object
 *
 * @return string date
 */
function it_exchange_get_transaction_coupons( $transaction ) {

	// Try to locate the IT_Exchange_Transaction object from the var
	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		return $transaction->get_coupons();
	}

	return apply_filters( 'it_exchange_get_transaction_coupons', false, $transaction );
}

/**
 * Return the total discount of all coupons applied to a given transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param bool $format Format the price
 *
 * @return string date
 */
function it_exchange_get_transaction_coupons_total_discount( $transaction, $format = true ) {

	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {

		$discount = $transaction->get_coupons_total_discount();

		return $format ? it_exchange_format_price( $discount ) : $discount;
	}

	return apply_filters( 'it_exchange_get_transaction_coupons_total_discount', false, $transaction, $format );
}

/**
 * Whether the given transaction can be refunded.
 *
 * @since 2.0.0
 *
 * @param IT_Exchange_Transaction|int $transaction
 *
 * @return bool
 */
function it_exchange_transaction_can_be_refunded( $transaction ) {

	$transaction = it_exchange_get_transaction( $transaction );

	if ( ! $transaction ) {
		return false;
	}

	$gateway = ITE_Gateways::get( $transaction->get_method() ) ;

	if ( ! $gateway ) {
		return false;
	}

	if ( ! $gateway->can_handle( 'refund' ) ) {
		return false;
	}

	if ( $transaction->get_total() <= 0 ) {
		return false;
	}


	/**
	 * Filter whether this transaction can be refunded.
	 *
	 * @since 2.0.0
	 *
	 * @param bool                     $eligible
	 * @param \IT_Exchange_Transaction $transaction
	 */
	$eligible = apply_filters( 'it_exchange_transaction_can_be_refunded', true, $transaction );

	/**
	 * Filter whether this transaction can be refunded.
	 *
	 * The dynamic portion of this hook refers to the transaction method slug.
	 *
	 * @since 2.0.0
	 *
	 * @param bool                     $eligible
	 * @param \IT_Exchange_Transaction $transaction
	 */
	return apply_filters( "it_exchange_{$transaction->get_method()}_transaction_can_be_refunded", $eligible, $transaction );
}

/**
 * Adds a refund to a transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction transaction ID or object
 * @param string $amount
 * @param bool|string $date Date in Y-m-d H:i:s format
 * @param array $options
 */
function it_exchange_add_refund_to_transaction( $transaction, $amount, $date = false, $options = array() ) {

	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		$transaction->add_refund( $amount, $date, $options );
	}
}

/**
 * Grab refunds for a transaction
 *
 * @since 0.4.0
 *
 * @deprecated 2.0.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return array|false
 */
function it_exchange_get_transaction_refunds( $transaction ) {

	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		return $transaction->get_transaction_refunds();
	}

	return apply_filters_deprecated( 'it_exchange_get_transaction_refunds', array( false, $this ), '2.0.0' );
}

/**
 * Checks if there are refunds for a transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return bool
 */
function it_exchange_has_transaction_refunds( $transaction ) {

	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		return $transaction->has_refunds();
	}

	return apply_filters( 'it_exchange_has_transaction_refunds', false, $transaction );
}

/**
 * Returns the a sum of all the applied refund amounts for this transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param bool $format Format the price
 *
 * @return float|string
 */
function it_exchange_get_transaction_refunds_total( $transaction, $format = true ) {

	$transaction = it_exchange_get_transaction( $transaction );

	if ( ! $transaction ) {
		return 0.00;
	}

	$total = $transaction->get_refund_total();

	return $format ? it_exchange_format_price( $total ) : $total;
}

/**
 * Returns the transaction description
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return string
 */
function it_exchange_get_transaction_description( $transaction ) {

	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		return $transaction->get_description();
	}

	return apply_filters( 'it_exchange_get_transaction_description', __( 'Unknown', 'it-l10n-ithemes-exchange' ), $transaction );
}

/**
 * Returns the customer object associated with a transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return IT_Exchange_Customer|false
 */
function it_exchange_get_transaction_customer( $transaction ) {

	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		return $transaction->get_customer();
	}

	return apply_filters( 'it_exchange_get_transaction_customer', false, $transaction );
}

/**
 * Returns the transaction customer's Display Name
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return string
 */
function it_exchange_get_transaction_customer_display_name( $transaction ) {
	$unknown = __( 'Deleted Customer', 'it-l10n-ithemes-exchange' );

	if ( $customer = it_exchange_get_transaction_customer( $transaction ) ) {
		$display_name = $customer->get_display_name();
		return apply_filters( 'it_exchange_get_transaction_customer_display_name', $display_name, $transaction );
	}

	return apply_filters( 'it_exchange_get_transaction_customer_display_name', $unknown, $transaction );
}

/**
 * Returns the transaction customer's ID
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return int|false
 */
function it_exchange_get_transaction_customer_id( $transaction ) {
	$unknown = 0;

	if ( $customer = it_exchange_get_transaction_customer( $transaction ) ) {
		$ID = empty( $customer->wp_user->ID ) ? $unknown : $customer->wp_user->ID;

		return apply_filters( 'it_exchange_get_transaction_customer_id', $ID, $transaction );
	}

	return apply_filters( 'it_exchange_get_transaction_customer_id', $unknown, $transaction );
}

/**
 * Returns the transaction customer's email
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return string
 */
function it_exchange_get_transaction_customer_email( $transaction ) {
	$unknown = __( 'Unknown', 'it-l10n-ithemes-exchange' );

	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		return $transaction->get_customer_email();
	}

	return apply_filters( 'it_exchange_get_transaction_customer_email', $unknown, $transaction );
}

/**
 * Returns the transaction customer's IP Address
 *
 * @since 1.11.5
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param bool                                $label Label or raw value.
 *
 * @return string
 */
function it_exchange_get_transaction_customer_ip_address( $transaction, $label = true ) {

	$transaction = it_exchange_get_transaction( $transaction );

	if ( ! $transaction ) {
		return false;
	}

	$return = $label ? __( 'IP Address: %s', 'it-l10n-ithemes-exchange' ) : '%s';

	$ip = $transaction->get_customer_ip();

	if ( ! empty( $ip ) ) {
		return sprintf( $return, $ip );
	}

	return sprintf( $return, __( 'Unknown', 'it-l10n-ithemes-exchange' ) );
}

/**
 * Returns the transaction customer's profile URL
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param array $options
 *
 * @return string
 */
function it_exchange_get_transaction_customer_admin_profile_url( $transaction, $options=array() ) {

	if ( ! $customer = it_exchange_get_transaction_customer( $transaction ) ) {
		return false;
	}

	$defaults = array(
		'tab' => 'transactions',
	);
	$options = ITUtility::merge_defaults( $options, $defaults );

	$url = add_query_arg( array(
		'user_id' => $customer->id,
		'it_exchange_customer_data' => 1,
		'tab' => $options['tab'] ),
		admin_url( 'user-edit.php' ) );

	return apply_filters( 'it_exchange_get_transaction_customer_admin_profile_url', $url, $transaction, $options );
}

/**
 * Get Transaction Order Number
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction id or object
 * @param string $prefix What to prefix the order number with, defaults to #
 *
 * @return string
 */
function it_exchange_get_transaction_order_number( $transaction, $prefix='#' ) {

	if ( ! $transaction = it_exchange_get_transaction( $transaction ) ) {
		return false;
	}

	return $transaction->get_order_number( $prefix );
}

/**
 * Returns the shipping addresss saveed with the transaction
 *
 * @since 1.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction transaction object or ID
 *
 * @return array|false shipping address
 */
function it_exchange_get_transaction_shipping_address( $transaction ) {

	if ( ! $transaction = it_exchange_get_transaction( $transaction ) ) {
		return false;
	}

	$address = $transaction->get_shipping_address();

	return $address ? $address->to_array() : false;
}

/**
 * Returns the billing addresss saveed with the transaction
 *
 * @since 1.3.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction transaction object or ID
 *
 * @return array|false billing address
 */
function it_exchange_get_transaction_billing_address( $transaction ) {

	if ( ! $transaction = it_exchange_get_transaction( $transaction ) ) {
		return false;
	}

	$address = $transaction->get_billing_address();

	return $address ? $address->to_array() : false;
}

/**
 * Returns an array of product objects as they existed when added to the transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction id or object
 *
 * @return array
 */
function it_exchange_get_transaction_products( $transaction ) {

	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		return $transaction->get_products();
	}

	return apply_filters( 'it_exchange_get_transaction_products', array(), $transaction );
}

/**
 * Returns a specific product from a transaction based on the product_cart_id
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction
 * @param string $product_cart_id
 *
 * @return object
 */
function it_exchange_get_transaction_product( $transaction, $product_cart_id ) {

	if ( $products = it_exchange_get_transaction_products( $transaction ) ) {

		$product = empty( $products[ $product_cart_id ] ) ? false : $products[ $product_cart_id ];

		return apply_filters( 'it_exchange_get_transaction_product', $product, $transaction, $product_cart_id );
	}

	return apply_filters( 'it_exchange_get_transaction_product', false, $transaction, $product_cart_id );
}

/**
 * Returns data from the transaction product
 *
 * @since 0.4.0
 *
 * @param array $product
 * @param string $feature
 *
 * @return mixed
 */
function it_exchange_get_transaction_product_feature( $product, $feature ) {

	if ( 'title' === $feature || 'name' === $feature )
		$feature = 'product_name';

	$feature_value = isset( $product[$feature] ) ? $product[$feature] : '';

	return apply_filters( 'it_exchange_get_transaction_product_feature', $feature_value, $product, $feature );
}

/**
 * Returns the transaction method name from the add-on's slug
 *
 * @since 0.3.7
 *
 * @param string $slug
 *
 * @return string
 */
function it_exchange_get_transaction_method_name_from_slug( $slug ) {

	if ( $method = it_exchange_get_addon( $slug ) ) {
		return apply_filters( 'it_exchange_get_transaction_method_name_' . $slug, $method['name'] );
	}

	return apply_filters( 'it_exchange_get_transaction_method_name_' . $slug, $slug );
}

/**
 * Returns the name of a transaction method used for a specific transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return string
 */
function it_exchange_get_transaction_method_name( $transaction ) {

	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		return $transaction->get_method( true );
	}

	return apply_filters( 'it_exchange_get_transaction_method_name', false, $transaction );
}

/**
 * Updates the ID of a transaction method used for a specific transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param string $method_id ID from the transaction method
 *
 * @return string
 */
function it_exchange_update_transaction_method_id( $transaction, $method_id ) {

	if ( ! $transaction = it_exchange_get_transaction( $transaction ) ) {
		return false;
	}

	return $transaction->update_method_id( $method_id );
}

/**
 * Updates the Cart Object of a transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 * @param object $cart_object Cart Object for specific transaction
 *
 * @return bool
 */
function it_exchange_update_transaction_cart_object( $transaction, $cart_object ) {
	$transaction = it_exchange_get_transaction( $transaction );

	if ( ! $transaction ) {
		return false;
	}

	return (bool) update_post_meta( $transaction->ID, '_it_exchange_cart_object', $cart_object );
}

/**
 * Returns the ID of a transaction method used for a specific transaction
 *
 * @since 0.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction ID or object
 *
 * @return string
 */
function it_exchange_get_transaction_method_id( $transaction ) {

	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		return $transaction->get_method_id();
	}

	return false;
}

/**
 * For processing a transaction
 *
 * @since 0.3.7
 *
 * @param string $method
 * @param object $transaction_object
 *
 * @return mixed
 */
function it_exchange_do_transaction( $method, $transaction_object ) {
	return apply_filters( 'it_exchange_do_transaction_' . $method, false, $transaction_object );
}

/**
 * Does the given transaction have a status that warants delivery of product(s)
 *
 * Returns true/false. Rely on transaction method addon to give us that. Default is false.
 *
 * @since 0.4.2
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction id or object
 *
 * @return bool
 */
function it_exchange_transaction_is_cleared_for_delivery( $transaction ) {

	if ( $transaction = it_exchange_get_transaction( $transaction ) ) {
		return $transaction->is_cleared_for_delivery();
	}

	return false;
}

/**
 * Returns the make-payment action
 *
 * Leans on tranasction_method to actually provide it.
 *
 * @since 0.4.0
 *
 * @param string $transaction_method slug registered with addon
 * @param array $options
 *
 * @return mixed
 */
function it_exchange_get_transaction_method_make_payment_button ( $transaction_method, $options=array() ) {
	return apply_filters( 'it_exchange_get_' . $transaction_method . '_make_payment_button', '', $options );
}

/**
 * Grab all registered webhook / IPN keys
 *
 * @since 0.4.0
 *
 * @return array
 */
function it_exchange_get_webhooks() {
	$webhooks = empty( $GLOBALS['it_exchange']['webhooks'] ) ? array() : (array) $GLOBALS['it_exchange']['webhooks'];
	return apply_filters( 'it_exchange_get_webhooks', $webhooks );
}

/**
 * Register a webhook / IPN key
 *
 * @since 0.4.0
 *
 * @param string $key   the addon slug or ID
 * @param string $param the REQUEST param we are listening for
 *
 * @return void
 */
function it_exchange_register_webhook( $key, $param ) {
	$GLOBALS['it_exchange']['webhooks'][$key] = $param;
	do_action( 'it_exchange_register_webhook', $key, $param );
}

/**
 * Grab a specific registered webhook / IPN param
 *
 * @since 0.4.0
 *
 * @param string $key the key for the param we are looking for
 *
 * @return string|bool or false
 */
function it_exchange_get_webhook( $key ) {

	$webhook = empty( $GLOBALS['it_exchange']['webhooks'][$key] ) ? false : $GLOBALS['it_exchange']['webhooks'][$key];

	return apply_filters( 'it_exchange_get_webhook', $webhook, $key );
}

/**
 * Check what webhook is being processed.
 *
 * @since 1.34
 *
 * @param string $webhook Optionally, specify the webhook to compare against.
 *
 * @return bool|string
 */
function it_exchange_doing_webhook( $webhook = '' ) {

	if ( $webhook ) {
		$param = it_exchange_get_webhook( $webhook );

		if ( ! empty( $_REQUEST[$param] ) ) {
			return true;
		}

		return false;
	}

	foreach ( it_exchange_get_webhooks() as $key => $param ) {

		if ( ! empty( $_REQUEST[$param] ) ) {
			return $key;
		}
	}

	return false;
}

/**
 * Get a URL for a webhook.
 *
 * @since 2.0.0
 *
 * @param string $webhook_key
 *
 * @return string
 */
function it_exchange_get_webhook_url( $webhook_key ) {
	return get_home_url() . '/?' . it_exchange_get_webhook( $webhook_key ) . '=1';
}

/**
 * Get the confirmation URL for a transaction
 *
 * @since 0.4.0
 *
 * @param integer $transaction_id id of the transaction
 *
 * @return string url
 */
function it_exchange_get_transaction_confirmation_url( $transaction_id ) {

	// If we can't grab the hash, return false
	if ( ! $transaction_hash = it_exchange_get_transaction_hash( $transaction_id ) ) {
		return apply_filters( 'it_exchange_get_transaction_confirmation_url', false, $transaction_id );
	}

	// Get base page URL
	$confirmation_url = it_exchange_get_page_url( 'confirmation' );

	if ( '' != get_option( 'permalink_structure' ) ) {
		$confirmation_url = trailingslashit( $confirmation_url ) . $transaction_hash;
	} else {
		$slug             = it_exchange_get_page_slug( 'confirmation' );
		$confirmation_url = remove_query_arg( $slug, $confirmation_url );
		$confirmation_url = add_query_arg( $slug, $transaction_hash, $confirmation_url );
	}

	return apply_filters( 'it_exchange_get_transaction_confirmation_url', $confirmation_url, $transaction_id );
}

/**
 * Can this transaction status be manually updated?
 *
 * @since 0.4.11
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction the id or object
 *
 * @return boolean
 */
function it_exchange_transaction_status_can_be_manually_changed( $transaction ) {
	if ( ! $method = it_exchange_get_transaction_method( $transaction ) ) {
		return false;
	}

	return apply_filters( 'it_exchange_' . $method . '_transaction_status_can_be_manually_changed', false );
}

/**
 * Does this transaction include shipping details
 *
 * @since 1.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction the id or object
 *
 * @return boolean
 */
function it_exchange_transaction_includes_shipping( $transaction ) {
	$includes_shipping = it_exchange_get_transaction_shipping_method( $transaction );
	$includes_shipping = ! empty( $includes_shipping->label );

	return apply_filters( 'it_exchange_transaction_includes_shipping', $includes_shipping, $transaction );
}

/**
 * Return the total for shipping for this transaction
 *
 * @since 1.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction the id or object
 * @param bool $format_price
 *
 * @return float|string|false
 */
function it_exchange_get_transaction_shipping_total( $transaction, $format_price = false ) {

	if ( ! $transaction = it_exchange_get_transaction( $transaction ) ) {
		return false;
	}

	$total = $transaction->get_items( 'shipping', true )->total();

	$total = apply_filters( 'it_exchange_get_transaction_shipping_total', $total, $transaction );

	return $format_price ? it_exchange_format_price( $total ) : $total;
}

/**
 * Returns the shipping method object used with this transaction
 *
 * If Multiple Methods was used, returns a stdClass with slug and label properties
 *
 * @since 1.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction the id or object
 *
 * @return object|false
 */
function it_exchange_get_transaction_shipping_method( $transaction ) {

	if ( ! $transaction = it_exchange_get_transaction( $transaction ) ) {
		return false;
	}

	// If Multiple, Just return the string since its not a registered method
	if ( it_exchange_does_transaction_have_multiple_shipping_methods( $transaction ) ) {
		$method = new stdClass();
		$method->slug  = 'multiple-methods';
		$method->label = __( 'Multiple Shipping Methods', 'it-l10n-ithemes-exchange' );

		return apply_filters( 'it_exchange_get_transaction_shipping_method', $method, $transaction );
	}

	/** @var ITE_Shipping_Line_Item $shipping_item */
	$shipping_item   = $transaction->get_items( 'shipping' )->first();
	$shipping_method = $shipping_item ? $shipping_item->get_method() : false;

	return apply_filters( 'it_exchange_get_transaction_shipping_method', $shipping_method, $transaction );
}

/**
 * Check if a transaction has multiple shipping methods.
 *
 * @since 2.0.0
 *
 * @param int|IT_Exchange_Transaction $transaction
 *
 * @return bool
 */
function it_exchange_does_transaction_have_multiple_shipping_methods( $transaction ) {

	if ( ! $transaction = it_exchange_get_transaction( $transaction ) ) {
		return false;
	}

	return $transaction->get_items( 'shipping', true )->unique( function( ITE_Shipping_Line_Item $shipping ) {
		return $shipping->get_method() ? $shipping->get_method()->slug : uniqid( '', true );
	} )->count() > 1;
}

/**
 * Prints Shipping Method used for a specific product in the transaction
 *
 * @since 1.4.0
 *
 * @param WP_Post|int|IT_Exchange_Transaction $transaction
 * @param string                              $product_cart_id
 *
 * @return string
 */
function it_exchange_get_transaction_shipping_method_for_product( $transaction, $product_cart_id ) {

	if ( ! $transaction = it_exchange_get_transaction( $transaction ) ) {
		return false;
	}

	$transaction_method = it_exchange_get_transaction_shipping_method( $transaction );

	/** @var ITE_Cart_Product $item */
	$item = $transaction->get_item( 'product', $product_cart_id );

	if ( ! $item ) {
		return false;
	}

	if ( 'multiple-methods' === $transaction_method->slug ) {

		/** @var ITE_Shipping_Line_Item $shipping_line */
		$shipping_line = $item->get_line_items()->with_only( 'shipping' )->first();

		if ( $shipping_line ) {
			$method = $shipping_line->get_method();
		} else {
			$method = false;
		}

		if ( $method ) {
			$method = $method->label;
		} else {
			$method = __( 'Unknown Method', 'it-l10n-ithemes-exchange' );
		}

	} else {
		$method = $transaction_method->label;
	}

	return apply_filters( 'it_exchange_get_transaction_shipping_method_for_product', $method, $transaction, $product_cart_id );
}
