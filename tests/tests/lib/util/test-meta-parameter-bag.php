<?php
/**
 * Test the meta parameter bag.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_ITE_Meta_Parameter_Bag
 */
class Test_ITE_Meta_Parameter_Bag extends IT_Exchange_UnitTestCase {

	/** @var WP_Post */
	private static $post;

	/** @var ITE_Parameter_Bag */
	private static $bag;

	public static function setUpBeforeClass() {

		self::$post = self::factory()->post->create_and_get();
		self::$bag  = new ITE_Meta_Parameter_Bag( self::$post->ID, 'post', '_test_' );

		return parent::setUpBeforeClass();
	}

	public function test_set() {

		self::$bag->set_param( 'key', 'value' );
		self::assertEquals( 'value', get_post_meta( self::$post->ID, '_test_key', true ) );
		self::assertEquals( 'value', self::$bag->get_param( 'key' ) );
		self::assertTrue( self::$bag->has_param( 'key' ) );
	}

	/**
	 * @expectedException OutOfBoundsException
	 */
	public function test_get_throws_exception() {
		self::$bag->get_param( 'fake' );
	}

	public function test_remove() {

		self::$bag->set_param( 'key', 'value' );
		self::$bag->remove_param( 'key' );
		self::assertEmpty( get_post_meta( self::$post->ID, '_test_key', true ) );
		self::assertFalse( self::$bag->has_param( 'key' ) );
	}

	public function test_get_params() {
		self::$bag->set_param( 'key', 'value' );
		self::$bag->set_param( 'other', 'value' );

		self::assertEquals( array( 'key' => 'value', 'other' => 'value' ), self::$bag->get_params() );
	}

	public function test_get_params_excludes_other_meta() {
		self::$bag->set_param( 'key', 'value' );
		update_post_meta( self::$post->ID, 'fake', 'value' );

		self::assertEquals( array( 'key' => 'value' ), self::$bag->get_params() );
	}
}
