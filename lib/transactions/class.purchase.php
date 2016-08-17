<?php
/**
 * Purchase Model.
 *
 * @since   1.36.0
 * @license GPLv2
 */
use IronBound\DB\Relations\HasOne;

/**
 * Class ITE_Purchase
 *
 * @property int                $id
 * @property int                $customer_id
 * @property-read string        $hash
 * @property float              $total
 * @property float              $subtotal
 * @property \DateTime          $order_date
 * @property string             $status
 * @property bool               $cleared
 * @property \ITE_Saved_Address $billing
 * @property \ITE_Saved_Address $shipping
 */
class ITE_Purchase extends \IronBound\DB\Model {

	/**
	 * @inheritDoc
	 */
	public function get_pk() {
		return $this->id;
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return static::$_db_manager->get( 'ite-purchases' );
	}

	/**
	 * Get the corresponding transaction.
	 *
	 * @since 1.36.0
	 *
	 * @return \IT_Exchange_Transaction
	 */
	public function transaction() { return it_exchange_get_transaction( $this->id ); }

	protected function _billing_relation() {
		return new HasOne( 'pk', '\ITE_Saved_Address', $this, 'billing' );
	}

	protected function _shipping_relation() {
		return new HasOne( 'pk', '\ITE_Saved_Address', $this, 'shipping' );
	}
}