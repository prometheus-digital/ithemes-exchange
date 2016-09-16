<?php
/**
 * K-Ary Tree.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Tree
 */
class ITE_Tree {

	const PRE_ORDER = 'pre';
	const POST_ORDER = 'post';

	/** @var ITE_Tree_Node */
	private $root;

	/**
	 * ITE_Tree constructor.
	 *
	 * @param mixed $data
	 */
	public function __construct( $data = null ) {
		if ( $data ) {
			$this->root = new \ITE_Tree_Node( $data );
		}
	}

	/**
	 * Get the root of the tree.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Tree_Node
	 */
	public function get_root() {
		return $this->root;
	}

	/**
	 * Set the root node for the tree.
	 *
	 * @since 1.36.0
	 *
	 * @param mixed $data
	 */
	public function set_root( $data ) {
		$this->root = new ITE_Tree_Node( $data );
	}

	/**
	 * Add a child to the tree.
	 *
	 * @since 1.36.0
	 *
	 * @param mixed  $child The child element. If no `$key` provided, the child must have a `__toString()` method.
	 * @param string $key   The key to identify the child by. Leave empty to use the `__toString()` method.
	 *
	 * @return \ITE_Tree_Node
	 */
	public function add_child( $child, $key = '' ) {
		return $this->root->add_child( $child, $key );
	}

	/**
	 * Search a tree for a given piece of data.
	 *
	 * @since 1.36.0
	 *
	 * @param mixed $data
	 *
	 * @return \ITE_Tree_Node|null
	 */
	public function dfs( $data ) {
		return $this->_dfs( $this->get_root(), $data );
	}

	private function _dfs( ITE_Tree_Node $node, $data ) {
		if ( $node->get_data() === $data ) {
			return $node;
		}

		foreach ( $node->get_children() as $child ) {
			$found = $this->_dfs( $child, $data );

			if ( $found ) {
				return $found;
			}
		}

		return null;
	}

	/**
	 * Perform a depth-first traversal.
	 *
	 * @since 1.36.0
	 *
	 * @param callable $callback
	 * @param string   $order
	 */
	public function dft( $callback, $order = self::PRE_ORDER ) {
		$this->{'_dft_' . $order}( $this->get_root(), $callback );
	}

	private function _dft_pre( \ITE_Tree_Node $node, $callback ) {
		$callback( $node->get_data() );

		foreach ( $node->get_children() as $child ) {
			$this->_dft_pre( $child, $callback );
		}
	}

	private function _dft_post( \ITE_Tree_Node $node, $callback ) {
		foreach ( $node->get_children() as $child ) {
			$this->_dft_post( $child, $callback );
		}

		$callback( $node->get_data() );
	}

	/**
	 * Get the height of the tree.
	 *
	 * @since 1.36.0
	 *
	 * @return int
	 */
	public function height() {
		if ( ! $this->get_root() ) {
			return - 1;
		}

		return $this->_height( $this->get_root() );
	}

	private function _height( ITE_Tree_Node $node ) {

		if ( ! $node->has_children() ) {
			return 0;
		}

		$max_height = 0;

		foreach ( $node->get_children() as $child ) {
			$max_height = max( $max_height, $this->_height( $child ) );
		}

		return $max_height + 1;
	}

	/**
	 * Get the leaves of the tree.
	 *
	 * @since 1.36.0
	 *
	 * @return array
	 */
	public function leaves() {
		return $this->_leaves( $this->get_root() );
	}

	private function _leaves( ITE_Tree_Node $node, array $children = array() ) {
		if ( $node->has_children() ) {
			foreach ( $node->get_children() as $child ) {
				$children = $this->_leaves( $child, $children );
			}

			return $children;
		} else {
			$children[] = $node->get_data();

			return $children;
		}
	}

	/**
	 * Get the size of the tree.
	 *
	 * @since 1.36.0
	 *
	 * @return int
	 */
	public function size() {
		return $this->_size( $this->get_root() );
	}

	private function _size( ITE_Tree_Node $node ) {

		if ( ! $node->has_children() ) {
			return 1;
		}

		$size = 0;

		foreach ( $node->get_children() as $child ) {
			$size += $this->_size( $child );
		}

		return $size + 1;
	}
}