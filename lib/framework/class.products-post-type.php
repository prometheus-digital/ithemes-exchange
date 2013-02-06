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
		add_action( 'save_post', array( $this, 'save_product' ) );
		add_action( 'admin_init', array( $this, 'get_edit_item_label' ) );
	}

	function init() {
		$this->post_type = 'it_cart_buddy_prod';
		$labels    = array(
			'name'          => __( 'Products', 'LION' ),
			'singular_name' => __( 'Product', 'LION' ),
			'add_new_item'  => $this->get_add_new_item_label(),
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
	 * Call Back hook
	 *
	 * @since 0.3.0
	 * @uses it_cart_buddy_get_enabled_add_ons()
	 * @return void
	*/
	function meta_box_callback( $post ) {
		$this->setup_post_type_properties( $post );

		if ( $product_types = it_cart_buddy_get_enabled_add_ons( array( 'category' => array( 'product-type' ) ) ) ) {
			foreach( $product_types as $addon_slug => $params ) {
				do_action( 'it_cart_buddy_product_metabox_callback_' . $addon_slug, $post );
				do_action( 'it_cart_buddy_product_metabox_callback', $post );
			}
		}
	}

	/**
	 * Generates the Add New Product Label for a new Product 
	 *
	 * @since 0.3.0
	 * @return string $label Label for add new product page.
	*/
	function get_add_new_item_label() {
		global $pagenow;
		if ( $pagenow != 'post-new.php' || empty( $_GET['post_type'] ) || 'it_cart_buddy_prod' != $_GET['post_type'] )
			return apply_filters( 'it_cart_buddy_add_new_product_label', __( 'Add New Product', 'LION' ) );

		$product_add_ons = it_cart_buddy_get_enabled_add_ons( array( 'category' => array( 'product-type' ) ) );
		$product = array();
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
		return apply_filters( 'it_cart_buddy_add_new_product_label-' . $product['slug'], __( 'Add New ', 'LION' ) . $singular );
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
	function get_edit_item_label() {
		global $pagenow, $wp_post_types;
		$post = empty( $_GET['post'] ) ? false : get_post( $_GET['post'] );

		if ( ! is_admin() || $pagenow != 'post.php' || ! $post )
			return;

		if ( empty( $wp_post_types['it_cart_buddy_prod'] ) )
			return;
			
		if ( 'it_cart_buddy_prod' != get_post_type( $post ) )
			return;

		$product_type = it_cart_buddy_get_product_type( $post );

		$product_add_ons = it_cart_buddy_get_enabled_add_ons( array( 'category' => array( 'product-type' ) ) );
		$product = array();
		if ( 1 == count( $product_add_ons ) ) {
			$product = reset( $product_add_ons );
		} else {
			if ( ! empty( $product_type ) && ! empty( $product_add_ons[$product_type] ) )
				$product = $product_add_ons[$product_type];
			else
				$product['options']['labels']['singular_name'] = 'Product';
		}

		$singular = empty( $product['options']['labels']['singular_name'] ) ? $product['name'] : $product['options']['labels']['singular_name'];
		$label = apply_filters( 'it_cart_buddy_edit_product_label-' . $product['slug'], __( 'Edit ', 'LION' ) . $singular );
		$wp_post_types['it_cart_buddy_prod']->labels->edit_item = $label;
	}

	/**
	 * Add's Product Type vars to this post
	 *
	 * @since 0.3.1
	 * @return void
	*/
	function setup_post_type_properties() {
		global $post, $pagenow;

		// Set the product type from Param or from post_meta
		$product_type = it_cart_buddy_get_product_type( $post );

		// If we're not on the add-new or edit product page, exit. Also, if we're not on the correct post type exit
		if ( 'post.php' != $pagenow && 'post-new.php' != $pagenow && 'it_cart_buddy_prod' != get_post_type( $post ) )
			return;

		// If this is a new product, tag the product_type from the URL param
		if ( 'post-new.php' == $pagenow )
			update_post_meta( $post->ID, '_it_cart_buddy_product_type', $product_type );

		// Add to product type to $post object
		$post->it_cart_buddy_product_type = $product_type;
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
		$product_type_addons = it_cart_buddy_get_enabled_add_ons( array( 'category' => 'product-type' ) );
		
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
}
$IT_Cart_Buddy_Product_Post_Type = new IT_Cart_Buddy_Product_Post_Type();
