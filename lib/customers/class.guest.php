<?php
/**
 * Guest Customer class.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Guest_Customer
 */
class IT_Exchange_Guest_Customer extends IT_Exchange_Customer {

	/** @var array */
	private $properties = array();

	/** @var IT_Exchange_Transaction|null */
	private $transaction;

	/**
	 * IT_Exchange_Guest_Customer constructor.
	 *
	 * @param mixed $email
	 * @param array $properties
	 */
	public function __construct( $email, array $properties = array() ) {

		$user     = new WP_User();
		$user->ID = $email;

		$full = '';

		if ( isset( $properties['first-name'] ) ) {
			$full .= $properties['first-name'] . ' ';
		} elseif ( isset( $properties['last-name'] ) ) {
			$full .= $properties['last-name'];
		}

		$full = trim( $full );

		if ( $full ) {
			$display_name = $full;
		} else {
			$display_name = $email;
		}

		$data               = new stdClass();
		$data->ID           = $email;
		$data->user_login   = false;
		$data->user_pass    = false;
		$data->display_name = sprintf( __( '%s (Guest)', 'it-l10n-ithemes-exchange' ), $display_name );
		$data->email        = $email;
		$data->user_email   = $email;
		$data->is_guest     = true;
		$user->data         = $data;

		parent::__construct( $user );

		$this->properties = $properties;
	}

	/**
	 * Create a guest customer from a transaction.
	 *
	 * @since 1.36.0
	 *
	 * @param \IT_Exchange_Transaction $transaction
	 *
	 * @return \IT_Exchange_Guest_Customer
	 */
	public static function from_transaction( IT_Exchange_Transaction $transaction ) {

		$props = array();

		if ( $transaction->get_billing_address() ) {
			$props['first-name'] = $transaction->get_billing_address()->offsetGet( 'first-name' );
			$props['last-name']  = $transaction->get_billing_address()->offsetGet( 'last-name' );
		}

		$customer = new self( $transaction->customer_email, $props );

		$customer->transaction = $transaction;

		return $customer;
	}

	/**
	 * @return string
	 */
	public function get_first_name() {
		return isset( $this->properties['first-name'] ) ? $this->properties['first-name'] : '';
	}

	/**
	 * @return string
	 */
	public function get_last_name() {
		return isset( $this->properties['last-name'] ) ? $this->properties['last-name'] : '';
	}

	/**
	 * @inheritDoc
	 */
	public function get_display_name() {

		$name = trim( "{$this->get_first_name()} {$this->get_last_name()}" );

		if ( ! $name ) {
			$name = $this->get_email();
		}

		return sprintf( __( '%s (Guest)', 'it-l10n-ithemes-exchange' ), $name );
	}

	/**
	 * @inheritDoc
	 */
	public function add_transaction_to_user( $transaction_id ) {
		// Do nothing
	}

	/**
	 * @inheritDoc
	 */
	public function has_transaction( $transaction_id ) {

		if ( ! $transaction = it_exchange_get_transaction( $transaction_id ) ) {
			return false;
		}

		return $this->get_email() === $transaction->customer_email;
	}

	/**
	 * @inheritDoc
	 */
	public function get_billing_address() {
		if ( $this->transaction ) {
			return $this->transaction->get_billing_address();
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function set_billing_address( ITE_Location $location ) {
		// No-op
	}

	/**
	 * @inheritDoc
	 */
	public function get_shipping_address() {
		if ( $this->transaction ) {
			return $this->transaction->get_shipping_address();
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function set_shipping_address( ITE_Location $location ) {
		// No-op
	}
}