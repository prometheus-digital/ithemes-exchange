<?php
/**
 * Test the email class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Email
 *
 * @group emails
 */
class Test_IT_Exchange_Email extends IT_Exchange_UnitTestCase {

	public function test_cc() {

		$to           = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$cc           = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$notification = $this->getMockBuilder( 'IT_Exchange_Email_Notification' )
		                     ->disableOriginalConstructor()->getMock();

		$email = new IT_Exchange_Email( $to, $notification );
		$email->add_cc( $cc );
		$this->assertEquals( array( $cc ), $email->get_ccs() );
	}

	public function test_bcc() {

		$to           = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$bcc          = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$notification = $this->getMockBuilder( 'IT_Exchange_Email_Notification' )
		                     ->disableOriginalConstructor()->getMock();

		$email = new IT_Exchange_Email( $to, $notification );
		$email->add_bcc( $bcc );
		$this->assertEquals( array( $bcc ), $email->get_bccs() );
	}

	public function test_context() {

		$to           = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$notification = $this->getMockBuilder( 'IT_Exchange_Email_Notification' )
		                     ->disableOriginalConstructor()->getMock();

		$email = new IT_Exchange_Email( $to, $notification, array( 'a' => 'b' ) );
		$this->assertEquals( array( 'a' => 'b' ), $email->get_context() );

		$email->add_context( 'd', 'c' );
		$this->assertEqualSets( array( 'a' => 'b', 'c' => 'd' ), $email->get_context() );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_exception_thrown_for_invalid_context_key() {

		$to           = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$notification = $this->getMockBuilder( 'IT_Exchange_Email_Notification' )
		                     ->disableOriginalConstructor()->getMock();

		$email = new IT_Exchange_Email( $to, $notification );
		$email->add_context( 'context', array() );
	}

	public function test_get_subject() {

		$to           = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$notification = $this->getMockBuilder( 'IT_Exchange_Email_Notification' )->disableOriginalConstructor()
		                     ->setMethods( array( 'get_subject' ) )
		                     ->getMockForAbstractClass();
		$notification->method( 'get_subject' )->willReturn( 'Subject' );

		$email = new IT_Exchange_Email( $to, $notification );
		$this->assertEquals( 'Subject', $email->get_subject() );
	}

	public function test_get_body() {

		$to           = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$notification = $this->getMockBuilder( 'IT_Exchange_Email_Notification' )->disableOriginalConstructor()
		                     ->setMethods( array( 'get_body' ) )
		                     ->getMockForAbstractClass();
		$notification->method( 'get_body' )->willReturn( 'Body' );

		$email = new IT_Exchange_Email( $to, $notification );
		$this->assertEquals( 'Body', $email->get_body() );
	}

	public function test_serialize_registered_notification() {

		$to           = new IT_Exchange_Email_Recipient_Email( 'example@example.org' );
		$notification = it_exchange_email_notifications()->get_notification( 'receipt' );

		$email = new IT_Exchange_Email( $to, $notification );

		$this->assertEquals( $email, unserialize( serialize( $email ) ) );
	}

	public function test_serialize_unregistered_notification() {

		$to           = new IT_Exchange_Email_Recipient_Email( 'example@example.org' );
		$notification = new IT_Exchange_Customer_Email_Notification( 'Name', 'name' );

		$email = new IT_Exchange_Email( $to, $notification );

		$this->assertEquals( $email, unserialize( serialize( $email ) ) );
	}

}
