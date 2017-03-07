<?php
/**
 * Test Case for REST Routes.
 */

/**
 * Class Test_IT_Exchange_REST_Route
 */
abstract class Test_IT_Exchange_REST_Route extends IT_Exchange_UnitTestCase {

	/** @var Spy_REST_Server */
	protected $server;

	/** @var \iThemes\Exchange\REST\Manager */
	protected $manager;

	/**
	 * @inheritDoc
	 */
	public function setUp() {
		parent::setUp();

		wp_set_current_user( 0 );
		$this->initialize_server();
	}

	protected function initialize_server() {

		$this->manager = \iThemes\Exchange\REST\get_rest_manager();
		$this->server  = $GLOBALS['wp_rest_server'] = new Spy_REST_Server;

		do_action( 'rest_api_init' );
	}

	/**
	 * @inheritDoc
	 */
	public function tearDown() {
		parent::tearDown();

		$this->manager->_reset();
	}

	protected function assertErrorResponse( $code, $response, $status = null ) {

		if ( $response instanceof WP_REST_Response ) {
			$response = $response->as_error();
		}

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( $code, $response->get_error_code() );

		if ( null !== $status ) {
			$data = $response->get_error_data();
			$this->assertArrayHasKey( 'status', $data );
			$this->assertEquals( $status, $data['status'] );
		}
	}
}