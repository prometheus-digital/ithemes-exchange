<?php
/**
 * Test the Postmark Sender.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Email_Postmark_Sender
 */
class Test_IT_Exchange_Email_Postmark_Sender extends IT_Exchange_UnitTestCase {

	public function test_send() {

		add_filter( 'it_exchange_get_option-settings_email', function ( $data ) {
			$data['receipt-email-name']    = 'Test Store';
			$data['receipt-email-address'] = 'exchange@example.org';

			return $data;
		} );

		$template = $this->getMockBuilder( 'IT_Exchange_Email_Template' )->disableOriginalConstructor()
		                 ->setMethods( array( 'get_html' ) )->getMock();
		$template->method( 'get_html' )->willReturnCallback( function ( $args ) {
			return $args['message'];
		} );

		$http = $this->getMock( 'WP_Http' );
		$http->method( 'post' )->with( IT_Exchange_Email_Postmark_Sender::URL . 'email', array(
			'reject_unsafe_urls' => true,
			'headers'            => array(
				'Content-Type'            => 'application/json',
				'Accept'                  => 'application/json',
				'X-Postmark-Server-Token' => 'token'
			),
			'body'               => wp_json_encode( array(
				'From'     => 'exchange@example.org',
				'To'       => 'to@example.org',
				'Subject'  => 'Subject',
				'HtmlBody' => 'Body',
				'Cc'       => 'cc@example.org',
				'Bcc'      => 'bcc@example.org',
				'TrackOpens' => true
			) )
		) );

		$to = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$to->method( 'get_email' )->willReturn( 'to@example.org' );

		$cc = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$cc->method( 'get_email' )->willReturn( 'cc@example.org' );

		$bcc = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$bcc->method( 'get_email' )->willReturn( 'bcc@example.org' );

		$sendable = $this->getMockForAbstractClass( 'IT_Exchange_Sendable' );
		$sendable->method( 'get_subject' )->willReturn( 'Subject' );
		$sendable->method( 'get_body' )->willReturn( 'Body' );
		$sendable->method( 'get_recipient' )->willReturn( $to );
		$sendable->method( 'get_ccs' )->willReturn( array( $cc ) );
		$sendable->method( 'get_bccs' )->willReturn( array( $bcc ) );
		$sendable->method( 'get_template' )->willReturn( $template );
		$sendable->method( 'get_context' )->willReturn( array() );

		$handler = $this->getMock( 'IT_Exchange_Email_Middleware_Handler' );
		$handler->expects( $this->once() )->method( 'handle' )->with( $this->callback( function ( $mutable ) use ( $sendable ) {

			if ( ! $mutable instanceof IT_Exchange_Sendable_Mutable_Wrapper ) {
				return false;
			}

			return $mutable->get_original() === $sendable;
		} ) )->willReturn( true );

		$sender = new IT_Exchange_Email_Postmark_Sender( $handler, $http, array(
			'server-token' => 'token'
		) );
		$sender->send( $sendable );
	}
	
}