<?php
/**
 * Addresses endpoint.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Customer\Address;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Addresses
 *
 * @package iThemes\Exchange\REST\Route\Customer\Address
 */
class Addresses extends Base implements Getable, Postable {

	/** @var Serializer */
	private $serializer;

	/**
	 * Addresses constructor.
	 *
	 * @param Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {
		$customer_id = $request->get_param( 'customer_id', 'URL' );

		$data = array();

		foreach ( \ITE_Saved_Address::query()->where( 'customer', '=', $customer_id ) as $address ) {
			$data[] = $this->serializer->serialize( $address );
		}

		return new \WP_REST_Response( $data );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {

		if ( ! $user ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_permissions',
				__( 'You must be logged-in to view addresses.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::UNAUTHORIZED )
			);
		}

		return true; // Cascades to Customer route.
	}

	/**
	 * @inheritDoc
	 */
	public function handle_post( Request $request ) {

		$customer_id = $request->get_param( 'customer_id', 'URL' );

		$data    = array_merge( $request->get_body_params(), array( 'customer' => $customer_id ) );
		$address = \ITE_Saved_Address::query()->where( $data )->take( 1 )->first();

		if ( $address ) {
			$response = new \WP_REST_Response( null, \WP_Http::SEE_OTHER );
		} else {
			$address = \ITE_Saved_Address::create( $data );

			if ( ! $address ) {
				return new \WP_Error(
					'it_exchange_rest_unable_to_create',
					__( 'Unable to create address.', 'it-l10n-ithemes-exchange' ),
					array( 'status' => \WP_Http::INTERNAL_SERVER_ERROR )
				);
			}

			$response = new \WP_REST_Response( $this->serializer->serialize( $address ), \WP_Http::CREATED );
		}

		$response->add_link( 'Location', \iThemes\Exchange\REST\get_rest_url(
			$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\Customer\Address\Address' ),
			array( 'customer_id' => $customer_id, 'address_id' => $address->get_pk() )
		) );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( Request $request, \IT_Exchange_Customer $user = null ) {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return 'addresses/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}