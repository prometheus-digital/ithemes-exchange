<?php
/**
 * Log DB table.
 *
 * @since   2.0.0
 * @license GPLv2
 */
use IronBound\DB\Table\Column\IntegerBased;

/**
 * Class ITE_Log_DB_Table
 */
class ITE_Log_DB_Table extends \IronBound\DBLogger\Table {

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array_merge( parent::get_columns(), array(
			'level_num' => new IntegerBased( 'SMALLINT', 'level_num', array( 'unsigned' ) )
		) );
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array_merge( parent::get_column_defaults(), array(
			'level_num' => 0,
		) );
	}
}