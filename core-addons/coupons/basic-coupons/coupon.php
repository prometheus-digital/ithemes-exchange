<?php
/**
 * Cart Coupon class.
 *
 * @since   1.33
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Cart_Coupon
 */
class IT_Exchange_Cart_Coupon extends IT_Exchange_Coupon {

	const TYPE_PERCENT = '%';
	const TYPE_FLAT = 'amount';

	const APPLY_CART = 'cart';
	const APPLY_PRODUCT = 'per-product';

	const E_NO_QUANTITY = 100;
	const E_INVALID_CUSTOMER = 101;
	const E_INVALID_PRODUCTS = 102;
	const E_INVALID_START = 103;
	const E_INVALID_END = 104;
	const E_FREQUENCY = 105;

	/**
	 * @var IT_Exchange_Product[]
	 */
	private $products = array();

	/**
	 * IT_Exchange_Cart_Coupon constructor.
	 *
	 * @param bool|mixed $post
	 */
	public function __construct( $post ) {
		parent::__construct( $post );

		if ( is_array( $this->product_id ) ) {
			$products = $this->product_id;

			$this->product_id = reset( $products );
			$this->products   = array_map( 'it_exchange_get_product', $products );
		} else {
			$this->products[] = it_exchange_get_product( $this->product_id );
		}

		$this->products = array_filter( $this->products );
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'cart';
	}

	/**
	 * Increment usage of this coupon.
	 *
	 * @since 1.33
	 *
	 * @param object $transaction_object
	 */
	public function increment_usage( $transaction_object ) {
		parent::increment_usage( $transaction_object );

		if ( $this->is_quantity_limited() ) {
			$this->modify_quantity_available( - 1 );
		}

		if ( $this->is_frequency_limited() ) {
			$this->bump_customer_coupon_frequency( $transaction_object );
		}
	}

	/**
	 * Decrement the usage of this coupon.
	 *
	 * @since 1.33
	 *
	 * @param object $transaction_object
	 */
	public function decrement_usage( $transaction_object ) {
		parent::decrement_usage( $transaction_object );

		if ( $this->is_quantity_limited() ) {
			$this->modify_quantity_available( + 1 );
		}

		if ( $this->is_frequency_limited() ) {
			$this->reduce_customer_coupon_frequency( $transaction_object );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function valid_for_product( ITE_Cart_Product $product ) {
		return it_exchange_basic_coupons_valid_product_for_coupon( $product->bc(), $this );
	}

	/**
	 * Reduce the remaining quantity available by a fixed amount.
	 *
	 * @since 1.33
	 *
	 * @param int $by
	 */
	public function modify_quantity_available( $by ) {

		$quantity = $this->get_remaining_quantity();
		$quantity += $by;

		update_post_meta( $this->get_ID(), '_it-basic-quantity', $quantity );
	}

	/**
	 * Bump the customer coupon uses.
	 *
	 * @since 1.33
	 *
	 * @param object $transaction_object
	 */
	public function bump_customer_coupon_frequency( $transaction_object ) {

		$customer_id = $transaction_object->customer_id;

		if ( ! $customer_id ) {
			return;
		}

		$coupon_history = it_exchange_basic_coupons_get_customer_coupon_frequency( false, $customer_id );

		if ( empty( $coupon_history[ $this->get_ID() ] ) ) {
			$coupon_history[ $this->get_ID() ] = array();
		}

		$coupon_history[ $this->get_ID() ][ $transaction_object->cart_id ] = date_i18n( 'U' );

		if ( ! empty( $transaction_object->is_guest_checkout ) ) {
			update_option( '_it_exchange_basic_coupon_history_' . $customer_id, $coupon_history );
		} else {
			update_user_meta( $customer_id, '_it_exchagne_basic_coupon_history', $coupon_history );
		}
	}

	/**
	 * Reduce the customer coupon frequency. This will remove the bump associated with the given transaction.
	 *
	 * @since 1.33
	 *
	 * @param object $transaction_object
	 */
	public function reduce_customer_coupon_frequency( $transaction_object ) {

		$customer_id = $transaction_object->customer_id;

		if ( ! $customer_id ) {
			return;
		}

		$coupon_history = it_exchange_basic_coupons_get_customer_coupon_frequency( false, $customer_id );

		if ( empty( $coupon_history[ $this->get_ID() ] ) || empty( $coupon_history[ $this->get_ID() ][ $transaction_object->cart_id ] ) ) {
			return;
		}

		unset( $coupon_history[ $this->get_ID() ][ $transaction_object->cart_id ] );

		if ( ! empty( $transaction_object->is_guest_checkout ) ) {
			update_option( '_it_exchange_basic_coupon_history_' . $customer_id, $coupon_history );
		} else {
			update_user_meta( $customer_id, '_it_exchagne_basic_coupon_history', $coupon_history );
		}
	}

	/**
	 * Get data to save to the transaction object.
	 *
	 * @since 1.33
	 *
	 * @return array
	 */
	public function get_data_for_transaction_object() {
		$data = parent::get_data_for_transaction_object();

		$data['amount_number'] = it_exchange_convert_to_database_number( $this->get_amount_number() );
		$data['amount_type']   = $this->get_amount_type();
		$data['start_date']    = $this->get_start_date() ? $this->get_start_date()->format( 'Y-m-d H:i:s' ) : '';
		$data['end_date']      = $this->get_end_date() ? $this->get_end_date()->format( 'Y-m-d H:i:s' ) : '';

		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public static function supported_data_for_transaction_object() {
		return array_merge( parent::supported_data_for_transaction_object(), array(
			'amount_number',
			'amount_type',
			'start_date',
			'end_date'
		) );
	}

	/**
	 * Validate the coupon.
	 *
	 * @since 1.35
	 *
	 * @throws Exception
	 */
	public function validate( ITE_Cart $cart = null ) {

		$cart = $cart ? $cart : it_exchange_get_current_cart();

		if ( $this->is_quantity_limited() && ! $this->get_remaining_quantity() ) {
			throw new Exception( __( 'This coupon has reached its maximum uses.', 'it-l10n-ithemes-exchange' ), self::E_NO_QUANTITY );
		}

		if ( $this->is_customer_limited() && ( ! $cart->get_customer() || $cart->get_customer()->ID != $this->get_customer()->ID ) ) {
			throw new Exception( __( 'Invalid coupon.', 'it-l10n-ithemes-exchange' ), self::E_INVALID_CUSTOMER );
		}

		$has_product = false;

		foreach ( $cart->get_items( 'product' ) as $product ) {
			if ( it_exchange_basic_coupons_valid_product_for_coupon( $product->bc(), $this ) ) {
				$has_product = true;

				break;
			}
		}

		if ( ! $has_product ) {
			throw new Exception( __( 'Invalid coupon for current cart products.', 'it-l10n-ithemes-exchange' ), self::E_INVALID_PRODUCTS );
		}

		$now = new DateTime();

		// Abort if not within start and end dates
		$start_okay = ! $this->get_start_date() || $this->get_start_date() < $now;
		$end_okay   = ! $this->get_end_date() || $now < $this->get_end_date();

		if ( ! $start_okay ) {

			$message = sprintf(
				__( 'This coupon is not valid until %s.', 'it-l10n-ithemes-exchange' ),
				$this->get_start_date()->format( get_option( 'date_format' ) )
			);

			throw new Exception( $message, self::E_INVALID_START );
		}

		if ( ! $end_okay ) {
			throw new Exception( __( 'This coupon has expired.', 'it-l10n-ithemes-exchange' ), self::E_INVALID_END );
		}

		if ( it_exchange_basic_coupon_frequency_limit_met_by_customer( $this ) ) {
			throw new Exception( __( "This coupon's frequency limit has been met.", 'it-l10n-ithemes-exchange' ), self::E_FREQUENCY );
		}
	}

	/**
	 * Get the coupon title. This is only used internally.
	 *
	 * @since 1.33
	 *
	 * @param bool|false $raw Whether to apply the title filters.
	 *
	 * @return string
	 */
	public function get_title( $raw = false ) {
		return $raw ? $this->post_title : get_the_title( $this->get_ID() );
	}

	/**
	 * Get the type of the discount.
	 *
	 * Either '%' or 'amount', but you should evaluate against the constants provided.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_amount_type() {
		return get_post_meta( $this->get_ID(), '_it-basic-amount-type', true );
	}

	/**
	 * Get the total amount of the discount.
	 *
	 * ie, the 5 in 5% or the 10 in $10 off.
	 *
	 * @since 1.33
	 *
	 * @return float
	 */
	public function get_amount_number() {
		return (float) it_exchange_convert_from_database_number( get_post_meta( $this->get_ID(), '_it-basic-amount-number', true ) );
	}

	/**
	 * Get the coupon start date.
	 *
	 * @since 1.33
	 *
	 * @return DateTime|null
	 */
	public function get_start_date() {

		$start = get_post_meta( $this->get_ID(), '_it-basic-start-date', true );

		if ( ! $start ) {
			return null;
		}

		try {
			return new DateTime( $start );
		} catch ( Exception $e ) {
			return null;
		}
	}

	/**
	 * Set the coupon start date.
	 *
	 * @since 1.33
	 *
	 * @param DateTime|null $start
	 */
	public function set_start_date( DateTime $start = null ) {

		if ( $start ) {
			update_post_meta( $this->get_ID(), '_it-basic-start-date', $start->format( 'Y-m-d H:i:s' ) );
		} else {
			delete_post_meta( $this->get_ID(), '_it-basic-start-date' );
		}
	}

	/**
	 * Get the coupon end date.
	 *
	 * @since 1.33
	 *
	 * @return DateTime|null
	 */
	public function get_end_date() {

		$end = get_post_meta( $this->get_ID(), '_it-basic-end-date', true );

		if ( ! $end ) {
			return null;
		}

		try {
			return new DateTime( $end );
		} catch ( Exception $e ) {
			return null;
		}
	}

	/**
	 * Get the application method.
	 *
	 * @since 1.35
	 *
	 * @return string
	 */
	public function get_application_method() {

		$method = get_post_meta( $this->get_ID(), '_it-basic-apply-discount', true );

		if ( empty( $method ) ) {
			$method = self::APPLY_CART;
		}

		return $method;
	}

	/**
	 * Set the coupon end date.
	 *
	 * @since 1.33
	 *
	 * @param DateTime $end
	 */
	public function set_end_date( DateTime $end = null ) {

		if ( $end ) {
			update_post_meta( $this->get_ID(), '_it-basic-end-date', $end->format( 'Y-m-d H:i:s' ) );
		} else {
			delete_post_meta( $this->get_ID(), '_it-basic-end-date' );
		}
	}

	/**
	 * Does this coupon have a limited quantity.
	 *
	 * @since 1.33
	 *
	 * @return bool
	 */
	public function is_quantity_limited() {

		$limit_quantity = get_post_meta( $this->get_ID(), '_it-basic-limit-quantity', true );

		return ! empty( $limit_quantity );
	}

	/**
	 * Get the remaining quantity.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_remaining_quantity() {
		return (int) get_post_meta( $this->get_ID(), '_it-basic-quantity', true );
	}

	/**
	 * Get the total coupons allotted.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_allotted_quantity() {

		$quantity = (int) get_post_meta( $this->get_ID(), '_it-basic-allotted-quantity', true );

		if ( ! $quantity && $this->is_quantity_limited() ) {
			$quantity = $this->get_remaining_quantity();
		}

		return $quantity;
	}

	/**
	 * Set the allotted coupon quantity.
	 *
	 * @since 1.33
	 *
	 * @param int $quantity
	 */
	public function set_allotted_quantity( $quantity ) {
		update_post_meta( $this->get_ID(), '_it-basic-allotted-quantity', (int) $quantity );
	}

	/**
	 * Is this coupon product limited.
	 *
	 * @since 1.33
	 *
	 * @return bool
	 */
	public function is_product_limited() {

		$limit_product = get_post_meta( $this->get_ID(), '_it-basic-limit-product', true );

		return ! empty( $limit_product );
	}

	/**
	 * Get the products this coupon is limited to.
	 *
	 * @since 1.33
	 *
	 * @return IT_Exchange_Product[]
	 */
	public function get_limited_products() {
		return $this->products;
	}

	/**
	 * Get the products that are excluded from this coupon.
	 *
	 * @since 1.33
	 *
	 * @return IT_Exchange_Product[]
	 */
	public function get_excluded_products() {

		$products = get_post_meta( $this->get_ID(), '_it-basic-excluded-products', true );

		if ( ! $products ) {
			$products = array();
		}

		return array_filter( array_map( 'it_exchange_get_product', $products ) );
	}

	/**
	 * Get the product categories this coupon is limited to.
	 *
	 * @since 1.33
	 *
	 * @param bool $ids Only return term IDs.
	 *
	 * @return WP_Term[]
	 */
	public function get_product_categories( $ids = false ) {

		if ( ! taxonomy_exists( 'it_exchange_category' ) ) {
			return array();
		}

		$term_ids = get_post_meta( $this->get_ID(), '_it-basic-product-categories', true );

		if ( ! is_array( $term_ids ) ) {
			return array();
		}

		$terms = array();

		foreach ( $term_ids as $term_id ) {
			$term = get_term( $term_id, 'it_exchange_category' );

			if ( $term && ! is_wp_error( $term ) ) {

				if ( $ids ) {
					$terms[] = $term->term_id;
				} else {
					$terms[] = $term;
				}
			}
		}

		return $terms;
	}

	/**
	 * Are items on sale excluded from discounts.
	 *
	 * @since 1.33
	 *
	 * @return bool
	 */
	public function is_sale_item_excluded() {
		return (bool) get_post_meta( $this->get_ID(), '_it-basic-sales-excluded', true );
	}

	/**
	 * Is this coupon limited to a customer.
	 *
	 * @since 1.33
	 *
	 * @return bool
	 */
	public function is_customer_limited() {

		$limit_customer = get_post_meta( $this->get_ID(), '_it-basic-limit-customer', true );

		return ! empty( $limit_customer );
	}

	/**
	 * Get the customer this coupon is limited to.
	 *
	 * @since 1.33
	 *
	 * @return IT_Exchange_Customer
	 */
	public function get_customer() {
		return it_exchange_get_customer( get_post_meta( $this->get_ID(), '_it-basic-customer', true ) );
	}

	/**
	 * Is the coupon limited to a certain frequency of use.
	 *
	 * @since 1.33
	 *
	 * @return bool
	 */
	public function is_frequency_limited() {

		$limit_frequency = get_post_meta( $this->get_ID(), '_it-basic-limit-frequency', true );

		return ! empty( $limit_frequency );
	}

	/**
	 * Get the coupon frequency times.
	 *
	 * This is how many times a customer can use this coupon in the frequency period.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_frequency_times() {
		return (int) get_post_meta( $this->get_ID(), '_it-basic-frequency-times', true );
	}

	/**
	 * Get the total frequency period in seconds.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_frequency_period_in_seconds() {

		switch ( $this->get_frequency_units() ) {
			case 'years' :
				$base = YEAR_IN_SECONDS;
				break;
			case 'months' :
				$base = DAY_IN_SECONDS * date_i18n( 't' ); // Not perfect for < PHP 5.3
				break;
			case 'weeks' :
				$base = WEEK_IN_SECONDS;
				break;
			case 'days' :
			default     :
				$base = DAY_IN_SECONDS;
				break;
		}

		return $this->get_frequency_length() * $base;
	}

	/**
	 * Get frequency length.
	 *
	 * This is the numeric length of the frequency period. For example N weeks.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_frequency_length() {
		return (int) get_post_meta( $this->get_ID(), '_it-basic-frequency-length', true );
	}

	/**
	 * Get frequency units.
	 *
	 * This is how we measure the period of a frequency.
	 *
	 * @since 1.33
	 *
	 * @return string One of 'years', 'months', 'weeks', 'days'.
	 */
	public function get_frequency_units() {
		return get_post_meta( $this->get_ID(), '_it-basic-frequency-units', true );
	}

	/* ------------------------------------------------
					Deprecated Properties
	--------------------------------------------------*/

	/**
	 * The discount amount.
	 *
	 * @deprecated 1.33
	 *
	 * @var float
	 */
	var $amount_number;

	/**
	 * The discount type.
	 *
	 * @deprecated 1.33
	 *
	 * @var string
	 */
	var $amount_type;

	/**
	 * The start date.
	 *
	 * @deprecated 1.33
	 *
	 * @var int
	 */
	var $start_date;

	/**
	 * The end date.
	 *
	 * @deprecated 1.33
	 *
	 * @var int
	 */
	var $end_date;

	/**
	 * Whether to limit this coupon quantity.
	 *
	 * @deprecated 1.33
	 *
	 * @var bool
	 */
	var $limit_quantity;

	/**
	 * Quantity remaining.
	 *
	 * @deprecated 1.33
	 *
	 * @var int
	 */
	var $quantity;

	/**
	 * Whether to limit the coupon to a product.
	 *
	 * @deprecated 1.33
	 *
	 * @var bool
	 */
	var $limit_product;

	/**
	 * Product ID property.
	 *
	 * For BC purposes, if this coupon is limited to multiple products,
	 * only the first product ID will be listed here. Use the
	 * get_limited_products() instead.
	 *
	 * @deprecated 1.33
	 *
	 * @var int
	 */
	var $product_id;

	/**
	 * Whether to limit the coupon to a certain frequency of use.
	 *
	 * @deprecated 1.33
	 *
	 * @var bool
	 */
	var $limit_frequency;

	/**
	 * Frequency times.
	 *
	 * @deprecated 1.33
	 *
	 * @var int
	 */
	var $frequency_times;

	/**
	 * Frequency length.
	 *
	 * @deprecated 1.33
	 *
	 * @var int
	 */
	var $frequency_length;

	/**
	 * Frequency units.
	 *
	 * @deprecated 1.33
	 *
	 * @var string
	 */
	var $frequency_units;

	/**
	 * Whether to limit this coupon to a certain customer.
	 *
	 * @deprecated 1.33
	 *
	 * @var bool
	 */
	var $limit_customer;

	/**
	 * Customer this coupon is limited to.
	 *
	 * @deprecated 1.33
	 *
	 * @var int
	 */
	var $customer;
}
