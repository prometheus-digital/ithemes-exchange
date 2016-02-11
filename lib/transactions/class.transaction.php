<?php
/**
 * This file holds the class for an iThemes Exchange Transaction
 *
 * @package IT_Exchange
 * @since 0.3.3
*/

/**
 * Merges a WP Post with iThemes Exchange Transaction data
 *
 * @since 0.3.3
*/
class IT_Exchange_Transaction {

	// WP Post Type Properties
	var $ID;
	var $post_author;
	var $post_date;
	var $post_date_gmt;
	var $post_content;
	var $post_title;
	var $post_excerpt;
	var $post_status;
	var $comment_status;
	var $ping_status;
	var $post_password;
	var $post_name;
	var $to_ping;
	var $pinged;
	var $post_modified;
	var $post_modified_gmt;
	var $post_content_filtered;
	var $post_parent;
	var $guid;
	var $menu_order;
	var $post_type;
	var $post_mime_type;
	var $comment_count;

	/**
	 * @param string $transaction_method The transaction method for this transaction
	 * @since 0.3.3
	*/
	var $transaction_method;


	/**
	 * @param array $transaction_supports what features does this transaction support
	 * @since 0.3.3
	*/
	var $transaction_supports;

	/**
	 * @param array $transaction_data  any custom data registered by the transaction-method for this transaction
	 * @since 0.3.3
	*/
	var $transaction_data = array();

	/**
	 * @var string
	 */
	var $status;

	/**
	 * @var object
	 * @internal
	 */
	var $cart_details;

	/**
	 * Constructor. Loads post data and transaction data
	 *
	 * @since 0.3.3
	 * @param mixed $post  wp post id or post object. optional.
	 *
	 * @throws Exception
	*/
	function __construct( $post=false ) {

		// If not an object, try to grab the WP object
		if ( ! is_object( $post ) )
			$post = get_post( (int) $post );

		// Ensure that $post is a WP_Post object
		if ( is_object( $post ) && ! $post instanceof WP_Post )
			$post = false;

		// Ensure this is a transaction post type
		if ( 'it_exchange_tran' != get_post_type( $post ) )
			$post = false;

		// Return a WP Error if we don't have the $post object by this point
		if ( ! $post )
			throw new Exception( __( 'The IT_Exchange_Transaction class must have a WP post object or ID passed to its constructor', 'it-l10n-ithemes-exchange' ) );

		// Grab the $post object vars and populate this objects vars
		foreach( (array) get_object_vars( $post ) as $var => $value ) {
			$this->$var = $value;
		}

		// Set the transaction method
		$this->set_transaction_method();

		// Set the transaction data
		if ( did_action( 'init' ) )
			$this->set_transaction_supports_and_data();
		else
			add_action( 'init', array( $this, 'set_transaction_supports_and_data' ) );


		// Set supports for new and edit screens
		if ( did_action( 'admin_init' ) )
			$this->set_add_edit_screen_supports();
		else
			add_action( 'admin_init', array( $this, 'set_add_edit_screen_supports' ) );

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
	 * Sets the transaction_method property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 0.3.3
	*/
	function set_transaction_method() {
		global $pagenow;
		if ( ! $transaction_method = get_post_meta( $this->ID, '_it_exchange_transaction_method', true ) ) {
			if ( is_admin() && 'post-new.php' == $pagenow && ! empty( $_GET['transaction-method'] ) )
				$transaction_method = $_GET['transaction-method'];
		}
		$this->transaction_method = $transaction_method;
	}

	/**
	 * Gets the transaction_status property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 0.4.0
	*/
	function get_status() {
		return get_post_meta( $this->ID, '_it_exchange_transaction_status', true );
	}

	/**
	 * Updates the transaction_status property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 0.4.0
	*/
	function update_status( $new_status ) {
		update_post_meta( $this->ID, '_it_exchange_transaction_status', $new_status );
		$this->status = $new_status;
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
	public function update_meta($key, $value) {
		update_post_meta( $this->ID, '_it_exchange_transaction_' . $key, $value );
	}

	/**
	 * Delete meta.
	 *
	 * @since 1.35
	 *
	 * @param string $key
	 * @param mixed $value
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
	 * Get the order number.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_order_number() {
		return it_exchange_get_transaction_order_number( $this );
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
	function get_date( $gmt=false ) {
		if ( $gmt )
			return $this->post_date_gmt;

		return $this->post_date;
	}

	/**
	 * Returns the transaction subtotal - subtotal of all items.
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function get_subtotal() {

		if ( isset( $this->cart_details->sub_total ) ) {
			return $this->cart_details->sub_total;
		}

		$products = $this->get_products();
		$subtotal = 0;
		foreach( (array) $products as $key => $data ) {
			$subtotal += $data['product_subtotal'];
		}
		return empty( $subtotal ) ? false : $subtotal;
	}

	/**
	 * Returns the transaction total
	 *
	 * @since 0.4.0
	 *
	 * @param boolean $without_refunds if true, the original total before refunds will be given
	 * @return string
	*/
	function get_total( $subtract_refunds=true ) {
		$total = empty( $this->cart_details->total ) ? false : $this->cart_details->total;

		if ( $total && $subtract_refunds && $refunds_total = it_exchange_get_transaction_refunds_total( $this->ID, false ) )
			$total = $total - $refunds_total;

		return apply_filters( 'it_exchange_get_transaction_total', $total, $this->ID );
	}

	/**
	 * Returns the transaction currency
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function get_currency() {
		$settings = it_exchange_get_option( 'settings_general' );
		$default_currency = $settings['default-currency'];
		return empty( $this->cart_details->currency ) ? $default_currency : $this->cart_details->currency;
	}

	/**
	 * Returns the description
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function get_description() {
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
	function get_coupons() {
		return empty( $this->cart_details->coupons ) ? false : $this->cart_details->coupons;
	}

	/**
	 * Returns the total discount applied by the coupons
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function get_coupons_total_discount() {
		return empty( $this->cart_details->coupons_total_discount ) ? false : $this->cart_details->coupons_total_discount;
	}

	/**
	 * Returns the products array
	 *
	 * @since 0.4.0
	 *
	 * @return array
	*/
	function get_products() {
		$products = empty( $this->cart_details->products ) ? array() : $this->cart_details->products;
		return apply_filters( 'it_exchange_get_transaction_products', $products, $this );
	}

	/**
	 * Add the transaction refund amount.
	 *
	 * @since 0.4.0
	 *
	 * @param string $refund Amount
	 * @param string $date Date refund occurred. In mysql format.
	 * @param array  $options Additional refund options.
	*/
	function add_refund( $refund, $date = '', $options = array() ) {
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
	function has_refunds() {
		return (bool) get_post_meta( $this->ID, '_it_exchange_transaction_refunds' );
	}

	/**
	 * Get the transaction refunds.
	 *
	 * @since 0.4.0
	*/
	function get_transaction_refunds() {
		return get_post_meta( $this->ID, '_it_exchange_transaction_refunds' );
	}

	/**
	 * Sets the transaction_data property from appropriate transaction-method options and assoicated post_meta
	 *
	 * @ since 0.3.2
	 * @return void
	*/
	function set_transaction_supports_and_data() {

		// Set status
		$this->status = $this->get_status();

		// Set refunds
		$this->refunds = $this->get_transaction_refunds();

		// Set customer ID
		$this->customer_id = get_post_meta( $this->ID, '_it_exchange_customer_id', true );

		// Set Cart information
		$this->cart_details = get_post_meta( $this->ID, '_it_exchange_cart_object', true );

		// Gateway ID for the transaction
		$this->gateway_id_for_transaction = get_post_meta( $this->ID, '_it_exchange_transaction_method_id', true );

		do_action( 'it_exchange_set_transaction_supports_and_data', $this->ID );
	}

	/**
	 * checks if the transaction has children.
	 *
	 * @since 1.3.0
	 * @return bool
	*/
	function has_children( $args=array() ) {
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
	 * @return WP_Post[]
	*/
	function get_children( $args=array() ) {
		$defaults = array(
			'post_parent' => $this->ID,
			'post_type'   => 'it_exchange_tran',
		);
		$args = wp_parse_args( $args, $defaults );

		return get_children( $args );
	}

	/**
	 * Returns the transaction ID used by the gateway
	 *
	 * @since 0.4.0
	 * @return mixed
	*/
	function get_gateway_id_for_transaction() {
		return empty( $this->gateway_id_for_transaction ) ? false : $this->gateway_id_for_transaction;
	}

	/**
	 * Sets the supports array for the post_type.
	 *
	 * @since 0.3.3
	*/
	function set_add_edit_screen_supports() {
		global $pagenow;
		$supports = array(
			'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields',
			'comments', 'revisions', 'post-formats',
		);

		// If is_admin and is post-new.php or post.php, only register supports for current transaction-method
		if ( 'post-new.php' != $pagenow && 'post.php' != $pagenow )
			return; // Don't remove any if not on post-new / or post.php

		if ( $addon = it_exchange_get_addon( $this->transaction_method ) ) {
			// Remove any supports args that the transaction add-on does not want.
			foreach( $supports as $option ) {
				if ( empty( $addon['options']['supports'][$option] ) )
					remove_post_type_support( 'it_exchange_tran', $option );
			}
		} else {
			// Can't find the transaction - remove everything
			foreach( $supports as $option ) {
				remove_post_type_support( 'it_exchange_tran', $option );
			}
		}
	}
}
