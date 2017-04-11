<?php
/**
 * Test the cart class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_ITE_Cart
 *
 * @group cart
 */
class Test_ITE_Cart extends IT_Exchange_UnitTestCase {

	public function test_get_item_instances_are_unique() {

		$cart = $this->cart( 1, true );

		$item   = $cart->get_items( 'product' )->first();
		$item_1 = $cart->get_item( $item->get_type(), $item->get_id() );
		$item_2 = $cart->get_item( $item->get_type(), $item->get_id() );

		$this->assertNotSame( $item, $item_1 );
		$this->assertNotSame( $item_1, $item_2 );
		$this->assertNotSame( $item, $item_2 );

		$item->set_param( 'test', 'test' );
		$this->assertNotTrue( $item_1->has_param( 'test' ) );
		$this->assertNotTrue( $item_2->has_param( 'test' ) );
	}

	public function test_get_item_after_save_item_returns_updated_value() {

		$cart = $this->cart( 1, true );

		$item = $cart->get_items( 'product' )->first();
		$item->set_param( 'test', 'test' );
		$cart->save_item( $item );

		$_item = $cart->get_item( 'product', $item->get_id() );
		$this->assertTrue( $_item->has_param( 'test' ) );
		$this->assertEquals( 'test', $_item->get_param( 'test' ) );
	}

	public function test_item_not_returned_after_being_removed() {

		$cart = $this->cart( 1, true );
		$item = $cart->get_items( 'product' )->first();

		$cart->remove_item( $item );
		$this->assertNull( $cart->get_item( 'product', $item->get_id() ) );
	}

	public function test_multi_items_added_during_add_item_are_accessible() {

		$cart    = $this->cart( 1, true );
		$product = $cart->get_items( 'product' )->first();

		$tax = ITE_Simple_Tax_Line_Item::create( 5 );
		$cart->add_item( $tax );

		$this->assertCount( 1, $cart->get_items( 'tax', true ) );

		/** @var ITE_Tax_Line_Item $product_tax */
		$product_tax = $cart->get_items( 'tax', true )->first();

		$this->assertEquals( $product->get_id(), $product_tax->get_aggregate()->get_id() );
	}
}