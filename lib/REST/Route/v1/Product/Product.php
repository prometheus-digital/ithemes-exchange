<?php
/**
 * Single Product Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Product;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Errors;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;
use iThemes\Exchange\REST\Route\v1\Product\Serializer;

/**
 * Class Product
 *
 * @package iThemes\Exchange\REST\Route\v1\Product\Product
 */
class Product extends Base implements Getable {

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

		$product = it_exchange_get_product( $request->get_param( 'product_id', 'URL' ) );

		$data = $this->serializer->serialize( $product, $request['context'] );

		$response = new \WP_REST_Response( $data );

		foreach ( $this->serializer->generate_links( $product ) as $rel => $links ) {
			foreach ( $links as $link ) {
				$response->add_link( $rel, $link['href'], $link );
			}
		}

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, AuthScope $scope ) {

		$product_id = $request->get_param( 'product_id', 'URL' );

		if ( ! it_exchange_get_product( $product_id ) ) {
			return Errors::not_found();
		}

		if ( get_post_status( $product_id ) !== 'published' && ! $scope->can( 'edit_it_product' ) ) {
			return Errors::cannot_view();
		}

		if ( in_array( $request['context'], array( 'edit', 'stats' ), true ) && ! $scope->can( 'edit_it_product', $product_id ) ) {
			return Errors::forbidden_context( $request['context'] );
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
	public function get_path() { return '(?P<product_id>\d+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}