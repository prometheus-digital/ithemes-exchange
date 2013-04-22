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
		add_meta_box( 'it-exchange-product-downloads', __( 'Product Downloads', 'LION' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_advanced', 'low' );
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

		// Set description
		$description = __( 'Attach any file here that you want delivered to the customer after purchasing this product', 'LION' );
		$description = apply_filters( 'it_exchange_product_downloads_metabox_description', $description );

		// Echo the form field
		echo $description;
		
		/** @todo Temporary UI **/
		?>
		<br />**UGLY TEMP UI**
		<p>
			<strong>Add Download</strong><br />
			<input type="text" name="it-exchange-digital-downloads[0][source]" /> URL<br />
			<input type="text" name="it-exchange-digital-downloads[0][name]" /> Name<br />
			<input type="text" name="it-exchange-digital-downloads[0][limit]" /> Download Limit<br />
			<input type="text" name="it-exchange-digital-downloads[0][expiration]" /> Expiration<br />
		</p>
		<hr />
		<strong>Exisiting Downloads</strong>
		<?php
		if ( ! empty( $existing_downloads ) ) {
			?>
			<table>
			<tr style="text-align:left;"><th>Delete</th><th>Name</th><th>Download Limit</th><th>Expiration</th></tr>
			<?php
			foreach( $existing_downloads as $download ) {
				echo '<tr>';
				echo '<td>';
					echo '<input type="checkbox" name="it-exchange-delete-download[]" value="' . esc_attr( $download['id'] ) . '" />';
				echo '</td>';
				echo '<td>' . esc_attr( $download['name'] ) . '</td>';
				echo '<td>' . esc_attr( $download['limit'] ) . '</td>';
				echo '<td>' . esc_attr( $download['expiration'] ) . '</td>';
				echo '</tr>';
			}
			echo '</table>';
		} else {
			echo '<p>No existing downloads</p>';
		}
	}

	/**
	 * This saves the downloads value
	 *
	 * @todo Convert to use product feature API
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

		$to_delete = empty( $_POST['it-exchange-delete-download'] ) ? array() : (array) $_POST['it-exchange-delete-download'];

		foreach( $to_delete as $download_id ) {
			wp_delete_post( $download_id );
		}

		// Abort if key for downloads option isn't set in POST data
		if ( ! isset( $_POST['it-exchange-digital-downloads'] ) )
			return;

		// Grab existing data
		//$existing_downloads = $this->get_downloads_for_product( $product_id );

		// Save Data
		foreach ( (array) $_POST['it-exchange-digital-downloads'] as $file ) {

			$source     = empty( $file['source'] ) ? false : $file['source'];
			$name       = empty( $file['name'] ) ? false : $file['name'];
			$limit      = empty( $file['limit'] ) ? false : $file['limit'];
			$expiration = empty( $file['expiration'] ) ? false : $file['expiration'];

			$data = array(
				'product_id' => $product_id,
				'source'     => $source,
				'name'       => $name,
				'limit'      => $limit,
				'expiration' => $expiration,
			);

			if ( ! empty( $product_id ) && ! empty( $source ) && ! empty( $name ) )
				it_exchange_update_product_feature( $product_id, 'downloads', $data );
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

		$data = array(
			'post_type'   => 'it_exchange_download',
			'post_status' => 'publish',
			'post_title'  => $new_value['name'],
			'post_parent' => $new_value['product_id'],
		);

		remove_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		if ( $download_id = wp_insert_post( $data ) ) {
			update_post_meta( $download_id, '_it-exchange-download-info', $new_value );
		}
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
	function get_feature( $existing, $product_id, $options ) {
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
				$limit      = empty( $post_meta['limit'] ) ? false : $post_meta['limit'];
				$expiration = empty( $post_meta['expiration'] ) ? false : $post_meta['expiration'];

				$downloads[$post->ID] = array(
					'id' => $post->ID,
					'name' => $post->post_title,
					'source' => $source,
					'limit'    => $limit,
					'expiration' => $expiration,
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
		// Does this product type support feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'downloads' ) ) 
			return false;
		return (boolean) $this->get_feature( false, $product_id );
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
