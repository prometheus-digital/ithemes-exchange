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
		'found'                  => 'found',
		'title'                  => 'title',
		'excerpt'	             => 'description',
		'description'            => 'description',
		'content'                => 'extended_description',
		'extendeddescription'    => 'extended_description',
		'author'                 => 'author',
		'baseprice'              => 'base_price',
		'quantity'               => 'quantity',
		'inventory'              => 'inventory',
		'availabilitydatesstart' => 'available_start',
		'availabilitydatesend'   => 'availability_dates_end',
		'isavailable'            => 'is_available',
		'image'                  => 'featured_image',
		'productimage'           => 'featured_image',
		'featuredimage'          => 'featured_image',
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
		if ( it_exchange_product_has_feature( $this->product->ID, 'title' ) ) {

			$result   = '';
			$title    = it_exchange_get_product_feature( $this->product->ID, 'title' );
			$defaults = array(
				'before' => '<h1 class="entry-title">',
				'after'  => '</h1>',
				'format' => 'html',
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

		if ( it_exchange_product_has_feature( $this->product->ID, 'base_price' ) ) {

			$result     = '';
			$base_price = it_exchange_get_product_feature( $this->product->ID, 'base_price' );
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
		if ( it_exchange_product_has_feature( $this->product->ID, 'extended-description' ) ) {
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
		return false;
	}

	/**
	 * The product author
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function author( $options=array() ) {
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
		if ( it_exchange_product_has_feature( $this->product->ID, 'inventory' ) )
			return it_exchange_get_product_feature( $this->product->ID, 'inventory' );
		return false;
	}

	/**
	 * The product's start date for purchase availability
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function availability_dates_start( $options=array() ) {

	}

	/**
	 * The product's end date for purchase availability
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function availability_dates_end( $options=array() ) {

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
}
