<?php
/**
 * Test the wp_mail() sender.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Lib_Email_Sender_WP_Mail
 *
 * @group emails
 */
class Test_IT_Exchange_Lib_Email_Sender_WP_Mail extends IT_Exchange_UnitTestCase {

	public function test_send_email() {

		add_filter( 'it_exchange_get_option-settings_email', function ( $data ) {
			$data['receipt-email-name']    = 'Test Store';
			$data['receipt-email-address'] = 'exchange@example.org';

			return $data;
		} );

		$recipient = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient', array( 'get_email' ) );
		$recipient->method( 'get_email' )->willReturn( 'example@example.org' );

		$template = $this->getMockBuilder( 'IT_Exchange_Email_Template' )->disableOriginalConstructor()
		                 ->setMethods( array( 'get_html' ) )->getMock();
		$template->method( 'get_html' )->with( array(
			'message' => "Body",
			'extra'   => 'details',
		) )->willReturnCallback( function ( $context ) {
			return $context['message'];
		} );
		$cc = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient', array( 'get_email', 'get_full_name' ) );
		$cc->method( 'get_email' )->willReturn( 'cc@example.org' );
		$cc->method( 'get_full_name' )->willReturn( 'Some Guy' );

		$bcc = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient', array( 'get_email', 'get_full_name' ) );
		$bcc->method( 'get_email' )->willReturn( 'bcc@example.org' );
		$bcc->method( 'get_full_name' )->willReturn( 'Some Girl' );

		$sendable = $this->getMock( 'IT_Exchange_Sendable' );
		$sendable->method( 'get_subject' )->willReturn( 'Subject' );
		$sendable->method( 'get_body' )->willReturn( 'Body' );
		$sendable->method( 'get_recipient' )->willReturn( $recipient );
		$sendable->method( 'get_template' )->willReturn( $template );
		$sendable->method( 'get_context' )->willReturn( array( 'extra' => 'details' ) );
		$sendable->method( 'get_ccs' )->willReturn( array( $cc ) );
		$sendable->method( 'get_bccs' )->willReturn( array( $bcc ) );


		WP_Mock::wpFunction( 'wp_mail', array(
			'times' => 1,
			'args'  => array(
				'example@example.org',
				'Subject',
				"Body",
				array(
					'From: Test Store <exchange@example.org>',
					'MIME-Version: 1.0',
					'Content-Type: text/html',
					'charset=utf-8',
					'Cc: Some Guy <cc@example.org>',
					'Bcc: Some Girl <bcc@example.org>'
				)
			)
		) );

		$sender = new IT_Exchange_WP_Mail_Sender( new IT_Exchange_Email_Middleware_Handler() );
		$sender->send( $sendable );
	}

}