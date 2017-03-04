<?php
/**
 * Payment Token Table.
 *
 * @since   2.0.0
 * @license GPLv2
 */
use IronBound\DB\Extensions\Trash\TrashTable;
use IronBound\DB\Table\Column\Boolean;
use IronBound\DB\Table\Column\DateTime;
use IronBound\DB\Table\Column\Enum;
use IronBound\DB\Table\Column\ForeignUser;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;

/**
 * Class ITE_Payment_Tokens_Table
 */
class ITE_Payment_Tokens_Table extends \IronBound\DB\Table\BaseTable implements TrashTable {

	/** @var array */
	private $columns = array();

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) { return $wpdb->prefix . 'ite_payment_tokens'; }

	/**
	 * @inheritDoc
	 */
	public function get_slug() { return 'ite-payment-tokens'; }

	/**
	 * @inheritDoc
	 */
	public function get_columns() {

		if ( $this->columns ) {
			return $this->columns;
		}

		$this->columns = array(
			'ID'         =>
				new IntegerBased( 'BIGINT', 'ID', array( 'unsigned', 'NOT NULL', 'auto_increment' ), array( 20 ) ),
			'customer'   => new ForeignUser( 'customer' ),
			'token'      => new StringBased( 'VARCHAR', 'token', array(), array( 255 ) ),
			'label'      => new StringBased( 'VARCHAR', 'label', array(), array( 255 ) ),
			'redacted'   => new StringBased( 'VARCHAR', 'redacted', array(), array( 32 ) ),
			'primary'    => new Boolean( 'primary' ),
			'gateway'    => new StringBased( 'VARCHAR', 'gateway', array(), array( 64 ) ),
			'type'       => new StringBased( 'VARCHAR', 'type', array(), array( 32 ) ),
			'mode'       => new Enum( array( 'live', 'sandbox' ), 'mode' ),
			'deleted'    => new DateTime( 'deleted' ),
			'expires_at' => new DateTime( 'expires_at' ),
		);

		return $this->columns;
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'ID'         => 0,
			'customer'   => 0,
			'token'      => '',
			'label'      => '',
			'redacted'   => '',
			'primary'    => false,
			'type'       => 'cc',
			'mode'       => '',
			'deleted'    => null,
			'expires_at' => null,
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
	public function get_deleted_at_column() { return 'deleted'; }
}
