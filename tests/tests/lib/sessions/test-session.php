<?php
/**
 * Test the sessions.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class Test_IT_Exchange_Session
 *
 * @group session
 */
class Test_IT_Exchange_Session extends IT_Exchange_UnitTestCase {

	public function test_cleanup_does_not_delete_active_sessions() {

		$session = ITE_Session_Model::create( array(
			'expires_at' => time() + DAY_IN_SECONDS,
			'ID'         => it_exchange_create_unique_hash(),
		) );

		it_exchange_db_session_cleanup();

		$this->assertNotNull( ITE_Session_Model::get( $session->get_pk() ) );
	}


	public function test_cleanup_does_not_delete_purchased_sessions() {

		$session = ITE_Session_Model::create( array(
			'expires_at'   => time() - DAY_IN_SECONDS,
			'ID'           => it_exchange_create_unique_hash(),
			'purchased_at' => time() - HOUR_IN_SECONDS
		) );

		it_exchange_db_session_cleanup();

		$this->assertNotNull( ITE_Session_Model::get( $session->get_pk() ) );
	}

	public function test_cleanup_deletes_expired_sessions() {

		$session = ITE_Session_Model::create( array(
			'expires_at' => time() - DAY_IN_SECONDS,
			'ID'         => it_exchange_create_unique_hash(),
		) );

		it_exchange_db_session_cleanup();

		$this->assertNull( ITE_Session_Model::get( $session->get_pk() ) );
	}

	public function test_cleanup_deletes_7_day_old_purchased_sessions() {

		$session = ITE_Session_Model::create( array(
			'expires_at'   => time() - DAY_IN_SECONDS - WEEK_IN_SECONDS,
			'ID'           => it_exchange_create_unique_hash(),
			'purchased_at' => time() - WEEK_IN_SECONDS - 1
		) );

		it_exchange_db_session_cleanup();

		$this->assertNull( ITE_Session_Model::get( $session->get_pk() ) );
	}

}