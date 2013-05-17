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
		$this->init();
	}
	
	function init() {
		if ( '' == session_id() )
			session_start();
			
		$session = empty( $_SESSION['it_exchange'] ) ? array() : $_SESSION['it_exchange'];
		$this->_session = $session;
	}
	
	function get_session_data( $key = false ) {
		$session = empty( $_SESSION['it_exchange'] ) ? array() : $_SESSION['it_exchange'];
		
		
		if ( isset( $key ) ) {
			
			$key = sanitize_key( $key );
			
			if ( $key && !empty( $this->_session[$key] ) )
				return $this->_session[$key];
			
		
		} else {
			return $this->_session;
		}
		
		return array();	
	}
	
	function add_session_data( $key, $data ) {
		$session = empty( $_SESSION['it_exchange'] ) ? array() : $_SESSION['it_exchange'];
		
		$key = sanitize_key( $key );
		
		if ( !empty( $this->_session[$key] ) ) {
			$current_data = maybe_unserialize( $this->_session[$key] );
			$this->_session[$key] = maybe_serialize( array_merge( $current_data, $data ) );
		} else {
			$this->_session[$key] = maybe_serialize( (array)$data );
		}
		
		$_SESSION['it_exchange'] = $this->_session;
	}
	
	function update_session_data( $key, $data ) {
		$session = empty( $_SESSION['it_exchange'] ) ? array() : $_SESSION['it_exchange'];
		
		$key = sanitize_key( $key );
		
		$this->_session[$key] = maybe_serialize( (array)$data );
		
		$_SESSION['it_exchange'] = $this->_session;
	}
	
	function clear_session_data( $key = false ) {
		$session = empty( $_SESSION['it_exchange'] ) ? array() : $_SESSION['it_exchange'];
		
		if ( ! $key ) {
				
			$key = sanitize_key( $key );
			
			if ( isset( $this->_session[$key] ) )
				unset( $this->_session[$key] );
		
		}
		
		$_SESSION['it_exchange'] = $this->_session;
	}
	
	function clear_session( $hard = false ) {		
		if ( $hard )
			$this->init();
		
		$_SESSION['it_exchange'] = $this->_session = array();
	}
}
$GLOBALS['it_exchange']['session'] = new IT_Exchange_Session();
