<?php
/**
 * Refund model.
 *
 * @since   1.36.0
 * @license GPLv2
 */
use IronBound\DB\Extensions\Meta\ModelWithMeta;

/**
 * Class ITE_Refund
 *
 * @property int                      $ID
 * @property float                    $amount
 * @property \IT_Exchange_Transaction $transaction
 * @property string                   $reason
 * @property string                   $gateway_id
 * @property \WP_User|null            $issued_by
 * @property \DateTime                $created_at
 * @property \DateTime                $updated_at
 */
class ITE_Refund extends ModelWithMeta {

	/**
	 * @inheritDoc
	 */
	protected static function _do_create( array $attributes = array() ) {

		/** @var ITE_Refund $refund */
		$refund = parent::_do_create( $attributes );

		if ( $refund && $refund->transaction ) {

			/**
			 * Fires when a refund has been added to a transaction.
			 *
			 * @since 1.36.0
			 *
			 * @param \ITE_Refund              $refund
			 * @param \IT_Exchange_Transaction $this
			 */
			do_action( 'it_exchange_add_transaction_refund', $refund, $refund->transaction );
		}

		return $refund;
	}

	/**
	 * @inheritDoc
	 */
	public function get_pk() { return $this->ID; }

	/**
	 * @inheritDoc
	 */
	protected static function get_table() { return static::$_db_manager->get( 'ite-refunds' ); }

	/**
	 * @inheritDoc
	 */
	public static function get_meta_table() {
		return static::$_db_manager->get( 'ite-refunds-meta' );
	}
}