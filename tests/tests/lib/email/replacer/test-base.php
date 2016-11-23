<?php
/**
 * Test the base tag replacer.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Email_Tag_Replacer_Base
 *
 * @group emails
 */
class Test_IT_Exchange_Email_Tag_Replacer_Base extends IT_Exchange_UnitTestCase {

	public function test_add_get_tag() {

		$tag = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'get_tag' ) )->getMockForAbstractClass();
		$tag->method( 'get_tag' )->willReturn( 'a' );

		$replacer = $this->getMockForAbstractClass( 'IT_Exchange_Email_Tag_Replacer_Base' );
		$replacer->add_tag( $tag );

		$this->assertEquals( $tag, $replacer->get_tag( 'a' ) );
		$this->assertEquals( array( 'a' => $tag ), $replacer->get_tags() );
	}

	public function test_get_tags_for() {

		$notification = new IT_Exchange_Customer_Email_Notification( 'Test', 'test' );

		$a = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'get_tag', 'is_available_for' ) )
		          ->getMockForAbstractClass();
		$a->method( 'get_tag' )->willReturn( 'a' );
		$a->method( 'is_available_for' )->with( $notification )->willReturn( false );

		$b = $this->getMockBuilder( 'IT_Exchange_Email_Tag' )->setMethods( array( 'get_tag', 'is_available_for' ) )
		          ->getMockForAbstractClass();
		$b->method( 'get_tag' )->willReturn( 'b' );
		$b->method( 'is_available_for' )->with( $notification )->willReturn( true );

		$replacer = $this->getMockForAbstractClass( 'IT_Exchange_Email_Tag_Replacer_Base' );
		$replacer->add_tag( $a );
		$replacer->add_tag( $b );

		$this->assertEquals( array( $b ), $replacer->get_tags_for( $notification ) );
	}
}
