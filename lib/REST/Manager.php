<?php
/**
 * REST Route Manager
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Auth\CustomerAuthScope;
use iThemes\Exchange\REST\Auth\GuestAuthScope;
use iThemes\Exchange\REST\Auth\PublicAuthScope;
use iThemes\Exchange\REST\Middleware\Stack;
use iThemes\Exchange\REST\Route\Base;
use iThemes\Exchange\REST\Route\v1\Cart\Purchase;
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

	const AUTH_STOP_CASCADE = 1;

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

	/** @var AuthScope */
	private $auth_scope;

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

		if ( $route instanceof Base ) {
			$route->set_manager( $this );
		}

		$this->routes[] = $route;

		return $this;
	}

	/**
	 * Get the first route matching a given class.
	 *
	 * @since 2.0.0
	 *
	 * @param string $class
	 *
	 * @return \iThemes\Exchange\REST\Route|null
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
	 * @return \iThemes\Exchange\REST\Route[]
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
	 * Get the auth scope.
	 *
	 * @since 2.0.0
	 *
	 * @return AuthScope
	 */
	public function get_auth_scope() {

		if ( $this->auth_scope === null ) {
			$current_customer = it_exchange_get_current_customer();

			if ( $current_customer instanceof \IT_Exchange_Guest_Customer ) {
				$this->set_auth_scope( new GuestAuthScope( $current_customer ) );
			} elseif ( $current_customer ) {
				$this->set_auth_scope( new CustomerAuthScope( $current_customer ) );
			} else {
				$this->set_auth_scope( new PublicAuthScope() );
			}
		}

		return $this->auth_scope;
	}

	/**
	 * Set the auth scope.
	 *
	 * @since 2.0.0
	 *
	 * @param AuthScope $scope
	 */
	public function set_auth_scope( AuthScope $scope ) {
		$this->auth_scope = $scope;
	}

	/**
	 * Register a route with the server.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Route $route
	 *
	 * @return bool
	 */
	private function register_with_server( Route $route ) {

		if ( $schema = $route->get_schema() ) {
			$this->schemas[ $schema['title'] ] = json_encode( $this->transform_schema( $schema ) );

			if ( $route instanceof VariableSchema ) {
				foreach ( $route->schema_varies_on() as $method ) {
					$method_schema = $route->get_schema_for_method( $method );

					$this->schemas[ $method_schema['title'] . '-' . strtolower( $method ) ] = json_encode( $method_schema );
				}
			}
		}

		$path     = '';
		$building = $route;
		$parents  = array();

		do {
			if ( $building !== $route ) {
				$parents[] = $building;
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
			$manager          = $this;

			$permission = function ( \WP_REST_Request $request ) use ( $manager, $verb, $route, $parents, &$exchange_request ) {

				$exchange_request = $request instanceof Request ? $request : Request::from_wp( $request );
				$exchange_request->set_matched_route_controller( $route );

				$auth = $manager->get_auth_scope();

				$callback = array( $route, 'user_can_' . strtolower( $verb ) );

				if ( ! is_callable( $callback ) ) {
					return false;
				}

				$allowed = call_user_func( $callback, $exchange_request, $auth );

				if ( $allowed === Manager::AUTH_STOP_CASCADE ) {
					return true;
				}

				if ( $allowed !== true ) {
					return $allowed;
				}

				if ( empty( $parents ) ) {
					return $allowed;
				}

				foreach ( $parents as $parent ) {

					$callback = array( $parent, 'user_can_' . strtolower( $verb ) );

					if ( ! is_callable( $callback ) ) {
						if ( is_callable( array( $parent, 'user_can_get' ) ) ) {
							$callback = array( $parent, 'user_can_get' );
						} else {
							continue;
						}
					}

					$allowed = call_user_func( $callback, $exchange_request, $auth );

					if ( $allowed === true ) {
						continue;
					}

					if ( $allowed === Manager::AUTH_STOP_CASCADE ) {
						return true;
					}

					return $allowed;
				}

				return $allowed;
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
		$query_args = $route->get_query_args();

		if ( $route instanceof VariableSchema && in_array( $request->get_method(), $route->schema_varies_on(), true ) ) {
			$schema = $route->get_schema_for_method( $request->get_method() );
			$title  = $schema['title'] . '-' . strtolower( $request->get_method() );
		} else {
			$schema = $route->get_schema();
			$title  = isset( $schema['title'] ) ? $schema['title'] : '';
		}

		if ( ! $schema && ( $request->get_method() === 'POST' || $request->get_method() === 'PUT' ) ) {
			return $response;
		}

		if ( ! $query_args && $request->get_method() === 'GET' ) {
			return $response;
		}

		$schema_object = $this->schema_storage->getSchema( url_for_schema( $title ) );

		$to_validate = array();

		$types_to_check = $request->get_method() === 'GET' ? array( 'GET' ) : array( 'JSON', 'POST' );
		$properties     = $request->get_method() === 'GET' ? $query_args : $schema['properties'];

		foreach ( $properties as $property => $_ ) {
			if ( ! empty( $_['readonly'] ) && $request->has_param( $property, $types_to_check ) ) {
				$to_validate[ $property ] = $request[ $property ];
			}
		}

		if ( empty( $to_validate ) ) {
			return $response;
		}

		if ( $request->get_method() === 'GET' ) {
			$schema_object = json_decode( json_encode( array(
				'type'       => 'object',
				'properties' => $properties
			) ) );
		}

		$data_or_error = $this->validate_params( $to_validate, $schema_object );

		if ( is_wp_error( $data_or_error ) ) {
			return $data_or_error;
		}

		foreach ( $data_or_error as $key => $value ) {
			$request[ $key ] = $value;
		}

		return null;
	}

	/**
	 * Validate parameters.
	 *
	 * @since 2.0.0
	 *
	 * @param $to_validate
	 * @param $schema_object
	 *
	 * @return array|\WP_Error
	 */
	public function validate_params( $to_validate, $schema_object ) {

		$to_validate = json_decode( json_encode( $to_validate ) );
		$validator   = $this->make_validator();

		$validator->validate( $to_validate, $schema_object );

		if ( $validator->isValid() ) {
			$return = array();

			foreach ( json_decode( json_encode( $to_validate ), true ) as $prop => $value ) {
				$return[ $prop ] = $value;
			}

			return $return;
		}

		$invalid_params = array();

		foreach ( $validator->getErrors() as $error ) {
			$invalid_params[ $error['property'] ] = $error['message'];
		}

		return new \WP_Error(
			'rest_invalid_param',
			sprintf( __( 'Invalid parameter(s): %s', 'it-l10n-ithemes-exchange' ), implode( ', ', array_keys( $invalid_params ) ) ),
			array( 'status' => 400, 'params' => $invalid_params )
		);
	}

	/**
	 * Make a Schema validator.
	 *
	 * @since 2.0.0
	 *
	 * @return Validator
	 */
	protected function make_validator() {
		$factory = new Factory(
			$this->schema_storage,
			$this->uri_retreiver,
			Constraint::CHECK_MODE_COERCE_TYPES | Constraint::CHECK_MODE_APPLY_DEFAULTS | Constraint::CHECK_MODE_TYPE_CAST
		);

		return new Validator( $factory );
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
	 * @param \iThemes\Exchange\REST\Route $route
	 * @param string                       $verb
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
	 * @param \iThemes\Exchange\REST\Route $route
	 *
	 * @return array
	 */
	protected function generate_query_args_for_server( Route $route ) {

		$args            = $route->get_query_args();
		$args['context'] = $this->get_context_param( $route, array( 'default' => 'view' ) );

		foreach ( $args as &$arg ) {
			if ( ! isset( $arg['sanitize_callback'] ) ) {
				$arg['sanitize_callback'] = false;
			}

			if ( ! isset( $arg['validate_callback'] ) ) {
				$arg['validate_callback'] = false;
			}

			if ( isset( $arg['arg_options'] ) ) {
				$options = $arg['arg_options'];
				unset( $arg['arg_options'] );
				$arg = array_merge( $args, $options );
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
	 * @param \iThemes\Exchange\REST\Route $route
	 * @param array                        $args
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

		if ( $authed === true || is_wp_error( $authed ) ) {
			return $authed;
		}

		if ( ! $this->is_our_endpoint() ) {
			return $authed;
		}

		if ( empty( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			return $authed;
		}

		$authorization = trim( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) );
		$regex         = '/ITHEMES-EXCHANGE-GUEST\s?email="(\S+)"/i';

		if ( ! preg_match( $regex, $authorization, $matches ) ) {
			return $authed;
		}

		if ( ! it_exchange_is_guest_checkout_enabled() ) {
			return new \WP_Error(
				'it_exchange_rest_guest_checkout_disabled',
				__( 'Guest Checkout is disabled.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => 401 )
			);
		}

		if ( empty( $matches[1] ) || ! is_email( $matches[1] ) ) {
			return new \WP_Error(
				'it_exchange_rest_authentication_failed',
				__( 'Invalid guest email address provided.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => 400 )
			);
		}

		$email = $matches[1];

		$this->set_auth_scope( new GuestAuthScope( it_exchange_get_customer( $email ) ) );
		$GLOBALS['current_user'] = it_exchange_guest_checkout_generate_guest_user_object( $email );

		add_filter( 'it_exchange_get_current_customer', function () use ( $email ) {
			return it_exchange_get_customer( $email );
		} );

		return true;
	}

	/**
	 * Reset the Manager.
	 *
	 * @since 2.0.0
	 */
	public function _reset() {
		$this->initialized    = false;
		$this->routes         = array();
		$this->auth_scope     = null;
		$this->schemas        = array();
		$this->schema_storage = null;
		$this->uri_retreiver  = null;

		remove_filter( 'rest_authentication_errors', array( $this, 'authenticate' ), 20 );
		remove_filter( 'rest_dispatch_request', array( $this, 'conform_request_to_schema' ), 10 );
	}
}
