<?php
/**
 * Coupon factory for unit tests.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Test_Factory_For_Basic_Coupons
 */
class IT_Exchange_Test_Factory_For_Basic_Coupons extends WP_UnitTest_Factory_For_Post {

	/**
	 * IT_Exchange_Test_Factory_For_Basic_Coupons Constructor.
	 *
	 * @since 1.35
	 *
	 * @param WP_UnitTest_Factory $factory
	 */
	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions['post_type']  = 'it_exchange_coupon';
		$this->default_generation_definitions['post_title'] = new WP_UnitTest_Generator_Sequence(
			'Coupon title %s'
		);

		$this->default_generation_definitions['code'] = new WP_UnitTest_Generator_Sequence( 'CODE%s' );
	}

	/**
	 * Create a product object.
	 *
	 * @param array $args
	 *
	 * @return int|WP_Error
	 */
	function create_object( $args ) {

		$args['post_meta']['_it-basic-code'] = $args['code'];

		return it_exchange_add_coupon( $args );
	}

	/**
	 * Get a product object by its ID.
	 *
	 * @since 1.35
	 *
	 * @param int $post_id
	 *
	 * @return bool|IT_Exchange_Coupon
	 */
	function get_object_by_id( $post_id ) {
		return it_exchange_get_coupon( $post_id );
	}
}