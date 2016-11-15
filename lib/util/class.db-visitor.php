<?php
/**
 * DB Visitor.
 *
 * @since   1.36.0
 * @license GPLv2
 */
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\Common\Collections\Expr\Value;
use IronBound\DB\Query\FluentQuery;

/**
 * Class ITE_DB_Visitor
 */
class ITE_DB_Visitor extends \Doctrine\Common\Collections\Expr\ExpressionVisitor {

	/** @var FluentQuery */
	private $query;

	/** @var FluentQuery */
	public $current_query;

	public $current_level = - 1;
	public $stack = array();

	/**
	 * ITE_DB_Visitor constructor.
	 *
	 * @param FluentQuery $query
	 */
	public function __construct( FluentQuery $query ) {
		$this->query         = $query;
		$this->current_query = $this->query;
	}

	/**
	 * @inheritDoc
	 */
	public function walkComparison( Comparison $comparison ) {

		$query = $this->stack[ $this->current_level ]['query'];

		if ( isset( $this->stack[ $this->current_level - 1 ] ) ) {
			$nest = $this->stack[ $this->current_level - 1 ]['nest'];
		} else {
			$nest = $this->stack[ $this->current_level ]['nest'];
		}

		$parent                  = isset( $this->stack[ $this->current_level - 1 ] ) ? $this->current_level - 1 : - 1;
		$composite_parent        = $this->find_parent_composite_from_level( $this->current_level );
		$second_composite_parent = $this->find_parent_composite_from_level( $composite_parent );

		// First child expression of a composite. Use second parent.
		if ( isset( $this->stack[ $parent ] ) && $this->stack[ $parent ]['type'] === 'composite' && $second_composite_parent !== - 1 ) {
			$bool = $this->stack[ $second_composite_parent ]['bool'];
		} elseif ( isset( $this->stack[ $composite_parent ] ) ) {
			$bool = $this->stack[ $composite_parent ]['bool'];
		} else {
			$bool = $this->stack[ $this->current_level ]['bool'] ?: 'and';
		}

		$this->do_walk_comparison( $query, $comparison, $nest, $bool );
	}

	/**
	 * Find the nearest parent composite expression from a given level.
	 *
	 * @since 1.36.0
	 *
	 * @param int $level
	 *
	 * @return int
	 */
	protected function find_parent_composite_from_level( $level ) {

		if ( $level === - 1 ) {
			return - 1;
		}

		while ( $level >= 0 && isset( $this->stack[ -- $level ] ) && $this->stack[ $level ]['type'] === 'comparison' ) {

		}

		return isset( $this->stack[ $level ] ) && $this->stack[ $level ]['type'] === 'composite' ? $level : - 1;
	}

	/**
	 * Do a comparison walk.
	 *
	 * @since 1.36.0
	 *
	 * @param FluentQuery  $query
	 * @param Comparison   $comparison
	 * @param Closure|null $nested
	 * @param string       $boolean
	 */
	public function do_walk_comparison( FluentQuery $query, Comparison $comparison, Closure $nested = null, $boolean = 'and' ) {

		$field = $comparison->getField();
		$value = $this->walkValue( $comparison->getValue() );

		switch ( $comparison->getOperator() ) {
			case Comparison::EQ:
			case Comparison::NEQ:
			case Comparison::LT:
			case Comparison::LTE:
			case Comparison::GT:
			case Comparison::GTE:
				$query->where( $field, $comparison->getOperator(), $value, $nested, $boolean );
				break;
			case Comparison::IN:
				$query->where( $field, true, $value, $nested, $boolean );
				break;
			case Comparison::NIN:
				$query->where( $field, false, $value, $nested, $boolean );
				break;
			case Comparison::CONTAINS:
				$query->where( $field, 'LIKE', '%' . $GLOBALS['wpdb']->esc_like( $value ) . '%', $nested, $boolean );
				break;

			default:
				throw new \RuntimeException( "Unknown comparison operator: " . $comparison->getOperator() );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function walkValue( Value $value ) {
		return $value->getValue();
	}

	/**
	 * @inheritDoc
	 */
	public function walkCompositeExpression( CompositeExpression $expr ) {

		$list       = $expr->getExpressionList();
		$nested     = null;
		$composites = $comparisons = array();

		foreach ( $list as $expression ) {
			if ( $expression instanceof CompositeExpression ) {
				$composites[] = $expression;
			} elseif ( $expression instanceof Comparison ) {
				$comparisons[] = $expression;
			}
		}

		$first_comparison = reset( $comparisons );

		if ( count( $comparisons ) > 1 ) {
			$self   = $this;
			$nested = function ( FluentQuery $query ) use ( $comparisons, $self ) {
				for ( $i = 1; $i < count( $comparisons ); $i ++ ) {
					$self->dispatch( $comparisons[ $i ], $query );
				}
			};
		}

		$this->stack[ $this->current_level ]['nest'] = $nested;

		if ( $first_comparison ) {
			$this->dispatch( $first_comparison );
		}

		foreach ( $composites as $composite ) {
			$this->dispatch( $composite );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function dispatch( Expression $expr, FluentQuery $query = null ) {

		$level = - 1;

		if ( $expr instanceof Comparison || $expr instanceof CompositeExpression ) {
			$this->current_level ++;

			$this->stack[ $this->current_level ] = array(
				'type'  => $expr instanceof Comparison ? 'comparison' : 'composite',
				'bool'  => $expr instanceof CompositeExpression ? strtolower( $expr->getType() ) : '',
				'nest'  => null,
				'expr'  => $expr,
				'query' => $query ?: $this->current_query,
			);

			$level = $this->current_level;
		}

		$r = parent::dispatch( $expr );

		if ( $level !== - 1 ) {
			unset( $this->stack[ $level ] );
			$this->current_level --;

			ksort( $this->stack, SORT_NUMERIC );
		}

		return $r;
	}
}