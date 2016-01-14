<?php
/**
 * Generate transactions for testing.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Test_Factory_For_Transactions
 */
class IT_Exchange_Test_Factory_For_Transactions extends WP_UnitTest_Factory_For_Post {

	/**
	 * IT_Exchange_Test_Factory_For_Transactions constructor.
	 *
	 * @param WP_UnitTest_Factory $factory
	 */
	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions['method_id'] = new WP_UnitTest_Generator_Sequence(
			'test-method-id-%s'
		);
		$this->default_generation_definitions['post_type'] = 'it_exchange_tran';
	}

	/**
	 * Create the transaction in the database.
	 *
	 * @param array $args
	 *
	 * @return int|WP_Error
	 *
	 * @throws Exception
	 */
	function create_object( $args ) {

		$defaults = array(
			'method'      => 'test-method',
			'status'      => 'pending',
			'customer'    => 1,
			'cart_object' => new stdClass(),
			'cart_id'     => it_exchange_create_cart_id(),
		);

		$args = wp_parse_args( $args, $defaults );

		$method      = $args['method'];
		$method_id   = $args['method_id'];
		$status      = $args['status'];
		$customer    = $args['customer'];
		$cart_object = $args['cart_object'];

		if ( empty( $cart_object->cart_id ) ) {
			$cart_object->cart_id = $args['cart_id'];
		}

		unset( $args['method'], $args['method_id'], $args['status'],
			$args['customer'], $args['cart_object'], $args['cart_id'] );

		return it_exchange_add_transaction( $method, $method_id, $status, $customer, $cart_object, $args );
	}

	/**
	 * Get a transaction by ID.
	 *
	 * @since 1.35
	 *
	 * @param int $post_id
	 *
	 * @return bool|IT_Exchange_Transaction
	 */
	function get_object_by_id( $post_id ) {
		return it_exchange_get_transaction( $post_id );
	}
}
