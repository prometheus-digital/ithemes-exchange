<?php
/**
 * Test the Saved Address object.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Saved_Address_Test
 *
 * @group location
 */
class IT_Exchange_Saved_Address_Test extends IT_Exchange_UnitTestCase {

	public function test_save_address_updates_record_if_not_in_use() {

		$address = array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street'
		);
		$saved   = ITE_Saved_Address::create( array_merge( $address, array( 'customer' => 1 ) ) );

		$saved['address2'] = '#4';

		$new = ITE_Saved_Address::convert_to_saved(
			$saved,
			ITE_Saved_Address::get( $saved->ID ),
			it_exchange_get_customer( 1 ),
			'billing',
			false
		);

		$this->assertEquals( $saved->ID, $new->ID );
	}

	public function test_save_address_leaves_transactions_in_tact() {

		$address = array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street'
		);

		$customer = it_exchange_get_customer( 1 );
		$txn      = self::transaction_factory()->create_and_get( array(
			'billing_address' => $address,
		) );

		$saved             = $customer->get_billing_address( true );
		$saved_id          = $saved->ID;
		$saved['address2'] = '#4';

		ITE_Saved_Address::convert_to_saved( $saved, ITE_Saved_Address::get( $saved->ID ), $customer );

		$txn = it_exchange_get_transaction( $txn->ID );

		$_customer = it_exchange_get_customer( 1 );
		$this->assertNotEquals( $_customer->get_billing_address( true )->ID, $saved_id );
		$this->assertEquals( $txn->get_billing_address()->ID, $saved_id, 'Transaction has same address ID' );
		$this->assertEquals( '#4', $_customer->get_billing_address( true )->offsetGet( 'address2' ), 'Billing change saved.' );
		$this->assertEquals( '', $txn->get_billing_address()->offsetGet( 'address2' ), 'Transaction address not modified' );
		$this->assertEquals( $saved_id, $txn->get_billing_address()->ID );
		$this->assertTrue( $txn->get_billing_address()->is_trashed() );
	}

	public function test_save_address_reuses_record_from_same_customer() {

		$address  = array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street'
		);
		$customer = it_exchange_get_customer( 1 );

		$saved = ITE_Saved_Address::create( array_merge( $address, array( 'customer' => 1 ) ) );
		$new   = ITE_Saved_Address::convert_to_saved( new ITE_In_Memory_Address( $address ), null, $customer, '', false );

		$this->assertEquals( $saved->ID, $new->ID );
	}

	public function test_save_address_doesnt_reuse_record_from_different_customer() {

		$address  = array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street'
		);
		$customer = it_exchange_get_customer( 1 );

		$saved = ITE_Saved_Address::create( array_merge( $address, array( 'customer' => 2 ) ) );
		$new   = ITE_Saved_Address::convert_to_saved( new ITE_In_Memory_Address( $address ), null, $customer, '', false );

		$this->assertNotEquals( $saved->ID, $new->ID );
	}

	public function test_save_address_doesnt_reuse_record_for_non_customer() {

		$address = array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street'
		);

		$saved = ITE_Saved_Address::create( $address );
		$new   = ITE_Saved_Address::convert_to_saved( new ITE_In_Memory_Address( $address ), null, null, '', false );

		$this->assertNotEquals( $saved->ID, $new->ID );
	}

	public function test_new_address_is_also_primary() {

		$address = array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street'
		);

		$customer = it_exchange_get_customer( 1 );
		$customer->set_billing_address( new ITE_In_Memory_Address( $address ) );
		$txn = self::transaction_factory()->create_and_get( array(
			'billing_address' => $customer->get_billing_address(),
		) );

		$saved             = $customer->get_billing_address( true );
		$saved['address2'] = '#4';

		$new = ITE_Saved_Address::convert_to_saved( $saved, ITE_Saved_Address::get( $saved->ID ), $customer, 'billing', false );
		$this->assertNotEquals( $new->ID, $saved->ID );
		$this->assertEquals( '', ITE_Saved_Address::get( $txn->get_billing_address()->ID )->offsetGet( 'address2' ) );

		$_customer = it_exchange_get_customer( 1 );
		$primary   = $_customer->get_billing_address( true );
		$this->assertEquals( '#4', $primary['address2'] );
	}

	public function test_single_address_record_for_same_billing_and_shipping() {

		$customer = it_exchange_get_customer( self::factory()->user->create() );

		$address = array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street'
		);

		$customer->set_billing_address( new ITE_In_Memory_Address( $address ) );
		$customer->set_shipping_address( new ITE_In_Memory_Address( $address ) );

		$this->assertCount( 1, $customer->get_addresses() );
		$this->assertEquals(
			$customer->get_billing_address( true )->get_pk(),
			$customer->get_shipping_address( true )->get_pk()
		);
	}

	/**
	 * @depends test_single_address_record_for_same_billing_and_shipping
	 */
	public function test_updating_billing_for_shared_shipping_billing_in_use_does_not_update_shipping() {
		$customer = it_exchange_get_customer( self::factory()->user->create() );

		$address = array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street'
		);

		$billing  = $customer->set_billing_address( new ITE_In_Memory_Address( $address ) );
		$shipping = $customer->set_shipping_address( new ITE_In_Memory_Address( $address ) );

		$this->assertEquals( $billing->ID, $shipping->ID );

		add_filter( 'it_exchange_shipping_address_purchase_requirement_enabled', '__return_true' );
		self::transaction_factory()->create( array(
			'customer'         => $customer,
			'billing_address'  => $billing,
			'shipping_address' => $shipping
		) );

		$new_billing             = ITE_Saved_Address::get( $billing->get_pk() );
		$new_billing['address1'] = '456 Main Street';
		$new_billing             = ITE_Saved_Address::convert_to_saved( $new_billing, $billing, $customer, 'billing' );

		$customer     = it_exchange_get_customer( $customer->get_ID() );
		$_new_billing = $customer->get_billing_address( true );
		$this->assertFalse( $_new_billing->is_trashed() );
		$this->assertEquals( $new_billing->get_pk(), $_new_billing->get_pk() );
		$this->assertEquals( '456 Main Street', $_new_billing['address1'], 'Change to billing address saved' );
		$this->assertNotEquals( $_new_billing->get_pk(), $billing->get_pk() );

		$_shipping = $customer->get_shipping_address( true );
		$this->assertFalse( $_shipping->is_trashed(), 'Shipping not trashed' );
		$this->assertEquals( $shipping->get_pk(), $_shipping->get_pk(), 'Shipping ID is the same' );
		$this->assertEquals( '123 Main Street', $_shipping['address1'], 'Shipping address fields are unchanged' );
		$this->assertEquals( $customer->get_ID(), $_shipping->customer->get_ID(), 'Shipping customer unchanged' );

		$this->assertNotEquals( $_new_billing->get_pk(), $_shipping->get_pk() );
		$this->assertCount( 2, $customer->get_addresses() );
	}

	public function test_updating_billing_for_shared_shipping_billing_not_used_does_not_update_shipping() {
		$customer = it_exchange_get_customer( self::factory()->user->create() );

		$address = array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street'
		);

		$billing  = $customer->set_billing_address( new ITE_In_Memory_Address( $address ) );
		$shipping = $customer->set_shipping_address( new ITE_In_Memory_Address( $address ) );

		$this->assertEquals( $billing->ID, $shipping->ID );

		$new_billing             = ITE_Saved_Address::get( $billing->get_pk() );
		$new_billing['address1'] = '456 Main Street';
		$new_billing             = ITE_Saved_Address::convert_to_saved( $new_billing, $billing, $customer, 'billing' );

		$customer     = it_exchange_get_customer( $customer->get_ID() );
		$_new_billing = $customer->get_billing_address( true );
		$this->assertFalse( $_new_billing->is_trashed() );
		$this->assertEquals( $new_billing->get_pk(), $_new_billing->get_pk() );
		$this->assertEquals( '456 Main Street', $_new_billing['address1'], 'Change to billing address saved' );
		$this->assertNotEquals( $_new_billing->get_pk(), $billing->get_pk() );

		$_shipping = $customer->get_shipping_address( true );
		$this->assertFalse( $_shipping->is_trashed(), 'Shipping not trashed' );
		$this->assertEquals( $shipping->get_pk(), $_shipping->get_pk(), 'Shipping ID is the same' );
		$this->assertEquals( '123 Main Street', $_shipping['address1'], 'Shipping address fields are unchanged' );

		$this->assertCount( 2, $customer->get_addresses() );
	}

	/**
	 * @depends test_updating_billing_for_shared_shipping_billing_in_use_does_not_update_shipping
	 */
	public function test_updating_address_reuses_existing_record() {
		$customer = it_exchange_get_customer( self::factory()->user->create() );

		$address = array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street'
		);

		$billing  = $customer->set_billing_address( new ITE_In_Memory_Address( $address ) );
		$shipping = $customer->set_shipping_address( new ITE_In_Memory_Address( $address ) );

		$this->assertEquals( $billing->ID, $shipping->ID );

		add_filter( 'it_exchange_shipping_address_purchase_requirement_enabled', '__return_true' );
		self::transaction_factory()->create( array(
			'customer'         => $customer,
			'billing_address'  => $billing,
			'shipping_address' => $shipping
		) );

		$new_billing             = ITE_Saved_Address::get( $billing->get_pk() );
		$new_billing['address1'] = '456 Main Street';
		$new_billing             = ITE_Saved_Address::convert_to_saved( $new_billing, $billing, $customer, 'billing' );

		$customer = it_exchange_get_customer( $customer->get_ID() );

		$new_shipping             = ITE_Saved_Address::get( $shipping->get_pk() );
		$new_shipping['address1'] = '456 Main Street';
		$new_shipping             = ITE_Saved_Address::convert_to_saved( $new_shipping, $shipping, $customer, 'shipping' );

		$this->assertEquals( $new_billing->get_pk(), $new_shipping->get_pk() );
		$this->assertEquals( '456 Main Street', $new_shipping['address1'] );

		$customer = it_exchange_get_customer( $customer->get_ID() );
		$this->assertEquals( '456 Main Street', $customer->get_shipping_address()->offsetGet( 'address1' ) );

		$this->assertTrue( ITE_Saved_Address::get( $shipping->get_pk() )->is_trashed() );
		$this->assertFalse( ITE_Saved_Address::get( $new_shipping->get_pk() )->is_trashed() );
	}

	public function test_trashed_address_will_be_reused() {
		$customer = it_exchange_get_customer( self::factory()->user->create() );

		$address           = array(
			'first-name' => 'John',
			'last-name'  => 'Doe',
			'address1'   => '123 Main Street'

		);
		$billing           = new ITE_Saved_Address( $address );
		$billing->customer = $customer;
		$billing->save();

		$billing->delete();

		$new_billing = $customer->set_billing_address( new ITE_In_Memory_Address( $address ) );

		$this->assertEquals( $billing->get_pk(), $new_billing->get_pk() );
		$this->assertFalse( $new_billing->is_trashed() );
	}
}