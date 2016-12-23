<?php
/**
 * Saved Address Table.
 *
 * @since   2.0.0
 * @license GPLv2
 */
use IronBound\DB\Table\Column\Enum;
use IronBound\DB\Table\Column\ForeignUser;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;

/**
 * Class ITE_Saved_Address_Table
 */
class ITE_Saved_Address_Table extends \IronBound\DB\Table\BaseTable implements \IronBound\DB\Extensions\Trash\TrashTable {

	/** @var array */
	private $columns = array();

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return $wpdb->prefix . 'ite_address';
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'ite-address';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {

		if ( $this->columns ) {
			return $this->columns;
		}

		$this->columns = array(
			'ID'           =>
				new IntegerBased( 'BIGINT', 'ID', array( 'unsigned', 'NOT NULL', 'auto_increment' ), array( 20 ) ),
			'customer'     => new ForeignUser( 'customer' ),
			'label'        => new StringBased( 'VARCHAR', 'label', array( 'NOT NULL' ), array( '191' ) ),
			'company-name' => new StringBased( 'VARCHAR', 'company-name', array( 'NOT NULL' ), array( '191' ) ),
			'first-name'   => new StringBased( 'VARCHAR', 'first-name', array( 'NOT NULL' ), array( '191' ) ),
			'last-name'    => new StringBased( 'VARCHAR', 'last-name', array( 'NOT NULL' ), array( '191' ) ),
			'address1'     => new StringBased( 'VARCHAR', 'address1', array( 'NOT NULL' ), array( '191' ) ),
			'address2'     => new StringBased( 'VARCHAR', 'address2', array( 'NOT NULL' ), array( '191' ) ),
			'city'         => new StringBased( 'VARCHAR', 'city', array( 'NOT NULL' ), array( '100' ) ),
			'state'        => new StringBased( 'VARCHAR', 'state', array( 'NOT NULL' ), array( '100' ) ),
			'zip'          => new StringBased( 'VARCHAR', 'zip', array( 'NOT NULL' ), array( '20' ) ),
			'country'      => new StringBased( 'VARCHAR', 'country', array( 'NOT NULL' ), array( '3' ) ),
			'email'        => new StringBased( 'VARCHAR', 'email', array( 'NOT NULL' ), array( '191' ) ),
			'phone'        => new StringBased( 'VARCHAR', 'phone', array( 'NOT NULL' ), array( '25' ) ),
			'deleted_at'   => new \IronBound\DB\Table\Column\DateTime( 'deleted_at' ),
		);

		return $this->columns;
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'ID'           => null,
			'customer'     => 0,
			'label'        => '',
			'first-name'   => '',
			'last-name'    => '',
			'company-name' => '',
			'address1'     => '',
			'address2'     => '',
			'city'         => '',
			'state'        => '',
			'zip'          => '',
			'country'      => '',
			'email'        => '',
			'phone'        => '',
			'deleted_at'   => null,
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_deleted_at_column() { return 'deleted_at'; }

	/**
	 * @inheritDoc
	 */
	public function get_primary_key() {
		return 'ID';
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() {
		return 1;
	}

	/**
	 * @inheritDoc
	 */
	protected function get_keys() {
		return array_merge( parent::get_keys(), array(
			'KEY customer (customer)',
			'KEY country__state (country,state)'
		) );
	}
}
