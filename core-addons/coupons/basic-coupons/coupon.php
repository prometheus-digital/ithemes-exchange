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
	 * Get the coupon start date.
	 *
	 * @since 1.33
	 *
	 * @return DateTime|null
	 */
	public function get_start_date() {

		try {
			return new DateTime( "@{$this->start_date}" );
		}
		catch ( Exception $e ) {
			return null;
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

		try {
			return new DateTime( "@{$this->end_date}" );
		}
		catch ( Exception $e ) {
			return null;
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
		return ! empty( $this->limit_quantity );
	}

	/**
	 * Get the remaining quantity.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_remaining_quantity() {
		return (int) $this->quantity;
	}

	/**
	 * Is this coupon product limited.
	 *
	 * @since 1.33
	 *
	 * @return bool
	 */
	public function is_product_limited() {
		return ! empty( $this->limit_product );
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
	 * Is this coupon limited to a customer.
	 *
	 * @since 1.33
	 *
	 * @return bool
	 */
	public function is_customer_limited() {
		return ! empty( $this->limit_customer );
	}

	/**
	 * Is the coupon limited to a certain frequency of use.
	 *
	 * @since 1.33
	 *
	 * @return bool
	 */
	public function is_frequency_limited() {
		return ! empty( $this->limit_frequency );
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
		return (int) $this->frequency_times;
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
		return (int) $this->frequency_length;
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
		return $this->frequency_units;
	}

	/* ------------------------------------------------
					Deprecated Properties
	--------------------------------------------------*/

	/**
	 * The discount amount.
	 *
	 * @deprecated 1.33
	 *
	 * @var int
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