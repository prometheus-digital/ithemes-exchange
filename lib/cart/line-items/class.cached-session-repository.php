<?php
/**
 * Cached Session Repository.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Line_Item_Cached_Session_Repository
 */
class ITE_Line_Item_Cached_Session_Repository extends ITE_Line_Item_Session_Repository {

	/** @var  IT_Exchange_Customer */
	protected $customer;

	/** @var string|null */
	protected $session_id;

	/** @var string */
	protected $cart_id;

	/**
	 * @inheritDoc
	 */
	public function __construct(
		\IT_Exchange_In_Memory_Session $session,
		\IT_Exchange_Customer $customer,
		\ITE_Line_Item_Repository_Events $events
	) {
		parent::__construct( $session, $events );

		$this->customer = $customer;
	}

	/**
	 * Retrieve the cached session from a customer.
	 *
	 * @since 1.36
	 *
	 * @param \IT_Exchange_Customer $customer
	 *
	 * @return self
	 *
	 * @throws \InvalidArgumentException
	 * @throws UnexpectedValueException If the cart cannot be retrieved.
	 */
	public static function from_customer( \IT_Exchange_Customer $customer ) {

		$session = it_exchange_get_cached_customer_cart( $customer->id );

		if ( ! is_array( $session ) || count( $session ) === 0 ) {
			throw new UnexpectedValueException( "No cached cart can be retrieved for #{$customer->id}." );
		}

		$self = new self(
			new IT_Exchange_In_Memory_Session( '', $session ),
			$customer,
			new ITE_Line_Item_Repository_Events()
		);
		$self->session->set_save( array( $self, '_do_cache_save' ) );

		if ( isset( $session['cart_id'], $session['cart_id'][0] ) ) {
			$self->cart_id = $session['cart_id'][0];
		}

		return $self;
	}

	/**
	 * Retrieve one of the customer's active sessions by its ID.
	 *
	 * @since 1.36
	 *
	 * @param \IT_Exchange_Customer $customer
	 * @param string                $session_id
	 *
	 * @return self
	 * @throws \InvalidArgumentException
	 * @throws \UnexpectedValueException If cart cannot be retrieved.
	 */
	public static function from_session_id( \IT_Exchange_Customer $customer, $session_id ) {

		$sessions = it_exchange_get_active_carts_for_customer( true, $customer->id );

		if ( ! isset( $sessions[ $session_id ] ) ) {
			throw new UnexpectedValueException( "No cart can be retrieved for #{$customer->id} with ID '$session_id'." );
		}

		$session = get_option( '_it_exchange_db_session_' . $session_id, array() );

		if ( ! is_array( $session ) || count( $session ) === 0 ) {
			throw new UnexpectedValueException( "No cart can be retrieved for #{$customer->id} with ID '$session_id'." );
		}

		$self = new self(
			new IT_Exchange_In_Memory_Session( '', $session ),
			$customer,
			new ITE_Line_Item_Repository_Events()
		);

		$self->session->set_save( array(
			$self,
			'_do_active_save'
		) );
		$self->session_id = $session_id;

		if ( isset( $session['cart_id'], $session['cart_id'][0] ) ) {
			$self->cart_id = $session['cart_id'][0];
		}

		return $self;
	}

	/**
	 * @inheritDoc
	 */
	public function get_shipping_address() {

		$customer = $this->customer;

		$customer_data = empty( $customer->data ) ? new stdClass() : $customer->data;

		// Default values for first time use.
		$defaults = array(
			'first-name'   => empty( $customer_data->first_name ) ? '' : $customer_data->first_name,
			'last-name'    => empty( $customer_data->last_name ) ? '' : $customer_data->last_name,
			'company-name' => '',
			'address1'     => '',
			'address2'     => '',
			'city'         => '',
			'state'        => '',
			'zip'          => '',
			'country'      => '',
			'email'        => empty( $customer_data->user_email ) ? '' : $customer_data->user_email,
			'phone'        => '',
		);

		// See if the customer has a shipping address saved. If so, overwrite defaults with saved shipping address
		if ( ! empty( $customer_data->shipping_address ) ) {
			$defaults = ITUtility::merge_defaults( $customer_data->shipping_address, $defaults );
		}

		// If data exists in the session, use that as the most recent
		$session_data = $this->session->get_session_data( 'shipping-address' );

		return ITUtility::merge_defaults( $session_data, $defaults );
	}

	/**
	 * @inheritDoc
	 */
	public function get_billing_address() {

		$customer = $this->customer;

		$customer_data = empty( $customer->data ) ? new stdClass() : $customer->data;

		// Default values for first time use.
		$defaults = array(
			'first-name'   => empty( $customer_data->first_name ) ? '' : $customer_data->first_name,
			'last-name'    => empty( $customer_data->last_name ) ? '' : $customer_data->last_name,
			'company-name' => '',
			'address1'     => '',
			'address2'     => '',
			'city'         => '',
			'state'        => '',
			'zip'          => '',
			'country'      => '',
			'email'        => empty( $customer_data->user_email ) ? '' : $customer_data->user_email,
			'phone'        => '',
		);

		// See if the customer has a billing address saved. If so, overwrite defaults with saved billing address
		if ( ! empty( $customer_data->billing_address ) ) {
			$defaults = ITUtility::merge_defaults( $customer_data->billing_address, $defaults );
		}

		// If data exists in the session, use that as the most recent
		$session_data = $this->session->get_session_data( 'billing-address' );

		return ITUtility::merge_defaults( $session_data, $defaults );
	}

	/**
	 * Get the cart ID.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_cart_id() {
		return $this->cart_id;
	}

	/**
	 * Do the session saving.
	 *
	 * @since 1.36
	 *
	 * @internal
	 *
	 * @param array $session
	 */
	public function _do_cache_save( array $session ) {
		update_user_meta( $this->customer->id, '_it_exchange_cached_cart', $session );
	}

	/**
	 * Do the session saving.
	 *
	 * @since 1.36
	 *
	 * @internal
	 *
	 * @param array $session
	 */
	public function _do_active_save( array $session ) {
		update_option( '_it_exchange_db_session_' . $this->session_id, $session, false );
	}
}