<?php
/**
 * Contains the activity factory.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Txn_Activity_Factory
 */
class IT_Exchange_Txn_Activity_Factory {

	/**
	 * @var array
	 */
	private $types = array();

	/**
	 * @var string
	 */
	private $post_type;

	/**
	 * @var string
	 */
	private $type_taxonomy;

	/**
	 * IT_Exchange_Txn_Activity_Factory constructor.
	 *
	 * @param $post_type
	 * @param $type_taxonomy
	 */
	public function __construct( $post_type, $type_taxonomy ) {

		if ( ! post_type_exists( $post_type ) ) {
			throw new InvalidArgumentException( "Post type '{$post_type} does not exist." );
		}

		if ( ! taxonomy_exists( $type_taxonomy ) ) {
			throw new InvalidArgumentException( "Taxonomy '{$type_taxonomy}' does not exist." );
		}

		$this->post_type     = $post_type;
		$this->type_taxonomy = $type_taxonomy;
	}

	/**
	 * Get the post type activity is stored as.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_post_type() {
		return $this->post_type;
	}

	/**
	 * Get the taxonomy for controlling the activity type.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_type_taxonomy() {
		return $this->type_taxonomy;
	}

	/**
	 * Register an activity type.
	 *
	 * @since 1.34
	 *
	 * @param string   $type
	 * @param callable $function Function called to make the object.
	 *
	 * @return $this
	 */
	public function register( $type, $function ) {

		if ( ! is_callable( $function, false ) ) {
			throw new InvalidArgumentException( "Function for '{$type}' type is not callable." );
		}

		$this->types[ $type ] = $function;

		return $this;
	}

	/**
	 * Make an activity item.
	 *
	 * @since 1.34
	 *
	 * @param int $id
	 *
	 * @return IT_Exchange_Txn_Activity|null
	 */
	public function make( $id ) {

		$terms = wp_get_object_terms( $id, $this->type_taxonomy, array(
			'fields' => 'names'
		) );

		if ( is_wp_error( $terms ) ) {
			throw new UnexpectedValueException( 'WP Error: ' . $terms->get_error_message() );
		}

		if ( empty( $terms ) ) {
			return null;
		}

		$type = reset( $terms );

		if ( ! isset( $this->types[ $type ] ) ) {
			return null;
		}

		$function = $this->types[ $type ];

		$activity = $function( $id );

		if ( ! $activity instanceof IT_Exchange_Txn_Activity ) {
			throw new UnexpectedValueException( "Activity with ID '{$id}', is not valid." );
		}

		return $activity;
	}
}