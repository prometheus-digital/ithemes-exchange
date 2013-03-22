<?php
/**
 * Creates the post type for Products
 *
 * @package IT_Cart_Buddy
 * @since 0.3.0
*/

/**
 * Registers the it_cart_buddy_prod post type
 *
 * @since 0.3.0
*/
class IT_Cart_Buddy_Product_Post_Type {
	
	/**
	 * Class Constructor
	 *
	 * @todo Filter some of these options. Not all.
	 * @since 0.3.0
	 * @return void
	*/
	function IT_Cart_Buddy_Product_Post_Type() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'register_disabled_post_status' ) );
		add_action( 'save_post', array( $this, 'save_product' ) );
		add_action( 'admin_init', array( $this, 'set_add_new_item_label' ) );
		add_action( 'admin_init', array( $this, 'set_edit_item_label' ) );
		add_action( 'it_cart_buddy_save_product_unvalidated', array( $this, 'set_initial_post_product_type' ) );
		add_filter( 'manage_edit-it_cart_buddy_prod_columns', array( $this, 'add_product_type_column_to_view_all_table' ) );
		add_filter( 'manage_edit-it_cart_buddy_prod_sortable_columns', array( $this, 'make_product_type_column_sortable' ) );
		add_filter( 'manage_it_cart_buddy_prod_posts_custom_column', array( $this, 'add_product_type_info_to_view_all_table_rows' ) );
		add_action( 'it_cart_buddy_add_on_enabled', array( $this, 'maybe_enable_product_type_posts' ) );
		add_action( 'it_cart_buddy_add_on_disabled', array( $this, 'maybe_disable_product_type_posts' ) );
	}

	function init() {
		$this->post_type = 'it_cart_buddy_prod';
		$labels    = array(
			'name'          => __( 'Products', 'LION' ),
			'singular_name' => __( 'Product', 'LION' ),
		);
		$this->options = array(
			'labels' => $labels,
			'description' => __( 'A Cart Buddy Post Type for storing all Products in the system', 'LION' ),
			'public'      => true,
			'show_ui'     => true,
			'show_in_nav_menus' => true,
			'show_in_menu'      => false, // We will be adding it manually with various labels based on available product-type add-ons
			'show_in_admin_bar' => false,
			'hierarchical'      => false,
			'supports'          => array( // Support everything but page-attributes for add-on flexibility
				'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields',
				'comments', 'revisions', 'post-formats',
			),
			'register_meta_box_cb' => array( $this, 'meta_box_callback' ),
		);

		add_action( 'init', array( $this, 'register_the_post_type' ) );
	}

	/**
	 * Actually registers the post type
	 *
	 * @since 0.3.0
	 * @return void
	*/
	function register_the_post_type() {
		register_post_type( $this->post_type, $this->options );
	}

	/**
	 * Call Back hook for product post type admin views
	 *
	 * @since 0.3.0
	 * @uses it_cart_buddy_get_enabled_add_ons()
	 * @return void
	*/
	function meta_box_callback( $post ) {
		$product = it_cart_buddy_get_product( $post );

		// Add action for current product type
		if ( $product_types = it_cart_buddy_get_enabled_addons( array( 'category' => array( 'product-type' ) ) ) ) {
			foreach( $product_types as $addon_slug => $params ) {
				if ( $addon_slug == $product->product_type )
					do_action( 'it_cart_buddy_product_metabox_callback_' . $addon_slug, $product );
			}
		}

		// Do action for any product type
		do_action( 'it_cart_buddy_product_metabox_callback', $product );
	}

	/**
	 * Generates the Add New Product Label for a new Product 
	 *
	 * @since 0.3.0
	 * @return string $label Label for add new product page.
	*/
	function set_add_new_item_label() {
		global $pagenow, $wp_post_types;
		if ( $pagenow != 'post-new.php' || empty( $_GET['post_type'] ) || 'it_cart_buddy_prod' != $_GET['post_type'] )
			return apply_filters( 'it_cart_buddy_add_new_product_label', __( 'Add New Product', 'LION' ) );

		if ( empty( $wp_post_types['it_cart_buddy_prod'] ) )
			return;
			
		$product_add_ons = it_cart_buddy_get_enabled_addons( array( 'category' => array( 'product-type' ) ) );
		$product = array();

		// Isolate the product type
		if ( 1 == count( $product_add_ons ) ) {
			$product = reset( $product_add_ons );
		} else {
			$product_type = it_cart_buddy_get_product_type();
			if ( ! empty( $product_type ) && ! empty( $product_add_ons[$product_type] ) )
				$product = $product_add_ons[$product_type];
			else
				$product['options']['labels']['singular_name'] = 'Product';

		}
		$singular = empty( $product['options']['labels']['singular_name'] ) ? $product['name'] : $product['options']['labels']['singular_name'];
		$label = apply_filters( 'it_cart_buddy_add_new_product_label-' . $product['slug'], __( 'Add New ', 'LION' ) . $singular );
		$wp_post_types['it_cart_buddy_prod']->labels->add_new_item = $label;
	}

	/**
	 * Generates the Edit Product Label for a new Product 
	 *
	 * Post types have to be registered earlier than we know that type of post is being edited
	 * so this function inserts the correct label into the $wp_post_types global after post type is registered
	 *
	 * @since 0.3.1
	 * @return string $label Label for edit product page.
	*/
	function set_edit_item_label() {
		global $pagenow, $wp_post_types;
		$post = empty( $_GET['post'] ) ? false : get_post( $_GET['post'] );

		if ( ! is_admin() || $pagenow != 'post.php' || ! $post )
			return;

		if ( empty( $wp_post_types['it_cart_buddy_prod'] ) )
			return;
			
		if ( 'it_cart_buddy_prod' != get_post_type( $post ) )
			return;

		$product_type = it_cart_buddy_get_product_type( $post );

		$product_add_ons = it_cart_buddy_get_enabled_addons( array( 'category' => array( 'product-type' ) ) );
		$product = array();
		if ( 1 == count( $product_add_ons ) ) {
			$product = reset( $product_add_ons );
		} else {
			if ( ! empty( $product_type ) && ! empty( $product_add_ons[$product_type] ) ) {
				$product = $product_add_ons[$product_type];
			} else {
				$product['slug'] = '';
				$product['options']['labels']['singular_name'] = 'Product';
			}
		}

		$singular = empty( $product['options']['labels']['singular_name'] ) ? $product['name'] : $product['options']['labels']['singular_name'];
		$label = apply_filters( 'it_cart_buddy_edit_product_label-' . $product['slug'], __( 'Edit ', 'LION' ) . $singular );
		$wp_post_types['it_cart_buddy_prod']->labels->edit_item = $label;
	}

	/**
	 * Provides specific hooks for when cart buddy products are saved.
	 *
	 * This method is hooked to save_post. It provides hooks for add-on developers
	 * that will only be called when the post being saved is a cart buddy product. 
	 * It provides the following 4 hooks:
	 * - it_cart_buddy_save_product_unvalidated                // Runs every time a cart buddy product is saved.
	 * - it_cart_buddy_save_product_unavalidate-[product-type] // Runs every time a specific cart buddy product type is saved.
	 * - it_cart_buddy_save_product                            // Runs every time a cart buddy product is saved if not an autosave and if user has permission to save post
	 * - it_cart_buddy_save_product-[product-type]             // Runs every time a specific cart buddy product-type is saved if not an autosave and if user has permission to save post
	 *
	 * @since 0.3.1
	 * @return void
	*/
	function save_product( $post ) { 

		// Exit if not it_cart_buddy_prod post_type
		if ( ! 'it_cart_buddy_prod' == get_post_type( $post ) ) 
			return;

		// Grab enabled product add-ons
		$product_type_addons = it_cart_buddy_get_enabled_addons( array( 'category' => 'product-type' ) );
		
		// Grab current post's product_type
		$product_type = it_cart_buddy_get_product_type();

		// These hooks fire off any time a it_cart_buddy_prod post is saved w/o validations
		do_action( 'it_cart_buddy_save_product_unvalidated', $post );
		foreach( (array) $product_type_addons as $slug => $params ) { 
			if ( $slug == $product_type ) { 
				do_action( 'it_cart_buddy_save_product_unvalidated-' . $slug, $post );
			}   
		}   

		// Fire off actions with validations that most instances need to use.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( ! current_user_can( 'edit_post', $post ) ) 
			return;

		// This is called any time save_post hook
		do_action( 'it_cart_buddy_save_product', $post );
		foreach( (array) $product_type_addons as $slug => $params ) { 
			if ( $slug == $product_type ) { 
				do_action( 'it_cart_buddy_save_product-' . $slug, $post );
			}   
		}   
	}

	/**
	 * Sets the post product_type on post creation
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function set_initial_post_product_type( $post ) {
		global $pagenow;
		if ( $product = it_cart_buddy_get_product( $post ) ) {
			if ( ! empty( $product->product_type ) && ! get_post_meta( $product->ID, '_it_cart_buddy_product_type', true ) )
				update_post_meta( $product->ID, '_it_cart_buddy_product_type', $product->product_type );
		}
	}

	/**
	 * Register Hidden Disabled Product Post status
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function register_disabled_post_status() {
		$args = array(
			'label'                     => _x( '_it_cart_buddy_disab', 'Status General Name', 'LION' ),
			'label_count'               => _n_noop( 'Disabled Product (%s)',  'Disabled Products (%s)', 'LION' ),
			'public'                    => false,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => false,
			'exclude_from_search'       => true,
		);
		register_post_status( '_it_cart_buddy_disab', $args );
	}

	/**
	 * Fires when add-ons are enabled and determines if associated products need to be enabled
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function maybe_enable_product_type_posts( $addon ) {
		$addon_category = empty( $addon['options']['category'] ) ? false : $addon['options']['category'];
		if ( 'product-type' != $addon_category )
			return;

		$this->enable_product_type_posts( $addon['slug'] );
	}

	/**
	 * When a Product add-on is enabled, re-enable any diabled post products previously created by it.
	 *
	 * 1 - Find all product posts for this product type with a post_status of _it_cart_buddy_disab
	 * 2 - Foreach product, pass to enable_product_post() method
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function enable_product_type_posts( $product_type ) {

		// Grab all products for this product-type
		$args = array(
			'post_status'  => '_it_cart_buddy_disab',
			'product_type' => $product_type,
			'number_posts' => -1,
		);
		if ( $products = it_cart_buddy_get_products( $args ) ) {
			foreach( $products as $product ) {
				$this->enable_product_post( $product );
			}
		}
	}

	/**
	 * Enable a single product type by changing post_status back to its original status
	 *
	 * 1 - Grab the post_status as it was prior to being disabled
	 * 2 - Delete post_meta holding prior status
	 * 3 - Change post status back to orginal
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function enable_product_post( $post ) {
		if ( $previous_status = get_post_meta( $post->ID, '_it_cart_buddy_enabled_status', true ) ) {
			delete_post_meta( $post->ID, '_it_cart_buddy_enabled_status' );
			$args = array( 'ID' => $post->ID, 'post_status' => $previous_status );
			wp_update_post( $args );
		}
	}

	/**
	 * Fires when add-ons are disabled and determines if associated products need to be disabled
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function maybe_disable_product_type_posts( $addon ) {
		$addon_category = empty( $addon['options']['category'] ) ? false : $addon['options']['category'];
		if ( 'product-type' != $addon_category )
			return;

		$this->disable_product_type_posts( $addon['slug'] );
	}

	/**
	 * When a Product Add-on is disabled, prevent it from showing
	 *
	 * 1 - Find all product posts for this product type
	 * 2 - Foreach product, pass to disable_product_post() method
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function disable_product_type_posts( $product_type ) {
		$post_stati = get_post_stati();
		if ( isset( $post_stati['_it_cart_buddy_disab'] ) )
			unset( $post_stati['_it_cart_buddy_disab'] );

		// Grab all products for this product-type
		$args = array(
			'post_status'  => array_keys( $post_stati ),
			'product_type' => $product_type,
			'number_posts' => -1,
		);
		if ( $products = it_cart_buddy_get_products( $args ) ) {
			foreach( $products as $product ) {
				$this->disable_product_post( $product );
			}
		}
	}

	/**
	 * Disable a single product type by changing post_status to _it_cart_buddy_disab.
	 *
	 * Changing the post_status will prevent it from showing in WP queries
	 * 1 - Save current post_status to post_meta: _it_Cart_buddy_enabled_status
	 * 2 - Change post status to _it_cart_buddy_disab
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function disable_product_post( $post ) {
		update_post_meta( $post->ID, '_it_cart_buddy_enabled_status', $post->post_status );
		$args = array( 'ID' => $post->ID, 'post_status' => '_it_cart_buddy_disab' );
		wp_update_post( $args );
	}

	/**
	 * Adds the product type column to the View All products table
	 *
	 * @since 0.3.3
	 * @param array $existing  exisiting columns array
	 * @return array  modified columns array
	*/
	function add_product_type_column_to_view_all_table( $existing ) {
		// Insert after title
		foreach ( (array) $existing as $id => $label ) {
			$columns[$id] = $label;
			if ( 'title' == $id )
				$columns['it_cart_buddy_product_type_column'] = __( 'Product Type', 'LION' );
		}
		// Insert at end if title wasn't found
		if ( empty( $columns['it_cart_buddy_product_type_column'] ) )
			$columns['it_cart_buddy_product_type_column'] = __( 'Product Type', 'LION' );

		return $columns;
	}

	/**
	 * Makes the product_type column added above sortable
	 *
	 * @since 0.3.3
	 * @param array $sortables  existing sortable columns
	 * @return array  modified sortable columnns
	*/
	function make_product_type_column_sortable( $sortables ) {
		$sortables['it_cart_buddy_product_type_column'] = 'it_cart_buddy_product_type_column';
		return $sortables;
	}

	/**
	 * Adds the product_type of a product to each row of the column added above
	 *
	 * @since 0.3.3
	 * @param string $column  column title
	 * @param integer $post  post ID
	 * @return void
	*/
	function add_product_type_info_to_view_all_table_rows( $column ) {
		global $post;
		if ( 'it_cart_buddy_product_type_column' != $column )
			return;

		$product = it_cart_buddy_get_product( $post );
		if ( $product_type = it_cart_buddy_get_addon( $product->product_type ) )
			esc_attr_e( $product_type['name'] );
	}
}
$IT_Cart_Buddy_Product_Post_Type = new IT_Cart_Buddy_Product_Post_Type();
