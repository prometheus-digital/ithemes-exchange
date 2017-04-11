<?php
/**
 * Generate transactions for testing.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Test_Factory_For_Transactions
 *
 * @method IT_Exchange_Transaction create_and_get( array $args = array() )
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
			'method'   => 'test-method',
			'status'   => 'pending',
			'customer' => 1,
			'cart_id'  => it_exchange_create_cart_id(),
		);

		$args = wp_parse_args( $args, $defaults );

		$method    = $args['method'];
		$method_id = $args['method_id'];
		$status    = $args['status'];
		$customer  = $args['customer'];

		if ( ! empty( $args['cart_object'] ) ) {
			$cart_object = $args['cart_object'];

			if ( empty( $cart_object->cart_id ) ) {
				$cart_object->cart_id = $args['cart_id'];
			}
		} elseif ( ! empty( $args['cart'] ) ) {
			$cart_object = $args['cart'];
		} else {
			$product_factory = new IT_Exchange_Test_Factory_For_Products();

			if ( empty( $args['make_current_cart'] ) ) {
				$cart_object = ITE_Cart::create(
					new ITE_Cart_Session_Repository( new IT_Exchange_In_Memory_Session( null ), new ITE_Line_Item_Repository_Events() ),
					it_exchange_get_customer( $args['customer'] )
				);
			} else {
				wp_set_current_user( it_exchange_get_customer( $args['customer'] )->get_ID() );
				$cart_object = it_exchange_get_current_cart( false );

				if ( $cart_object ) {
					$cart_object->destroy();
				}

				$cart_object = it_exchange_get_current_cart();
			}

			$cart_object->add_item( ITE_Cart_Product::create( $product_factory->create_and_get() ) );

			if ( ! empty( $args['shipping_address'] ) ) {
				if ( $args['shipping_address'] instanceof ITE_Location ) {
					$cart_object->set_shipping_address( $args['shipping_address'] );
				} else {
					$cart_object->set_shipping_address( new ITE_In_Memory_Address( $args['shipping_address'] ) );
				}
			}

			if ( ! empty( $args['billing_address'] ) ) {
				if ( $args['billing_address'] instanceof ITE_Location ) {
					$cart_object->set_billing_address( $args['billing_address'] );
				} else {
					$cart_object->set_billing_address( new ITE_In_Memory_Address( $args['billing_address'] ) );
				}
			}
		}

		unset( $args['method'], $args['method_id'], $args['status'],
			$args['customer'], $args['cart_object'], $args['cart'], $args['cart_id'] );

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
