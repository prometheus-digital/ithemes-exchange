<?php
/**
 * Logger Class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Logger
 */
class ITE_DB_Logger extends \IronBound\DBLogger\Logger implements ITE_Date_Purgeable_Logger, ITE_Queryable_Logger {

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * @var \IronBound\DBLogger\AbstractTable
	 */
	private $table;


	/**
	 * @inheritDoc
	 */
	public function __construct( \IronBound\DBLogger\AbstractTable $table, \wpdb $wpdb ) {
		parent::__construct( $table, new \IronBound\DB\Query\Simple_Query( $wpdb, $table ) );

		$this->table = $table;
		$this->wpdb  = $wpdb;
	}

	/**
	 * @inheritDoc
	 */
	public function log( $level, $message, array $context = array() ) {
		$context['_level_num'] = ITE_Log_Levels::get_level_severity( $level );
		parent::log( $level, $message, $context );
	}

	/**
	 * @inheritDoc
	 */
	public function purge() {
		return \IronBound\DB\Manager::maybe_empty_table( $this->table, $this->wpdb );
	}

	/**
	 * @inheritDoc
	 */
	public function purge_older_than( $days ) {
		parent::purge( $days, $this->wpdb );

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function query( \Doctrine\Common\Collections\Criteria $criteria ) {

		$query = new \IronBound\DB\Query\FluentQuery( $this->table, $this->wpdb );

		$visitor = new ITE_DB_Visitor( $query );
		$visitor->dispatch( $criteria->getWhereExpression() );

		foreach ( $criteria->getOrderings() as $column => $direction ) {
			$query->order_by( $this->order_by_map( $column ), $direction );
		}

		if ( $criteria->getMaxResults() ) {
			$query->take( $criteria->getMaxResults() );
		}

		if ( $criteria->getFirstResult() ) {
			$query->offset( $criteria->getFirstResult() );
		}

		return array_map( function ( $row ) {
			return new ITE_Log_Item( array(
				'message' => $row['message'],
				'level'   => $row['level'],
				'group'   => $row['lgroup'],
				'time'    => new DateTime( $row['time'] ),
				'user'    => $row['user'],
				'ip'      => $row['ip'],

			) );
		}, $query->results()->toArray() );
	}

	/**
	 * Map requested order by field to internal order by column.
	 *
	 * @since 2.0.0
	 *
	 * @param string $requested
	 *
	 * @return string
	 */
	protected function order_by_map( $requested ) {
		switch ( $requested ) {
			case 'level':
				return 'level_num';
			default:
				return $requested;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_supported_filters() {
		return array(
			'message' => 'message',
			'level'   => 'level',
			'group'   => 'lgroup',
			'user'    => 'user',
			'ip'      => 'ip',
		);
	}
}