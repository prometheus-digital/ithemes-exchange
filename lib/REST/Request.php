<?php
/**
 * REST Request.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

/**
 * Class Request
 *
 * @package iThemes\Exchange\REST
 */
class Request extends \WP_REST_Request {

	/** @var Route */
	private $matched_route_controller;

	/** @var \ITE_Cart|null */
	private $cart;

	/**
	 * Create a Request object from the WP_REST_Request object.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \iThemes\Exchange\REST\Request
	 */
	public static function from_wp( \WP_REST_Request $request ) {

		$self = new static();

		foreach ( get_object_vars( $request ) as $key => $value ) {
			$self->{$key} = $value;
		}

		return $self;
	}

	/**
	 * @inheritDoc
	 */
	public static function from_url( $url ) {
		$wp_request = parent::from_url( $url );

		if ( $wp_request ) {
			return static::from_wp( $wp_request );
		}

		return null;
	}

	/**
	 * Get the matched route controller.
	 *
	 * @since 2.0.0
	 *
	 * @return \iThemes\Exchange\REST\Route|null
	 */
	public function get_matched_route_controller() {
		return $this->matched_route_controller;
	}

	/**
	 * Set the matched route controller.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Route $matched_route_controller
	 *
	 * @return $this
	 */
	public function set_matched_route_controller( $matched_route_controller ) {
		$this->matched_route_controller = $matched_route_controller;

		return $this;
	}

	/**
	 * Get the cart being operated on in this request.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Cart|null
	 */
	public function get_cart() { return $this->cart; }

	/**
	 * Set the cart being operated on in this request.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 *
	 * @return $this
	 */
	public function set_cart( \ITE_Cart $cart ) {
		$this->cart = $cart;

		return $this;
	}

	/**
	 * Retrieves a parameter from the request.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key  Parameter name.
	 * @param string $type Type of param to draw from.
	 *
	 * @return mixed|null Value if set, null otherwise.
	 */
	public function get_param( $key, $type = '' ) {

		if ( $type ) {
			if ( isset( $this->params[ $type ], $this->params[ $type ][ $key ] ) ) {
				return $this->params[ $type ][ $key ];
			}

			return null;
		}

		return parent::get_param( $key );
	}

	/**
	 * Check if the request contains a parameter.
	 *
	 * Iterates over the parameter order, excluding defaults.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 * @param array  $param_types_to_check Limit the check to certain parameter types.
	 *
	 * @return bool
	 */
	public function has_param( $key, array $param_types_to_check = array() ) {

		$order = $this->get_parameter_order();

		if ( $param_types_to_check ) {
			$order = array_intersect( $order, $param_types_to_check );
		}

		if ( ( $i = array_search( 'defaults', $order, true ) ) !== false ) {
			unset( $order[ $i ] );
		}

		foreach ( $order as $type ) {
			if ( ! isset( $this->params[ $type ] ) ) {
				continue;
			}

			if ( array_key_exists( $key, $this->params[ $type ] ) ) {
				return true;
			}
		}

		return false;
	}
}
