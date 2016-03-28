<?php
/**
 * Test the shortcode replacer class.
 *
 * @since    1.36
 * @license  GPLv2
 */

/**
 * Class Test_IT_Exchange_Email_Shortcode_Tag_Replacer
 *
 * @group emails
 */
class Test_IT_Exchange_Email_Shortcode_Tag_Replacer extends IT_Exchange_UnitTestCase {

	public function test_basic_replacement_without_context() {

		$content = 'Test [it_exchange_email show="test"]';

		$tag = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'render', 'get_tag' ) )
		            ->getMockForAbstractClass();
		$tag->method( 'get_tag' )->willReturn( 'test' );
		$tag->method( 'get_required_context' )->willReturn( array() );
		$tag->expects( $this->once() )->method( 'render' )->with( array() )->willReturn( 'content' );

		$replacer = new IT_Exchange_Email_Shortcode_Tag_Replacer();
		$replacer->add_tag( $tag );

		$this->assertEquals( 'Test content', $replacer->replace( $content, array() ) );
	}

	public function test_render_not_called_when_required_context_not_provided() {

		$content = 'Test [it_exchange_email show="test"]';

		$tag = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'render', 'get_tag' ) )
		            ->getMockForAbstractClass();
		$tag->method( 'get_tag' )->willReturn( 'test' );
		$tag->method( 'get_required_context' )->willReturn( array( 'customer' ) );
		$tag->expects( $this->never() )->method( 'render' );

		$replacer = new IT_Exchange_Email_Shortcode_Tag_Replacer();
		$replacer->add_tag( $tag );

		$this->assertEquals( 'Test ', $replacer->replace( $content, array() ) );
	}

	public function test_render_called_when_required_context_is_provided() {

		$content = 'Test [it_exchange_email show="test"]';

		$tag = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'render', 'get_tag' ) )
		            ->getMockForAbstractClass();
		$tag->method( 'get_tag' )->willReturn( 'test' );
		$tag->method( 'get_required_context' )->willReturn( array( 'dude' ) );
		$tag->expects( $this->once() )->method( 'render' )->with( array( 'dude' => 'bob' ) )->willReturn( 'content' );

		$replacer = new IT_Exchange_Email_Shortcode_Tag_Replacer();
		$replacer->add_tag( $tag );

		$this->assertEquals( 'Test content', $replacer->replace( $content, array( 'dude' => 'bob' ) ) );
	}

	public function test_render_called_when_extra_context_is_provided() {

		$content = 'Test [it_exchange_email show="test"]';
		$context = array( 'dude' => 'bob', 'a' => 'guy' );

		$tag = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'render', 'get_tag' ) )
		            ->getMockForAbstractClass();
		$tag->method( 'get_tag' )->willReturn( 'test' );
		$tag->method( 'get_required_context' )->willReturn( array( 'dude' ) );
		$tag->expects( $this->once() )->method( 'render' )->with( $context )->willReturn( 'content' );

		$replacer = new IT_Exchange_Email_Shortcode_Tag_Replacer();
		$replacer->add_tag( $tag );

		$this->assertEquals( 'Test content', $replacer->replace( $content, $context ) );
	}

	public function test_shortcode_options_passed_to_render_callback() {

		$content = 'Test [it_exchange_email show="test" options="verbose"]';

		$tag = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'render', 'get_tag' ) )
		            ->getMockForAbstractClass();
		$tag->method( 'get_tag' )->willReturn( 'test' );
		$tag->method( 'get_required_context' )->willReturn( array() );
		$tag->expects( $this->once() )->method( 'render' )->with( array(), array( 'verbose' ) )->willReturn( 'content' );

		$replacer = new IT_Exchange_Email_Shortcode_Tag_Replacer();
		$replacer->add_tag( $tag );

		$this->assertEquals( 'Test content', $replacer->replace( $content, array() ) );
	}

	public function test_extra_shortcode_atts_are_passed_as_options() {

		$content = 'Test [it_exchange_email show="test" options="verbose" before="<p>"]';

		$tag = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'render', 'get_tag' ) )
		            ->getMockForAbstractClass();
		$tag->method( 'get_tag' )->willReturn( 'test' );
		$tag->method( 'get_required_context' )->willReturn( array() );
		$tag->expects( $this->once() )->method( 'render' )->with( array(), array( 'verbose', 'before' => '<p>' ) )
		    ->willReturn( 'content' );

		$replacer = new IT_Exchange_Email_Shortcode_Tag_Replacer();
		$replacer->add_tag( $tag );

		$this->assertEquals( 'Test content', $replacer->replace( $content, array() ) );
	}

	public function test_deprecated_shortcode_functions() {

		add_filter( 'it_exchange_deprecated_filter_trigger_error', '__return_false' );

		$content = 'Test [it_exchange_email show="test"]';

		add_filter( 'it_exchange_email_notification_shortcode_functions', function ( $functions ) {
			$functions['test'] = function () {
				return 'content';
			};

			return $functions;
		} );

		$replacer = new IT_Exchange_Email_Shortcode_Tag_Replacer();

		$this->assertEquals( 'Test content', $replacer->replace( $content, array() ) );
	}

	public function test_back_compat_data_set_from_context() {

		$transaction = $this->transaction_factory->create_and_get();
		$customer    = it_exchange_get_customer( 1 );

		$replacer = new IT_Exchange_Email_Shortcode_Tag_Replacer();
		$replacer->replace( 'Content', array(
			'transaction' => $transaction,
			'customer'    => $customer
		) );

		$this->assertEquals( $transaction->ID, it_exchange_email_notifications()->transaction_id );
		$this->assertEquals( $customer->id, it_exchange_email_notifications()->customer_id );
		$this->assertEquals( $customer, it_exchange_email_notifications()->user );

		$this->assertEquals( $transaction, $GLOBALS['it_exchange']['email-confirmation-data'][0] );
		$this->assertEquals( it_exchange_email_notifications(), $GLOBALS['it_exchange']['email-confirmation-data'][1] );
	}
}