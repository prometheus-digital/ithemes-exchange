<?php
/**
 * Products Collection Endpoint
 *
 * @since   2.0.0
 * @license GPLv2
 */


namespace iThemes\Exchange\REST\Route\v1\Product;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Products
 *
 * @package iThemes\Exchange\REST\Route\v1\Product\Product
 */
class Products extends Base implements Getable {

	/** @var Serializer */
	private $serializer;

	/**
	 * Product constructor.
	 *
	 * @param Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$page     = $request['page'];
		$per_page = $request['per_page'];

		$args = array(
			'posts_per_page' => $per_page,
			'paged'          => $page,
		);

		$args['show_hidden'] = ! $request['visible_only'];

		if ( $request['search'] ) {
			$args['s'] = sanitize_text_field( $request['search'] );
		}

		$products = it_exchange_get_products( $args, $total );
		$data     = array();

		foreach ( $products as $product ) {
			$serialized = $this->serializer->serialize( $product );
			$links      = $this->serializer->generate_links( $product );

			foreach ( $links as $rel => $rel_links ) {
				$serialized['_links'][ $rel ] = array();

				foreach ( $rel_links as $link ) {
					$serialized['_links'][ $rel ][] = $link;
				}
			}

			$data[] = $serialized;
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

		if ( $request['visible_only'] !== true && ! $scope->can( 'edit_it_products' ) ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_context',
				__( "Sorry, you aren't allowed to filter products by their visibility.", 'it-l10n-ithemes-exchange' ),
				array( 'status' => rest_authorization_required_code() )
			);
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
	public function get_path() { return 'products/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() {
		return array(
			'page'         => array(
				'description' => __( 'Current page of the collection.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			),
			'per_page'     => array(
				'description' => __( 'Maximum number of items to be returned in result set.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'integer',
				'default'     => 10,
				'minimum'     => 1,
				'maximum'     => 100,
			),
			'visible_only' => array(
				'description' => __( 'Whether to only include products that are visible in the store.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'boolean',
				'default'     => true,
			),
			'search'       => array(
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
	public function get_schema() { return $this->serializer->get_schema(); }
}