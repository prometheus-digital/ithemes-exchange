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
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'title' );

		if ( it_exchange_product_has_feature( $this->product->ID, 'title' ) ) {

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
	 * The product base price
	 *
	 * @since 0.4.0
	 * @return mixed
	*/
	function base_price( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'base_price' );

		if ( it_exchange_product_has_feature( $this->product->ID, 'base_price' ) ) {

			$result     = '';
			$base_price = it_exchange_get_product_feature( $this->product->ID, 'base_price' );
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
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'description' );

		if ( it_exchange_product_has_feature( $this->product->ID, 'description' ) )
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
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'extended-description' );

		if ( it_exchange_product_has_feature( $this->product->ID, 'extended-description' ) ) {
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
		return false;
	}

	/**
	 * The product's WP excerpt
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function excerpt( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'wp-excerpt' );

		if ( it_exchange_product_has_feature( $this->product->ID, 'wp-excerpt' ) )
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
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'author' );

		if ( it_exchange_product_has_feature( $this->product->ID, 'author' ) )
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
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'quantity' );

		if ( it_exchange_product_has_feature( $this->product->ID, 'quantity' ) )
			return it_exchange_get_product_feature( $this->product->ID, 'quantity' );
		return false;
	}

	/**
	 * The product's current inventory
	 *
	 * @since 0.4.0
	 * @return integer 
	*/
	function inventory( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'inventory' );

		if ( it_exchange_product_has_feature( $this->product->ID, 'inventory' ) )
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

		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'availability', $options );

		if ( it_exchange_product_has_feature( $this->product->ID, 'availability', $options ) )
			return it_exchange_get_product_feature( $this->product->ID, 'availability', $options );
		return false;
	}

	/**
	 * Is the product currently available to purchase? 
	 *
	 * @since 0.4.0
	 * @return string
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
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'featured-image' );

		if ( it_exchange_product_has_feature( $this->product->ID, 'featured-image' ) ) {

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
	 *
	 * @return boolean
	*/
	function downloads( $options=array() ) {

		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'downloads' );

		// If we made it here, we're doing a loop of downloads for the current product.
		// This will init/reset the downloads global and loop through them. the /api/theme/download.php file will handle the downloads.
		$GLOBALS['it_exchange']['downloads'] = it_exchange_get_product_feature( $this->product->ID, 'downloads' );

		if ( ! isset( $GLOBALS['it_exchange']['downloads_pointer'] ) ) {
			$downloads = $GLOBALS['it_exchange']['downloads'];
			reset( $downloads );
			$GLOBALS['it_exchange']['downloads_pointer'] = key( $downloads );
			return current( $downloads );
		} else if ( false === $GLOBALS['it_exchange']['downloads_pointer'] ) {
			return false;
		} else {
			$downloads = $GLOBALS['it_exchange']['downloads'];
			$prev =  $GLOBALS['it_exchange']['downloads_pointer'];
			while( $prev !== key( $downloads ) && end( $downloads ) !== current( $downloads ) ) {
				next( $downloads );
			}
			$GLOBALS['it_exchange']['downloads_pointer'] = next( $downloads ) ? key( $downloads ) : false;
			return current( $downloads );
		}

		$GLOBALS['it_exchange']['downloads_pointer'] = false;
		return false;
	}

	/**
	 * The product's max allowed quantity per purchase
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function lllquantity( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'quantity' );

		if ( it_exchange_product_has_feature( $this->product->ID, 'quantity' ) ) {

			$quantity = it_exchange_get_product_feature( $this->product->ID, 'quantity', $options );
			return $quantity;
		}
		return false;
	}
}
