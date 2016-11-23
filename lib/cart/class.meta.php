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
	 */
	public function __construct( $key, array $args ) {
		$this->key  = $key;
		$this->args = wp_parse_args( $args, array(
			'show_in_rest'     => false,
			'editable_in_rest' => false,
			'type'             => 'string',
		) );
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
	 * Get the meta type.
	 *
	 * ie, 'string' or 'integer'.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->args['type'];
	}
}
