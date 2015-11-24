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
class IT_Exchange_Shortcodes {

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
		add_shortcode( 'it_exchange_sw', array( $this, 'sw_callback' ) );
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
	public function sw_callback( $atts ) {

		$atts = shortcode_atts( array( 'product' => null, 'description' => false ), $atts, 'it_exchange_sw' );

		$product = it_exchange_get_product( $atts['product'] );

		if ( ! $product ) {
			if ( current_user_can( 'edit_post', $GLOBALS['post']->ID ) ) {
				return __( "Invalid product ID.", 'it-l10n-ithemes-exchange' );
			}

			return '';
		}

		it_exchange_set_product( $product->ID );
		$this->product = $product;

		if ( ! $atts['description'] ) {
			$this->hide_parts[] = 'description';
		}

		add_filter( 'it_exchange_super_widget_empty_product_id', array( $this, 'set_sw_product_id' ) );
		add_filter( 'it_exchange_get_content_product_product_info_loop_elements', array( $this, 'hide_template_parts' ) );

		ob_start();

		it_exchange_get_template_part( 'content-product/loops/product-info' );

		$html = ob_get_clean();

		remove_filter( 'it_exchange_super_widget_empty_product_id', array( $this, 'set_sw_product_id' ) );
		remove_filter( 'it_exchange_get_content_product_product_info_loop_elements', array( $this, 'hide_template_parts' ) );

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
	public function hide_template_parts( $parts ) {

		foreach ( $this->hide_parts as $part ) {

			$index = array_search( $part, $parts );

			if ( $index !== false ) {
				unset( $parts[$index] );
			}
		}

		return $parts;
	}
}


new IT_Exchange_Shortcodes();
