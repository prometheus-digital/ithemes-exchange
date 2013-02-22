<?php
/**
 * This file contains the session class
 *
 * @since 0.3.3
 * @package IT_Cart_Buddy
*/

/**
 * The IT_Cart_Buddy_Session class holds cart and purchasing details
 *
 * @since 0.3.3
*/
class IT_Cart_Buddy_Session {

	/**
	 * @param string $_token  session token
	 * @since 0.3.3
	*/
	private $_token;

	/**
	 * @param array $_products an array of items currently in the user's shopping cart
	 * @since 0.3.3
	*/
	private $_products;

	/**
	 * @param array $_cart_data  an array of any additional data needed by the cart
	 * @since 0.3.3
	*/
	private $_session_data;
	
	function IT_Cart_Buddy_Session() {
		$this->set_session_token();
		$this->init_session();
		$this->register_hooks();
	}

	/**
	 * Inits the session
	 *
	 * Starts a new one or loads the current one into this object
	 *
	 * @since 0.3.3
	*/
	function init_session() {
		if ( '' == session_id() )
			$this->start_php_session();

		if ( empty( $_SESSION['it_cart_buddy']['_session'] ) || $this->_token !== $_SESSION['it_cart_buddy']['_session'] )
			$this->regenerate_session_id();

		$this->load_products();
		$this->load_session_data();
	}

	/**
	 * Starts a PHP session with some basic safety mechanisms.
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function start_php_session() {
		session_start();
	}

	/**
	 * Add's actions and filters used with Sessions
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function register_hooks() {
		//add_action( 'init', array( $this, 'show_session_data' ) );
	}


	/**
	 * Generates a session token
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function set_session_token() {
		$token  = ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$token .= '-it-cart-buddy-' . AUTH_SALT;
		$this->_token = md5( $token );
	}

	/**
	 * Regenerates the session id for a added level of security
	 *
	 * @since 0.3.3.
	 * @return void
	*/
	function regenerate_session_id() {
		session_regenerate_id();
		$_SESSION['it_cart_buddy']['_session'] = $this->_token;
	}

	/**
	 * Deletes the Session
	 *
	 * @since 0.3.3
	 * @param boolean $restart  try to restart the session after destroying it? default is yes
	 * @return void
	*/
	function delete_session( $restart=true ) {
		if ( ! empty( $_SESSION['it_cart_buddy'] ) )
			unset( $_SESSION['it_cart_buddy'] );
		if ( $restart )
			$this->init_session();
	}

	/**
	 * Loads $_SESSION['it_cart_buddy']['products'] into $this->_products
	 *
	 * @since 0.3.3
	 * @return void
	*/
	private function load_products() {
		$products = empty( $_SESSION['it_cart_buddy']['products'] ) ? array() : $_SESSION['it_cart_buddy']['products'];
		$this->_products = $products;
	}

	/**
	 * Loads $_SESSION['it_cart_buddy']['data'] into $this->_session_data
	 *
	 * @since 0.3.3
	 * @return void
	*/
	private function load_session_data() {
		$data = empty( $_SESSION['it_cart_buddy']['data'] ) ? array() : $_SESSION['it_cart_buddy']['data'];
		$this->_session_data = $data;
	}

	/**
	 * Get Session Data 
	 *
	 * @since 0.3.3
	 * @return array $_session_data property
	*/
	function get_data() {
		return $this->_session_data;
	}

	/**
	 * Adds sesson data to the array
	 *
	 * This will add it directly to the SESSION's data array and reload the object's variable
	 *
	 * @since 0.3.7
	 * @param mixed $data data as passed by the shopping cart
	 * @param mixed $key optional identifier for the data.
	 * @return void 
	*/
	function add_session_data( $data, $key=false ) {

		if ( ! empty( $key ) )
			$_SESSION['it_cart_buddy']['data'][$key] = $data;
		else
			$_SESSION['it_cart_buddy']['data'][] = $data;
		$this->load_session_data();
	}

	/**
	 * Removes data from session_data array in the PHP Session
	 *
	 * @since 0.3.7
	 * @param mixed $key the array key storing the data 
	 * @return boolean
	*/
	function remove_data( $key ) {
		if ( isset( $_SESSION['it_cart_buddy']['data'][$key] ) ) {
			unset( $_SESSION['it_cart_buddy']['data'][$key] );
			$this->load_session_data();
			$return true
		}
		return false;
	}

	/**
	 * Removes all data from the session
	 *
	 * @since 0.3.7
	 * @return array the $_session_data property
	*/
	function reset_data() {
		$_SESSION['it_cart_buddy']['data'] = array();
		$this->load_session_data();
		return true;
	}

	/**
	 * Get products
	 *
	 * @since 0.3.3
	 * @return array $_products property
	*/
	function get_products() {
		if ( ! empty( $this->_products ) )
			return $this->_products;
		return false;
	}

	/**
	 * Adds a product to the product array
	 *
	 * This will add it directly to the SESSION array and reload the object variable
	 *
	 * @since 0.3.3
	 * @param mixed $product product data as passed by the shopping cart
	 * @param mixed $key optional identifier for the product.
	 * @return void 
	*/
	function add_product( $product, $key=false ) {

		if ( ! empty( $key ) )
			$_SESSION['it_cart_buddy']['products'][$key] = $product;
		else
			$_SESSION['it_cart_buddy']['products'][] = $product;
		$this->load_products();
	}

	/**
	 * Removes a product from products array in the PHP Session
	 *
	 * @since 0.3.3
	 * @param mixed $key the array key storing the product
	 * @return boolean
	*/
	function remove_product( $key ) {
		if ( isset( $_SESSION['it_cart_buddy']['products'][$key] ) ) {
			unset( $_SESSION['it_cart_buddy']['products'][$key] );
			$this->load_products();
			$return true
		}
		return false;
	}

	/**
	 * Removes all products from the session
	 *
	 * @since 0.3.3
	 * @return array the $_products_property
	*/
	function reset_products() {
		$_SESSION['it_cart_buddy']['products'] = array();
		$this->load_products();
		return true;
	}
}
$GLOBALS['it_cart_buddy']['session'] = new IT_Cart_Buddy_Session();
