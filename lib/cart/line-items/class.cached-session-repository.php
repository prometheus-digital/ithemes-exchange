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

	/** @var ITE_Session_Model */
	protected $model;

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
	 * @throws InvalidArgumentException If the cart cannot be retrieved.
	 */
	public static function from_customer( \IT_Exchange_Customer $customer ) {

		$session = ITE_Session_Model::query()
		                            ->where( 'customer', '=', $customer->id )
		                            ->order_by( 'updated_at', 'ASC' )
		                            ->first();

		return static::setup_from_session( $session, $customer );
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
	 *
	 * @throws \InvalidArgumentException If invalid session ID or cart cannot be retrieved.
	 */
	public static function from_session_id( \IT_Exchange_Customer $customer, $session_id ) {

		$session = ITE_Session_Model::get( $session_id );

		if ( $session && $session->customer->ID != $customer->id ) {
			throw new InvalidArgumentException( "Session ID '{$session->ID}' does not match customer #{$customer->id}'" );
		}

		return static::setup_from_session( $session, $customer );
	}

	/**
	 * Setup the repository from a session model.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Session_Model    $session
	 * @param \IT_Exchange_Customer $customer
	 *
	 * @return \ITE_Line_Item_Cached_Session_Repository
	 *
	 * @throws \InvalidArgumentException
	 */
	private static function setup_from_session( ITE_Session_Model $session = null, IT_Exchange_Customer $customer ) {

		if ( ! $session || ! $session->data || count( $session->data ) === 0 ) {
			throw new InvalidArgumentException( "No cart can be retrieved for #{$customer->id}." );
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
	 * @since 1.36.0
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
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_cart_id() {
		return $this->cart_id;
	}

	/**
	 * Get the session model.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Session_Model
	 */
	public function get_model() {
		return $this->model;
	}

	/**
	 * @inheritDoc
	 */
	protected function back_compat_filter_cart_product( $data ) {
		return apply_filters_deprecated( 'it_exchange_get_cart_product', array(
			$data,
			$data['product_cart_id'],
			array( 'use_cached_customer_cart' => $this->get_cart_id() )
		), '1.36.0' );
	}
}
