<?php
/**
 * Test the custommer recipient.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Email_Recipient_Customer
 *
 * @group emails
 */
class Test_IT_Exchange_Email_Recipient_Customer extends IT_Exchange_UnitTestCase {

	public function test_get_email() {

		$uid = $this->factory()->user->create( array(
			'user_email'   => 'john.doe@gmail.com',
			'first_name'   => 'John',
			'last_name'    => 'Doe',
			'display_name' => 'JohnnyDoe'
		) );

		$customer = it_exchange_get_customer( $uid );

		$recipient = new IT_Exchange_Email_Recipient_Customer( $customer );
		$this->assertEquals( 'john.doe@gmail.com', $recipient->get_email() );
	}

	public function test_get_first_name() {

		$uid = $this->factory()->user->create( array(
			'user_email'   => 'john.doe@gmail.com',
			'first_name'   => 'John',
			'last_name'    => 'Doe',
			'display_name' => 'JohnnyDoe'
		) );

		$customer = it_exchange_get_customer( $uid );

		$recipient = new IT_Exchange_Email_Recipient_Customer( $customer );
		$this->assertEquals( 'John', $recipient->get_first_name() );
	}

	public function test_get_first_name_fallsback_to_display_name() {

		$uid = $this->factory()->user->create( array(
			'user_email'   => 'john.doe@gmail.com',
			'last_name'    => 'Doe',
			'display_name' => 'JohnnyDoe'
		) );

		$customer = it_exchange_get_customer( $uid );

		$recipient = new IT_Exchange_Email_Recipient_Customer( $customer );
		$this->assertEquals( 'JohnnyDoe', $recipient->get_first_name() );
	}

	public function test_get_full_name() {

		$uid = $this->factory()->user->create( array(
			'user_email'   => 'john.doe@gmail.com',
			'first_name'   => 'John',
			'last_name'    => 'Doe',
			'display_name' => 'JohnnyDoe'
		) );

		$customer = it_exchange_get_customer( $uid );

		$recipient = new IT_Exchange_Email_Recipient_Customer( $customer );
		$this->assertEquals( 'John Doe', $recipient->get_full_name() );
	}

	public function test_get_full_name_fallsback_to_display_name() {

		$uid = $this->factory()->user->create( array(
			'user_email'   => 'john.doe@gmail.com',
			'last_name'    => 'Doe',
			'display_name' => 'JohnnyDoe'
		) );

		$customer = it_exchange_get_customer( $uid );

		$recipient = new IT_Exchange_Email_Recipient_Customer( $customer );
		$this->assertEquals( 'JohnnyDoe', $recipient->get_full_name() );
	}
}
