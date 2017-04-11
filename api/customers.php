<?php
/**
 * API functions to deal with customer data and actions
 *
 * @since 0.3.7
 * @package IT_Exchange
*/
use IronBound\DB\Query\FluentQuery;

/**
 * Registers a customer
 *
 * @since 0.3.7
 * @param array $customer_data array of customer data to be processed by the customer management add-on when creating a
 *                             customer
 * @param array $args optional array of arguments. not used by all add-ons
 *
 * @return mixed
*/
function it_exchange_register_customer( $customer_data, $args=array() ) {
	return do_action( 'it_exchange_register_customer', $customer_data, $args );
}

/**
 * Get a customer.
 *
 * @since 0.3.7
 * @since 2.0.0 Add support for retrieving a guest customer instance.
 *
 * @param int|WP_User|string $customer_id User ID. User object. Or email address. If email given,
 *                                        a guest customer object will be returned.
 *
 * @return IT_Exchange_Customer|false
*/
function it_exchange_get_customer( $customer_id ) {
    // Grab the WP User

	if ( $customer_id instanceof IT_Exchange_Customer ) {
		$customer = $customer_id;
	} else {

		try {
			if ( is_string( $customer_id ) && is_email( $customer_id ) ) {
				$customer = new IT_Exchange_Guest_Customer( $customer_id );
			} else {
				$customer = new IT_Exchange_Customer( $customer_id );
			}
		}
		catch ( Exception $e ) {
			return false;
		}
	}

	if ( ! $customer->id || ! $customer->is_wp_user() ) {
		$customer = false;
	}

	return apply_filters( 'it_exchange_get_customer', $customer, $customer_id );
}

/**
 * Get the currently logged in customer or return false
 *
 * @since 0.3.7
 *
 * @return IT_Exchange_Customer|bool customer data
*/
function it_exchange_get_current_customer() {
	if ( ! is_user_logged_in() )
		return apply_filters( 'it_exchange_get_current_customer', false );

	$customer = it_exchange_get_customer( get_current_user_id() );
	return apply_filters( 'it_exchange_get_current_customer', $customer );
}

/**
 * Get the currently logged in customer ID or return false
 *
 * @since 0.4.0
 *
 * @return int|bool customer ID
*/
function it_exchange_get_current_customer_id() {
	if ( ! is_user_logged_in() )
		return false;

	return apply_filters( 'it_exchange_get_current_customer_id', get_current_user_id() );
}

/**
 * Update a customer's data
 *
 * @since 0.3.7
 *
 * @param integer $customer_id id for the customer
 * @param mixed $customer_data data to be updated
 * @param array $args optional array of arguments. not used by all add-ons
 *
 * @return mixed
*/
function it_exchange_update_customer( $customer_id, $customer_data, $args ) {
	return do_action( 'it_exchange_update_customer', $customer_id, $customer_data, $args );
}

/**
 * Returns all customer transactions
 *
 * @since 0.4.0
 *
 * @param integer $customer_id customer id
 * @param array   $args
 * @param int     $total
 *
 * @return array
*/
function it_exchange_get_customer_transactions( $customer_id, array $args = array(), &$total = null ) {
	if ( ! $customer = it_exchange_get_customer( $customer_id ) ) {
		return array();
	}

	$wp = array(
		'customer_id' => $customer->id,
	);

	if ( isset( $args['page'] ) ) {
		$wp['paged']          = $args['page'];
		$wp['posts_per_page'] = isset( $args['per_page'] ) ? $args['per_page'] : 10;
	} else {
		$wp['numberposts'] = -1;
	}

	/**
	 * Filter the args used to get a customer's transactions.
	 *
	 * @since 1.33.0
	 *
	 * @param array $args
	 * @param IT_Exchange_Customer $customer
	 */
	$filtered = apply_filters_deprecated( 'it_exchange_get_customer_transactions_args', array( $wp, $customer ), '2.0.0' );

	if ( $wp !== $filtered ) {
		$transactions = it_exchange_get_transactions( $filtered, $total );
	} else {

		$fq_args = array(
			'customer' => $customer_id,
			'parent'   => 0,
			'order'    => array( 'order_date' => 'DESC' )
		);

		if ( isset( $args['page'] ) ) {
			$fq_args['page'] = $args['page'];
			$fq_args['items_per_page'] = isset( $args['per_page'] ) ? $args['per_page'] : 10;
		} elseif ( isset( $args['per_page'] ) ) {
			$fq_args['items_per_page'] = $args['per_page'];
			$fq_args['calc_found_rows'] = false;
		}

		if ( func_num_args() !== 3 ) {
			$fq_args['calc_found_rows'] = false;
		}

		if ( isset( $args['with'] ) ) {
			$fq_args['eager_load'] = $args['with'];
		}

		$query = new ITE_Transaction_Query( $fq_args );

		$transactions = $query->results();

		if ( isset( $args['page'] ) && func_num_args() === 3 ) {
			$total = $query->total();
		}
	}

	return apply_filters( 'it_exchange_get_customer_transactions', $transactions, $customer_id, $wp );
}

/**
 * Returns all customer transactions
 *
 * @since 0.4.0
 *
 * @param integer $transaction_id
 * @param integer $customer_id
 *
 * @return bool
*/
function it_exchange_customer_has_transaction( $transaction_id, $customer_id = NULL ) {

	$customer = $customer_id ? it_exchange_get_customer( $customer_id ) : it_exchange_get_current_customer();

	if ( ! $customer ) {
		return apply_filters( 'it_exchange_customer_has_transaction', false, $transaction_id, $customer_id );
	}

	$has = $customer->has_transaction( $transaction_id );

	return apply_filters( 'it_exchange_customer_has_transaction', $has, $transaction_id, $customer_id );
}

/**
 * Returns all customer products purchased across various transactions
 *
 * @since 0.4.0
 *
 * @param integer $customer_id the WP id of the customer
 *
 * @return array
*/
function it_exchange_get_customer_products( $customer_id ) {

	$models = ITE_Transaction_Line_Item_Model::query()
		->where( 'type', '=', 'product' )
		->join( new ITE_Transactions_Table(), 'transaction', 'ID', '=', function( FluentQuery $query ) use ( $customer_id ) {
			$query->and_where( 'customer_id', '=', $customer_id );
		} )
		->order_by( 'created_at', 'DESC' )
		->results()->toArray();

	$items_by_txn = ITE_Cart_Transaction_Repository::convert_to_items_segmented( $models );

	$products = array();

	/** @var ITE_Cart_Product[] $items */
	foreach ( $items_by_txn as $transaction => $items ) {
		foreach ( $items as $item ) {
			$product_info = $item->bc();
			$product_info['transaction_id'] = $transaction;

			$products[] = $product_info;
		}
	}

	// Return
	return apply_filters( 'it_exchange_get_customer_products', $products, $customer_id );
}

/**
 * Handles $_REQUESTs and submits them to the profile for processing
 *
 * @since 0.4.0
 * @return void
*/
function handle_it_exchange_save_profile_action() {

	// Grab action and process it.
	if ( isset( $_REQUEST['it-exchange-save-profile'] ) ) {
		
		if ( ! $customer_id = it_exchange_get_current_customer_id() ) {
			return false;
		}

		if ( empty( $_POST['_profile_nonce'] ) ) {
			return false;
		}

		if ( ! wp_verify_nonce( $_POST['_profile_nonce'], 'it-exchange-update-profile-' . $customer_id ) ) {
			return false;
		}

		//WordPress builtin
		require_once(ABSPATH . 'wp-admin/includes/user.php');
		$customer = it_exchange_get_current_customer();
		$result = edit_user( $customer->id );

		if ( is_wp_error( $result ) ) {
			it_exchange_add_message( 'error', $result->get_error_message() );
		} else {
			it_exchange_add_message( 'notice', __( 'Successfully saved profile!', 'it-l10n-ithemes-exchange' ) );
		}

		do_action( 'handle_it_exchange_save_profile_action' );

	}

}
add_action( 'template_redirect', 'handle_it_exchange_save_profile_action', 5 );

/**
 * Register's an exchange user
 *
 * @since 0.4.0
 * @param array $user_data optional. Overwrites POST data
 *
 * @return int|WP_Error
*/
function it_exchange_register_user( $user_data=array() ) {

	if ( empty( $_POST['_exchange_register_nonce'] ) ) {
		return new WP_Error( 'not-allowed', __( 'Action not allowed', 'it-l10n-ithemes-exchange' ) );;
	}

	if ( ! wp_verify_nonce( $_POST['_exchange_register_nonce'], 'it-exchange-register-customer' ) ) {
		return new WP_Error( 'not-allowed', __( 'Action not allowed', 'it-l10n-ithemes-exchange' ) );
	}

	// Include WP file
	require_once( ABSPATH . 'wp-admin/includes/user.php' );

	// If any data was passed in through param, inject into POST variable
	foreach( $user_data as $key => $value ) {
		$_POST[$key] = $value;
	}

	do_action( 'it_exchange_register_user' );

	/**
	 * Filter the result from registering a user.
	 *
	 * @since 1.34
	 *
	 * @param WP_Error $errors
	 */
	$errors = apply_filters( 'it_exchange_register_user_errors', null );

	if ( is_wp_error( $errors ) ) {
		return $errors;
	}

	return edit_user();
}

/**
 * Retrieve specific customer data
 *
 * @since 1.3.0
 *
 * @param string $data_key the key of the data property of the IT Exchange customer object.
 * @param integer|bool $customer_id the customer id. leave blank to use the current customer.
 *
 * @return mixed
*/
function it_exchange_get_customer_data( $data_key, $customer_id=false ) {
	$customer = empty( $customer_id ) ? it_exchange_get_current_customer() : it_exchange_get_customer( $customer_id );

	// Return false if no customer was found
	if ( ! $customer )
		return false;

	// Set requested data if it exists, otherwise set as false
	$data = empty( $customer->data->$data_key ) ? false : $customer->data->$data_key;

	// Return the data
	return apply_filters( 'it_exchange_get_customer_data', $data, $data_key, $customer );
}

/**
 * Get Customer Shipping Address
 *
 * Among other things this function is used as a callback for the shipping address
 * purchase requriement.
 *
 * @since 1.4.0
 *
 * @param integer|bool $customer_id the customer id. leave blank to use the current customer.
 *
 * @return array|false
*/
function it_exchange_get_customer_shipping_address( $customer_id = false ) {

	$customer = $customer_id ? it_exchange_get_customer( $customer_id ) : it_exchange_get_current_customer();

	if ( ! $customer ) {
		return false;
	}

	$address = $customer->get_shipping_address();

	return $address ? $address->to_array() : array();
}

/**
 * Save the shipping address based on the User's ID
 *
 * @since 1.4.0
 *
 * @param array    $address the shipping address as an array
 * @param int|bool $customer_id optional. if empty, will attempt to get he current user's ID
 *
 * @return boolean Will fail if no user ID was provided or found
 */
function it_exchange_save_shipping_address( $address, $customer_id = false ) {

	$customer = $customer_id ? it_exchange_get_customer( $customer_id ) : it_exchange_get_current_customer();

	if ( ! $customer ) {
		return false;
	}

	try {
		$location = $customer->set_shipping_address( new ITE_In_Memory_Address( $address ) );

		return $location !== null;
	} catch ( InvalidArgumentException $e ) {
		return false;
	}
}

/**
 * Get Customer Billing Address
 *
 * Among other things this function is used as a callback for the billing address
 * purchase requriement.
 *
 * @since 1.3.0
 *
 * @param integer|bool $customer_id the customer id. leave blank to use the current customer.
 *
 * @return array|false
*/
function it_exchange_get_customer_billing_address( $customer_id = false ) {

	$customer = $customer_id ? it_exchange_get_customer( $customer_id ) : it_exchange_get_current_customer();

	if ( ! $customer ) {
		return false;
	}

	$address = $customer->get_billing_address();

	return $address ? $address->to_array() : array();
}

/**
 * Updates the customer billing address
 *
 * @since 1.5.0
 *
 * @param array   $address      address obejct
 * @param integer|bool $customer_id optional. defualts to current customer
 *
 * @return boolean
*/
function it_exchange_save_customer_billing_address( $address, $customer_id = false ) {

	$customer = $customer_id ? it_exchange_get_customer( $customer_id ) : it_exchange_get_current_customer();

	if ( ! $customer ) {
		return false;
	}

	try {
		$location = $customer->set_billing_address( new ITE_In_Memory_Address( $address ) );

		return $location !== null;
	} catch ( InvalidArgumentException $e ) {
		return false;
	}
}
