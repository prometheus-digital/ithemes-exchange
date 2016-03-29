<?php
/**
 * Contains tests for the email only recipient.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Email_Recipient_Email
 *
 * @group emails
 */
class Test_IT_Exchange_Email_Recipient_Email extends IT_Exchange_UnitTestCase {

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_non_emails_rejects() {
		new IT_Exchange_Email_Recipient_Email( 'garbage' );
	}

	public function test_get_email() {
		$recipient = new IT_Exchange_Email_Recipient_Email( 'example@example.org' );
		$this->assertEquals( 'example@example.org', $recipient->get_email() );
	}

	public function test_get_name() {

		$recipient = new IT_Exchange_Email_Recipient_Email( 'example@example.org' );
		$this->assertEquals( 'example', $recipient->get_first_name() );
		$this->assertEquals( 'example', $recipient->get_full_name() );

	}
}