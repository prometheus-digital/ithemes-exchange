<?php
/**
 * Test the middleware handler.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Email_Middleware_Handler
 */
class Test_IT_Exchange_Email_Middleware_Handler extends IT_Exchange_UnitTestCase {

	public function test_push() {

		$sendable = $this->getMockBuilder( 'IT_Exchange_Sendable_Mutable_Wrapper' )->disableOriginalConstructor()->getMock();

		$middleware = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )->getMock();
		$middleware->expects( $this->once() )->method( 'handle' )->with( $sendable )->willReturn( true );

		$handler = new IT_Exchange_Email_Middleware_Handler();
		$handler->push( $middleware );

		$this->assertTrue( $handler->handle( $sendable ) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_push_throws_exception_for_duplicate_names() {

		$handler = new IT_Exchange_Email_Middleware_Handler();
		$handler->push( $this->getMockForAbstractClass( 'IT_Exchange_Email_Middleware' ), 'name' );
		$handler->push( $this->getMockForAbstractClass( 'IT_Exchange_Email_Middleware' ), 'name' );
	}

	public function test_before() {

		$sendable = $this->getMockBuilder( 'IT_Exchange_Sendable_Mutable_Wrapper' )->disableOriginalConstructor()->getMock();

		$m1 = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )->getMock();
		$m1->expects( $this->once() )->method( 'handle' )->with( $sendable )->willReturn( false );

		$m2 = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )->getMock();
		$m2->expects( $this->never() )->method( 'handle' );

		$handler = new IT_Exchange_Email_Middleware_Handler();
		$handler->push( $m2, 'm2' );
		$handler->before( $m1, 'm2' );

		$this->assertFalse( $handler->handle( $sendable ) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_before_throws_exception_for_duplicate_names() {

		$handler = new IT_Exchange_Email_Middleware_Handler();
		$handler->push( $this->getMockForAbstractClass( 'IT_Exchange_Email_Middleware' ), 'name' );
		$handler->before( $this->getMockForAbstractClass( 'IT_Exchange_Email_Middleware' ), 'name', 'name' );
	}

	public function test_after() {

		$sendable = $this->getMockBuilder( 'IT_Exchange_Sendable_Mutable_Wrapper' )->disableOriginalConstructor()->getMock();

		$m1 = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )->getMock();
		$m1->expects( $this->once() )->method( 'handle' )->with( $sendable )->willReturn( true );

		$m2 = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )->getMock();
		$m2->expects( $this->once() )->method( 'handle' )->willReturn( false );

		$m3 = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )->getMock();
		$m3->expects( $this->never() )->method( 'handle' );

		$handler = new IT_Exchange_Email_Middleware_Handler();
		$handler->push( $m1, 'm1' );
		$handler->push( $m3, 'm3' );
		$handler->after( $m2, 'm1' );

		$this->assertFalse( $handler->handle( $sendable ) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_after_throws_exception_for_duplicate_names() {

		$handler = new IT_Exchange_Email_Middleware_Handler();
		$handler->push( $this->getMockForAbstractClass( 'IT_Exchange_Email_Middleware' ), 'name' );
		$handler->after( $this->getMockForAbstractClass( 'IT_Exchange_Email_Middleware' ), 'name', 'name' );
	}

	public function test_first() {

		$sendable = $this->getMockBuilder( 'IT_Exchange_Sendable_Mutable_Wrapper' )->disableOriginalConstructor()->getMock();

		$m1 = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )->getMock();
		$m1->expects( $this->once() )->method( 'handle' )->with( $sendable )->willReturn( false );

		$m2 = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )->getMock();
		$m2->expects( $this->never() )->method( 'handle' );

		$handler = new IT_Exchange_Email_Middleware_Handler();
		$handler->push( $m2 );
		$handler->first( $m1 );

		$this->assertFalse( $handler->handle( $sendable ) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_first_throws_exception_for_duplicate_names() {

		$handler = new IT_Exchange_Email_Middleware_Handler();
		$handler->push( $this->getMockForAbstractClass( 'IT_Exchange_Email_Middleware' ), 'name' );
		$handler->first( $this->getMockForAbstractClass( 'IT_Exchange_Email_Middleware' ), 'name' );
	}

	public function test_complex() {

		$recipient = $this->getMockForAbstractClass( 'IT_Exchange_Email_Recipient' );
		$sendable  = new IT_Exchange_Sendable_Mutable_Wrapper( new IT_Exchange_Simple_Email( '', '', $recipient ) );

		$m1 = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )
		           ->setMockClassName( 'IT_Exchange_Email_MiddlewareM1' )->getMock();
		$m1->expects( $this->once() )->method( 'handle' )->with( $this->callback( function ( $sendable ) {
			return true;
		} ) )->willReturnCallback( function ( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {

			$sendable->override_subject( 'm1' );

			return true;
		} );

		$m2 = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )
		           ->setMockClassName( 'IT_Exchange_Email_MiddlewareM2' )->getMock();
		$m2->expects( $this->once() )->method( 'handle' )->with( $this->callback(
			function ( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {
				return strpos( $sendable->get_subject(), 'm1' ) === 0;
			} ) )->willReturnCallback( function ( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {

			$sendable->override_subject( $sendable->get_subject() . 'm2' );

			return true;
		} );

		$m3 = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )
		           ->setMockClassName( 'IT_Exchange_Email_MiddlewareM3' )->getMock();
		$m3->expects( $this->once() )->method( 'handle' )->with( $this->callback(
			function ( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {
				return strpos( $sendable->get_subject(), 'm1m2' ) === 0;
			} ) )->willReturnCallback( function ( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {

			$sendable->override_subject( $sendable->get_subject() . 'm3' );

			return true;
		} );

		$m4 = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )
		           ->setMockClassName( 'IT_Exchange_Email_MiddlewareM4' )->getMock();
		$m4->expects( $this->once() )->method( 'handle' )->with( $this->callback(
			function ( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {
				return strpos( $sendable->get_subject(), 'm1m2m3' ) === 0;
			} ) )->willReturnCallback( function ( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {

			$sendable->override_subject( $sendable->get_subject() . 'm4' );

			return true;
		} );

		$m5 = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )
		           ->setMockClassName( 'IT_Exchange_Email_MiddlewareM5' )->getMock();
		$m5->expects( $this->once() )->method( 'handle' )->with( $this->callback(
			function ( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {
				return strpos( $sendable->get_subject(), 'm1m2m3m4' ) === 0;
			} ) )->willReturnCallback( function ( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {

			$sendable->override_subject( $sendable->get_subject() . 'm5' );

			return true;
		} );

		$m6 = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )
		           ->setMockClassName( 'IT_Exchange_Email_MiddlewareM6' )->getMock();
		$m6->expects( $this->once() )->method( 'handle' )->with( $this->callback(
			function ( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {
				return strpos( $sendable->get_subject(), 'm1m2m3m4m5' ) === 0;
			} ) )->willReturnCallback( function ( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {

			$sendable->override_subject( $sendable->get_subject() . 'm6' );

			return true;
		} );

		$handler = new IT_Exchange_Email_Middleware_Handler();

		$handler->push( $m4, 'm4' );
		$handler->before( $m2, 'm4', 'm2' );
		$handler->after( $m3, 'm2' );
		$handler->first( $m1 );
		$handler->push( $m6 );
		$handler->after( $m5, 'm4' );

		$handler->handle( $sendable );
	}

	public function test_skip() {

		$sendable = $this->getMockBuilder( 'IT_Exchange_Sendable_Mutable_Wrapper' )->disableOriginalConstructor()->getMock();

		$m1 = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )->getMock();
		$m1->expects( $this->exactly( 2 ) )->method( 'handle' )->with( $sendable )->willReturn( true );

		$m2 = $this->getMockBuilder( 'IT_Exchange_Email_Middleware' )->setMethods( array( 'handle' ) )->getMock();
		$m2->expects( $this->once() )->method( 'handle' )->with( $sendable )->willReturn( true );

		$handler = new IT_Exchange_Email_Middleware_Handler();
		$handler->push( $m1, 'm1' )->push( $m2, 'm2' );

		$handler->skip( 'm2' );
		$this->assertTrue( $handler->handle( $sendable ) );

		$this->assertTrue( $handler->handle( $sendable ) );
	}

}
