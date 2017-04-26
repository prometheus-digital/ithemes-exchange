<?php
/**
 * REST CallableQueryArg class definition.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Fields;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use iThemes\Exchange\REST\Auth\AuthScope;

/**
 * Class CallbackQueryArg
 *
 * @package iThemes\Exchange\REST\Fields
 */
class CallableQueryArg implements QueryArg {

	/** @var string */
	private $attribute;

	/** @var array|callable */
	private $schema;

	/** @var callable|null */
	private $can_use;

	/** @var callable|string|null */
	private $add_criteria;

	/** @var callable|null */
	private $is_valid;

	/**
	 * BaseQueryArg constructor.
	 *
	 * @param string               $attribute
	 * @param array|callable       $schema
	 * @param callable|null        $can_use
	 * @param callable|string|null $add_criteria
	 * @param callable|null        $is_valid
	 */
	public function __construct( $attribute, $schema, $can_use = null, $add_criteria = null, $is_valid = null ) {
		$this->attribute    = $attribute;
		$this->schema       = $schema;
		$this->can_use      = $can_use;
		$this->add_criteria = $add_criteria;
		$this->is_valid     = $is_valid;
	}

	/**
	 * @inheritDoc
	 */
	public function get_attribute() { return $this->attribute; }

	/**
	 * @inheritDoc
	 */
	public function get_schema() {

		if ( is_callable( $this->schema ) ) {
			$this->schema = call_user_func( $this->schema );
		}

		return $this->schema;
	}

	/**
	 * @inheritDoc
	 */
	public function scope_can_use( AuthScope $scope, $value = '' ) {
		if ( $this->can_use ) {
			return call_user_func( $this->can_use, $scope, $value );
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function is_valid( $value ) {
		if ( $this->is_valid ) {
			return call_user_func( $this->is_valid, $value );
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function add_criteria( Criteria $criteria, $value, array $all_query_args ) {
		if ( is_callable( $this->add_criteria ) ) {
			call_user_func( $this->add_criteria, $criteria, $value, $all_query_args );
		} else {

			if ( is_string( $this->add_criteria ) ) {
				$field = $this->add_criteria;
			} else {
				$field = $this->get_attribute();
			}

			$criteria->andWhere( new Comparison( $field, Comparison::EQ, $value ) );
		}
	}
}
