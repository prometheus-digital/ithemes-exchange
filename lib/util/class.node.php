<?php
/**
 * K-Ary Tree Node.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Tree_Node
 */
class ITE_Tree_Node {

	/** @var mixed[] */
	private $children = array();

	/** @var mixed */
	private $data;

	/**
	 * ITE_Tree_Node constructor.
	 *
	 * @param mixed $data
	 */
	public function __construct( $data ) { $this->data = $data; }

	/**
	 * Get the data for the node.
	 *
	 * @since 1.36.0
	 *
	 * @return mixed
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Add a child to the node.
	 *
	 * @since 1.36.0
	 *
	 * @param mixed  $child The child element. If no `$key` provided, the child must have a `__toString()` method.
	 * @param string $key   The key to identify the child by. Leave empty to use the `__toString()` method.
	 *
	 * @return \ITE_Tree_Node
	 *
	 * @throws InvalidArgumentException
	 */
	public function add_child( $child, $key = '' ) {

		if ( ! $key ) {
			$key = $this->get_key_for_element( $child );
		}

		$this->children[ $key ] = $node = new ITE_Tree_Node( $child );

		return $node;
	}

	/**
	 * Remove a child from the node.
	 *
	 * @since 1.36.0
	 *
	 * @param mixed|string $child_or_key Object implementing `__toString()` or the identifier used when registering it.
	 *
	 * @return bool
	 */
	public function remove_child( $child_or_key ) {

		$key = $this->get_key_for_element( $child_or_key );

		if ( array_key_exists( $key, $this->children ) ) {
			unset( $this->children[ $key ] );

			return true;
		}

		return false;
	}

	private function get_key_for_element( $element ) {
		if ( is_object( $element ) && is_callable( array( $element, '__toString' ) ) ) {
			return (string) $element;
		} elseif ( is_scalar( $element ) ) {
			return $element;
		} elseif ( is_object( $element ) ) {
			return spl_object_hash( $element );
		} else {
			throw new InvalidArgumentException( 'Invalid element and key combination.' );
		}
	}

	/**
	 * Get all child nodes.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Tree_Node[]
	 */
	public function get_children() {
		return array_values( $this->children );
	}

	/**
	 * Does the node have any children.
	 *
	 * @since 1.36.0
	 *
	 * @return bool
	 */
	public function has_children() {
		return (bool) $this->children;
	}
}