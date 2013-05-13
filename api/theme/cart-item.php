<?php
/**
 * Cart Item class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Cart_Item implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'cart-item';

	/**
	 * The current cart item
	 * @var array
	 * @since 0.4.0
	*/
	public $_cart_item = false;

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	public $_tag_map = array(
		'title'    => 'title',
		'remove'   => 'remove',
		'quantity' => 'quantity',
		'price'    => 'price',
	);

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Cart_Item() {
		$this->_cart_item = empty( $_SESSION['it_exchange']['cart-item'] ) ? false : $_SESSION['it_exchange']['cart-item'];
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 0.4.0
	 * 
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * Returns the title element / var based on format option
	 *
	 * @since 0.4.0
	 *
	*/
	function title( $options=array() ) {
		return 'title goes-here';
	}

	/**
	 * Returns the remove from cart element / var based on format option
	 *
	 * @since 0.4.0
	 *
	*/
	function remove( $options=array() ) {
		return 'remove-from-cart goes-here';
	}

	/**
	 * Returns the quantity element / var based on format option
	 *
	 * @since 0.4.0
	 *
	*/
	function quantity( $options=array() ) {
		return 'quantity goes-here';
	}

	/**
	 * Returns the price element / var based on format option
	 *
	 * @since 0.4.0
	 *
	*/
	function price( $options=array() ) {
		return 'price goes-here';
	}
}
