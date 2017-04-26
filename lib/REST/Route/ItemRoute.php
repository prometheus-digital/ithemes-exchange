<?php
/**
 * Single Item Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Helpers\RouteHelper as Helper;
use iThemes\Exchange\REST\Putable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\RouteObjectExpandable;

/**
 * Class ItemRoute
 *
 * @package iThemes\Exchange\REST\Route
 */
class ItemRoute extends Base implements Getable, Putable, Deletable, RouteObjectExpandable {

	/** @var \ITE_RESTful_Object_Type */
	private $object_type;

	/** @var string */
	private $object_id_url_attribute;

	/** @var int */
	private $version = 1;

	/** @var string */
	private $path;

	/** @var Helper */
	private $helper;

	/**
	 * ItemRoute constructor.
	 *
	 * @param Helper                   $helper
	 * @param \ITE_RESTful_Object_Type $object_type
	 * @param string                   $object_id_url_attribute
	 * @param string                   $path
	 * @param int                      $version
	 */
	public function __construct( Helper $helper, \ITE_RESTful_Object_Type $object_type, $object_id_url_attribute, $path, $version = 1 ) {
		$this->object_type             = $object_type;
		$this->helper                  = $helper;
		$this->object_id_url_attribute = $object_id_url_attribute;
		$this->version                 = $version;
		$this->path                    = $path;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$object = $request->get_route_object( $this->object_id_url_attribute );

		return $this->helper->serialize_object( $request, $object );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, AuthScope $scope ) {

		$object = $request->get_route_object( $this->object_id_url_attribute );

		return $this->helper->can_get_object( $request, $scope, $object );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_put( Request $request ) {

		$object = $request->get_route_object( $this->object_id_url_attribute );

		$this->helper->update_object( $request, $object );

		$request['context'] = 'edit';

		return $this->helper->serialize_object( $object, $request );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( Request $request, AuthScope $scope ) {

		$object = $request->get_route_object( $this->object_id_url_attribute );

		return $this->helper->can_update_object( $request, $scope, $object );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_delete( Request $request ) {

		$object  = $request->get_route_object( $this->object_id_url_attribute );
		$deleted = $this->helper->delete_object( $request, $object );

		if ( is_wp_error( $deleted ) ) {
			return $deleted;
		}

		return new \WP_REST_Response( null, \WP_Http::NO_CONTENT );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( Request $request, AuthScope $scope ) {

		$object = $request->get_route_object( $this->object_id_url_attribute );

		return $this->helper->can_delete_object( $request, $scope, $object );

	}

	/**
	 * @inheritDoc
	 */
	public function get_route_object_map() {

		static $map = null;

		if ( $map === null ) {
			$type = $this->object_type;
			$map  = array(
				$this->object_id_url_attribute => function ( $id ) use ( $type ) {
					return $type->get_object_by_id( $id );
				}
			);
		}

		return $map;
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return $this->version; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return $this->path; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return $this->helper->get_query_arg_schema_properties(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->helper->get_endpoint_schema(); }
}