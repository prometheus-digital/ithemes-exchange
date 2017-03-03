<?php
/**
 * Test the activity routes.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_v1_Transaction_Activity_Route
 *
 * @group rest-api
 */
class Test_IT_Exchange_v1_Transaction_Activity_Route extends Test_IT_Exchange_REST_Route {

	public function test_collection_forbidden_for_public() {

		$txn     = self::transaction_factory()->create();
		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/activity" );

		$scope = new \iThemes\Exchange\REST\Auth\PublicAuthScope();
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_forbidden_for_guest() {

		$txn     = self::transaction_factory()->create();
		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/activity" );

		$scope = new \iThemes\Exchange\REST\Auth\GuestAuthScope( it_exchange_get_customer( 'guest@example.org' ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_forbidden_for_customer() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/activity" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_forbidden_for_other_customer() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => 1 ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/activity" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_collection_allowed_for_administrator() {

		$txn     = self::transaction_factory()->create();
		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/activity" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_public_only_collection_allowed_for_customer() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/activity" );
		$request->set_param( 'public_only', true );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
	}

	public function test_public_only_false_collection_forbidden_for_customer() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/activity" );
		$request->set_param( 'public_only', false );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_create_forbidden_for_public() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/activity" );
		$request->set_method( 'POST' );
		$request->set_body( wp_json_encode( array( 'description' => 'Description.' ) ) );
		$request->add_header( 'content-type', 'application/json' );

		$scope = new \iThemes\Exchange\REST\Auth\PublicAuthScope();
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_create_forbidden_for_guest() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/activity" );
		$request->set_method( 'POST' );
		$request->set_body( wp_json_encode( array( 'description' => 'Description.' ) ) );
		$request->add_header( 'content-type', 'application/json' );

		$scope = new \iThemes\Exchange\REST\Auth\GuestAuthScope( it_exchange_get_customer( 'guest@example.org' ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_create_forbidden_for_customer() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/activity" );
		$request->set_method( 'POST' );
		$request->set_body( wp_json_encode( array( 'description' => 'Description.' ) ) );
		$request->add_header( 'content-type', 'application/json' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
	}

	public function test_create_allowed_for_administrator() {

		wp_set_current_user( 1 );
		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$request  = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/activity" );
		$request->set_method( 'POST' );
		$request->set_body( wp_json_encode( array( 'description' => 'Description.' ) ) );
		$request->add_header( 'content-type', 'application/json' );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( WP_Http::CREATED, $response->get_status() );
		$this->assertNotEmpty( $response->data['id'] );
		$this->assertInstanceOf( 'IT_Exchange_Txn_Activity', it_exchange_get_txn_activity( $response->data['id'] ) );
	}

	public function test_object_not_found_if_invalid_id() {

		$txn     = self::transaction_factory()->create();
		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/activity/500" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_object_not_found_if_transaction_deleted() {

		/** @var IT_Exchange_Transaction $txn */
		$txn     = self::transaction_factory()->create_and_get();
		$builder = new \IT_Exchange_Txn_Activity_Builder( $txn, 'note' );
		$builder->set_description( 'Test' );
		$activity = $builder->build( it_exchange_get_txn_activity_factory() );
		$this->assertNotNull( $activity );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn->get_ID()}/activity/{$activity->get_ID()}" );
		wp_delete_post( $txn->get_ID(), true );
		$txn->delete();

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_object_not_found_if_id_mismatch() {

		$t1      = self::transaction_factory()->create();
		$t2      = self::transaction_factory()->create();
		$builder = new \IT_Exchange_Txn_Activity_Builder( it_exchange_get_transaction( $t1 ), 'note' );
		$builder->set_description( 'Test' );
		$activity = $builder->build( it_exchange_get_txn_activity_factory() );
		$this->assertNotNull( $activity );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$t2}/activity/{$activity->get_ID()}" );

		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 404, $response->get_status() );
	}

	public function test_get_object() {

		$txn     = self::transaction_factory()->create();
		$builder = new \IT_Exchange_Txn_Activity_Builder( it_exchange_get_transaction( $txn ), 'note' );
		$builder->set_description( 'Test' );
		$activity = $builder->build( it_exchange_get_txn_activity_factory() );
		$this->assertNotNull( $activity );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/activity/{$activity->get_ID()}" );
		$scope   = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( $activity->get_ID(), $response->data['id'] );
		$this->assertEquals( $activity->get_description(), $response->data['description'] );
	}

	public function test_delete_object() {

		$txn     = self::transaction_factory()->create();
		$builder = new \IT_Exchange_Txn_Activity_Builder( it_exchange_get_transaction( $txn ), 'note' );
		$builder->set_description( 'Test' );
		$activity = $builder->build( it_exchange_get_txn_activity_factory() );
		$this->assertNotNull( $activity );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/activity/{$activity->get_ID()}" );
		$request->set_method( 'DELETE' );
		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( it_exchange_get_customer( 1 ) );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( WP_Http::NO_CONTENT, $response->get_status() );
		$this->assertNull( it_exchange_get_txn_activity( $activity->get_ID() ) );
	}

	public function test_delete_object_forbidden_for_customer() {

		$customer = it_exchange_get_customer( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$txn      = self::transaction_factory()->create( array( 'customer' => $customer ) );
		$builder  = new \IT_Exchange_Txn_Activity_Builder( it_exchange_get_transaction( $txn ), 'note' );
		$builder->set_description( 'Test' );
		$activity = $builder->build( it_exchange_get_txn_activity_factory() );
		$this->assertNotNull( $activity );

		$request = \iThemes\Exchange\REST\Request::from_path( "/it_exchange/v1/transactions/{$txn}/activity/{$activity->get_ID()}" );
		$request->set_method( 'DELETE' );
		$scope = new \iThemes\Exchange\REST\Auth\CustomerAuthScope( $customer );
		$this->manager->set_auth_scope( $scope );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( rest_authorization_required_code(), $response->get_status() );
		$this->assertNotNull( it_exchange_get_txn_activity( $activity->get_ID() ) );
	}
}