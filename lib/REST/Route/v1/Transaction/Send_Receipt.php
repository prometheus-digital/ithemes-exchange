<?php
/**
 * Send Receipt endpoint.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Transaction;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Errors;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Send_Receipt
 *
 * @package iThemes\Exchange\REST\Route\v1\Transaction
 */
class Send_Receipt extends Base implements Postable {

	/** @var \IT_Exchange_Email_Notifications */
	private $notifications;

	/**
	 * Send_Receipt constructor.
	 *
	 * @param \IT_Exchange_Email_Notifications $notifications
	 */
	public function __construct( \IT_Exchange_Email_Notifications $notifications ) { $this->notifications = $notifications; }

	/**
	 * @inheritDoc
	 */
	public function handle_post( Request $request ) {

		/** @var \IT_Exchange_Transaction $transaction */
		$transaction = $request->get_route_object( 'transaction_id' );
		$email       = $request['email'];

		if ( $email && $email !== $transaction->get_customer_email() ) {
			$notification = $this->notifications->get_notification( $transaction->has_parent() ? 'renewal-receipt' : 'receipt' );

			if ( $notification && $notification->is_active() ) {
				$recipient = new \IT_Exchange_Email_Recipient_Email( $email );
				$email     = new \IT_Exchange_Email( $recipient, $notification, array(
					'transaction' => $transaction,
					'customer'    => $transaction->get_customer()
				) );

				$this->notifications->get_sender()->send( $email );
			}
		} else {
			$this->notifications->send_purchase_emails( $transaction, false );
		}

		return new \WP_REST_Response( null, \WP_Http::ACCEPTED );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( Request $request, AuthScope $scope ) {

		if ( ! $scope->can( 'edit_it_transaction', $request->get_route_object( 'transaction_id' ) ) ) {
			return Errors::cannot_edit();
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
	public function get_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'send-receipt',
			'type'       => 'object',
			'properties' => array(
				'email' => array(
					'description' => __( 'The email to send the receipt to.', 'it-l10n-ithemes-exchange' ),
					'oneOf'       => array(
						array(
							'description' => __( 'Send to the transaction email address.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'enum'        => array( '' ),
						),
						array(
							'description' => __( 'Override the email address to send the receipt to.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'format'      => 'email',
						),
					)
				),
			),
		);
	}
}
