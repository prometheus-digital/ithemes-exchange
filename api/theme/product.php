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

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	var $_tag_map = array(
		'found'               => 'found',
		'title'               => 'title',
		'permalink'           => 'permalink',
		'excerpt'	          => 'excerpt',
		'description'         => 'description',
		'content'             => 'extended_description',
		'extendeddescription' => 'extended_description',
		'author'              => 'author',
		'baseprice'           => 'base_price',
		'quantity'            => 'quantity',
		'inventory'           => 'inventory',
		'availability'        => 'availability',
		'isavailable'         => 'is_available',
		'image'               => 'featured_image',
		'productimage'        => 'featured_image',
		'featuredimage'       => 'featured_image',
		'downloads'           => 'downloads',
		'addtocart'           => 'add_to_cart',
		'buynow'              => 'buy_now',
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

		if ( it_exchange_product_supports_feature( $this->product->ID, 'title' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'title' ) ) {

			$result   = '';
			$title    = it_exchange_get_product_feature( $this->product->ID, 'title' );
			$defaults = array(
				'before' => '<h1 class="entry-title">',
				'after'  => '</h1>',
				'format' => 'raw',
			);

			$options = ITUtility::merge_defaults( $options, $defaults );

			if ( 'html' == $options['format'] )
				$result .= $options['before'];

			$result .= $title;

			if ( 'html' == $options['format'] )
				$result .= $options['after'];

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
			'after'  => '">' . it_exchange( 'product', 'get-title' ) . '</a>',
			'format' => 'raw',
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
				'format' => 'raw',
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
			'format' => 'raw',
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
	 * The product quantity (max purchase option by customer) 
	 *
	 * @since 0.4.0
	 * @return integer 
	*/
	function quantity( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'quantity' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'quantity' );

		// Set options
		$defaults      = array(
			'before'      => '',
			'after'       => '',
			'format'      => 'html',
			'class'       => 'product-quantity',
		);
		$options   = ITUtility::merge_defaults( $options, $defaults );

		$class     = empty( $options['class'] ) ? '' : ' class="' . esc_attr( $options['class'] ) .'"';
		$var_key   = it_exchange_get_field_name( 'product_quantity' );

		// Is the checkbox on add/edit products unchecked to allow quantities greater than 1
		if ( it_exchange_product_supports_feature( $this->product->ID, 'quantity' ) )
			$max_quantity = it_exchange_get_product_feature( $this->product->ID, 'quantity' );
		else
			return '';
		$max_quantity = empty( $max_quantity ) ? '0' : $max_quantity;

		// Return requested format
		switch ( $options['format'] ) {
			case 'max-quantity' :
				return $max_quantity;
				break;
			case 'html' :
			default :
				$html  = '<input' . $class . ' type="text" name="' . esc_attr( $var_key ) . '" value="1" />' . "\n";
				$html .= '<input type="hidden" name="' . it_exchange_get_field_name( 'product_max_quantity' ) . '[' . esc_attr( $this->product->ID ) . ']" value="' . ( $max_quantity ) . '" />';
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
	 * Use type of 'start', 'end', 'both' in options
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

		if ( it_exchange_product_supports_feature( $this->product->ID, 'availability' )
				&& it_exchange_product_has_feature( $this->product->ID, 'availability', $options ) )
			return it_exchange_get_product_feature( $this->product->ID, 'availability', $options );
		return false;
	}

	/**
	 * Is the product currently available to purchase? 
	 *
	 * @since 0.4.0
	 * @return string
	 * @todo this whole function needs to be written!
	*/
	function is_available( $options=array() ) {
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
	 * Returns add_to_cart field or field_name
	 *
	 * Format options:
	 * - form: (default) includes quanity select if available to product type and product
	 * - button: returns an HTML button
	 * - link: returns an HTML anchor tag
	 * - var-key: returns the query_var needed to build your own button or link.
	 * - var-value: returns the query_var value needed to build your own button or link
	 * 
	 * Format examples
	 * - Form: <?php it_exchange( 'product', 'add-to-cart' ); ?> // returns form. We do all the checks for you. include quanity options if product_type and product support it. Check inventory, etc.
	 * - Button: <?php it_exchange( 'product', 'add-to-cart', 'format=button' ); ?> // returns button and hidden field without form
	 * - Link: <?php it_exchange( 'product', 'add-to-cart', 'data=link' ); ?> // returns button
	 * - Custom link: <a href="?<?php it_exchange( 'product', 'add-to-cart', 'format=var-key' ); ?>=it_exchange( 'product', 'add-to-cart', 'format=var-value' ); ?>">Add to cart</a>
	 *
	 * Other options:
	 * - class: a CSS class applied to button or links
	 * - title: Link title or Button value
	 * - button-type: submit or button. Default is submit
	 * - button-name: default is false. No name attribute is provided when false
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function add_to_cart( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return true;

		// Return boolean if has flag was set
		if ( $options['has'] )
			return true;
		
		// Parse options
		$result        = false;

		$defaults      = array(
			'before'      => '',
			'after'       => '',
			'format'      => 'form',
			'class'       => 'add-product-to-cart',
			'title'       => __( 'Add to cart', 'LION' ),
			'button-type' => 'submit',
			'button-name' => false,
		);
		$options   = ITUtility::merge_defaults( $options, $defaults );

		$class         = empty( $options['class'] ) ? '' : ' class="' . esc_attr( $options['class'] ) .'"';
		$var_key       = it_exchange_get_field_name( 'add_product_to_cart' );
		$var_value     = $this->product->ID;
		$button_name   = empty( $options['button-name'] ) ? '' : ' name="' . esc_attr( $options['button-name'] ) . '"';
		$button        = '<input' . $button_name . ' type="' . esc_attr( $options['button-type'] ) . '" value="' . $options['title'] . '"' . $class . ' />';

		$hidden_fields  = '<input type="hidden" name="it-exchange-action" value="add_product_to_cart" />';
		$hidden_fields .= wp_nonce_field( 'it-exchange-add-product-to-cart-' . $this->product->ID, '_wpnonce', true, false );
		$hidden_fields .= '<input type="hidden" name="' . esc_attr( $var_key ). '" value="' . esc_attr( $var_value ). '" />';
		/** @todo Maybe add nonce_field. Will have to code for it in api/cart.php though. **/

		// Generate correct output
		switch( $options['format'] ) {

			case 'var-key':
				return esc_attr( $var_key );
				break;
			case 'var-value':
				return esc_attr( $var_value );
				break;
			case 'button' :
				return $hidden_fields . $button;
				break;
			case 'link' :
				$url = add_query_arg( array( $var_key => $var_value ) );
				return '<a' . $class . 'href="' . $url . '">' . $options['link_title'] . '</a>';
				break;
			case 'form' :
				$output  = '<form action="" method="post">';
				$output .= it_exchange( 'product', 'get-quantity' );
				$output .= $hidden_fields;
				$output .= $button;
				$output .= '</form>';
			default:
				break;
			
		}

		return $output;
	}
}
