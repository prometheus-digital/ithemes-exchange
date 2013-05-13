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
	 * Holds our session data
	 *
	 * @var array
	 * @access private
	 * @since 0.4.0
	 */
	private $_session = array();
	
	function __construct() {
		if ( ! defined( 'WP_SESSION_COOKIE' ) )
			define( 'WP_SESSION_COOKIE', '_wp_session' );
		
		if ( ! class_exists( 'Recursive_ArrayAccess' ) )
			require( 'wp_session_manager/class-recursive-arrayaccess.php' );
		
		if ( ! class_exists( 'WP_Session' ) ) {
			require( 'wp_session_manager/class-wp-session.php' );
			require( 'wp_session_manager/wp-session.php' );
		}
				
		if ( empty( $this->_session_data ) )
			add_action( 'plugins_loaded', array( $this, 'init' ) );
		else
			add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Setup the WP_Session instance
	 *
	 * @access public
	 * @since 0.4.0
	 * @return void
	 */
	function init() {
	
		$this->_session = WP_Session::get_instance();
		return $this->_session;
	}

	/**
	 * Retrieve a session variable
	 *
	 * @access public
	 * @since 0.4.0
	 * @param string $type Session type
	 * @return mixed Session variable or flase
	 */
	function get( $type ) {
		$type = sanitize_key( $type );
		return isset( $this->_session['it_exchange'][ $type ] ) ? maybe_unserialize( $this->_session['it_exchange'][ $type ] ) : false;
	}

	/**
	 * Set a session variable
	 *
	 * @access public
	 * @since 0.4.0
	 * @param string $type Session type
	 * @param string $key Session key
	 * @param string $data Session variable
	 */
	function set( $type, $key, $data ) {
		$type = sanitize_key( $type );
		$key = sanitize_key( $key );
		
		if ( is_array( $data ) )
			$data = serialize( $data );
		
		if ( ! empty( $key ) )
			$this->_session['it_exchange'][$type][$key] = $data;
		else
			$this->_session['it_exchange'][$type][] = $data;
	}

	/**
	 * Update am existing session variable
	 *
	 * @access public
	 * @since 0.4.0
	 * @param string $type Session type
	 * @param string $key Session key
	 * @param string $data Session variable
	 */
	function update( $type, $key, $data ) {
		$type = sanitize_key( $type );
		$key = sanitize_key( $key );
		
		if ( is_array( $data ) )
			$data = serialize( $data );
		
		if ( ! empty( $key ) ) {
			
			if ( isset( $this->_session['it_exchange'][$type][$key] ) ) {
				
				$this->_session['it_exchange'][$type][$key] = $data;
				return true;
				
			}
				
		}
				
		return false;
	}

	/**
	 * Unset a session variable
	 *
	 * @access public
	 * @since 0.4.0
	 * @param string $type Session type
	 * @param string $key Session key
	 */
	function unset_data( $type, $key ) {
		$type = sanitize_key( $type );
		$key = sanitize_key( $key );
		
		if ( isset( $this->_session['it_exchange'][$type][$key] ) )
			unset( $this->_session['it_exchange'][$type][$key] );
	}

	/**
	 * Removes all data from the session
	 *
	 * @since 0.4.0
	 * @param string $type Session type
	 * @return array the $_session_data property
	*/
	function clear_data( $type ) {
		$this->_session['it_exchange'][$type] = array();
	}
}
$GLOBALS['it_exchange']['session'] = new IT_Exchange_Session();