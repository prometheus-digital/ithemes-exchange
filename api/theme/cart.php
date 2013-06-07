<?php
/**
 * Cart class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Cart implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'cart';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	public $_tag_map = array(
		'cartitems'     => 'cart_items',
		'formopen'      => 'form_open',
		'noncefield'    => 'nonce_field',
		'formclose'     => 'form_close',
		'subtotal'      => 'sub_total',
		'total'         => 'total',
		'update'        => 'update_cart',
		'checkout'      => 'checkout_cart',
		'empty'         => 'empty_cart',
		'multipleitems' => 'multiple_items',
		'itemcount'     => 'item_count',
		'focus'         => 'focus',
	);

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Cart() {
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
	 * This loops through the cart session products and updates the cart-item global.
	 *
	 * It return false when it reaches the last item 
	 * If the has flag has been passed, it just returns a boolean
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function cart_items( $options=array() ) {
		// Return boolean if has flag was set
		if ( $options['has'] )
			return count( it_exchange_get_cart_products() ) > 0 ;

		// If we made it here, we're doing a loop of products for the current cart.
		// We're accessing the SESSION directly to make looping easier.
		// This will init/reset the SESSION products and loop through them. the /api/theme/cart-item.php file will handle individual products.
		if ( empty( $GLOBALS['it_exchange']['cart-item'] ) ) {
			$GLOBALS['it_exchange']['products'] = it_exchange_get_cart_products();
			$GLOBALS['it_exchange']['cart-item'] = reset( $GLOBALS['it_exchange']['products'] );
			return true;
		} else {
			if ( next( $GLOBALS['it_exchange']['products'] ) ) {
				$GLOBALS['it_exchange']['cart-item'] = current( $GLOBALS['it_exchange']['products'] );
				return true;
			} else {
				$GLOBALS['it_exchange']['cart-item'] = false;
				return false;
			}
		}
		end( $GLOBALS['it_exchange']['products'] );
		$GLOBALS['it_exchange']['cart-item'] = false;
		return false;
	}

	/**
	 * Prints the opening form field tag for the cart
	 * @todo: Not Production Ready. Beef this up
	 * @since 0.4.0
	*/
	function form_open( $options=array() ) {
		$class = it_exchange_in_superwidget() ? 'it-exchange-sw-update-cart' : 'it-exchange-update-cart';
		return '<form action="" method="post" class="' . $class . '">';
	}

	/**
	 * Returns the nonce form field
	 *
	 * @since 0.4.0
	*/
	function nonce_field( $options=array() ) {
		return it_exchange_get_cart_nonce_field();
	}

	/**
	 * Prints the closing form field
	 *
	 * @todo Not Production Ready. Beef this up!
	 * @since 0.4.0
	*/
	function form_close( $options=array() ) {
		$defaults = array(
			'before'        => '',
			'after'         => '',
			'include-nonce' => true,
		);  
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		$output = $options['before'];
		if ( $options['include-nonce'] )
			$output .= it_exchange_get_cart_nonce_field();

		$output .= '</form>';
		$output .= $options['after'];
		return $output;
	}

	/**
	 * Returns the update cart button / varname
	 *
	 * @todo Not production ready.
	 * @since 0.4.0
	*/
	function update_cart( $options=array() ) {
		$defaults = array(
			'before' => '',
			'after'  => '',
			'class'  => 'it-exchange-update-cart',
			'format' => 'button',
			'label'  => __( 'Update Cart', 'LION' ),
		);  
		$options = ITUtility::merge_defaults( $options, $defaults );

		$var = it_exchange_get_field_name( 'update_cart_action' );

		switch( $options['format'] ) {
			case 'var' :
				return $var;
				break;
			case 'button' :
			default :
				$output  = $options['before'];
				$output .= '<input type="submit" class="' . esc_attr( $options['class'] ). '" name="' . esc_attr( $var ) . '" value="' . esc_attr( $options['label'] ) . '" />';
				$output .= $options['after'];
				break;
		}
		return $output;
	}

	function checkout_cart( $options=array() ) {
		$defaults = array(
			'before' => '',
			'after'  => '',
			'class'  => 'it-exchange-checkout-cart',
			'format' => 'button',
			'label'  => __( 'Checkout', 'LION' ),
		);  
		$options = ITUtility::merge_defaults( $options, $defaults );

		$class = ( 'it-exchange-checkout-cart' != $options['class'] ) ? 'it-exchange-checkout-cart ' . esc_attr( $options['class'] ) : $options['class'];
		$var   = it_exchange_get_field_name( 'proceed_to_checkout' );

		// If we're in the superwidget, we need to use that format.
		if ( it_exchange_in_superwidget() )
			$options['format'] = 'link';

		switch( $options['format'] ) {
			case 'var' :
				return $var;
				break;
			case 'link' :
				$url = '';
				// Tack on the superwidget state if in it.
				if ( it_exchange_in_superwidget() ) {
					// Get clean URL without any exchange query args
					$url = clean_it_exchange_query_args();
					$url = add_query_arg( 'ite-sw-state', 'checkout', $url );
				} else {
					if ( it_exchange_is_multi_item_cart_allowed() )
						$url = it_exchange_get_page_url( 'checkout' );
				}

				$output  = $options['before'];
				$output .= '<a href="' . $url . '" class="' . $class . '" name="' . esc_attr( $var ) . '">' . esc_attr( $options['label'] ) . '</a>';
				$output .= $options['after'];
				break;
			case 'button' :
			default :
				$output  = $options['before'];
				$output .= '<input type="submit" class="' . $class . '" name="' . esc_attr( $var ) . '" value="' . esc_attr( $options['label'] ) . '" />';
				$output .= $options['after'];
				break;
		}
		return $output;
	}
	function empty_cart( $options=array() ) {
		$defaults = array(
			'before' => '',
			'after'  => '',
			'class'  => 'it-exchange-empty-cart',
			'format' => 'button',
			'title'  => 'Empty Cart',
			'label'  => __( 'Empty Cart', 'LION' ),
		);  
		$options = ITUtility::merge_defaults( $options, $defaults );

		$var = it_exchange_get_field_name( 'empty_cart' );
		$nonce_var   = apply_filters( 'it_exchange_cart_action_nonce_var', '_wpnonce' );

		switch( $options['format'] ) {
			case 'var' :
				return $var;
				break;
			case 'link' :
				// Get clean url without any exchange query args
				$url = clean_it_exchange_query_args();
				$url = add_query_arg( $var, 1, $url );
				$url = add_query_arg( $nonce_var, wp_create_nonce( 'it-exchange-cart-action-' . session_id() ), $url );
				$output  = $options['before'];
				$output .= '<a href="' . $url . '" class="' . esc_attr( $options['class'] ) . '" title="' . esc_attr( $options['title'] ) . '">' . esc_attr( $options['label'] ) . '</a>';
				$output .= $options['after'];
				break;
			case 'button' :
			default :
				$output  = $options['before'];
				$output .= '<input type="submit" class="' . esc_attr( $options['class'] ). '" name="' . esc_attr( $var ) . '" value="' . esc_attr( $options['label'] ) . '" />';
				$output .= $options['after'];
				break;
		}
		return $output;
	}

	/**
	 * @todo add options and docblock
	*/
	function sub_total( $options=array() ) {
		return it_exchange_get_cart_subtotal();	
	}
	
	/**
	 * @todo add options and docblock
	*/
	function total( $options=array() ) {
		return it_exchange_get_cart_total();	
	}

	/**
	 * Does the cart support multiple items?
	 *
	 * @since 0.4.0
	 *
	 * @param array $options
	 * @return boolean
	*/
	function multiple_items( $options=array() ) {
		return it_exchange_is_multi_item_cart_allowed();
	}

	/**
	 * Returns the number of items in the cart
	 *
	 * @since 0.4.0
	 *
	 * @return integer
	*/
	function item_count() {
		return it_exchange_get_cart_products_count();
	}

	/**
	 * Return the current focus if indicated
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function focus( $options=array() ) {
		$defaults = array(
			'type'  => false,
		);  
		$options = ITUtility::merge_defaults( $options, $defaults );
		$focus_key = it_exchange_get_field_name( 'sw_cart_focus' );

		// Get the focus from REQUEST
		$focus = empty( $_REQUEST[$focus_key] ) ? false : $_REQUEST[$focus_key];

		// Return true if $focus is false or if $options['type'] is false
		if ( ! $options['type'] || ! $focus )
			return true;

		// return boolean if focus == type
		return $focus == $options['type'];
	}
}
