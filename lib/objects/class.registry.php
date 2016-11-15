<?php
/**
 * Object Type Registry.
 *
 * @since   2.0.0
 * @license GPlv2
 */

/**
 * Class ITE_Object_Type_Registry
 */
class ITE_Object_Type_Registry {

	/** @var ITE_Object_Type[] */
	private $types = array();

	/**
	 * Register an object type.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Object_Type $type
	 *
	 * @return $this
	 */
	public function register( ITE_Object_Type $type ) {

		$this->types[ $type->get_slug() ] = $type;

		return $this;
	}

	/**
	 * Get an object type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug
	 *
	 * @return ITE_Object_Type|null
	 */
	public function get( $slug ) {
		return isset( $this->types[ $slug ] ) ? $this->types[ $slug ] : null;
	}

	/**
	 * Get an object type or fail.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug
	 *
	 * @return ITE_Object_Type|void
	 *
	 * @throws ITE_Object_Type_Not_Found_Exception
	 */
	public function get_or_fail( $slug ) {
		return isset( $this->types[ $slug ] ) ? $this->types[ $slug ] : $this->not_found( $slug );
	}

	/**
	 * Get all object types.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Object_Type[]
	 */
	public function all() {	return array_values( $this->types ); }

	/**
	 * Get all RESTful object types.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_RESTful_Object_Type[]
	 */
	public function restful() {
		return array_filter( $this->all(), function ( ITE_Object_Type $type ) { return $type->is_restful(); } );
	}

	/**
	 * Get all object types with meta support.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Object_Type_With_Meta[]
	 */
	public function with_meta() {
		return array_filter( $this->all(), function ( ITE_Object_Type $type ) { return $type->supports_meta(); } );
	}

	/**
	 * Throw an object type not found exception.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug
	 *
	 *
	 * @throws ITE_Object_Type_Not_Found_Exception
	 */
	protected final function not_found( $slug ) {
		throw new ITE_Object_Type_Not_Found_Exception( "Object type {$slug} not found." );
	}
}
