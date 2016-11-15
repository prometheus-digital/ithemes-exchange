<?php
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\ExpressionVisitor;
use Doctrine\Common\Collections\Expr\Value;

/**
 * WP User Query Constraints Visitor
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_WP_User_Query_Visitor
 */
class ITE_WP_User_Query_Visitor extends ExpressionVisitor {

	private $args = array(
		'date_query' => array(),
	);

	private $meta_query = array();
	private $meta_depth = - 2;
	private $last_meta = array();

	private $core_fields = array(
		'role',
		's',
		'year',
		'month',
		'week',
		'day',
		'hour',
		'second'
	);

	public function get_args() {
		$args = $this->args;

		if ( empty( $args['date_query'] ) ) {
			unset( $args['date_query'] );
		}

		if ( ! empty( $this->meta_query ) ) {
			$args['meta_query'] = $this->meta_query;
		} elseif ( ! empty( $this->last_meta ) ) {
			$args['meta_query'] = $this->last_meta;
		}

		return $args;
	}

	/**
	 * @inheritDoc
	 */
	public function walkComparison( Comparison $comparison ) {

		switch ( $comparison->getField() ) {
			case 'role':

				if ( $comparison->getOperator() === Comparison::EQ ) {
					$this->args['role'] = $comparison->getValue()->getValue();
				} elseif ( $comparison->getOperator() === Comparison::NEQ ) {
					$this->args['role__not_in'] = array( $comparison->getValue()->getValue() );
				} elseif ( $comparison->getOperator() === Comparison::IN ) {
					$this->args['role__in'] = $comparison->getValue()->getValue();
				} elseif ( $comparison->getOperator() === Comparison::NIN ) {
					$this->args['role__not_in'] = $comparison->getValue()->getValue();
				} else {
					throw new InvalidArgumentException( "Invalid operator for 'role' field." );
				}

				break;

			case 's':

				if ( $comparison->getOperator() === Comparison::EQ ) {
					$this->args['s'] = '*' . $comparison->getValue()->getValue() . '*';
				} else {
					throw new InvalidArgumentException( "Invalid operator for 's' field." );
				}

				break;

			case 'year':
			case 'month':
			case 'week':
			case 'day':
			case 'hour':
			case 'minute':
			case 'second':

				if ( $comparison->getOperator() === Comparison::EQ ) {
					$this->args['date_query'][ $comparison->getField() ] = $comparison->getValue()->getValue();
				} else {
					throw new InvalidArgumentException( "Invalid operator for '{$comparison->getField()}' field." );
				}

				break;

			default:
				// This is a meta key.

				$numeric = array(
					Comparison::LT,
					Comparison::LTE,
					Comparison::GT,
					Comparison::GTE,
				);

				$valid = array_merge( array(
					Comparison::EQ,
					Comparison::NEQ,
					Comparison::CONTAINS,
					Comparison::IN,
					Comparison::NIN,
				), $numeric );

				if ( ! in_array( $comparison->getOperator(), $valid ) ) {
					throw new InvalidArgumentException( "Invalid operator for '{$comparison->getField()}' field." );
				}

				$compare = $comparison->getOperator();

				if ( $compare === Comparison::NEQ ) {
					$compare = '!=';
				} elseif ( $compare === Comparison::NIN ) {
					$compare = 'NOT IN';
				} elseif ( $compare === Comparison::CONTAINS ) {
					$compare = 'LIKE';
				}

				$meta = array(
					'key'     => $comparison->getField(),
					'value'   => $comparison->getValue()->getValue(),
					'compare' => $compare,
				);

				if ( in_array( $compare, $numeric ) ) {
					$meta['type'] = 'NUMERIC';
				}

				$this->last_meta = $meta;

				return $meta;
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function walkValue( Value $value ) { return $value->getValue(); }

	/**
	 * @inheritDoc
	 */
	public function walkCompositeExpression( CompositeExpression $expr ) {

		$list   = $expr->getExpressionList();
		$nested = null;

		/** @var CompositeExpression[] $composites */
		$composites = array();

		/** @var Comparison[] $comparisons */
		$comparisons = array();

		foreach ( $list as $expression ) {
			if ( $expression instanceof CompositeExpression ) {
				$composites[] = $expression;
			} elseif ( $expression instanceof Comparison ) {
				$comparisons[] = $expression;
			}
		}

		$metas = array();

		foreach ( $comparisons as $comparison ) {

			if ( in_array( $comparison->getField(), $this->core_fields ) && $expr->getType() !== 'AND' ) {
				throw new InvalidArgumentException( "Core fields '{$comparison->getField()}' can only be ANDed together." );
			}

			$maybe_meta = $this->dispatch( $comparison );

			if ( $maybe_meta ) {
				$metas[] = $maybe_meta;
			}
		}

		if ( $metas ) {
			$with_relation = array( 'relation' => $expr->getType() ) + $metas;

			$this->meta_depth ++;

			if ( $this->meta_depth > 0 ) {
				$this->meta_query[ $this->meta_depth ][] = $with_relation;
			} elseif ( $this->meta_depth === 0 ) {
				$this->meta_query[] = $with_relation;
			} else {
				$this->meta_query = $with_relation;
			}
		}

		foreach ( $composites as $composite ) {
			$this->dispatch( $composite );
		}
	}
}