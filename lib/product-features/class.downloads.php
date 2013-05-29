<?php
/**
 * This will associate downloads with any product types who register download support.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 0.4.0
 * @package IT_Exchange
*/


class IT_Exchange_Product_Feature_Downloads {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 0.4.0
	 * @return void
	 * @todo remove it_exchange_enabled_addons_loaded action???
	*/
	function IT_Exchange_Product_Feature_Downloads() {
		if ( is_admin() ) {
			add_action( 'init', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'init', array( $this, 'register_downloads_post_type' ) );
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_downloads', array( $this, 'save_feature' ), 9, 2 );
		add_filter( 'it_exchange_get_product_feature_downloads', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_downloads', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_downloads', array( $this, 'product_supports_feature') , 9, 2 );
			
		//We want to do this sooner than 10
		add_filter( 'it_exchange_add_transaction_success', array( $this, 'add_transaction_hash_to_product' ), 5, 2 );
	}
	
	/**
	 * Adds transaction hashes to the products in a transaction.
	 *
	 * @since 0.4.0
	 *
	 * @param object the cart data
	 * @param integer the transaction id
	 * @return updated cart data with the download hashes
	*/
	function add_transaction_hash_to_product( $transaction_object, $transaction_id ) {
			
		foreach( (array) $transaction_object->products as $object ) {
			// If this is a downloadable product, generate a hash
			if ( $this->product_has_feature( 'false', $object['product_id'] ) ) {
						
				// Grab existing downloads for each product in transaction
				$existing_downloads = it_exchange_get_product_feature( $object['product_id'], 'downloads' );
				
				// Loop through downloads and create hash for each
				foreach( $existing_downloads as $id => $data ) {
					while ( !in_array( $hash = wp_hash( time() ), (array)get_post_meta( $id, '_it_exchange_download_hashes' ) ) ) {
						add_post_meta( $id, '_it_exchange_download_hashes', $hash );
					}
					$object['product_download_hashes'][$hash] = $id;
				}
			}
		}
		return $transaction_object;
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 0.4.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'downloads';
		$description = 'Downloadable files associated with a product';
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) {
			it_exchange_add_feature_support_to_product_type( 'downloads', $params['slug'] );
		}
	}

	/**
	 * Register's the metabox for any product type that supports the feature
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function init_feature_metaboxes() {
		// Abort if there are not product addon's currently enabled.
		if ( ! $product_addons = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) ) )
			return;

		// Loop through product types and register a metabox if it supports the feature 
		foreach( $product_addons as $slug => $args ) {
			if ( it_exchange_product_type_supports_feature( $slug, 'downloads' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $slug, array( $this, 'register_metabox' ) );
		}
	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports this feature
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-product-downloads', __( 'Product Downloads', 'LION' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_normal', 'low' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the feature for this product
		$existing_downloads = it_exchange_get_product_feature( $product->ID, 'downloads' );
		?>
			<div class="downloads-label-add">
				<label>Files</label>
				<div class="download-add-new right">
					<a href class="button">Add New</a>
				</div>
			</div>
			<div class="downloads-list-wrapper">
				<div class="downloads-list-titles">
					<div class="download-item columns-wrapper">
						<div class="download-name column col-4-12">
							<span>Name</span>
						</div>
						<div class="download-source column col-7-12">
							<span>Source</span>
						</div>
					</div>
				</div>
				<div class="downloads-list">
					<!-- New download items start. -->
					<div class="download-item download-item-clone columns-wrapper hidden">
						<div class="download-name column col-4-12">
							<input type="text" name="" autocomplete="off" class="" placeholder="<?php esc_attr_e( __( 'Name', 'LION' ) ); ?>" value="" />
						</div>
						<div class="download-source column col-7-12">
							<input type="url" name="" autocomplete="off" class="" placeholder="<?php esc_attr_e( __( 'http://', 'LION' ) ); ?>" value="" />
							<a href class="it-exchange-upload-digital-download">Upload</a>
						</div>
						<div class="download-remove column col-1-12">
							<a href="#" class="it-exchange-delete-new-digital-download">&times;</a>
						</div>
					</div>
					<!-- New download items end. -->
					<?php if ( empty( $existing_downloads ) ) : ?>
						<script type="text/javascript" charset="utf-8">
							var it_exchange_new_download_interation = 1;
						</script>
						<div class="download-item columns-wrapper" id="download-item-0">
							<div class="download-name column col-4-12">
								<input type="text" name="it-exchange-digital-downloads[0][name]" autocomplete="off" class="" placeholder="<?php esc_attr_e( __( 'Name', 'LION' ) ); ?>" value="" />
							</div>
							<div class="download-source column col-7-12">
								<input type="url" name="it-exchange-digital-downloads[0][source]" autocomplete="off" class="" placeholder="<?php esc_attr_e( __( 'http://', 'LION' ) ); ?>" value="" />
								<a href class="it-exchange-upload-digital-download">Upload</a>
							</div>
							<div class="download-remove column col-1-12">
								<a href="#" class="it-exchange-delete-new-digital-download">&times;</a>
							</div>
						</div>
					<?php else : ?>
						<script type="text/javascript" charset="utf-8">
							var it_exchange_new_download_interation = 0;
						</script>
						<?php foreach( $existing_downloads as $id => $data ) : ?>
							<div id="download-item-<?php esc_attr_e( $id ); ?>" class="download-item download-exists columns-wrapper">
								<input type="hidden" name="it-exchange-digital-downloads[<?php esc_attr_e( $id ); ?>][id]" value="<?php esc_attr_e( $data['id'] ); ?>" />
								<div class="download-name column col-4-12">
									<input type="text" name="it-exchange-digital-downloads[<?php esc_attr_e( $id ); ?>][name]" class="not-empty" value="<?php esc_attr_e( $data['name'] ); ?>" />
								</div>
								<div class="download-source column col-7-12">
									<input type="text" name="it-exchange-digital-downloads[<?php esc_attr_e( $id ); ?>][source]" class="not-empty" value="<?php esc_attr_e( $data['source'] ); ?>" />
									<a href class="it-exchange-upload-digital-download">Upload</a>
								</div>
								<div class="download-remove column col-1-12">
									<input id="it-exchange-digital-downloads-delete-<?php esc_attr_e( $id ); ?>" class="hide-if-js" type="checkbox" name="it-exchange-digital-downloads[<?php esc_attr_e( $id ); ?>][delete]" value="true" />
									<a href="#" class="it-exchange-delete-digital-download" data-checkbox-id="it-exchange-digital-downloads-delete-<?php esc_attr_e( $id ); ?>">&times;</a>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		<?php
	}

	/**
	 * This saves the downloads value
	 *
	 * @since 0.3.8
	 * @param object $post wp post object
	 * @return void
	*/
	function save_feature_on_product_save() {
		// Abort if we can't determine a product type
		if ( ! $product_type = it_exchange_get_product_type() )
			return;

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		if ( ! $product_id )
			return;

		// Abort if this product type doesn't support downloads
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'downloads' ) )
			return;
		
		$previous_downloads = it_exchange_get_product_feature( $product_id, 'downloads' );
		
		//Delete Non-Existant Downloads
		if ( !empty( $previous_downloads ) && is_array( $previous_downloads ) ) {
			foreach( $previous_downloads as $download_id => $data ) {
				if ( !array_key_exists( $download_id, $_POST['it-exchange-digital-downloads'] ) )
					wp_delete_post( $download_id, true );
			}
		}

		//Add/Update Existnat Downloads
		if ( ! empty( $_POST['it-exchange-digital-downloads'] ) && is_array( $_POST['it-exchange-digital-downloads'] ) ) {
			foreach ( (array) $_POST['it-exchange-digital-downloads'] as $download ) {
	
				$data = array(
					'product_id'  => $product_id,
					'download_id' => empty( $download['id'] ) ? false : trim( $download['id'] ),
					'source'      => empty( $download['source'] ) ? false : trim( $download['source'] ),
					'name'        => empty( $download['name'] ) ? false : trim( $download['name'] ),
				);
	
				if ( ! empty( $product_id ) && ! empty( $data['source'] ) && ! empty( $data['name'] ) )
					it_exchange_update_product_feature( $product_id, 'downloads', $data );
					
			}
		}
		
	}

	/**
	 * This updates the feature for a product
	 *
	 * @todo Validate product id and new value 
	 *
	 * @since 0.4.0
	 * @param integer $product_id the product id
	 * @param mixed $new_value the new value 
	 * @return bolean
	*/
	function save_feature( $product_id, $new_value ) {

		// Format data coming from $new_value
		$data = array(
			'post_type'   => 'it_exchange_download',
			'post_status' => 'publish',
			'post_title'  => $new_value['name'],
			'post_parent' => $new_value['product_id'],
		);

		// Add download id if we're updating an existing one.
		if ( ! empty( $new_value['download_id'] ) )
			$data['ID'] = $new_value['download_id'];

		// Remove our save_post action so we don't hit and endless loop
		remove_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		if ( $download_id = wp_insert_post( $data ) ) {
			// Save the download
			update_post_meta( $download_id, '_it-exchange-download-info', $new_value );
		}
		// Add our action back
		add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
	}

	/**
	 * Return the product's features
	 *
	 * @since 0.4.0
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_feature( $existing, $product_id ) {
		//$value = get_post_meta( $product_id, '_it-exchange-downloads', false );

		$args = array(
			'post_parent' => $product_id,
			'post_type'   => 'it_exchange_download',
			'post_status' => 'publish',
		);

		if ( $download_posts = get_posts( $args ) ) {
			$downloads = array();
			foreach( $download_posts as $post ) {
				$post_meta  = get_post_meta( $post->ID, '_it-exchange-download-info', true );
				$source     = empty( $post_meta['source'] ) ? false : $post_meta['source'];
				//$limit      = empty( $post_meta['limit'] ) ? false : $post_meta['limit'];
				//$expiration = empty( $post_meta['expiration'] ) ? false : $post_meta['expiration'];

				$downloads[$post->ID] = array(
					'id'     => $post->ID,
					'name'   => $post->post_title,
					'source' => $source,
					//'limit'    => $limit,
					//'expiration' => $expiration,
				);
			}
			return $downloads;
		}
		return false;
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_has_feature( $result, $product_id ) {
		// Does this product type support this feature?
		if ( false === $this->product_supports_feature( false, $product_id ) )
			return false;
		return (boolean) $this->get_feature( false, $product_id );
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can 
	 * support a feature but might not have the feature set.
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_supports_feature( $result, $product_id ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		return it_exchange_product_type_supports_feature( $product_type, 'downloads' );
	}

	/**
	 * Registers the downloads post type
	 *
	 * @since 0.4.0
	 * @since return void
	*/
	function register_downloads_post_type() {
		$post_type = 'it_exchange_download';
		$labels    = array(
			'name'          => __( 'Exchange Downloads', 'LION' ),
			'singular_name' => __( 'Download', 'LION' ),
		);  
		$options = array(
			'labels' => $labels,
			'description' => __( 'An iThemes Exchange Post Type for storing all Downloads in the system', 'LION' ),
			'public'      => false,
			'show_ui'     => false,
			'show_in_nav_menus' => false,
			'show_in_menu'      => false,
			'show_in_admin_bar' => false,
			'hierarchical'      => false,
			'supports'          => array( // Support everything but page-attributes for add-on flexibility
				'title', 'editor', 'author', 'custom-fields',
			),  
			'register_meta_box_cb' => array( $this, 'meta_box_callback' ),
		);  
		register_post_type( $post_type, $options );
    }
}
$IT_Exchange_Product_Feature_Downloads = new IT_Exchange_Product_Feature_Downloads();
