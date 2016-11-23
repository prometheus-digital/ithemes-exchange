<?php
/**
 * Test the mutable wrapper.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Sendable_Mutable_Wrapper
 *
 * @group emails
 */
class Test_IT_Exchange_Sendable_Mutable_Wrapper extends IT_Exchange_UnitTestCase {

	public function test_override_subject() {

		$sendable = $this->getMockBuilder( 'IT_Exchange_Sendable' )->setMethods( array( 'get_subject' ) )
		                 ->getMockForAbstractClass();
		$sendable->method( 'get_subject' )->willReturn( 'Original' );

		$mutable = new IT_Exchange_Sendable_Mutable_Wrapper( $sendable );
		$this->assertEquals( 'Original', $mutable->get_subject() );

		$mutable->override_subject( 'Overwritten' );
		$this->assertEquals( 'Overwritten', $mutable->get_subject() );
	}

	public function test_override_body() {

		$sendable = $this->getMockBuilder( 'IT_Exchange_Sendable' )->setMethods( array( 'get_body' ) )
		                 ->getMockForAbstractClass();
		$sendable->method( 'get_body' )->willReturn( 'Original' );

		$mutable = new IT_Exchange_Sendable_Mutable_Wrapper( $sendable );
		$this->assertEquals( 'Original', $mutable->get_body() );

		$mutable->override_body( 'Overwritten' );
		$this->assertEquals( 'Overwritten', $mutable->get_body() );
	}

	public function test_get_template_falls_through() {

		$template = new IT_Exchange_Email_Template( 'receipt' );

		$sendable = $this->getMockBuilder( 'IT_Exchange_Sendable' )->setMethods( array( 'get_template' ) )
		                 ->getMockForAbstractClass();
		$sendable->method( 'get_template' )->willReturn( $template );

		$mutable = new IT_Exchange_Sendable_Mutable_Wrapper( $sendable );
		$this->assertEquals( $template, $mutable->get_template() );
	}

	public function test_get_recipient_falls_through() {

		$recipient = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );

		$sendable = $this->getMockBuilder( 'IT_Exchange_Sendable' )->setMethods( array( 'get_recipient' ) )
		                 ->getMockForAbstractClass();
		$sendable->method( 'get_recipient' )->willReturn( $recipient );

		$mutable = new IT_Exchange_Sendable_Mutable_Wrapper( $sendable );
		$this->assertEquals( $recipient, $mutable->get_recipient() );
	}

	public function test_override_ccs() {

		$cc1 = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );

		$sendable = $this->getMockBuilder( 'IT_Exchange_Sendable' )->setMethods( array( 'get_ccs' ) )
		                 ->getMockForAbstractClass();
		$sendable->method( 'get_ccs' )->willReturn( array( $cc1 ) );

		$mutable = new IT_Exchange_Sendable_Mutable_Wrapper( $sendable );
		$this->assertEquals( array( $cc1 ), $mutable->get_ccs() );

		$cc2 = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$mutable->add_cc( $cc2 );
		$this->assertEqualSets( array( $cc1, $cc2 ), $mutable->get_ccs() );
	}

	public function test_override_bccs() {

		$bcc1 = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );

		$sendable = $this->getMockBuilder( 'IT_Exchange_Sendable' )->setMethods( array( 'get_bccs' ) )
		                 ->getMockForAbstractClass();
		$sendable->method( 'get_bccs' )->willReturn( array( $bcc1 ) );

		$mutable = new IT_Exchange_Sendable_Mutable_Wrapper( $sendable );
		$this->assertEquals( array( $bcc1 ), $mutable->get_bccs() );

		$bcc2 = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$mutable->add_bcc( $bcc2 );
		$this->assertEqualSets( array( $bcc1, $bcc2 ), $mutable->get_bccs() );
	}

	public function test_override_context() {

		$sendable = $this->getMockBuilder( 'IT_Exchange_Sendable' )->setMethods( array( 'get_context' ) )
		                 ->getMockForAbstractClass();
		$sendable->method( 'get_context' )->willReturn( array( 'a' => 'b' ) );

		$mutable = new IT_Exchange_Sendable_Mutable_Wrapper( $sendable );
		$this->assertEquals( array( 'a' => 'b' ), $mutable->get_context() );

		$mutable->add_context( 'c', 'd' );
		$this->assertEqualSets( array( 'a' => 'b', 'c' => 'd' ), $mutable->get_context() );
	}

	public function test_get_original() {

		$sendable = $this->getMockForAbstractClass( 'IT_Exchange_Sendable' );
		$mutable  = new IT_Exchange_Sendable_Mutable_Wrapper( $sendable );
		$this->assertEquals( $sendable, $mutable->get_original() );
	}

	public function test_serialize() {

		$sendable = new IT_Exchange_Simple_Email( 'Subject', 'Message', new IT_Exchange_Email_Recipient_Email( 'example@example.org' ) );
		$mutable  = new IT_Exchange_Sendable_Mutable_Wrapper( $sendable );

		$mutable->override_subject( 'New Subject' );
		$mutable->override_body( 'New Body' );
		$mutable->add_cc( $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' ) );
		$mutable->add_bcc( $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' ) );
		$mutable->add_context( 'a', 'b' );

		$this->assertEquals( $mutable, unserialize( serialize( $mutable ) ) );
	}
}
