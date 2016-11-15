<?php
/**
 * Send Receipt endpoint.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Transaction;

use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Send_Receipt
 *
 * @package iThemes\Exchange\REST\Route\Transaction
 */
class Send_Receipt extends Base implements Postable {

	/**
	 * @inheritDoc
	 */
	public function handle_post( Request $request ) {
		it_exchange_email_notifications()->send_purchase_emails( $request->get_param( 'transaction_id', 'URL' ), false );

		return new \WP_REST_Response( '', \WP_Http::ACCEPTED );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( Request $request, \IT_Exchange_Customer $user = null ) {

		if ( ! $user || ! user_can( $user->wp_user, 'edit_it_transaction', $request->get_param( 'transaction_id', 'URL' ) ) ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to edit this transaction.', 'it-l10n-ithemes-exchange' ),
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
	public function get_path() { return 'send_receipt/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return array(); }
}
