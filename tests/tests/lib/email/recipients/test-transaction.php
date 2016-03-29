<?php
/**
 * Contains the tests for the transaction recipient.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Email_Recipient_Transaction
 *
 * @group emails
 */
class Test_IT_Exchange_Email_Recipient_Transaction extends IT_Exchange_UnitTestCase {

	public function test_get_email() {

		$uid = $this->factory()->user->create( array(
			'user_email'   => 'john.doe@gmail.com',
			'first_name'   => 'John',
			'last_name'    => 'Doe',
			'display_name' => 'JohnnyDoe'
		) );

		$txn = $this->transaction_factory->create_and_get( array( 'customer' => $uid ) );

		$recipient = new IT_Exchange_Email_Recipient_Transaction( $txn );
		$this->assertEquals( 'john.doe@gmail.com', $recipient->get_email() );
	}

	public function test_get_first_name() {

		$uid = $this->factory()->user->create( array(
			'user_email'   => 'john.doe@gmail.com',
			'first_name'   => 'John',
			'last_name'    => 'Doe',
			'display_name' => 'JohnnyDoe'
		) );

		$txn = $this->transaction_factory->create_and_get( array( 'customer' => $uid ) );

		$recipient = new IT_Exchange_Email_Recipient_Transaction( $txn );
		$this->assertEquals( 'John', $recipient->get_first_name() );
	}

	public function test_get_first_name_fallsback_to_display_name() {

		$uid = $this->factory()->user->create( array(
			'user_email'   => 'john.doe@gmail.com',
			'last_name'    => 'Doe',
			'display_name' => 'JohnnyDoe'
		) );

		$txn = $this->transaction_factory->create_and_get( array( 'customer' => $uid ) );

		$recipient = new IT_Exchange_Email_Recipient_Transaction( $txn );
		$this->assertEquals( 'JohnnyDoe', $recipient->get_first_name() );
	}

	public function test_get_full_name() {

		$uid = $this->factory()->user->create( array(
			'user_email'   => 'john.doe@gmail.com',
			'display_name' => 'JohnnyDoe'
		) );

		$txn = $this->transaction_factory->create_and_get( array( 'customer' => $uid ) );

		$recipient = new IT_Exchange_Email_Recipient_Transaction( $txn );
		$this->assertEquals( 'JohnnyDoe', $recipient->get_full_name() );
	}

	public function test_get_email_guest_checkout() {

		$txn = $this->transaction_factory->create_and_get( array(
			'customer'    => 'john.doe@gmail.com',
			'cart_object' => (object) array(
				'is_guest_checkout' => true
			)
		) );

		$recipient = new IT_Exchange_Email_Recipient_Transaction( $txn );
		$this->assertEquals( 'john.doe@gmail.com', $recipient->get_email() );
	}


	public function test_get_first_name_guest_checkout() {

		$txn = $this->transaction_factory->create_and_get( array(
			'customer'    => 'john.doe@gmail.com',
			'cart_object' => (object) array(
				'is_guest_checkout' => true
			)
		) );

		$recipient = new IT_Exchange_Email_Recipient_Transaction( $txn );
		$this->assertEquals( 'john', $recipient->get_first_name() );
	}

	public function test_get_full_name_guest_checkout() {

		$txn = $this->transaction_factory->create_and_get( array(
			'customer'    => 'john.doe@gmail.com',
			'cart_object' => (object) array(
				'is_guest_checkout' => true
			)
		) );

		$recipient = new IT_Exchange_Email_Recipient_Transaction( $txn );
		$this->assertEquals( 'Guest Customer (john.doe@gmail.com)', $recipient->get_full_name() );
	}
}