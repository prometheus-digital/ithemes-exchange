<?php
/**
 * Contains the factory for creating product objects.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Test_Factory_For_Products
 */
class IT_Exchange_Test_Factory_For_Products extends WP_UnitTest_Factory_For_Post {

	/**
	 * IT_Exchange_Test_Factory_For_Products Constructor.
	 *
	 * @since 1.35
	 *
	 * @param WP_UnitTest_Factory $factory
	 */
	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions['title'] = new WP_UnitTest_Generator_Sequence(
			'Product title %s'
		);
	}

	/**
	 * Create a product object.
	 *
	 * @param array $args
	 *
	 * @return int|WP_Error
	 */
	function create_object( $args ) {

		$args = wp_parse_args( $args, array(
			'type' => 'simple-product-type'
		) );

		return it_exchange_add_product( $args );
	}

	/**
	 * Get a product object by its ID.
	 *
	 * @since 1.35
	 *
	 * @param int $post_id
	 *
	 * @return bool|IT_Exchange_Product
	 */
	function get_object_by_id( $post_id ) {
		return it_exchange_get_product( $post_id );
	}
}