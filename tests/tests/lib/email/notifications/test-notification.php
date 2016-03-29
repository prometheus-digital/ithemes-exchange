<?php
/**
 * Contains tests for the base notification class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Email_Notification
 *
 * @group emails
 */
class Test_IT_Exchange_Email_Notification extends IT_Exchange_UnitTestCase {

	public function test_defaults() {

		$subject = 'This is my subject';
		$body    = 'This is my body';

		$notification = $this->getMockForAbstractClass( 'IT_Exchange_Email_Notification', array(
			'name',
			'Name',
			null,
			array(
				'defaults' => array(
					'subject' => $subject,
					'body'    => $body,
					'active'  => false
				)
			)
		) );

		$this->assertEquals( $subject, $notification->get_subject() );
		$this->assertEquals( $body, $notification->get_body() );
		$this->assertFalse( $notification->is_active() );
	}

	public function test_crud() {

		$subject = 'Test subject';
		$body    = 'Test body';

		$notification = $this->getMockForAbstractClass( 'IT_Exchange_Email_Notification', array(
			'Test Crud',
			'test-crud'
		) );

		$notification->set_subject( $subject );
		$notification->set_body( $body );
		$notification->set_active( false );
		$notification->save();

		$notification = $this->getMockForAbstractClass( 'IT_Exchange_Email_Notification', array(
			'Test Crud',
			'test-crud'
		) );

		$this->assertEquals( $subject, $notification->get_subject() );
		$this->assertEquals( $body, $notification->get_body() );
		$this->assertFalse( $notification->is_active() );
	}
}