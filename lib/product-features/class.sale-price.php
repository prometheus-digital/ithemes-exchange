<?php
/**
 * Sales Price product feature.
 *
 * @since   1.32.0
 * @package IT_Exchange
 */


/**
 * Class IT_Exchange_Sales_Price
 *
 * @since 1.32.0
 */
class IT_Exchange_Sale_Price extends IT_Exchange_Product_Feature_Abstract {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$args = array(
			'slug'             => 'sale-price',
			'description'      => __( "Offer this product at a discount.", 'it-l10n-ithemes-exchange' ),
			'metabox_title'    => __( "Sale Price", 'it-l10n-ithemes-exchange' ),
			'metabox_priority' => 'high',
			'metabox_context'  => 'it_exchange_normal'
		);

		parent::__construct( $args );

		add_filter( 'it_exchange_get_cart_product_base_price', array( $this, 'override_cart_base_price' ), 0, 3 );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 1.32.0
	 *
	 * @param \WP_Post $post
	 */
	function print_metabox( $post ) {

		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		$sale_price = it_exchange_get_product_feature( $product->ID, $this->slug );

		if ( ! $product->has_feature( $this->slug ) ) {
			$sale_price = '';
		} else {
			$sale_price = it_exchange_format_price( $sale_price );
		}

		// Set description
		$description = __( 'Sale Price', 'it-l10n-ithemes-exchange' );
		$description = apply_filters( 'it_exchange_sale-price_addon_metabox_description', $description, $post );

		$settings = it_exchange_get_option( 'settings_general' );
		$currency = it_exchange_get_currency_symbol( $settings['default-currency'] );

		// Echo the form field
		do_action( 'it_exchange_before_print_metabox_sale_price', $product );
		?>
		<label for="sale-price"><?php esc_html_e( $description ); ?></label>
		<input type="text" placeholder=""
		       id="sale-price" name="it-exchange-sale-price" value="<?php esc_attr_e( $sale_price ); ?>" tabindex="3"
		       data-symbol="<?php esc_attr_e( $currency ); ?>" data-symbol-position="<?php esc_attr_e( $settings['currency-symbol-position'] ); ?>"
		       data-thousands-separator="<?php esc_attr_e( $settings['currency-thousands-separator'] ); ?>"
		       data-decimals-separator="<?php esc_attr_e( $settings['currency-decimals-separator'] ); ?>">
		<?php
		do_action( 'it_exchange_after_print_metabox_sale_price', $product );
	}

	/**
	 * This saves the value.
	 *
	 * @since 1.32.0
	 */
	public function save_feature_on_product_save() {

		// Abort if we can't determine a product type
		if ( ! $product_type = it_exchange_get_product_type() ) {
			return;
		}

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		if ( ! $product_id ) {
			return;
		}

		// Abort if this product type doesn't support base-price
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'sale-price' ) ) {
			return;
		}

		// Abort if key for base-price option isn't set in POST data
		if ( ! isset( $_POST['it-exchange-sale-price'] ) ) {
			return;
		}

		if ( isset( $_POST['it-exchange-sale-price'] ) ) {
			$new_price = $_POST['it-exchange-sale-price'];
		} else {
			$new_price = '';
		}

		it_exchange_update_product_feature( $product_id, $this->slug, $new_price );
	}

	/**
	 * This updates the feature for a product
	 *
	 * @since 1.32.0
	 *
	 * @param integer $product_id the product id
	 * @param array   $new_value  the new value
	 * @param array   $options
	 *
	 * @return boolean
	 */
	public function save_feature( $product_id, $new_value, $options = array() ) {

		$defaults = array(
			'setting' => 'sale-price'
		);

		$options = ITUtility::merge_defaults( $options, $defaults );

		if ( $options['setting'] === 'sale-price' ) {

			if ( $new_value === '' ) {
				return delete_post_meta( $product_id, '_it_exchange_sale_price' );
			}

			$new_value = it_exchange_convert_to_database_number( $new_value );

			return update_post_meta( $product_id, '_it_exchange_sale_price', $new_value );
		}

		return false;
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.32.0
	 *
	 * @param mixed   $existing   the values passed in by the WP Filter API. Ignored here.
	 * @param integer $product_id the WordPress post ID
	 * @param array   $options
	 *
	 * @return string product feature
	 */
	public function get_feature( $existing, $product_id, $options = array() ) {

		$default = array(
			'setting' => 'sale-price'
		);

		$options = ITUtility::merge_defaults( $options, $default );

		if ( $options['setting'] == 'sale-price' ) {
			return (float) it_exchange_convert_from_database_number( get_post_meta( $product_id, '_it_exchange_sale_price', true ) );
		}

		return false;
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 1.32.0
	 *
	 * @param mixed   $result Not used by core
	 * @param integer $product_id
	 * @param array   $options
	 *
	 * @return boolean
	 */
	public function product_has_feature( $result, $product_id, $options = array() ) {
		return metadata_exists( 'post', $product_id, '_it_exchange_sale_price' );
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can
	 * support a feature but might not have the feature set.
	 *
	 * @since 1.32.0
	 *
	 * @param mixed   $result Not used by core
	 * @param integer $product_id
	 * @param array   $options
	 *
	 * @return boolean
	 */
	function product_supports_feature( $result, $product_id, $options = array() ) {
		$product_type = it_exchange_get_product_type( $product_id );

		return it_exchange_product_type_supports_feature( $product_type, $this->slug );
	}

	/**
	 * Override the cart base price to the sale price, if the sale is active.
	 *
	 * We filter this very early so other extensions can properly filter the price.
	 *
	 * @since 1.32.0
	 *
	 * @param string|float $base_price
	 * @param array        $product
	 * @param bool         $format
	 *
	 * @return string|float
	 */
	public function override_cart_base_price( $base_price, $product, $format ) {

		if ( it_exchange_is_product_sale_active( $product['product_id'] ) ) {

			$base_price = it_exchange_get_product_feature( $product['product_id'], $this->slug );

			if ( $format ) {
				if ( empty( $base_price ) ) {
					$base_price = __( 'Free', 'it-l10n-ithemes-exchange' );
				} else {
					$base_price = it_exchange_format_price( $base_price );
				}
			}
		}

		return $base_price;
	}
}

new IT_Exchange_Sale_Price();