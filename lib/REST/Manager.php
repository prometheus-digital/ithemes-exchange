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
 *
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
	public function __construct( $namespace ) {
		$this->namespace = $namespace;
	}

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

		add_filter( 'rest_authentication_errors', array( $this, 'authenticate' ), 20 );

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

					$user = it_exchange_get_current_customer() ?: null;

					foreach ( $routes as $route ) {

						$callback = array( $route, 'user_can_' . strtolower( $verb ) );

						if ( ! is_callable( $callback ) ) {
							if ( is_callable( array( $route, 'user_can_get' ) ) ) {
								$callback = array( $route, 'user_can_get' );
							} else {
								continue;
							}
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

	/**
	 * Is the request going to our endpoint.
	 *
	 * @since 1.36.0
	 *
	 * @return bool
	 */
	protected function is_our_endpoint() {

		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$rest_prefix = trailingslashit( rest_get_url_prefix() );

		// Check if our endpoint.
		return false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix . "{$this->get_namespace()}/" );
	}

	/**
	 * Authenticate the user.
	 *
	 * @since 1.36.0
	 *
	 * @param \WP_Error|null|bool $authed
	 *
	 * @return \WP_Error|null|bool
	 */
	public function authenticate( $authed ) {

		if ( ! $this->is_our_endpoint() ) {
			return $authed;
		}

		if ( $authed === true ) {
			return $authed;
		}

		if (
			! empty( $_SERVER['PHP_AUTH_USER'] ) &&
			( empty( $_SERVER['PHP_AUTH_PW'] ) || trim( $_SERVER['PHP_AUTH_PW'] ) === '' ) &&
			is_email( $_SERVER['PHP_AUTH_USER'] ) &&
			function_exists( 'it_exchange_guest_checkout_generate_guest_user_object' )
		) {
			$email = $_SERVER['PHP_AUTH_USER'];

			$GLOBALS['current_user'] = it_exchange_guest_checkout_generate_guest_user_object( $email );

			add_filter( 'it_exchange_get_current_customer', function () use ( $email ) {
				return it_exchange_get_customer( $email );
			} );

			return true;
		}

		return $authed;
	}
}