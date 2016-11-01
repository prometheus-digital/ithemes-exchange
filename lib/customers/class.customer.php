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
class IT_Exchange_Customer {

	/**
	 * @var integer $id the customer id. corresponds with the WP user id
	 * @since 0.3.8
	 */
	public $id;

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
	 * @param  mixed $user customer id or WP User object
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $user ) {

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
	 * @since 0.4.0
	 *
	 * @param integer $transaction_id id of the transaction
	 *
	 * @return void
	 */
	public function add_transaction_to_user( $transaction_id ) {
		add_user_meta( $this->id, '_it_exchange_transaction_id', $transaction_id );
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
		$transaction_ids = (array) get_user_meta( $this->id, '_it_exchange_transaction_id' );

		return in_array( $transaction_id, $transaction_ids );
	}

	/**
	 * Get the customer's email address.
	 *
	 * @since 1.36.0
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
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_first_name() {
		return get_user_meta( $this->ID, 'first_name', true );
	}

	/**
	 * Get the customer's last name.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_last_name() {
		return get_user_meta( $this->ID, 'last_name', true );
	}

	/**
	 * Get the customer's full name.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_full_name() {
		return trim( "{$this->get_first_name()} {$this->get_last_name()}" );
	}

	/**
	 * Get the customer's display name.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_display_name() {
		return ! empty( $this->data->display_name ) ? $this->data->display_name : '';
	}

	/**
	 * Get the customer's billing address.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Location|null
	 */
	public function get_billing_address() {
		return $this->retrieve_address( ITE_Saved_Address::T_BILLING );
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
	 * @since 1.36.0
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
	 * @since 1.36.0
	 *
	 * @return \ITE_Location|null
	 */
	public function get_shipping_address() {
		return $this->retrieve_address( ITE_Saved_Address::T_SHIPPING );
	}

	/**
	 * Set the customer's shipping address.
	 *
	 * @see   IT_Exchange_Customer::set_billing_address() for more information.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Location $location
	 *
	 * @return \ITE_Location
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
	 * @since 1.36.0
	 *
	 * @param string $type Accepts either 'shipping' or 'billing'.
	 *
	 * @return \ITE_Saved_Address|ITE_Location|null In 99.9% of cases this returns an ITE_Saved_Address instance or
	 *                                              null.
	 */
	protected function retrieve_address( $type ) {

		if ( $this->$type ) {
			return $this->$type;
		}

		/** @var ITE_Saved_Address $address */
		$address = ITE_Saved_Address::query()
		                            ->where( 'customer', '=', $this->id )->and_where( 'primary', '=', true )
		                            ->and_where( 'type', '=', $type )->first();

		if ( ! $address ) {
			$parts = get_user_meta( $this->id, "it-exchange-{$type}-address", true );

			if ( is_array( $parts ) ) {
				$address = ITE_Saved_Address::create( array_merge( $parts, array(
					'customer' => $this->id,
					'primary'  => true,
					'type'     => $type,
				) ) );
			} else {
				$address = null;
			}
		}

		$raw_address = $address ? $address->to_array() : array();

		$filtered_address = apply_filters( 'it_exchange_get_customer_data', $raw_address, "{$type}_address", $this );

		if ( $filtered_address && $filtered_address !== $raw_address ) {
			_deprecated_hook(
				'it_exchange_get_customer_data', '1.36.0', '', "Filtering $type address with data filter is deprecated."
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
			"it_exchange_get_customer_{$type}_address", array( $filtered_address, $this->id ), '1.36.0'
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

		return $address;
	}

	/**
	 * Persist an address.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Location      $location
	 * @param \ITE_Location|null $current
	 * @param string             $type
	 *
	 * @return \ITE_Location
	 * @throws \InvalidArgumentException
	 */
	protected function persist_address( ITE_Location $location, ITE_Location $current = null, $type ) {
		$saved = ITE_Saved_Address::convert_to_saved( $location, $current, $this, $type, true, true );

		if ( $saved ) {
			$saved->make_primary();
		}

		return $saved;
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
	 * @since 1.36.0
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
	 * @since 1.36.0
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
	 * Get all payment tokens for this customer.
	 *
	 * @since 1.36.0
	 *
	 * @param string $gateway
	 *
	 * @return \IronBound\DB\Collection|\ITE_Payment_Token[]
	 */
	public function get_tokens( $gateway = '' ) {

		if ( ! $gateway ) {
			return ITE_Payment_Token::query()->where( 'customer', '=', $this->ID )->results();
		}

		return ITE_Payment_Token::without_global_scopes( array( 'active' ) )
		                        ->and_where( 'customer', '=', $this->ID )
		                        ->and_where( 'gateway', '=', $gateway )
		                        ->results();
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
	 * @since 1.36.0
	 *
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	public function __get( $name ) {
		if ( $name === 'billing_address' ) {
			return $this->customer->get_billing_address();
		}

		if ( $name === 'shipping_address' ) {
			return $this->customer->get_shipping_address();
		}

		return isset( $this->data->{$name} ) ? $this->data->{$name} : null;
	}

	/**
	 * Magic set method.
	 *
	 * @since 1.36.0
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
	 * @since 1.36.0
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		return $this->__get( $name ) !== null;
	}
}

/**
 * Handles $_REQUESTs and submits them to the registration for processing
 *
 * @since 0.4.0
 * @return void
 */
function handle_it_exchange_customer_registration_action() {

	// Grab action and process it.
	if ( isset( $_POST['it-exchange-register-customer'] ) ) {
		global $wp;

		do_action( 'before_handle_it_exchange_customer_registration_action' );

		$user_id = it_exchange_register_user();

		if ( is_wp_error( $user_id ) ) {
			it_exchange_add_message( 'error', $user_id->get_error_message() );

			return;
		}

		$creds = array(
			'user_login'    => $_POST['user_login'],
			'user_password' => $_POST['pass1'],
		);

		$user = wp_signon( $creds );

		if ( is_wp_error( $user ) ) {
			it_exchange_add_message( 'error', $user->get_error_message() );

			return;
		}

		$registration_url = trailingslashit( it_exchange_get_page_url( 'registration' ) );
		$checkout_url     = trailingslashit( it_exchange_get_page_url( 'checkout' ) );
		$current_home_url = trailingslashit( home_url( $wp->request ) );
		$current_site_url = trailingslashit( site_url( $wp->request ) );
		$referrer         = trailingslashit( wp_get_referer() );

		// Redirect or clear query args
		$redirect_hook_slug = false;

		if ( in_array( $referrer, array( $registration_url, $checkout_url ) )
		     || in_array( $current_home_url, array( $registration_url, $checkout_url ) )
		     || in_array( $current_site_url, array( $registration_url, $checkout_url ) )
		) {
			// If on the reg page, check for redirect cookie.
			$login_redirect = it_exchange_get_session_data( 'login_redirect' );
			if ( ! empty( $login_redirect ) ) {
				$redirect           = reset( $login_redirect );
				$redirect_hook_slug = 'registration-to-variable-return-url';
				it_exchange_clear_session_data( 'login_redirect' );
			} else {
				if ( it_exchange_is_page( 'registration' ) ) {
					$redirect           = it_exchange_get_page_url( 'profile' );
					$redirect_hook_slug = 'registration-success-from-registration';
				}
				if ( it_exchange_is_page( 'checkout' ) ) {
					$redirect           = it_exchange_get_page_url( 'checkout' );
					$redirect_hook_slug = 'registration-success-from-checkout';
				}
			}
		} else {
			// Then were in the superwidget
			$redirect = it_exchange_clean_query_args( array(), array( 'ite-sw-state' ) );
		}

		do_action( 'handle_it_exchange_customer_registration_action' );
		do_action( 'after_handle_it_exchange_customer_registration_action' );

		it_exchange_redirect( $redirect, $redirect_hook_slug );
		die();
	}
}

add_action( 'template_redirect', 'handle_it_exchange_customer_registration_action', 5 );
