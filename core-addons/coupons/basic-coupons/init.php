<?php
/**
 * Basic Coupons
 *
 * @package IT_Exchange
 * @since   0.4.0
 */

use iThemes\Exchange\REST\Request;

if ( is_admin() ) {
	require __DIR__ . '/admin.php';
}

/**
 * Include the IT_Exchange_Cart_Coupon class.
 *
 * @since 1.33
 */
require_once __DIR__ . '/coupon.php';
require_once __DIR__ . '/deprecated.php';

/**
 * Adds meta data for Basic Coupons to the coupon object
 *
 * @since 0.4.0
 *
 * @param array              $data
 * @param IT_Exchange_Coupon $object
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
	foreach ( $post_meta_keys as $property => $key ) {
		$data[ $property ] = get_post_meta( $object->ID, $key, true );
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
 *
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
 * @param array    $_
 * @param ITE_Cart $cart
 *
 * @return false|IT_Exchange_Coupon[]
 */
function it_exchange_basic_coupons_applied_cart_coupons( $_ = array(), ITE_Cart $cart = null ) {

	$cart = $cart ?: it_exchange_get_current_cart();

	$items = $cart->get_items( 'coupon', true )->filter( function ( ITE_Coupon_Line_Item $item ) {
		return $item->get_coupon() && $item->get_coupon()->get_type() === 'cart';
	} )->unique( function ( ITE_Coupon_Line_Item $item ) {
		return $item->get_coupon()->get_code();
	} );

	$coupons = array();

	foreach ( $items as $item ) {
		$coupons[] = $item->get_coupon();
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
 * @param bool     $_    Sent from WP filter. Discarded here.
 * @param ITE_Cart $cart Cart to check against.
 *
 * @return bool
 */
function it_exchange_basic_coupons_accepting_cart_coupons( $_ = false, $cart = null ) {
	return ! (bool) it_exchange_get_applied_coupons( 'cart', $cart );
}

add_filter( 'it_exchange_accepting_cart_coupons', 'it_exchange_basic_coupons_accepting_cart_coupons', 10, 2 );

/**
 * Return the form field for applying a coupon code to a cart
 *
 * @since 0.4.0
 *
 * @param mixed $incoming sent from WP filter. Discarded here.
 *
 * @return string
 */
function it_exchange_base_coupons_apply_cart_coupon_field( $incoming = false, $options = array() ) {
	$defaults = array(
		'class'       => 'apply-coupon',
		'placeholder' => __( 'Coupon Code', 'it-l10n-ithemes-exchange' ),
	);
	$options  = ITUtility::merge_defaults( $options, $defaults );
	$var      = it_exchange_get_field_name( 'apply_coupon' ) . '-cart';

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
	if ( ! $coupon_code = empty( $_REQUEST[ $var ] ) ? false : $_REQUEST[ $var ] ) {
		return;
	}

	it_exchange_apply_coupon( 'cart', $coupon_code );
}

add_action( 'it_exchange_update_cart', 'it_exchange_basic_coupons_handle_coupon_on_cart_update' );

/**
 * Applies a coupon code to a cart if it exists and is valid
 *
 * @since 0.4.0
 *
 * @param boolean   $result  this is default to false. gets set by apply_filters
 * @param array     $options - must contain coupon key
 * @param \ITE_Cart $cart
 *
 * @return boolean
 */
function it_exchange_basic_coupons_apply_to_cart( $result, $options = array(), ITE_Cart $cart = null ) {

	$cart = $cart ?: it_exchange_get_current_cart();

	// Set coupon code. Return false if one is not available
	$coupon_code = empty( $options['code'] ) ? false : $options['code'];
	$coupon      = it_exchange_get_cart_coupon_from_code( null, $coupon_code );

	if ( ! $coupon ) {
		$cart->get_feedback()->add_error( __( 'Invalid coupon', 'it-l10n-ithemes-exchange' ) );

		return false;
	}

	if ( ! doing_action( 'it_exchange_apply_coupon_to_cart' ) ) {
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
	 * @since 2.0.0 Add $cart parameter.
	 *
	 * @param $addon_result  bool
	 * @param $options       array
	 * @param $coupon        IT_Exchange_Coupon
	 * @param $cart          \ITE_Cart
	 */
	$addon_result = apply_filters( 'it_exchange_basic_coupons_apply_coupon_to_cart', null, $options, $coupon, $cart );

	if ( $addon_result === false ) {
		return $addon_result;
	}

	// Format data for session
	$coupon = array(
		'id'    => $coupon->ID,
		'title' => $coupon->get_title(),
		'code'  => $coupon->get_code()
	);

	if ( $cart->is_current() ) {
		// Add to session data
		$data = array( $coupon['code'] => $coupon );
		it_exchange_update_cart_data( 'basic_coupons', $data );
		do_action_deprecated( 'it_exchange_basic_coupon_applied', array( $data ), '2.0.0', 'it_exchange_add_coupon_to_cart' );
	}

	if ( $cart->is_current() ) {
		$cart->get_feedback()->add_notice( __( 'Coupon applied', 'it-l10n-ithemes-exchange' ) );
	}

	return true;
}

add_action( 'it_exchange_apply_coupon_to_cart', 'it_exchange_basic_coupons_apply_to_cart', 10, 3 );

/**
 * Has the frequency limit for this coupon been met.
 *
 * Grabs array of timestamps specified (or current) user has used the specific coupon.
 * Determines # of seconds before now to count uses
 * Makes sure that customer has not met limit of use in calculated time period
 *
 * @since 1.9.2
 *
 * @param int|IT_Exchange_Cart_Coupon $coupon      wp post id for the coupon
 * @param int|bool                    $customer_id Customer ID to check against. If false, current customer is used.
 *
 * @return boolean
 */
function it_exchange_basic_coupon_frequency_limit_met_by_customer( $coupon, $customer_id = false ) {

	$customer_id = $customer_id ?: it_exchange_get_current_customer_id();

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
	foreach ( (array) $current_frequencies as $date ) {
		if ( $date > $earliest_limit ) {
			$relevant_uses ++;
		}
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
			$customer       = it_exchange_get_current_customer();
			$coupon_history = get_option( '_it_exchange_basic_coupon_history_' . $customer->data->email );
		}
	} else {
		$coupon_history = get_user_meta( $customer_id, '_it_exchagne_basic_coupon_history', true );
	}

	if ( empty( $coupon_id ) ) {
		$validated_history = $coupon_history;
	} else {
		$validated_history = empty( $coupon_history[ $coupon_id ] ) ? array() : $coupon_history[ $coupon_id ];
	}

	return apply_filters( 'it_exchange_basic_coupons_get_customer_coupon_frequency', $validated_history, $coupon_id, $customer_id, $coupon_history );
}

/**
 * Clear cart coupons when cart is emptied
 *
 * @since 0.4.0
 *
 * @param \ITE_Cart $cart
 *
 * @return void
 */
function it_exchange_clear_cart_coupons_on_empty( ITE_Cart $cart ) {
	if ( $cart->is_current() ) {
		it_exchange_remove_cart_data( 'basic_coupons' );
	}
}

add_action( 'it_exchange_empty_cart', 'it_exchange_clear_cart_coupons_on_empty' );

/**
 * Return the form checkbox for removing a coupon code to a cart
 *
 * @since 0.4.0
 *
 * @param mixed $incoming sent from WP filter. Discarded here.
 *
 * @return string
 */
function it_exchange_base_coupons_remove_cart_coupon_html( $incoming = false, $code, $options = array() ) {
	$defaults = array(
		'class'  => 'remove-coupon',
		'format' => 'link',
		'label'  => _x( '&times;', 'html representation for multiplication symbol (x)', 'it-l10n-ithemes-exchange' ),
	);
	$options  = ITUtility::merge_defaults( $options, $defaults );

	$var = it_exchange_get_field_name( 'remove_coupon' ) . '-cart';

	if ( 'checkbox' == $options['format'] ) {
		return '<input type="checkbox" class="' . esc_attr( $options['class'] ) . '" name="' . esc_attr( $var ) . '[]" value="' . esc_attr( $options['code'] ) . '" />&nbsp;' . esc_attr( $options['label'] );
	} else {
		$url = it_exchange_clean_query_args( array( it_exchange_get_field_name( 'sw_cart_focus' ) ) );
		$url = add_query_arg( $var . '[]', $options['code'], $url );

		return '<a data-coupon-code="' . esc_attr( $options['code'] ) . '" class="' . esc_attr( $options['class'] ) . '" href="' . esc_url( $url ) . '">' . esc_attr( $options['label'] ) . '</a>';
	}
}

add_filter( 'it_exchange_remove_cart_coupon_html', 'it_exchange_base_coupons_remove_cart_coupon_html', 10, 3 );

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
	$options  = ITUtility::merge_defaults( $options, $defaults );

	$cart    = it_exchange_get_current_cart();
	$coupons = $cart->get_items( 'coupon', true )->filter( function ( ITE_Coupon_Line_Item $item ) {
		return $item->get_coupon()->get_type() === 'cart';
	} );
	$total   = $coupons->total();

	return $options['format_price'] ? it_exchange_format_price( $total ) : - $total;
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
	 * @param $cart_product array
	 * @param $coupon       IT_Exchange_Coupon
	 */
	return apply_filters( 'it_exchange_basic_coupons_valid_product_for_coupon', $valid, $cart_product, $coupon );
}

/**
 * Returns the coupon discount label
 *
 * @since 0.4.0
 *
 * @param string $label   incoming from WP filter. Not used here.
 * @param array  $options $options['coupon'] should have the coupon object
 *
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
	if ( empty( $_REQUEST[ $var ] ) ) {
		return;
	}

	foreach ( (array) $_REQUEST[ $var ] as $code ) {
		it_exchange_remove_coupon( 'cart', $code );
	}

	if ( it_exchange_is_multi_item_cart_allowed() ) {
		$url = it_exchange_get_page_url( 'cart' );
	} else {
		$url = it_exchange_clean_query_args( array( it_exchange_get_field_name( 'sw_cart_focus' ) ) );
	}

	it_exchange_add_message( 'notice', __( 'Coupon removed', 'it-l10n-ithemes-exchange' ) );
	wp_redirect( esc_url_raw( $url ) );
	die();
}

add_action( 'template_redirect', 'it_exchange_basic_coupons_handle_remove_coupon_from_cart_request', 9 );

/**
 * Removes a coupon from the cart
 *
 * @param boolean $result  default result passed by apply_filters
 * @param array   $options The $code parameter must contain the coupon code.
 *
 * @return boolean
 */
function it_exchange_basic_coupons_remove_coupon_from_cart( $result, $options = array() ) {

	$coupon_code = empty( $options['code'] ) ? false : $options['code'];
	$coupon      = it_exchange_get_coupon_from_code( $coupon_code, 'cart' );

	if ( empty( $coupon_code ) || empty( $coupon ) ) {
		return false;
	}

	$cart = empty( $options['cart'] ) ? it_exchange_get_current_cart() : $options['cart'];

	if ( ! $cart->is_current() ) {
		return true;
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
 * @param string $summary            passed in by WP filter. Ignored here.
 * @param mixed  $transaction_coupon the coupon data stored in the transaction
 *
 * @return string summary
 */
function it_exchange_basic_coupons_transaction_summary( $summary, $transaction_coupon ) {
	$transaction_coupon = reset( $transaction_coupon );

	$id     = empty( $transaction_coupon['id'] ) ? false : $transaction_coupon['id'];
	$title  = empty( $transaction_coupon['title'] ) ? false : $transaction_coupon['title'];
	$code   = empty( $transaction_coupon['code'] ) ? false : $transaction_coupon['code'];
	$number = empty( $transaction_coupon['amount_number'] ) ? false : $transaction_coupon['amount_number'];
	$type   = empty( $transaction_coupon['amount_type'] ) ? false : $transaction_coupon['amount_type'];

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
 * @param string $method  default type passed by WP filters. Not used here.
 * @param array  $options includes the ID we're looking for.
 *
 * @return string
 */
function it_exchange_basic_coupons_get_discount_method( $mehod, $options = array() ) {

	if ( empty( $options['id'] ) || ! $coupon = it_exchange_get_coupon( $options['id'] ) ) {
		return false;
	}

	return empty( $coupon->amount_type ) ? false : $coupon->amount_type;
}

add_filter( 'it_exchange_get_coupon_discount_method', 'it_exchange_basic_coupons_get_discount_method', 10, 2 );

function it_exchange_addon_basic_coupons_replace_order_table_tag_before_total_row( $email_obj, $options ) {

	if ( ! it_exchange_get_transaction_coupons_total_discount( $email_obj->transaction_id, false ) ) {
		return;
	}

	?>
    <tr>
        <td colspan="2"
            style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Savings', 'it-l10n-ithemes-exchange' ); ?></td>
        <td style="padding: 10px;border:1px solid #DDD;"><?php echo it_exchange_get_transaction_coupons_total_discount( $email_obj->transaction_id ); ?></td>
    </tr>
	<?php
}

add_action( 'it_exchange_replace_order_table_tag_before_total_row', 'it_exchange_addon_basic_coupons_replace_order_table_tag_before_total_row', 10, 2 );

add_action( 'it_exchange_register_coupon_types', function ( ITE_Coupon_Types $types ) {
	$type = new ITE_Coupon_Type( 'cart', array(
		'class'            => 'IT_Exchange_Cart_Coupon',
		'schema'           => array(
			'title'      => 'cart-coupon',
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'type'       => 'object',
			'properties' => array(
				'id'                 => array(
					'type'        => 'integer',
					'description' => __( 'A unique ID for the coupon.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' )
				),
				'name'               => array(
					'type'        => 'string',
					'description' => __( 'An admin reference name for the coupon.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' ),
				),
				'code'               => array(
					'type'        => 'string',
					'description' => __( "A unique code that customer's enter to receive the discount.", 'it-l10n-ithemes--exchange' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
				),
				'amount'             => array(
					'type'        => 'object',
					'description' => __( 'The amount of the discount receieved.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
					'properties'  => array(
						'number' => array(
							'type'        => 'number',
							'description' => __( 'The total amount of the discount.', 'it-l10n-ithemes-exchange' ),
							'minimum'     => 0,
							'required'    => true,
						),
						'type'   => array(
							'type'        => 'string',
							'description' => __( 'How the amount is calculated.', 'it-l10n-ithemes-exchange' ),
							'enum'        => array( 'flat', 'percent' ),
							'required'    => true,
						)
					),
				),
				'application_method' => array(
					'type'        => 'string',
					'description' => __( 'How the discount is applied to the customer.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' ),
					'enum'        => array( 'cart', 'product' )
				),
				'start_date'         => array(
					'description' => __( 'When this coupon becomes available for use.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' ),
					'oneOf'       => array(
						array( 'type' => 'string', 'format' => 'date-time', ),
						array( 'type' => 'string', 'enum' => array( '' ) ),
					),
				),
				'end_date'           => array(
					'description' => __( 'When this coupon becomes no longer available for use.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' ),
					'oneOf'       => array(
						array( 'type' => 'string', 'format' => 'date-time', ),
						array( 'type' => 'string', 'enum' => array( '' ) ),
					),
				),
				'limit_quantity'     => array(
					'type'        => 'boolean',
					'description' => __( 'Whether to limit the number of times the coupon can be used by any customer.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' ),
				),
				'quantity'           => array(
					'type'        => 'integer',
					'description' => __( 'The total number of times the coupon can be used by any customer.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' )
				),
				'uses'               => array(
					'type'        => 'integer',
					'description' => __( 'The total number of times the coupon has been used.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'stats' ),
					'readonly'    => true,
				),
				'limit_customer'     => array(
					'type'        => 'boolean',
					'description' => __( 'Whether to limit the usage of the coupon to a specific customer.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' )
				),
				'customer'           => array(
					'type'        => 'integer',
					'description' => __( 'The ID of the customer who can use the coupon.', 'it-l10n-ithemes-exchange' ),
					'default'     => 0,
					'context'     => array( 'view', 'edit' ),
					'args'        => array(
						'validate_callback' => function ( $value ) {

							if ( ! $value ) {
								return 0;
							}

							$customer = it_exchange_get_customer( $value );

							if ( ! $customer ) {
								return new WP_Error( '', sprintf( __( 'No customer exists with id #%d.', 'it-l10n-ithemes-exchange' ), $value ) );
							}

							return true;
						}
					)
				),
				'limit_frequency'    => array(
					'type'        => 'boolean',
					'description' => __( 'Limit the frequency at which a customer can use the coupon.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' ),
				),
				'frequency'          => array(
					'type'       => 'object',
					'context'    => array( 'view', 'edit' ),
					'properties' => array(
						'uses'            => array(
							'description' => __( 'The number of times the coupon can be used within the specified interval.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'number',
							'multipleOf'  => 1,
						),
						'interval_length' => array(
							'type'        => 'number',
							'mutlipleOf'  => 1,
							'minimum'     => 1,
							'maximum'     => 30,
							'description' => __( 'The length of the interval type during which the coupon use is limited.', 'it-l10n-ithemes-exchange' )
						),
						'interval_unit'   => array(
							'type'        => 'string',
							'description' => __( 'The type of interval during which the coupon use is limited.', 'it-l10n-ithemes-exchange' ),
							'enum'        => array( 'day', 'week', 'year' )
						),
					),
				),
				'limit_product'      => array(
					'type'        => 'boolean',
					'description' => __( 'Whether to limit the usage of the coupon to specific products.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' )
				),
				'product'            => array(
					'type'       => 'object',
					'context'    => array( 'view', 'edit' ),
					'properties' => array(
						'included_products'  => array(
							'type'        => 'array',
							'description' => __( 'The IDs of the products the coupon can be applied to.', 'it-l10n-ithemes-exchange' ),
							'context'     => array( 'view', 'edit' ),
							'items'       => array(
								'type' => 'integer',
							),
							'args'        => array(
								'validate_callback' => function ( $value ) {
									if ( ! is_array( $value ) ) {
										return false;
									}

									foreach ( $value as $id ) {
										if ( ! it_exchange_get_product( $id ) ) {
											return new WP_Error( '', sprintf( __( 'No product exists with id #%d.', 'it-l10n-ithemes-exchange' ), $value ) );
										}
									}

									return true;
								},
							),
						),
						'excluded_products'  => array(
							'type'        => 'array',
							'description' => __( 'The IDs of the products the coupon cannot be applied to.', 'it-l10n-ithemes-exchange' ),
							'context'     => array( 'view', 'edit' ),
							'items'       => array(
								'type' => 'integer',
							),
							'args'        => array(
								'validate_callback' => function ( $value ) {
									if ( ! is_array( $value ) ) {
										return false;
									}

									foreach ( $value as $id ) {
										if ( ! it_exchange_get_product( $id ) ) {
											return new WP_Error( '', sprintf( __( 'No product exists with id #%d.', 'it-l10n-ithemes-exchange' ), $value ) );
										}
									}

									return true;
								},
							),
						),
						'product_categories' => array(
							'type'        => 'array',
							'description' => __( 'The product categories that the coupon can be applied to.', 'it-l10n-ithemes-exchange' ),
							'context'     => array( 'view', 'edit' ),
							'items'       => array(
								'type' => 'number',
							),
							'args'        => array(
								'validate_callback' => function ( $value ) {
									if ( ! is_array( $value ) ) {
										return false;
									}

									foreach ( $value as $id ) {
										/** @var WP_Term $term */
										$term = ! get_term( $id, 'it_exchange_category' );

										if ( ! $term || $term->taxonomy !== 'it_exchange_category' ) {
											return new WP_Error( '', sprintf( __( 'No category exists with id #%d.', 'it-l10n-ithemes-exchange' ), $value ) );
										}
									}

									return true;
								}
							)
						),
						'exclude_sales'      => array(
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
							'description' => __( 'Whether to exclude products on sale from discount calculations.', 'it-l10n-ithemes-exchange' ),
						),
					),
				),
			)
		),
		'rest_serializer'  => function ( IT_Exchange_Cart_Coupon $coupon ) {
			return array(
				'id'                 => $coupon->get_ID(),
				'name'               => $coupon->post_title,
				'code'               => $coupon->get_code(),
				'amount'             => array(
					'number' => $coupon->get_amount_number(),
					'type'   => $coupon->get_amount_type(),
				),
				'application_method' => $coupon->get_application_method(),

				'start_date' => \iThemes\Exchange\REST\format_rfc339( $coupon->get_start_date() ),
				'end_date'   => \iThemes\Exchange\REST\format_rfc339( $coupon->get_start_date() ),

				'limit_quantity' => $coupon->is_quantity_limited(),
				'quantity'       => $coupon->is_quantity_limited() ? $coupon->get_allotted_quantity() : 0,
				'uses'           => $coupon->get_total_uses(),

				'limit_customer' => $coupon->is_customer_limited(),
				'customer'       => $coupon->is_customer_limited() && $coupon->get_customer() ? $coupon->get_customer()->ID : 0,

				'limit_frequency' => $coupon->is_frequency_limited(),
				'frequency'       => array(
					'uses'            => $coupon->get_frequency_times(),
					'interval_length' => $coupon->get_frequency_length(),
					'interval_unit'   => $coupon->get_frequency_units(),
				),

				'limit_product' => $coupon->is_product_limited(),
				'product'       => array(
					'included'      => $coupon->is_product_limited() ? array_map( function ( $product ) {
						return $product->ID;
					}, $coupon->get_limited_products() ) : array(),
					'excluded'      => $coupon->is_product_limited() ? array_map( function ( $product ) {
						return $product->ID;
					}, $coupon->get_excluded_products() ) : array(),
					'categories'    => $coupon->is_product_limited() ? $coupon->get_product_categories( true ) : array(),
					'exclude_sales' => $coupon->is_sale_item_excluded(),
				)
			);
		},
		'update_from_rest' => function ( Request $request ) {

			/** @var IT_Exchange_Cart_Coupon $coupon */
			$coupon = $request->get_route_object( 'coupon_id' );
			$meta   = array();

			if ( ! $coupon ) {
				$meta['_it-basic-code'] = $request['code'];
			}

			$meta['_it-basic-amount-number']      = it_exchange_convert_to_database_number( $request['amount']['number'] );
			$meta['_it-basic-amount-type']        = $request['amount']['type'];
			$meta['_it-basic-start-date']         = $request['start_date'] ? date( 'Y-m-d H:i:s', strtotime( $request['start_date'] ) ) : '';
			$meta['_it-basic-end-date']           = $request['end_date'] ? date( 'Y-m-d H:i:s', strtotime( $request['end_date…'] ) ) : '';
			$meta['_it-basic-apply-discount']     = $request['application_method'];
			$meta['_it-basic-limit-quantity']     = $request['limit_quantity'];
			$meta['_it-basic-allotted-quantity']  = $request['quantity'];
			$meta['_it-basic-limit-product']      = $request['limit_product'];
			$meta['_it-basic-product-categories'] = $request['product']['categories'];
			$meta['_it-basic-product-id']         = $request['product']['included'];
			$meta['_it-basic-excluded-products']  = $request['product']['excluded'];
			$meta['_it-basic-sales-excluded']     = $request['product']['exclude_sales'];
			$meta['_it-basic-limit-frequency']    = $request['limit_frequency'];
			$meta['_it-basic-frequency-times']    = $request['frequency']['uses'];
			$meta['_it-basic-frequency-length']   = $request['frequency']['interval_length'];
			$meta['_it-basic-frequency-units']    = $request['frequency']['interval_unit'];
			$meta['_it-basic-customer']           = $request['customer'];
			$meta['_it-basic-limit-customer']     = $request['limit_customer'];


			if ( $coupon ) {
				$prev_allotted = $coupon->get_allotted_quantity();

				foreach ( $meta as $key => $value ) {
					update_post_meta( $coupon->get_ID(), $key, $value );
				}

				$coupon = it_exchange_get_coupon( $coupon->get_ID(), 'cart' );

				if ( $prev_allotted !== $coupon->get_allotted_quantity() ) {
					$coupon->modify_quantity_available( $coupon->get_allotted_quantity() - $prev_allotted );
				}

				return $coupon;
			}

			$coupon_id = it_exchange_add_coupon( array(
				'post_meta'    => $meta,
				'type'         => 'cart',
				'post_title'   => $request['name'],
				'post_content' => $request['code'],
			) );

			if ( is_wp_error( $coupon_id ) ) {
				return $coupon_id;
			}

			if ( $coupon_id === false ) {
				return new WP_Error(
					'it_exchange_rest_create_coupon_failed',
					__( 'Unable to create a coupon from the given data.', 'it-l10n-ithemes-exchange' ),
					array( 'status' => WP_Http::INTERNAL_SERVER_ERROR )
				);
			}

			return it_exchange_get_coupon( $coupon_id, 'cart' );
		}
	) );

	$types::register( $type );
} );
