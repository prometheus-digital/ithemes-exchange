<?php
/**
 * This file contains functions related to the shipping API
 * See also: api/shipping-features.php
 * @since 1.4.0
 * @package IT_Exchagne
*/

/**
 * Register a shipping provider
 *
 * @since 1.4.0
 *
 * @param  string  $slug    provider slug
 * @param  array   $options options for the provider
 *
 * @return boolean
*/
function it_exchange_register_shipping_provider( $slug, $options ) {

	// Lets just make sure the slug is in the options
	$options['slug'] = $slug;

	// Store the initiated class in our global
	$GLOBALS['it_exchange']['shipping']['providers'][$slug] = $options;

	// Return the object
	return true;
}

/**
 * Returns all registered shipping providers
 *
 * @since 1.4.0
 *
 * @param  mixed $filtered a string or an array of strings to limit returned providers to specific providers
 *
 * @return array
*/
function it_exchange_get_registered_shipping_providers( $filtered=array() ) {
	$providers = empty( $GLOBALS['it_exchange']['shipping']['providers'] ) ? array() : $GLOBALS['it_exchange']['shipping']['providers'];
	if ( empty( $filtered ) )
		return $providers;

	foreach( (array) $filtered as $provider ) {
		if ( isset( $providers[$provider] ) )
			unset( $providers[$provider] );
	}
	return $providers;
}

/**
 * Returns a specific registered shipping provider object
 *
 * @since 1.4.0
 *
 * @param  string $slug the registerd slug
 *
 * @return IT_Exchange_Shipping_Provider|bool  false or object
*/
function it_exchange_get_registered_shipping_provider( $slug ) {
	// Return false if we don't have one registered
	if ( empty( $GLOBALS['it_exchange']['shipping']['providers'][$slug] ) )
		return false;

	// Retrieve the provider details
	$options = $GLOBALS['it_exchange']['shipping']['providers'][$slug];

	// Include the class
	include_once( dirname( dirname( __FILE__ ) ) . '/lib/shipping/class-provider.php' );

	// Init the class
	return new IT_Exchange_Shipping_Provider( $slug, $options );
}

/**
 * Is the requested shipping provider registered?
 *
 * @since 1.4.0
 *
 * @param  string  $slug the registerd slug
 *
 * @return boolean
*/
function it_exchange_is_shipping_provider_registered( $slug ) {
	return (boolean) it_exchange_get_registered_shipping_provider( $slug );
}

/**
 * Register a shipping method
 *
 * @since 1.4.0
 *
 * @param string  $slug    method slug
 * @param string  $class  class name
 *
 * @return boolean
*/
function it_exchange_register_shipping_method( $slug, $class, $args=array() ) {
	// Validate opitons
	if ( ! class_exists( $class ) ) {
		return false;
	}

	// Store the initiated class in our global
	$GLOBALS['it_exchange']['shipping']['methods'][$slug]['class'] = $class;
	$GLOBALS['it_exchange']['shipping']['methods'][$slug]['args'] = $args;

	return true;
}

/**
 * Returns a specific registered shipping method object
 *
 * @since 1.4.0
 *
 * @param  string $slug the registered slug
 * @param int|bool $product_id
 *
 * @return IT_Exchange_Shipping_Method|false
*/
function it_exchange_get_registered_shipping_method( $slug, $product_id=false ) {

	// Return false if we don't have one registered
	if ( empty( $GLOBALS['it_exchange']['shipping']['methods'][$slug] ) ) {
		return false;
	}

	// Retrieve the method class
	$class = $GLOBALS['it_exchange']['shipping']['methods'][$slug]['class'];
	$args = $GLOBALS['it_exchange']['shipping']['methods'][$slug]['args'];

	// Make sure we have a class index and it corresponds to a defined class
	if ( empty( $class ) || ! class_exists( $class ) ) {
		return false;
	}
		
	if ( apply_filters( 'it_exchange_get_registered_shipping_method', false, $slug, $product_id, $class, $args ) ) {
		return false;
	}

	// Init the class
	return new $class( $product_id, $args );
}

/**
 * Get the registered shipping method class.
 * 
 * @since 1.36
 * 
 * @param string $slug
 *
 * @return string
 */
function it_exchange_get_registered_shipping_method_class( $slug ) {

	// Return false if we don't have one registered
	if ( empty( $GLOBALS['it_exchange']['shipping']['methods'][$slug] ) ) {
		return '';
	}

	return $GLOBALS['it_exchange']['shipping']['methods'][$slug]['class'];
}

/**
 * Get the registered shipping method class.
 *
 * @since 1.36
 *
 * @param string $slug
 *
 * @return array
 */
function it_exchange_get_registered_shipping_method_args( $slug ) {

	// Return false if we don't have one registered
	if ( empty( $GLOBALS['it_exchange']['shipping']['methods'][$slug] ) ) {
		return array();
	}

	return $GLOBALS['it_exchange']['shipping']['methods'][$slug]['args'];
}

/**
 * Returns all registered shipping methods
 *
 * @since 1.4.0
 *
 * @param  array|string $filtered a string or an array of strings to limit returned methods to specific methods
 *
 * @return array
*/
function it_exchange_get_registered_shipping_methods( $filtered=array() ) {
	$methods = empty( $GLOBALS['it_exchange']['shipping']['methods'] ) ? array() : $GLOBALS['it_exchange']['shipping']['methods'];

	if ( empty( $filtered ) )
		return $methods;

	foreach( (array) $filtered as $method ) {
		if ( isset( $methods[$method] ) )
			unset( $methods[$method] );
	}
	return $methods;
}

/**
 * Returns the value of an address field for the address form.
 *
 * @since 1.4.0
 *
 * @param string   $field       the form field we are looking for the value
 * @param int|bool $customer_id the wp ID of the customer
 *
 * @return void
*/
function it_exchange_print_shipping_address_value( $field, $customer_id=false ) {
    $customer_id = empty( $customer_id ) ? it_exchange_get_current_customer_id() : $customer_id;
    $saved_address = get_user_meta( $customer_id, 'it_exchange_shipping_address', true );
    $cart_address = it_exchange_get_cart_shipping_address();

    $value = empty( $saved_address[$field] ) ? '' : $saved_address[$field];
    $value = empty( $cart_address[$field] ) ? $value : $cart_address[$field];
    echo 'value="' . esc_attr( $value ) . '" ';
}

/**
 * Formats the Shipping Address for display
 *
 * @todo this function sucks. Lets make a function for formatting any address. ^gta
 * @since 1.4.0
 *
 * @param array|bool $shipping_address
 *
 * @return string HTML
*/
function it_exchange_get_formatted_shipping_address( $shipping_address=false ) {

	$shipping = empty( $shipping_address ) ? it_exchange_get_cart_shipping_address() : $shipping_address;

	$formatted = it_exchange_format_address( $shipping );

	return apply_filters( 'it_exchange_get_formatted_shipping_address', $formatted );
}

/**
 * Grabs all the shipping methods available to the passed product
 *
 * 1) Grab all shipping methods
 * 2) Check to see if they're enabled
 * 3) Return an arry of ones that are enabled.
 *
 * @since 1.4.0
 *
 * @param  IT_Exchange_Product $product an IT_Exchange_Product object
 *
 * @return IT_Exchange_Shipping_Method[]
*/
function it_exchange_get_available_shipping_methods_for_product( $product ) {

	$providers         = it_exchange_get_registered_shipping_providers();
	$provider_methods  = array();
	$available_methods = array();

	// Grab all registerd shipping methods for all providers
	foreach( (array) $providers as $provider ) {
		$provider         = it_exchange_get_registered_shipping_provider( $provider['slug'] );
		$provider_methods = array_merge( $provider_methods, $provider->shipping_methods );
	}

	// Loop through provider methods and only use the ones that are available for this product
	$provider_methods = apply_filters( 'it_exchange_get_available_shipping_methods_for_product_provider_methods', $provider_methods, $product );
	foreach( $provider_methods as $slug ) {
		if ( $method = it_exchange_get_registered_shipping_method( $slug, $product->ID ) ) {
			if ( apply_filters( 'it_exchange_get_registered_shipping_method_available', $method->available, $slug, $method, $product ) )
				$available_methods[$slug] = $method;
		}
	}

	return apply_filters( 'it_exchange_get_available_shipping_methods_for_product', $available_methods, $product );
}

/**
 * Get all of the enabled shipping methods for this product.
 *
 * A product can have certain shipping methods disabled, even though the product might otherwise be eligible for them.
 *
 * @since 1.4.0
 *
 * @param IT_Exchange_Product $product
 * @param string              $return  Return value for shipping methods. Either 'slug' or 'object'.
 *
 * @return IT_Exchange_Shipping_Method[]|string[]|false
 */
function it_exchange_get_enabled_shipping_methods_for_product( $product, $return = 'object' ) {

	// Are we viewing a new product?
	$screen         = is_admin() ? get_current_screen() : false;
	$is_new_product = is_admin() && ! empty( $screen->action ) && 'add' === $screen->action;

	// Return false if shipping is turned off for this product
	if ( ! it_exchange_product_has_feature( $product->ID, 'shipping' ) && ! $is_new_product )
		return false;

	$enabled_methods                    = array();
	$product_overriding_default_methods = it_exchange_get_shipping_feature_for_product( 'core-available-shipping-methods', $product->ID );

	foreach( (array) it_exchange_get_available_shipping_methods_for_product( $product ) as $slug => $available_method ) {
		// If we made it here, the method is available. Check to see if it has been turned off for this specific product
		if ( false !== $product_overriding_default_methods ) {
			if ( ! empty( $product_overriding_default_methods->$slug ) )
				$enabled_methods[$slug] = ( 'slug' === $return ) ? $slug : $available_method;
		} else {
			$enabled_methods[$slug] = ( 'slug' === $return ) ? $slug : $available_method;
		}
	}
	return $enabled_methods;
}

/**
 * Is cart address valid?
 *
 * @since 1.4.0
 *
 * @return boolean
*/
function it_exchange_is_shipping_address_valid() {
	$cart_address  = it_exchange_get_cart_data( 'shipping-address' );
	$cart_customer = empty( $cart_address['customer'] ) ? 0 : $cart_address['customer'];
	$customer_id   = it_exchange_get_current_customer_id();
	$customer_id   = empty( $customer_id ) ? $cart_customer : $customer_id;

	return (boolean) get_user_meta( $customer_id, 'it_exchange_shipping_address', true );
}

/**
 * Returns the selected shipping method saved in the cart Session
 *
 * @since 1.4.0
 *
 * @return string method slug
*/
function it_exchange_get_cart_shipping_method() {
	$method = it_exchange_get_cart_data( 'shipping-method' );
	$method = empty( $method[0] ) ? false : $method[0];

	// If there is only one possible shippign method for the cart, set it and return it.
	$cart_methods         = it_exchange_get_available_shipping_methods_for_cart();
	$cart_product_methods = it_exchange_get_available_shipping_methods_for_cart_products();

	$cart_methods_count = count( $cart_methods );
	$cart_product_methods_count = count( $cart_product_methods );

	$single_method = reset( $cart_methods );
	
	if ( 1 === $cart_product_methods_count && $cart_methods_count && $single_method->slug !== $method ) {
		it_exchange_update_cart_data( 'shipping-method', $single_method->slug );
		it_exchange_get_current_cart()->set_shipping_method( $single_method->slug );

		return $single_method->slug;
	} elseif ( 0 === $cart_methods_count && ! $method ) {

		$cart = it_exchange_get_current_cart();

		it_exchange_update_cart_data( 'shipping-method', 'multiple-methods' );
		$cart->set_shipping_method( 'multiple-methods' );
		it_exchange_remove_cart_data( 'multiple-shipping-methods' );

		/** @var ITE_Cart_Product $product */
		foreach ( $cart->get_items( 'product' ) as $product ) {
			$enabled_methods = it_exchange_get_enabled_shipping_methods_for_product( $product->get_product() );

			if ( is_array( $enabled_methods ) && count( $enabled_methods ) === 1 ) {
				$method = key( $enabled_methods );
				$cart->set_shipping_method( $method, $product );
				it_exchange_update_multiple_shipping_method_for_cart_product( $product->get_id(), key( $enabled_methods ) );
			}
		}

		return 'multiple-methods';
	}

	return $method;
}

/**
 * This returns available shipping methods for the cart
 *
 * By default, it only returns the highest common denominator for all products.
 * ie: If product one supports methods A and B but product two only supports method A,
 *     this function will only return method A.
 * Toggling the first paramater to false will return a composite of all available methods across products
 *
 * @since 1.4.0
 *
 * @param boolean $only_return_methods_available_to_all_cart_products defaults to true.
 *
 * @return IT_Exchange_Shipping_Method[]
*/
function it_exchange_get_available_shipping_methods_for_cart( $only_return_methods_available_to_all_cart_products = true ) {

	// I need this as a global for some hooks later with Table Rate Shipping (and possibly other future add-ons
	$GLOBALS['it_exchange']['shipping']['only_return_methods_available_to_all_cart_products'] = $only_return_methods_available_to_all_cart_products;

	$methods   = array();
	$product_i = 0;

	/** @var ITE_Cart_Product $cart_product */
	foreach ( it_exchange_get_current_cart()->get_items( 'product' ) as $cart_product ) {

		if ( ! $product = $cart_product->get_product() ) {
			continue;
		}

		if ( ! $product->has_feature( 'shipping' ) ) {
			continue;
		}

		// Bump product incrementer
		$product_i++;
		$product_methods = array();

		// Loop through shipping methods available for this product
		foreach( (array) it_exchange_get_enabled_shipping_methods_for_product( $product ) as $method ) {

			// Skip if method is false
			if ( empty( $method->slug ) ) {
				continue;
			}

			// If this is the first product, put all available methods in methods array
			if ( ! empty( $method->slug ) && 1 === $product_i ) {
				$methods[$method->slug] = $method;
			}

			// If we're returning all methods, even when they aren't available to other products, tack them onto the array
			if ( ! $only_return_methods_available_to_all_cart_products ) {
				$methods[ $method->slug ] = $method;
			}

			// Keep track of all this products methods
			$product_methods[] = $method->slug;
		}

		// Remove any methods previously added that aren't supported by this product
		if ( $only_return_methods_available_to_all_cart_products ) {
			foreach( $methods as $slug => $object ) {
				if ( ! in_array( $slug, $product_methods ) ) {
					unset( $methods[ $slug ] );
				}
			}
		}
	}

	return apply_filters( 'it_exchange_get_available_shipping_methods_for_cart', $methods );
}

/**
 * Returns all available shipping methods for all cart products
 *
 * @since 1.4.0
 *
 * @return array an array of shipping methods
*/
function it_exchange_get_available_shipping_methods_for_cart_products() {
	$methods = it_exchange_get_available_shipping_methods_for_cart( false );
	return apply_filters( 'it_exchange_get_available_shipping_methods_for_cart_products', $methods );
}

/**
 * Returns the cost of shipping for the cart based on selected shipping method(s)
 *
 * If called without the method param, it uses the selected cart method. Use with a param to get estimates for an
 * unselected method
 *
 * @since 1.4.0
 *
 * @param string|bool $shipping_method optional method.
 * @param bool        $format_price
 *
 * @return mixed
*/
function it_exchange_get_cart_shipping_cost( $shipping_method = false, $format_price = true ) {

	$cart  = it_exchange_get_current_cart();
	$items = $cart->get_items();

	if ( $items->count() === 0 ) {
		return false;
	}

	$cart_cost = 0.00;

	if ( $shipping_method = trim( $shipping_method ) ) {
		$additional_cost = array();
		foreach ( $cart->get_items( 'product' ) as $product ) {
			if ( $product->get_product()->has_feature( 'shipping' ) ) {
				$cart_cost += it_exchange_get_shipping_method_cost_for_cart_item(
					$shipping_method, $product->bc()
				);

				if ( $method = it_exchange_get_registered_shipping_method( $shipping_method ) ) {
					if ( ! isset( $additional_cost[$shipping_method] ) ) {
						$additional_cost[$shipping_method] = 0;
					} else {
						$additional_cost[$shipping_method]++;
					}
				}
			}
		}

		foreach ( $additional_cost as $method => $times ) {
			while ( $times > 0 ) { // intentionally > 0 not >= 0 so that only one additional cost remains.
				$times--;
				$cart_cost-= it_exchange_get_registered_shipping_method( $method )->get_additional_cost_for_cart( $cart );
			}
		}
	} else {

		$shipping_method = it_exchange_get_cart_shipping_method();

		$cart_cost = $cart->get_items( 'shipping', true )
           ->filter( function ( ITE_Shipping_Line_Item $shipping ) use ( $shipping_method ) {

               if ( $shipping_method === 'multiple-methods' ) {

                   if ( ! $shipping->get_aggregate() ) {
                       return true;
                   }

	               $shipping_method = it_exchange_get_multiple_shipping_method_for_cart_product(
                       $shipping->get_aggregate()->get_id()
                   );
               }

               return $shipping->get_method()->slug === $shipping_method;
           } )->total();
	}

	$cart_cost = $format_price ? it_exchange_format_price( $cart_cost ) : $cart_cost;
	
	return apply_filters( 'it_exchange_get_cart_shipping_cost',
		$cart_cost, $shipping_method, it_exchange_get_session_data( 'products' ), $format_price );
}

/**
 * This will return the shipping cost for a specific method/product combination in the cart.
 *
 * @since 1.4.0
 *
 * @param string  $method_slug  the shipping method slug
 * @param array   $cart_product the cart product array
 * @param boolean $format_price format the price for a display
 *
 * @return float|string
*/
function it_exchange_get_shipping_method_cost_for_cart_item( $method_slug, $cart_product, $format_price=false ) {
	$method = it_exchange_get_registered_shipping_method( $method_slug, $cart_product['product_id'] );
	
	if ( ! $method || ! $method->slug ) {
		return 0;
	}
	
	$cart = it_exchange_get_current_cart();

	$shipping = $cart->get_item( 'product', $cart_product['product_cart_id'] )
		->get_line_items()->with_only( 'shipping' )->filter( function ( ITE_Shipping_Line_Item $item ) use ( $method_slug ) {
			return $item->get_method()->slug === $method_slug && $item->get_aggregate();
		} );

	if ( $shipping->count() === 0 ) {
		$cost = $method->get_shipping_cost_for_product( $cart_product );
	} else {
		$cost = $shipping->total();
	}

	$cost += $method->get_additional_cost_for_cart( $cart );

	$cost = is_numeric( $cost ) ? $cost : 0;

	$cost = $format_price ? it_exchange_format_price( $cost ) : $cost;
	
	return apply_filters( 'it_exchange_get_shipping_method_cost_for_cart_item', $cost, $method_slug, $cart_product, $format_price );
}

/**
 * Returns the shipping method slug used by a specific cart product
 *
 * Only applicable when the cart is using multiple shipping methods for multiple products
 *
 * @since 1.4.0
 *
 * @param string|ITE_Cart $product the product_cart_id in the cart session. NOT the database ID of the product
 * @param \ITE_Cart|null  $cart
 *
 * @return string
*/
function it_exchange_get_multiple_shipping_method_for_cart_product( $product, ITE_Cart $cart = null ) {

	if ( ! $cart ) {
		$cart = it_exchange_get_current_cart();
	}

	if ( is_string( $product ) ) {
		$product = $cart->get_item( 'product', $product );
	}

	if ( ! $product ) {
		return false;
	}

	$method   = it_exchange_get_shipping_method_for_item( $product );
	$selected = array();

	foreach ( $cart->get_items( 'product' ) as $other_product ) {
		$product_method = it_exchange_get_shipping_method_for_item( $other_product );

		if ( $product_method ) {
			$selected[ $other_product->get_id() ] = $product_method->slug;
		}
	}

	$slug = $method ? $method->slug : false;

	return apply_filters( 'it_exchange_get_multiple_shipping_method_for_cart_product', $slug, $selected, $product->get_id() );
}

/**
 * This function updates the shipping method being used for a specific product in the cart
 *
 * Only applicable when the cart is using multiple shipping methods for multiple products
 *
 * @since 1.4.0
 *
 * @param string $product_cart_id the product_cart_id in the cart session. NOT the database ID of the product
 * @param string $method_slug     the slug of the method this cart product will use
 *
 * @return void
*/
function it_exchange_update_multiple_shipping_method_for_cart_product( $product_cart_id, $method_slug ) {
	$selected_multiple_methods = it_exchange_get_cart_data( 'multiple-shipping-methods' );
	$selected_multiple_methods = empty( $selected_multiple_methods ) ? array() : $selected_multiple_methods;

	$selected_multiple_methods[$product_cart_id] = $method_slug;

	it_exchange_update_cart_data( 'multiple-shipping-methods', $selected_multiple_methods );
}
