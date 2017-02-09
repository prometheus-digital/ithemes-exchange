<?php
/**
 * Test the curly replacer class.
 *
 * @since    2.0.0
 * @license  GPLv2
 */

/**
 * Class Test_IT_Exchange_Email_Curly_Tag_Replacer
 *
 * @group emails
 */
class Test_IT_Exchange_Email_Curly_Tag_Replacer extends IT_Exchange_UnitTestCase {

	public function test_basic_replacement_without_context() {

		$content = 'Test {{test}}';

		$tag = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'render', 'get_tag' ) )
		            ->getMockForAbstractClass();
		$tag->method( 'get_tag' )->willReturn( 'test' );
		$tag->method( 'get_required_context' )->willReturn( array() );
		$tag->expects( $this->once() )->method( 'render' )->with( array() )->willReturn( 'content' );

		$replacer = new IT_Exchange_Email_Curly_Tag_Replacer();
		$replacer->add_tag( $tag );

		$this->assertEquals( 'Test content', $replacer->replace( $content, array() ) );
	}

	public function test_render_not_called_when_required_context_not_provided() {

		$content = 'Test {{test}}';

		$tag = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'render', 'get_tag' ) )
		            ->getMockForAbstractClass();
		$tag->method( 'get_tag' )->willReturn( 'test' );
		$tag->method( 'get_required_context' )->willReturn( array( 'customer' ) );
		$tag->expects( $this->never() )->method( 'render' );

		$replacer = new IT_Exchange_Email_Curly_Tag_Replacer();
		$replacer->add_tag( $tag );

		$this->assertEquals( 'Test ', $replacer->replace( $content, array() ) );
	}

	public function test_render_called_when_required_context_is_provided() {

		$content = 'Test {{test}}';

		$tag = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'render', 'get_tag' ) )
		            ->getMockForAbstractClass();
		$tag->method( 'get_tag' )->willReturn( 'test' );
		$tag->method( 'get_required_context' )->willReturn( array( 'dude' ) );
		$tag->expects( $this->once() )->method( 'render' )->with( array( 'dude' => 'bob' ) )->willReturn( 'content' );

		$replacer = new IT_Exchange_Email_Curly_Tag_Replacer();
		$replacer->add_tag( $tag );

		$this->assertEquals( 'Test content', $replacer->replace( $content, array( 'dude' => 'bob' ) ) );
	}

	public function test_render_called_when_extra_context_is_provided() {

		$content = 'Test {{test}}';
		$context = array( 'dude' => 'bob', 'a' => 'guy' );

		$tag = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'render', 'get_tag' ) )
		            ->getMockForAbstractClass();
		$tag->method( 'get_tag' )->willReturn( 'test' );
		$tag->method( 'get_required_context' )->willReturn( array( 'dude' ) );
		$tag->expects( $this->once() )->method( 'render' )->with( $context )->willReturn( 'content' );

		$replacer = new IT_Exchange_Email_Curly_Tag_Replacer();
		$replacer->add_tag( $tag );

		$this->assertEquals( 'Test content', $replacer->replace( $content, $context ) );
	}

	public function test_shortcode_options_passed_to_render_callback() {

		$content = 'Test {{test:verbose}}';

		$tag = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'render', 'get_tag' ) )
		            ->getMockForAbstractClass();
		$tag->method( 'get_tag' )->willReturn( 'test' );
		$tag->method( 'get_required_context' )->willReturn( array() );
		$tag->expects( $this->once() )->method( 'render' )->with( array(), array( 'verbose' ) )->willReturn( 'content' );

		$replacer = new IT_Exchange_Email_Curly_Tag_Replacer();
		$replacer->add_tag( $tag );

		$this->assertEquals( 'Test content', $replacer->replace( $content, array() ) );
	}

	/**
	 * @expectedDeprecated it_exchange_email_notification_shortcode_functions
	 */
	public function test_deprecated_shortcode_functions() {

		$content = 'Test {{test}}';

		add_filter( 'it_exchange_email_notification_shortcode_functions', function ( $functions ) {
			$functions['test'] = function () {
				return 'content';
			};

			return $functions;
		} );

		$replacer = new IT_Exchange_Email_Curly_Tag_Replacer();

		$this->assertEquals( 'Test content', $replacer->replace( $content, array() ) );
	}

	public function test_back_compat_data_set_from_context() {

		$transaction = $this->transaction_factory->create_and_get();
		$customer    = it_exchange_get_customer( 1 );

		$replacer = new IT_Exchange_Email_Curly_Tag_Replacer();
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

	public function test_replacement_map() {

		$context = array( 'key' => 'val' );

		$tag1 = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'render', 'get_tag' ) )
		             ->getMockForAbstractClass();
		$tag1->method( 'get_tag' )->willReturn( 'test1' );
		$tag1->method( 'get_required_context' )->willReturn( array() );
		$tag1->expects( $this->once() )->method( 'render' )->with( $context )->willReturn( 'content1' );

		$tag2 = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'render', 'get_tag' ) )
		             ->getMockForAbstractClass();
		$tag2->method( 'get_tag' )->willReturn( 'test2' );
		$tag2->method( 'get_required_context' )->willReturn( array() );
		$tag2->expects( $this->once() )->method( 'render' )->with( $context )->willReturn( 'content2' );

		$replacer = new IT_Exchange_Email_Curly_Tag_Replacer();
		$replacer->add_tag( $tag1 )->add_tag( $tag2 );
		$map = $replacer->get_replacement_map( 'This is a {{test1}} with {{test2}}.', $context );

		$this->assertEquals( array( 'test1' => 'content1', 'test2' => 'content2' ), $map );
	}

	public function test_transform_tags_to_format() {

		$content = 'This is a {{test1}} with {{test2}} and {{test1}}';

		$replacer    = new IT_Exchange_Email_Curly_Tag_Replacer();
		$transformed = $replacer->transform_tags_to_format( '*|', '|*', $content );

		$this->assertEquals( 'This is a *|test1|* with *|test2|* and *|test1|*', $transformed );
	}
}
