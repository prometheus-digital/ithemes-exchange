<?php
/**
 * This will control email messages with any product types that register email message support.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 0.4.0
 * @package IT_Exchange
*/


class IT_Exchange_Product_Feature_Inventory extends IT_Exchange_Product_Feature_Abstract {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function IT_Exchange_Product_Feature_Inventory( $args=array() ) {
		parent::__construct( $args );
		// Decrease inventory on purchase
		add_action( 'it_exchange_add_transaction_success', array( $this, 'decrease_inventory_on_purchase' ) );
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
		$product_feature_enable_value = it_exchange_get_product_feature( $product->ID, 'inventory', array( 'setting' => 'enabled' ) );
		$product_feature_value = it_exchange_get_product_feature( $product->ID, 'inventory' );

		// Set description
		$description = __( 'Use this to set the product\'s current inventory number.', 'LION' );
		$description = apply_filters( 'it_exchange_product_inventory_metabox_description', $description );

		?>
			<?php if ( $description ) : ?>
				<p class="intro-description"><?php echo $description; ?></p>
			<?php endif; ?>
			<p>
				<input type="checkbox" id="it-exchange-enable-product-inventory" class="it-exchange-checkbox-enable" name="it-exchange-enable-product-inventory" <?php checked( 'yes', $product_feature_enable_value ); ?> /> <label for="it-exchange-enable-product-inventory"><?php _e( 'Enable Inventory Tracking for this Product', 'LION' ); ?></label><br />
			</p>
			<p class="it-exchange-enable-product-inventory<?php echo ( $product_feature_enable_value == 'no' ) ? ' hide-if-js' : '' ?>">
				<label for="it-exchange-product-inventory"><?php _e( 'Current Inventory', 'LION' ); ?></label>
				<input type="number" id="it-exchange-product-inventory" name="it-exchange-product-inventory" value="<?php esc_attr_e( $product_feature_value ); ?>" />
				<br /><span class="description"><?php _e( 'Leave blank for unlimited.', 'LION' ); ?></span>
			</p>
		<?php
	}

	/**
	 * This saves the value
	 *
	 * @since 0.3.8
	 *
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
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'inventory' ) )
			return;

        // Save option for checkbox allowing quantity
        if ( empty( $_POST['it-exchange-enable-product-inventory'] ) )
			it_exchange_update_product_feature( $product_id, 'inventory', 'no', array( 'setting' => 'enabled' ) );
        else
			it_exchange_update_product_feature( $product_id, 'inventory', 'yes', array( 'setting' => 'enabled' ) );

		if ( isset( $_POST['it-exchange-product-inventory'] ) )
			it_exchange_update_product_feature( $product_id, 'inventory', $_POST['it-exchange-product-inventory'] );

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
		// Using options to determine if we're setting the enabled setting or the actual max_number setting
		$defaults = array(
			'setting' => 'inventory',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Only accept settings for max_number (default) or 'enabled' (checkbox)
		if ( 'inventory' == $options['setting'] ) {
			$new_value = empty( $new_value ) && !is_numeric( $new_value ) ? '' : absint( $new_value );
			update_post_meta( $product_id, '_it-exchange-product-inventory', $new_value );
			return true;
		} else if ( 'enabled' == $options['setting'] ) {
			// Enabled setting must be yes or no.
			if ( ! in_array( $new_value, array( 'yes', 'no' ) ) )
				$new_value = 'yes';
			update_post_meta( $product_id, '_it-exchange-product-enable-inventory', $new_value );
			return true;
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

        // Using options to determine if we're getting the enabled setting or the actual inventory number
        $defaults = array(
            'setting' => 'inventory',
        );
        $options = ITUtility::merge_defaults( $options, $defaults );

        if ( 'enabled' == $options['setting'] ) {
            $enabled = get_post_meta( $product_id, '_it-exchange-product-enable-inventory', true );
            if ( ! in_array( $enabled, array( 'yes', 'no' ) ) )
                $enabled = 'no';
            return $enabled;
        } else if ( 'inventory' == $options['setting'] ) {
            if ( it_exchange_product_supports_feature( $product_id, 'inventory' ) )
                return get_post_meta( $product_id, '_it-exchange-product-inventory', true );
        }
        return false;
	}

	/**
	 * Does the product have this feature?
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_has_feature( $result, $product_id, $options=array() ) {
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
	function product_supports_feature( $result, $product_id, $options=array() ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( it_exchange_product_type_supports_feature( $product_type, 'inventory' ) ) {
			if ( 'yes' === it_exchange_get_product_feature( $product_id, 'inventory', array( 'setting' => 'enabled' ) ) )
				return true;
		} else {
			return false;
		}
	}

	/**
	 * Decreases inventory at purchase
	 *
	 * @since 0.4.13
	 *
	 * @param interger $transaction_id the id of the transaction
	 * @return void
	*/
	function decrease_inventory_on_purchase( $transaction_id ) {
		if ( ! $products = it_exchange_get_transaction_products( $transaction_id ) )
			return;

		// Loop through products
		foreach( $products as $cart_id => $data ) {
			if ( ! it_exchange_product_supports_feature( $data['product_id'], 'inventory' ) )
				continue;

			$count     = $data['count'];
			$inventory = it_exchange_get_product_feature( $data['product_id'], 'inventory' );
			$updated   = absint( $inventory - $count );
			it_exchange_update_product_feature( $data['product_id'], 'inventory', $updated );
		}
	}

}
$IT_Exchange_Product_Feature_Inventory = new IT_Exchange_Product_Feature_Inventory( array( 'slug' => 'inventory', 'description' => __( 'The current inventory number', 'LION' ) ) );
