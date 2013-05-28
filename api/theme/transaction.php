<?php
/**
 * Transaction class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Transaction implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'transaction';

	/**
	 * The current transaction
	 * @var array
	 * @since 0.4.0
	*/
	public $_transaction = false;

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	public $_tag_map = array(
		'status' => 'status',
	);

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Transaction() {
		$this->_transaction = empty( $GLOBALS['it_exchange']['transaction'] ) ? false : $GLOBALS['it_exchange']['transaction'];
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
	function status( $options=array() ) {
		// Set options
		$defaults      = array(
			'before' => '', 
			'after'  => '', 
		);  
		$options = ITUtility::merge_defaults( $options, $defaults );

		return $options['before'] . it_exchange_get_transaction_status( $this->_transaction ) . $options['after'];
	}
}
