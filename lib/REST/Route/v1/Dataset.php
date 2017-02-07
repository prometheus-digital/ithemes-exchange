<?php
/**
 * Dataset route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Dataset
 *
 * @package iThemes\Exchange\REST\Route\v1
 */
class Dataset extends Base implements Getable {

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$dataset = $request->get_param( 'dataset', 'URL' );

		$data = it_exchange_get_data_set( $dataset, $request->get_query_params() );

		if ( ! $data ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_dataset',
				__( 'Invalid datset.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::BAD_REQUEST )
			);
		}

		return new \WP_REST_Response( array( 'data' => $data ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return 'datasets/(?P<dataset>\w+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return array(); }
}