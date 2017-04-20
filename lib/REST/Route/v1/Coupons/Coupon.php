<?php
/**
 * Single Coupon Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Coupons;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Errors;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Putable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;
use iThemes\Exchange\REST\RouteObjectExpandable;

/**
 * Class Coupon
 *
 * @package iThemes\Exchange\REST\Route\v1
 */
class Coupon extends Base implements Getable, Putable, Deletable, RouteObjectExpandable {

	/** @var \ITE_Coupon_Type */
	private $type;

	/**
	 * Coupon constructor.
	 *
	 * @param \ITE_Coupon_Type $type
	 */
	public function __construct( \ITE_Coupon_Type $type ) { $this->type = $type; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {
		$data = $this->type->get_rest_serializer()->serialize( $request->get_route_object( 'coupon_id' ) );

		return new \WP_REST_Response( $data );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, AuthScope $scope ) {

		/** @var \IT_Exchange_Coupon $coupon */
		$coupon = $request->get_route_object( 'coupon_id' );

		if ( ! $coupon && $scope->can( 'edit_it_coupons' ) ) {
			return Errors::cannot_view();
		}

		if ( ! $coupon ) {
			return Errors::not_found();
		}

		$cap = $request['context'] === 'edit' ? 'edit_it_coupon' : 'read_post';

		if ( ! $scope->can( $cap, $coupon->get_ID() ) ) {
			return Errors::cannot_view();
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_put( Request $request ) {

		$coupon = $this->type->update_from_rest( $request );

		if ( is_wp_error( $coupon ) ) {
			return $coupon;
		}

		return new \WP_REST_Response( $this->type->get_rest_serializer()->serialize( $coupon ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( Request $request, AuthScope $scope ) {

		/** @var \IT_Exchange_Coupon $coupon */
		$coupon = $request->get_route_object( 'coupon_id' );

		if ( ! $coupon && $scope->can( 'edit_it_coupons' ) ) {
			return Errors::cannot_view();
		}

		if ( ! $coupon ) {
			return Errors::not_found();
		}

		if ( ! $scope->can( 'edit_it_coupon', $coupon->get_ID() ) ) {
			return Errors::cannot_view();
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_delete( Request $request ) {

		$coupon = $request->get_route_object( 'coupon_id' );

		$r = wp_delete_post( $coupon->ID, $request['force'] );

		if ( $r === false ) {
			return new \WP_Error(
				'it_exchange_rest_delete_failed',
				__( 'Failed to delete the coupon.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::INTERNAL_SERVER_ERROR )
			);
		}

		return new \WP_REST_Response( null, \WP_Http::NO_CONTENT );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( Request $request, AuthScope $scope ) {

		$coupon = $request->get_route_object( 'coupon_id' );

		if ( ! $coupon || ! $scope->can( 'delete_it_coupon', $coupon->ID ) ) {
			return Errors::cannot_delete();
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return '(?P<coupon_id>\d+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_route_object_map() { return array( 'coupon_id' => 'it_exchange_get_coupon' ); }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() {
		return array(
			'force' => array(
				'type'        => 'boolean',
				'default'     => false,
				'description' => __( 'Whether to bypass trash and force deletion.', 'it-l10n-ithemes-exchange' ),
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->type->get_schema(); }
}