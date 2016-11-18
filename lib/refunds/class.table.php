<?php
/**
 * Refunds Table.
 *
 * @since   2.0.0
 * @license GPLv2
 */
use IronBound\DB\Table\Column\DateTime;
use IronBound\DB\Table\Column\DecimalBased;
use IronBound\DB\Table\Column\ForeignModel;
use IronBound\DB\Table\Column\ForeignUser;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;
use IronBound\DB\Table\TimestampedTable;

/**
 * Class ITE_Refunds_Table
 */
class ITE_Refunds_Table extends \IronBound\DB\Table\BaseTable implements TimestampedTable {

	/** @var array */
	private $columns = array();

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) { return $wpdb->prefix . 'ite_refunds'; }

	/**
	 * @inheritDoc
	 */
	public function get_slug() { return 'ite-refunds'; }

	/**
	 * @inheritDoc
	 */
	public function get_columns() {

		if ( $this->columns ) {
			return $this->columns;
		}

		$this->columns = array(
			'ID'          =>
				new IntegerBased( 'BIGINT', 'ID', array( 'unsigned', 'NOT NULL', 'auto_increment' ), array( 20 ) ),
			'amount'      => new DecimalBased( 'DECIMAL', 'amount', array(), array( 16, 6 ) ),
			'transaction' => new ForeignModel( 'transaction', 'IT_Exchange_Transaction', new ITE_Transactions_Table() ),
			'reason'      => new StringBased( 'VARCHAR', 'reason', array(), array( 255 ) ),
			'issued_by'   => new ForeignUser( 'issued_by' ),
			'gateway_id'  => new StringBased( 'VARCHAR', 'gateway_id', array(), array( 255 ) ),
			'created_at'  => new DateTime( 'created_at' ),
			'updated_at'  => new DateTime( 'updated_at' ),
		);

		return $this->columns;
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'ID'          => 0,
			'amount'      => 0.00,
			'transaction' => 0,
			'refund'      => '',
			'issued_by'   => 0,
			'gateway_id'  => '',
			'created_at'  => '',
			'updated_at'  => '',
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary_key() { return 'ID'; }

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_created_at_column() { return 'created_at'; }

	/**
	 * @inheritDoc
	 */
	public function get_updated_at_column() { return 'updated_at'; }

	/**
	 * @inheritDoc
	 */
	protected function get_keys() {
		$keys   = parent::get_keys();
		$keys[] = "KEY transaction (transaction)";

		return $keys;
	}
}
