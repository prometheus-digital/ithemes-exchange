<?php
/**
 * Contains the product factory class.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Product_Factory
 *
 * @since 1.35
 */
class IT_Exchange_Product_Factory {

	/**
	 * Make a product object.
	 *
	 * @since 1.35
	 *
	 * @param WP_Post|int|IT_Exchange_Product $post
	 *
	 * @return IT_Exchange_Product
	 */
	public function make( $post ) {

		if ( $post instanceof IT_Exchange_Product ) {
			return $post;
		}

		if ( is_object( $post ) && ! empty( $post->ID ) ) {
			$ID = $post->ID;
		} else {
			$ID = $post;
		}

		$product_type = get_post_meta( $ID, '_it_exchange_product_type', true );

		if ( ! $product_type ) {
			return new IT_Exchange_Product( $post );
		}

		$addon = it_exchange_get_addon( $product_type );

		if ( $addon && ! empty( $addon['options']['class'] ) ) {
			$class = $addon['options']['class'];

			return new $class( $post );
		}

		return new IT_Exchange_Product( $post );
	}
}