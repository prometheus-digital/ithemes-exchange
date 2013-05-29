<?php
/**
 * Registers IT Exchange Product Categories
 *
 * @package iThemes Exchange
 * @since 0.4.0
 */
 
if ( !function_exists( 'create_it_exchange_categories' ) ) {
		
	/**
	 * Registers iThemes Exchange Product Category Taxonomy
	 *
	 * @since 1.0.0
	 * @uses register_taxonomy()
	 */
	function create_it_exchange_categories() {
		
	  $labels = array(
			'name' 				=> __( 'Product Categories', 'LION' ),
			'singular_name' 	=> __( 'Product Category', 'LION' ),
			'search_items'		=> __( 'Search Product Categories', 'LION' ),
			'all_items' 		=> __( 'All Product Categories', 'LION' ), 
			'parent_item' 		=> __( 'Parent Product Categories', 'LION' ),
			'parent_item_colon' => __( 'Parent Product Categories:', 'LION' ),
			'edit_item' 		=> __( 'Edit Product Categories', 'LION' ), 
			'update_item' 		=> __( 'Update Product Categories', 'LION' ),
			'add_new_item' 		=> __( 'Add New Product Categories', 'LION' ),
			'new_item_name' 	=> __( 'New Product Category', 'LION' ),			
		); 	
	
		register_taxonomy(
			'it_exchange_category', 
			array(), //do not add to any post type (yet)
			array(
				'hierarchical' 	=> true,
				'labels' 		=> $labels,
				'show_ui' 		=> true,
				'query_var' 	=> true,
				'rewrite' 		=> array( 'slug' => 'product-category' ),
			)
		);
		
	}
	add_action( 'init', 'create_it_exchange_categories', 0 );

}

if ( !function_exists( 'it_exchange_categories_add_menu_item' ) ) {
			
	/**
	 * This adds a menu item to the Exchange menu pointing to the WP All [post_type] table
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function it_exchange_categories_add_menu_item() {
		$url = add_query_arg( array( 'taxonomy' => 'it_exchange_category' ), 'edit-tags.php' );
		add_submenu_page( 'it-exchange', __( 'Product Categories', 'LION' ), __( 'Product Categories', 'LION' ), 'update_plugins', $url );
	}
	add_action( 'admin_menu', 'it_exchange_categories_add_menu_item' );

}