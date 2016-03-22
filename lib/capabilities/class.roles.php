<?php
/**
 * Contains the roles class.
 *
 * @since   1.36
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
	 * Add the custom capabilities to the default roles.
	 * 
	 * @since 1.36
	 */
	public function add_caps_to_roles() {

		/** @var WP_Roles $wp_roles */
		global $wp_roles;
		
		foreach ( $this->capabilities->get_caps_for_product() as $cap ) {
			$wp_roles->get_role( 'administrator' )->add_cap( $cap );
		}
		
		foreach ( $this->capabilities->get_caps_for_transaction() as $cap ) {
			$wp_roles->get_role( 'administrator' )->add_cap( $cap );
		}
	}

}