<?php
/**
 * Contains the class or the customer object
 *
 * @since   0.3.8
 * @package IT_Exchange
 */

/**
 * The IT_Exchange_Customer class holds all important data for a specific customer
 *
 * @since 0.3.8
 */
class IT_Exchange_Customer implements ITE_Object {

	/**
	 * @var integer $id the customer id. corresponds with the WP user id
	 * @since 0.3.8
	 */
	public $id;
	public $ID;

	/**
	 * @var object $wp_user the wp_user or false
	 * @since 0.3.8
	 */
	public $wp_user;

	/**
	 * @var object $customer_data customer information
	 * @since 0.3.8
	 */
	public $data;

	/**
	 * @var array $transaction_history an array of all transactions the user has ever created
	 * @since 0.3.8
	 */
	public $transaction_history;

	/**
	 * @var array $purchase_history an array of all products ever purchased
	 * @since 0.3.8
	 */
	public $purchase_history;

	/** @var ITE_Location|null */
	private $billing;

	/** @var ITE_Location|null */
	private $shipping;

	/**
	 * Constructor. Sets up the customer
	 *
	 * @since 0.3.8
	 *
	 * @param  mixed $user customer id or WP User objectm
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $user ) {

		if ( is_string( $user ) && is_email( $user ) ) {
			_doing_it_wrong(
				'IT_Exchange_Customer::__construct()',
				"Don't instantiate customer directly. Use it_exchange_get_customer()",
				'2.0.0'
			);
		}

		if ( $user instanceof WP_User ) {
			$this->id      = $user->ID;
			$this->wp_user = $user;
			$this->set_customer_data();
		} else {
			$this->id = $user;
			$this->set_wp_user();
			$this->set_customer_data();
		}

		// Return false if not a WP User
		if ( ! $this->is_wp_user() ) {
			throw new InvalidArgumentException( 'Invalid user.' );
		}

		$this->ID = $this->id; // back-compat
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @param mixed $user
	 *
	 * @deprecated
	 */
	public function IT_Exchange_Customer( $user ) {

		self::__construct( $user );

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Sets the $wp_user property
	 *
	 * @since 0.3.8
	 * @return void
	 */
	public function set_wp_user() {
		$this->wp_user = new WP_User( $this->id );

		if ( is_wp_error( $this->wp_user ) ) {
			$this->wp_user = false;
		}
	}

	/**
	 * Sets customer data
	 *
	 * @since 0.3.8
	 * @return void
	 */
	public function set_customer_data() {

		if ( ! $this->data instanceof _IT_Exchange_Customer_Data ) {
			$this->data = new _IT_Exchange_Customer_Data( $this, (object) $this->data );
		}

		$data = $this->data;

		if ( is_object( $this->wp_user->data ) ) {
			$wp_user_data = get_object_vars( $this->wp_user->data );
			foreach ( (array) $wp_user_data as $key => $value ) {
				$data->$key = $value;
			}
		}

		$data->first_name = get_user_meta( $this->id, 'first_name', true );
		$data->last_name  = get_user_meta( $this->id, 'last_name', true );

		$this->data = apply_filters( 'it_exchange_set_customer_data', $data, $this->id );
	}

	/**
	 * Tack transaction_id to user_meta of customer
	 *
	 * @since      0.4.0
	 *
	 * @deprecated 2.0.0
	 *
	 * @param integer $transaction_id id of the transaction
	 *
	 * @return void
	 */
	public function add_transaction_to_user( $transaction_id ) {
		_deprecated_function( __FUNCTION__, '2.0.0' );
	}

	/**
	 * Tack transaction_id to user_meta of customer
	 *
	 * @since 0.4.0
	 *
	 * @param integer $transaction_id id of the transaction
	 *
	 * @return bool
	 */
	public function has_transaction( $transaction_id ) {
		return (bool) IT_Exchange_Transaction::query()
		                                     ->where( 'customer_id', '=', $this->get_ID() )
		                                     ->and_where( 'ID', '=', $transaction_id )
		                                     ->take( 1 )
		                                     ->first();
	}

	/**
	 * Get the customer's email address.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_email() {
		if ( isset( $this->data->user_email ) ) {
			return $this->data->user_email;
		} elseif ( is_email( $this->ID ) ) {
			return $this->ID;
		} else {
			return '';
		}
	}

	/**
	 * Get the customer's first name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_first_name() {
		return get_user_meta( $this->ID, 'first_name', true );
	}

	/**
	 * Get the customer's last name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_last_name() {
		return get_user_meta( $this->ID, 'last_name', true );
	}

	/**
	 * Get the customer's full name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_full_name() {
		return trim( "{$this->get_first_name()} {$this->get_last_name()}" );
	}

	/**
	 * Get the customer's display name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_display_name() {
		return ! empty( $this->data->display_name ) ? $this->data->display_name : '';
	}

	/**
	 * Get the customer's billing address.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $force_saved Force getting an ITE_Saved_Address object.
	 *
	 * @return \ITE_Location|ITE_Saved_Address|null
	 */
	public function get_billing_address( $force_saved = false ) {
		return $this->retrieve_address( ITE_Saved_Address::T_BILLING, $force_saved );
	}

	/**
	 * Set the customer's billing address.
	 *
	 * Accepts any `ITE_Location` instance. If passed a `ITE_Saved_Address` instance and it has the same ID as the
	 * current address, the passed address will simply be saved. If it is a different model, the passed model will
	 * become the primary address for this customer. If the old address `equals()` the new address, it will be deleted.
	 *
	 * Any other `ITE_Location` instance will be converted to an `ITE_Saved_Address` and saved.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Location $location
	 *
	 * @return \ITE_Saved_Address
	 * @throws \InvalidArgumentException
	 */
	public function set_billing_address( ITE_Location $location ) {

		$location = $this->persist_address( $location, $this->get_billing_address(), ITE_Saved_Address::T_BILLING );

		$this->billing = $location;

		do_action( 'it_exchange_customer_billing_address_updated', $location->to_array(), $this->id );

		return $location;
	}

	/**
	 * Get the customer's shipping address.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $force_saved Force getting an ITE_Saved_Address object.
	 *
	 * @return \ITE_Location|ITE_Saved_Address|null
	 */
	public function get_shipping_address( $force_saved = false ) {
		return $this->retrieve_address( ITE_Saved_Address::T_SHIPPING, $force_saved );
	}

	/**
	 * Set the customer's shipping address.
	 *
	 * @see   IT_Exchange_Customer::set_billing_address() for more information.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Location $location
	 *
	 * @return \ITE_Saved_Address
	 * @throws \InvalidArgumentException
	 */
	public function set_shipping_address( ITE_Location $location ) {

		$location = $this->persist_address( $location, $this->get_shipping_address(), ITE_Saved_Address::T_SHIPPING );

		$this->shipping = $location;

		do_action( 'it_exchange_shipping_address_updated', $location->to_array(), $this->id );

		return $location;
	}

	/**
	 * Retrieve this customer's address.
	 *
	 * Will fire necessary deprecated hooks and filters dynamically based on type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type        Accepts either 'shipping' or 'billing'.
	 * @param bool   $force_saved Force getting an ITE_Saved_Address object.
	 *
	 * @return \ITE_Saved_Address|ITE_Location|null In 99.9% of cases this returns an ITE_Saved_Address instance or
	 *                                              null.
	 */
	protected function retrieve_address( $type, $force_saved = false ) {

		if ( $this->$type ) {
			return $this->$type;
		}

		/** @var ITE_Saved_Address $address */
		$address = ITE_Saved_Address::get( $this->get_customer_meta( "primary_{$type}" ) );

		if ( ! $address ) {
			$parts = get_user_meta( $this->id, "it-exchange-{$type}-address", true );

			if ( is_array( $parts ) ) {
				$address = ITE_Saved_Address::create( array_merge( $parts, array(
					'customer' => $this->get_ID(),
				) ) );

			} elseif ( ! $this->get_customer_meta( $type === 'shipping' ? 'primary_billing' : 'primary_shipping' ) ) {
				$address = ITE_Saved_Address::query()->and_where( 'customer', '=', $this->get_ID() )->take( 1 )->first();
			}

			if ( $address ) {
				$this->update_customer_meta( "primary_{$type}", $address->ID );
			}
		}

		if ( $force_saved ) {
			return $address ?: null;
		}

		$raw_address = $address ? $address->to_array() : array();

		$filtered_address = apply_filters( 'it_exchange_get_customer_data', $raw_address, "{$type}_address", $this );

		if ( $filtered_address && $filtered_address !== $raw_address ) {
			_deprecated_hook(
				'it_exchange_get_customer_data', '2.0.0', '', "Filtering $type address with data filter is deprecated."
			);

			if ( $address ) {
				foreach ( $filtered_address as $key => $value ) {
					if ( ! isset( $address[ $key ] ) || $address[ $key ] !== $value ) {
						$address[ $key ] = $value;
					}
				}
			} else {
				$address = new ITE_In_Memory_Address( $filtered_address );
			}
		}

		$second_filtered = apply_filters_deprecated(
			"it_exchange_get_customer_{$type}_address", array( $filtered_address, $this->id ), '2.0.0'
		);

		if ( $second_filtered && $second_filtered !== $filtered_address ) {
			if ( $address ) {
				foreach ( $filtered_address as $key => $value ) {
					if ( ! isset( $address[ $key ] ) || $address[ $key ] !== $value ) {
						$address[ $key ] = $value;
					}
				}
			} else {
				$address = new ITE_In_Memory_Address( $filtered_address );
			}
		}

		$this->$type = $address;

		return $address ?: null;
	}

	/**
	 * Persist an address.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Location      $location
	 * @param \ITE_Location|null $current
	 * @param string             $type
	 *
	 * @return \ITE_Saved_Address
	 * @throws \InvalidArgumentException
	 */
	protected function persist_address( ITE_Location $location, ITE_Location $current = null, $type ) {

		if ( $location instanceof ITE_Saved_Address ) {
			$saved = $location;
		} else {
			$saved = ITE_Saved_Address::convert_to_saved( $location, $current, $this, $type, true, true );
		}

		if ( $saved ) {
			$this->update_customer_meta( "primary_{$type}", $saved->ID );
		}

		return $saved;
	}

	/**
	 * Get a customer's addresses.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $include_primary
	 *
	 * @return ITE_Saved_Address[]
	 */
	public function get_addresses( $include_primary = true ) {

		$addresses = ITE_Saved_Address::query()->and_where( 'customer', '=', $this->get_ID() )->results()->toArray();

		if ( $include_primary ) {
			return $addresses;
		}

		$billing  = (int) $this->get_customer_meta( 'primary_billing' );
		$shipping = (int) $this->get_customer_meta( 'primary_shipping' );

		$return = $addresses;

		foreach ( $addresses as $i => $address ) {
			if ( $address->get_pk() === $billing ) {
				unset( $return[ $i ] );
			} elseif ( $address->get_pk() === $shipping ) {
				unset( $return[ $i ] );
			}
		}

		return $return;
	}

	/**
	 * Gets a customer meta property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 1.3.0
	 */
	public function get_customer_meta( $key, $single = true ) {
		return get_user_meta( $this->id, '_it_exchange_customer_' . $key, $single );
	}

	/**
	 * Updates a customer meta property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 1.3.0
	 */
	public function update_customer_meta( $key, $value ) {
		update_user_meta( $this->id, '_it_exchange_customer_' . $key, $value );
	}

	/**
	 * Get the customer's purchase count.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_transactions_count() {
		return IT_Exchange_Transaction::query()
		                              ->where( 'customer_id', '=', $this->ID )
		                              ->expression( 'COUNT', 'ID', 'count' )
		                              ->results()->get( 'count' );
	}

	/**
	 * Get a customer's historical lifetime value.
	 *
	 * @since 2.0.0
	 *
	 * @return float
	 */
	public function get_total_spent() {
		return IT_Exchange_Transaction::query()
		                              ->where( 'customer_id', '=', $this->ID )
		                              ->and_where( 'cleared', '=', true )
		                              ->expression( 'SUM', 'subtotal', 'sum' )
		                              ->results()->get( 'sum' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_ID() { return $this->ID; }

	/**
	 * @inheritDoc
	 */
	public function __toString() { return $this->get_full_name(); }

	/**
	 * @inheritDoc
	 */
	public static function get_object_type() { return it_exchange_object_type_registry()->get( 'customer' ); }

	/**
	 * Get all payment tokens for this customer.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args
	 *
	 * @return \IronBound\DB\Collection|\ITE_Payment_Token[]
	 */
	public function get_tokens( array $args = array() ) {

		$args = ITUTility::merge_defaults( $args, array(
			'gateway' => '',
			'status'  => 'active',
			'primary' => null,
		) );

		$without = array();

		if ( $args['gateway'] ) {
			$without[] = 'active';
		}

		if ( $args['status'] === 'all' ) {
			$without[] = 'expires_at';
		}

		$query = ITE_Payment_Token::without_global_scopes( $without );
		$query->and_where( 'customer', '=', $this->ID );

		if ( $args['gateway'] ) {
			$mode = it_exchange_is_gateway_in_sandbox_mode( $args['gateway'] ) ? 'sandbox' : 'live';
			$query->and_where( 'gateway', '=', $args['gateway'] )
			      ->and_where( 'mode', '=', $mode );
		}

		if ( is_bool( $args['primary'] ) ) {
			$query->and_where( 'primary', '=', $args['primary'] );
		}

		return $query->results();
	}

	/**
	 * Returns true or false based on whether the $id property is a WP User id
	 *
	 * @since 0.3.8
	 * @return boolean
	 */
	public function is_wp_user() {
		return ! empty( $this->wp_user->ID );
	}

	/**
	 * Returns the purchase history
	 *
	 * @since 0.3.8
	 * @return mixed purchase_history or false
	 */
	public function get_purchase_history() {
		$history = empty( $this->purchase_history ) ? false : $this->purchase_history;

		return apply_filters( 'it_exchange_get_customer_purchase_history', $history, $this->id );
	}
}

/**
 * Class _IT_Exchange_Customer_Data
 *
 * @internal
 */
class _IT_Exchange_Customer_Data extends stdClass {

	/** @var IT_Exchange_Customer */
	private $customer;

	/** @var stdClass */
	private $data;

	/**
	 * _IT_Exchange_Customer_Data constructor.
	 *
	 * @param IT_Exchange_Customer $customer
	 * @param stdClass             $data
	 */
	public function __construct( IT_Exchange_Customer $customer, stdClass $data ) {
		$this->customer = $customer;
		$this->data     = $data;
	}

	/**
	 * Get a property.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	public function __get( $name ) {
		if ( $name === 'billing_address' ) {
			$a = $this->customer->get_billing_address();

			return $a ? $a->to_array() : array();
		}

		if ( $name === 'shipping_address' ) {
			$a = $this->customer->get_shipping_address();

			return $a ? $a->to_array() : array();
		}

		return isset( $this->data->{$name} ) ? $this->data->{$name} : null;
	}

	/**
	 * Magic set method.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name
	 * @param mixed  $value
	 */
	public function __set( $name, $value ) {

		if ( $name !== 'shipping_address' && $name !== 'billing_address' ) {
			$this->data->{$name} = $value;
		}
	}

	/**
	 * Magic isset method.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		return $this->__get( $name ) !== null;
	}
}
