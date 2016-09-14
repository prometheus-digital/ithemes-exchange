<?php
/**
 * REST Route Manager
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

/**
 * Class Manager
 * @package iThemes\Exchange\REST
 */
class Manager {

	/** @var string */
	private $namespace;

	/** @var Route[] */
	private $routes = array();

	/** @var bool */
	private $initialized = false;

	/** @var array */
	private static $interfaces = array(
		'GET'    => 'Getable',
		'POST'   => 'Postable',
		'PUT'    => 'Putable',
		'DELETE' => 'Deletable',
	);

	/**
	 * Manager constructor.
	 *
	 * @param string $namespace No forward or trailing slashes.
	 */
	public function __construct( $namespace ) { $this->namespace = $namespace; }

	/**
	 * Register a route.
	 *
	 * @since 1.36.0
	 *
	 * @param \iThemes\Exchange\REST\Route $route
	 *
	 * @return $this
	 *
	 * @throws \UnexpectedValueException
	 */
	public function register_route( Route $route ) {

		if ( $this->initialized ) {
			throw new \UnexpectedValueException( 'Route Manager has already been initialized.' );
		}

		$this->routes[] = $route;

		return $this;
	}

	/**
	 * Register a route provider.
	 *
	 * @since 1.36.0
	 *
	 * @param \iThemes\Exchange\REST\Route_Provider $provider
	 *
	 * @return $this
	 */
	public function register_provider( Route_Provider $provider ) {

		foreach ( $provider->get_routes() as $route ) {
			$this->register_route( $route );
		}

		return $this;
	}

	/**
	 * Initialize the manager.
	 *
	 * This should be done _after_ all routes have been registered.
	 *
	 * @return $this
	 */
	public function initialize() {

		foreach ( $this->routes as $route ) {
			$this->register_with_server( $route );
		}

		return $this;
	}

	/**
	 * Get the manager namespace.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_namespace() {
		return $this->namespace;
	}

	/**
	 * Register a route with the server.
	 *
	 * @since 1.36.0
	 *
	 * @param \iThemes\Exchange\REST\Route $route
	 *
	 * @return bool
	 */
	private function register_with_server( Route $route ) {

		$path     = '';
		$building = $route;
		$routes   = array();

		do {
			array_unshift( $routes, $building );
			$path = $building->get_path() . $path;
		} while ( $building->has_parent() && $building = $building->get_parent() );

		$args = array();

		foreach ( static::$interfaces as $verb => $interface ) {
			$interface = "\\iThemes\\Exchange\\REST\\{$interface}";

			if ( $route instanceof $interface ) {

				$permission = function ( \WP_REST_Request $request ) use ( $verb, $routes ) {

					$user = wp_get_current_user();

					foreach ( $routes as $route ) {

						$callback = array( $route, 'user_can_' . strtolower( $verb ) );

						if ( ! is_callable( $callback ) && is_callable( array( $route, 'user_can_get' ) ) ) {
							$callback = array( $route, 'user_can_get' );
						}

						if ( ( $r = call_user_func( $callback, $request, $user ) ) !== true ) {
							return $r;
						}
					}

					$callback = array( $route, 'user_can_' . strtolower( $verb ) );

					return call_user_func( $callback, $request, $user );
				};

				$args[] = array(
					'methods'             => $verb,
					'callback'            => array( $route, 'handle_' . strtolower( $verb ) ),
					'permission_callback' => $permission,
					'args'                => $route->get_query_args(),
				);
			}
		}

		if ( ! $args ) {
			return false;
		}

		$args['schema'] = function () use ( $route ) {
			static $schema = null;

			if ( ! $schema ) {
				$schema = $route->get_schema();
			}

			return $schema;
		};

		return register_rest_route(
			"{$this->namespace}/v{$route->get_version()}",
			$path,
			$args
		);
	}
}