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
	function it_exchange_categories_addon_create() {

		$labels = array(
			'name'              => __( 'Product Categories', 'LION' ),
			'singular_name'     => __( 'Product Category', 'LION' ),
			'search_items'      => __( 'Search Product Categories', 'LION' ),
			'all_items'         => __( 'All Product Categories', 'LION' ),
			'parent_item'       => __( 'Parent Product Categories', 'LION' ),
			'parent_item_colon' => __( 'Parent Product Categories:', 'LION' ),
			'edit_item'         => __( 'Edit Product Categories', 'LION' ),
			'update_item'       => __( 'Update Product Categories', 'LION' ),
			'add_new_item'      => __( 'Add New Product Categories', 'LION' ),
			'new_item_name'     => '', //leave blank
		);

		// A little hackery for admin --> appearances --> menues page
		if ( is_admin() && ! empty( $GLOBALS['pagenow'] ) && 'nav-menus.php' == $GLOBALS['pagenow'] )
			$labels['name'] = __( 'Exchange Categories', 'LION' );

		register_taxonomy(
			'it_exchange_category',
			array( 'it_exchange_prod' ),
			array(
				'hierarchical' => true,
				'labels'       => $labels,
				'show_ui'      => true,
				'query_var'    => true,
				'rewrite'      => array( 'slug' => 'product-category' ),
			)
		);

	}
	add_action( 'init', 'it_exchange_categories_addon_create', 0 );

}

if ( !function_exists( 'it_exchange_category_addon_widget_init' ) ) {

	/**
	 * Register all of the default WordPress widgets on startup.
	 *
	 * Calls 'widgets_init' action after all of the WordPress widgets have been
	 * registered.
	 *
	 * @since 2.2.0
	 */
	function it_exchange_category_addon_widget_init() {

		include( 'class.category-widget.php' );
		register_widget('IT_Exchange_Category_Widget');

	}
	add_action( 'widgets_init', 'it_exchange_category_addon_widget_init', 1 );

}

if ( !function_exists( 'it_exchange_categories_addon_add_menu_item' ) ) {

	/**
	 * This adds a menu item to the Exchange menu pointing to the WP All [post_type] table
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function it_exchange_categories_addon_add_menu_item() {
		$url = "edit-tags.php?taxonomy=it_exchange_category&amp;post_type=it_exchange_prod";
		add_submenu_page( 'it-exchange', __( 'Product Categories', 'LION' ), __( 'Product Categories', 'LION' ), 'update_plugins', $url );
	}
	add_action( 'admin_menu', 'it_exchange_categories_addon_add_menu_item' );

}

if ( !function_exists( 'it_exchange_categories_addon_fix_menu_parent_file' ) ) {

	/**
	 * This fixed the $parent_file variable so that the Exchange top-level menu expands when on the Product Tags page
	 *
	 * @since 0.4.11
	 *
	 * @return void
	*/
	function it_exchange_categories_addon_fix_menu_parent_file() {
		if ( 'it_exchange_category' == $_GET['taxonomy'] )
			$GLOBALS['parent_file'] = 'it-exchange';
	}
	add_action( 'admin_head-edit-tags.php', 'it_exchange_categories_addon_fix_menu_parent_file' );

}

if ( !function_exists( 'it_exchange_categories_pre_get_posts' ) ) {

	/**
	 * Removes hidden products from product category queries
	 *
	 * @since 1.7.10
	 *
	 * @return void
	*/
	function it_exchange_categories_pre_get_posts( $query ) {
	    if ( ! is_admin() && is_tax( 'it_exchange_category' ) && ! empty( $query->it_exchange_category ) ) {
	    	$meta_query = (array) $query->meta_query;
	    	$meta_query[] = array(
	    		'key'   => '_it-exchange-visibility',
	    		'value' => 'visible',
	    	);
	    	$query->set( 'meta_query', $meta_query );
	    }
	}
	add_action( 'pre_get_posts', 'it_exchange_categories_pre_get_posts' );
	
}

