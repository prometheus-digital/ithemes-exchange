<?php
/**
 * Test the admin email notification class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Admin_Email_Notification
 *
 * @group emails
 */
class Test_IT_Exchange_Admin_Email_Notification extends IT_Exchange_UnitTestCase {

	public function test_default_email() {
		add_filter( 'it_exchange_get_option-settings_general', function ( $settings ) {
			$settings['company-email'] = 'test@example.org';

			return $settings;
		} );

		$email = new IT_Exchange_Admin_Email_Notification( 'Test Admin Email', 'test-admin-email-0' );
		$this->assertEquals( array( 'test@example.org' ), $email->get_emails() );
	}

	public function test_add_remove_emails() {

		$email = new IT_Exchange_Admin_Email_Notification( 'Test Admin Email', 'test-admin-email-1' );
		$email->set_emails( array() );
		$email->add_email( 'example@example.org' )->add_email( 'example@example.org' );

		$this->assertEqualSets( array( 'example@example.org' ), $email->get_emails() );

		$email->add_email( 'joe@example.org' );

		$this->assertEqualSets( array( 'joe@example.org', 'example@example.org' ), $email->get_emails() );
		$email->remove_email( 'example@example.org' );

		$this->assertEqualSets( array( 'joe@example.org' ), $email->get_emails() );

		$email->set_emails( array( 'amy@example.org' ) );
		$this->assertEqualSets( array( 'amy@example.org' ), $email->get_emails() );
	}

	public function test_save_emails() {

		$email = new IT_Exchange_Admin_Email_Notification( 'Test Admin Email', 'test-admin-email-2' );
		$email->set_emails( array() );
		$email->add_email( 'john@example.org' )->add_email( 'jane@example.org' )->save();

		$email = new IT_Exchange_Admin_Email_Notification( 'Test Admin Email', 'test-admin-email-2' );

		$this->assertEqualSets( array( 'john@example.org', 'jane@example.org' ), $email->get_emails() );
	}
}