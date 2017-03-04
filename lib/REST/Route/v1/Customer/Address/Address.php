<?php
/**
 * Address Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Customer\Address;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Errors;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Putable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;
use iThemes\Exchange\REST\RouteObjectExpandable;

/**
 * Class Address
 *
 * @package iThemes\Exchange\REST\Route\v1\Customer\Address
 */
class Address extends Base implements Getable, Putable, Deletable, RouteObjectExpandable {

	/** @var Serializer */
	private $serializer;

	/**
	 * Address constructor.
	 *
	 * @param Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		/** @var \ITE_Saved_Address $address */
		$address = $request->get_route_object( 'address_id' );

		return new \WP_REST_Response( $this->serializer->serialize( $address ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, AuthScope $scope ) {

		if ( ! $this->check_exists( $request ) ) {
			return Errors::not_found();
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_put( Request $request ) {

		$address_id  = $request->get_param( 'address_id', 'URL' );
		$customer_id = $request->get_param( 'customer_id', 'URL' );

		// Purposely bypass route object cache
		$current = \ITE_Saved_Address::get( $address_id );
		$address = \ITE_Saved_Address::get( $address_id );

		foreach ( $request->get_json_params() as $key => $value ) {
			$address[ $key ] = $value;
		}

		if ( $request['label'] !== null ) {
			$address->label = $request['label'];
		}

		$saved = \ITE_Saved_Address::convert_to_saved( $address, $current, $address->customer );

		if ( ! $saved ) {
			return new \WP_Error(
				'it_exchange_rest_unable_to_save',
				__( 'Unable to update this address.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::INTERNAL_SERVER_ERROR )
			);
		}

		if ( $saved->get_pk() != $current->get_pk() ) {
			$current->delete();
			$response = new \WP_REST_Response( null, \WP_Http::SEE_OTHER );
			$response->header(
				'Location',
				\iThemes\Exchange\REST\get_rest_url(
					$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\v1\Customer\Address\Address' ),
					array( 'customer_id' => $customer_id, 'address_id' => $saved->get_pk() )
				)
			);

			return $response;
		}

		return new \WP_REST_Response( $this->serializer->serialize( $address ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( Request $request, AuthScope $scope ) {

		if ( ! $this->check_exists( $request ) ) {
			return Errors::not_found();
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_delete( Request $request ) {

		$address = \ITE_Saved_Address::get( $request->get_param( 'address_id', 'URL' ) );
		$address->delete();

		return new \WP_REST_Response( null, \WP_Http::NO_CONTENT );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( Request $request, AuthScope $scope ) {

		if ( ! $this->check_exists( $request ) ) {
			return Errors::not_found();
		}

		return true;
	}

	/**
	 * Check if the address exists.
	 *
	 * @since 2.0.0
	 *
	 * @param Request $request
	 *
	 * @return bool
	 */
	protected function check_exists( Request $request ) {

		/** @var \ITE_Saved_Address $address */
		$address = $request->get_route_object( 'address_id' );

		return $address && $address->customer && $address->customer->get_ID() === (int) $request->get_param( 'customer_id', 'URL' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return '(?P<address_id>\d+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_route_object_map() { return array( 'address_id' => 'ITE_Saved_Address::get' ); }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}