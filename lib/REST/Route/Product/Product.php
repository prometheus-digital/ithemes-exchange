<?php
/**
 * Single Product Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Product;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;
use iThemes\Exchange\REST\Route\Product\Serializer;

/**
 * Class Product
 *
 * @package iThemes\Exchange\REST\Route\Product\Product
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

		$data = $this->serializer->serialize( $product );

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
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {

		$product_id = $request->get_param( 'product_id', 'URL' );

		$cap = 'read_it_product';

		if ( get_post_status( $product_id ) !== 'published' ) {

			$cap = 'edit_it_product';

			if ( ! $user ) {
				return new \WP_Error(
					'it_exchange_rest_forbidden_context',
					__( 'Sorry, you are not allowed to access this product.', 'it-l10n-ithemes-exchange' ),
					array( 'status' => \WP_Http::UNAUTHORIZED )
				);
			}
		}

		if ( $request['context'] === 'edit' ) {
			$cap = 'edit_it_product';
		}

		if ( $cap === 'read_it_product' ) {
			return true;
		}

		if ( ! $user || ! user_can( $user->wp_user, $cap, $product_id ) ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to access this product.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::UNAUTHORIZED )
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