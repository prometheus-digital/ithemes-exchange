<?php
/**
 * Contains the roles class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Roles
 */
class IT_Exchange_Roles {

	/**
	 * @var IT_Exchange_Capabilities
	 */
	private $capabilities;

	/**
	 * IT_Exchange_Roles constructor.
	 *
	 * @param IT_Exchange_Capabilities $capabilities
	 */
	public function __construct( IT_Exchange_Capabilities $capabilities ) {

		$this->capabilities = $capabilities;

		add_action( 'init', array( $this, 'add_caps_to_roles' ) );
	}

	/**
	 * Get the capabilities manager.
	 *
	 * @since 2.0.0
	 *
	 * @return IT_Exchange_Capabilities
	 */
	public function get_capabilities() {
		return $this->capabilities;
	}

	/**
	 * Add the custom capabilities to the default roles.
	 *
	 * @since 2.0.0
	 */
	public function add_caps_to_roles() {

		/** @var WP_Roles $wp_roles */
		global $wp_roles;

		$administrator = $wp_roles->get_role( 'administrator' );

		foreach ( $this->capabilities->get_caps_for_product() as $cap ) {
			$administrator->add_cap( $cap );
		}

		foreach ( $this->capabilities->get_caps_for_transaction() as $cap ) {
			$administrator->add_cap( $cap );
		}

		foreach ( $this->capabilities->get_caps_for_coupons() as $cap ) {
			$administrator->add_cap( $cap );
		}

		$administrator->add_cap( 'it_perform_upgrades' );

		$administrator->add_cap( 'it_list_others_payment_tokens' );
		$administrator->add_cap( 'it_create_others_payment_tokens' );
		$administrator->add_cap( 'it_edit_others_payment_tokens' );

		$administrator->add_cap( 'it_edit_refunds' );
		$administrator->add_cap( 'it_list_refunds' );
		$administrator->add_cap( 'it_list_transaction_refunds' );

		/**
		 * Fires when custom capabilities should be added to roles.
		 *
		 * @since 2.0.0
		 *
		 * @param \IT_Exchange_Roles $this
		 * @param \WP_Roles          $wp_roles
		 */
		do_action( 'it_exchange_add_caps_to_roles', $this, $wp_roles );
	}

}
