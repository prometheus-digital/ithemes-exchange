<?php
/**
 * Transaction Line Item Model.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Transaction_Line_Item_Model
 *
 * @property int         $pk
 * @property string      $id
 * @property-read string $type
 * @property string      $name
 * @property string      $description
 * @property int         $object_id
 * @property float       $amount
 * @property int         $quantity
 * @property float       $total
 * @property bool        $summary_only
 * @property int         $transaction
 * @property \DateTime   $created_at
 * @property \DateTime   $updated_at
 * @property string      $_class
 * @property int         $_parent
 */
class ITE_Transaction_Line_Item_Model extends \IronBound\DB\Extensions\Meta\ModelWithMeta {

	/**
	 * @inheritDoc
	 */
	public function get_pk() {
		return $this->pk;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_meta_table() {
		return static::$_db_manager->get( 'ite-line-items-meta' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return static::$_db_manager->get( 'ite-line-items' );
	}
}