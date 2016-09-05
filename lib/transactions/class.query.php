<?php
/**
 * Transaction Query class.
 *
 * @since   1.36.0
 * @license GPLv2
 */
use IronBound\DB\Query\FluentQuery;
use IronBound\DB\WP\PostMeta;
use IronBound\DB\WP\Posts;

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
	 * Get a query var.
	 *
	 * @since 1.36.0
	 *
	 * @param string $query_var
	 * @param string $default
	 *
	 * @return mixed|string
	 */
	public function get( $query_var, $default = '' ) {
		return isset( $this->args[ $query_var ] ) ? $this->args[ $query_var ] : $default;
	}

	/**
	 * Set a query var.
	 *
	 * @since 1.36.0
	 *
	 * @param string $query_var
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set( $query_var, $value ) {
		$this->args[ $query_var ] = $value;

		return $this;
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
			'return_value'    => 'object', // count, object, or any field.
			'distinct'        => false,
			'calc_found_rows' => true,
			'eager_load'      => array(),
			'ID'              => null,
			'customer'        => null,
			'customer_email'  => null,
			'method'          => null,
			'method_id'       => null,
			'status'          => null,
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

		do_action( 'it_exchange_transaction_query_before', $this->query, $this );

		$this->build_query();

		do_action( 'it_exchange_transaction_query_after', $this->query, $this );

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
		} elseif ( $this->args['items_per_page'] !== - 1 && ! $this->args['calc_found_rows'] ) {
			$this->query->take( $this->args['items_per_page'] );
			$this->query->offset( $this->args['items_per_page'] * ( $this->args['page'] - 1 ) );
		}

		$in_or_not_in = array(
			'ID',
			'customer',
			'customer_email',
			'method',
			'method_id',
			'status',
			'hash',
			'cart_id',
			'cleared',
			'parent'
		);

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

	/**
	 * Build a transaction query from args that would be traditionally passed to `WP_Query`.
	 *
	 * @since 1.36.0
	 *
	 * @param array $wp_args
	 *
	 * @return \ITE_Transaction_Query|null
	 */
	public static function from_wp_args( array $wp_args ) {

		if ( ! $wp_args['suppress_filters'] ) {
			return null;
		}

		$meta_to_fields = array(
			'_it_exchange_transaction_method'    => 'method',
			'_it_exchange_transaction_method_id' => 'method_id',
			'_it_exchange_transaction_status'    => 'status',
			'_it_exchange_customer_id'           => 'customer',
			'_it_exchange_cart_id'               => 'cart_id',
			'_it_exchange_transaction_hash'      => 'hash',
		);

		$args = array();

		if ( ! empty( $wp_args['fields'] ) && $wp_args['fields'] === 'ids' ) {
			$args['return_value'] = 'ID';
		} elseif ( ! empty( $wp_args['fields'] ) && $wp_args['fields'] === 'id=>parent' ) {
			$args['return_value'] = 'parent';
		}

		if ( ! empty( $wp_args['post_status'] ) && in_array( $wp_args['post_status'], array( 'publish', 'any' ) ) ) {
			unset( $wp_args['post_status'] );
		}

		if ( ! empty( $wp_args['date_query'] ) ) {
			$args['order_date'] = $wp_args['date_query'];
		}

		if ( $wp_args['orderby'] === 'date' ) {
			$args['order'] = array( 'order_date' => $wp_args['order'] );
		} elseif ( in_array( $wp_args['orderby'], array( 'ID', 'parent' ), true ) ) {
			$args['order'] = array( $wp_args['orderby'] => $wp_args['order'] );
		} elseif ( $wp_args['orderby'] === 'meta_key' && array_key_exists( $wp_args['meta_key'], $meta_to_fields ) ) {
			$args['order'] = array(
				$meta_to_fields[ $wp_args['meta_key'] ] => $wp_args['order']
			);
		} elseif ( $wp_args['orderby'] === 'none' ) {
			$wp_args['order'] = array();
		} else {
			return null;
		}

		if ( ! empty( $wp_args['meta_key'] ) && ! empty( $wp_args['meta_value'] ) ) {
			if ( array_key_exists( $wp_args['meta_key'], $meta_to_fields ) ) {

				$key = $meta_to_fields[ $wp_args['meta_key'] ];

				if ( isset( $wp_args['meta_compare'] ) &&
				     in_array( $wp_args['meta_compare'], array( '!=', 'NOT IN' ), true )
				) {
					$args[ $key . '__not_in' ] = (array) $wp_args['meta_value'];
				} else {
					$args[ $key . '__in' ] = (array) $wp_args['meta_value'];
				}
			} else {
				return null;
			}
		}

		$meta_query = $wp_args['meta_query'];

		foreach ( $wp_args['meta_query'] as $key => $value ) {
			if ( $key === 'relation' && $value === 'OR' ) {
				return null;
			}

			// Something wrong with user input
			if ( $key !== 'relation' && ! is_array( $value ) ) {
				return null;
			}

			if ( ! isset( $value['key'] ) ) {
				continue;
			}

			// If the meta key doesn't map to a field, bail.
			if ( ! isset( $meta_to_fields[ $value['key'] ] ) ) {
				continue;
			}

			unset( $meta_query[ $key ] );

			$field = $meta_to_fields[ $value['key'] ];

			if ( isset( $value['compare'] ) &&
			     in_array( $value['compare'], array( '!=', 'NOT IN' ), true )
			) {
				$args[ $field . '__not_in' ] = (array) $value['value'];
			} else {
				$args[ $field . '__in' ] = (array) $value['value'];
			}
		}

		if ( ! empty( $wp_args['post__in'] ) ) {
			$args['ID__in'] = $wp_args['post__in'];
		}

		if ( ! empty( $wp_args['post__not_in'] ) ) {
			$args['ID__not_in'] = $wp_args['post__not_in'];
		}

		if ( empty( $wp_args['nopaging'] ) ) {
			if ( isset( $wp_args['posts_per_page'] ) && $wp_args['posts_per_page'] !== - 1 ) {
				$args['items_per_page'] = $wp_args['posts_per_page'];
			}

			if ( ! empty( $wp_args['paged'] ) ) {
				$args['page'] = $wp_args['paged'];
			}
		}

		$query = new self( $args );

		$needs_join = array( 'post_status' );
		$intersect  = array_intersect_key( $wp_args, array_flip( $needs_join ) );

		if ( $intersect ) {
			/** @noinspection ExceptionsAnnotatingAndHandlingInspection */
			$query->query->join( new Posts(), 'ID', 'ID', '=', function ( FluentQuery $query ) use ( $intersect ) {
				foreach ( $intersect as $key => $value ) {
					$query->and_where( $key, '=', $value );
				}
			} );
		}

		// What we do for backwards compatibility :)
		$post_process = null;
		$post_process =
			function ( FluentQuery $fq, ITE_Transaction_Query $t_query )
			use ( $query, $wp_args, &$post_process, $meta_query ) {
				if ( $query !== $t_query ) {
					return;
				}

				if ( ! empty( $wp_args['offset'] ) && empty( $wp_args['nopaging'] ) ) {
					$fq->offset( $wp_args['offset'] );
				}

				if ( $meta_query ) {
					$fq->where_meta( $meta_query, new PostMeta(), 'post' );
				}

				remove_action( 'it_exchange_transaction_query_after', $post_process );
			};

		add_action( 'it_exchange_transaction_query_after', $post_process, 10, 2 );

		return $query;
	}
}