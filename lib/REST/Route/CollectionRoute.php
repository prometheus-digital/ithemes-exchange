<?php
/**
 * Collection Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Helpers\RouteHelper as Helper;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;

/**
 * Class CollectionRoute
 *
 * @package iThemes\Exchange\REST
 */
class CollectionRoute extends Base implements Getable, Postable {

	/** @var Helper */
	private $helper;

	/** @var int */
	private $version;

	/** @var string */
	private $path;

	/**
	 * CollectionRoute constructor.
	 *
	 * @param Helper $helper
	 * @param string $path
	 * @param int    $version
	 */
	public function __construct( Helper $helper, $path, $version = 1 ) {
		$this->helper  = $helper;
		$this->version = $version;
		$this->path    = $path;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {
		return $this->helper->get_collection( $request );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, AuthScope $scope ) {
		return $this->helper->can_get_collection( $request, $scope );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_post( Request $request ) {

		$object = $this->helper->create_object( $request );

		if ( ! $object ) {
			return new \WP_Error(
				'it_exchange_rest_unable_to_create',
				__( 'Unable to create an object with the given attributes.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::INTERNAL_SERVER_ERROR )
			);
		}

		$request['context'] = 'edit';

		return $this->helper->serialize_object( $request, $object );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( Request $request, AuthScope $scope ) {
		return $this->helper->can_create_object( $request, $scope );
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