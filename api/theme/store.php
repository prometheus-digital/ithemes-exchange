<?php
/**
 * Store class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Store implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'store';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	public $_tag_map = array(
		'products'       => 'products',
		'productclasses' => 'product_classes',
	);

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	*/
	function __construct() {
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Theme_API_Store() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * This loops through the products GLOBAL and updates the product global.
	 *
	 * It return false when it reaches the last product
	 * If the has flag has been passed, it just returns a boolean
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function products( $options=array() ) {
		// Return boolean if has flag was set
		if ( $options['has'] ) {
			$settings = it_exchange_get_option( 'settings_general' );
			return count( it_exchange_get_products( apply_filters( 'it_exchange_store_get_products_args',  array( 'posts_per_page' => -1, 'order' => $settings['store-product-order'], 'orderby' => $settings['store-product-order-by'] ) ) ) ) > 0;
		}

		// If we made it here, we're doing a loop of products for the current query.
		// This will init/reset the products global and loop through them. the /api/theme/product.php file will handle individual products.
		if ( empty( $GLOBALS['it_exchange']['products'] ) ) {
			$settings = it_exchange_get_option( 'settings_general' );
			$GLOBALS['it_exchange']['products'] = it_exchange_get_products( apply_filters( 'it_exchange_store_get_products_args',  array( 'posts_per_page' => -1, 'order' => $settings['store-product-order'], 'orderby' => $settings['store-product-order-by'] ) ) );
			it_exchange_set_product( reset( $GLOBALS['it_exchange']['products'] ) );
			return true;
		} else {
			if ( next( $GLOBALS['it_exchange']['products'] ) ) {
				it_exchange_set_product( current( $GLOBALS['it_exchange']['products'] ) );
				return true;
			} else {
				$GLOBALS['it_exchange']['products'] = array();
				end( $GLOBALS['it_exchange']['products'] );
				it_exchange_set_product( false );
				return false;
			}
		}
	}

	function product_classes( $options=array() ) {

        // Return boolean if has flag was set
        if ( $options['supports'] )
            return true;

        // Return boolean if has flag was set
        if ( $options['has'] )
            return true;

		$product    = empty( $GLOBALS['it_exchange']['product'] ) ? 0 : $GLOBALS['it_exchange']['product'];
		$product_id = empty( $product->ID ) ? 0 : $product->ID;

		if ( empty( $product_id ) ) {
			return '';
		}

		$type = it_exchange_get_product_type( $product_id );

		$classes = array(
			'it-exchange-product-' . $product_id,
			'it-exchange-product-type-' . $type,
		);
		$classes = apply_filters( 'it_exchange_get_store_li_classes_for_product', $classes, $type, $product_id );

		$classes = implode( ' ', $classes );
		$classes = esc_attr( trim( $classes ) );
		return $classes;
	}
}
