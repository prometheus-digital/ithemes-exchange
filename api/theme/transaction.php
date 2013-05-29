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
		'status'   => 'status',
		'date'     => 'date',
		'total'    => 'total',
		'products' => 'products',
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
	 * Returns the transaction status
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

		return $options['before'] . it_exchange_get_transaction_status_label( $this->_transaction ) . $options['after'];
	}

	/**
	 * Returns the transaction date
	 *
	 * @since 0.4.0
	 *
	 * @param array $options output options
	 * @return string
	*/
	function date( $options=array() ) {
		// Set options
		$defaults      = array(
			'before' => '', 
			'after'  => '', 
			'format' => get_option('date_format'),
		);  
		$options = ITUtility::merge_defaults( $options, $defaults );

		return $options['before'] . it_exchange_get_transaction_date( $this->_transaction, $options['format'] ) . $options['after'];
	}

	/**
	 * Returns the transaction total
	 *
	 * @since 0.4.0
	 *
	 * @param array $options output options
	 * @return string
	*/
	function total( $options=array() ) {
		// Set options
		$defaults      = array(
			'before'          => '', 
			'after'           => '', 
			'format_currency' => true,
		);  
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		return $options['before'] . it_exchange_get_transaction_total( $this->_transaction, $options['format_currency'] ) . $options['after'];
	}

    /** 
     * This loops through the products GLOBAL and updates the product global.
     *
     * It return false when it reaches the last product
     * If the has flag has been passed, it just returns a boolean
     *
     * @since 0.4.0
     * @return string
    */
    function products( $options=array() ) { 
        // Return boolean if has flag was set
        if ( $options['has'] )
            return count( it_exchange_get_transaction_products( $this->_transaction ) ) > 0 ; 

        // If we made it here, we're doing a loop of products for the current query.
        // This will init/reset the products global and loop through them. the /api/theme/product.php file will handle individual products.
        if ( empty( $GLOBALS['it_exchange']['products'] ) ) { 
            $GLOBALS['it_exchange']['products'] = it_exchange_get_transaction_products( $this->_transaction );
            $GLOBALS['it_exchange']['product'] = reset( $GLOBALS['it_exchange']['products'] );
            return true;
        } else {
            if ( next( $GLOBALS['it_exchange']['products'] ) ) { 
                $GLOBALS['it_exchange']['product'] = current( $GLOBALS['it_exchange']['products'] );
                return true;
            } else {
                $GLOBALS['it_exchange']['product'] = false;
                return false;
            }   
        }   
        end( $GLOBALS['it_exchange']['products'] );
        $GLOBALS['it_exchange']['product'] = false;
        return false;
    }
}
