<?php
/**
 * Product Object Type.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Product_Object_Type
 */
class ITE_Product_Object_Type extends ITE_CPT_Object_Type {

	/**
	 * @inheritDoc
	 */
	protected function get_post_type() { return 'it_exchange_prod'; }

	/**
	 * @inheritDoc
	 */
	public function create_object( array $attributes ) {

		$id = it_exchange_add_product( $attributes );

		return it_exchange_get_product( $id );
	}

	/**
	 * @inheritDoc
	 */
	protected function convert_post( WP_Post $post ) {
		return it_exchange_get_product( $post );
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() { return 'product'; }
}
