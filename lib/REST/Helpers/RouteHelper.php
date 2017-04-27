<?php
/**
 * REST Route helper.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Helpers;

use Doctrine\Common\Collections\Criteria;
use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Errors;
use iThemes\Exchange\REST\Fields\Field;
use iThemes\Exchange\REST\Fields\QueryArg;
use iThemes\Exchange\REST\Fields\ResponseModifier;
use iThemes\Exchange\REST\Fields\Serializer;
use iThemes\Exchange\REST\Request;

/**
 * Class RouteHelper
 *
 * @package iThemes\Exchange\REST\Helpers
 */
class RouteHelper {

	/** @var \ITE_RESTful_Object_Type|\ITE_Object_Type_With_Capabilities */
	private $object_type;

	/** @var Serializer */
	private $serializer;

	/** @var Field[] */
	private $fields;

	/** @var QueryArg[] */
	private $query_args;

	/** @var array|null */
	private $query_arg_schema;

	/** @var string */
	private $config_file = '';

	/** @var bool */
	private $config_parsed = false;

	/**
	 * Helper constructor.
	 *
	 * @param \ITE_Object_Type $object_type
	 * @param Serializer       $serializer
	 * @param Field[]          $fields
	 * @param QueryArg[]       $query_args
	 */
	public function __construct( \ITE_Object_Type $object_type, Serializer $serializer, array $fields, array $query_args ) {
		$this->object_type = $object_type;
		$this->serializer  = $serializer;

		foreach ( $fields as $field ) {
			$this->fields[ $field->get_attribute() ] = $field;
		}

		foreach ( $query_args as $query_arg ) {
			$this->query_args[ $query_arg->get_attribute() ] = $query_arg;
		}
	}

	/**
	 * Lazily initialize a route helper from a config file.
	 *
	 * The config file should return an array with two keys, 'fields' and 'query', containing an array of
	 * Field objects and QueryArg objects respectively.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Object_Type $type
	 * @param Serializer       $serializer
	 * @param string           $config_file
	 *
	 * @return RouteHelper
	 * @throws \InvalidArgumentException
	 */
	public static function from_file( \ITE_Object_Type $type, Serializer $serializer, $config_file ) {

		if ( ! file_exists( $config_file ) ) {
			throw new \InvalidArgumentException( "Config file not found ({$config_file})" );
		}

		$helper              = new RouteHelper( $type, $serializer, array(), array() );
		$helper->config_file = $config_file;

		return $helper;
	}

	/**
	 * Does the given auth scope have permission to access the given object.
	 *
	 * @since 2.0.0
	 *
	 * @param Request     $request
	 * @param AuthScope   $scope
	 * @param \ITE_Object $object
	 *
	 * @return bool|\WP_Error
	 */
	public function can_get_object( Request $request, AuthScope $scope, \ITE_Object $object = null ) {

		if ( ! $object ) {
			return $this->not_found_error( $scope );
		}

		if ( $this->object_type->has_capabilities() && ! $scope->can( $this->object_type->get_view_capability(), $object ) ) {
			return Errors::cannot_view();

		}

		foreach ( $this->get_query_args() as $key => $query_arg ) {
			if ( ! $request->has_param( $key, array( 'GET', 'defaults' ) ) ) {
				continue;
			}

			$value = $request->get_param( $key );

			if ( ! $query_arg->scope_can_use( $scope, $value ) ) {
				return Errors::cannot_use_query_var( $key );
			}

			if ( ! $query_arg->is_valid( $value ) ) {
				return Errors::invalid_query_var_usage( $key );
			}
		}

		return true;
	}

	/**
	 * Get a collection of objects matching the given request.
	 *
	 * @since 2.0.0
	 *
	 * @param Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function get_collection( Request $request ) {

		$criteria = Criteria::create();
		$params   = array();

		foreach ( $this->get_query_args() as $key => $query_arg ) {
			if ( ! $request->has_param( $key, array( 'GET', 'defaults' ) ) ) {
				continue;
			}

			$params[ $key ] = $request->get_param( $key );
		}

		$query_args = $this->get_query_args();

		foreach ( $params as $key => $value ) {
			$query_args[ $key ]->add_criteria( $criteria, $value, $params );
		}

		$response = $this->serialize_objects( $request, $this->object_type->get_objects( $criteria, $total ) );

		$response->it_exchange_total = $total;

		foreach ( $this->get_query_args() as $query_arg ) {
			if ( $query_arg instanceof ResponseModifier ) {
				$query_arg->modify_response( $response, $request );
			}
		}

		return $response;
	}

	/**
	 * Does the given auth scope have permission to access the collection matching the given request.
	 *
	 * This checks both list permissions and query arg usage.
	 *
	 * @since 2.0.0
	 *
	 * @param Request   $request
	 * @param AuthScope $scope
	 *
	 * @return bool|\WP_Error
	 */
	public function can_get_collection( Request $request, AuthScope $scope ) {

		if ( $this->object_type->has_capabilities() && ! $scope->can( $this->object_type->get_list_capability() ) ) {
			return Errors::cannot_list();
		}

		foreach ( $this->get_query_args() as $key => $query_arg ) {
			if ( ! $request->has_param( $key, array( 'GET', 'defaults' ) ) ) {
				continue;
			}

			$value = $request->get_param( $key );

			if ( ! $query_arg->scope_can_use( $scope, $value ) ) {
				return Errors::cannot_use_query_var( $key );
			}

			if ( ! $query_arg->is_valid( $value ) ) {
				return Errors::invalid_query_var_usage( $key );
			}
		}

		return true;
	}

	/**
	 * Update an object according to the given request.
	 *
	 * @since 2.0.0
	 *
	 * @param Request     $request
	 * @param \ITE_Object $object
	 */
	public function update_object( Request $request, \ITE_Object $object ) {
		$to_update = $this->get_changed_fields( $request, $object );
		$fields    = $this->get_fields();

		foreach ( $to_update as $key => $value ) {
			$fields[ $key ]->update( $object, $value );
		}
	}

	/**
	 * Does the given auth scope have permission to update this object.
	 *
	 * This will also check permission for individual fields, but only fields that have changed.
	 *
	 * @since 2.0.0
	 *
	 * @param Request     $request
	 * @param AuthScope   $scope
	 * @param \ITE_Object $object
	 *
	 * @return bool|\WP_Error
	 */
	public function can_update_object( Request $request, AuthScope $scope, \ITE_Object $object = null ) {

		if ( ! $object ) {
			return $this->not_found_error( $scope, 'edit' );
		}

		if ( $this->object_type->has_capabilities() && ! $scope->can( $this->object_type->get_edit_capability(), $object ) ) {
			return Errors::cannot_edit();
		}

		$fields        = $this->get_fields();
		$changed       = $this->get_changed_fields( $request, $object );
		$no_permission = array();

		foreach ( $changed as $key => $value ) {
			if ( ! $fields[ $key ]->scope_can_set( $scope, $value ) ) {
				$no_permission[ $key ] = $fields[ $key ];
			}
		}

		if ( ! $no_permission ) {
			return true;
		}

		return new \WP_Error(
			'it_exchange_rest_cannot_set_fields',
			sprintf(
				__( 'Sorry, you cannot set the following field(s): %s', 'it-l10n-ithemes-exchange' ),
				implode( ',', array_keys( $no_permission ) )
			),
			array( 'fields' => array_keys( $no_permission ) )
		);
	}

	/**
	 * Create an object from the given request.
	 *
	 * @since 2.0.0
	 *
	 * @param Request $request
	 *
	 * @return \ITE_Object
	 */
	public function create_object( Request $request ) {

		$attributes = array();

		foreach ( $this->get_fields() as $key => $field ) {
			if ( ! $request->has_param( $key ) ) {
				continue;
			}

			$attributes[ $key ] = $request->get_param( $key );
		}

		return $this->object_type->create_object( $attributes );
	}

	/**
	 * Does the given auth scope have permission to create an object with the given fields.
	 *
	 * This will check for a blanket create object permission as well as check the individual fields that have been
	 * passed.
	 *
	 * @since 2.0.0
	 *
	 * @param Request   $request
	 * @param AuthScope $scope
	 *
	 * @return bool|\WP_Error
	 */
	public function can_create_object( Request $request, AuthScope $scope ) {

		if ( $this->object_type->has_capabilities() && ! $scope->can( $this->object_type->get_create_capability() ) ) {
			return Errors::cannot_create();
		}

		$no_permission = array();

		foreach ( $this->get_fields() as $key => $field ) {
			if ( ! $request->has_param( $key ) ) {
				continue;
			}

			if ( ! $field->scope_can_set( $scope, $request->get_param( $field ) ) ) {
				$no_permission[ $key ] = $field;
			}
		}

		if ( ! $no_permission ) {
			return true;
		}

		return new \WP_Error(
			'it_exchange_rest_cannot_set_fields',
			sprintf(
				__( 'Sorry, you cannot set the following field(s): %s', 'it-l10n-ithemes-exchange' ),
				implode( ',', array_keys( $no_permission ) )
			),
			array( 'fields' => array_keys( $no_permission ) )
		);
	}

	/**
	 * Delete the given object.
	 *
	 * @since 2.0.0
	 *
	 * @param Request     $request
	 * @param \ITE_Object $object
	 *
	 * @return bool|\WP_Error
	 */
	public function delete_object( Request $request, \ITE_Object $object ) {

		if ( ! $this->object_type->delete_object_by_id( $object->get_ID() ) ) {
			return new \WP_Error(
				'it_exchange_rest_unable_to_delete',
				__( 'Unable to delete this object.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::INTERNAL_SERVER_ERROR )
			);
		}

		return true;
	}

	/**
	 * Does the given auth scope have permission to delete the given object.
	 *
	 * @since 2.0.0
	 *
	 * @param Request     $request
	 * @param AuthScope   $scope
	 * @param \ITE_Object $object
	 *
	 * @return bool|\WP_Error
	 */
	public function can_delete_object( Request $request, AuthScope $scope, \ITE_Object $object = null ) {

		if ( ! $object ) {
			return $this->not_found_error( $scope, 'delete' );
		}

		if ( $this->object_type->has_capabilities() && ! $scope->can( $this->object_type->get_delete_capability(), $object ) ) {
			return Errors::cannot_delete();
		}

		return true;
	}

	/**
	 * Serialize an object into a REST response.
	 *
	 * @since 2.0.0
	 *
	 * @param Request     $request
	 * @param \ITE_Object $object
	 *
	 * @return \WP_REST_Response
	 */
	public function serialize_object( Request $request, \ITE_Object $object ) {

		$data  = $this->serializer->serialize( $object, $request['context'] );
		$links = $this->serializer->generate_links( $object, $request['context'] );

		$response = new \WP_REST_Response( $data, \WP_Http::CREATED );

		if ( $links ) {
			$response->add_links( $links );
		}

		return $response;
	}

	/**
	 * Serialize multiple objects into a REST response.
	 *
	 * @since 2.0.0
	 *
	 * @param Request       $request
	 * @param \ITE_Object[] $objects
	 *
	 * @return \WP_REST_Response
	 */
	public function serialize_objects( Request $request, array $objects ) {

		$context    = $request['context'];
		$query_args = $request->get_query_params();
		$data       = array();

		foreach ( $objects as $object ) {
			$serialized = $this->serializer->serialize( $object, $context, $query_args );
			$links      = $this->serializer->generate_links( $object, $context );

			foreach ( $links as $rel => $rel_links ) {
				$serialized['_links'][ $rel ] = array();

				foreach ( $rel_links as $link ) {
					$serialized['_links'][ $rel ][] = $link;
				}
			}

			$data[] = $serialized;
		}

		return new \WP_REST_Response( $data );
	}

	/**
	 * Get the schema definition for this object type.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_endpoint_schema() { return $this->serializer->get_schema(); }

	/**
	 * Get the schema properties for this object type's query args.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_query_arg_schema_properties() {

		if ( $this->query_arg_schema !== null ) {
			return $this->query_arg_schema;
		}

		$this->query_arg_schema = array();

		foreach ( $this->get_query_args() as $query_arg ) {
			$this->query_arg_schema[ $query_arg->get_attribute() ] = $query_arg->get_schema();
		}

		return $this->query_arg_schema;
	}

	/**
	 * Get the fields belonging to the route.
	 *
	 * @since 2.0.0
	 *
	 * @return Field[]
	 */
	protected function get_fields() {

		if ( $this->config_file && ! $this->config_parsed ) {
			$this->parse_config();
		}

		return $this->fields;
	}

	/**
	 * Get the query args belonging to the route.
	 *
	 * @since 2.0.0
	 *
	 * @return QueryArg[]
	 */
	protected function get_query_args() {

		if ( $this->config_file && ! $this->config_parsed ) {
			$this->parse_config();
		}

		return $this->query_args;
	}

	/**
	 * Parse the configuration file.
	 *
	 * @since 2.0.0
	 */
	protected function parse_config() {
		$config = require $this->config_file;

		if ( ! is_array( $config ) || ! isset( $config['fields'] ) || ! isset( $config['query'] ) ) {
			throw new \UnexpectedValueException( "Invalid config file format ({$this->config_file})" );
		}

		/** @var Field $field */
		foreach ( $config['fields'] as $field ) {
			$this->fields[ $field->get_attribute() ] = $field;
		}

		$this->serializer->set_fields( $this->fields );

		/** @var QueryArg $query */
		foreach ( $config['query'] as $query ) {
			$this->query_args[ $query->get_attribute() ] = $query;
		}

		$this->config_parsed = true;
	}

	/**
	 * Generate an error when the given item cannot be found depending on the scope's permission.
	 *
	 * @since 2.0.0
	 *
	 * @param AuthScope $scope
	 * @param string    $type
	 *
	 * @return \WP_Error
	 */
	protected function not_found_error( AuthScope $scope, $type = 'view' ) {

		if ( ! $this->object_type->has_capabilities() ) {
			return Errors::not_found();
		}

		if ( $scope->can( $this->object_type->get_list_capability() ) ) {
			return Errors::not_found();
		}

		$method = "cannot_{$type}";

		return Errors::$method();
	}

	/**
	 * Get the fields that have changed.
	 *
	 * @since 2.0.0
	 *
	 * @param Request     $request
	 * @param \ITE_Object $object
	 *
	 * @return array
	 */
	protected function get_changed_fields( Request $request, \ITE_Object $object ) {

		$changed = array();

		foreach ( $this->get_fields() as $key => $field ) {
			if ( $request->has_param( $key ) ) {

				$value = $request->get_param( $key );

				if ( $value === $field->serialize( $object ) ) {
					continue;
				}

				$changed[ $key ] = $value;
			}
		}

		return $changed;
	}
}