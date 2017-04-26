<?php
/**
 * Reusable PerPage Query Arg parameter.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Fields;

use Doctrine\Common\Collections\Criteria;
use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Request;

/**
 * Class PerPageQueryArg
 *
 * @package iThemes\Exchange\REST\Fields
 */
class PerPageQueryArg implements QueryArg, ResponseModifier {

	/**
	 * @inheritDoc
	 */
	public function get_attribute() { return 'per_page'; }

	/**
	 * @inheritDoc
	 */
	public function get_schema() {
		return array(
			'description' => __( 'Maximum number of items to be returned in result set.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'integer',
			'default'     => 10,
			'minimum'     => 1,
			'maximum'     => 100,
		);
	}

	/**
	 * @inheritDoc
	 */
	public function scope_can_use( AuthScope $scope, $value = '' ) { return true; }

	/**
	 * @inheritDoc
	 */
	public function is_valid( $value ) { return true; }

	/**
	 * @inheritDoc
	 */
	public function add_criteria( Criteria $criteria, $value, array $all_query_args ) {
		$criteria->setMaxResults( $value );
	}

	/** @inheritdoc */
	public function modify_response( \WP_REST_Response $response, Request $request ) {

		$page     = $request->get_param( 'page', 'GET' );
		$per_page = $request->get_param( $this->get_attribute(), 'GET' ) ?: get_option( 'posts_per_page' );

		$total       = isset( $response->it_exchange_total ) ? $response->it_exchange_total : 0;
		$total_pages = $total ? ceil( $total / $per_page ) : 0;

		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', $total_pages );

		$base_pagination_url = add_query_arg( $request->get_query_params(), rest_url( $request->get_route() ) );

		$response->link_header( 'first', add_query_arg( 'page', 1, $base_pagination_url ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;

			$prev_page = min( $prev_page, $total_pages );

			$response->link_header( 'prev', add_query_arg( 'page', $prev_page, $base_pagination_url ) );
		}

		if ( $total_pages > $page ) {
			$next_page = $page + 1;

			$response->link_header( 'next', add_query_arg( 'page', $next_page, $base_pagination_url ) );
		}

	}
}