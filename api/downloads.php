<?php
/**
 * API functions for downloads
 * @package IT_Exchange
 * @package IT_Exchange
 * @since 0.4.0
*/

/**
 * Generate a unique hash for file downloads
 *
 * @since 0.4.0
 *
 * @param integer $download_id the WP post ID for the download
 * @return string the hash
*/
function it_exchange_create_download_hash( $download_id ) {
	// Initial attempt at creating unique hash
	$hash = wp_hash( time() . $download_id );

	// Confirm it doesn't exist. Retry if we find hash already exists
	while ( get_post_meta( $download_id, '_download_hash_' . $hash ) ) { 
		$hash = wp_hash( time() . $download_id );
	}

	return $hash;
}

/** 
 * Adds metadata associated with a transaction to the download
 *
 * Doesn't work if hash already exists
 *
 * @since 0.4.0
 *
 * @param integer $download_id ID of the download post
 * @param string $hash
 * @param array $hash_data
*/
function it_exchange_add_download_hash_data( $download_id, $hash, $hash_data ) {
	// If hash already exists, something went wrong
	if ( it_exchange_get_download_data_from_hash( $hash ) )
		return false;

	// Attach hash and data to downlod
	if ( $pm_id = update_post_meta( $download_id, '_download_hash_' . $hash, $hash_data ) ) {

		// Update the hash index for the transaction
		it_exchange_update_transaction_download_hash_index( $hash_data['transaction_id'], $hash_data['product_id'], $download_id, $hash );

		return $pm_id;
	}

	return false;
}

/** 
 * Updates meta-data associated with a specific file hash
 *
 * Hash has to already exist
 *
 * @since 0.4.0
 *
 * @param string $hash
 * @param array $data
 * @return array updated data
*/
function it_exchange_update_download_hash_data( $hash, $data ) {
	if ( ! $old_data = it_exchange_get_download_data_from_hash( $hash ) )
		return;

	/** @todo finish this **/
	ITUtility::print_r($old_data);die();
}

/** 
 * Get a requested file hash
 *
 * @since 0.4.0
 *
 * @param string $hash The hash holding the meta for the file
 * @return array hash data
*/
function it_exchange_get_download_data_from_hash( $hash ) {
	global $wpdb;
	$meta_key = '_download_hash_' . $hash;
	$sql = $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s LIMIT 1;", $meta_key );
	if ( $data = $wpdb->get_var( $sql ) )
		return $data;

	return false;
}

/**
 * Grabs download data for a specific transaction / product / file combination
 *
 * Fourth param is opitonal.
 *
 * @param  mixed   $transaction transaction ID or object
 * @param  array   $transaction_product this is the product array found in cart_details property in the transaction object
 * @param  integer $download_id the id of the download attached to the product passed in param 2
 * @param  string  $data_key optional key for specific download data
 * @return mixed   array of all data or a specific key
*/
function it_exchange_get_download_data_from_transaction_product( $transaction, $transaction_product, $download_id, $data_key=false ) {
	// Grab the transaction or return false
	if ( false === ( $transaction = it_exchange_get_transaction( $transaction ) ) )
		return false;

	// Grab the product key from the tranaction product or return false
	if ( false === ( $product_id = empty( $transaction_product['product_id'] ) ? false : $transaction_product['product_id'] ) )
		return false;

	// Grab an array of all download hashes for this transaction, grouped by product
	$transaction_hash_index = it_exchange_get_transaction_download_hash_index( $transaction->ID );

	// If the requested download / product / transaction combination is in the hash_index, use that to look up the hash data
	if ( ! empty( $transaction_hash_index[$product_id][$download_id] ) 
		&& $hash_data = it_exchange_get_download_data_from_hash( $transaction_hash_index[$product_id][$download_id] ) ) {

		// Unserialize the hash data
		$hash_data = maybe_unserialize( $hash_data );

		// Return a single key if requested and set
		if ( ! empty( $data_key ) )
			return isset( $hash_data[$data_key] ) ? $hash_data[$data_key] : false;

		// Return the whole array if no key was requested
		return $hash_data;
	}

	// Return false if we made it this far
	return false;
}

/**
 * Get all download hashes attached to a specific transaction
 *
 * @since 0.4.0
 *
 * @param mixed $transaction ID or object
 * @return array
*/
function it_exchange_get_transaction_download_hash_index( $transaction ) {
	$transaction = it_exchange_get_transaction( $transaction );
	$hash_index = get_post_meta( $transaction->ID, '_it_exchange_download_hash_index', true );
	return empty( $hash_index ) ? array() : $hash_index;
}

/**
 * This updates the index of hashes per product per transaction stored in the transaction
 *
 * @param mixed   $transaction         transaction ID or object
 * @param array   $transaction_product this is the product array found in cart_details property in the transaction object
 * @param integer $download_id         the id of the download attached to the product passed in param 2
 * @param string  $hash                the has we're adding to the index
 * @return boolean
*/
function it_exchange_update_transaction_download_hash_index( $transaction, $product, $download_id, $hash ) {
	// Grab transaction object
	if ( ! $transaction = it_exchange_get_transaction( $transaction ) )
		return false;

	// Grab existing hash index
	$hash_index = (array) it_exchange_get_transaction_download_hash_index( $transaction );

	// Add hash to existing hash index
	$hash_index[$product][$download_id] = $hash;

	// Update hash index
	update_post_meta( $transaction->ID, '_it_exchange_download_hash_index', $hash_index );
	return true;
}

/**
 * Deletes a has from a transaction index
 *
 * This function doesn't care what product its attached to. If it finds it, it deletes it.
 *
 * @param mixed  $transaction the ID or object
 * @param string $hash        the hash we're looking for
 * @return boolean
*/
function it_exchange_delete_hash_from_transaction_hash_index( $transaction, $hash ) {
	// Grab transaction object
	if ( ! $transaction = it_exchange_get_transaction( $transaction ) )
		return false;

	// Grab existing hash index
	$hash_index = (array) it_exchange_get_transaction_download_hash_index( $transaction );
	
	// Delete if it exists
	foreach( $hash_index as $product ) {
		if ( in_array( $hash, $product ) )
			unset( $hash_index[$product][$hash] );
	}

	// Update
	update_post_meta( $transaction->ID, '_it_exchange_download_hash_index', $hash_index );
	return true;
}

/**
 * Clear the hash index for this transaction
 *
 * @since 0.4.0
 *
 * @param mixed $transaction ID or object
 * @return boolean
*/
function it_exchange_clear_transaction_hash_index( $transaction ) {
	// Grab transaction object
	if ( ! $transaction = it_exchange_get_transaction( $transaction ) )
		return false;

	delete_post_meta( $transaction->ID, '_it_exchange_download_hash_index' );
	return true;
}

/** 
 * Serves a file from its URL
 *
 * Uses wp_remote_get to locate the file and force download.
 *
 * @since 0.4.0
 *
 * @param array $download_info download hash data
 * @return void;
*/
function it_exchange_serve_product_download( $hash_data ) { 

	// Grab the download info
	$download_info = get_post_meta( $hash_data['file_id'], '_it-exchange-download-info', true );
	$url           = empty( $download_info['source'] ) ? false : $download_info['source'];

	/** 
	 * Allow addons to override this.
	 * If you override this, you need to tick the download counts with it_exchange_increment_download_count( $download_info )
	*/
	do_action( 'it_exchange_serve_download_file', $download_info );


	// Attempt to grab file
	$filename = basename( $url );
	if ( $response = wp_remote_get( $url ) ) { 
		if ( ! is_wp_error( $response ) ) { 
			$valid_response_codes = array(
				200,
			);  
			$valid_response_codes = apply_filters( 'it_exchange_valid_response_codes_for_downloadable_files', $valid_response_codes, $download_info );
			if ( in_array( wp_remote_retrieve_response_code( $response ), (array) $valid_response_codes ) ) { 

				// Increment Download count if not Admin
				it_exchange_increment_download_count( $download_info );

				// Get Resource Headers
				$headers = wp_remote_retrieve_headers( $response );

				// White list of headers to pass from original resource
				$passthru_headers = array(
					'accept-ranges',
					'content-length',
					'content-type',
				);  
				apply_filters( 'it_exchange_file_download_passthru_headers', $passthru_headers, $download_info );

				// Set Headers for download from original resource
				foreach ( (array) $passthru_headers as $header ) { 
					if ( isset( $headers[$header] ) ) 
						header( esc_attr( $header ) . ': ' . esc_attr( $headers[$header] ) );
				}   

				// Force download
				header( 'Content-disposition: attachment; filename="' . $filename . '"' );

				// Deliver file
				echo wp_remote_retrieve_body( $response );
				die();
			}
			die( __( 'Download Error: Invalid response', 'LION' ) );
		} else {
			die( __( 'Download Error:', 'LION' ) . ' ' . $response->get_error_message() );
		}
	}
}

/**
 * Increments download counts
 *
 * @since 0.4.0
 *
 * @param array   $download_info file hash data
 * @param boolean $increment_admin_downloads Default is false
 * @return void
*/
function it_exchange_increment_download_count( $download_info, $increment_admin_downloads=false ) {

}
