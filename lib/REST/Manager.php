<?php
/**
 * REST Route Manager
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

use iThemes\Exchange\REST\Middleware\Stack;
use iThemes\Exchange\REST\Route\Base;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Uri\Retrievers\PredefinedArray;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\UriResolverInterface;
use JsonSchema\Validator;

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

	/** @var \iThemes\Exchange\REST\Middleware\Stack */
	private $middleware;

	/** @var SchemaStorage */
	private $schema_storage;

	/** @var UriResolverInterface */
	private $uri_retreiver;

	/** @var array */
	private $schemas = array();

	/** @var bool */
	private $initialized = false;

	/** @var array */
	private $shared_schemas;

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
	 * @param string                                  $namespace No forward or trailing slashes.
	 * @param \iThemes\Exchange\REST\Middleware\Stack $stack
	 * @param array                                   $shared_schemas
	 */
	public function __construct( $namespace, Stack $stack, array $shared_schemas = array() ) {
		$this->namespace      = $namespace;
		$this->middleware     = $stack;
		$this->shared_schemas = $shared_schemas;
	}

	/**
	 * Register a route.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Route\v1 $route
	 *
	 * @return $this
	 *
	 * @throws \UnexpectedValueException
	 */
	public function register_route( Route $route ) {

		if ( $this->initialized ) {
			throw new \UnexpectedValueException( 'Route Manager has already been initialized.' );
		}

		if ( $route instanceof Base ) {
			$route->set_manager( $this );
		}

		$this->routes[] = $route;

		return $this;
	}

	/**
	 * Register a route provider.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Route\v1_Provider $provider
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
	 * Get the first route matching a given class.
	 *
	 * @since 2.0.0
	 *
	 * @param string $class
	 *
	 * @return \iThemes\Exchange\REST\Route\v1|null
	 */
	public function get_first_route( $class ) {

		foreach ( $this->routes as $route ) {
			if ( $route instanceof $class ) {
				return $route;
			}
		}

		return null;
	}

	/**
	 * Get all routes matching a given class.
	 *
	 * @since 2.0.0
	 *
	 * @param string $class
	 *
	 * @return \iThemes\Exchange\REST\Route\v1[]
	 */
	public function get_routes_by_class( $class ) {

		$routes = array();

		foreach ( $this->routes as $route ) {
			if ( $route instanceof $class ) {
				$routes[] = $route;
			}
		}

		return $routes;
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

		$modified = array();

		foreach ( $this->schemas as $id => $schema ) {
			$modified[ url_for_schema( $id ) ] = $schema;
		}

		foreach ( $this->shared_schemas as $id => $schema ) {
			$modified[ url_for_schema( $id ) ] = json_encode( $schema );
		}

		$strategy            = new PredefinedArray( $modified );
		$this->uri_retreiver = new UriRetriever();
		$this->uri_retreiver->setUriRetriever( $strategy );

		$this->schema_storage = new SchemaStorage( $this->uri_retreiver );

		add_filter( 'rest_authentication_errors', array( $this, 'authenticate' ), 20 );
		add_filter( 'rest_dispatch_request', array( $this, 'conform_request_to_schema' ), 10, 4 );

		$this->initialized = true;

		return $this;
	}

	/**
	 * Get a list of schemas.
	 *
	 * @since 2.0.0
	 *
	 * @param array $titles A list of schema titles to retrieve. If empty, all schemas will be returned.
	 *
	 * @return array
	 */
	public function get_schemas( $titles = array() ) {

		$flipped = $titles ? array_flip( $titles ) : array();
		$schemas = array();

		foreach ( $this->routes as $route ) {

			if ( ( $schema = $route->get_schema() ) && ( isset( $flipped[ $schema['title'] ] ) || empty( $flipped ) ) ) {
				$schemas[ $schema['title'] ] = $schema;
			}
		}

		return $schemas;
	}

	/**
	 * Get the manager namespace.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_namespace() {
		return $this->namespace;
	}

	/**
	 * Get the Middleware Stack.
	 *
	 * @since 2.0.0
	 *
	 * @return \iThemes\Exchange\REST\Middleware\Stack
	 */
	public function get_middleware() {
		return $this->middleware;
	}

	/**
	 * Register a route with the server.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Route\v1 $route
	 *
	 * @return bool
	 */
	private function register_with_server( Route $route ) {

		if ( $schema = $route->get_schema() ) {
			$this->schemas[ $schema['title'] ] = json_encode( $this->transform_schema( $schema ) );
		}

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

			$exchange_request = null;

			$permission = function ( \WP_REST_Request $request ) use ( $verb, $route, $parents, &$exchange_request ) {

				$exchange_request = Request::from_wp( $request );
				$exchange_request->set_matched_route_controller( $route );

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

					if ( ( $r = call_user_func( $callback, $exchange_request, $user ) ) !== true ) {
						return $r;
					}
				}

				$callback = array( $route, 'user_can_' . strtolower( $verb ) );

				return call_user_func( $callback, $exchange_request, $user );
			};

			$middleware = $this->get_middleware();

			$handle = function ( \WP_REST_Request $request ) use ( $middleware, $route, $exchange_request ) {

				if ( ! $exchange_request ) {
					$exchange_request = Request::from_wp( $request );
					$exchange_request->set_matched_route_controller( $route );
				}

				return $middleware->handle( $exchange_request, $route );
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
				'ite_route'           => $route,
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
	 * Conform a request to a schema.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Error|\WP_HTTP_Response|null $response
	 * @param \WP_REST_Request                 $request
	 * @param string                           $_
	 * @param array                            $handler
	 *
	 * @return null|\WP_Error
	 */
	public function conform_request_to_schema( $response, $request, $_, $handler ) {

		if ( $request->get_method() === 'DELETE' ) {
			return $response;
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( empty( $handler['ite_route'] ) || ! $handler['ite_route'] instanceof Route ) {
			return $response;
		}

		/** @var Route $route */
		$route      = $handler['ite_route'];
		$request    = Request::from_wp( $request );
		$schema     = $route->get_schema();
		$query_args = $route->get_query_args();

		if ( ! $schema && ( $request->get_method() === 'POST' || $request->get_method() === 'PUT' ) ) {
			return $response;
		}

		if ( ! $query_args && $request->get_method() === 'GET' ) {
			return $response;
		}

		$factory       = new Factory(
			$this->schema_storage,
			$this->uri_retreiver,
			Constraint::CHECK_MODE_COERCE_TYPES | Constraint::CHECK_MODE_APPLY_DEFAULTS
		);
		$validator     = new Validator( $factory );
		$schema_object = $this->schema_storage->getSchema( url_for_schema( $schema['title'] ) );

		$to_validate = array();

		$types_to_check = $request->get_method() === 'GET' ? array( 'GET' ) : array( 'JSON', 'POST' );
		$properties     = $request->get_method() === 'GET' ? $query_args : $schema['properties'];

		foreach ( $properties as $property => $_ ) {
			if ( $request->has_param( $property, $types_to_check ) ) {
				$to_validate[ $property ] = $request[ $property ];
			}
		}

		if ( empty( $to_validate ) ) {
			return $response;
		}

		$to_validate = json_decode( json_encode( $to_validate ) );

		if ( $request->get_method() === 'GET' ) {
			$schema_object = json_decode( json_encode( array(
				'type'       => 'object',
				'properties' => $properties
			) ) );
		}

		$validator->validate( $to_validate, $schema_object );

		foreach ( json_decode( json_encode( $to_validate ), true ) as $prop => $value ) {
			$request[ $prop ] = $value;
		}

		if ( $validator->isValid() ) {
			return null;
		}

		$invalid_params = array();

		foreach ( $validator->getErrors() as $error ) {
			$invalid_params[ $error['property'] ] = $error['message'];
		}

		return new \WP_Error(
			'rest_invalid_param',
			sprintf( __( 'Invalid parameter(s): %s' ), implode( ', ', array_keys( $invalid_params ) ) ),
			array( 'status' => 400, 'params' => $invalid_params )
		);
	}

	/**
	 * Transform a schema to properly adhere to JSON schema.
	 *
	 * @since 2.0.0
	 *
	 * @param array $schema
	 *
	 * @return array
	 */
	protected function transform_schema( $schema ) {

		if ( ! isset( $schema['properties'] ) ) {
			return $schema;
		}

		$required = array();

		foreach ( $schema['properties'] as $property => $config ) {
			if ( ! empty( $config['required'] ) ) {
				$required[] = $property;
			}

			unset( $schema['properties'][ $property ]['required'] );

			if ( isset( $config['type'] ) && $config['type'] === 'object' ) {
				$schema['properties'][ $property ] = $this->transform_schema( $config );
			}
		}

		if ( $required ) {
			$schema['required'] = $required;
		}

		return $schema;
	}

	/**
	 * Generate the endpoint args for the server.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Route\v1 $route
	 * @param string                          $verb
	 *
	 * @return array
	 */
	protected function generate_endpoint_args_for_server( Route $route, $verb ) {

		if ( $route instanceof VariableSchema && in_array( $verb, $route->schema_varies_on(), true ) ) {
			$schema = $route->get_schema_for_method( $verb );
		} else {
			$schema = $route->get_schema();
		}

		$schema_properties = ! empty( $schema['properties'] ) ? $schema['properties'] : array();
		$endpoint_args     = array();

		foreach ( $schema_properties as $field_id => $params ) {

			// Arguments specified as `readonly` are not allowed to be set.
			if ( ! empty( $params['readonly'] ) ) {
				continue;
			}

			$endpoint_args[ $field_id ] = array(
				'validate_callback' => false,
				'sanitize_callback' => false,
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
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Route\v1 $route
	 *
	 * @return array
	 */
	protected function generate_query_args_for_server( Route $route ) {

		$args            = $route->get_query_args();
		$args['context'] = $this->get_context_param( $route, array( 'default' => 'view' ) );

		foreach ( $args as $arg ) {
			if ( ! isset( $arg['sanitize_callback'] ) ) {
				$arg['sanitize_callback'] = false;
			}

			if ( ! isset( $arg['validate_callback'] ) ) {
				$arg['validate_callback'] = false;
			}
		}

		return $args;
	}

	/**
	 * Get the magical context param.
	 *
	 * Ensures consistent description between endpoints, and populates enum from schema.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Route\v1 $route
	 * @param array                           $args
	 *
	 * @return array
	 */
	protected function get_context_param( Route $route, $args = array() ) {
		$param_details = array(
			'description' => __( 'Scope under which the request is made; determines fields present in response.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'string',
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
	 * @since 2.0.0
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
	 * @since 2.0.0
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
