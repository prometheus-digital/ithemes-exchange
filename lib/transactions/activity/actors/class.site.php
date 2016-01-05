<?php
/**
 * Contains site actor class.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Txn_Activity_Site_Actor
 *
 * This is used for actions like refunds, which are processed by the site.
 */
class IT_Exchange_Txn_Activity_Site_Actor implements IT_Exchange_Txn_Activity_Actor {

	/**
	 * Get the actor's name.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_name() {
		return get_bloginfo( 'name' );
	}

	/**
	 * Get the URL to the icon representing this actor.
	 *
	 * This will attempt to use the site icon, otherwise it will fall back to the Exchange icon.
	 *
	 * @since 1.34
	 *
	 * @param int $size Suggested size. Do not rely on this value.
	 *
	 * @return string
	 */
	public function get_icon_url( $size ) {

		/** @var IT_Exchange */
		global $IT_Exchange;

		$exchange_icon = $IT_Exchange->_plugin_url . '/lib/admin/images/e64.png';

		if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
			return get_site_icon_url( $size, $exchange_icon );
		}

		return $exchange_icon;
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
		return 'site';
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