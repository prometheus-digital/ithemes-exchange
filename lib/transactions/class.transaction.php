<?php
/**
 * This file holds the class for an iThemes Exchange Transaction
 *
 * @package IT_Exchange
 * @since 0.3.3
*/

/**
 * Merges a WP Post with iThemes Exchange Transaction data
 *
 * @since 0.3.3
*/
class IT_Exchange_Transaction {

	// WP Post Type Properties
	var $ID;
	var $post_author;
	var $post_date;
	var $post_date_gmt;
	var $post_content;
	var $post_title;
	var $post_excerpt;
	var $post_status;
	var $comment_status;
	var $ping_status;
	var $post_password;
	var $post_name;
	var $to_ping;
	var $pinged;
	var $post_modified;
	var $post_modified_gmt;
	var $post_content_filtered;
	var $post_parent;
	var $guid;
	var $menu_order;
	var $post_type;
	var $post_mime_type;
	var $comment_count;

	/**
	 * @param string $transaction_method The transaction method for this transaction
	 * @since 0.3.3
	*/
	var $transaction_method;


	/**
	 * @param array $transaction_supports what features does this transaction support
	 * @since 0.3.3
	*/
	var $transaction_supports;

	/**
	 * @param array $transaction_data  any custom data registered by the transaction-method for this transaction
	 * @since 0.3.3
	*/
	var $transaction_data = array();

	/**
	 * Constructor. Loads post data and transaction data
	 *
	 * @since 0.3.3
	 * @param mixed $post  wp post id or post object. optional.
	 * @return void
	*/
	function IT_Exchange_Transaction( $post=false ) {
		
		// If not an object, try to grab the WP object
		if ( ! is_object( $post ) )
			$post = get_post( (int) $post );

		// Ensure that $post is a WP_Post object
		if ( is_object( $post ) && 'WP_Post' != get_class( $post ) )
			$post = false;

		// Ensure this is a transaction post type
		if ( 'it_exchange_tran' != get_post_type( $post ) )
			$post = false;

		// Return a WP Error if we don't have the $post object by this point
		if ( ! $post )
			return new WP_Error( 'it-exchange-transaction-not-a-wp-post', __( 'The IT_Exchange_Transaction class must have a WP post object or ID passed to its constructor', 'LION' ) );

		// Grab the $post object vars and populate this objects vars
		foreach( (array) get_object_vars( $post ) as $var => $value ) {
			$this->$var = $value;
		}

		// Set the transaction method
		$this->set_transaction_method();

		// Set the transaction data
		if ( did_action( 'init' ) )
			$this->set_transaction_supports_and_data();
		else
			add_action( 'init', array( $this, 'set_transaction_supports_and_data' ) );


		// Set supports for new and edit screens
		if ( did_action( 'admin_init' ) )
			$this->set_add_edit_screen_supports();
		else
			add_action( 'admin_init', array( $this, 'set_add_edit_screen_supports' ) );
			
	}

	/**
	 * Sets the transaction_method property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 0.3.3
	*/
	function set_transaction_method() {
		global $pagenow;
		if ( ! $transaction_method = get_post_meta( $this->ID, '_it_exchange_transaction_method', true ) ) {
			if ( is_admin() && 'post-new.php' == $pagenow && ! empty( $_GET['transaction-method'] ) )	
				$transaction_method = $_GET['transaction-method'];		
		}
		$this->transaction_method = $transaction_method;
	}

	/**
	 * Gets the transaction_status property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 0.4.0
	*/
	function get_transaction_status() {
		return get_post_meta( $this->ID, '_it_exchange_transaction_status', true );
	}
	
	/**
	 * Updates the transaction_status property.
	 *
	 * If the custom value is already set, it uses that.
	 * If the custom value is not set and we're on post-add.php, check for a URL param
	 *
	 * @since 0.4.0
	*/
	function update_transaction_status( $new_status ) {
		update_post_meta( $this->ID, '_it_exchange_transaction_status', $new_status );
	}
	
	/**
	 * Add the transaction refund amount.
	 *
	 * @since 0.4.0
	*/
	function add_transaction_refund( $refund ) {
		$args = array(
			'amount' => $refund,
			'date'   => date_i18n( 'Y-m-d H:i:s' ),
		);
		add_post_meta( $this->ID, '_it_exchange_transaction_refunds', $args );
	}
	
	/**
	 * Get the transaction refunds.
	 *
	 * @since 0.4.0
	*/
	function get_transaction_refunds() {
		return get_post_meta( $this->ID, '_it_exchange_transaction_refunds' );
	}

	/**
	 * Sets the transaction_data property from appropriate transaction-method options and assoicated post_meta
	 *
	 * @ since 0.3.2
	 * @return void
	*/
    function set_transaction_supports_and_data() {
        // Get transaction_method options

		/****** @todo THIS WAS OLD CARTBUDDY. WAS SUPPOSED TO BE 'FLEXIBLE' BUT I DON'T LIKE IT. ***/
        if ( $transaction_method_options = it_exchange_get_transaction_method_options( $this->transaction_method ) ) { 
            if ( ! empty( $transaction_method_options['supports'] ) ) { 
                foreach( $transaction_method_options['supports'] as $feature => $params ) { 

                    // Set the transaction_supports array
                    $this->transaction_supports[$feature] = $params;

                    // transaction_data only contains post_meta data
                    if ( 'post_meta' != $params['componant'] )
                        continue;

                    // Set transaction_data to post_meta value or feature default
                    if ( $value = get_post_meta( $this->ID, $params['key'], true ) ) 
                        $this->transaction_data[$params['key']] = $value;
                    else
                        $this->transaction_data[$params['key']] = $this->transaction_supports[$feature]['default'];
                }   
            }   
        }   
		/** END OF OLD. BEGINNING OF TEMP **/

		// Set status
		$this->status = get_post_meta( $this->ID, '_it_exchange_transaction_status', true );

		// Set refunds
		$this->refunds = $this->get_transaction_refunds();

		// Set customer ID
		$this->customer_id = get_post_meta( $this->ID, '_it_exchange_customer_id', true );

		// Set Cart information
		$this->cart_details = get_post_meta( $this->ID, '_it_exchange_transaction_object', true );
	}


    /** 
     * Sets the supports array for the post_type.
     *
     * @since 0.3.3
    */
    function set_add_edit_screen_supports() {
		global $pagenow;
        $supports = array(
            'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields',
            'comments', 'revisions', 'post-formats',
        );  

        // If is_admin and is post-new.php or post.php, only register supports for current transaction-method
        if ( 'post-new.php' != $pagenow && 'post.php' != $pagenow )
			return; // Don't remove any if not on post-new / or post.php

		if ( $addon = it_exchange_get_addon( $this->transaction_method ) ) { 
			// Remove any supports args that the transaction add-on does not want.
			foreach( $supports as $option ) { 
                if ( empty( $addon['options']['supports'][$option] ) )
					remove_post_type_support( 'it_exchange_tran', $option );
            }   
        }   
    }  
}
