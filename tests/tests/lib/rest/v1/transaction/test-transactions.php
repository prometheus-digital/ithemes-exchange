<?php
/**
 * Test the Transactions Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_v1_Transactions_Route
 *
 * @group rest-api
 */
class Test_IT_Exchange_v1_Transactions_Route extends IT_Exchange_UnitTestCase {

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

	public function test_route_registered() {

		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/it_exchange/v1/transactions', $routes );
		$this->assertArrayHasKey( '/it_exchange/v1/transactions/(?P<transaction_id>\d+)', $routes );
	}

	public function test_collection_forbidden_for_public() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\PublicAuthScope();
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_forbidden_for_guest() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\GuestAuthScope( it_exchange_get_customer( 'guest@example.org' ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_forbidden_for_subscriber() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer(
			self::factory()->user->create( array( 'role' => 'subscriber' ) )
		) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_allowed_for_customer() {

		$customer = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$request->set_param( 'customer', $customer );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( $customer ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_collection_allowed_for_administrator() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_object_forbidden_for_public() {

		$txn_id  = self::transaction_factory()->create();
		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/$txn_id" );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\PublicAuthScope();
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_object_forbidden_for_guest() {

		$txn_id  = self::transaction_factory()->create();
		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/$txn_id" );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\GuestAuthScope( it_exchange_get_customer( 'guest@example.org' ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_object_forbidden_for_subscriber() {

		$txn_id  = self::transaction_factory()->create();
		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/$txn_id" );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer(
			self::factory()->user->create( array( 'role' => 'subscriber' ) )
		) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_object_allowed_for_administrator() {

		$txn_id  = self::transaction_factory()->create();
		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/$txn_id" );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_object_allowed_for_customer() {

		$customer = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$txn_id   = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/$txn_id" );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( $customer ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_filter_by_other_customer_forbidden_for_customer() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$request->set_param( 'customer', 1 );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer(
			self::factory()->user->create( array( 'role' => 'subscriber' ) )
		) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_filter_by_parent_forbidden_for_customer() {

		$customer = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$request->set_param( 'customer', $customer );
		$request->set_param( 'parent', 500 );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( $customer ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_filter_by_parent_allowed_for_administrator() {

		$parent  = self::transaction_factory()->create_and_get();
		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$request->set_param( 'parent', $parent->get_ID() );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_filter_by_parent_rejected_for_invalid_transaction() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$request->set_param( 'parent', 500 );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 400, $response->get_status() );
	}

	public function test_filter_by_method_id_forbidden_for_customer() {

		$customer = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$request->set_param( 'customer', $customer );
		$request->set_param( 'method_id', 'test-method-id-1' );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( $customer ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_filter_by_method_id_allowed_for_administrator() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$request->set_param( 'method_id', 'test-method-id-1' );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_collection_edit_context_forbidden_for_customer() {

		$customer = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$request->set_param( 'customer', $customer );
		$request->set_param( 'context', 'edit' );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( $customer ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_edit_context_allowed_for_administrator() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$request->set_param( 'context', 'edit' );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_object_edit_context_forbidden_for_customer() {

		$customer = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}" );
		$request->set_param( 'context', 'edit' );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( $customer ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_object_edit_context_allowed_for_administrator() {

		$txn     = self::transaction_factory()->create();
		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}" );
		$request->set_param( 'context', 'edit' );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_invalid_pagination_parameters_are_rejected() {

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$request->set_param( 'page', 0 );
		$request->set_param( 'per_page', 'garbage' );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 400, $response->get_status() );
	}

	public function test_pagination_parameters() {

		$transactions = self::transaction_factory()->create_many( 5 );

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$request->set_param( 'page', 2 );
		$request->set_param( 'per_page', 3 );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );

		$headers = $response->get_headers();
		$data    = $response->get_data();
		$this->assertCount( 2, $data );

		$this->assertEquals( $transactions[1], $data[0]['id'] );
		$this->assertEquals( $transactions[0], $data[1]['id'] );

		$this->assertArrayHasKey( 'X-WP-Total', $headers );
		$this->assertEquals( 5, $headers['X-WP-Total'] );

		$this->assertArrayHasKey( 'X-WP-TotalPages', $headers );
		$this->assertEquals( 2, $headers['X-WP-TotalPages'] );
	}

	public function test_filter_by_customer() {

		$c1 = 1;
		$t1 = self::transaction_factory()->create( array( 'customer' => $c1 ) );
		$c2 = self::factory()->user->create();
		$t2 = self::transaction_factory()->create( array( 'customer' => $c2 ) );

		$request = \iThemes\Exchange\REST\Request::from_path( '/it_exchange/v1/transactions' );
		$request->set_param( 'customer', $c2 );
		$this->assertNotNull( $request );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );

		$this->assertCount( 1, $response->get_data() );
		$this->assertEquals( $t2, $response->data[0]['id'] );
	}

	public function test_update_transaction_status_forbidden_for_customer() {

		$customer = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}" );
		$request->set_method( 'PUT' );
		$request->set_body( wp_json_encode( array( 'status' => 'paid' ) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( $customer ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_update_transaction_status() {

		$txn = self::transaction_factory()->create();

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}" );
		$request->set_method( 'PUT' );
		$request->add_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array( 'status' => 'paid' ) ) );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );

		$this->assertEquals( 'paid', it_exchange_get_transaction_status( $txn ), 'Status not updated.' );
		$this->assertEquals( 'paid', $response->data['status']['slug'], 'New status not returned from response.' );
	}
}