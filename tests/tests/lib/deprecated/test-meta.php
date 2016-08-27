<?php
/**
 * Test Deprecated Meta handler.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Deprecated_Meta
 */
class Test_IT_Exchange_Deprecated_Meta extends IT_Exchange_UnitTestCase {

	/** @var WP_Post */
	private static $post;

	/**
	 * @inheritDoc
	 */
	public function setUp() {
		parent::setUp();

		self::$post = self::factory()->post->create_and_get();
	}

	public function test_wp_query_meta_query_top_level() {

		add_post_meta( self::$post->ID, 'new_key', 'my_value' );

		$deprecated = new IT_Exchange_Deprecated_Meta();
		$deprecated->add( 'old_key', 'new_key', '1.36.0' );

		$query = new WP_Query( array(
			'meta_key'   => 'old_key',
			'meta_value' => 'my_value'
		) );

		self::assertNotContains( 'old_key', $query->request );
		self::assertContains( 'new_key', $query->request );

		$posts = $query->get_posts();
		$post  = reset( $posts );

		self::assertEquals( 1, count( $posts ) );
		self::assertEquals( self::$post->ID, $post->ID );
	}

	public function test_wp_query_meta_query_simple() {

		add_post_meta( self::$post->ID, 'new_key', 'my_value' );

		$deprecated = new IT_Exchange_Deprecated_Meta();
		$deprecated->add( 'old_key', 'new_key', '1.36.0' );

		$query = new WP_Query( array(
			'meta_query' => array(
				array(
					'key'   => 'old_key',
					'value' => 'my_value'
				)
			)
		) );

		self::assertNotContains( 'old_key', $query->request );

		$posts = $query->get_posts();
		$post  = reset( $posts );

		self::assertEquals( 1, count( $posts ) );
		self::assertEquals( self::$post->ID, $post->ID );
	}

	public function test_wp_query_meta_query_doesnt_effect_non_deprecated() {

		add_post_meta( self::$post->ID, 'new_key', 'my_value' );

		$other = self::factory()->post->create_and_get();
		add_post_meta( $other->ID, 'custom', 'value' );

		$deprecated = new IT_Exchange_Deprecated_Meta();
		$deprecated->add( 'old_key', 'new_key', '1.36.0' );

		$query = new WP_Query( array(
			'meta_query' => array(
				array(
					'key'   => 'custom',
					'value' => 'value'
				)
			)
		) );

		self::assertNotContains( 'new_key', $query->request );

		$posts = $query->get_posts();
		$post  = reset( $posts );

		self::assertEquals( 1, count( $posts ) );
		self::assertEquals( $other->ID, $post->ID );
	}

	public function test_wp_query_meta_query_multi_dimensional() {

		add_post_meta( self::$post->ID, 'new_key', 'my_value' );
		add_post_meta( self::$post->ID, 'favorite_day', 'Monday' );

		add_post_meta( self::factory()->post->create(), 'favorite_day', 'Monday' );

		$deprecated = new IT_Exchange_Deprecated_Meta();
		$deprecated->add( 'old_key', 'new_key', '1.36.0' );

		$query = new WP_Query( array(
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'   => 'favorite_day',
					'value' => 'Monday'
				),
				array(
					'relation' => 'OR',
					array(
						'key'   => 'old_key',
						'value' => 'my_value'
					),
					array(
						'key'   => 'favorite_color',
						'value' => 'blue'
					)
				)
			)
		) );

		self::assertContains( 'favorite_day', $query->request );
		self::assertContains( 'favorite_color', $query->request );
		self::assertContains( 'new_key', $query->request );
		self::assertNotContains( 'old_key', $query->request );

		$posts = $query->get_posts();
		$post  = reset( $posts );

		self::assertEquals( 1, count( $posts ) );
		self::assertEquals( self::$post->ID, $post->ID );
	}

	/**
	 * @depends            test_wp_query_meta_query_top_level
	 * @expectedDeprecated old_key
	 */
	public function test_wp_query_meta_query_top_level_warn() {

		add_post_meta( self::$post->ID, 'new_key', 'my_value' );

		$deprecated = new IT_Exchange_Deprecated_Meta();
		$deprecated->add( 'old_key', 'new_key', '1.36.0', true );

		$query = new WP_Query( array(
			'meta_key'   => 'old_key',
			'meta_value' => 'my_value'
		) );

		$query->get_posts();
	}

	/**
	 * @depends            test_wp_query_meta_query_simple
	 * @expectedDeprecated old_key
	 */
	public function test_wp_query_meta_query_warn() {

		add_post_meta( self::$post->ID, 'new_key', 'my_value' );

		$deprecated = new IT_Exchange_Deprecated_Meta();
		$deprecated->add( 'old_key', 'new_key', '1.36.0', true );

		$query = new WP_Query( array(
			'meta_query' => array(
				array(
					'key'   => 'old_key',
					'value' => 'my_value'
				)
			)
		) );

		$query->get_posts();
	}

	/**
	 * @expectedDeprecated old_key
	 */
	public function test_add_meta() {

		$deprecated = new IT_Exchange_Deprecated_Meta();
		$deprecated->add( 'old_key', 'new_key', '1.36.0', true );

		add_post_meta( self::$post->ID, 'old_key', 'my_value' );

		self::assertEquals( 'my_value', get_post_meta( self::$post->ID, 'new_key', true ) );
	}

	/**
	 * @expectedDeprecated old_key
	 */
	public function test_update_meta() {

		$deprecated = new IT_Exchange_Deprecated_Meta();
		$deprecated->add( 'old_key', 'new_key', '1.36.0', true );

		update_post_meta( self::$post->ID, 'old_key', 'my_value' );

		self::assertEquals( 'my_value', get_post_meta( self::$post->ID, 'new_key', true ) );
	}

	/**
	 * @expectedDeprecated old_key
	 */
	public function test_delete_meta() {

		$deprecated = new IT_Exchange_Deprecated_Meta();
		$deprecated->add( 'old_key', 'new_key', '1.36.0', true );

		delete_post_meta( self::$post->ID, 'old_key' );

		self::assertEquals( '', get_post_meta( self::$post->ID, 'new_key', true ) );
	}

	/**
	 * @expectedDeprecated old_key
	 */
	public function test_get_meta() {

		$deprecated = new IT_Exchange_Deprecated_Meta();
		$deprecated->add( 'old_key', 'new_key', '1.36.0', true );

		add_post_meta( self::$post->ID, 'new_key', 'my_value' );

		self::assertEquals( 'my_value', get_post_meta( self::$post->ID, 'old_key', true ) );
	}
}