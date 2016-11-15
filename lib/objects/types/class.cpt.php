<?php
/**
 * CPT Object Type.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_CPT_Object_Type
 */
abstract class ITE_CPT_Object_Type implements ITE_Object_Type, ITE_Object_Type_With_Meta {

	/**
	 * @inheritDoc
	 */
	public function get_slug() { return $this->get_post_type(); }

	/**
	 * @inheritDoc
	 */
	public function get_label() {
		$labels = get_post_type_labels( get_post_type_object( $this->get_post_type() ) );

		return $labels ? $labels->singular_name : $this->get_post_type();
	}

	/**
	 * @inheritDoc
	 */
	public function get_object_by_id( $id ) {
		$post = get_post( $id );

		if ( ! $post || $post->post_type !== $this->get_post_type() ) {
			return null;
		}

		return $this->convert_post( $post );
	}

	/**
	 * @inheritDoc
	 */
	public function get_objects( \Doctrine\Common\Collections\Criteria $criteria = null ) {

		$args = array();

		if ( $criteria ) {
			$visitor = new ITE_WP_Query_Visitor();

			if ( $criteria->getWhereExpression() ) {
				$visitor->dispatch( $criteria->getWhereExpression() );
				$args = $visitor->get_args();
			}

			if ( $criteria->getOrderings() ) {
				$args['orderby'] = $criteria->getOrderings();
			}

			if ( $criteria->getMaxResults() ) {
				$args['posts_per_page'] = $criteria->getMaxResults();
			}

			if ( $criteria->getFirstResult() ) {
				$args['offset'] = $criteria->getFirstResult();
			}
		}

		$args['post_type']        = $this->get_post_type();
		$args['suppress_filters'] = true;
		$args['no_found_rows']    = true;

		$query   = new WP_Query( $args );
		$posts   = $query->get_posts();
		$objects = array();

		foreach ( $posts as $post ) {
			$objects[] = $this->convert_post( $post );
		}

		return $objects;
	}

	/**
	 * @inheritDoc
	 */
	public function delete_object_by_id( $id ) {
		$r = wp_delete_post( $id, true );

		if ( is_wp_error( $r ) || $r === false ) {
			return false;
		}

		return true;
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
	public function add_meta( $object_id, $key, $value, $unique = false ) {
		return (bool) add_post_meta( $object_id, $key, $value, $unique );
	}

	/**
	 * @inheritDoc
	 */
	public function update_meta( $object_id, $key, $value, $prev_value = '' ) {
		return (bool) update_post_meta( $object_id, $key, $value, $prev_value );
	}

	/**
	 * @inheritDoc
	 */
	public function get_meta( $object_id, $key = '', $single = true ) {
		return get_post_meta( $object_id, $key, $single );
	}

	/**
	 * @inheritDoc
	 */
	public function delete_meta( $object_id, $key, $value = '', $delete_all = false ) {
		return delete_metadata( 'post', $object_id, $key, $value, $delete_all );
	}

	/**
	 * Convert a WP_Post object into the correct model.
	 *
	 * @since 1.36.0
	 *
	 * @param WP_Post $post
	 *
	 * @return object
	 */
	protected function convert_post( WP_Post $post ) { return $post; }

	/**
	 * Get the post type.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	protected abstract function get_post_type();
}