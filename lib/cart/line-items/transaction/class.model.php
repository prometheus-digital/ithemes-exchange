<?php
/**
 * Transaction Line Item Model.
 *
 * @since   2.0.0
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

	const CHILDREN_KEY = 'it-exchange-txn-line-item-children';

	/**
	 * @inheritDoc
	 */
	public function get_pk() {
		return $this->pk;
	}

	/**
	 * Get children.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Transaction_Line_Item_Model[]|\IronBound\DB\Collection
	 */
	public function get_children() {
		$ids = wp_cache_get( $this->get_pk(), self::CHILDREN_KEY );

		if ( ! is_array( $ids ) || empty( $ids ) ) {
			$models = static::query()->where( '_parent', true, $this->get_pk() )->results()->toArray();
			$ids    = wp_list_pluck( $models, 'pk' );

			wp_cache_set( $this->get_pk(), $ids, self::CHILDREN_KEY );
		} else {
			$models = array_map( 'ITE_Transaction_Line_Item_Model::get', $ids );
		}

		return array_filter( $models );
	}

	/**
	 * @inheritDoc
	 */
	protected static function boot() {
		parent::boot();

		static::updated( function ( \IronBound\WPEvents\GenericEvent $event ) {

			/** @var ITE_Transaction_Line_Item_Model $model */
			$model = $event->get_subject();

			$changed = $event->get_argument( 'changed' );

			if ( isset( $changed['_parent'] ) ) {
				wp_cache_delete( $model->get_pk(), ITE_Transaction_Line_Item_Model::CHILDREN_KEY );
			}
		} );
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
