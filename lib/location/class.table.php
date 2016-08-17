<?php
/**
 * Saved Address Table.
 *
 * @since   1.36.0
 * @license GPLv2
 */
use IronBound\DB\Table\Column\Enum;
use IronBound\DB\Table\Column\ForeignUser;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;

/**
 * Class ITE_Saved_Address_Table
 */
class ITE_Saved_Address_Table extends \IronBound\DB\Table\BaseTable {
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
		return array(
			'pk'           =>
				new IntegerBased( 'BIGINT', 'pk', array( 'unsigned', 'NOT NULL', 'auto_increment' ), array( 20 ) ),
			'customer'     => new ForeignUser( 'customer' ),
			'label'        => new StringBased( 'VARCHAR', 'label', array(), array( '191' ) ),
			'primary'      => new IntegerBased( 'TINYINT', 'primary', array(), array( 1 ) ),
			'company-name' => new StringBased( 'VARCHAR', 'company-name', array(), array( '191' ) ),
			'first-name'   => new StringBased( 'VARCHAR', 'first-name', array(), array( '191' ) ),
			'last-name'    => new StringBased( 'VARCHAR', 'last-name', array(), array( '191' ) ),
			'address1'     => new StringBased( 'VARCHAR', 'address1', array(), array( '191' ) ),
			'address2'     => new StringBased( 'VARCHAR', 'address2', array(), array( '191' ) ),
			'city'         => new StringBased( 'VARCHAR', 'city', array(), array( '100' ) ),
			'state'        => new StringBased( 'VARCHAR', 'state', array(), array( '3' ) ),
			'zip'          => new StringBased( 'VARCHAR', 'zip', array(), array( '20' ) ),
			'country'      => new StringBased( 'VARCHAR', 'country', array(), array( '3' ) ),
			'email'        => new StringBased( 'VARCHAR', 'email', array(), array( '191' ) ),
			'phone'        => new StringBased( 'VARCHAR', 'phone', array(), array( '25' ) ),
			'type'         => new Enum( array( 'billing', 'shipping' ), 'type', 'billing', false ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'pk'           => 0,
			'customer'     => 0,
			'label'        => '',
			'primary'      => false,
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
			'type'         => 'billing',
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary_key() {
		return 'pk';
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
			'KEY customer__type__primary (customer,type,primary)',
			'KEY country (country)'
		) );
	}
}