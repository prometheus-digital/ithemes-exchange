<?php
/**
 * Cart Meta.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Cart_Meta
 */
class ITE_Cart_Meta {

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var array
	 */
	private $args = array();

	/**
	 * ITE_Cart_Meta constructor.
	 *
	 * @param string $key
	 * @param array  $args
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $key, array $args ) {
		$this->key  = $key;
		$this->args = wp_parse_args( $args, array(
			'show_in_rest'     => false,
			'editable_in_rest' => false,
			'schema'           => array(),
		) );

		if ( empty( $this->args['schema'] ) && ( $this->show_in_rest() || $this->editable_in_rest() ) ) {
			throw new InvalidArgumentException( "'schema' argument is required." );
		}

		if ( ! $this->editable_in_rest() ) {
			$this->args['schema']['readonly'] = true;
		}
	}

	/**
	 * Get a meta key.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_key() {
		return $this->key;
	}

	/**
	 * Does this meta key show in rest.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function show_in_rest() {
		return (bool) $this->args['show_in_rest'];
	}

	/**
	 * Is this meta key editable in rest.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function editable_in_rest() {
		return (bool) $this->args['editable_in_rest'];
	}

	/**
	 * Get the schema for the meta field.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_schema() {
		return $this->args['schema'];
	}

	/**
	 * Can the current rest scope edit this meta.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Auth\AuthScope $scope
	 *
	 * @return bool
	 */
	public function has_rest_edit_permission( \iThemes\Exchange\REST\Auth\AuthScope $scope ) {

		if ( is_callable( $this->args['editable_in_rest'] ) ) {
			return call_user_func( $this->args['editable_in_rest'], $scope );
		}

		return true;
	}
}
