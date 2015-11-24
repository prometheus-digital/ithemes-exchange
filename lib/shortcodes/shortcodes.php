<?php
/**
 * Contains shortcodes.
 *
 * @author    iThemes
 * @since     1.33
 */

/**
 * Class IT_Exchange_Shortcodes
 */
class IT_Exchange_SW_Shortcode {

	/**
	 * @var \IT_Exchange_Product
	 */
	private $product;

	/**
	 * @var array
	 */
	private $hide_parts = array();

	/**
	 * IT_Exchange_Shortcodes constructor.
	 */
	public function __construct() {
		add_shortcode( 'it_exchange_sw', array( $this, 'callback' ) );
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'register_feature' ) );
	}

	/**
	 * Check if this page has the shortcode in it.
	 *
	 * @since 1.33
	 *
	 * @return bool
	 */
	public static function has_shortcode() {

		global $post;

		return ( $post && has_shortcode( $post->post_content, 'it_exchange_sw' ) );
	}

	/**
	 * Register the feature with Exchange.
	 *
	 * @sine 1.33
	 */
	public function register_feature() {

		$desc = __( "Allows products to be embedded in a shortcode.", 'it-l10n-ihemes-exchange' );

		it_exchange_register_product_feature( 'sw-shortcode', $desc );
	}

	/**
	 *
	 * Super widget shortcode callback.
	 *
	 * @since 1.33
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function callback( $atts ) {

		$atts = shortcode_atts( array( 'product' => null, 'description' => false ), $atts, 'it_exchange_sw' );

		$product      = it_exchange_get_product( $atts['product'] );
		$product_type = it_exchange_get_product_type( $product->ID );

		if ( ! $product ) {
			if ( current_user_can( 'edit_post', $GLOBALS['post']->ID ) ) {
				return __( "Invalid product ID.", 'it-l10n-ithemes-exchange' );
			}

			return '';
		} else if ( ! it_exchange_product_type_supports_feature( $product_type, 'sw-shortcode' ) ) {

			if ( current_user_can( 'edit_post', $GLOBALS['post']->ID ) ) {

				return __( "This product does not support being embedded in shortcodes.", 'it-l10n-ithemes-exchange' );
			}

			return '';
		}

		it_exchange_set_product( $product->ID );
		$this->product = $product;

		if ( ! $atts['description'] ) {
			$this->hide_parts[] = 'description';
		}

		add_filter( 'it_exchange_super_widget_empty_product_id', array( $this, 'set_sw_product_id' ) );
		add_filter( 'it_exchange_get_content_product_product_info_loop_elements', array( $this, 'hide_templates' ) );

		ob_start();

		it_exchange_get_template_part( 'content-product/loops/product-info' );

		$html = ob_get_clean();

		remove_filter( 'it_exchange_super_widget_empty_product_id', array( $this, 'set_sw_product_id' ) );
		remove_filter( 'it_exchange_get_content_product_product_info_loop_elements', array( $this, 'hide_templates' ) );

		return $html;
	}

	/**
	 * Set the product ID for use in the SW when no product found.
	 *
	 * @since 1.33
	 *
	 * @param int $product
	 *
	 * @return int|bool
	 */
	public function set_sw_product_id( $product ) {

		if ( $this->product ) {
			return $this->product->ID;
		}

		return $product;
	}

	/**
	 * Hide template parts.
	 *
	 * @since 1.33
	 *
	 * @param array $parts
	 *
	 * @return array
	 */
	public function hide_templates( $parts ) {

		foreach ( $this->hide_parts as $part ) {

			$index = array_search( $part, $parts );

			if ( $index !== false ) {
				unset( $parts[ $index ] );
			}
		}

		return $parts;
	}
}

new IT_Exchange_SW_Shortcode();
