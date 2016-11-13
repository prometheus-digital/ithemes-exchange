<?php
/**
 * This file holds the class for an iThemes Exchange Transaction
 *
 * @package IT_Exchange
 * @since   0.3.3
 */
use IronBound\DB\Collection;
use IronBound\DB\Model;
use IronBound\DB\Relations\HasForeign;
use IronBound\DB\Relations\HasForeignPost;
use IronBound\DB\Relations\HasMany;

/**
 * Merges a WP Post with iThemes Exchange Transaction data
 *
 * @since 0.3.3
 *
 * @property int                                       $ID
 * @property-read int                                  $customer_id
 * @property-read string                               $customer_email
 * @property string                                    $status
 * @property-read string                               $method
 * @property string                                    $method_id
 * @property-read string                               $hash
 * @property-read string                               $cart_id
 * @property-read float                                $total
 * @property-read float                                $subtotal
 * @property-read \DateTime                            $order_date
 * @property \ITE_Payment_Token|null                   $payment_token
 * @property string                                    $purchase_mode
 * @property-read \IT_Exchange_Transaction             $parent
 * @property-read stdClass                             $cart_object // Internal
 * @property-read Collection|IT_Exchange_Transaction[] $children
 * @property-read Collection|ITE_Refund[]              $refunds
 */
class IT_Exchange_Transaction extends Model implements ITE_Contract_Prorate_Credit_Provider {

	const P_MODE_LIVE = 'live';
	const P_MODE_SANDBOX = 'sandbox';

	/**
	 * List of relations to be eager loaded.
	 *
	 * @var array
	 */
	protected static $_eager_load = array( 'ID' );

	/**
	 * @var WP_Post
	 */
	private $post;

	/**
	 * Constructor. Loads post data and transaction data
	 *
	 * @since 0.3.3
	 *
	 * @param array|stdClass|WP_Post|int $post_or_data
	 *
	 * @throws Exception
	 */
	public function __construct( $post_or_data = 0 ) {

		if ( func_num_args() === 0 ) {
			parent::__construct();

			return;
		}

		if ( $post_or_data === false || is_numeric( $post_or_data ) ) {
			$post_or_data = get_post( (int) $post_or_data );

			$this->assert_post( $post_or_data );
		}

		if ( $this->is_post_like( $post_or_data ) ) {

			if ( $post_or_data->post_type !== 'it_exchange_tran' ) {
				throw new Exception( "Unable to construct IT_Exchange_Transaction #{$post_or_data->ID}. Incorrect post type." );
			}

			$this->post = $post_or_data;
			$data       = self::get_data_from_pk( $post_or_data->ID );

			if ( ! $data ) {
				$upgraded = static::upgrade( $post_or_data );

				if ( $upgraded ) {
					$data = $upgraded->get_raw_attributes();
				} else {
					throw new Exception( "Unable to construct IT_Exchange_Transaction #{$post_or_data->ID}" );
				}
			}

			$this->_exists = true;

			parent::__construct( $data );
		} else {
			parent::__construct( $post_or_data );
		}

		if ( $this->exists() ) {
			$this->set_transaction_supports_and_data();
		}
	}

	/**
	 * Assert the post is valid.
	 *
	 * @since 1.36.0
	 *
	 * @param mixed $post
	 *
	 * @throws \Exception
	 */
	private function assert_post( $post ) {
		if ( ! $this->is_post_like( $post ) ) {
			throw new Exception( 'The IT_Exchange_Transaction class must have a WP post object or ID passed to its constructor' );
		}
	}

	/**
	 * Check if a value is post like.
	 *
	 * @since 1.36.0
	 *
	 * @param mixed $post
	 *
	 * @return bool
	 */
	private function is_post_like( $post ) {
		return $post instanceof WP_Post || ( $post instanceof stdClass && isset( $post->post_type ) );
	}

	/**
	 * @inheritDoc
	 */
	protected static function _do_create( array $attributes = array() ) {

		/** @var IT_Exchange_Transaction $txn */
		$txn = parent::_do_create( $attributes );

		update_post_meta( $txn->ID, '_it_exchange_transaction_method', $txn->get_method() );
		update_post_meta( $txn->ID, '_it_exchange_transaction_method_id', $txn->get_method_id() );
		update_post_meta( $txn->ID, '_it_exchange_transaction_status', $txn->get_status() );
		update_post_meta( $txn->ID, '_it_exchange_customer_id', $txn->customer_id );
		update_post_meta( $txn->ID, '_it_exchange_cart_id', $txn->cart_id );
		update_post_meta( $txn->ID, '_it_exchange_transaction_hash', $txn->hash );

		if ( $txn->has_parent() ) {
			update_post_meta( $txn->ID, '_it_exchange_parent_tx_id', $txn->parent->ID );
		}

		return $txn;
	}

	/**
	 * Upgrade a transaction to be saved in the database table as well.
	 *
	 * @since 1.36.0
	 *
	 * @param WP_Post $post
	 *
	 * @return $this
	 */
	public static function upgrade( $post ) {

		$post_id      = $post->ID;
		$cart_details = get_post_meta( $post_id, '_it_exchange_cart_object', true );

		$customer_id    = get_post_meta( $post_id, '_it_exchange_customer_id', true );
		$customer_email = '';

		if ( is_numeric( $customer_id ) ) {
			if ( $user = get_user_by( 'id', $customer_id ) ) {
				$customer_email = $user->user_email;
			}
		} else {
			$customer_email = $customer_id;
			$customer_id    = 0;
		}

		$billing = $shipping = 0;

		if ( ! empty( $cart_details->billing_address ) && is_array( $cart_details->billing_address ) ) {
			if ( $customer_id ) {
				try {
					$billing = ITE_Saved_Address::query()->first_or_create( array_merge(
						array_intersect_key( $cart_details->billing_address, ITE_Saved_Address::table()->get_column_defaults() ),
						array( 'customer' => $customer_id, 'type' => ITE_Saved_Address::T_BILLING )
					) );
				}
				catch ( Exception $e ) {

				}
			} else {
				$billing = ITE_Saved_Address::create( array_merge(
					$cart_details->billing_address,
					array( 'type' => ITE_Saved_Address::T_BILLING )
				) );
			}

			$billing = $billing ? $billing->get_pk() : 0;
		}

		if ( ! empty( $cart_details->shipping_address ) && is_array( $cart_details->shipping_address ) ) {
			if ( $customer_id ) {
				try {
					$shipping = ITE_Saved_Address::query()->first_or_create( array_merge(
						array_intersect_key( $cart_details->shipping_address, ITE_Saved_Address::table()->get_column_defaults() ),
						array( 'customer' => $customer_id, 'type' => ITE_Saved_Address::T_SHIPPING )
					) );
				}
				catch ( Exception $e ) {

				}
			} else {
				$shipping = ITE_Saved_Address::create( array_merge(
					$cart_details->shipping_address,
					array( 'type' => ITE_Saved_Address::T_SHIPPING )
				) );
			}

			$shipping = $shipping ? $shipping->get_pk() : 0;
		}

		$method_id = get_post_meta( $post_id, '_it_exchange_transaction_method_id', true );

		$data = array(
			'ID'             => $post_id,
			'customer_id'    => $customer_id,
			'customer_email' => $customer_email,
			'status'         => get_post_meta( $post_id, '_it_exchange_transaction_status', true ),
			'method'         => get_post_meta( $post_id, '_it_exchange_transaction_method', true ),
			'method_id'      => $method_id ? $method_id : uniqid( 'RAND', true ),
			'hash'           => get_post_meta( $post_id, '_it_exchange_transaction_hash', true ),
			'cart_id'        => get_post_meta( $post_id, '_it_exchange_cart_id', true ),
			'total'          => isset( $cart_details->total ) ? $cart_details->total : 0,
			'subtotal'       => isset( $cart_details->sub_total ) ? $cart_details->sub_total : 0,
			'order_date'     => $post->post_date_gmt,
			'cleared'        => false,
			'billing'        => $billing,
			'shipping'       => $shipping
		);

		if ( $p = get_post_meta( $post_id, '_it_exchange_parent_tx_id', true ) ) {
			$p = it_exchange_get_transaction( $p );

			if ( $p ) {
				$data['parent'] = $p->ID;
			}
		}

		$transaction = static::create( $data );

		if ( $transaction && $transaction->is_cleared_for_delivery() ) {
			$transaction->set_attribute( 'cleared', true );
			$transaction->save();
		}

		if ( $customer_email && ! empty( $cart_details->is_guest_checkout ) ) {
			$transaction->cart()->set_meta( 'guest-email', $customer_email );
		}

		return $transaction;
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
	 * Sets the transaction_data property from appropriate transaction-method options and assoicated post_meta
	 *
	 * @since      0.3.2
	 *
	 * @deprecated 1.36.0
	 *
	 * @return void
	 */
	protected function set_transaction_supports_and_data() {

		do_action_deprecated( 'it_exchange_set_transaction_supports_and_data', array( $this->ID ), '1.36.0' );
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
		$prefix = ( '#' === $prefix ) ? __( '#', 'it-l10n-ithemes-exchange' ) : $prefix;

		$order_number = sprintf( '%06d', $this->get_ID() );
		$order_number = empty( $prefix ) ? $order_number : $prefix . $order_number;

		return apply_filters( 'it_exchange_get_transaction_order_number', $order_number, $this, $prefix );
	}

	/**
	 * Gets the transaction's payment status.
	 *
	 * There isn't a set list of transaction statuses available. Each payment gateway dynamically declares their own.
	 *
	 * @since 0.4.0
	 * @since 1.36.0 Added $label parameter.
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	public function get_status( $label = false ) {

		if ( $label ) {
			return apply_filters( 'it_exchange_transaction_status_label_' . $this->get_method(), $this->status, array(
				'status' => $this->status
			) );
		}

		return apply_filters( 'it_exchange_get_transaction_status', $this->status, $this );
	}

	/**
	 * Updates the transaction_status property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 0.4.0
	 * @since 1.36.0 Add return value.
	 *
	 * @param string $status
	 *
	 * @return bool
	 */
	public function update_status( $status ) {

		if ( $this->get_status() === $status ) {
			return true;
		}

		$old_status  = $this->get_status();
		$old_cleared = $this->is_cleared_for_delivery();

		$this->status = $status;

		if ( ! $this->save() ) {
			return false;
		}

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

		$this->set_attribute( 'cleared', $this->is_cleared_for_delivery() );
		$this->save();

		return true;
	}

	/**
	 * Get the method used.
	 *
	 * @since 1.36.0
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	public function get_method( $label = false ) {

		$method = apply_filters( 'it_exchange_get_transaction_method', $this->method, $this );

		if ( $label ) {
			$label = it_exchange_get_transaction_method_name_from_slug( $method );

			return apply_filters( 'it_exchange_get_transaction_method_name', $label, $this );
		} else {
			return $method;
		}
	}

	/**
	 * Get the method ID.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_method_id() {
		return apply_filters( 'it_exchange_get_transaction_method_id', $this->method_id, $this );
	}

	/**
	 * Update the method ID.
	 *
	 * @since 1.36
	 *
	 * @param string $method_id
	 *
	 * @return bool|int
	 *
	 * @throws InvalidArgumentException
	 */
	public function update_method_id( $method_id ) {

		if ( ! is_string( $method_id ) || trim( $method_id ) === '' ) {
			throw new InvalidArgumentException( '$method_id must be non-zero length string.' );
		}

		$previous_method_id = $this->get_method_id();

		$this->method_id = $method_id;

		$success = $this->save();

		if ( $success ) {

			/**
			 * Fires when the transaction method ID is updated.
			 *
			 * @since 1.36
			 *
			 * @param IT_Exchange_Transaction $this
			 * @param string                  $previous_method_id
			 */
			do_action( 'it_exchange_update_transaction_method_id', $this, $previous_method_id );
		}

		return $success;
	}

	/**
	 * Get the gateway used to pay for this transaction.
	 *
	 * @since 1.36.0
	 *
	 * @return ITE_Gateway|null
	 */
	public function get_gateway() {
		return ITE_Gateways::get( $this->get_method() );
	}

	/**
	 * Is this transaction cleared for delivery.
	 *
	 * This should always be used over the `cleared` property. The `cleared` property is a cached value for assistance
	 * in querying.
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
	 * @return IT_Exchange_Customer|null
	 */
	public function get_customer() {

		$customer_id = $this->customer_id;

		if ( $this->is_guest_purchase() ) {
			$customer = IT_Exchange_Guest_Customer::from_transaction( $this );
		} else {
			$customer = it_exchange_get_customer( $customer_id );
			$customer = $customer instanceof IT_Exchange_Customer ? $customer : null;
		}

		$customer = apply_filters( 'it_exchange_get_transaction_customer', $customer, $this );

		return $customer instanceof IT_Exchange_Customer ? $customer : null;
	}

	/**
	 * Get the customer's email address.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_customer_email() {
		return apply_filters( 'it_exchange_get_transaction_customer_email', $this->customer_email, $this );
	}

	/**
	 * Check if this transaction is a guest purchase.
	 *
	 * @since 1.36.0
	 *
	 * @return bool
	 */
	public function is_guest_purchase() {
		return (bool) get_post_meta( $this->ID, '_it-exchange-is-guest-checkout', true );
	}

	/**
	 * Does this transaction have a parent.
	 *
	 * @since 1.36
	 *
	 * @return bool
	 */
	public function has_parent() {
		return (bool) $this->get_raw_attribute( 'parent' );
	}

	/**
	 * Get the parent transaction.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Transaction
	 */
	public function get_parent() {
		return $this->parent;
	}

	/**
	 * Was this transaction purchased in live mode.
	 *
	 * @since 1.36.0
	 *
	 * @return bool
	 */
	public function is_live_purchase() { return $this->purchase_mode === self::P_MODE_LIVE; }

	/**
	 * Was this transaction purchased in sandbox mode.
	 *
	 * @since 1.36.0
	 *
	 * @return bool
	 */
	public function is_sandbox_purchase() { return $this->purchase_mode === self::P_MODE_SANDBOX; }

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
		return update_post_meta( $this->ID, '_it_exchange_transaction_' . $key, $value );
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
			return $this->order_date->format( 'Y-m-d H:i:s' );
		}

		return get_date_from_gmt( $this->order_date->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Returns the transaction total
	 *
	 * @since 0.4.0
	 *
	 * @param bool $subtract_refunds If true, return total less refunds.
	 *
	 * @return float
	 */
	public function get_total( $subtract_refunds = true ) {

		$total = $this->total;

		if ( $total && $subtract_refunds && $refunds_total = $this->get_refund_total() ) {
			$total -= $refunds_total;
		}

		return apply_filters( 'it_exchange_get_transaction_total', $total, $this->ID, false, $subtract_refunds );
	}

	/**
	 * Returns the transaction subtotal - subtotal of all items.
	 *
	 * @since 0.4.0
	 *
	 * @return float
	 */
	public function get_subtotal() {
		return apply_filters( 'it_exchange_get_transaction_subtotal', $this->subtotal, $this, false );
	}

	/**
	 * Get the billing address.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Location|null
	 */
	public function get_billing_address() {

		/** @var ITE_Saved_Address|null $address */
		$address = $this->billing;

		$raw = $address ? $address->to_array() : array();

		$filtered = apply_filters_deprecated(
			'it_exchange_get_transaction_billing_address', array( $raw, $this ), '1.36'
		);

		if ( ! $filtered && ! $address ) {
			return null;
		}

		if ( $filtered && $raw !== $filtered ) {
			foreach ( $filtered as $field => $value ) {
				$address[ $field ] = $value;
			}
		}

		return $address;
	}

	/**
	 * Get the shipping address.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Location|null
	 */
	public function get_shipping_address() {

		/** @var ITE_Saved_Address|null $address */
		$address = $this->shipping;

		$raw = $address ? $address->to_array() : array();

		$filtered = apply_filters_deprecated(
			'it_exchange_get_transaction_shipping_address', array( $raw, $this ), '1.36'
		);

		if ( ! $filtered && ! $address ) {
			return null;
		}

		if ( $filtered && $raw !== $filtered ) {
			foreach ( $filtered as $field => $value ) {
				$address[ $field ] = $value;
			}
		}

		return $address;
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

		$currency = empty( $this->cart_details->currency ) ? $default_currency : $this->cart_details->currency;

		return apply_filters( 'it_exchange_get_transaction_currency', $currency, $this );
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
			$description = $this->cart_details->description;
		} else if ( $this->has_parent() ) {

			$parent = $this->get_parent();

			$description = it_exchange_get_transaction_description( $parent );
			$description .= ' ' . __( '(Renewal)', 'it-l10n-ithemes-exchange' );
		} else {
			$description = '';
		}

		return apply_filters( 'it_exchange_get_transaction_description', $description, $this );
	}

	/**
	 * Returns the coupons applied to this transaction if they exist
	 *
	 * @since 0.4.0
	 *
	 * @return array
	 */
	public function get_coupons() {

		$coupons = empty( $this->cart_details->coupons ) ? false : $this->cart_details->coupons;

		return apply_filters( 'it_exchange_get_transaction_coupons', $coupons, $this );
	}

	/**
	 * Returns the total discount applied by the coupons
	 *
	 * @since 0.4.0
	 *
	 * @return float
	 */
	public function get_coupons_total_discount() {
		$discount = empty( $this->cart_details->coupons_total_discount ) ? 0 : $this->cart_details->coupons_total_discount;

		return apply_filters( 'it_exchange_get_transaction_coupons_total_discount', $discount, $this, false );
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
	 * @param string $amount  Amount
	 * @param string $date    Date refund occurred. In mysql format.
	 * @param array  $options Additional refund options.
	 *
	 * @return bool
	 */
	public function add_refund( $amount, $date = '', $options = array() ) {

		$date = $date ?: current_time( 'mysql', true );

		if ( is_numeric( $date ) ) {
			$datetime = new DateTime( "@$date", new DateTimeZone( 'UTC' ) );
		} elseif ( ! $date instanceof DateTime ) {
			$datetime = new DateTime( $date, new DateTimeZone( 'UTC' ) );
		} else {
			$datetime = $date;
		}

		$refund = ITE_Refund::create( array(
			'transaction' => $this,
			'amount'      => $amount,
			'created_at'  => $datetime,
			'reason'      => empty( $options['reason'] ) ? '' : $options['reason'],
		) );

		foreach ( $options as $option => $value ) {
			if ( $option !== 'reason' ) {
				$refund->update_meta( $option, $value );
			}
		}

		if ( ! $refund ) {
			return false;
		}

		do_action_deprecated(
			'it_exchange_add_refund_to_transaction',
			array( $this, $amount, $date, $options ),
			'1.36.0',
			'it_exchange_add_transaction_refund'
		);

		return true;
	}

	/**
	 * checks if the transaction has refunds.
	 *
	 * @since 1.3.0
	 *
	 * @return bool
	 */
	public function has_refunds() {

		$has_refunds = (bool) $this->refunds->count();

		/**
		 * Filter whether this transaction has any refunds.
		 *
		 * @since 1.3.0
		 *
		 * @param bool                     $has_refunds
		 * @param \IT_Exchange_Transaction $this
		 */
		return apply_filters( 'it_exchange_has_transaction_refunds', $has_refunds, $this );
	}

	/**
	 * Returns the a sum of all the applied refund amounts for this transaction
	 *
	 * @since 1.36.0
	 *
	 * @return float
	 */
	public function get_refund_total() {
		$total = ITE_Refund::query()
		                   ->and_where( 'transaction', '=', $this->ID )
		                   ->expression( 'SUM', 'amount', 'SUM' )->results()->get( 'SUM' );

		$total = (float) $total;

		/**
		 * Filter the total amount that has been refunded.
		 *
		 * @since 1.0.0
		 *
		 * @param float                   $total
		 * @param IT_Exchange_Transaction $this
		 * @param bool                    $format
		 */
		return apply_filters( 'it_exchange_get_transaction_refunds_total', $total, $this, false );
	}

	/**
	 * Get the transaction refunds.
	 *
	 * @since      0.4.0
	 *
	 * @deprecated 1.36.0
	 *
	 * @return array
	 */
	public function get_transaction_refunds() {

		_deprecated_function( __METHOD__, '1.36.0', 'IT_Exchange_Transaction::refunds' );

		$refunds = array();

		foreach ( $this->refunds as $refund ) {
			$refunds[] = array(
				'amount'  => $refund->amount,
				'date'    => $refund->created_at->format( 'Y-m-d H:i:s' ),
				'options' => $refund->get_meta()
			);
		}

		return apply_filters_deprecated( 'it_exchange_get_transaction_refunds', array( $refunds, $this ), '1.36.0' );
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
	 * @since 1.36.0 Introduce `$return_transactions` parameter.
	 *
	 * @param array $args                Arguments to filter children.
	 * @param bool  $return_transactions Return transaction objects.
	 *
	 * @return WP_Post[]|IT_Exchange_Transaction[]
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
	 * Get the cart object for the transaction.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Cart
	 */
	public function cart() {
		$repo = new ITE_Line_Item_Transaction_Repository( new ITE_Line_Item_Repository_Events(), $this );

		return new ITE_Cart( $repo, $this->cart_id, $this->get_customer() );
	}

	/**
	 * Convert a cart object to line items.
	 *
	 * @since 1.36.0
	 *
	 * @return bool
	 */
	public function convert_cart_object() {

		if ( $this->get_meta( 'cart_object_converted', true ) ) {
			return false;
		}

		$converter = new ITE_Line_Item_Transaction_Object_Converter();
		$converter->convert( $this->cart_details, $this );

		$this->update_meta( 'cart_object_converted', true );

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function get_pk() {
		return $this->ID;
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return static::$_db_manager->get( 'ite-transactions' );
	}

	/**
	 * @inheritDoc
	 */
	public function set_raw_attribute( $attribute, $value ) {

		if ( $attribute === 'ID' && is_object( $value ) ) {
			$value = $value->ID;
		}

		return parent::set_raw_attribute( $attribute, $value );
	}

	protected function _ID_relation() {
		return new HasForeignPost( 'ID', $this );
	}

	protected function _billing_relation() {
		return new HasForeign( 'billing', $this, '\ITE_Saved_Address' );
	}

	protected function _shipping_relation() {
		return new HasForeign( 'shipping', $this, '\ITE_Saved_Address' );
	}

	protected function _parent_relation() {
		return new HasForeign( 'parent', $this, get_class() );
	}

	protected function _children_relation() {
		return new HasMany( 'parent', 'IT_Exchange_Transaction', $this, 'children' );
	}

	protected function _refunds_relation() {
		return new HasMany( 'transaction', 'ITE_Refund', $this, 'refunds' );
	}

	/**
	 * @inheritDoc
	 */
	public static function accepts_prorate_credit_request( ITE_Prorate_Credit_Request $request ) {
		return $request instanceof ITE_Prorate_Forever_Credit_Request;
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
			'old_transaction_method' => $transaction->get_method()
		) );
	}

	/**
	 * Post helper.
	 *
	 * @since 1.36.0
	 *
	 * @return \WP_Post
	 */
	private function post() {
		if ( ! $this->post ) {
			$this->post = get_post( $this->ID );
		}

		return $this->post;
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
				// Do nothing
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
			return $this->method_id;
		}

		if ( $name === 'transaction_method' ) {
			return $this->method;
		}

		if ( $name === 'cart_details' ) {
			return get_post_meta( $this->ID, '_it_exchange_cart_object', true );
		}

		if ( in_array( $name, array( 'transaction_supports', 'transaction_data' ), true ) ) {
			return array();
		}

		if ( in_array( $name, array(
			'post_author',
			'post_date',
			'post_date_gmt',
			'post_content',
			'post_title',
			'post_excerpt',
			'post_status',
			'comment_status',
			'ping_status',
			'post_password',
			'post_name',
			'to_ping',
			'pinged',
			'post_modified',
			'post_modified_gmt',
			'post_content_filtered',
			'post_parent',
			'guid',
			'menu_order',
			'post_type',
			'post_mime_type',
			'comment_count',
			'filter',
		), true ) ) {
			return $this->post()->$name;
		}

		if ( $name === 'ID' || $name === 'id' ) {
			return (int) $this->get_raw_attribute( 'ID' );
		}

		if ( $name === 'customer_id' ) {
			return (int) $this->get_raw_attribute( 'customer_id' );
		}

		return parent::__get( $name );
	}

	/**
	 * @inheritDoc
	 */
	public function __isset( $name ) {
		return parent::__isset( $name );
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

	/**
	 * Gets a transaction meta property.
	 *
	 * @since      1.3.0
	 *
	 * @deprecated 1.36.0
	 */
	function get_transaction_meta( $key, $single = true ) {
		return $this->get_meta( $key, $single );
	}

	/**
	 * Updates a transaction meta property.
	 *
	 * @since      1.3.0
	 *
	 * @deprecated 1.36.0
	 */
	function update_transaction_meta( $key, $value ) {
		$this->update_meta( $key, $value );
	}

	/**
	 * Deletes a transaction meta property.
	 *
	 * @since      1.3.0
	 *
	 * @deprecated 1.36.0
	 */
	function delete_transaction_meta( $key, $value = '' ) {
		$this->delete_meta( $key, $value );
	}
}
