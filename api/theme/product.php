<?php
/**
 * Product class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Product implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'product';

	/**u
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	var $_tag_map = array(
		'found'               => 'found',
		'title'               => 'title',
		'permalink'           => 'permalink',
		'excerpt'             => 'excerpt',
		'description'         => 'description',
		'content'             => 'extended_description',
		'extendeddescription' => 'extended_description',
		'author'              => 'author',
		'baseprice'           => 'base_price',
		'purchasequantity'    => 'purchase_quantity',
		'inventory'           => 'inventory',
		'availability'        => 'availability',
		'isavailable'         => 'is_available',
		'visibility'          => 'visibility',
		'isvisible'           => 'is_visible',
		'images'              => 'product_images',
		'gallery'             => 'product_gallery',
		'featuredimage'       => 'featured_image',
		'downloads'           => 'downloads',
		'purchaseoptions'     => 'purchase_options',
		'buynow'              => 'buy_now',
		'addtocart'           => 'add_to_cart',
		'buynowvar'           => 'buy_now_var',
		'addtocartvar'        => 'add_to_cart_var',
	);

	/**
	 * Current product in iThemes Exchange Global
	 * @var object $product
	 * @since 0.4.0
	*/
	private $product;

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Product() {
		// Set the current global product as a property
		$this->product = empty( $GLOBALS['it_exchange']['product'] ) ? false : $GLOBALS['it_exchange']['product'];
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
	 * Returns boolean value if we have a product or not
	 *
	 * @since 0.4.0
	 *
	 * @return boolean
	*/
	function found( $options=array() ) {
		return (boolean) $this->product;
	}

	/**
	 * The product title
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function title( $options=array() ) {
		
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'title' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'title' );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'title' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'title' ) ) {

			$result   = '';
			$title    = it_exchange_get_product_feature( $this->product->ID, 'title' );
			
			$defaults = array(
				'wrap'   => 'h1',
				'format' => 'html',
			);
			$options = ITUtility::merge_defaults( $options, $defaults );

			if ( 'html' == $options['format'] )
				$result .= '<' . $options['wrap'] . ' class="entry-title">';

			$result .= $title;

			if ( 'html' == $options['format'] )
				$result .= '</' . $options['wrap'] . '>';

			return $result;
		}
		return false;
	}

	/**
	 * The permalink
	 *
	 * @since 0.4.0
	 * @return mixed
	*/
	function permalink( $options=array() ) {
		
		$permalink = empty( $this->product->ID ) ? false : get_permalink( $this->product->ID );
			
		if ( $options['has'] )
			return (boolean) $permalink;

		$result = '';
		$defaults   = array(
			'before' => '<a href="',
			'after'  => '">' . it_exchange( 'product', 'get-title', 'format=' ) . '</a>',
			'format' => 'html',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		if ( 'html' == $options['format'] )
			$result .= $options['before'];

		$result .= $permalink;

		if ( 'html' == $options['format'] )
			$result .= $options['after'];

		return $result;
	
	}

	/**
	 * The product base price
	 *
	 * @since 0.4.0
	 * @return mixed
	*/
	function base_price( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'base-price' );
			
		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'base-price' );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'base-price' )
				&& it_exchange_product_has_feature( $this->product->ID, 'base-price' ) ) {

			$result     = '';
			$base_price = it_exchange_get_product_feature( $this->product->ID, 'base-price' );
			$defaults   = array(
				'before' => '<span class="it-exchange-base-price">',
				'after'  => '</span>',
				'format' => 'html',
			);
			$options = ITUtility::merge_defaults( $options, $defaults );

			if ( 'html' == $options['format'] )
				$result .= $options['before'];

			$result .= it_exchange_format_price( $base_price );

			if ( 'html' == $options['format'] )
				$result .= $options['after'];

			return $result;
		}

		return false;
	}

	/**
	 * The product's large description
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function description( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'description' );
			
		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'description' );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'description' )
				&& it_exchange_product_has_feature( $this->product->ID, 'description' ) )
			return it_exchange_get_product_feature( $this->product->ID, 'description' );
		return false;
	}

	/**
	 * The extended description
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function extended_description( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'extended-description' );
			
		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'extended-description' );

		$result        = false;
		$extended_desc = it_exchange_get_product_feature( $this->product->ID, 'extended-description' );
		$defaults      = array(
			'before' => '<div class="entry-content">',
			'after'  => '</div>',
			'format' => 'html',
		);
		$options      = ITUtility::merge_defaults( $options, $defaults );

		if ( 'html' == $options['format'] )
			$result .= $options['before'];

		$result .= $extended_desc;

		if ( 'html' == $options['format'] )
			$result .= $options['after'];

		return $result;
	}

	/**
	 * The product's WP excerpt
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function excerpt( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'wp-excerpt' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'wp-excerpt' );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'wp-excerpt' )
				&& it_exchange_product_has_feature( $this->product->ID, 'wp-excerpt' ) )
			return it_exchange_get_product_feature( $this->product->ID, 'wp-excerpt' );
		return false;
	}

	/**
	 * The product author
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function author( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'author' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'author' );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'author' )
				&& it_exchange_product_has_feature( $this->product->ID, 'author' ) )
			return it_exchange_get_product_feature( $this->product->ID, 'author' );
		return false;
	}

	/**
	 * The product purchase quantity (max purchase option by customer) 
	 *
	 * @since 0.4.0
	 * @return integer 
	*/
	function purchase_quantity( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'purchase-quantity' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'purchase-quantity' );

		// Set options
		$defaults      = array(
			'before'      => '',
			'after'       => '',
			'format'      => 'html',
			'class'       => 'product-purchase-quantity',
		);
		$options   = ITUtility::merge_defaults( $options, $defaults );

		$class     = empty( $options['class'] ) ? '' : ' class="' . esc_attr( $options['class'] ) .'"';
		$var_key   = it_exchange_get_field_name( 'product_purchase_quantity' );

		// Is the checkbox on add/edit products unchecked to allow quantities greater than 1
		if ( it_exchange_product_supports_feature( $this->product->ID, 'purchase-quantity' ) )
			$max_quantity = it_exchange_get_product_feature( $this->product->ID, 'purchase-quantity' );
		else
			return '';
		$max_quantity = empty( $max_quantity ) ? '0' : $max_quantity;

		// Lets do some inventory checking and make sure that if we're supporing inventory, that we don't allow max to be greater than inventory
		if ( it_exchange_product_supports_feature( $this->product->ID, 'inventory' ) ) {
			// If we support inventory, but we don't have any, and we've been passed the HTML format, return empty string
			if ( ! $inventory = it_exchange_get_product_feature( $this->product->ID, 'inventory' ) )
				return '';

		// Lets check product availability and return and empty string if its not available.
		if ( ! it_exchange( 'product', 'is-available' ) )
			return '';
				
			if ( (int) $max_quantity > 0 && (int) $max_quantity > $inventory )
				$max_quantity = $inventory;
		}

		// Return requested format
		switch ( $options['format'] ) {
			case 'max-quantity' :
				return $max_quantity;
				break;
			case 'html' :
			default :
				$html  = '<input' . $class . ' type="text" name="' . esc_attr( $var_key ) . '" value="1" />' . "\n";
				$html .= '<input type="hidden" name="' . it_exchange_get_field_name( 'product_max_purchase_quantity' ) . '[' . esc_attr( $this->product->ID ) . ']" value="' . ( $max_quantity ) . '" />';
				return $html;
				break;
		}
	}

	/**
	 * The product's current inventory
	 *
	 * @since 0.4.0
	 * @return integer 
	*/
	function inventory( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'inventory' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'inventory' );

		if ( false !== it_exchange_product_supports_feature( $this->product->ID, 'inventory' )
				&& false !== it_exchange_product_has_feature( $this->product->ID, 'inventory' ) )
			return it_exchange_get_product_feature( $this->product->ID, 'inventory' );
		return false;
	}

	/**
	 * The product's dates purchase availability
	 *
	 * Use type of 'start', 'end', 'both', either in options
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function availability( $options=array() ) {

		$defaults = array(
			'type' => 'start',
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'availability' );

		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'availability', $options );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'availability', $options ) )
			return it_exchange_get_product_feature( $this->product->ID, 'availability', $options );
		return true;
	}

	/**
	 * Uses start and end availability dates to now to determine if the product is currently available
	 *
     * @since 0.4.0
	 *
	 * @return boolean
	*/
	function is_available( $options=array() ) {
		return it_exchange_is_product_available( $this->product->ID );
	}

	/**
	 * The product's dates purchase availability
	 *
	 * Use type of 'start', 'end', 'both', either in options
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function visibility( $options=array() ) {
		// Return boolean if has flag was set
		if ( $options['has'] )
			return ( false !== get_post_meta( $product_id, '_it-exchange-visibility', true ) );

		return get_post_meta( $product_id, '_it-exchange-visibility', true );
	}

	/**
	 * Uses start and end availability dates to now to determine if the product is currently available
	 *
     * @since 0.4.0
	 *
	 * @return boolean
	*/
	function is_visible( $options=array() ) {
		return it_exchange_is_product_visible( $this->product->ID );
	}

	/**
	 * The product's featured image
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function featured_image( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'featured-image' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'featured-image' );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'featured-image' )
				&& it_exchange_product_has_feature( $this->product->ID, 'featured-image' ) ) {

			$defaults = array(
				'size' => 'post-thumbnail',
			);

			$options = ITUtility::merge_defaults( $options, $defaults );
			$img = it_exchange_get_product_feature( $this->product->ID, 'featured-image', $options );
			return $img;
		}
		return false;
	}

	/**
	 * The product's product images
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function product_images( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'product-images' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'product-images' );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'product-images' )
				&& it_exchange_product_has_feature( $this->product->ID, 'product-images' ) ) {

			$defaults = array(
				'size'   => 'thumbnail', //thumbnail/post-thumbnail, large
			);
			$options = ITUtility::merge_defaults( $options, $defaults );
			$output = array();
	
			$product_images = it_exchange_get_product_feature( $this->product->ID, 'product-images' );

			foreach( $product_images as $image_id ) {
				if ( 'thumbnail' === $options['size'] )
					$img_url = wp_get_attachment_thumb_url( $image_id );
				else
					$img_url = wp_get_attachment_url( $image_id );
					
				$output[] = $img_url;
			}
			
			return $output;
		}
		return false;
	}

	/**
	 * The product's product image gallery
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function product_gallery( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'product-images' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'product-images' );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'product-images' )
				&& it_exchange_product_has_feature( $this->product->ID, 'product-images' ) ) {

			$defaults = array(
				'size'   => 'thumbnail', //thumbnail/post-thumbnail, large
				'output' => 'gallery', //gallery, featured, or thumbnails
			);
			$options = ITUtility::merge_defaults( $options, $defaults );
			$output = NULL;
	
			$product_images = it_exchange_get_product_feature( $this->product->ID, 'product-images' );

			switch( $options['output'] ) {
				
				case 'featured': //really just the first image
					if ( !empty( $product_images ) ) {
						$output = '<div id="it-exchange-product-images-gallery">';
						
						$img_url = wp_get_attachment_url( $product_images[0] );
						$img_thumb_url = wp_get_attachment_thumb_url( $product_images[0] );
						
						if ( 'thumbnail' === $options['size'] )
							$img_src = $img_thumb_url;
						else
							$img_src = $img_url;
			
						$output .= '<div id="it-exchange-feature-image-' . $product_images[0] . '" class="it-exchange-featured-image">';
						$output .= '<div class="featured-image-wrapper">';
						$output .= '   <img alt="" src="' . $img_src . '" data-src-large="' . $img_url . '" data-src-thumb="' . $img_thumb_url . '">';
						$output .= '</div>';
						$output .= '</div>';
						
						$output .= '</div>';
					}
					break;
				
				case 'thumbnails': //just thumbnails
					if ( !empty( $product_images ) ) {
						$output = '<div id="it-exchange-product-images-gallery">';
								
						$output .=  '<ul id="it-exchange-gallery-images">';
						foreach( $product_images as $image_id ) {
							
							$img_url = wp_get_attachment_url( $image_id );
							$img_thumb_url = wp_get_attachment_thumb_url( $image_id );
							
							$output .=  '  <li class="it-exchange-product-image-thumb-' . $image_id . '">';
							$output .=  '      <img alt="" src=" ' . $img_thumb_url . '" data-src-large="' . $img_url . '" data-src-thumb="' . $img_thumb_url . '">';
							$output .=  '  </li>';
							
						}
						$output .=  '</ul>';

						$output .= '</div>';
					}
					break;
			
				case 'gallery':
				default:
					if ( !empty( $product_images ) ) {
						$output = '<div id="it-exchange-product-images-gallery">';
						
						$img_url = wp_get_attachment_url( $product_images[0] );
						$img_thumb_url = wp_get_attachment_thumb_url( $product_images[0] );
						
						$output .= '<div id="it-exchange-feature-image-' . $product_images[0] . '" class="it-exchange-featured-image">';
						$output .= '<div class="featured-image-wrapper">';
						$output .= '   <img alt="" src="' . $img_url . '" data-src-large="' . $img_url . '" data-src-thumb="' . $img_thumb_url . '">';
						$output .= '</div>';
						$output .= '</div>';
						
						unset( $product_images[0] ); //we don't want this listed below
						
						if ( !empty( $product_images ) ){
							
							$output .=  '<ul id="it-exchange-gallery-images">';
							foreach( $product_images as $image_id ) {
								
								$img_url = wp_get_attachment_url( $image_id );
								$img_thumb_url = wp_get_attachment_thumb_url( $image_id );
								
								$output .=  '  <li class="it-exchange-product-image-thumb-' . $image_id . '">';
								$output .=  '      <img alt="" src=" ' . $img_thumb_url . '" data-src-large="' . $img_url . '" data-src-thumb="' . $img_thumb_url . '">';
								$output .=  '  </li>';
								
							}
							$output .=  '</ul>';
						
						}
						$output .= '</div>';
					}
					break;
				
			}			
			
			return $output;
		}
		return false;
	}

	/**
	 * Returns downloads for product. 
	 *
	 * If has option is true, returns boolean
	 *
	 * @since 0.4.0
	 * @todo this looks incomplete to Lew... check later
	 *
	 * @return boolean
	*/
	function downloads( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'downloads' );

        // Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'downloads' );

		// If we made it here, we're doing a loop of downloads for the current product.
		// This will init/reset the downloads global and loop through them. the /api/theme/download.php file will handle individual downloads.
		if ( empty( $GLOBALS['it_exchange']['downloads'] ) ) { 
			$GLOBALS['it_exchange']['downloads'] = it_exchange_get_product_feature( $this->product->ID, 'downloads' );
			$GLOBALS['it_exchange']['download'] = reset( $GLOBALS['it_exchange']['downloads'] );
			return true;
		} else {
			if ( next( $GLOBALS['it_exchange']['downloads'] ) ) { 
				$GLOBALS['it_exchange']['download'] = current( $GLOBALS['it_exchange']['downloads'] );
				return true;
			} else {
				$GLOBALS['it_exchange']['download'] = false;
				return false;
			}   
		}   
		end( $GLOBALS['it_exchange']['downloads'] );
		$GLOBALS['it_exchange']['download'] = false;
		return false;
	}

	/**
	 * Returns the buy now or add_to_cart form. Or both.
	 *
	 * Options:
	 * - buy-now-before:          Gets added before the buy-now form
	 * - buy-now-after:           Gets added after the buy-now form
	 * - buy-now-class:           A CSS class applied to the buy-now button
	 * - buy-now-label:           The HTML value of the buy now button.
	 * - buy-now-button-type:     The button-type: submit or button. Default is submit
	 * - buy-now-button-name:     The default is false. No name attribute is provided when false
	 * - add-to-cart-before:      Gets added before the buy-now form
	 * - add-to-cart-after:       Gets added after the buy-now form
	 * - add-to-cart-class:       A CSS class applied to the buy-now button
	 * - add-to-cart-label:       The HTML value of the buy now button.
	 * - add-to-cart-button-type: The button-type: submit or button. Default is submit
	 * - add-to-cart-button-name: The default is false. No name attribute is provided when false
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function purchase_options( $options=array() ) {

		// Return boolean if has flag was set. Just keeping this here since its in all other product.php methods
		if ( $options['supports'] )
			return true;

		// Return boolean if has flag was set
		if ( $options['has'] )
			return true;
		
		// Parse options
		$result        = false;

		$defaults      = array(
			'type'                    => false,
			'buy-now-before'          => '',
			'buy-now-after'           => '',
			'buy-now-class'           => false,
			'buy-now-label'           => __( 'Buy Now', 'LION' ),
			'buy-now-button-type'     => 'submit',
			'buy-now-button-name'     => false,
			'add-to-cart-before'      => '',
			'add-to-cart-after'       => '',
			'add-to-cart-class'       => false,
			'add-to-cart-label'       => __( 'Add to Cart', 'LION' ),
			'add-to-cart-button-type' => 'submit',
			'add-to-cart-button-name' => false,
			'out-of-stock-text'       => __( 'Out of stock.', 'LION' ),
			'not-available--text'     => __( 'Product not available right now.', 'LION' ),
		);
		$options   = ITUtility::merge_defaults( $options, $defaults );

		// If we are tracking inventory, lets make sure we have some available
		$product_in_stock = it_exchange_product_supports_feature( $this->product->ID, 'inventory' ) ? it_exchange_product_has_feature( $this->product->ID, 'inventory' ) : true;

		// If we're supporting availability dates, check that
		$product_is_available = it_exchange( 'product', 'is-available' );

		// Do we have multi-item cart add-on enabled?
		$multi_item_cart = it_exchange_is_multi_item_cart_allowed();

		// Init empty hidden field variables
		$buy_now_hidden_fields = $add_to_cart_hidden_fields = '';

		$output = '';

		// Set buy-now options
		$options['before']      = $options['buy-now-before'];
		$options['after']       = $options['buy-now-after'];
		$options['class']       = $options['buy-now-class'];
		$options['label']       = $options['buy-now-label'];
		$options['button-type'] = $options['buy-now-button-type'];
		$options['button-name'] = $options['buy-now-button-name'];

		// Add buy-now form to output if product is available for purchase and template asked for it.
		if ( $product_in_stock && $product_is_available && ( empty( $options['type'] ) || 'buy-now' == $options['type'] ) )
			$output .= it_exchange( 'product', 'get-buy-now', $options );

		// Set add-to-cart options
		$options['before']      = $options['add-to-cart-before'];
		$options['after']       = $options['add-to-cart-after'];
		$options['class']       = $options['add-to-cart-class'];
		$options['label']       = $options['add-to-cart-label'];
		$options['button-type'] = $options['add-to-cart-button-type'];
		$options['button-name'] = $options['add-to-cart-button-name'];

		// Add add-to-cart form to output if product is available for purchase and template asked for it.
		if ( $product_in_stock && $product_is_available && $multi_item_cart && ( empty( $options['type'] ) || 'add-to-cart' == $options['type'] ) )
			$output .= it_exchange( 'product', 'get-add-to-cart', $options );

		// Return output
		return $output;
	}

	function buy_now( $options=array() ) {

		// Return boolean if has flag was set. Just keeping this here since its in all other product.php methods
		if ( $options['supports'] )
			return true;

		// Return boolean if has flag was set
		if ( $options['has'] )
			return true;
		
		// Parse options
		$result        = false;

		$defaults      = array(
			'before'              => '',
			'after'               => '',
			'class'               => false,
			'label'               => __( 'Buy Now', 'LION' ),
			'button-type'         => 'submit',
			'button-name'         => false,
			'out-of-stock-text'   => __( 'Out of stock.', 'LION' ),
			'not-available-text' => __( 'Product not available right now.', 'LION' ),
		);
		$options   = ITUtility::merge_defaults( $options, $defaults );

		// If we are tracking inventory, lets make sure we have some available
		$product_in_stock = it_exchange_product_supports_feature( $this->product->ID, 'inventory' ) ? it_exchange_product_has_feature( $this->product->ID, 'inventory' ) : true;

		// If we're supporting availability dates, check that
		$product_is_available = it_exchange( 'product', 'is-available' );

		$output = '';

		$class          = empty( $options['class'] ) ? 'buy-now-button' : 'buy-now-button ' . esc_attr( $options['class'] );
		$var_key        = it_exchange_get_field_name( 'buy_now' );
		$var_value      = $this->product->ID;
		$button_name    = empty( $options['button-name'] ) ? '' : ' name="' . esc_attr( $options['button-name'] ) . '"';
		$button         = '<input' . $button_name . ' type="' . esc_attr( $options['button-type'] ) . '" value="' . esc_attr( $options['label'] ) . '" class="' . esc_attr( $class ) . '" />';
		$hidden_fields  = '<input type="hidden" name="it-exchange-action" value="buy_now" />';
		$hidden_fields .= '<input class="buy-now-product-id" type="hidden" name="' . esc_attr( $var_key ). '" value="' . esc_attr( $var_value ). '" />';
		$hidden_fields .= wp_nonce_field( 'it-exchange-purchase-product-' . $this->product->ID, '_wpnonce', true, false );
		
		if ( ! $product_in_stock )
			return '<p>' . esc_attr( $options['out-of-stock-label'] ) . '</p>';

		if ( ! $product_is_available )
			return '<p>' . esc_attr( $options['not-available-text'] ) . '</p>';

		$result  = '<form action="" method="post" class="it-exchange-sw-purchase-options it-exchange-sw-buy-now">';
		$result .= $hidden_fields;
		$result .= it_exchange( 'product', 'get-purchase-quantity' );
		$result .= $button;
		$result .= '</form>';

		return $result;
	}

	function add_to_cart( $options=array() ) {

		// Return boolean if has flag was set. Just keeping this here since its in all other product.php methods
		if ( $options['supports'] )
			return true;

		// Return boolean if has flag was set
		if ( $options['has'] )
			return true;
		
		// Parse options
		$result        = false;

		$defaults      = array(
			'before'              => '',
			'after'               => '',
			'class'               => false,
			'label'               => __( 'Add to Cart', 'LION' ),
			'button-type'         => 'submit',
			'button-name'         => false,
			'out-of-stock-text'   => __( 'Out of stock.', 'LION' ),
			'not-available--text' => __( 'Product not available right now.', 'LION' ),
		);
		$options   = ITUtility::merge_defaults( $options, $defaults );

		// If we are tracking inventory, lets make sure we have some available
		$product_in_stock = it_exchange_product_supports_feature( $this->product->ID, 'inventory' ) ? it_exchange_product_has_feature( $this->product->ID, 'inventory' ) : true;

		// If we're supporting availability dates, check that
		$product_is_available = it_exchange( 'product', 'is-available' );

		// Do we have multi-item cart add-on enabled?
		$multi_item_cart = it_exchange_is_multi_item_cart_allowed();

		// Init empty hidden field variables
		$buy_now_hidden_fields = $add_to_cart_hidden_fields = '';

		$class          = empty( $options['class'] ) ? 'add-to-cart-button' : 'add-to-cart-button ' . esc_attr( $options['class'] );
		$var_key        = it_exchange_get_field_name( 'add_product_to_cart' );
		$var_value      = $this->product->ID;
		$button_name    = empty( $options['button-name'] ) ? '' : ' name="' . esc_attr( $options['button-name'] ) . '"';
		$button         = '<input' . $button_name . ' type="' . esc_attr( $options['button-type'] ) . '" value="' . esc_attr( $options['label'] ) . '" class="' . esc_attr( $class ) . '" />';
		$hidden_fields  = '<input type="hidden" name="it-exchange-action" value="add_product_to_cart" />';
		$hidden_fields .= '<input class="add-to-cart-product-id" type="hidden" name="' . esc_attr( $var_key ). '" value="' . esc_attr( $var_value ). '" />';
		$hidden_fields .= wp_nonce_field( 'it-exchange-purchase-product-' . $this->product->ID, '_wpnonce', true, false );
		
		if ( ! $product_in_stock )
			return '<p>' . esc_attr( $options['out-of-stock-label'] ) . '</p>';

		if ( ! $product_is_available )
			return '<p>' . esc_attr( $options['not-available-text'] ) . '</p>';

		if ( ! $multi_item_cart )
			return '';

		$result  = '<form action="" method="post" class="it-exchange-sw-purchase-options it-exchange-sw-add-to-cart">';
		$result .= $hidden_fields;
		$result .= it_exchange( 'product', 'get-purchase-quantity' );
		$result .= $button;
		$result .= '</form>';

		return $result;
	}

	/**
	 * Returns a buy_now var
	 *
	 * @since 0.4.0
	 *
	 * @param array $options
	 * @return string
	*/
	function buy_now_var( $options ) {

		// Return boolean if has flag was set.
		if ( $options['supports'] )
			return true;

		// Return boolean if has flag was set
		if ( $options['has'] )
			return true;
		
		// Parse options
		$defaults      = array(
			'format'      => 'key',
		);
		$options   = ITUtility::merge_defaults( $options, $defaults );

		if ( 'key' == $format )
			return it_exchange_get_field_name( 'buy_now' );
		else
			return $this->product->ID;
	}

	/**
	 * Returns a add_to_cart var
	 *
	 * @since 0.4.0
	 *
	 * @param array $options
	 * @return string
	*/
	function add_to_cart_var( $options ) {

		// Return boolean if has flag was set.
		if ( $options['supports'] )
			return true;

		// Return boolean if has flag was set
		if ( $options['has'] )
			return true;
		
		// Parse options
		$defaults = array(
			'format' => 'key',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		if ( 'key' == $format )
			return it_exchange_get_field_name( 'add_product_to_cart' );
		else
			return $this->product->ID;
	}
}
