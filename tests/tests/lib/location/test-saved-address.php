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

		$customer->set_billing_address( ITE_Saved_Address::convert_to_saved(
			$saved,
			ITE_Saved_Address::get( $saved->ID ),
			$customer,
			'billing',
			false
		) );

		$_customer = it_exchange_get_customer( 1 );
		$this->assertEquals( '#4', $_customer->get_billing_address( true )->offsetGet( 'address2' ) );
		$this->assertEquals( '', $txn->get_billing_address()->offsetGet( 'address2' ) );
		$this->assertEquals( $saved_id, $txn->get_billing_address()->ID );
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
}