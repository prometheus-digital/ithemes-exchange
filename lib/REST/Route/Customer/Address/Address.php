<?php
/**
 * Address Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Customer\Address;

use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Putable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Address
 *
 * @package iThemes\Exchange\REST\Route\Customer\Address
 */
class Address extends Base implements Getable, Putable, Deletable {

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

		$address = \ITE_Saved_Address::get( $request->get_param( 'address_id', 'URL' ) );

		return new \WP_REST_Response( $this->serializer->serialize( $address ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {

		$address = \ITE_Saved_Address::get( $request->get_param( 'address_id', 'URL' ) );

		if ( ! $address ) {
			return new \WP_Error(
				'it_exchange_rest_not_found',
				__( 'Address not found.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::NOT_FOUND )
			);
		}

		if ( ! $user ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_permissions',
				__( 'You must be logged-in to view this address.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::UNAUTHORIZED )
			);
		}

		if ( ! $address->customer && ! user_can( $user->wp_user, 'edit_users' ) ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_permissions',
				__( 'You do not have permission to view this address.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::FORBIDDEN )
			);
		}

		if ( ! user_can( $user->wp_user, 'edit_user', $address->customer->get_ID() ) ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_permissions',
				__( 'You do not have permission to view this address.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::FORBIDDEN )
			);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_put( Request $request ) {

		$address = \ITE_Saved_Address::get( $request->get_param( 'address_id', 'URL' ) );

		foreach ( $request->get_json_params() as $key => $value ) {
			$address[ $key ] = $value;
		}

		if ( ! $address->save() ) {
			return new \WP_Error(
				'it_exchange_rest_unable_to_save',
				__( 'Unable to update this address.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::INTERNAL_SERVER_ERROR )
			);
		}

		return new \WP_REST_Response( $this->serializer->serialize( $address ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( Request $request, \IT_Exchange_Customer $user = null ) {
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
	public function user_can_delete( Request $request, \IT_Exchange_Customer $user = null ) {
		return true; // Cascade to get.
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
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}