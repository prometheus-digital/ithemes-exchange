<?php
/**
 * CallableField class definition.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Fields;

use iThemes\Exchange\REST\Auth\AuthScope;

/**
 * Class CallableField
 *
 * @package iThemes\Exchange\REST\Fields
 */
class CallableField implements Field {

	/** @var string */
	private $attribute;

	/** @var array|callable */
	private $schema;

	/** @var string[] */
	private $contexts = array();

	/** @var callable */
	private $serialize;

	/** @var callable */
	private $update;

	/** @var callable */
	private $can_set;

	/**
	 * BaseField constructor.
	 *
	 * @param string         $attribute
	 * @param array|callable $schema
	 * @param \string[]      $contexts
	 * @param callable       $serialize
	 * @param callable|null  $update
	 * @param callable|null  $can_set
	 */
	public function __construct( $attribute, $schema, array $contexts = array(), $serialize, $update = null, $can_set = null ) {
		$this->attribute = $attribute;
		$this->schema    = $schema;
		$this->contexts  = $contexts;
		$this->serialize = $serialize;
		$this->update    = $update;
		$this->can_set   = $can_set;
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
	public function available_in_contexts() { return $this->contexts; }

	/**
	 * @inheritDoc
	 */
	public function serialize( $object, array $query_args = array() ) {
		return call_user_func( $this->serialize, $object, $query_args );
	}

	/**
	 * @inheritDoc
	 */
	public function update( $object, $new_value ) {
		if ( $this->update ) {
			return call_user_func( $this->update, $object, $new_value );
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function scope_can_set( AuthScope $scope, $new_value ) {
		if ( $this->can_set ) {
			return call_user_func( $this->can_set, $scope, $new_value );
		}

		return true;
	}
}