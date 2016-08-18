<?php
/**
 * Purchase Table
 *
 * @since   1.36.0
 * @license GPLv2
 */
use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Column\DateTime;
use IronBound\DB\Table\Column\DecimalBased;
use IronBound\DB\Table\Column\ForeignModel;
use IronBound\DB\Table\Column\ForeignPost;
use IronBound\DB\Table\Column\ForeignUser;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;
use IronBound\DB\Table\ForeignKey\DeleteConstrained;

/**
 * Class ITE_Purchase_Table
 */
class ITE_Transactions_Table extends BaseTable implements DeleteConstrained {
	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return $wpdb->prefix . 'ite_transactions';
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'ite-transactions';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			'ID'             => new ForeignPost( 'ID' ),
			'customer_id'    => new ForeignUser( 'customer_id' ),
			'customer_email' => new StringBased( 'VARCHAR', 'customer_email', array(), array( 255 ) ),
			'status'         => new StringBased( 'VARCHAR', 'status', array(), array( 255 ) ),
			'method'         => new StringBased( 'VARCHAR', 'method', array(), array( 32 ) ),
			'method_id'      => new StringBased( 'VARCHAR', 'method_id', array(), array( 128 ) ),
			'hash'           => new StringBased( 'VARCHAR', 'hash', array(), array( 32 ) ),
			'cart_id'        => new StringBased( 'VARCHAR', 'cart_id', array(), array( 32 ) ),
			'total'          => new DecimalBased( 'DECIMAL', 'total', array(), array( 16, 6 ) ),
			'subtotal'       => new DecimalBased( 'DECIMAL', 'subtotal', array(), array( 16, 6 ) ),
			'order_date'     => new DateTime( 'order_date' ),
			'cleared'        => new IntegerBased( 'TINYINT', 'cleared', array(), array( 1 ) ),
			'billing'        => new ForeignModel( 'billing', 'ITE_Saved_Address', new ITE_Saved_Address_Table() ),
			'shipping'       => new ForeignModel( 'shipping', 'ITE_Saved_Address', new ITE_Saved_Address_Table() ),
			'parent'         => new ForeignModel( 'parent', 'IT_Exchange_Transaction', $this ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'ID'             => '',
			'customer_id'    => 0,
			'customer_email' => '',
			'hash'           => '',
			'cart_id'        => '',
			'status'         => '',
			'method'         => '',
			'method_id'      => '',
			'total'          => 0.00,
			'subtotal'       => 0.00,
			'order_date'     => '',
			'cleared'        => false,
			'billing'        => 0,
			'shipping'       => 0,
			'parent'         => 0,
		);
	}

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
			'KEY customer_id (customer_id)',
			'KEY hash (hash)',
			'UNIQUE KEY method__method_id (method,method_id)'
		) );
	}

	/**
	 * @inheritDoc
	 */
	public function get_delete_constraints() {
		return array(
			'billing'  => self::RESTRICT,
			'shipping' => self::RESTRICT,
			'ID'       => self::CASCADE,
		);
	}
}