<?php
/**
 * Cached Session Repository.
 *
 * @since   2.0.0
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

	/** @var ITE_Session_Model */
	protected $model;

	/**
	 * @inheritDoc
	 */
	public function __construct(
		\IT_Exchange_In_Memory_Session $session,
		\IT_Exchange_Customer $customer = null,
		\ITE_Line_Item_Repository_Events $events
	) {
		parent::__construct( $session, $events );

		$this->customer = $customer;
	}

	/**
	 * Retrieve the cached session from a customer.
	 *
	 * @since 2.0.0
	 *
	 * @param \IT_Exchange_Customer $customer
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException If the cart cannot be retrieved.
	 */
	public static function from_customer( \IT_Exchange_Customer $customer ) {

		$session = ITE_Session_Model::find_best_for_customer( $customer );

		return static::setup_from_session( $session, $customer );
	}

	/**
	 * Retrieve one of the customer's active sessions by its ID.
	 *
	 * @since 2.0.0
	 *
	 * @param \IT_Exchange_Customer $customer
	 * @param string                $session_id
	 *
	 * @return self
	 *
	 * @throws \InvalidArgumentException If invalid session ID or cart cannot be retrieved.
	 */
	public static function from_session_id( \IT_Exchange_Customer $customer, $session_id ) {

		$session = ITE_Session_Model::get( $session_id );

		if ( $session && $session->customer && $session->customer->ID != $customer->id ) {
			throw new InvalidArgumentException( "Session ID '{$session->ID}' does not match customer #{$customer->id}'" );
		}

		return static::setup_from_session( $session, $customer );
	}

	/**
	 * Initialize the repository by cart id.
	 *
	 * @since 2.0.0
	 *
	 * @param string $cart_id
	 *
	 * @return \ITE_Line_Item_Cached_Session_Repository
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function from_cart_id( $cart_id ) {

		$session = ITE_Session_Model::from_cart_id( $cart_id );

		return static::setup_from_session( $session );
	}

	/**
	 * Setup the repository from a session model.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Session_Model    $session
	 * @param \IT_Exchange_Customer $customer
	 *
	 * @return \ITE_Line_Item_Cached_Session_Repository
	 *
	 * @throws \InvalidArgumentException
	 */
	private static function setup_from_session( ITE_Session_Model $session = null, IT_Exchange_Customer $customer = null ) {

		if ( ! $session ) {
			$cid = $customer ? $customer->id : 0;
			throw new InvalidArgumentException( "No cart can be retrieved for #{$cid}." );
		}

		if ( ! $customer && $session->customer ) {
			$customer = it_exchange_get_customer( $session->customer );
		}

		$self = new self(
			new IT_Exchange_In_Memory_Session( static::get_saver( $session->ID ), $session->data ),
			$customer,
			new ITE_Line_Item_Repository_Events()
		);

		$self->session_id = $session->ID;
		$self->cart_id    = $session->cart_id;
		$self->model      = $session;

		return $self;
	}

	/**
	 * Get the saver for the In Memory Session.
	 *
	 * @since 2.0.0
	 *
	 * @param string $session_id
	 *
	 * @return \Closure
	 */
	private static function get_saver( $session_id ) {

		return function ( $data ) use ( $session_id ) {
			$model = ITE_Session_Model::get( $session_id );

			if ( $model ) {
				$model->data = $data;
				$model->save();
			}
		};
	}

	/**
	 * @inheritDoc
	 */
	public function get_shipping_address() {
		return new ITE_In_Memory_Address( $this->get_shipping_address_data_for_customer( $this->customer ) );
	}

	/**
	 * @inheritDoc
	 */
	public function get_billing_address() {
		return new ITE_In_Memory_Address( $this->get_billing_address_data_for_customer( $this->customer ) );
	}

	/**
	 * Get the cart ID.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_cart_id() {
		return $this->cart_id;
	}

	/**
	 * Get the repo's customer.
	 *
	 * @since 2.0.0
	 *
	 * @return \IT_Exchange_Customer
	 */
	public function get_customer() {
		return $this->customer;
	}

	/**
	 * Get the session model.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Session_Model
	 */
	public function get_model() {
		return $this->model;
	}

	/**
	 * @inheritDoc
	 */
	public function expires_at() { return $this->get_model()->expires_at; }

	/**
	 * @inheritDoc
	 */
	protected function back_compat_filter_cart_product( $data ) {
		return apply_filters_deprecated( 'it_exchange_get_cart_product', array(
			$data,
			$data['product_cart_id'],
			array( 'use_cached_customer_cart' => $this->get_cart_id() )
		), '2.0.0' );
	}
}
