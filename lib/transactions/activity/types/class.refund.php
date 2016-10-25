<?php
/**
 * Contains the status change activity type.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Txn_Status_Activity
 */
class IT_Exchange_Txn_Refund_Activity extends IT_Exchange_Txn_AbstractActivity {

	/**
	 * Retrieve a refund activity item.
	 *
	 * This is used by the activity factory, and should not be called directly.
	 *
	 * @since 1.34
	 *
	 * @internal
	 *
	 * @param int                            $id
	 * @param IT_Exchange_Txn_Activity_Actor $actor
	 *
	 * @return IT_Exchange_Txn_Refund_Activity|null
	 */
	public static function make( $id, IT_Exchange_Txn_Activity_Actor $actor = null ) {

		$post = get_post( $id );

		if ( ! $post instanceof WP_Post ) {
			return null;
		}

		return new self( $post, $actor );
	}

	/**
	 * Get the type of the activity.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_type() {
		return 'refund';
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {

		$refund = $this->get_refund();

		if ( ! $refund ) {
			return __( 'Refund issued.', 'it-l10n-ithemes-exchange' );
		}

		if ( $refund->reason ) {
			return sprintf(
				__( 'Refund of %1$s issued. Reason: %2$s', 'it-l10n-ithemes-exchange' ),
				it_exchange_format_price( $refund->amount ),
				$refund->reason
			);
		}

		return sprintf(
			__( 'Refund of %1$s issued.', 'it-l10n-ithemes-exchange' ),
			it_exchange_format_price( $refund->amount )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function is_public() { return false; }

	/**
	 * Get the refund model.
	 *
	 * @since 1.36.0
	 *
	 * @return ITE_Refund|null
	 */
	public function get_refund() { return ITE_Refund::get( get_post_meta( $this->get_ID(), '_refund', true ) ); }
}