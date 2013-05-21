<?php
/**
 * This will control email messages with any product types that register email message support.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 0.4.0
 * @package IT_Exchange
*/


class IT_Exchange_Product_Feature_Product_Images {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 0.4.0
	 * @return void
	 * @todo remove it_exchange_enabled_addons_loaded action???
	*/
	function IT_Exchange_Product_Feature_Product_Images() {
		if ( is_admin() ) {
			add_action( 'init', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_product-images', array( $this, 'save_feature' ), 9, 2 );
		add_filter( 'it_exchange_get_product_feature_product-images', array( $this, 'get_feature' ), 9, 2 );
		add_filter( 'it_exchange_product_has_feature_product-images', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_product-images', array( $this, 'product_supports_feature') , 9, 2 );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 0.4.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'product-images';
		$description = 'Product Images';
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) {
			it_exchange_add_feature_support_to_product_type( 'product-images', $params['slug'] );
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
			if ( it_exchange_product_type_supports_feature( $slug, 'product-images' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $slug, array( $this, 'register_metabox' ) );
		}
	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports the feature 
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-product-images', __( 'Product Images', 'LION' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_side', 'default' );
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

		$product_images = it_exchange_get_product_feature( $product->ID, 'product-images' );
		?>
        
        <label for="product-images-field">Images</label>
        <div id="it-exchange-product-images">
			<?php
            if ( !empty( $product_images ) ) {
                
                $thumb = wp_get_attachment_thumb_url( $product_images[0] );
                $large = wp_get_attachment_url( $product_images[0] );
                $src = $large;
    
                echo '<div id="it-exchange-feature-image" class="ui-droppable">';
                echo '<ul class="feature-image">';
                echo '  <li id="' . $product_images[0] . '">';
				echo '    <a class="image-edit" href="">';
				echo '      <img alt="" data-thumb="' . $thumb . '" data-large="' . $large . '" src=" ' . $src . '">';
				echo '      <span class="overlay"></span>';
				echo '    </a>';
				echo '    <span class="remove-item">×</span>';
				echo '    <input type="hidden" value="' . $product_images[0] . '" name="it-exchange-product-images[]">';
				echo '  </li>';
                echo '</ul>';
                echo '<div class="replace-feature-image"><span>Replace featured image</span></div>';
                echo '</div>';
				
                unset( $product_images[0] ); //we don't want this listed below
                
                echo '<ul id="it-exchange-gallery-images" class="ui-sortable">';
                foreach( $product_images as $image_id ) {
                    
                    $thumb = wp_get_attachment_thumb_url( $image_id );
                    $large = wp_get_attachment_url( $image_id );
                    $src = $thumb;
                    
					echo '  <li id="' . $image_id . '">';
					echo '    <a class="image-edit" href="">';
					echo '      <img alt="" data-thumb="' . $thumb . '" data-large="' . $large . '" src=" ' . $src . '">';
					echo '      <span class="overlay"></span>';
					echo '    </a>';
					echo '    <span class="remove-item">×</span>';
					echo '    <input type="hidden" value="' . $image_id . '" name="it-exchange-product-images[]">';
					echo '  </li>';
                    
                }
                echo '<li id="it-exchange-add-new-image" class="disable-sorting"><a href><span>Add Images</span></a></li>';
                echo '</ul>';
                
            } else {
                ?>
                <div id="it-exchange-feature-image" class="ui-droppable">
                <ul class="feature-image"></ul>
                <div class="replace-feature-image"><span>Replace featured image</span></div>
                </div>
                <ul id="it-exchange-gallery-images">
                    <li id="it-exchange-add-new-image" class="disable-sorting empty"><a href><span>Add Images</span></a></li>
                </ul>
                <?php
            };
            ?>
        </div>
    	<?php
	}

	/** 
	 * This saves the value
	 *
	 * @todo Convert to use product feature API
	 *
	 * @since 0.4.0
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

		// Abort if this product type doesn't support this feature 
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'product-images' ) ) 
			return;

		// Abort if key for feature option isn't set in POST data
		if ( !empty( $_POST['it-exchange-product-images'] ) ) 
			it_exchange_update_product_feature( $product_id, 'product-images', $_POST['it-exchange-product-images'] );
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
		update_post_meta( $product_id, '_it-exchange-product-images', $new_value );
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
		$value = get_post_meta( $product_id, '_it-exchange-product-images', true );
		return $value;
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
		return it_exchange_product_type_supports_feature( $product_type, 'product-images' );
	}
}
$IT_Exchange_Product_Feature_Product_Images = new IT_Exchange_Product_Feature_Product_Images();
