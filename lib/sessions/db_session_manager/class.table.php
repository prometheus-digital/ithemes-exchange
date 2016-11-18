<?php
/**
 * Load the Session table.
 *
 * @since   2.0.0
 * @license GPLv2
 */
use IronBound\DB\Table\Column\DateTime;
use IronBound\DB\Table\Column\ForeignUser;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;
use IronBound\DB\Table\TimestampedTable;

/**
 * Class ITE_Sessions_Table
 */
class ITE_Sessions_Table extends \IronBound\DB\Table\BaseTable implements TimestampedTable {

	/** @var array */
	private $columns = array();

	public function get_table_name( \wpdb $wpdb ) {
		return $wpdb->prefix . 'ite_sessions';
	}

	public function get_slug() {
		return 'ite-sessions';
	}

	public function get_columns() {

		if ( $this->columns ) {
			return $this->columns;
		}

		$this->columns = array(
			'ID'           => new StringBased( 'VARCHAR', 'ID', array( 'NOT NULL' ), array( 32 ) ),
			'cart_id'      => new StringBased( 'VARCHAR', 'cart_id', array(), array( 32 ) ),
			'customer'     => new ForeignUser( 'customer' ),
			'data'         => new StringBased( 'LONGTEXT', 'data', array( 'NOT NULL' ) ),
			'is_main'      => new IntegerBased( 'TINYINT', 'is_main', array( 'NOT NULL' ), array( 1 ) ),
			'purchased_at' => new DateTime( 'purchased_at' ),
			'expires_at'   => new DateTime( 'expires_at' ),
			'created_at'   => new DateTime( 'created_at' ),
			'updated_at'   => new DateTime( 'updated_at' ),
		);

		return $this->columns;
	}

	public function get_column_defaults() {
		return array(
			'ID'           => '',
			'cart_id'      => null,
			'customer'     => null,
			'data'         => '',
			'is_main'      => true,
			'expires_at'   => time() + (int) apply_filters( 'it_exchange_db_session_expiration', 2 * DAY_IN_SECONDS ),
			'purchased_at' => null,
			'created_at'   => '',
			'updated_at'   => '',
		);
	}

	public function get_primary_key() {
		return 'ID';
	}

	public function get_version() {
		return 1;
	}

	public function get_created_at_column() {
		return 'created_at';
	}

	public function get_updated_at_column() {
		return 'updated_at';
	}
}
