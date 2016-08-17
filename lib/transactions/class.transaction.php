<?php
/**
 * This file holds the class for an iThemes Exchange Transaction
 *
 * @package IT_Exchange
 * @since   0.3.3
 */

/**
 * Merges a WP Post with iThemes Exchange Transaction data
 *
 * @since 0.3.3
 */
class IT_Exchange_Transaction implements ITE_Contract_Prorate_Credit_Provider {

	/**
	 * @var int
	 */
	private $ID;

	/**
	 * @var array
	 */
	private $refunds;

	/**
	 * @var int
	 */
	private $customer_id;

	/**
	 * @var string
	 */
	private $method_id;

	/**
	 * @param string $transaction_method The transaction method for this transaction
	 *
	 * @since 0.3.3
	 */
	private $transaction_method;

	/**
	 * @var string
	 */
	private $status;

	/**
	 * @var object
	 * @internal
	 */
	private $cart_details;

	/**
	 * Constructor. Loads post data and transaction data
	 *
	 * @since 0.3.3
	 *
	 * @param mixed $post wp post id or post object. optional.
	 *
	 * @throws Exception
	 */
	public function __construct( $post = false ) {

		// If not an object, try to grab the WP object
		if ( ! is_object( $post ) ) {
			$post = get_post( (int) $post );
		}

		// Ensure that $post is a WP_Post object
		if ( is_object( $post ) && ! $post instanceof WP_Post ) {
			$post = false;
		}

		// Ensure this is a transaction post type
		if ( 'it_exchange_tran' != get_post_type( $post ) ) {
			$post = false;
		}

		// Return a WP Error if we don't have the $post object by this point
		if ( ! $post ) {
			throw new Exception( 'The IT_Exchange_Transaction class must have a WP post object or ID passed to its constructor' );
		}

		// Grab the $post object vars and populate this objects vars
		foreach ( (array) get_object_vars( $post ) as $var => $value ) {
			$this->$var = $value;
		}

		// Set the transaction method
		$this->set_transaction_method();
		$this->set_transaction_supports_and_data();
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 *
	 * @throws Exception
	 */
	function IT_Exchange_Transaction() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Get all items in this transaction.
	 *
	 * @since 1.36.0
	 *
	 * @param string $type
	 * @param bool   $flatten
	 *
	 * @return \ITE_Line_Item_Collection|\ITE_Line_Item[]
	 */
	public function get_items( $type = '', $flatten = false ) {
		$repository = new ITE_Line_Item_Transaction_Repository( new ITE_Line_Item_Repository_Events(), $this );

		if ( $flatten ) {
			$items = $this->get_items()->flatten();

			return $type ? $items->with_only( $type ) : $items;
		}

		return $repository->all( $type );
	}

	/**
	 * Get a line item.
	 *
	 * @since 1.36.0
	 *
	 * @param string $type
	 * @param string $id
	 *
	 * @return \ITE_Line_Item|null
	 */
	public function get_item( $type, $id ) {
		$repository = new ITE_Line_Item_Transaction_Repository( new ITE_Line_Item_Repository_Events(), $this );

		return $repository->get( $type, $id );
	}

	/**
	 * Sets the transaction_method property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 0.3.3
	 */
	protected function set_transaction_method() {

		global $pagenow;

		// todo refactor out reliance on pagenow

		if ( ! $transaction_method = get_post_meta( $this->ID, '_it_exchange_transaction_method', true ) ) {
			if ( is_admin() && 'post-new.php' == $pagenow && ! empty( $_GET['transaction-method'] ) ) {
				$transaction_method = $_GET['transaction-method'];
			}
		}

		$this->transaction_method = $transaction_method;
	}

	/**
	 * Sets the transaction_data property from appropriate transaction-method options and assoicated post_meta
	 *
	 * @since 0.3.2
	 *
	 * @return void
	 */
	protected function set_transaction_supports_and_data() {

		// Set status
		$this->status = $this->get_status();

		// Set refunds
		$this->refunds = $this->get_transaction_refunds();

		// Set customer ID
		$this->customer_id = get_post_meta( $this->ID, '_it_exchange_customer_id', true );

		// Set Cart information
		$this->cart_details = get_post_meta( $this->ID, '_it_exchange_cart_object', true );

		// Gateway ID for the transaction
		$this->method_id = get_post_meta( $this->ID, '_it_exchange_transaction_method_id', true );

		do_action( 'it_exchange_set_transaction_supports_and_data', $this->ID );
	}

	/**
	 * Get the transaction ID.
	 *
	 * @since 1.36
	 *
	 * @return int
	 */
	public function get_ID() {
		return $this->ID;
	}

	/**
	 * Get the order number.
	 *
	 * @since 1.34
	 *
	 * @param string $prefix
	 *
	 * @return string
	 */
	public function get_order_number( $prefix = '#' ) {

		// Translate default prefix
		$prefix = ( '#' == $prefix ) ? __( '#', 'it-l10n-ithemes-exchange' ) : $prefix;

		$order_number = sprintf( '%06d', $this->get_ID() );
		$order_number = empty( $prefix ) ? $order_number : $prefix . $order_number;

		return $order_number;
	}

	/**
	 * Gets the transaction_status property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 0.4.0
	 */
	public function get_status() {
		return get_post_meta( $this->ID, '_it_exchange_transaction_status', true );
	}

	/**
	 * Updates the transaction_status property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 0.4.0
	 *
	 * @param string $status
	 */
	public function update_status( $status ) {

		$old_status   = $this->status;
		$old_cleared  = $this->is_cleared_for_delivery();
		$this->status = $status;

		update_post_meta( $this->ID, '_it_exchange_transaction_status', $status );

		/**
		 * Fires when the transaction's status is updated.
		 *
		 * @since 1.0.0
		 *
		 * @param IT_Exchange_Transaction $this
		 * @param string                  $old_status
		 * @param bool                    $old_cleared
		 * @param string                  $old_status
		 */
		do_action( 'it_exchange_update_transaction_status', $this, $old_status, $old_cleared, $status );

		/**
		 * Fires when the transaction's status is updated.
		 *
		 * The dynamic portion of the hook name, `$this->get_method()`, refers to the
		 * method slug used for this transaction.
		 *
		 * @since 1.0.0
		 *
		 * @param IT_Exchange_Transaction $this
		 * @param string                  $old_status
		 * @param bool                    $old_cleared
		 * @param string                  $old_status
		 */
		do_action( "it_exchange_update_transaction_status_{$this->get_method()}", $this, $old_status, $old_cleared, $status );
	}

	/**
	 * Get the method used.
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	public function get_method( $label = false ) {
		return $label ? it_exchange_get_transaction_method_name_from_slug( $this->transaction_method ) : $this->transaction_method;
	}

	/**
	 * Get the method ID.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_method_id() {
		return $this->method_id;
	}

	/**
	 * Update the method ID.
	 *
	 * @since 1.36
	 *
	 * @param $method_id
	 *
	 * @return bool|int
	 */
	public function update_method_id( $method_id ) {

		$previous_method_id = $this->method_id;
		$this->method_id    = $method_id;

		$success = update_post_meta( $this->ID, '_it_exchange_transaction_method_id', $method_id );

		/**
		 * Fires when the transaction method ID is updated.
		 *
		 * @since 1.36
		 *
		 * @param IT_Exchange_Transaction $this
		 * @param string                  $previous_method_id
		 */
		do_action( 'it_exchange_update_transaction_method_id', $this, $previous_method_id );

		return $success;
	}

	/**
	 * Is this transaction cleared for delivery.
	 *
	 * @since 1.36
	 *
	 * @return bool
	 */
	public function is_cleared_for_delivery() {
		return apply_filters( "it_exchange_{$this->get_method()}_transaction_is_cleared_for_delivery", false, $this );
	}

	/**
	 * Get the transaction customer.
	 *
	 * @since 1.36
	 *
	 * @return bool|IT_Exchange_Customer
	 */
	public function get_customer() {
		$customer = it_exchange_get_customer( $this->customer_id );

		return apply_filters( 'it_exchange_get_transaction_customer', $customer, $this );
	}

	/**
	 * Does this transaction have a parent.
	 *
	 * @since 1.36
	 *
	 * @return bool
	 */
	public function has_parent() {
		return ! empty( $this->post_parent );
	}

	/**
	 * Get the parent transaction.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Transaction
	 */
	public function get_parent() {
		return it_exchange_get_transaction( $this->post_parent );
	}

	/**
	 * Add metadata.
	 *
	 * @since 1.35
	 *
	 * @param string $key
	 * @param string $value
	 * @param bool   $unique
	 *
	 * @return false|int
	 */
	public function add_meta( $key, $value, $unique = false ) {
		return add_post_meta( $this->ID, '_it_exchange_transaction_' . $key, $value, $unique );
	}

	/**
	 * Get meta.
	 *
	 * @since 1.35
	 *
	 * @param string $key
	 * @param bool   $single
	 *
	 * @return mixed
	 */
	public function get_meta( $key, $single = true ) {
		return get_post_meta( $this->ID, '_it_exchange_transaction_' . $key, $single );
	}

	/**
	 * Update meta data.
	 *
	 * @since 1.35
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return int|bool Meta ID on new, true on update, false on fail.
	 */
	public function update_meta( $key, $value ) {
		update_post_meta( $this->ID, '_it_exchange_transaction_' . $key, $value );
	}

	/**
	 * Delete meta.
	 *
	 * @since 1.35
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function delete_meta( $key, $value = '' ) {
		return delete_post_meta( $this->ID, '_it_exchange_transaction_' . $key, $value );
	}

	/**
	 * Check if meta exists.
	 *
	 * @since 1.35
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function meta_exists( $key ) {
		return metadata_exists( 'post', $this->ID, '_it_exchange_transaction_' . $key );
	}

	/**
	 * Gets a transaction meta property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 1.3.0
	 */
	function get_transaction_meta( $key, $single = true ) {
		return $this->get_meta( $key, $single );
	}

	/**
	 * Updates a transaction meta property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 1.3.0
	 */
	function update_transaction_meta( $key, $value ) {
		$this->update_meta( $key, $value );
	}

	/**
	 * Deletes a transaction meta property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 1.3.0
	 */
	function delete_transaction_meta( $key, $value = '' ) {
		$this->delete_meta( $key, $value );
	}

	/**
	 * Gets the date property.
	 *
	 * @since 0.4.0
	 *
	 * @param bool $gmt
	 *
	 * @return string
	 */
	public function get_date( $gmt = false ) {
		if ( $gmt ) {
			return $this->post_date_gmt;
		}

		return $this->post_date;
	}

	/**
	 * Returns the transaction total
	 *
	 * @since 0.4.0
	 *
	 * @param bool $subtract_refunds If true, return total less refunds.
	 *
	 * @return string
	 */
	public function get_total( $subtract_refunds = true ) {
		$total = empty( $this->cart_details->total ) ? false : $this->cart_details->total;

		if ( $total && $subtract_refunds && $refunds_total = it_exchange_get_transaction_refunds_total( $this->ID, false ) ) {
			$total = $total - $refunds_total;
		}

		return apply_filters( 'it_exchange_get_transaction_total', $total, $this->ID );
	}

	/**
	 * Returns the transaction subtotal - subtotal of all items.
	 *
	 * @since 0.4.0
	 *
	 * @return string
	 */
	public function get_subtotal() {

		if ( isset( $this->cart_details->sub_total ) ) {
			return $this->cart_details->sub_total;
		}

		$products = $this->get_products();
		$subtotal = 0;
		foreach ( (array) $products as $key => $data ) {
			$subtotal += $data['product_subtotal'];
		}

		return empty( $subtotal ) ? false : $subtotal;
	}

	/**
	 * Get the billing address.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Location|null
	 */
	public function get_billing_address() {

		$address = empty( $this->cart_details->billing_address ) ? array() : $this->cart_details->billing_address;

		$address = apply_filters( 'it_exchange_get_transaction_billing_address', $address, $this );

		if ( empty( $address ) ) {
			return null;
		}

		return new ITE_In_Memory_Address( $address );
	}

	/**
	 * Get the shipping address.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Location|null
	 */
	public function get_shipping_address() {

		$address = empty( $this->cart_details->shipping_address ) ? array() : $this->cart_details->shipping_address;

		$address = apply_filters( 'it_exchange_get_transaction_shipping_address', $address, $this );

		if ( empty( $address ) ) {
			return null;
		}

		return new ITE_In_Memory_Address( $address );
	}

	/**
	 * Returns the transaction currency
	 *
	 * @since 0.4.0
	 *
	 * @return string
	 */
	public function get_currency() {
		$settings         = it_exchange_get_option( 'settings_general' );
		$default_currency = $settings['default-currency'];

		return empty( $this->cart_details->currency ) ? $default_currency : $this->cart_details->currency;
	}

	/**
	 * Returns the description
	 *
	 * @since 0.4.0
	 *
	 * @return string
	 */
	public function get_description() {
		if ( ! empty( $this->cart_details->description ) && trim( $this->cart_details->description ) !== '' ) {
			return $this->cart_details->description;
		} else if ( $p = get_post_meta( $this->ID, '_it_exchange_parent_tx_id', true ) ) {

			$parent = it_exchange_get_transaction( $p );

			$description = it_exchange_get_transaction_description( $parent );
			$description .= ' ' . __( '(Renewal)', 'it-l10n-ithemes-exchange' );

			return $description;
		} else {
			return '';
		}
	}

	/**
	 * Returns the coupons applied to this transaction if they exist
	 *
	 * @since 0.4.0
	 *
	 * @return string
	 */
	public function get_coupons() {
		return empty( $this->cart_details->coupons ) ? false : $this->cart_details->coupons;
	}

	/**
	 * Returns the total discount applied by the coupons
	 *
	 * @since 0.4.0
	 *
	 * @return string
	 */
	public function get_coupons_total_discount() {
		return empty( $this->cart_details->coupons_total_discount ) ? false : $this->cart_details->coupons_total_discount;
	}

	/**
	 * Returns the products array
	 *
	 * @since 0.4.0
	 *
	 * @return array
	 */
	public function get_products() {
		$products = empty( $this->cart_details->products ) ? array() : $this->cart_details->products;

		return apply_filters( 'it_exchange_get_transaction_products', $products, $this );
	}

	/**
	 * Add the transaction refund amount.
	 *
	 * @since 0.4.0
	 *
	 * @param string $refund  Amount
	 * @param string $date    Date refund occurred. In mysql format.
	 * @param array  $options Additional refund options.
	 */
	public function add_refund( $refund, $date = '', $options = array() ) {
		$date = empty( $date ) ? date_i18n( 'Y-m-d H:i:s' ) : $date;
		$args = array(
			'amount'  => $refund,
			'date'    => $date,
			'options' => $options,
		);
		add_post_meta( $this->ID, '_it_exchange_transaction_refunds', $args );
	}

	/**
	 * checks if the transaction has refunds.
	 *
	 * @since 1.3.0
	 * @return bool
	 */
	public function has_refunds() {
		return (bool) get_post_meta( $this->ID, '_it_exchange_transaction_refunds' );
	}

	/**
	 * Get the transaction refunds.
	 *
	 * @since 0.4.0
	 */
	public function get_transaction_refunds() {
		return get_post_meta( $this->ID, '_it_exchange_transaction_refunds' );
	}

	/**
	 * Get the customer's IP address for this transaction.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_customer_ip() {
		return get_post_meta( $this->ID, '_it_exchange_customer_ip', true );
	}

	/**
	 * checks if the transaction has children.
	 *
	 * @since 1.3.0
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	public function has_children( $args = array() ) {
		$defaults = array(
			'post_parent' => $this->ID,
			'post_type'   => 'it_exchange_tran',
			'numberposts' => 1
		);

		$args = wp_parse_args( $args, $defaults );

		return (bool) get_children( $args );
	}

	/**
	 * Gets the transactions children.
	 *
	 * @since 1.3.0
	 *
	 * @param array $args                Arguments to filter children.
	 * @param bool  $return_transactions Return transaction objects.
	 *
	 * @return WP_Post[]
	 */
	public function get_children( $args = array(), $return_transactions = false ) {

		$defaults = array(
			'post_parent' => $this->ID,
			'post_type'   => 'it_exchange_tran',
		);

		$args = wp_parse_args( $args, $defaults );

		$posts = get_children( $args );

		if ( $return_transactions ) {
			$posts = array_map( 'it_exchange_get_transaction', $posts );
		}

		return $posts;
	}

	/**
	 * @inheritdoc
	 */
	public static function handle_prorate_credit_request( ITE_Prorate_Credit_Request $request, ITE_Daily_Price_Calculator $calculator ) {

		if ( ! self::accepts_prorate_credit_request( $request ) ) {
			throw new DomainException( "This credit request can't be handled by this provider." );
		}

		/** @var IT_Exchange_Transaction $transaction */
		$transaction = $request->get_transaction();

		$for = $request->get_product_providing_credit();

		foreach ( $transaction->get_products() as $product ) {
			if ( $product['product_id'] == $for->ID ) {
				$amount = (float) $product['product_subtotal'];
			}
		}

		if ( ! isset( $amount ) ) {
			throw new InvalidArgumentException( "Product with ID '$for->ID' not found in transaction '$transaction->ID'." );
		}

		if ( (float) $transaction->get_total( false ) < $amount ) {
			$amount = (float) $transaction->get_total( false );
		}

		$request->set_credit( $amount );

		$request->update_additional_session_details( array(
			'old_transaction_id'     => $transaction->ID,
			'old_transaction_method' => $transaction->transaction_method
		) );
	}

	/**
	 * is triggered when invoking inaccessible methods in an object context.
	 *
	 * @since 1.36
	 *
	 * @param $name      string
	 * @param $arguments array
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	public function __call( $name, $arguments ) {

		switch ( $name ) {
			case 'set_transaction_supports_and_data':
				$this->set_transaction_supports_and_data();
				break;
			case 'set_transaction_method':
				$this->set_transaction_method();
				break;
			case 'get_gateway_id_for_transaction':
				return $this->get_method_id();
		}


		throw new Exception( "Method not found: $name" );
	}

	/**
	 * Provide backwards compatibility for deprecated properties.
	 *
	 * @since 1.36
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {

		if ( $name === 'gateway_id_for_transaction' ) {
			return $this->get_method_id();
		}

		if ( in_array( $name, array( 'transaction_supports', 'transaction_data' ) ) ) {
			return array();
		}

		if ( in_array( $name, array(
			'ID',
			'refunds',
			'customer_id',
			'transaction_method',
			'status',
			'cart_details',
		) ) ) {
			return $this->$name;
		}

		return null;
	}

	/**
	 * Back-compat.
	 *
	 * @since 1.36
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {

		$val = $this->__get( $name );

		return ! empty( $val );
	}

	/**
	 * Sets the supports array for the post_type.
	 *
	 * @since 0.3.3
	 *
	 * @deprecated
	 */
	public function set_add_edit_screen_supports() {
		_deprecated_function( __METHOD__, '1.36' );
	}

	/* Deprecated Properties */

	/** @deprecated */
	public $post_author;
	public $post_date;
	public $post_date_gmt;
	public $post_content;
	public $post_title;
	public $post_excerpt;
	public $post_status;
	public $comment_status;
	public $ping_status;
	public $post_password;
	public $post_name;
	public $to_ping;
	public $pinged;
	public $post_modified;
	public $post_modified_gmt;
	public $post_content_filtered;
	public $post_parent;
	public $guid;
	public $menu_order;
	public $post_type;
	public $post_mime_type;
	public $comment_count;
}
