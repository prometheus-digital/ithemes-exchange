<?php
/**
 * Coupon Object Type.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Coupon_Object_Type
 */
class ITE_Coupon_Object_Type extends ITE_CPT_Object_Type {

	/**
	 * @inheritDoc
	 */
	public function get_slug() { return 'coupon'; }

	/**
	 * @inheritDoc
	 */
	protected function get_post_type() { return 'it_exchange_coupon'; }

	/**
	 * @inheritDoc
	 */
	public function create_object( array $attributes ) {

		$id = it_exchange_add_coupon( $attributes );

		return it_exchange_get_coupon( $id );
	}

	/**
	 * @inheritDoc
	 */
	protected function convert_post( WP_Post $post ) {
		return it_exchange_get_coupon( $post ) ?: null;
	}
}
