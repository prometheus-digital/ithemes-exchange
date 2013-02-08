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
		echo "regenerated";
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
	 * Get products
	 *
	 * @since 0.3.3
	 * @return array $_products property
	*/
	function get_products() {
		return $this->_products;
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
	 * Adds a product to the product array
	 *
	 * This will add it directly to the SESSION array and reload the object variable
	 *
	 * @since 0.3.3
	 * @param array $product product data
	 * @return array the $_products property
	*/
	function add_product( $product ) {
		$_SESSION['it_cart_buddy']['products'][] = $product;
		$this->load_products();
		return $this->get_products();
	}

	/**
	 * Removes a product from the $_products property by its array key
	 *
	 * @since 0.3.3
	 * @param mixed $array_key  the array key storing the product
	 * @return array the $_products property
	*/
	function remove_product( $array_key ) {
		if ( isset( $_SESSION['it_cart_buddy']['products'][$array_key] ) )
			unset( $_SESSION['it_cart_buddy']['products'][$array_key] );
		$this->load_products();
		return $this->get_products();
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
		return $this->get_products();
	}
		

function show_session_data() { if ( !isset($_GET['session'] ) ) { return; } echo "<pre>";print_r($this);echo "</pre>"; }
}
$GLOBALS['it_cart_buddy']['session'] = new IT_Cart_Buddy_Session();
