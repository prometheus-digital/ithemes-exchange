<?php
/**
 * Test the cart object converter class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Upgrades_Cart_Object_Converter
 *
 * @group upgrade-routines
 */
class Test_IT_Exchange_Upgrades_Cart_Object_Converter extends IT_Exchange_UnitTestCase {

	/** @var stdClass */
	protected static $cartObjects;

	/** @var ITE_Line_Item_Transaction_Object_Converter */
	protected static $converter;

	public static function setUpBeforeClass() {

		$decoded = json_decode( file_get_contents( dirname( __FILE__ ) . '/../../data/cart-object-converter.json' ), true );

		foreach ( $decoded as $test => $object ) {
			// cart objects are only objects as far as the first level
			self::$cartObjects[ $test ] = (object) $object;
		}

		self::$converter = new ITE_Line_Item_Transaction_Object_Converter();

		return parent::setUpBeforeClass();
	}

	public function test_single_shipping_method_single_product() {

		$post        = self::factory()->post->create( array( 'post_type' => 'it_exchange_tran' ) );
		$cart_object = self::$cartObjects['singleShippingMethodSingleProduct'];
		$transaction = $this->getMockBuilder( 'IT_Exchange_Transaction' )->setMethods( array( 'get_ID' ) )->getMock();
		$transaction->expects( $this->any() )->method( 'get_ID' )->willReturn( $post );

		self::$converter->convert( $cart_object, $transaction );

		$repo  = new ITE_Line_Item_Transaction_Repository( new ITE_Line_Item_Repository_Events(), $transaction );
		$items = $repo->all();

		$this->assertCount( 1, $items->with_only( 'product' ) );

		/** @var ITE_Cart_Product $p1 */
		$p1 = $items->get( 'product', '8-40cd750bba9870f18aada2478b24840a' );
		$this->assertNotNull( $p1 );
		$this->assertEquals( 15, $p1->get_total() );
		$this->assertEquals( 1, $p1->get_quantity() );
		$this->assertEquals( 'Physical Product', $p1->get_name() );

		$this->assertCount( 1, $p1->get_line_items() );

		/** @var ITE_Shipping_Line_Item $ps1 */
		$ps1 = $p1->get_line_items()->with_only( 'shipping' )->first();
		$this->assertNotNull( $ps1 );
		$this->assertEquals( 'exchange-flat-rate-shipping', $ps1->get_method_slug() );
		$this->assertEquals( 5.00, $ps1->get_total() );

		/** @var ITE_Shipping_Line_Item $global */
		$global = $items->with_only( 'shipping' )->first();
		$this->assertNotNull( $global );
		$this->assertEquals( 'exchange-flat-rate-shipping', $global->get_method_slug() );
	}

	public function test_single_shipping_method_multiple_products() {

		$post        = self::factory()->post->create( array( 'post_type' => 'it_exchange_tran' ) );
		$cart_object = self::$cartObjects['singleShippingMethodMultipleProduct'];
		$transaction = $this->getMockBuilder( 'IT_Exchange_Transaction' )->setMethods( array( 'get_ID' ) )->getMock();
		$transaction->expects( $this->any() )->method( 'get_ID' )->willReturn( $post );

		self::$converter->convert( $cart_object, $transaction );

		$repo  = new ITE_Line_Item_Transaction_Repository( new ITE_Line_Item_Repository_Events(), $transaction );
		$items = $repo->all();

		$this->assertCount( 2, $items->with_only( 'product' ) );

		/** @var ITE_Cart_Product $p1 */
		$p1 = $items->get( 'product', '8-40cd750bba9870f18aada2478b24840a' );
		$this->assertNotNull( $p1 );
		$this->assertEquals( 15, $p1->get_total() );
		$this->assertEquals( 1, $p1->get_quantity() );
		$this->assertEquals( 'Physical Product', $p1->get_name() );

		$this->assertCount( 1, $p1->get_line_items() );
		/** @var ITE_Shipping_Line_Item $ps1 */
		$ps1 = $p1->get_line_items()->with_only( 'shipping' )->first();
		$this->assertNotNull( $ps1 );
		$this->assertEquals( 'exchange-flat-rate-shipping', $ps1->get_method_slug() );
		$this->assertEquals( 4.68, $ps1->get_total(), '', .1 );

		/** @var ITE_Cart_Product $p2 */
		$p2 = $items->get( 'product', '4-40cd750bba9870f18aada2478b24840a' );
		$this->assertNotNull( $p2 );
		$this->assertEquals( 1, $p2->get_total() );
		$this->assertEquals( 1, $p2->get_quantity() );
		$this->assertEquals( 'My Sample Product', $p2->get_name() );

		$this->assertCount( 1, $p2->get_line_items() );
		/** @var ITE_Shipping_Line_Item $ps2 */
		$ps2 = $p2->get_line_items()->with_only( 'shipping' )->first();
		$this->assertNotNull( $ps2 );
		$this->assertEquals( 'exchange-flat-rate-shipping', $ps2->get_method_slug() );
		$this->assertEquals( .32, $ps2->get_total(), '', .1 );

		/** @var ITE_Shipping_Line_Item $global */
		$global = $items->with_only( 'shipping' )->first();
		$this->assertNotNull( $global );
		$this->assertEquals( 'exchange-flat-rate-shipping', $global->get_method_slug() );
	}

	public function test_multiple_shipping_methods() {

		$post        = self::factory()->post->create( array( 'post_type' => 'it_exchange_tran' ) );
		$cart_object = self::$cartObjects['multipleShippingMethods'];
		$transaction = $this->getMockBuilder( 'IT_Exchange_Transaction' )->setMethods( array( 'get_ID' ) )->getMock();
		$transaction->expects( $this->any() )->method( 'get_ID' )->willReturn( $post );

		self::$converter->convert( $cart_object, $transaction );

		$repo  = new ITE_Line_Item_Transaction_Repository( new ITE_Line_Item_Repository_Events(), $transaction );
		$items = $repo->all();

		$this->assertCount( 2, $items->with_only( 'product' ) );

		/** @var ITE_Cart_Product $p1 */
		$p1 = $items->get( 'product', '8-40cd750bba9870f18aada2478b24840a' );
		$this->assertNotNull( $p1 );
		$this->assertEquals( 15, $p1->get_total() );
		$this->assertEquals( 1, $p1->get_quantity() );
		$this->assertEquals( 'Physical Product', $p1->get_name() );

		$this->assertCount( 1, $p1->get_line_items() );
		/** @var ITE_Shipping_Line_Item $ps1 */
		$ps1 = $p1->get_line_items()->with_only( 'shipping' )->first();
		$this->assertNotNull( $ps1 );
		$this->assertEquals( 'exchange-free-shipping', $ps1->get_method_slug() );
		$this->assertEquals( 0, $ps1->get_total() );

		/** @var ITE_Cart_Product $p2 */
		$p2 = $items->get( 'product', '4-40cd750bba9870f18aada2478b24840a' );
		$this->assertNotNull( $p2 );
		$this->assertEquals( 1, $p2->get_total() );
		$this->assertEquals( 1, $p2->get_quantity() );
		$this->assertEquals( 'My Sample Product', $p2->get_name() );

		$this->assertCount( 1, $p2->get_line_items() );
		/** @var ITE_Shipping_Line_Item $ps2 */
		$ps2 = $p2->get_line_items()->with_only( 'shipping' )->first();
		$this->assertNotNull( $ps2 );
		$this->assertEquals( 'exchange-flat-rate-shipping', $ps2->get_method_slug() );
		$this->assertEquals( 5.0, $ps2->get_total() );

		$globals = $items->with_only( 'shipping' )->to_array();
		$this->assertCount( 2, $globals );
		$slugs = array_map( function ( ITE_Shipping_Line_Item $shipping ) { return $shipping->get_method_slug(); }, $globals );
		$this->assertEqualSets( array( 'exchange-flat-rate-shipping', 'exchange-free-shipping' ), $slugs );
	}

}