<?php

/**
 * Cart Item class for THEME API
 *
 * @since 0.4.0
 */
class IT_Theme_API_Cart_Item extends IT_Theme_API_Line_Item {

	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	 */
	private $_context = 'cart-item';

	/** @var ITE_Cart|null */
	private $cart;

	/**
	 * The current cart item
	 * @var array
	 * @since 0.4.0
	 */
	public $_cart_item = false;

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 */
	public function __construct() {

		parent::__construct();

		$this->cart       = it_exchange_get_requested_cart_and_check_auth() ?: it_exchange_get_current_cart();
		$this->_cart_item = empty( $GLOBALS['it_exchange']['cart-item'] ) ? false : $GLOBALS['it_exchange']['cart-item'];

		if ( $this->_cart_item ) {
			$this->item = $this->cart->get_item( 'product', $this->_cart_item['product_cart_id'] );
		} else {
			$this->item = null;
		}

		$this->_tag_map += array(
			'title'            => 'title',
			'remove'           => 'remove',
			'price'            => 'price',
			'subtotal'         => 'sub_total',
			'purchasequantity' => 'supports_purchase_quantity',
			'permalink'        => 'permalink',
			'featuredimage'    => 'featured_image',
			'images'           => 'product_images',
		);
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	public function IT_Theme_API_Cart_Item() {

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
	public function get_api_context() {
		return $this->_context;
	}

	/**
	 * Returns the remove from cart element / var based on format option
	 *
	 * @since 0.4.0
	 *
	 * @return string
	 */
	public function remove( $options = array() ) {

		// Set options
		$defaults = array(
			'before' => '',
			'after'  => '',
			'format' => 'html',
			'class'  => false,
			'label'  => _x( '&times;', 'html representation for multiplication symbol (x)', 'it-l10n-ithemes-exchange' ),
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		if ( $this->cart && ! $this->cart->is_current() ) {
			return '';
		}

		// Force link in SuperWidget
		$options['format'] = it_exchange_in_superwidget() ? 'link' : $options['format'];

		$var_key    = it_exchange_get_field_name( 'remove_product_from_cart' );
		$var_value  = $this->item->get_id();
		$core_class = ( it_exchange_is_multi_item_cart_allowed() && it_exchange_get_cart_products_count() > 1 ) ? 'remove-cart-item' : 'it-exchange-empty-cart';
		$class      = empty( $options['class'] ) ? $core_class : $core_class . ' ' . esc_attr( $options['class'] );

		switch ( $options['format'] ) {
			case 'var_key' :
				$output = $var_key;
				break;
			case 'var_value' :
				$output = $var_value;
				break;
			case 'checkbox' :
				$var_value = esc_attr( $var_value );
				$class     = esc_attr( $class );
				$output    = $options['before'];
				$output .= "<input type='checkbox' name='{$var_key}[]' value='{$var_value}' class='{$class}' />";
				$output .= $options['label'] . $options['after'];
				break;
			case 'link' :
			default :
				$data       = it_exchange_in_superwidget() ? 'data-cart-product-id="' . esc_attr( $var_value ) . '" ' : '';
				$nonce_var  = apply_filters( 'it_exchange_remove_product_from_cart_nonce_var', '_wpnonce' );
				$session_id = it_exchange_get_session_id();
				$url        = it_exchange_clean_query_args();
				$url        = add_query_arg( $var_key, $var_value, $url );
				$url        = add_query_arg( $nonce_var, wp_create_nonce( 'it-exchange-cart-action-' . $session_id ), $url );

				$output = $options['before'];
				$output .= '<a href="' . esc_url( $url ) . '" ' . $data . 'class="' . $class . '" >' . esc_attr( $options['label'] ) . '</a>';
				$output .= $options['after'];
				break;
		}

		return $output;
	}

	/**
	 * Returns the title element / var based on format option
	 *
	 * @since 0.4.0
	 *
	 * @return string
	 */
	public function title( $options = array() ) {
		// Set options
		$defaults = array(
			'before' => '',
			'after'  => '',
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		return $options['before'] . apply_filters( 'it_exchange_api_theme_cart_item_title', $this->item->get_name(), $this->item ) . $options['after'];
	}

	/**
	 * Returns the quantity element / var based on format option
	 *
	 * @since 0.4.0
	 *
	 * @return int
	 */
	public function quantity( $options = array() ) {
		// Set options
		$defaults = array(
			'before' => '',
			'after'  => '',
			'format' => 'text-field',
			'class'  => 'product-cart-quantity',
			'label'  => '',
		);

		$options   = ITUtility::merge_defaults( $options, $defaults );
		$var_key   = it_exchange_get_field_name( 'product_purchase_quantity' );
		$var_value = $this->item->get_quantity();

		if ( $this->item instanceof ITE_Quantity_Modifiable_Item && $this->item->is_quantity_modifiable() && $this->cart && ! $this->cart->is_current() ) {

			switch ( $options['format'] ) {
				case 'var_key' :
					$output = $var_key;
					break;
				case 'var_value' :
					$output = $var_value;
					break;
				case 'text-field' :
				default :
					$output = $options['before'];

					$max_quantity = $this->item->get_max_quantity_available();

					$max   = ! empty( $max_quantity ) ? 'max="' . $max_quantity . '"' : '';
					$id    = esc_attr( $this->item->get_id() );
					$class = esc_attr( $options['class'] );
					$output .= "<input type='number' min='1' {$max} data-cart-product-id='$id' name='{$var_key}[$id]' value='{$var_value}' class='{$class}' />";

					$output .= $options['after'];
					break;
			}

		} else {
			$output = $var_value;
		}

		return $output;
	}

	/**
	 * Returns the price element / var based on format option
	 *
	 * @since 0.4.0
	 *
	 * @return string
	 */
	public function price( $options = array() ) {
		return apply_filters( 'it_exchange_api_theme_cart_item_price', it_exchange_format_price( $this->item->get_amount() ), $this->_cart_item, $this->item );
	}

	/**
	 * Returns the subtotal for the cart item (price * quantity)
	 *
	 * @since 0.4.0
	 *
	 * @return string
	 */
	public function sub_total( $options = array() ) {

		$total = $this->item->get_total();

		if ( $this->item instanceof ITE_Aggregate_Line_Item ) {
			$total_negative = $this->item->get_line_items()->filter( function ( ITE_Line_Item $item ) {
				return ! $item->is_summary_only() && $item->get_total() < 0;
			} )->total();

			$total += $total_negative * -1;
		}

		return apply_filters( 'it_exchange_api_theme_cart_item_sub_total', it_exchange_format_price( $total ), $this->_cart_item, $this->item );
	}

	/**
	 * Returns boolean. Does this cart item support a purchase quantity
	 *
	 * @since 0.4.0
	 *
	 * @return bool
	 */
	public function supports_purchase_quantity( $options = array() ) {
		return $this->item->get_product()->supports_feature( 'purchase-quantity' );
	}

	/**
	 * Returns URL for cart item
	 *
	 * @since 0.4.4
	 *
	 * @return string
	 */
	public function permalink( $options = array() ) {
		return get_permalink( $this->item->get_product()->ID );
	}

	/**
	 * The product's product images
	 *
	 * @since 0.4.12
	 *
	 * @return string
	 */
	public function product_images( $options = array() ) {

		// Get the real product item or return empty
		if ( ! $this->item ) {
			return false;
		}

		// Return boolean if has flag was set
		if ( $options['supports'] ) {
			return $this->item->get_product()->supports_feature( 'product-images' );
		}

		// Return boolean if has flag was set
		if ( $options['has'] ) {
			return $this->item->get_product()->has_feature( 'product-images' );
		}

		if ( $this->item->get_product()->supports_feature( 'product-images' ) && $this->item->get_product()->has_feature( 'product-images' ) ) {

			$defaults = array(
				'size' => 'all'
			);
			$options  = ITUtility::merge_defaults( $options, $defaults );

			$images         = array();
			$product_images = $this->item->get_product()->get_feature( 'product-images' );
			$image_sizes    = get_intermediate_image_sizes();

			foreach ( $product_images as $image_id ) {
				foreach ( $image_sizes as $size ) {
					$images[ $size ] = wp_get_attachment_image_src( $image_id, $size );
				}
			}

			$images['full'] = wp_get_attachment_image_src( $image_id, 'full' );

			if ( $options['size'] === 'all' ) {
				$output = $images;
			} else {
				if ( isset( $images[ $options['size'] ] ) ) {
					$output = $images[ $options['size'] ];
				} else if ( $options['size'] === 'full' ) {
					$output = $images['full'];
				} else {
					$output = __( 'Unregisterd image size.', 'it-l10n-ithemes-exchange' );
				}
			}

			return $output;
		}

		return false;
	}

	/**
	 * The product's featured image
	 *
	 * @since 0.4.12
	 *
	 * @return string
	 */
	public function featured_image( $options = array() ) {

		if ( ! $this->item instanceof ITE_Cart_Product ) {
			return false;
		}

		/** @var ITE_Cart_Product $item */
		$item = $this->item;

		// Return boolean if has flag was set
		if ( $options['supports'] ) {
			return $item->get_product()->supports_feature( 'product-images' );
		}

		// Return boolean if has flag was set
		if ( $options['has'] ) {
			return $item->get_product()->has_feature( 'product-images' );
		}

		if ( $item->get_product()->supports_feature( 'product-images' ) && $item->get_product()->has_feature( 'product-images' ) ) {

			$defaults = array(
				'size' => 'thumbnail'
			);
			$options  = ITUtility::merge_defaults( $options, $defaults );

			if ( $item->has_itemized_data( 'it_variant_combo_hash' ) ) {
				$combo_hash = $item->get_itemized_data( 'it_variant_combo_hash' );
			}

			$images_located = false;

			if ( isset( $combo_hash ) && function_exists( 'it_exchange_variants_addon_get_product_feature_controller' ) ) {

				$product_id = $item->get_param( 'product_id' );

				$variant_combos_data = it_exchange_get_variant_combo_attributes_from_hash( $product_id, $combo_hash );
				$combos_array        = empty( $variant_combos_data['combo'] ) ? array() : $variant_combos_data['combo'];
				$alt_hashes          = it_exchange_addon_get_selected_variant_alts( $combos_array, $product_id );

				$controller = it_exchange_variants_addon_get_product_feature_controller( $product_id, 'product-images', array( 'setting' => 'variants' ) );

				if ( $variant_combos_data['hash'] == $combo_hash ) {
					if ( ! empty( $controller->post_meta[ $combo_hash ]['value'] ) ) {
						$product_images = $controller->post_meta[ $combo_hash ]['value'];
						$images_located = true;
					}
				}
				// Look for alt hashes if direct match was not found
				if ( ! $images_located && ! empty( $alt_hashes ) ) {
					foreach ( $alt_hashes as $alt_hash ) {
						if ( ! empty( $controller->post_meta[ $alt_hash ]['value'] ) ) {
							$product_images = $controller->post_meta[ $alt_hash ]['value'];
							$images_located = true;
						}
					}
				}
			}

			if ( ! $images_located || ! isset( $product_images ) ) {
				$product_images = $this->item->get_product()->get_feature( 'product-images' );
			}

			$feature_image = array(
				'id'    => $product_images[0],
				'thumb' => wp_get_attachment_thumb_url( $product_images[0] ),
				'large' => wp_get_attachment_url( $product_images[0] )
			);

			if ( 'thumbnail' === $options['size'] ) {
				$img_src = $feature_image['thumb'];
			} else {
				$img_src = $feature_image['large'];
			}

			ob_start();
			?>
			<div class="it-exchange-feature-image-<?php echo $feature_image['id']; ?> it-exchange-featured-image">
				<div class="featured-image-wrapper">
					<img alt="" src="<?php echo $img_src ?>" data-src-large="<?php echo $feature_image['large'] ?>" data-src-thumb="<?php echo $feature_image['thumb'] ?>" />
				</div>
			</div>
			<?php

			return ob_get_clean();
		}

		return false;
	}
}
