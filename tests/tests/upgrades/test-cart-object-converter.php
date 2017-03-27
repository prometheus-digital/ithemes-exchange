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

	public function test_coupon_single_product() {

		$post        = self::factory()->post->create( array( 'post_type' => 'it_exchange_tran' ) );
		$cart_object = self::$cartObjects['couponSingleProduct'];
		$transaction = $this->getMockBuilder( 'IT_Exchange_Transaction' )->setMethods( array( 'get_ID' ) )->getMock();
		$transaction->expects( $this->any() )->method( 'get_ID' )->willReturn( $post );

		self::$converter->convert( $cart_object, $transaction );

		$repo  = new ITE_Line_Item_Transaction_Repository( new ITE_Line_Item_Repository_Events(), $transaction );
		$items = $repo->all();

		$this->assertCount( 1, $items->with_only( 'product' ) );
		/** @var ITE_Cart_Product $product */
		$product = $items->get( 'product', '8-40cd750bba9870f18aada2478b24840a' );
		$this->assertNotNull( $product );

		$this->assertCount( 1, $items->with_only( 'coupon' ), 'Global coupon item added' );
		$this->assertCount( 1, $product->get_line_items()->with_only( 'coupon' ), 'Product coupon item added.' );

		/** @var ITE_Coupon_Line_Item $global */
		$global = $items->with_only( 'coupon' )->first();

		/** @var ITE_Coupon_Line_Item $item */
		$item = $product->get_line_items()->with_only( 'coupon' )->first();

		$this->assertEquals( 'Savings', $global->get_name() );
		$this->assertEquals( '5OFF', $global->get_description() );
		$this->assertEquals( '5OFF', $global->get_param( 'code' ) );
		$this->assertEquals( '%', $global->get_param( 'amount_type' ) );
		$this->assertTrue( $global->has_param( 'start_date' ) );
		$this->assertTrue( $global->has_param( 'end_date' ) );
		$this->assertEquals( 0, $global->get_total() );

		$this->assertEquals( 'Savings', $item->get_name() );
		$this->assertEquals( '5OFF', $item->get_description() );
		$this->assertEquals( '5OFF', $item->get_param( 'code' ) );
		$this->assertEquals( '%', $item->get_param( 'amount_type' ) );
		$this->assertTrue( $item->has_param( 'start_date' ) );
		$this->assertTrue( $item->has_param( 'end_date' ) );
		$this->assertEquals( - 1.25, $item->get_total() );
	}

	public function test_coupon_multiple_products() {

		$post        = self::factory()->post->create( array( 'post_type' => 'it_exchange_tran' ) );
		$cart_object = self::$cartObjects['couponMultipleProducts'];
		$transaction = $this->getMockBuilder( 'IT_Exchange_Transaction' )->setMethods( array( 'get_ID' ) )->getMock();
		$transaction->expects( $this->any() )->method( 'get_ID' )->willReturn( $post );

		self::$converter->convert( $cart_object, $transaction );

		$repo  = new ITE_Line_Item_Transaction_Repository( new ITE_Line_Item_Repository_Events(), $transaction );
		$items = $repo->all();

		$this->assertCount( 2, $items->with_only( 'product' ), 'Product count correct' );

		/** @var ITE_Cart_Product $p1 */
		$p1 = $items->get( 'product', '8-40cd750bba9870f18aada2478b24840a' );
		$this->assertNotNull( $p1 );

		/** @var ITE_Cart_Product $p2 */
		$p2 = $items->get( 'product', '4-40cd750bba9870f18aada2478b24840a' );
		$this->assertNotNull( $p2 );

		$this->assertCount( 1, $items->with_only( 'coupon' ), 'Global coupon item added' );
		$this->assertCount( 1, $p1->get_line_items()->with_only( 'coupon' ), 'Product coupon item added.' );
		$this->assertCount( 1, $p2->get_line_items()->with_only( 'coupon' ), 'Product coupon item added.' );

		/** @var ITE_Coupon_Line_Item $global */
		$global = $items->with_only( 'coupon' )->first();

		/** @var ITE_Coupon_Line_Item $c1 */
		$c1 = $p1->get_line_items()->with_only( 'coupon' )->first();

		/** @var ITE_Coupon_Line_Item $c2 */
		$c2 = $p2->get_line_items()->with_only( 'coupon' )->first();

		$this->assertEquals( 'Savings', $global->get_name() );
		$this->assertEquals( '10OFF', $global->get_description() );
		$this->assertEquals( '10OFF', $global->get_param( 'code' ) );
		$this->assertEquals( '%', $global->get_param( 'amount_type' ) );
		$this->assertTrue( $global->has_param( 'start_date' ) );
		$this->assertTrue( $global->has_param( 'end_date' ) );
		$this->assertEquals( 0, $global->get_total() );

		$this->assertEquals( 'Savings', $c1->get_name() );
		$this->assertEquals( '10OFF', $c1->get_description() );
		$this->assertEquals( '10OFF', $c1->get_param( 'code' ) );
		$this->assertEquals( '%', $c1->get_param( 'amount_type' ) );
		$this->assertTrue( $c1->has_param( 'start_date' ) );
		$this->assertTrue( $c1->has_param( 'end_date' ) );
		$this->assertEquals( - 3.0, $c1->get_total(), '', .1 );

		$this->assertEquals( 'Savings', $c2->get_name() );
		$this->assertEquals( '10OFF', $c2->get_description() );
		$this->assertEquals( '10OFF', $c2->get_param( 'code' ) );
		$this->assertEquals( '%', $c2->get_param( 'amount_type' ) );
		$this->assertTrue( $c2->has_param( 'start_date' ) );
		$this->assertTrue( $c2->has_param( 'end_date' ) );
		$this->assertEquals( - 2.0, $c2->get_total(), '', .1 );
	}

	public function test_multiple_coupons_multiple_products() {

		$post        = self::factory()->post->create( array( 'post_type' => 'it_exchange_tran' ) );
		$cart_object = self::$cartObjects['multipleCouponsMultipleProducts'];
		$transaction = $this->getMockBuilder( 'IT_Exchange_Transaction' )->setMethods( array( 'get_ID' ) )->getMock();
		$transaction->expects( $this->any() )->method( 'get_ID' )->willReturn( $post );

		self::$converter->convert( $cart_object, $transaction );

		$repo  = new ITE_Line_Item_Transaction_Repository( new ITE_Line_Item_Repository_Events(), $transaction );
		$items = $repo->all();

		$this->assertCount( 2, $items->with_only( 'product' ), 'Product count correct' );

		/** @var ITE_Cart_Product $p1 */
		$p1 = $items->get( 'product', '8-40cd750bba9870f18aada2478b24840a' );
		$this->assertNotNull( $p1 );

		/** @var ITE_Cart_Product $p2 */
		$p2 = $items->get( 'product', '4-40cd750bba9870f18aada2478b24840a' );
		$this->assertNotNull( $p2 );

		$this->assertCount( 2, $items->with_only( 'coupon' ), 'Global coupon items added' );
		$this->assertCount( 2, $p1->get_line_items()->with_only( 'coupon' ), 'Product coupon items added.' );
		$this->assertCount( 2, $p2->get_line_items()->with_only( 'coupon' ), 'Product coupon items added.' );

		/** @var ITE_Coupon_Line_Item $cart_global */
		$cart_global = $items->with_only( 'coupon' )->filter( function ( ITE_Coupon_Line_Item $coupon ) {
			return $coupon->has_param( 'code' ) && $coupon->get_param( 'code' ) === '10OFF';
		} )->first();
		$this->assertNotNull( $cart_global, "'cart' global coupon found." );

		/** @var ITE_Coupon_Line_Item $custom_global */
		$custom_global = $items->with_only( 'coupon' )->filter( function ( ITE_Coupon_Line_Item $coupon ) {
			return $coupon->has_param( 'code' ) && $coupon->get_param( 'code' ) === 'CUSTOM_CODE';
		} )->first();
		$this->assertNotNull( $custom_global, "'custom' global coupon found." );

		/** @var ITE_Coupon_Line_Item $p1_cart_item */
		$p1_cart_item = $p1->get_line_items()->with_only( 'coupon' )->filter( function ( ITE_Coupon_Line_Item $coupon ) {
			return $coupon->has_param( 'code' ) && $coupon->get_param( 'code' ) === '10OFF';
		} )->first();

		/** @var ITE_Coupon_Line_Item $p1_custom_item */
		$p1_custom_item = $p1->get_line_items()->with_only( 'coupon' )->filter( function ( ITE_Coupon_Line_Item $coupon ) {
			return $coupon->has_param( 'code' ) && $coupon->get_param( 'code' ) === 'CUSTOM_CODE';
		} )->first();

		/** @var ITE_Coupon_Line_Item $p2_cart_item */
		$p2_cart_item = $p2->get_line_items()->with_only( 'coupon' )->filter( function ( ITE_Coupon_Line_Item $coupon ) {
			return $coupon->has_param( 'code' ) && $coupon->get_param( 'code' ) === '10OFF';
		} )->first();

		/** @var ITE_Coupon_Line_Item $p2_custom_item */
		$p2_custom_item = $p2->get_line_items()->with_only( 'coupon' )->filter( function ( ITE_Coupon_Line_Item $coupon ) {
			return $coupon->has_param( 'code' ) && $coupon->get_param( 'code' ) === 'CUSTOM_CODE';
		} )->first();

		$this->assertEquals( 'Savings', $cart_global->get_name() );
		$this->assertEquals( '10OFF', $cart_global->get_description() );
		$this->assertEquals( '10OFF', $cart_global->get_param( 'code' ) );
		$this->assertEquals( '%', $cart_global->get_param( 'amount_type' ) );
		$this->assertTrue( $cart_global->has_param( 'start_date' ) );
		$this->assertTrue( $cart_global->has_param( 'end_date' ) );
		$this->assertEquals( 0, $cart_global->get_total() );
		$this->assertEquals('cart', $cart_global->get_param( 'type' ) );

		$this->assertEquals( 'Savings', $custom_global->get_name() );
		$this->assertEquals( 'CUSTOM_CODE', $custom_global->get_description() );
		$this->assertEquals( 'CUSTOM_CODE', $custom_global->get_param( 'code' ) );
		$this->assertTrue( $custom_global->has_param( 'customProp' ) );
		$this->assertEquals( 'value', $custom_global->get_param( 'customProp' ) );
		$this->assertFalse( $custom_global->has_param( 'end_date' ) );
		$this->assertEquals( 0, $custom_global->get_total() );
		$this->assertEquals('custom', $custom_global->get_param( 'type' ) );

		$this->assertNotNull( $p1_cart_item, "First product 'cart' coupon item found." );
		$this->assertNotNull( $p1_custom_item, "First product 'coupon' coupon item found." );
		$this->assertNotNull( $p2_cart_item, "Second product 'cart' coupon item found." );
		$this->assertNotNull( $p2_custom_item, "Second product 'coupon' coupon item found." );

		$this->assertEquals( 'Savings', $p1_cart_item->get_name() );
		$this->assertEquals( '10OFF', $p1_cart_item->get_description() );
		$this->assertEquals( '10OFF', $p1_cart_item->get_param( 'code' ) );
		$this->assertEquals( '%', $p1_cart_item->get_param( 'amount_type' ) );
		$this->assertTrue( $p1_cart_item->has_param( 'start_date' ) );
		$this->assertTrue( $p1_cart_item->has_param( 'end_date' ) );
		$this->assertEquals( - 1.5, $p1_cart_item->get_total(), '', .1 );
		$this->assertEquals( 'cart', $p1_cart_item->get_param( 'type' ) );

		$this->assertEquals( 'Savings', $p2_cart_item->get_name() );
		$this->assertEquals( '10OFF', $p2_cart_item->get_description() );
		$this->assertEquals( '10OFF', $p2_cart_item->get_param( 'code' ) );
		$this->assertEquals( '%', $p2_cart_item->get_param( 'amount_type' ) );
		$this->assertTrue( $p2_cart_item->has_param( 'start_date' ) );
		$this->assertTrue( $p2_cart_item->has_param( 'end_date' ) );
		$this->assertEquals( - 1.0, $p2_cart_item->get_total(), '', .1 );
		$this->assertEquals( 'custom', $p1_custom_item->get_param( 'type' ) );

		$this->assertEquals( 'Savings', $p1_custom_item->get_name() );
		$this->assertEquals( 'CUSTOM_CODE', $p1_custom_item->get_description() );
		$this->assertEquals( 'CUSTOM_CODE', $p1_custom_item->get_param( 'code' ) );
		$this->assertTrue( $p1_custom_item->has_param( 'customProp' ) );
		$this->assertEquals( 'value', $p1_custom_item->get_param( 'customProp' ) );
		$this->assertFalse( $p1_custom_item->has_param( 'end_date' ) );
		$this->assertEquals( - 1.5, $p1_cart_item->get_total(), '', .1 );
		$this->assertEquals( 'cart', $p2_cart_item->get_param( 'type' ) );

		$this->assertEquals( 'Savings', $p2_custom_item->get_name() );
		$this->assertEquals( 'CUSTOM_CODE', $p2_custom_item->get_description() );
		$this->assertEquals( 'CUSTOM_CODE', $p2_custom_item->get_param( 'code' ) );
		$this->assertTrue( $p2_custom_item->has_param( 'customProp' ) );
		$this->assertEquals( 'value', $p2_custom_item->get_param( 'customProp' ) );
		$this->assertFalse( $p2_custom_item->has_param( 'end_date' ) );
		$this->assertEquals( - 1.0, $p2_custom_item->get_total(), '', .1 );
		$this->assertEquals( 'custom', $p2_custom_item->get_param( 'type' ) );
	}
}