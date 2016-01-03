<?php
/**
 * Contains customer actor class.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Txn_Activity_Customer_Actor
 */
class IT_Exchange_Txn_Activity_Customer_Actor implements IT_Exchange_Txn_Activity_Actor {

	/**
	 * @var IT_Exchange_Customer
	 */
	private $customer;

	/**
	 * IT_Exchange_Txn_Activity_Customer_Actor constructor.
	 *
	 * @param IT_Exchange_Customer $customer
	 */
	public function __construct( IT_Exchange_Customer $customer ) {
		$this->customer = $customer;
	}

	/**
	 * Get the actor's name.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->customer->data->display_name;
	}

	/**
	 * Get the URL to the icon representing this actor.
	 *
	 * @since 1.34
	 *
	 * @param int $size Suggested size. Do not rely on this value.
	 *
	 * @return string
	 */
	public function get_icon_url( $size ) {
		return get_avatar_url( $this->customer->data->user_email, array(
			'size' => $size
		) );
	}

	/**
	 * Get the URL to view details about this actor.
	 *
	 * This could be a user's profile, for example.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_detail_url() {

		if ( is_int( $this->customer->ID ) ) {
			return add_query_arg( 'it_exchange_customer_data', 1, get_edit_user_link( $this->customer->ID ) );
		}

		return '';
	}

	/**
	 * Get the type of this actor.
	 *
	 * Ex: 'user', 'customer'.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_type() {
		return 'customer';
	}

	/**
	 * Attach this actor to an activity item.
	 *
	 * @since 1.34
	 *
	 * @param IT_Exchange_Txn_Activity $activity
	 *
	 * @return self
	 */
	public function attach( IT_Exchange_Txn_Activity $activity ) {
		return $this;
	}
}