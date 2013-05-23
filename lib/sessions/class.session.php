<?php
/**
 * This file contains the session class
 *
 * @since 0.3.3
 * @package IT_Exchange
*/

/**
 * The IT_Exchange_Session class holds cart and purchasing details
 *
 * @since 0.3.3
*/
class IT_Exchange_Session {
	
	/**
	 * @param array $_session  an array of any additional data needed by iThemes Exchange
	 * @since 0.4.0
	*/
	private $_session;
	
	function IT_Exchange_Session() {
		if( ! defined( 'IT_EXCHANGE_SESSION_COOKIE' ) )
			define( 'IT_EXCHANGE_SESSION_COOKIE', '_it_exchange' );
		
		if ( ! class_exists( 'Recursive_ArrayAccess' ) )
			require_once( 'db_session_manager/class-recursive-arrayaccess.php' );
		
		// Only include the functionality if it's not pre-defined.
		if ( ! class_exists( 'IT_Exchange_DB_Sessions' ) ) {
			require_once( 'db_session_manager/class-db-session.php' );
			require_once( 'db_session_manager/db-session.php' );
		}

		if ( empty( $this->_session ) )
			add_action( 'plugins_loaded', array( $this, 'init' ) );
		else
			add_action( 'init', array( $this, 'init' ) );
	}
	
	function init() {
		$this->_session = IT_Exchange_DB_Sessions::get_instance();
		return $this->_session;
	}
	
	function get_session_data( $key = false ) {
		if ( $key ) {
			
			$key = sanitize_key( $key );
			
			if ( $key && !empty( $this->_session[$key] ) )
				return $this->_session[$key];
			
		
		} else {
			return $this->_session;
		}
		
		return array();	
	}
	
	function add_session_data( $key, $data ) {
		$key = sanitize_key( $key );
		
		if ( !empty( $this->_session[$key] ) ) {
			$current_data = maybe_unserialize( $this->_session[$key] );
			$this->_session[$key] = maybe_serialize( array_merge( $current_data, (array)$data ) );
		} else {
			$this->_session[$key] = maybe_serialize( (array)$data );
		}
		it_exchange_db_session_commit();
	}
	
	function update_session_data( $key, $data ) {
		$key = sanitize_key( $key );
		$this->_session[$key] = maybe_serialize( (array)$data );
		it_exchange_db_session_commit();
	}
	
	function clear_session_data( $key=false ) {
		if ( $key ) {
			$key = sanitize_key( $key );
			
			if ( isset( $this->_session[$key] ) ) {
				unset( $this->_session[$key] );
				it_exchange_db_session_commit();
			}
		}
		$this->_session[$key] = $this->_session[$key];
	}
	
	function clear_session( $hard = false ) {		
		if ( $hard )
			$this->init();
	}
}
$GLOBALS['it_exchange']['session'] = new IT_Exchange_Session();
