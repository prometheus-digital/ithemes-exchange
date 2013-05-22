<?php
/**
 * Cart Item class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Cart_Item implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'cart-item';

	/**
	 * The current cart item
	 * @var array
	 * @since 0.4.0
	*/
	public $_cart_item = false;

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	public $_tag_map = array(
		'title'    => 'title',
		'remove'   => 'remove',
		'quantity' => 'quantity',
		'price'    => 'price',
		'subtotal' => 'sub_total',
	);

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Cart_Item() {
		$this->_cart_item = empty( $GLOBALS['it_exchange']['cart-item'] ) ? false : $GLOBALS['it_exchange']['cart-item'];
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
	 * Returns the remove from cart element / var based on format option
	 *
	 * @todo change session_id if we use WP_Session
	 * @since 0.4.0
	 *
	*/
	function remove( $options=array() ) {

		// Set options
		$defaults      = array(
			'before' => '', 
			'after'  => '', 
			'format' => 'html',
			'class'  => 'remove-cart-item',
			'label'  => __( '&times;', 'LION' ),
		);  
		$options   = ITUtility::merge_defaults( $options, $defaults );
		$var_key = it_exchange_get_field_name( 'remove_product_from_cart' );
		$var_value = $this->_cart_item['product_cart_id'];

		switch ( $options['format'] ) {
			case 'var_key' :
				$output = $var_key;
				break;
			case 'var_value' :
				$output = $var_value;
				break;
			case 'checkbox' :
				$output = $options['before'] . '<input type="checkbox" name="' . esc_attr( $var_key ) . '[]" value="' . esc_attr( $var_value ) . '" class="' . esc_attr( $options['class'] ) . '" />' . $options['label'] . $options['after'];
				break;
			case 'link' :
			default :
				$nonce_var = apply_filters( 'it_exchange_remove_product_from_cart_nonce_var', '_wpnonce' );
				$url = clean_it_exchange_query_args();
				$url = add_query_arg( $var_key, $var_value, $url );
				$url = add_query_arg( $nonce_var, wp_create_nonce( 'it-exchange-cart-action-' . session_id() ), $url ); 
				$output = $options['before'] . '<a href="' . $url . '" class="' . esc_attr( $options['class'] ) . '" >' . esc_attr( $options['label'] ) . '</a>' . $options['after'];
			break;
		}
		return $output;
	}

	/**
	 * Returns the title element / var based on format option
	 *
	 * @since 0.4.0
	 *
	*/
	function title( $options=array() ) {
		// Set options
		$defaults      = array(
			'before' => '', 
			'after'  => '', 
		);  
		$options = ITUtility::merge_defaults( $options, $defaults );

		return $options['before'] . it_exchange_get_cart_product_title( $this->_cart_item ) . $options['after'];
	}

	/**
	 * Returns the quantity element / var based on format option
	 *
	 * @since 0.4.0
	 *
	*/
	function quantity( $options=array() ) {
		// Set options
		$defaults      = array(
			'before' => '', 
			'after'  => '', 
			'format' => 'text-field',
			'class'  => 'product-cart-quantity',
			'label'  => '',
		);  
		$options   = ITUtility::merge_defaults( $options, $defaults );
		$var_key = it_exchange_get_field_name( 'product_purchase_quantity' );
		$var_value = it_exchange_get_cart_product_quantity( $this->_cart_item );

		switch ( $options['format'] ) {
			case 'var_key' :
				$output = $var_key;
				break;
			case 'var_value' :
				$output = $var_value;
				break;
			case 'text-field' :
			default :
				$output  = $options['before'];
				if ( it_exchange_product_supports_feature( $this->_cart_item['product_id'], 'purchase-quantity' ) ) {
					$output .= '<input type="text" name="' . esc_attr( $var_key ) . '[' . esc_attr( $this->_cart_item['product_cart_id'] ) . ']" value="' . esc_attr( $var_value ) . '" class="' . esc_attr( $options['class'] ) . '" />';
				} else {
					$output .= '1';
					$output .= '<input type="hidden" name="' . esc_attr( $var_key ) . '[' . esc_attr( $this->_cart_item['product_cart_id'] ) . ']" value="' . esc_attr( $var_value ) . '" class="' . esc_attr( $options['class'] ) . '" />';
				}
				$output .= $options['after'];
				break;
			break;
		}

		return $output;
	}

	/**
	 * Returns the price element / var based on format option
	 *
	 * @since 0.4.0
	 *
	*/
	function price( $options=array() ) {
		return it_exchange_get_cart_product_base_price( $this->_cart_item );
	}

	/**
	 * Returns the subtotal for the cart item (price * quantity)
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function sub_total( $options=array() ) {
		return it_exchange_get_cart_product_subtotal( $this->_cart_item );
	}
}
