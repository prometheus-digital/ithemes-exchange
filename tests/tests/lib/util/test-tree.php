<?php
/**
 * Test the tree.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class Test_ITE_Tree
 */
class Test_ITE_Tree extends IT_Exchange_UnitTestCase {

	/*
				G
			  /    \
			 B      H
	        / \       \
		   A   F       L
	          /|\    / | \
	         C D E  I  J  K

	 */
	private function get_tree() {

		$tree = new ITE_Tree( 'g' );

		$b = $tree->add_child( 'b' );
		$b->add_child( 'a' );
		$f = $b->add_child( 'f' );

		$f->add_child( 'c' );
		$f->add_child( 'd' );
		$f->add_child( 'e' );

		$h = $tree->add_child( 'h' );

		$l = $h->add_child( 'l' );
		$l->add_child( 'i' );
		$l->add_child( 'j' );
		$l->add_child( 'k' );

		return $tree;
	}

	public function test_dft_preorder() {

		$tree = $this->get_tree();

		$out = '';

		$fn = function ( $data ) use ( &$out ) {
			$out .= $data;
		};

		$tree->dft( $fn, ITE_Tree::PRE_ORDER );

		$this->assertEquals( 'gbafcdehlijk', $out );
	}

	public function test_dft_postorder() {

		$tree = $this->get_tree();

		$out = '';

		$fn = function ( $data ) use ( &$out ) {
			$out .= $data;
		};

		$tree->dft( $fn, ITE_Tree::POST_ORDER );

		$this->assertEquals( 'acdefbijklhg', $out );
	}

	public function test_height() {
		$this->assertEquals( 3, $this->get_tree()->height() );
	}

	public function test_leaves() {
		return $this->assertEqualSets( array( 'a', 'c', 'd', 'e', 'i', 'j', 'k' ), $this->get_tree()->leaves() );
	}

	public function test_size() {
		return $this->assertEquals( 12, $this->get_tree()->size() );
	}

	public function test_dfs() {

		$node = $this->get_tree()->dfs( 'g' );
		$this->assertInstanceOf( 'ITE_Tree_Node', $node );
		$this->assertEquals( 'g', $node->get_data() );

		$node = $this->get_tree()->dfs( 'f' );
		$this->assertInstanceOf( 'ITE_Tree_Node', $node );
		$this->assertEquals( 'f', $node->get_data() );

		$node = $this->get_tree()->dfs( 'i' );
		$this->assertInstanceOf( 'ITE_Tree_Node', $node );
		$this->assertEquals( 'i', $node->get_data() );

		$node = $this->get_tree()->dfs( 'e' );
		$this->assertInstanceOf( 'ITE_Tree_Node', $node );
		$this->assertEquals( 'e', $node->get_data() );
	}

	public function test_dfs_non_existent() {
		$this->assertNull( $this->get_tree()->dfs( 'z' ) );
	}
}