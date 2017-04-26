<?php
/**
 * User Object Type.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_User_Object_Type
 */
abstract class ITE_User_Object_Type implements ITE_Object_Type, ITE_Object_Type_With_Meta {

	/**
	 * @inheritDoc
	 */
	public function get_object_by_id( $id ) {
		$object = get_user_by( 'id', $id );

		if ( ! $object ) {
			return null;
		}

		return $this->convert_user( $object );
	}

	/**
	 * @inheritDoc
	 */
	public function get_objects( \Doctrine\Common\Collections\Criteria $criteria = null, &$total = null ) {

		$args = array();

		if ( $criteria ) {
			$visitor = new ITE_WP_User_Query_Visitor();

			if ( $criteria->getWhereExpression() ) {
				$visitor->dispatch( $criteria->getWhereExpression() );
				$args = $visitor->get_args();
			}

			if ( $criteria->getOrderings() ) {
				$args['orderby'] = $criteria->getOrderings();
			}

			if ( $criteria->getMaxResults() ) {
				$args['number'] = $criteria->getMaxResults();
			}

			if ( $criteria->getFirstResult() ) {
				$args['offset'] = $criteria->getFirstResult();
			}
		}

		if ( func_num_args() === 1 ) {
			$args['count_total'] = false;
		}

		$query   = new WP_User_Query( $args );
		$users   = $query->get_results();
		$objects = array();

		foreach ( $users as $user ) {
			$objects[] = $this->convert_user( $user );
		}

		if ( func_num_args() === 2 ) {
			$total = $query->get_total();
		}

		return $objects;
	}

	/**
	 * @inheritDoc
	 */
	public function delete_object_by_id( $id ) {
		return (bool) wp_delete_user( $id, true );
	}

	/**
	 * @inheritDoc
	 */
	public function supports_meta() { return true; }

	/**
	 * @inheritDoc
	 */
	public function is_restful() { return $this instanceof ITE_RESTful_Object_Type; }

	/**
	 * @inheritDoc
	 */
	public function has_capabilities() { return $this instanceof ITE_Object_Type_With_Capabilities; }

	/**
	 * @inheritDoc
	 */
	public function add_meta( $object_id, $key, $value, $unique = false ) {
		return (bool) add_user_meta( $object_id, $key, $value, $unique );
	}

	/**
	 * @inheritDoc
	 */
	public function update_meta( $object_id, $key, $value, $prev_value = '' ) {
		return (bool) update_user_meta( $object_id, $key, $value, $prev_value );
	}

	/**
	 * @inheritDoc
	 */
	public function get_meta( $object_id, $key = '', $single = true ) {
		return get_user_meta( $object_id, $key, $single );
	}

	/**
	 * @inheritDoc
	 */
	public function delete_meta( $object_id, $key, $value = '', $delete_all = false ) {
		return delete_metadata( 'user', $object_id, $key, $value, $delete_all );
	}

	/**
	 * Convert a user object into the correct model.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_User $user
	 *
	 * @return object
	 */
	protected function convert_user( WP_User $user ) { return $user; }
}
