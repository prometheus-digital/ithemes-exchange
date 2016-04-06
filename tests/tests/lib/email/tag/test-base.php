<?php
/**
 * Contains tests for the base email tag.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Email_Tag_Base
 *
 * @group emails
 */
class Test_IT_Exchange_Email_Tag_Base extends IT_Exchange_UnitTestCase {

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_invalid_tag() {
		new IT_Exchange_Email_Tag_Base( array(), 'Name', 'Description', function () {
		} );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_invalid_name() {
		new IT_Exchange_Email_Tag_Base( 'tag', array(), 'Description', function () {
		} );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_invalid_description() {
		new IT_Exchange_Email_Tag_Base( 'tag', 'Name', array(), function () {
		} );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_invalid_render() {
		new IT_Exchange_Email_Tag_Base( 'tag', 'Name', array(), array() );
	}

	public function test_add_required_context() {

		$tag = new IT_Exchange_Email_Tag_Base( 'tag', 'Tag', 'Description', function () {
		} );

		$tag->add_required_context( 'customer' )->add_required_context( 'customer' );
		$this->assertEquals( array( 'customer' ), $tag->get_required_context() );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_add_required_context_invalid_context() {

		$tag = new IT_Exchange_Email_Tag_Base( 'tag', 'Tag', 'Description', function () {
		} );

		$tag->add_required_context( new stdClass() );
	}

	public function test_availability() {

		$tag = new IT_Exchange_Email_Tag_Base( 'tag', 'Tag', 'Description', function () {
		} );

		$tag->add_available_for( 'test' );
		$tag->add_not_available_for( 'not-available' );

		$this->assertTrue( $tag->is_available_for( new IT_Exchange_Customer_Email_Notification( 'Test', 'test' ) ) );
		$this->assertFalse( $tag->is_available_for( new IT_Exchange_Customer_Email_Notification( 'Fake', 'fake' ) ) );
		$this->assertFalse( $tag->is_available_for( new IT_Exchange_Customer_Email_Notification( 'Not Available', 'not-available' ) ) );
	}

	public function test_not_available_only() {

		$tag = new IT_Exchange_Email_Tag_Base( 'tag', 'Tag', 'Description', function () {
		} );

		$tag->add_not_available_for( 'not-available' );

		$this->assertTrue( $tag->is_available_for( new IT_Exchange_Customer_Email_Notification( 'Test', 'test' ) ) );
		$this->assertFalse( $tag->is_available_for( new IT_Exchange_Customer_Email_Notification( 'Not Available', 'not-available' ) ) );
	}

	public function test_render() {

		$tag = new IT_Exchange_Email_Tag_Base( 'tag', 'Tag', 'Description', function () {
			return 'test';
		} );

		$this->assertEquals( 'test', $tag->render( array() ) );
	}


}