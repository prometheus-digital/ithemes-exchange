<?php
/**
 * Basic Coupons
 * @package IT_Exchange
 * @since 0.4.0
*/

if ( is_admin() ) {
	include( dirname( __FILE__) . '/admin.php' );
}

/**
 * Include the IT_Exchange_Cart_Coupon class.
 *
 * @since 1.33
 */
require_once( dirname( __FILE__ ) . '/coupon.php' );

/**
 * Register the cart coupon type
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_register_coupon_type() {
	it_exchange_register_coupon_type( 'cart', 'IT_Exchange_Cart_Coupon' );
}
add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_basic_coupons_register_coupon_type' );

/**
 * Adds meta data for Basic Coupons to the coupon object
 *
 * @since 0.4.0
 *
 * @return array
*/
function it_exchange_basic_coupons_add_meta_data_to_coupon_object( $data, $object ) {
	// Set post meta keys used in basic coupons
	$post_meta_keys = array(
		'code'             => '_it-basic-code',
		'amount_number'    => '_it-basic-amount-number',
		'amount_type'      => '_it-basic-amount-type',
		'start_date'       => '_it-basic-start-date',
		'end_date'         => '_it-basic-end-date',
		'limit_quantity'   => '_it-basic-limit-quantity',
		'quantity'         => '_it-basic-quantity',
		'limit_product'    => '_it-basic-limit-product',
		'product_id'       => '_it-basic-product-id',
		'limit_frequency'  => '_it-basic-limit-frequency',
		'frequency_times'  => '_it-basic-frequency-times',
		'frequency_length' => '_it-basic-frequency-length',
		'frequency_units'  => '_it-basic-frequency-units',
		'customer'         => '_it-basic-customer',
		'limit_customer'   => '_it-basic-limit-customer',
	);

	// Loop through and add them to the data that will be added as properties to coupon object
	foreach( $post_meta_keys as $property => $key ) {
		$data[$property] = get_post_meta( $object->ID, $key, true );
	}

	// Return data
	return $data;
}
add_filter( 'it_exchange_coupon_additional_data', 'it_exchange_basic_coupons_add_meta_data_to_coupon_object', 9, 2 );

/**
 * Add field names
 *
 * @since 0.4.0
 *
 * @param array $names Incoming core vars => values
 * @return array
*/
function it_exchange_basic_coupons_register_field_names( $names ) {
	$names['apply_coupon']  = 'it-exchange-basic-coupons-apply-coupon';
	$names['remove_coupon'] = 'it-exchange-basic-coupons-remove-coupon';
	return $names;
}
add_filter( 'it_exchange_default_field_names', 'it_exchange_basic_coupons_register_field_names' );

/**
 * Get a cart coupon from its code.
 *
 * @since 1.33
 *
 * @param IT_Exchange_Coupon|null $coupon
 * @param string                  $code
 *
 * @return IT_Exchange_Cart_Coupon|null
 */
function it_exchange_get_cart_coupon_from_code( IT_Exchange_Coupon $coupon = null, $code ) {

	if ( ! ( $ID = wp_cache_get( 'it-exchange-cart-coupon', $code ) ) ) {

		/** wpdb $wpdb */
		global $wpdb;

		$ID = $wpdb->get_var( $wpdb->prepare(
				"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s",
				'_it-basic-code',
				$code
		) );

		wp_cache_set( 'it-exchange-cart-coupon', $ID, $code );
	}

	return it_exchange_get_coupon( $ID, 'cart' );
}

add_filter( 'it_exchange_get_cart_coupon_from_code', 'it_exchange_get_cart_coupon_from_code', 10, 2 );

/**
 * Returns applied cart coupons
 *
 * @since 0.4.0
 *
 * @return IT_Exchange_Coupon[]|false
*/
function it_exchange_basic_coupons_applied_cart_coupons() {

	$cart_data = it_exchange_get_cart_data( 'basic_coupons' );

	$coupons = array();

	foreach ( $cart_data as $code => $coupon ) {
		$coupons[] = it_exchange_get_coupon_from_code( $code, 'cart' );
	}

	return empty( $coupons ) ? false : $coupons;
}
add_filter( 'it_exchange_get_applied_cart_coupons', 'it_exchange_basic_coupons_applied_cart_coupons' );

/**
 * Determines if we are currently accepting more coupons
 *
 * Basic coupons only allows one coupon applied to each cart.
 *
 * @since 0.4.0
 *
 * @param mixed $incoming sent from WP filter. Discarded here.
 * @return boolean
*/
function it_exchange_basic_coupons_accepting_cart_coupons( $incoming=false ) {
	return ! (boolean) it_exchange_get_applied_coupons( 'cart' );
}
add_filter( 'it_exchange_accepting_cart_coupons', 'it_exchange_basic_coupons_accepting_cart_coupons' );

/**
 * Return the form field for applying a coupon code to a cart
 *
 * @since 0.4.0
 *
 * @param mixed $incoming sent from WP filter. Discarded here.
 * @return string
*/
function it_exchange_base_coupons_apply_cart_coupon_field( $incoming=false, $options=array() ) {
	$defaults = array(
		'class'       => 'apply-coupon',
		'placeholder' => __( 'Coupon Code', 'it-l10n-ithemes-exchange' ),
	);
	$options = ITUtility::merge_defaults( $options, $defaults );
	$var = it_exchange_get_field_name( 'apply_coupon' ) . '-cart';
	return '<input type="text" class="' . esc_attr( $options['class'] ) . '" name="' . esc_attr( $var ) . '" placeholder="' . esc_attr( $options['placeholder'] ) . '" value="" />';
}
add_filter( 'it_exchange_apply_cart_coupon_field', 'it_exchange_base_coupons_apply_cart_coupon_field', 10, 2 );

/**
 * Apply a coupon to a cart on update
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_handle_coupon_on_cart_update() {
	$var = it_exchange_get_field_name( 'apply_coupon' ) . '-cart';

	// Abort if no coupon code was added
	if ( ! $coupon_code = empty( $_REQUEST[$var] ) ? false : $_REQUEST[$var] )
		return;

	it_exchange_apply_coupon( 'cart', $coupon_code );
}
add_action( 'it_exchange_update_cart', 'it_exchange_basic_coupons_handle_coupon_on_cart_update' );

/**
 * Applies a coupon code to a cart if it exists and is valid
 *
 * @since 0.4.0
 *
 * @param boolean $result this is default to false. gets set by apply_filters
 * @param array $options - must contain coupon key
 * @return boolean
*/
function it_exchange_basic_coupons_apply_to_cart( $result, $options=array() ) {

	// Set coupon code. Return false if one is not available
	$coupon_code = empty( $options['code'] ) ? false : $options['code'];

	$coupon = it_exchange_get_cart_coupon_from_code( null, $coupon_code );

	if ( ! $coupon ) {
		it_exchange_add_message( 'error', __( 'Invalid coupon', 'it-l10n-ithemes-exchange' ) );
		return false;
	}

	try {
		$coupon->validate();
	} catch ( Exception $e ) {
		it_exchange_add_message( 'error', $e->getMessage() );

		return false;
	}

	/**
	 * Fires before a coupon is applied to the cart.
	 *
	 * If false is returned, the coupon won't be applied.
	 *
	 * Your addon should output an it_exchange_add_message( 'error', $message )
	 * letting the user know the coupon was not applied.
	 *
	 * @param $addon_result  bool
	 * @param $options       array
	 * @param $coupon        IT_Exchange_Coupon
	 */
	$addon_result = apply_filters( 'it_exchange_basic_coupons_apply_coupon_to_cart', null, $options, $coupon );

	if ( $addon_result === false ) {

		return $addon_result;
	}

	// Format data for session
	$coupon = array(
		'id'            => $coupon->ID,
		'title'         => $coupon->get_title(),
		'code'          => $coupon->get_code()
	);

	// Add to session data
	$data = array( $coupon['code'] => $coupon );
	it_exchange_update_cart_data( 'basic_coupons', $data );
	do_action( 'it_exchange_basic_coupon_applied', $data );

	it_exchange_add_message( 'notice', __( 'Coupon applied', 'it-l10n-ithemes-exchange' ) );
	return true;
}

add_action( 'it_exchange_apply_coupon_to_cart', 'it_exchange_basic_coupons_apply_to_cart', 10, 2 );

/**
 * Has the frequency limit for this coupon been met.
 *
 * Grabs array of timestamps specified (or current) user has used the specific coupon.
 * Determines # of seconds before now to count uses
 * Makes sure that customer has not met limit of use in calculated time period
 *
 * @since 1.9.2
 *
 * @param int|IT_Exchange_Cart_Coupon $coupon wp post id for the coupon
 * @param int|bool                    $customer_id Customer ID to check against. If false, current customer is used.
 * @return boolean
*/
function it_exchange_basic_coupon_frequency_limit_met_by_customer( $coupon, $customer_id=false ) {

	$customer_id = empty( $customer_id ) ? it_exchange_get_current_customer_id() : $customer_id;

	$coupon = it_exchange_get_coupon( $coupon );

	if ( ! $coupon instanceof IT_Exchange_Cart_Coupon || ! $coupon->is_frequency_limited() ) {
		return false;
	}

	$current_frequencies = it_exchange_basic_coupons_get_customer_coupon_frequency( $coupon->get_ID(), $customer_id );

	// Multiply the length times the units to get seconds for set frequency
	$frequency_seconds = $coupon->get_frequency_period_in_seconds();

	$earliest_limit = date_i18n( 'U' ) - $frequency_seconds;

	// Loop through current frequencies and total uses since last limit
	$relevant_uses = 0;
	foreach( (array) $current_frequencies as $date ) {
		if ( $date > $earliest_limit )
			$relevant_uses++;
	}

	// If relevant uses is greater than limit, return error message
	if ( $relevant_uses >= $coupon->get_frequency_times() ) {
		return true;
	}

	return false;
}

/**
 * Get a customers usage of either a particular coupon or all coupons.
 *
 * @since 1.9.2
 *
 * @param int|bool $coupon_id   The coupon ID to check against. If false, history for all coupons is returned.
 * @param int|bool $customer_id The customer id. If false, the current customer will be used.
 *
 * @return array
*/
function it_exchange_basic_coupons_get_customer_coupon_frequency( $coupon_id = false, $customer_id = false ) {

	$customer_id = empty( $customer_id ) ? it_exchange_get_current_customer_id() : $customer_id;

	$coupon_history = array();

	if ( empty( $customer_id ) ) {
		if ( function_exists( 'it_exchange_doing_guest_checkout' ) && it_exchange_doing_guest_checkout() ) {
			$customer = it_exchange_get_current_customer();
			$coupon_history = get_option( '_it_exchange_basic_coupon_history_' . $customer->data->email );
		}
	} else {
		$coupon_history = get_user_meta( $customer_id, '_it_exchagne_basic_coupon_history', true );
	}

	if ( empty( $coupon_id ) )
		$validated_history = $coupon_history;
	else
		$validated_history = empty( $coupon_history[$coupon_id] ) ? array() : $coupon_history[$coupon_id];

	return apply_filters( 'it_exchange_basic_coupons_get_customer_coupon_frequency', $validated_history, $coupon_id, $customer_id, $coupon_history );
}

/**
 * Increments coupon use for a specific coupon for a user
 *
 * @since 1.9.2
 *
 * @deprecated 1.33 This function does not handle transient transactions, and makes reversing usage impossible.
 *
 * @param int      $coupon_id   the coupon code.
 * @param int|bool $customer_id The customer id to update. If false, the current customer will be used.
 *
 * @return array
*/
function it_exchange_basic_coupons_bump_customer_coupon_frequency( $coupon_id, $customer_id = false ) {

	_deprecated_function(
		'it_exchange_basic_coupons_bump_customer_coupon_frequency', '1.33',
		'IT_Exchange_Cart_Coupon::bump_customer_coupon_frequency' );

	$customer_id    = empty( $customer_id ) ? it_exchange_get_current_customer_id() : $customer_id;
	$coupon_history = it_exchange_basic_coupons_get_customer_coupon_frequency( false, $customer_id );

	if ( empty( $coupon_history[$coupon_id] ) )
		$coupon_history[$coupon_id] = array( date_i18n( 'U' ) );
	else
		$coupon_history[$coupon_id][] = date_i18n('U');

	if ( function_exists( 'it_exchange_doing_guest_checkout' ) && it_exchange_doing_guest_checkout() ) {
		update_option( '_it_exchange_basic_coupon_history_' . $customer_id, $coupon_history );
	} else {
		update_user_meta( $customer_id, '_it_exchagne_basic_coupon_history', $coupon_history );
	}
}

/**
 * Clear cart coupons when cart is emptied
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_clear_cart_coupons_on_empty() {
	it_exchange_remove_cart_data( 'basic_coupons' );
}
add_action( 'it_exchange_empty_shopping_cart', 'it_exchange_clear_cart_coupons_on_empty' );

/**
 * Return the form checkbox for removing a coupon code to a cart
 *
 * @since 0.4.0
 *
 * @param mixed $incoming sent from WP filter. Discarded here.
 * @return string
*/
function it_exchange_base_coupons_remove_cart_coupon_html( $incoming=false, $code, $options=array() ) {
	$defaults = array(
		'class'  => 'remove-coupon',
		'format' => 'link',
		'label'  => _x( '&times;', 'html representation for multiplication symbol (x)', 'it-l10n-ithemes-exchange' ),
	);
	$options = ITUtility::merge_defaults( $options, $defaults );

	$var = it_exchange_get_field_name( 'remove_coupon' ) . '-cart';

	if ( 'checkbox' == $options['format'] ) {
		return '<input type="checkbox" class="' . esc_attr( $options['class'] ) . '" name="' . esc_attr( $var ) . '[]" value="' . esc_attr( $options['code'] ) . '" />&nbsp;' . esc_attr( $options['label'] );
	} else {
		$url = it_exchange_clean_query_args( array( it_exchange_get_field_name( 'sw_cart_focus' ) ) );
		$url = add_query_arg( $var . '[]', $options['code'] );
		return '<a data-coupon-code="' . esc_attr( $options['code'] ) . '" class="' . esc_attr( $options['class'] ) . '" href="' . esc_url( $url ) . '">' . esc_attr( $options['label'] ) . '</a>';
	}
}
add_filter( 'it_exchange_remove_cart_coupon_html', 'it_exchange_base_coupons_remove_cart_coupon_html', 10, 3 );

/**
 * Modify the cart total to reflect coupons
 *
 * @since 0.4.0
 *
 * @param float $total
 *
 * @return float
*/
function it_exchange_basic_coupons_apply_discount_to_cart_total( $total ) {

	$total_discount = it_exchange_get_total_coupons_discount( 'cart', array( 'format_price' => false ) );
	$total = $total - $total_discount;

	return $total;
}
add_filter( 'it_exchange_get_cart_total', 'it_exchange_basic_coupons_apply_discount_to_cart_total' );

/**
 * Returns the total discount from applied coupons
 *
 * @since 0.4.0
 *
 * @param string|bool $discount existing value passed in by WP filter
 * @param array       $options
 *
 * @return string
*/
function it_exchange_basic_coupons_get_total_discount_for_cart( $discount = false, $options = array() ) {
	$defaults = array(
		'format_price' => true,
	);
	$options = ITUtility::merge_defaults( $options, $defaults );

	$coupons = it_exchange_get_applied_coupons( 'cart' );

	$has_valid = false;

	foreach( (array) $coupons as $coupon ) {

		if ( empty( $coupon ) || ! $coupon instanceof IT_Exchange_Cart_Coupon ) {
			continue;
		}

		$method = $coupon->get_application_method();
		$type   = $coupon->get_amount_type();

		$cart_products = it_exchange_get_cart_products();

		foreach( $cart_products as $cart_product ) {

			if ( it_exchange_basic_coupons_valid_product_for_coupon( $cart_product, $coupon ) || $method === IT_Exchange_Cart_Coupon::APPLY_CART ) {

				$has_valid = true;

				if ( $method === IT_Exchange_Cart_Coupon::APPLY_CART && $type === IT_Exchange_Cart_Coupon::TYPE_FLAT ) {
					$discount += $coupon->get_amount_number();

					break;
				}

				$base_price = it_exchange_get_cart_product_base_price( $cart_product, false );

				if ( $type === IT_Exchange_Cart_Coupon::TYPE_PERCENT ) {
					$product_discount = ( $coupon->get_amount_number() / 100 ) * $base_price;
				} else {
					$product_discount = $coupon->get_amount_number();
				}

				$product_discount *= $cart_product['count'];
				$discount += $product_discount;
			}
		}
	}

	$discount = round( $discount, 2 );

	if ( ! $has_valid ) {
		$discount = 0.00;
	}

	if ( $options['format_price'] ) {
		$discount = it_exchange_format_price( $discount );
	}

	return $discount;
}
add_filter( 'it_exchange_get_total_discount_for_cart', 'it_exchange_basic_coupons_get_total_discount_for_cart', 10, 2 );

/**
 * Determine if this is a valid product for a certain coupon.
 *
 * @since 1.10.6
 *
 * @param $cart_product array
 * @param $coupon       IT_Exchange_Cart_Coupon
 *
 * @return bool
 */
function it_exchange_basic_coupons_valid_product_for_coupon( $cart_product, $coupon ) {

	$valid = false;

	if ( ! $coupon->is_product_limited() ) {
		$valid = true;
	} else {

		foreach ( $coupon->get_product_categories() as $term ) {

			if ( is_object_in_term( $cart_product['product_id'], 'it_exchange_category', $term->term_id ) ) {
				$valid = true;

				break;
			}
		}

		if ( count( $coupon->get_limited_products() ) ) {
			foreach ( $coupon->get_limited_products() as $product ) {

				if ( $cart_product['product_id'] == $product->ID ) {
					$valid = true;

					break;
				}
			}
		} elseif ( ! $coupon->get_product_categories() ) {
			$valid = true;
		}

		foreach ( $coupon->get_excluded_products() as $product ) {
			if ( $cart_product['product_id'] == $product->ID ) {
				$valid = false;

				break;
			}
		}
	}

	if ( $coupon->is_sale_item_excluded() && it_exchange_is_product_sale_active( $cart_product['product_id'] ) ) {
		$valid = false;
	}

	/**
	 * Can be used by addons to modify if a target product is valid for a coupon.
	 *
	 * @param $valid        bool
	 * @param $cart_product object
	 * @param $coupon       IT_Exchange_Coupon
	 */
	return apply_filters( 'it_exchange_basic_coupons_valid_product_for_coupon', $valid, $cart_product, $coupon );
}

/**
 * Reduces coupon quanity by 1 if coupon was applied to transaction and coupon is tracking usage
 *
 * @since 1.0.2
 *
 * @param integer $transaction_id
 * @return void
*/
function it_exchange_basic_coupons_modify_coupon_quantity_on_transaction( $transaction_id ) {

	_deprecated_function(
		'it_exchange_basic_coupons_modify_coupon_quantity_on_transaction', '1.33',
		'IT_Exchange_Cart_Coupon::modify_quantity_available'
	);

	if ( ! $transaction = it_exchange_get_transaction( $transaction_id ) )
		return false;

	if ( ! $coupons = it_exchange_get_transaction_coupons( $transaction ) )
		return;

	// Do we have a cart coupon?
	if ( isset( $coupons['cart'] ) && ! empty( $coupons['cart'] ) ) {
		$coupon = reset( $coupons['cart'] );

		// Does this coupon have unlimited quantity
		if ( ! $limited = get_post_meta( $coupon['id'], '_it-basic-limit-quantity', true ) )
			return;

		// Does this coupon have a quantity?
		if ( ! $quantity = get_post_meta( $coupon['id'], '_it-basic-quantity', true ) )
			return;

		// Decrease quantity by one
		$quantity = absint( $quantity );
		$quantity--;

		update_post_meta( $coupon['id'], '_it-basic-quantity', $quantity );
	}
}

/**
 * Track the customer's use of this coupon on checkout
 *
 * @since 1.9.2
 *
 * @param integer $transaction_id
 * @return void
*/
function it_exchange_basic_coupons_bump_for_customer_on_checkout( $transaction_id ) {

	_deprecated_function(
		'it_exchange_basic_coupons_bump_for_customer_on_checkout', '1.33',
		'IT_Exchange_Cart_Coupon::bump_customer_coupon_frequency'
	);

	if ( ! $transaction = it_exchange_get_transaction( $transaction_id ) )
		return false;

	if ( ! $coupons = it_exchange_get_transaction_coupons( $transaction ) )
		return;

	// Do we have a cart coupon?
	if ( isset( $coupons['cart'] ) && ! empty( $coupons['cart'] ) ) {
		$coupon = reset( $coupons['cart'] );

		$coupon_id   = $coupon['id'];
		$customer_id = $transaction->customer_id;
		it_exchange_basic_coupons_bump_customer_coupon_frequency( $coupon_id, $customer_id );
	}
}

/**
 * Returns the coupon discount label
 *
 * @since 0.4.0
 *
 * @param string $label   incoming from WP filter. Not used here.
 * @param array  $options $options['coupon'] should have the coupon object
 * @return string
*/
function it_exchange_basic_coupons_get_discount_label( $label, $options = array() ) {

	$coupon = empty( $options['coupon'] ) ? false : $options['coupon'];

	if ( ! $coupon || ! $coupon instanceof IT_Exchange_Cart_Coupon ) {
		return '';
	}

	if ( IT_Exchange_Cart_Coupon::TYPE_FLAT == $coupon->get_amount_type() ) {
		return it_exchange_format_price( $coupon->get_amount_number() );
	} else {
		return $coupon->get_amount_number() . '%';
	}
}

add_filter( 'it_exchange_get_coupon_discount_label', 'it_exchange_basic_coupons_get_discount_label', 10, 2 );

/**
 * Remove coupon from cart
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_handle_remove_coupon_from_cart_request() {
	$var = it_exchange_get_field_name( 'remove_coupon' ) . '-cart';
	if ( empty( $_REQUEST[$var] ) )
		return;

	foreach( (array) $_REQUEST[$var] as $code ) {
		it_exchange_remove_coupon( 'cart', $code );
	}

	if ( it_exchange_is_multi_item_cart_allowed() )
		$url = it_exchange_get_page_url( 'cart' );
	else
		$url = it_exchange_clean_query_args( array( it_exchange_get_field_name( 'sw_cart_focus' ) ) );

	it_exchange_add_message( 'notice', __( 'Coupon removed', 'it-l10n-ithemes-exchange' ) );
	wp_redirect( esc_url_raw( $url ) );
	die();
}
add_action( 'template_redirect', 'it_exchange_basic_coupons_handle_remove_coupon_from_cart_request', 9 );

/**
 * Removes a coupon from the cart
 *
 * @param boolean $result default result passed by apply_filters
 * @param array   $options The $code parameter must contain the coupon code.
 * @return boolean
*/
function it_exchange_basic_coupons_remove_coupon_from_cart( $result, $options=array() ) {

	$coupon_code = empty( $options['code'] ) ? false : $options['code'];
	$coupon = it_exchange_get_coupon_from_code( $coupon_code, 'cart' );

	if ( empty( $coupon_code ) || empty( $coupon ) ) {
		return false;
	}

	$coupons = it_exchange_get_cart_data( 'basic_coupons' );

	if ( isset( $coupons[ $coupon_code ] ) ) {
		unset( $coupons[ $coupon_code ] );
	}

	// Unset coupons
	it_exchange_update_cart_data( 'basic_coupons', $coupons );

	/**
	 * Fires when a coupon is removed from the cart.
	 *
	 * @since 1.33 Add $coupon parameter
	 *
	 * @param string                  $coupon_code
	 * @param IT_Exchange_Cart_Coupon $coupon
	 */
	do_action( 'it_exchange_basic_coupons_remove_coupon_from_cart', $coupon_code, $coupon );

	return true;
}
add_filter( 'it_exchange_remove_coupon_for_cart', 'it_exchange_basic_coupons_remove_coupon_from_cart', 10, 2 );

/**
 * Returns the summary needed for a transaction
 *
 * @since 0.4.0
 *
 * @param string $summary passed in by WP filter. Ignored here.
 * @param mixed  $transaction_coupon the coupon data stored in the transaction
 * @return string summary
*/
function it_exchange_basic_coupons_transaction_summary( $summary, $transaction_coupon ) {
	$transaction_coupon = reset( $transaction_coupon );

	$id       = empty( $transaction_coupon['id'] )            ? false : $transaction_coupon['id'];
	$title    = empty( $transaction_coupon['title'] )         ? false : $transaction_coupon['title'];
	$code     = empty( $transaction_coupon['code'] )          ? false : $transaction_coupon['code'];
	$number   = empty( $transaction_coupon['amount_number'] ) ? false : $transaction_coupon['amount_number'];
	$type     = empty( $transaction_coupon['amount_type'] )   ? false : $transaction_coupon['amount_type'];

	$url = trailingslashit( get_admin_url() ) . 'admin.php';
	$url = add_query_arg( array( 'page' => 'it-exchange-edit-basic-coupon', 'post' => $id ), $url );

	$link = '<a href="' . esc_url( $url ) . '">' . __( 'View Coupon', 'it-l10n-ithemes-exchange' ) . '</a>';

	$string = '';

	if ( $title ) {
		$string .= $title . ': ';
	}

	if ( $code ) {
		$string .= $code . ' | ';
	}

	if ( $number && $type ) {
		$string .= implode( '', array( $number, $type ) ) . ' | ';
	}

	$string .= ' ' . $link;

	return $string;
}
add_filter( 'it_exchange_get_transaction_cart_coupon_summary', 'it_exchange_basic_coupons_transaction_summary', 10, 2 );

/**
 * Returns the coupon discount type
 *
 * @since 0.4.0
 *
 * @param string $method default type passed by WP filters. Not used here.
 * @param array $options includes the ID we're looking for.
 *
 * @return string
*/
function it_exchange_basic_coupons_get_discount_method( $mehod, $options=array() ) {

	if ( empty( $options['id'] ) || ! $coupon = it_exchange_get_coupon( $options['id'] ) )
		return false;

	return empty( $coupon->amount_type ) ? false : $coupon->amount_type;
}
add_filter( 'it_exchange_get_coupon_discount_method', 'it_exchange_basic_coupons_get_discount_method', 10, 2 );

function it_exchange_addon_basic_coupons_replace_order_table_tag_before_total_row( $email_obj, $options ) {

	if ( ! it_exchange_get_transaction_coupons_total_discount( $email_obj->transaction_id, false ) ) {
		return;
	}

	?>
	<tr>
		<td colspan="2" style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Savings', 'it-l10n-ithemes-exchange' ); ?></td>
		<td style="padding: 10px;border:1px solid #DDD;"><?php echo it_exchange_get_transaction_coupons_total_discount( $email_obj->transaction_id ); ?></td>
	</tr>
	<?php
}
add_action( 'it_exchange_replace_order_table_tag_before_total_row', 'it_exchange_addon_basic_coupons_replace_order_table_tag_before_total_row', 10, 2 );