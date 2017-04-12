<?php
/**
 * Clear sessions rest route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Tools;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route;

/**
 * Class ClearSessions
 *
 * @package iThemes\Exchange\REST\Route\v1\Tools
 */
class ClearSessions extends Route\Base implements Postable {

	/**
	 * @inheritDoc
	 */
	public function handle_post( Request $request ) {

		switch ( $request['type'] ) {
			case 'all':
				it_exchange_db_delete_all_sessions();
				break;
			case 'active':
				it_exchange_db_delete_active_sessions();
				break;
			case 'expired':
			default:
				it_exchange_db_session_cleanup();
				break;
		}

		return new \WP_REST_Response( null, \WP_Http::NO_CONTENT );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( Request $request, AuthScope $scope ) {
		return $scope->can( 'manage_options' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return 'tools/clear-sessions'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'clear-sessions-tool',
			'type'       => 'object',
			'properties' => array(
				'type' => array(
					'type'        => 'string',
					'description' => __( 'The type of sessions to clear.', 'it-l10n-ithemes-exchange' ),
					'default'     => 'expired',
					'enum'        => array( 'all', 'active', 'expired' )
				)
			),
		);
	}
}