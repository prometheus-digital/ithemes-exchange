<?php
/**
 * Customer Object Type.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Customer_Object_Type
 */
class ITE_Customer_Object_Type extends ITE_User_Object_Type {

	/**
	 * @inheritDoc
	 */
	public function get_slug() { return 'customer'; }

	/**
	 * @inheritDoc
	 */
	public function get_label() { return __( 'Customer', 'it-l10n-ithemes-exchange' ); }

	/**
	 * @inheritDoc
	 */
	public function create_object( array $attributes ) {

		$uid = wp_insert_user( $attributes );

		return it_exchange_get_customer( $uid );
	}

	/**
	 * @inheritDoc
	 */
	protected function convert_user( WP_User $user ) {
		return it_exchange_get_customer( $user->ID );
	}
}