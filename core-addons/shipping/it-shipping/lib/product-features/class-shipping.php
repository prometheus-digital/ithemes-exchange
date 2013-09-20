<?php
/**
 * This will associate shipping with any product types who register download support.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since CHANGEME
 * @package IT_Exchange
*/


class IT_Exchange_Product_Feature_Shipping {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since CHANGEME
	 * @return void
	*/
	function IT_Exchange_Product_Feature_Shipping() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			//add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'register_feature_support' ) );
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_physical_products' ) );
		/**
		add_action( 'init', array( $this, 'register_downloads_post_type' ) );
		add_action( 'it_exchange_update_product_feature_shipping', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_shipping', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_shipping', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_shipping', array( $this, 'product_supports_feature') , 9, 2 );
		add_filter( 'template_redirect', array( $this, 'handle_download_pickup_request' ) );
			
		//We want to do this sooner than 10
		add_action( 'it_exchange_add_transaction_success', array( $this, 'add_transaction_hash_to_product' ), 5 );
		*/
	}
	
	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since CHANGEME
	*/
	function register_feature_support() {
		// Register the product feature
		$slug        = 'shipping';
		$description = 'Adds shipping fields to a product';
		it_exchange_register_product_feature( $slug, $description );
	}

	/**
	 * Register downloads to the Physical Products product type add-on by default
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function add_feature_support_to_physical_products() {
		if ( it_exchange_is_addon_enabled( 'physical-product-type' ) )
			it_exchange_add_feature_support_to_product_type( 'shipping', 'physical-product-type' );
	}

	/**
	 * Register's the metabox for any product type that supports the feature
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function init_feature_metaboxes() {
		
		global $post;
		
		if ( isset( $_REQUEST['post_type'] ) ) {
			$post_type = $_REQUEST['post_type'];
		} else {
			if ( isset( $_REQUEST['post'] ) )
				$post_id = (int) $_REQUEST['post'];
			elseif ( isset( $_REQUEST['post_ID'] ) )
				$post_id = (int) $_REQUEST['post_ID'];
			else
				$post_id = 0;

			if ( $post_id )
				$post = get_post( $post_id );

			if ( isset( $post ) && !empty( $post ) )
				$post_type = $post->post_type;
		}
			
		if ( !empty( $_REQUEST['it-exchange-product-type'] ) )
			$product_type = $_REQUEST['it-exchange-product-type'];
		else
			$product_type = it_exchange_get_product_type( $post );
		
		if ( !empty( $post_type ) && 'it_exchange_prod' === $post_type ) {
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'shipping' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array( $this, 'register_metabox' ) );
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
		add_meta_box( 'it-exchange-product-shipping', __( 'Product Shipping', 'LION' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_normal', 'low' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product          = it_exchange_get_product( $post );
		?>
		<div class="shipping-header">
			<div class="shipping-label">
				<label for="it-exchange-flat-rate-shipping-cost"><?php _e( 'Shipping', 'LION' ); ?> <span class="tip" title="<?php _e( 'Set shipping details for the product here.', 'LION' ); ?>">i</span></label>
			</div>

			<div class="shipping-toggle">
					<label id="it-exchange-shipping-disabled-label" for="it-exchange-shipping-disabled">
						<input type="checkbox" id="it-exchange-shipping-disabled" name="it-exchange-shipping-disabled" />
						<?php _e( 'Disable shipping for this product', 'LION' ); ?>
						<span class="tip" title="<?php _e( 'Check this box to indicate that shipping is not needed for this product.', 'LION' ); ?>">i</span>
					</label>
			</div>
		</div>
		<!-- Glenn you will need to put logic here to determine if the hidden class needs to be applied based on whether the disable toggle is checked or not. -->
		<div class="shipping-wrapper <?php // echo 'hidden'; ?>">
			<div class="shipping-feature shipping-core">
				<div class="standard-shipping-flat-rate-cost">
					<label for="it-exchange-flat-rate-shipping-cost"><?php _e( 'Flat Rate Shipping Cost', 'LION' ); ?> <span class="tip" title="<?php _e( 'Shipping costs for this product. Multiplied by quantity purchased.', 'LION' ); ?>">i</span></label>
					<input type="text" id="it-exchange-flat-rate-shipping-cost" name="it-exchange-flat-rate-shipping-cost" class="input-money-small" value="$5.00"/>
				</div>
			</div>

			<div class="shipping-feature shipping-enabled-methods">
				<ul>
					<li>
						<label id="it-exchange-shipping-override-methods-label" for="it-exchange-shipping-override-methods">
							<input type="checkbox" id="it-exchange-shipping-override-methods" name="it-exchange-shipping-override-methods" /> <?php _e( 'Override Available Shipping Methods', 'LION' ); ?>
						</label>
						<!-- Glenn you will need to put logic here to determine if the hidden class needs to be applied based on whether the disable toggle is checked or not. -->
						<ul class="shipping-methods <?php echo 'hidden'; ?>">
							<li>
								<label id="it-exchange-shipping-override-aaa-method-label" for="it-exchange-shipping-override-aaa-method">
									<input type="checkbox" id="it-exchange-shipping-override-aaa-method" name="it-exchange-shipping-override-aaa-methods" /> <?php _e( 'FedEx Ground', 'LION' ); ?>
								</label>
							</li>
							<li>
								<label id="it-exchange-shipping-override-aaa-method-label" for="it-exchange-shipping-override-aaa-method">
									<input type="checkbox" id="it-exchange-shipping-override-aaa-method" name="it-exchange-shipping-override-aaa-methods" /> <?php _e( 'FedEx Air', 'LION' ); ?>
								</label>
							</li>
							<li>
								<label id="it-exchange-shipping-override-aaa-method-label" for="it-exchange-shipping-override-aaa-method">
									<input type="checkbox" id="it-exchange-shipping-override-aaa-method" name="it-exchange-shipping-override-aaa-methods" /> <?php _e( 'UPS Air', 'LION' ); ?>
								</label>
							</li>
						</ul>
					</li>
				</ul>

			</div>

			<div class="shipping-feature shipping-weight-dimensions columns-wrapper">
				<div class="shipping-weight column">
					<label><?php _e( 'Weight', 'LION' ); ?> <span class="tip" title="<?php _e( 'Weight of the package. Used to calculate shipping costs.', 'LION' ); ?>">i</span></label>
					<input type="text" id="it-exchange-shipping-weight" name="it-exchange-weight" class="small-input" value="12"/>
					<span class="it-exchange-shipping-weight-format">lbs</span>
				</div>
				<div class="shipping-dimensions column">
					<label><?php _e( 'Dimensions', 'LION' ); ?> <span class="tip" title="<?php _e( 'Size of the package: length, width and height of the package. Used to calculate shipping costs.', 'LION' ); ?>">i</span></label>
					<input type="text" id="it-exchange-shipping-length" name="it-exchange-length" class="small-input" value="48"/>
					<span class="it-exchange-shipping-dimensions-times">&times;</span>
					<input type="text" id="it-exchange-shipping-width" name="it-exchange-width" class="small-input" value="18"/>
					<span class="it-exchange-shipping-dimensions-times">&times;</span>
					<input type="text" id="it-exchange-shipping-height" name="it-exchange-height" class="small-input" value="4"/>
					<span class="it-exchange-shipping-dimensions-format">inches</span>
				</div>
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
		
		// Update Expires Meta
		$expires= isset( $_POST['it-exchange-digital-downloads-expires'] ) ? $_POST['it-exchange-digital-downloads-expires'] : 0;
		it_exchange_update_product_feature( $product_id, 'downloads', $expires, array( 'setting' => 'expires' ) );

		// Update Expire Int Meta
		$expire_int = isset( $_POST['it-exchange-digital-downloads-expire-int'] ) ? $_POST['it-exchange-digital-downloads-expire-int'] : 30;
		it_exchange_update_product_feature( $product_id, 'downloads', $expire_int, array( 'setting' => 'expire-int' ) );

		// Update Expire Units Meta
		$expire_units = isset( $_POST['it-exchange-digital-downloads-expire-units'] ) ? $_POST['it-exchange-digital-downloads-expire-units'] : 'days';
		it_exchange_update_product_feature( $product_id, 'downloads', $expire_units, array( 'setting' => 'expire-units' ) );

		// Update Download limit Meta
		$download_limit = isset( $_POST['it-exchange-digital-downloads-download-limit'] ) ? $_POST['it-exchange-digital-downloads-download-limit'] : 0;
		it_exchange_update_product_feature( $product_id, 'downloads', $download_limit, array( 'setting' => 'limit' ) );

		// Grab previously saved downloads
		$previous_downloads = it_exchange_get_product_feature( $product_id, 'downloads' );
		
		//Delete Non-Existant Downloads
		if ( !empty( $previous_downloads ) && is_array( $previous_downloads ) ) {
			foreach( $previous_downloads as $download_id => $data ) {
				if ( !array_key_exists( $download_id, $_POST['it-exchange-digital-downloads'] ) )
					wp_delete_post( $download_id, true );
			}
		}

		//Add/Update Existant Downloads
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
	 * @since 0.4.0
	 *
	 * @param integer $product_id the product id
	 * @param mixed $new_value the new value 
	 * @return bolean
	*/
	function save_feature( $product_id, $new_value, $options=array() ) {

		if ( ! it_exchange_get_product( $product_id ) )
			return false;

        // Using options to determine if we're setting the download limit or adding/updating files
        $defaults = array(
            'setting' => 'files',
        );
        $options = ITUtility::merge_defaults( $options, $defaults );

		if ( 'files' == $options['setting'] ) {
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
		} else {
			$meta = get_post_meta( $product_id, '_it-exchange-download-meta', true );
			if ( 'limit' == $options['setting'] ) {
				$meta['download-limit'] = $new_value;
			} else if ( 'expires' == $options['setting'] ) {
				$meta['expires'] = (boolean) $new_value;
			} else if ( 'expire-int' == $options['setting'] ) {
				$meta['expire-int'] = $new_value;
			} else if ( 'expire-units' == $options['setting'] ) {
				$meta['expire-units'] = $new_value;
			}
			update_post_meta( $product_id, '_it-exchange-download-meta', $meta );	
		}
	}

	/**
	 * Return the product's features
	 *
	 * @since 0.4.0
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_feature( $existing, $product_id, $options=array() ) {

        // Using options to determine if we're getting the download limit or adding/updating files
        $defaults = array(
            'setting' => 'files',
        );
        $options = ITUtility::merge_defaults( $options, $defaults );

		if ( 'files' == $options['setting'] ) {
			$args = array(
				'post_parent' => $product_id,
				'post_type'   => 'it_exchange_download',
				'post_status' => 'publish',
			);

			if ( $download_posts = get_posts( $args ) ) {
				$downloads = array();
				foreach( $download_posts as $post ) {
					$post_meta      = get_post_meta( $post->ID, '_it-exchange-download-info', true );
					$source         = empty( $post_meta['source'] ) ? false : $post_meta['source'];
					$expires        = it_exchange_get_product_feature( $product_id, 'downloads', array( 'setting' => 'expires' ) ); 
					$expire_int     = it_exchange_get_product_feature( $product_id, 'downloads', array( 'setting' => 'expire-int' ) ); 
					$expire_units   = it_exchange_get_product_feature( $product_id, 'downloads', array( 'setting' => 'expire-units' ) ); 
					$download_limit = it_exchange_get_product_feature( $product_id, 'downloads', array( 'setting' => 'limit' ) ); 

					$downloads[$post->ID] = array(
						'id'             => $post->ID,
						'name'           => $post->post_title,
						'source'         => $source,
						'expires'        => $expires,
						'expire_int'     => $expire_int,
						'expire_units'   => $expire_units,
						'download_limit' => $download_limit,
					);
				}
				return $downloads;
			}
		} else if ( 'limit' == $options['setting'] ) {
			$meta = get_post_meta( $product_id, '_it-exchange-download-meta', true );
			return empty( $meta['download-limit'] ) ? 0 : absint( $meta['download-limit'] );
		} else if ( 'expires' == $options['setting'] ) {
			$meta = get_post_meta( $product_id, '_it-exchange-download-meta', true );
			return ! empty( $meta['expires'] );
		} else if ( 'expire-int' == $options['setting'] ) {
			$meta = get_post_meta( $product_id, '_it-exchange-download-meta', true );
			return empty( $meta['expire-int'] ) ? 30 : absint( $meta['expire-int'] );
		} else if ( 'expire-units' == $options['setting'] ) {
			$meta = get_post_meta( $product_id, '_it-exchange-download-meta', true );
			return empty( $meta['expire-units'] ) ? 'days' : $meta['expire-units'];
		} else if ( 'limit' == $options['setting'] ) {
			$meta = get_post_meta( $product_id, '_it-exchange-download-meta', true );
			return empty( $meta['download-limit'] ) ? 'days' : $meta['download-limit'];
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
	function product_has_feature( $result, $product_id, $options=array() ) {
		// Does this product type support this feature?
		if ( false === $this->product_supports_feature( false, $product_id, $options ) )
			return false;
		return (boolean) $this->get_feature( false, $product_id, $options );
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

	/**
	 * If a pickup request is made for a download, do our thing
	 *
	 * 1) Confirm the download hash is legit
	 * 2) Confirm the download hash belongs to the current user
	 * 3) Confirm the download limit isn't up
	 * 4) Deliver the file
	 * 5) Update meta data like download count and download limit
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function handle_download_pickup_request() {
		// Abort if not looking for a download
		if ( empty( $_GET['it-exchange-download'] ) )
			return;

		// Abort with message if hash isn't found
		if ( ! $hash_data = it_exchange_get_download_data_from_hash( $_GET['it-exchange-download'] ) ) {
			it_exchange_add_message( 'error', __( 'Download not found', 'LION' ) );
			$url = apply_filters( 'it_exchange_download_error_url', it_exchange_get_page_url( 'store' ) );
			wp_redirect( $url );
			die();
		}
		
		// Get addon product type addon settings @todo move this setting to product-feature for downloads
		$settings = it_exchange_get_option( 'addon_digital_downloads' );
		// In the event that the admin never visited the settings page to register defaults
		if ( empty( $settings ) ) {
			add_filter( 'it_storage_get_defaults_exchange_addon_digital_downloads', array( 'IT_Exchange_Digital_Downloads_Add_On', 'set_default_settings' ) );
			$settings = it_exchange_get_option( 'addon_digital_downloads', true );
		}

		// If user isn't logged in, redirect them to login and bring them back when complete
		if ( ! empty( $settings['require-user-login'] ) && ! is_user_logged_in() ) {
			$redirect_url = site_url() . '?it-exchange-download=' . $hash_data['hash'];
			it_exchange_add_session_data( 'login_redirect', $redirect_url );
			wp_redirect( it_exchange_get_page_url( 'login' ) );
			die();
		}

		// If transaction isn't cleared for delivery of product, don't give them the refund
		if ( ! it_exchange_transaction_is_cleared_for_delivery( $hash_data['transaction_id'] ) ) {
			it_exchange_add_message( 'error', __( 'The transaction this download is attached to is not valid for download', 'LION' ) );
			$redirect_url = apply_filters( 'it_exchange_redirect_transaction_not_cleared_to_pickup_file', it_exchange_get_page_url( 'downloads' ) );
			wp_redirect( $redirect_url );
			die();
		}

		if ( $settings['require-user-login'] ) {
			// If user doesn't belong to the download, and isn't an admin, send them to their downloads page.
			$customer = it_exchange_get_current_customer();
			if ( empty( $customer->id ) || ( $customer->id != $hash_data['customer_id'] && ! current_user_can( 'administrator' ) ) ) {
				it_exchange_add_message( 'error', __( 'You are not allowed to download this file.', 'LION' ) );
				$redirect_url = apply_filters( 'it_exchange_redirect_no_permission_to_pickup_file', it_exchange_get_page_url( 'downloads' ) );
				wp_redirect( $redirect_url );
				die();
			}
		}

		// If download limit has been met, redirect to their downloads page
		if ( ! empty( $hash_data['download_limit'] ) && $hash_data['downloads'] >= $hash_data['download_limit'] ) {
			it_exchange_add_message( 'error', __( 'Download limit reached. Unable to download this file.', 'LION' ) );
			$redirect_url = apply_filters( 'it_exchange_redirect_no_permission_to_pickup_file', it_exchange_get_page_url( 'downloads' ) );
			wp_redirect( $redirect_url );
			die();
		}

		// If download expiration has passed, redirect to their downloads page
		if ( ! empty( $hash_data['expires'] ) && $hash_data['expire_time'] < ( strtotime( 'tomorrow' ) ) ) {
			it_exchange_add_message( 'error', __( 'Download expiration reached. Unable to download this file.', 'LION' ) );
			$redirect_url = apply_filters( 'it_exchange_redirect_no_permission_to_pickup_file', it_exchange_get_page_url( 'downloads' ) );
			wp_redirect( $redirect_url );
			die();
		}

		// Attempt to serve the file
		it_exchange_serve_product_download( $hash_data );
		die();
	}
}
$IT_Exchange_Product_Feature_Shipping= new IT_Exchange_Product_Feature_Shipping();
