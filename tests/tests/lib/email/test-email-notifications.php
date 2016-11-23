<?php
/**
 * Test the email notifications class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Email_Notifications
 *
 * @group emails
 */
class Test_IT_Exchange_Email_Notifications extends IT_Exchange_UnitTestCase {

	public function test_register_and_retrieve() {

		$notifications = it_exchange_email_notifications();

		$id           = uniqid();
		$notification = new IT_Exchange_Customer_Email_Notification( $id, $id );
		$notifications->register_notification( $notification );

		$this->assertEquals( $notification, $notifications->get_notification( $id ) );
	}

	public function test_get_notification_returns_null_by_default() {
		$this->assertNull( it_exchange_email_notifications()->get_notification( 'i-dont-exist' ) );
	}

	public function test_get_groups() {

		$notifications = it_exchange_email_notifications();

		$n1 = new IT_Exchange_Customer_Email_Notification( uniqid(), uniqid(), null, array( 'group' => 'C' ) );
		$n2 = new IT_Exchange_Customer_Email_Notification( uniqid(), uniqid(), null, array( 'group' => 'A' ) );
		$n3 = new IT_Exchange_Customer_Email_Notification( uniqid(), uniqid(), null, array( 'group' => 'B' ) );
		$n4 = new IT_Exchange_Customer_Email_Notification( uniqid(), uniqid(), null, array( 'group' => 'A' ) );

		$notifications->register_notification( $n1 )->register_notification( $n2 )
		              ->register_notification( $n3 )->register_notification( $n4 );

		$this->assertEquals( array( 'A', 'B', 'C', 'Core' ), $notifications->get_groups() );
	}

	public function test_get_replacer() {
		$this->assertInstanceOf( 'IT_Exchange_Email_Tag_Replacer', it_exchange_email_notifications()->get_replacer() );
	}

	public function test_legacy_send_email_notification() {

		$_sender = it_exchange_email_notifications()->get_sender();

		$txn = $this->transaction_factory->create();

		$sender = $this->getMockBuilder( 'IT_Exchange_Email_Sender' )->setMethods( array( 'send' ) )->getMockForAbstractClass();
		$sender->method( 'send' )->with( $this->callback( function ( $email ) use ( $txn ) {

			if ( ! $email instanceof IT_Exchange_Simple_Email ) {
				return false;
			}

			if ( $email->get_subject() !== 'Subject' ) {
				return false;
			}

			if ( $email->get_body() !== 'Body' ) {
				return false;
			}

			if ( $email->get_recipient()->get_email() !== get_user_by( 'id', 1 )->user_email ) {
				return false;
			}

			$context = $email->get_context();

			if ( ! isset( $context['customer'] ) || ! isset( $context['transaction'] ) ) {
				return false;
			}

			if ( ! $context['customer'] instanceof IT_Exchange_Customer || $context['customer']->id != 1 ) {
				return false;
			}

			if ( ! $context['transaction'] instanceof IT_Exchange_Transaction || $context['transaction']->get_ID() != $txn ) {
				return false;
			}

			return true;
		} ) )->willReturn( true );

		do_action( 'it_exchange_send_email_notification', 1, 'Subject', 'Body', $txn );

		it_exchange_email_notifications()->set_sender( $_sender );
	}
}
