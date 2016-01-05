<?php
/**
 * Contains the payment gateway activity actor class.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Txn_Activity_Gateway_Actor
 */
class IT_Exchange_Txn_Activity_Gateway_Actor implements IT_Exchange_Txn_Activity_Actor {

	/**
	 * @var array
	 */
	private $addon;

	/**
	 * IT_Exchange_Txn_Activity_Gateway_Actor constructor.
	 *
	 * @param array $addon
	 */
	public function __construct( $addon ) {
		$this->addon = $addon;
	}

	/**
	 * Make a gateway actor from an activity ID.
	 *
	 * This is used as a callback from the actor factory, it should not be called directly.
	 *
	 * @internal
	 *
	 * @param int $activity_id
	 *
	 * @return IT_Exchange_Txn_Activity_User_Actor
	 */
	public static function make( $activity_id ) {
		return new self( it_exchange_get_addon( get_post_meta( $activity_id, '_actor_gateway', true ) ) );
	}

	/**
	 * Get the actor's name.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->addon['name'];
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

		if ( ! empty( $this->addon['options']['icon'] ) ) {
			return $this->addon['options']['icon'];
		}

		/** @var IT_Exchange */
		global $IT_Exchange;

		return $IT_Exchange->_plugin_url . '/lib/admin/images/e64.png';
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
		return 'gateway';
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
		update_post_meta( $activity->get_ID(), '_actor_gateway',
			it_exchange_get_transaction_method( $activity->get_transaction() )
		);

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