<?php
/**
 * Coupon Collection Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Coupons;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Errors;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Coupons
 *
 * @package iThemes\Exchange\REST\Route\v1\Coupons
 */
class Coupons extends Base implements Getable, Postable {

	/** @var \ITE_Coupon_Type */
	private $type;

	/**
	 * Coupons constructor.
	 *
	 * @param \ITE_Coupon_Type $type
	 */
	public function __construct( \ITE_Coupon_Type $type ) { $this->type = $type; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$page     = $request['page'];
		$per_page = $request['per_page'];

		$args = array(
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'type'           => $this->type->get_type(),
		);

		if ( $request['search'] ) {
			$args['s'] = sanitize_text_field( $request['search'] );
		}

		$products = it_exchange_get_coupons( $args, $total );
		$data     = array();

		foreach ( $products as $product ) {
			$serialized = $this->type->get_rest_serializer()->serialize( $product );
			$data[]     = $serialized;
		}

		$total_pages         = $total ? ceil( $total / $per_page ) : 0;
		$base_pagination_url = add_query_arg(
			$request->get_query_params(),
			\iThemes\Exchange\REST\get_rest_url( $this, array( $request->get_url_params() ) )
		);

		$response = new \WP_REST_Response( $data );
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', $total_pages );

		$first_link = add_query_arg( 'page', 1, $base_pagination_url );
		$response->link_header( 'first', $first_link );

		if ( $page > 1 ) {
			$prev_page = $page - 1;

			if ( $prev_page > $total_pages ) {
				$prev_page = $total_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base_pagination_url );
			$response->link_header( 'prev', $prev_link );
		}

		if ( $total_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base_pagination_url );

			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, AuthScope $scope ) {

		if ( ! $scope->can( 'list_it_coupons' ) ) {
			return Errors::cannot_list();
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_post( Request $request ) {
		$coupon = $this->type->update_from_rest( $request );

		if ( is_wp_error( $coupon ) ) {
			return $coupon;
		}

		return new \WP_REST_Response( $this->type->get_rest_serializer()->serialize( $coupon ), \WP_Http::CREATED );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( Request $request, AuthScope $scope ) {

		if ( ! $scope->can( 'create_it_coupons' ) ) {
			return Errors::cannot_create();
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
	public function get_path() { return "coupons/{$this->type->get_type()}/"; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() {
		return array(
			'page'     => array(
				'description' => __( 'Current page of the collection.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			),
			'per_page' => array(
				'description' => __( 'Maximum number of items to be returned in result set.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'integer',
				'default'     => 10,
				'minimum'     => 1,
				'maximum'     => 100,
			),
			'search'   => array(
				'description' => __( 'Limit results to those matching a string.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'string',
				'minLength'   => 3,
				'maxLength'   => 300,
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->type->get_schema(); }
}