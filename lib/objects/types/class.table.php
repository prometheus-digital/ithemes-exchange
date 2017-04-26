<?php
/**
 * Table Object Type.
 *
 * @since   2.0.0
 * @license GPLv2
 */
use Doctrine\Common\Collections\Criteria;
use IronBound\DB\Query\FluentQuery;

/**
 * Class ITE_Table_Object_Type
 *
 * @since 2.0.0
 */
abstract class ITE_Table_Object_Type implements ITE_Object_Type {

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return $this->get_model()->table()->get_slug();
	}

	/**
	 * @inheritDoc
	 */
	public function get_object_by_id( $id ) {
		return $this->get_model()->get( $id );
	}

	/**
	 * @inheritDoc
	 */
	public function get_objects( Criteria $criteria = null, &$total = null ) {

		$query = $this->get_model()->query();

		if ( ! $criteria ) {
			return $query->results()->toArray();
		}

		if ( $criteria->getWhereExpression() ) {
			$visitor = new ITE_DB_Visitor( $query );
			$visitor->dispatch( $criteria->getWhereExpression() );
		}

		foreach ( $criteria->getOrderings() as $field => $order ) {
			$query->order_by( $field, $order );
		}

		if ( $criteria->getMaxResults() ) {
			$query->take( $criteria->getMaxResults() );
		}

		if ( $criteria->getFirstResult() ) {
			$query->offset( $criteria->getFirstResult() );
		}

		if ( func_num_args() === 2 ) {
			$query->calc_found_rows();
		}

		/**
		 * Fires when the query is about to be executed.
		 *
		 * @since 2.0.0
		 *
		 * @param FluentQuery $query
		 * @param Criteria    $criteria
		 */
		do_action( "it_exchange_get_{$this->get_slug()}_objects", $query, $criteria );

		$results = $query->results();

		if ( func_num_args() === 2 ) {
			$total = $query->total();
		}

		return $results->toArray();
	}

	/**
	 * @inheritDoc
	 */
	public function create_object( array $attributes ) {
		return $this->get_model()->create( $attributes );
	}

	/**
	 * @inheritDoc
	 */
	public function delete_object_by_id( $id ) {

		$object = $this->get_object_by_id( $id );

		if ( ! $object ) {
			return true;
		}

		try {
			$object->delete();
		} catch ( Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function supports_meta() { return $this->get_model() instanceof \IronBound\DB\Extensions\Meta\WithMeta; }

	/**
	 * @inheritDoc
	 */
	public function is_restful() { return $this instanceof ITE_RESTful_Object_Type; }

	/**
	 * @inheritDoc
	 */
	public function has_capabilities() { return $this instanceof ITE_Object_Type_With_Capabilities; }

	/**
	 * Get the model.
	 *
	 * @since 2.0.0
	 *
	 * @return \IronBound\DB\Model|\IronBound\DB\Extensions\Meta\WithMeta
	 */
	protected abstract function get_model();
}
