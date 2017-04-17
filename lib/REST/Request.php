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

	/** @var array */
	private $route_objects = array();

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
	 * Create a request from a path.
	 *
	 * @since 2.0.0
	 *
	 * @param string $path
	 *
	 * @return Request|null
	 */
	public static function from_path( $path ) {
		return static::from_url( rest_url( $path ) );
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
	 * Get the route object.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public function get_route_object( $key ) {

		if ( array_key_exists( $key, $this->route_objects ) ) {
			return $this->route_objects[ $key ];
		}

		$route = $this->get_matched_route_controller();

		return $this->expand_route_object( $route, $key );
	}

	/**
	 * Expand a route object ID into an actual object.
	 *
	 * Will traverse the route parent tree.
	 *
	 * @since 2.0.0
	 *
	 * @param Route  $route
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	protected function expand_route_object( Route $route, $key ) {

		if ( $route instanceof RouteObjectExpandable && ( $map = $route->get_route_object_map() ) && isset( $map[ $key ] ) ) {
			$callable  = $map[ $key ];
			$object_id = $this->get_param( $key, 'URL' );

			if ( ! $object_id ) {
				$this->set_route_object( $key, null );

				return null;
			}

			$object = call_user_func( $callable, $object_id ) ?: null;
			$this->set_route_object( $key, $object );

			return $object;
		} elseif ( $route->has_parent() ) {
			return $this->expand_route_object( $route->get_parent(), $key );
		} else {
			return null;
		}
	}

	/**
	 * Set the route object for a given route variable.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 * @param mixed  $object
	 */
	public function set_route_object( $key, $object ) {
		$this->route_objects[ $key ] = $object;
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
