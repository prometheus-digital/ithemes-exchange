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
	 * Make a customer actor from an activity ID.
	 *
	 * This is used as a callback from the actor factory, it should not be called directly.
	 *
	 * @internal
	 *
	 * @param int $activity_id
	 *
	 * @return IT_Exchange_Txn_Activity_Customer_Actor
	 */
	public static function make( $activity_id ) {

		// this sucks, it breaks encapsulation,
		// unfortunately we can't trust the customer ID because of guest transactions
		$txn_id = wp_get_post_parent_id( $activity_id );

		return new self( it_exchange_get_transaction_customer( $txn_id ) );
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

		if ( ! function_exists( 'get_avatar_url' ) ) {
			return '';
		}

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

	/**
	 * Convert the actor to an array of data.
	 *
	 * Substitute for jsonSerialize because 5.2 ;(
	 *
	 * @since 1.34
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'name' => $this->get_name(),
			'icon' => $this->get_icon_url( 48 ),
			'url'  => $this->get_detail_url()
		);
	}
}