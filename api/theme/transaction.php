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
		'status'                 => 'status',
		'date'                   => 'date',
		'total'                  => 'total',
		'instructions'           => 'instructions',
		'products'               => 'products',
		'productattribute'       => 'product_attribute',
		'productdownloads'       => 'product_downloads',
		'productdownload'        => 'product_download',
		'productdownloadhashes'  => 'product_download_hashes',
		'productdownloadhash'    => 'product_download_hash',
	);

	/**
	 * The current transaction product
	 * @var array $_transaction_product
	 * @since 0.4.0
	*/
	public $_transaction_product = false;

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Transaction() {
		$this->_transaction                       = empty( $GLOBALS['it_exchange']['transaction'] ) ? false : $GLOBALS['it_exchange']['transaction'];
		$this->_transaction_product               = empty( $GLOBALS['it_exchange']['transaction_product'] ) ? false : $GLOBALS['it_exchange']['transaction_product'];
		$this->_transaction_product_download      = empty( $GLOBALS['it_exchange']['transaction_product_download'] ) ? false : $GLOBALS['it_exchange']['transaction_product_download'];
		$this->_transaction_product_download_hash = empty( $GLOBALS['it_exchange']['transaction_product_download_hash'] ) ? false : $GLOBALS['it_exchange']['transaction_product_download_hash'];
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
	 * Returns the transaction instructions 
	 *
	 * @since 0.4.0
	 *
	*/
	function instructions( $options=array() ) {
		// Set options
		$defaults      = array(
			'before' => '', 
			'after'  => '', 
		);  
		$options = ITUtility::merge_defaults( $options, $defaults );

		return $options['before'] . it_exchange_get_transaction_instructions( $this->_transaction ) . $options['after'];
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
     * This loops through the transaction_products GLOBAL and updates the transaction_product global.
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

        // If we made it here, we're doing a loop of transaction_products for the current query.
        // This will init/reset the transaction_products global and loop through them.
        if ( empty( $GLOBALS['it_exchange']['transaction_products'] ) ) { 
            $GLOBALS['it_exchange']['transaction_products'] = it_exchange_get_transaction_products( $this->_transaction );
            $GLOBALS['it_exchange']['transaction_product'] = reset( $GLOBALS['it_exchange']['transaction_products'] );
            return true;
        } else {
            if ( next( $GLOBALS['it_exchange']['transaction_products'] ) ) { 
                $GLOBALS['it_exchange']['transaction_product'] = current( $GLOBALS['it_exchange']['transaction_products'] );
                return true;
            } else {
				$GLOBALS['it_exchange']['transaction_products'] = array();
        		end( $GLOBALS['it_exchange']['transaction_products'] );
                $GLOBALS['it_exchange']['transaction_product'] = false;
                return false;
            }   
        }
    }

	/** 
	 * The product title
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function product_attribute( $options=array() ) { 
	
		// Set defaults
		$defaults = array(
			'wrap'         => false,
			'format'       => 'html',
			'attribute'    => false,
			'format_price' => true,
		);  
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Return empty if attribute was not provided
		if ( empty( $options['attribute'] ) )
			return '';

		// Return empty string if empty
		if ( 'description' == $options['attribute'] ) {
			$attribute = it_exchange_get_product_feature( $this->_transaction_product['product_id'], 'description' );
			if ( empty( $attribute ) )
				return '';
		} else if ( 'confirmation-url' == $options['attribute'] ) {
			$attribute = it_exchange_get_transaction_confirmation_url( $this->_transaction->ID );
		} else if ( ! $attribute = it_exchange_get_transaction_product_feature( $this->_transaction_product, $options['attribute'] ) ) {
			return '';
		}

		// Format price
		if ( (boolean) $options['format_price'] && in_array( $options['attribute'], array( 'product_subtotal', 'product_base_price' ) ) )
			$attribute = it_exchange_format_price( $attribute );

		$open_wrap  = empty( $options['wrap'] ) ? '' : '<' . esc_attr( $options['wrap'] ) . ' class="entry-title">';
		$close_wrap = empty( $options['wrap'] ) ? '' : '</' . esc_attr( $options['wrap'] ) . '>';
		$result   = ''; 

		if ( 'html' == $options['format'] )
			$result .= $open_wrap;

		$result .= $attribute;

		if ( 'html' == $options['format'] )
			$result .= $close_wrap;

		return $result;
	} 

	function product_downloads( $options=array() ) {
		// Return false if we don't have a product id
		if ( empty( $this->_transaction_product['product_id'] ) )
			return false;

		// Return boolean if we'er just checking
		if ( ! empty( $options['has'] ) )
			return it_exchange_product_has_feature( $this->_transaction_product['product_id'], 'downloads' );

		// Set product id
		$product_id = $this->_transaction_product['product_id'];

        // If we made it here, we're doing a loop of transaction_product_downloads for the current query.
        // This will init/reset the transaction_product_downloads global and loop through them.
        if ( empty( $GLOBALS['it_exchange']['transaction_product_downloads'][$product_id] ) ) { 
            $GLOBALS['it_exchange']['transaction_product_downloads'][$product_id] = it_exchange_get_product_feature( $product_id, 'downloads' );
            $GLOBALS['it_exchange']['transaction_product_download'] = reset( $GLOBALS['it_exchange']['transaction_product_downloads'][$product_id] );
            return true;
        } else {
            if ( next( $GLOBALS['it_exchange']['transaction_product_downloads'][$product_id] ) ) { 
                $GLOBALS['it_exchange']['transaction_product_download'] = current( $GLOBALS['it_exchange']['transaction_product_downloads'][$product_id] );
                return true;
            } else {
				$GLOBALS['it_exchange']['transaction_product_downloads'][$prodcut_id] = array();
				end( $GLOBALS['it_exchange']['transaction_product_downloads'][$prodcut_id] );
                $GLOBALS['it_exchange']['transaction_product_download'] = false;
                return false;
            }   
        }   
	}

	function product_download( $options=array() ) {
		if ( ! empty( $options['has'] ) )
			return (boolean) $this->_transaction_product_download;

		if ( empty( $options['attribute'] ) )
			return false;

		if ( 'title' == $options['attribute'] || 'name' == $options['attribute'] ) {
			$value = get_the_title( $this->_transaction_product_download['id'] );
		}

		return $value;
	}

	function product_download_hashes( $options=array() ) {
		// Return false if we don't have a product id
		if ( empty( $this->_transaction_product['product_id'] ) || empty( $this->_transaction_product_download ) )
			return false;

		// Return boolean if we're just checking
		if ( ! empty( $options['has'] ) )
			return (boolean) it_exchange_get_download_hashes_for_transaction_product( $this->_transaction, $this->_transaction_product, $this->_transaction_product_download['id'] ); 

		// Download ID
		$download_id = $this->_transaction_product_download['id'];

		// If we made it here, we're doing a loop of transaction_product_download_hashes for the current query.
        // This will init/reset the transaction_product_download_hashes global and loop through them.
        if ( empty( $GLOBALS['it_exchange']['transaction_product_download_hashes'][$download_id] ) ) { 
            $GLOBALS['it_exchange']['transaction_product_download_hashes'][$download_id] = it_exchange_get_download_hashes_for_transaction_product( $this->_transaction, $this->_transaction_product, $download_id );
            $GLOBALS['it_exchange']['transaction_product_download_hash'] = reset( $GLOBALS['it_exchange']['transaction_product_download_hashes'][$download_id] );
            return true;
        } else {
            if ( next( $GLOBALS['it_exchange']['transaction_product_download_hashes'][$download_id] ) ) { 
                $GLOBALS['it_exchange']['transaction_product_download_hash'] = current( $GLOBALS['it_exchange']['transaction_product_download_hashes'][$download_id] );
                return true;
            } else {
				$GLOBALS['it_exchange']['transaction_product_download_hashes'][$download_id] = array();
				end( $GLOBALS['it_exchange']['transaction_product_download_hashes'][$download_id] );
                $GLOBALS['it_exchange']['transaction_product_download_hash'] = false;
                return false;
            }   
        }
	}

	function product_download_hash( $options=array() ) {
		if ( ! empty( $options['has'] ) )
			return (boolean) $this->_transaction_product_download_hash;

		if ( ! isset( $options['attribute'] ) )
			return false;

		$defaults = array(
			'date-format' => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$hash_data = it_exchange_get_download_data_from_hash( $this->_transaction_product_download_hash );
		if ( 'title' == $options['attribute'] || 'name' == $options['attribute'] )
			$options['attribute'] = 'hash';
		else if ( 'download-limit' == $options['attribute'] )
			$options['attribute'] = 'download_limit';
		else if ( 'download-count' == $options['attribute'] )
			$options['attribute'] = 'downloads';

		if ( 'expiration-date' == $options['attribute'] ) {
			$date_format = empty( $options['date-format'] ) ? false : $options['date-format'];
			$date = it_exchange_get_download_expiration_date_from_settings( $hash_data, $this->_transaction->post_date, $date_format );
			$value = empty( $date ) ? false : $date;
		} else if ( 'downloads-remaining' == $options['attribute'] ) {
			$limit = empty( $hash_data['download_limit'] ) ? __( 'Unlimited Downloads', 'LION' ) : absint( $hash_data['download_limit'] );
			$count = empty( $hash_data['downloads'] ) ? 0 : absint( $hash_data['downloads'] );
			$remaining = ( $limit - $count );
			$value = ( $remaining < 0 ) ? 0 : $remaining;
		} else if ( 'download-url' == $options['attribute'] ) {
			$value = add_query_arg( array( 'it-exchange-download' => $hash_data['hash'] ), get_home_url() );
		} else {
			$value = isset( $hash_data[$options['attribute']] ) ? $hash_data[$options['attribute']] : false;
		}

		return $value;
	}
}
