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
		$parents  = array();

		do {
			if ( $building !== $route ) {
				array_unshift( $parents, $building );
			}

			$path = $building->get_path() . $path;
		} while ( $building->has_parent() && $building = $building->get_parent() );

		$args = array();

		foreach ( static::$interfaces as $verb => $interface ) {
			$interface = "\\iThemes\\Exchange\\REST\\{$interface}";

			if ( ! $route instanceof $interface ) {
				continue;
			}

			$permission = function ( \WP_REST_Request $request ) use ( $verb, $route, $parents ) {

				$user = it_exchange_get_current_customer() ?: null;

				foreach ( $parents as $parent ) {

					$callback = array( $parent, 'user_can_' . strtolower( $verb ) );

					if ( ! is_callable( $callback ) ) {
						if ( is_callable( array( $parent, 'user_can_get' ) ) ) {
							$callback = array( $parent, 'user_can_get' );
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

			// TODO: Move to Middleware
			$handle = function ( \WP_REST_Request $request ) use ( $verb, $route ) {
				/** @var \WP_REST_Response|\WP_Error $response */
				$response = call_user_func( array( $route, 'handle_' . strtolower( $verb ) ), $request );

				if ( is_wp_error( $response ) ) {
					return $response;
				}

				try {

					$current = $request->get_route() ? trailingslashit( $request->get_route() ) : '';

					if ( $route->has_parent() ) {
						$up = get_rest_url( $route->get_parent(), $request->get_url_params() );
					} else {
						$up = '';
					}

					$data = $response->get_data();

					if ( is_array( $data ) && ! \ITUtility::is_associative_array( $data ) ) {

						$linked = array();

						foreach ( $data as $i => $item ) {
							if ( ! isset( $item['_links'] ) ) {
								$item['_links'] = array();
							}

							if ( $up && $route->get_parent() instanceof Getable ) {
								$item['_links']['up'] = array(
									'href' => $up
								);
							}

							if ( $current && isset( $item['id'] ) ) {
								$item['_links']['self'] = array(
									'href' => $current . $item['id'] . '/',
								);
							}

							$linked[ $i ] = $item;
						}

						$response->set_data( $linked );
					} elseif ( $data ) {
						if ( $up && $route->get_parent() instanceof Getable ) {
							$response->add_link( 'up', $up );
						}

						if ( $verb === 'POST' && $current && isset( $data['id'] ) ) {
							$response->add_link( 'self', $current . $data['id'] . '/' );
						} else {
							$response->add_link( 'self', rest_url( $request->get_route() ) );
						}
					}
				}
				catch ( \UnexpectedValueException $e ) {

				}

				return $response;
			};

			if ( $verb === 'GET' ) {
				$method_args = $this->generate_query_args_for_server( $route );
			} else {
				$method_args = $this->generate_endpoint_args_for_server( $route, $verb );
			}

			$args[] = array(
				'methods'             => $verb,
				'callback'            => $handle,
				'permission_callback' => $permission,
				'args'                => $method_args,
			);
		}

		if ( ! $args ) {
			return false;
		}

		$args['schema'] = function () use ( $route ) {
			$schema = $route->get_schema();

			if ( isset( $schema['properties'] ) ) {
				foreach ( $schema['properties'] as &$property ) {
					unset( $property['arg_options'] );
				}
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
	 * Generate the endpoint args for the server.
	 *
	 * @since 1.36.0
	 *
	 * @param \iThemes\Exchange\REST\Route $route
	 * @param string                       $verb
	 *
	 * @return array
	 */
	protected function generate_endpoint_args_for_server( Route $route, $verb ) {

		$schema = $route->get_schema();

		$schema_properties = ! empty( $schema['properties'] ) ? $schema['properties'] : array();
		$endpoint_args     = array();

		foreach ( $schema_properties as $field_id => $params ) {

			// Arguments specified as `readonly` are not allowed to be set.
			if ( ! empty( $params['readonly'] ) ) {
				continue;
			}

			$endpoint_args[ $field_id ] = array(
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'rest_sanitize_request_arg',
			);

			if ( \WP_REST_Server::CREATABLE === $verb && isset( $params['default'] ) ) {
				$endpoint_args[ $field_id ]['default'] = $params['default'];
			}

			if ( \WP_REST_Server::CREATABLE === $verb && ! empty( $params['required'] ) ) {
				$endpoint_args[ $field_id ]['required'] = true;
			}

			foreach ( array( 'type', 'format', 'enum' ) as $schema_prop ) {
				if ( isset( $params[ $schema_prop ] ) ) {
					$endpoint_args[ $field_id ][ $schema_prop ] = $params[ $schema_prop ];
				}
			}

			// Merge in any options provided by the schema property.
			if ( isset( $params['arg_options'] ) ) {

				// Only use required / default from arg_options on CREATABLE endpoints.
				if ( \WP_REST_Server::CREATABLE !== $verb ) {
					$params['arg_options'] = array_diff_key( $params['arg_options'], array(
						'required' => '',
						'default'  => ''
					) );
				}

				$endpoint_args[ $field_id ] = array_merge( $endpoint_args[ $field_id ], $params['arg_options'] );
			}
		}

		return $endpoint_args;
	}

	/**
	 * Generate query args for the server.
	 *
	 * @since 1.36.0
	 *
	 * @param \iThemes\Exchange\REST\Route $route
	 *
	 * @return array
	 */
	protected function generate_query_args_for_server( Route $route ) {

		$args = $route->get_query_args();

		$args['context'] = $this->get_context_param( $route, array( 'default' => 'view' ) );

		return $args;
	}

	/**
	 * Get the magical context param.
	 *
	 * Ensures consistent description between endpoints, and populates enum from schema.
	 *
	 * @since 1.36.0
	 *
	 * @param \iThemes\Exchange\REST\Route $route
	 * @param array                        $args
	 *
	 * @return array
	 */
	protected function get_context_param( Route $route, $args = array() ) {
		$param_details = array(
			'description'       => __( 'Scope under which the request is made; determines fields present in response.' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$schema = $route->get_schema();

		if ( empty( $schema['properties'] ) ) {
			return array_merge( $param_details, $args );
		}

		$contexts = array();

		foreach ( $schema['properties'] as $key => $attributes ) {
			if ( ! empty( $attributes['context'] ) ) {
				$contexts = array_merge( $contexts, $attributes['context'] );
			}
		}

		if ( ! empty( $contexts ) ) {
			$param_details['enum'] = array_unique( $contexts );
			rsort( $param_details['enum'] );
		}

		return array_merge( $param_details, $args );
	}

	/**
	 * Is the request going to our endpoint.
	 *
	 * @since 1.36.0
	 *
	 * @return bool
	 */
	public function is_our_endpoint() {

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