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

		return $self;
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