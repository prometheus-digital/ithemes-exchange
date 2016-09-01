<?php
/**
 * Transaction Query class.
 *
 * @since   1.36.0
 * @license GPLv2
 */
use IronBound\DB\Query\FluentQuery;

/**
 * Class ITE_Transactions_Query
 */
class ITE_Transaction_Query {

	/** @var FluentQuery */
	private $query;

	/** @var array */
	private $args = array();

	/** @var bool */
	private $queried = false;

	/**
	 * ITE_Transactions_Query constructor.
	 *
	 * @param array $args
	 */
	public function __construct( array $args = array() ) {

		$this->query = IT_Exchange_Transaction::query();
		$this->args  = array_merge( $this->get_default_args(), $args );
	}

	/**
	 * Return the total number of results, disregarding pagination.
	 *
	 * @since 1.36.0
	 *
	 * @return int
	 */
	public function total() {
		return $this->query()->total();
	}

	/**
	 * Fetch the results.
	 *
	 * @since 1.36.0
	 *
	 * @return IT_Exchange_Transaction[]|int
	 */
	public function results() {

		if ( $this->args['return_value'] === 'count' ) {
			return $this->query()->results()->get( 'count' );
		}

		return $this->query()->results()->toArray();
	}

	/**
	 * Get the SQL statement for this query.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function _get_sql() {
		return $this->query()->_get_sql();
	}

	/**
	 * Get the default arguments.
	 *
	 * @since 1.36.0
	 *
	 * @return array
	 */
	protected function get_default_args() {
		return array(
			'order'           => array(),
			'items_per_page'  => - 1,
			'page'            => 1,
			'return_value'    => 'object',
			'distinct'        => false,
			'calc_found_rows' => true,
			'eager_load'      => array(),
			'ID'              => null,
			'customer'        => null,
			'customer_email'  => null,
			'method'          => null,
			'method_id'       => null,
			'hash'            => null,
			'cart_id'         => null,
			'cleared'         => null,
			'parent'          => null,
			'order_date'      => array(), // WP_Date_Query args
			'total'           => null,
			'subtotal'        => null,
			'billing'         => array(), // An array of address columns to values
			'shipping'        => array(),
			'items'           => array(),
		);
	}

	/**
	 * Get a default arg.
	 *
	 * @since 2.0
	 *
	 * @param string $arg
	 *
	 * @return mixed
	 */
	protected function get_default_arg( $arg ) {

		$args = $this->get_default_args();

		if ( isset( $args[ $arg ] ) ) {
			return $args[ $arg ];
		} else {
			throw new \InvalidArgumentException();
		}
	}

	/**
	 * Perform the query.
	 *
	 * @since 1.36.0
	 */
	protected function query() {

		if ( $this->queried ) {
			return $this->query;
		}

		do_action( 'it_exchange_transaction_query', $this->query, $this->args, $this );

		$this->build_query();
		$this->query->results();
		$this->queried = true;

		return $this->query;
	}

	/**
	 * Build the query based on the arguments.
	 *
	 * @since 1.36.0
	 */
	protected function build_query() {

		if ( $this->args['return_value'] === 'count' ) {
			$this->query->expression( 'COUNT', 'ID', 'count' );
		} elseif ( is_array( $this->args['return_value'] ) ) {
			$this->query->select( $this->args['return_value'] );
		} elseif ( $this->args['return_value'] !== 'object' ) {
			$this->query->select_single( $this->args['return_value'] );
		}

		if ( $this->args['distinct'] ) {
			$this->query->distinct();
		}

		foreach ( $this->args['order'] as $column => $direction ) {
			$this->query->order_by( $column, $direction );
		}

		if ( $this->args['items_per_page'] !== - 1 && $this->args['calc_found_rows'] ) {
			$this->query->paginate( $this->args['page'], $this->args['items_per_page'] );
		} elseif ( $this->args['items_per_page'] !== -1 && ! $this->args['calc_found_rows'] ) {
			$this->query->take( $this->args['items_per_page'] );
			$this->query->offset( $this->args['items_per_page'] * ( $this->args['page'] - 1 ) );
		}

		$in_or_not_in = array( 'ID', 'customer', 'customer_email', 'method', 'method_id', 'hash', 'cart_id', 'cleared', 'parent' );

		foreach ( $in_or_not_in as $arg ) {
			$column = $arg;

			if ( $arg === 'customer' ) {
				$column = 'customer_id';
			}

			$this->parse_in_or_not_in_query( $column, $arg );
		}

		foreach ( array( 'billing', 'shipping' ) as $type ) {
			$this->parse_location( $type, $type );
		}

		foreach ( array( 'subtotal', 'total' ) as $amount ) {
			$this->parse_amount( $amount, $amount );
		}

		$this->parse_items();

		if ( $this->args['order_date'] ) {
			$this->query->where_date( $this->args['order_date'], 'order_date' );
		}

		if ( $this->args['eager_load'] ) {
			$this->query->with( array_merge( array( 'ID' ), (array) $this->args['eager_load'] ) );
		}
	}

	/**
	 * Parse an in or not in query.
	 *
	 * @since 1.36.0
	 *
	 * @param string $column Column name.
	 * @param string $arg    Argument name.
	 */
	protected function parse_in_or_not_in_query( $column, $arg ) {

		$in     = isset( $this->args[ $arg . '__in' ] ) ? $this->args[ $arg . '__in' ] : array();
		$not_in = isset( $this->args[ $arg . '__not_in' ] ) ? $this->args[ $arg . '__not_in' ] : array();

		if ( $this->args[ $arg ] !== null ) {
			$in = array( $this->args[ $arg ] );
		}

		if ( $in && $not_in ) {
			$this->query->and_where( $column, true, $in, function ( FluentQuery $query ) use ( $column, $not_in ) {
				$query->and_where( $column, false, $not_in );
			} );
		} elseif ( $in ) {
			$this->query->and_where( $column, true, $in );
		} elseif ( $not_in ) {
			$this->query->and_where( $column, false, $not_in );
		}
	}

	/**
	 * Parse an amount query.
	 *
	 * Automatically checks for the argument, and the argument appended with operators.
	 *
	 * @since 1.36.0
	 *
	 * @param string $column
	 * @param string $arg
	 */
	protected function parse_amount( $column, $arg ) {

		$map = array(
			'eq'  => true,
			'lt'  => '<',
			'gt'  => '>',
			'lte' => '<=',
			'gte' => '>='
		);

		if ( $this->args[ $arg ] !== null ) {
			$this->query->and_where( $column, true, $this->args[ $arg ] );
		}

		foreach ( $map as $english => $operator ) {
			$key = $arg . '__' . $english;

			if ( isset( $this->args[ $key ] ) ) {
				$this->query->and_where( $column, $operator, $this->args[ $key ] );
			}
		}
	}

	/**
	 * Parse a location query.
	 *
	 * @since 1.36.0
	 *
	 * @param string $column
	 * @param string $arg
	 */
	protected function parse_location( $column, $arg ) {

		if ( ! $this->args[ $arg ] ) {
			return;
		}

		$where = $this->args[ $arg ];

		$this->query->join( new ITE_Saved_Address_Table(), $column, 'pk', '=', function ( FluentQuery $query ) use ( $where ) {
			$query->and_where( $where );
		} );
	}

	/**
	 * Parse an items query.
	 *
	 * @since 1.36.0
	 */
	protected function parse_items() {

		if ( ! $this->args['items'] ) {
			return;
		}

		$items = $this->args['items'];

		/** @noinspection ExceptionsAnnotatingAndHandlingInspection */
		$this->query->join(
			new ITE_Transaction_Line_Item_Table(), 'ID', 'transaction', '=',
			function ( FluentQuery $query ) use ( $items ) {
				foreach ( $items as $type => $object_ids ) {
					$query->and_where( 'type', '=', $type, function ( FluentQuery $query ) use ( $object_ids ) {
						$query->and_where( 'object_id', true, $object_ids );
					} );
				}
			}
		);
	}
}