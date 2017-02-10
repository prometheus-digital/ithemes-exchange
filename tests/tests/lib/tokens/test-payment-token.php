<?php
/**
 * Test the payment token class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Payment_Token_Test
 *
 * @group payment-tokens
 */
class IT_Exchange_Payment_Token_Test extends IT_Exchange_UnitTestCase {

	public function test_only_tokens_for_correct_mode_are_returned() {
		$gateway             = new IT_Exchange_Test_Gateway_Sandbox();
		$gateway->handlers[] = new IT_Exchange_Stub_Gateway_Request_Handler();

		ITE_Gateways::register( $gateway );

		$t1 = ITE_Payment_Token_Card::create( array(
			'gateway'  => $gateway->get_slug(),
			'customer' => 1,
			'token'    => 'tok1',
			'primary'  => true,
			'mode'     => 'live',
		) );
		$t2 = ITE_Payment_Token_Card::create( array(
			'gateway'  => $gateway->get_slug(),
			'customer' => 1,
			'token'    => 'tok2',
			'primary'  => false,
			'mode'     => 'sandbox',
		) );

		$customer = it_exchange_get_customer( 1 );
		$tokens   = $customer->get_tokens();

		$this->assertEquals( 1, $tokens->count() );
		$this->assertEquals( $t2->ID, $tokens->first()->ID );
	}

	public function test_only_tokens_for_correct_mode_returned_when_gateway_specified() {

		$sandbox             = new IT_Exchange_Test_Gateway_Sandbox();
		$sandbox->handlers[] = new IT_Exchange_Stub_Gateway_Request_Handler();

		$live             = new IT_Exchange_Test_Gateway_Live();
		$live->handlers[] = new IT_Exchange_Stub_Gateway_Request_Handler();

		ITE_Gateways::register( $sandbox );

		$t1 = ITE_Payment_Token_Card::create( array(
			'gateway'  => $sandbox->get_slug(),
			'customer' => 1,
			'token'    => 'tok1',
			'primary'  => true,
			'mode'     => 'live',
		) );
		$t2 = ITE_Payment_Token_Card::create( array(
			'gateway'  => $sandbox->get_slug(),
			'customer' => 1,
			'token'    => 'tok2',
			'primary'  => false,
			'mode'     => 'sandbox',
		) );
		$t3 = ITE_Payment_Token_Card::create( array(
			'gateway'  => $live->get_slug(),
			'customer' => 1,
			'token'    => 'tok3',
			'primary'  => false,
			'mode'     => 'sandbox',
		) );

		$customer = it_exchange_get_customer( 1 );
		$tokens   = $customer->get_tokens( array( 'gateway' => $sandbox->get_slug() ) );

		$this->assertEquals( 1, $tokens->count() );
		$this->assertEquals( $t2->ID, $tokens->first()->ID );
	}

	public function test_primary_returned() {
		$gateway             = new IT_Exchange_Test_Gateway_Sandbox();
		$gateway->handlers[] = new IT_Exchange_Stub_Gateway_Request_Handler();

		ITE_Gateways::register( $gateway );

		$t1 = ITE_Payment_Token_Card::create( array(
			'gateway'  => $gateway->get_slug(),
			'customer' => 1,
			'token'    => 'tok1',
			'primary'  => true,
			'mode'     => 'sandbox',
		) );
		$t2 = ITE_Payment_Token_Card::create( array(
			'gateway'  => $gateway->get_slug(),
			'customer' => 1,
			'token'    => 'tok2',
			'primary'  => false,
			'mode'     => 'sandbox',
		) );

		$customer = it_exchange_get_customer( 1 );
		$tokens   = $customer->get_tokens( array( 'primary' => true ) );

		$this->assertEquals( 1, $tokens->count() );
		$this->assertEquals( $t1->ID, $tokens->first()->ID );

		$tokens = $customer->get_tokens( array( 'primary' => false ) );

		$this->assertEquals( 1, $tokens->count() );
		$this->assertEquals( $t2->ID, $tokens->first()->ID );
	}

	public function test_non_expired_tokens_not_returned_by_default() {

		$gateway             = new IT_Exchange_Test_Gateway_Sandbox();
		$gateway->handlers[] = new IT_Exchange_Stub_Gateway_Request_Handler();

		ITE_Gateways::register( $gateway );

		$t1 = ITE_Payment_Token_Card::create( array(
			'gateway'  => $gateway->get_slug(),
			'customer' => 1,
			'token'    => 'tok1',
			'primary'  => true,
			'mode'     => 'sandbox',
		) );
		$t2 = ITE_Payment_Token_Card::create( array(
			'gateway'    => $gateway->get_slug(),
			'customer'   => 1,
			'token'      => 'tok2',
			'primary'    => false,
			'mode'       => 'sandbox',
			'expires_at' => strtotime( '+1 year' ),
		) );
		$t3 = ITE_Payment_Token_Card::create( array(
			'gateway'    => $gateway->get_slug(),
			'customer'   => 1,
			'token'      => 'tok2',
			'primary'    => false,
			'mode'       => 'sandbox',
			'expires_at' => strtotime( '-1 year' ),
		) );

		$customer = it_exchange_get_customer( 1 );
		$tokens   = $customer->get_tokens();

		$this->assertEquals( 2, $tokens->count() );
		$this->assertNotNull( $tokens->get_model( $t1->ID ) );
		$this->assertNotNull( $tokens->get_model( $t2->ID ) );
	}

	public function test_expired_tokens_returned_if_status_is_active() {

		$gateway             = new IT_Exchange_Test_Gateway_Sandbox();
		$gateway->handlers[] = new IT_Exchange_Stub_Gateway_Request_Handler();

		ITE_Gateways::register( $gateway );

		$t1 = ITE_Payment_Token_Card::create( array(
			'gateway'  => $gateway->get_slug(),
			'customer' => 1,
			'token'    => 'tok1',
			'primary'  => true,
			'mode'     => 'sandbox',
		) );
		$t2 = ITE_Payment_Token_Card::create( array(
			'gateway'    => $gateway->get_slug(),
			'customer'   => 1,
			'token'      => 'tok2',
			'primary'    => false,
			'mode'       => 'sandbox',
			'expires_at' => strtotime( '+1 year' ),
		) );

		$customer = it_exchange_get_customer( 1 );
		$tokens   = $customer->get_tokens( array( 'status' => 'all' ) );

		$this->assertEquals( 2, $tokens->count() );
		$this->assertNotNull( $tokens->get_model( $t1->ID ) );
		$this->assertNotNull( $tokens->get_model( $t2->ID ) );
	}

	public function test_primary_token_is_included_first() {
		$gateway             = new IT_Exchange_Test_Gateway_Sandbox();
		$gateway->handlers[] = new IT_Exchange_Stub_Gateway_Request_Handler();

		ITE_Gateways::register( $gateway );

		$t1 = ITE_Payment_Token_Card::create( array(
			'gateway'  => $gateway->get_slug(),
			'customer' => 1,
			'token'    => 'tok1',
			'primary'  => false,
			'mode'     => 'sandbox',
		) );
		$t2 = ITE_Payment_Token_Card::create( array(
			'gateway'  => $gateway->get_slug(),
			'customer' => 1,
			'token'    => 'tok2',
			'primary'  => true,
			'mode'     => 'sandbox',
		) );

		$customer = it_exchange_get_customer( 1 );
		$tokens   = $customer->get_tokens();

		$this->assertEquals( 2, $tokens->count() );
		$this->assertEquals( $t2->ID, $tokens->first()->ID );
	}

	public function test_make_primary() {

		$gateway             = new IT_Exchange_Test_Gateway_Sandbox();
		$gateway->handlers[] = new IT_Exchange_Stub_Gateway_Request_Handler();

		ITE_Gateways::register( $gateway );

		$t1 = ITE_Payment_Token_Card::create( array(
			'gateway'  => $gateway->get_slug(),
			'customer' => 1,
			'token'    => 'tok1',
			'primary'  => false,
			'mode'     => 'sandbox',
		) );
		$t2 = ITE_Payment_Token_Card::create( array(
			'gateway'  => $gateway->get_slug(),
			'customer' => 1,
			'token'    => 'tok2',
			'primary'  => true,
			'mode'     => 'sandbox',
		) );

		$t1->make_primary();
		$t1 = ITE_Payment_Token::get( $t1->ID );
		$t2 = ITE_Payment_Token::get( $t2->ID );

		$this->assertTrue( (bool) $t1->primary );
		$this->assertFalse( (bool) $t2->primary );

		$t1->make_non_primary();
		$t1 = ITE_Payment_Token::get( $t1->ID );
		$t2 = ITE_Payment_Token::get( $t2->ID );

		$this->assertFalse( (bool) $t1->primary );
		$this->assertTrue( (bool) $t2->primary );
	}

	public function test_is_expiring() {
		$gateway             = new IT_Exchange_Test_Gateway_Sandbox();
		$gateway->handlers[] = new IT_Exchange_Stub_Gateway_Request_Handler();

		ITE_Gateways::register( $gateway );

		$t1 = ITE_Payment_Token_Card::create( array(
			'gateway'  => $gateway->get_slug(),
			'customer' => 1,
			'token'    => 'tok1',
			'primary'  => true,
			'mode'     => 'sandbox',
		) );
		$t2 = ITE_Payment_Token_Card::create( array(
			'gateway'    => $gateway->get_slug(),
			'customer'   => 1,
			'token'      => 'tok2',
			'primary'    => false,
			'mode'       => 'sandbox',
			'expires_at' => strtotime( '+1 year' ),
		) );
		$t3 = ITE_Payment_Token_Card::create( array(
			'gateway'    => $gateway->get_slug(),
			'customer'   => 1,
			'token'      => 'tok2',
			'primary'    => false,
			'mode'       => 'sandbox',
			'expires_at' => strtotime( '-1 year' ),
		) );

		$this->assertFalse( $t1->is_expired() );
		$this->assertFalse( $t2->is_expired() );
		$this->assertTrue( $t3->is_expired() );
	}

	public function test_is_expiring_soon() {
		$gateway             = new IT_Exchange_Test_Gateway_Sandbox();
		$gateway->handlers[] = new IT_Exchange_Stub_Gateway_Request_Handler();

		ITE_Gateways::register( $gateway );

		$t1 = ITE_Payment_Token_Card::create( array(
			'gateway'  => $gateway->get_slug(),
			'customer' => 1,
			'token'    => 'tok1',
			'primary'  => true,
			'mode'     => 'sandbox',
		) );
		$t2 = ITE_Payment_Token_Card::create( array(
			'gateway'    => $gateway->get_slug(),
			'customer'   => 1,
			'token'      => 'tok2',
			'primary'    => false,
			'mode'       => 'sandbox',
			'expires_at' => strtotime( '+1 year' ),
		) );
		$t3 = ITE_Payment_Token_Card::create( array(
			'gateway'    => $gateway->get_slug(),
			'customer'   => 1,
			'token'      => 'tok2',
			'primary'    => false,
			'mode'       => 'sandbox',
			'expires_at' => strtotime( '-1 year' ),
		) );
		$t4 = ITE_Payment_Token_Card::create( array(
			'gateway'    => $gateway->get_slug(),
			'customer'   => 1,
			'token'      => 'tok2',
			'primary'    => false,
			'mode'       => 'sandbox',
			'expires_at' => strtotime( '+1 week' ),
		) );

		$this->assertFalse( $t1->is_expiring_soon() );
		$this->assertFalse( $t2->is_expiring_soon() );
		$this->assertFalse( $t3->is_expiring_soon() );
		$this->assertTrue( $t4->is_expiring_soon() );
	}
}