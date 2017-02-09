<?php

/**
 * Class Test_IT_Exchange_Customer
 */
class Test_IT_Exchange_Customer extends IT_Exchange_UnitTestCase {

	public function test_data_first_name() {
		$customer = it_exchange_get_customer( self::factory()->user->create( array(
			'first_name' => 'John'
		) ) );

		$this->assertEquals( 'John', $customer->data->first_name );
	}

	public function test_data_last_name() {
		$customer = it_exchange_get_customer( self::factory()->user->create( array(
			'last_name' => 'Doe'
		) ) );

		$this->assertEquals( 'Doe', $customer->data->last_name );
	}

	public function test_billing_address() {
		$customer = it_exchange_get_customer( self::factory()->user->create() );

		$customer->set_billing_address( new ITE_In_Memory_Address( array(
			'country' => 'US'
		) ) );

		$this->assertInternalType( 'array', $customer->data->billing_address );
		$this->assertInstanceOf( 'ITE_Location', $customer->get_billing_address() );
		$this->assertEquals( 'US', $customer->get_billing_address()->offsetGet( 'country' ) );
	}

	public function test_shipping_address() {
		$customer = it_exchange_get_customer( self::factory()->user->create() );

		$customer->set_shipping_address( new ITE_In_Memory_Address( array(
			'country' => 'US'
		) ) );

		$this->assertInternalType( 'array', $customer->data->shipping_address );
		$this->assertInstanceOf( 'ITE_Location', $customer->get_shipping_address() );
		$this->assertEquals( 'US', $customer->get_shipping_address()->offsetGet( 'country' ) );
	}

	public function test_arbitrary_data() {

		$customer = it_exchange_get_customer( self::factory()->user->create() );

		$customer->data->test = 'value';

		$this->assertEquals( 'value', $customer->data->test );
	}
}
