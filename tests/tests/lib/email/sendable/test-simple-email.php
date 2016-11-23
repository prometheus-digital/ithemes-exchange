<?php
/**
 * Contains tests for the simple email class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Simple_Email
 *
 * @group emails
 */
class Test_IT_Exchange_Simple_Email extends IT_Exchange_UnitTestCase {

	public function test() {

		$to  = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$cc  = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$bcc = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );

		$email = new IT_Exchange_Simple_Email( 'Subject', 'Message', $to, array( 'a' => 'b' ), array(
			'cc'  => $cc,
			'bcc' => $bcc
		) );

		$this->assertEquals( 'Subject', $email->get_subject() );
		$this->assertEquals( 'Message', $email->get_body() );
		$this->assertEquals( $to, $email->get_recipient() );
		$this->assertEquals( array( 'a' => 'b' ), $email->get_context() );
		$this->assertEquals( array( $cc ), $email->get_ccs() );
		$this->assertEquals( array( $bcc ), $email->get_bccs() );
		$this->assertNull( $email->get_template()->get_name() );
		$this->assertEquals( $email, unserialize( serialize( $email ) ) );
	}
}
