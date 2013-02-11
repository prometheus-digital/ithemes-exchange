<?php
/**
 * Creates the post type for Transactions
 *
 * @package IT_Cart_Buddy
 * @since 0.3.3
*/

/**
 * Registers the it_cart_buddy_tran post type
 *
 * @since 0.3.3
*/
class IT_Cart_Buddy_Transaction_Post_Type {
	
	/**
	 * Class Constructor
	 *
	 * @todo Filter some of these options. Not all.
	 * @since 0.3.3
	 * @return void
	*/
	function IT_Cart_Buddy_Transaction_Post_Type() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'register_custom_post_stati' ) );
		add_action( 'admin_init', array( $this, 'modify_post_type_features' ) );
		add_action( 'save_post', array( $this, 'save_transaction' ) );
		add_filter( 'manage_edit-it_cart_buddy_tran_columns', array( $this, 'add_transaction_method_column_to_view_all_table' ) );
		add_filter( 'manage_edit-it_cart_buddy_tran_sortable_columns', array( $this, 'make_transaction_method_column_sortable' ) );
		add_filter( 'manage_it_cart_buddy_tran_posts_custom_column', array( $this, 'add_transaction_method_info_to_view_all_table_rows' ) );
	}

	function init() {
		$this->post_type = 'it_cart_buddy_tran';
		$labels    = array(
			'name'          => __( 'Transactions', 'LION' ),
			'singular_name' => __( 'Transaction', 'LION' ),
			'edit_item'     => __( 'Edit Transaction', 'LION' ),
		);
		$this->options = array(
			'labels' => $labels,
			'description' => __( 'A Cart Buddy Post Type for storing all Transactions in the system', 'LION' ),
			'public'      => false,
			'show_ui'     => true,
			'show_in_nav_menus' => false,
			'show_in_menu'      => false, // We will be adding it manually with various labels based on available product-type add-ons
			'show_in_admin_bar' => false,
			'hierarchical'      => false,
			'supports'          => array( // Support everything but page-attributes for add-on flexibility
				'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields',
				'comments', 'revisions', 'post-formats',
			),
			'register_meta_box_cb' => array( $this, 'meta_box_callback' ),
			'capability_type' => 'post',
			'capabilities'      => array(
				'edit_posts' => true,
				'create_posts' => false
			),
			'map_meta_cap' => true,
		);

		add_action( 'init', array( $this, 'register_the_post_type' ) );
	}

	/**
	 * Actually registers the post type
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function register_the_post_type() {
		register_post_type( $this->post_type, $this->options );
	}

	/**
	 * Callback hook for transaction post type admin views
	 *
	 * @since 0.3.3
	 * @uses it_cart_buddy_get_enabled_add_ons()
	 * @return void
	*/
	function meta_box_callback( $post ) {
		$transaction = it_cart_buddy_get_transaction( $post );

		// Add action for current product type
		if ( $transaction_methods = it_cart_buddy_get_enabled_add_ons( array( 'category' => array( 'transaction-method' ) ) ) ) {
			foreach( $transaction_methods as $addon_slug => $params ) {
				if ( $addon_slug == $transaction->transaction_method )
					do_action( 'it_cart_buddy_transaction_metabox_callback_' . $addon_slug, $transaction );
			}
		}

		// Do action for any product type
		do_action( 'it_cart_buddy_transaction_metabox_callback', $transaction );
	}

	/**
	 * Provides specific hooks for when cart buddy transactions are saved.
	 *
	 * This method is hooked to save_post. It provides hooks for add-on developers
	 * that will only be called when the post being saved is a cart buddy transaction. 
	 * It provides the following 4 hooks:
	 * - it_cart_buddy_save_transaction_unvalidated                    // Runs every time a cart buddy transaction is saved.
	 * - it_cart_buddy_save_transaction_unavalidate-[transaction-method] // Runs every time a specific cart buddy transaction type is saved.
	 * - it_cart_buddy_save_transaction                                // Runs every time a cart buddy transaction is saved if not an autosave and if user has permission to save post
	 * - it_cart_buddy_save_transaction-[transaction-method]             // Runs every time a specific cart buddy transaction-method is saved if not an autosave and if user has permission to save post
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function save_transaction( $post ) { 

		// Exit if not it_cart_buddy_prod post_type
		if ( ! 'it_cart_buddy_tran' == get_post_type( $post ) ) 
			return;

		// Grab enabled transaction-method add-ons
		$transaction_method_addons = it_cart_buddy_get_enabled_add_ons( array( 'category' => 'transaction-method' ) );
		
		// Grab current post's transaction-method
		$transaction_method = it_cart_buddy_get_transaction_method();

		// These hooks fire off any time a it_cart_buddy_tran post is saved w/o validations
		do_action( 'it_cart_buddy_save_transaction_unvalidated', $post );
		foreach( (array) $transaction_method_addons as $slug => $params ) { 
			if ( $slug == $transaction_method ) { 
				do_action( 'it_cart_buddy_save_transaction_unvalidated-' . $slug, $post );
			}   
		}   

		// Fire off actions with validations that most instances need to use.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( ! current_user_can( 'edit_post', $post ) ) 
			return;

		// This is called any time save_post hook
		do_action( 'it_cart_buddy_save_transaction', $post );
		foreach( (array) $transaction_method_addons as $slug => $params ) { 
			if ( $slug == $transaction_method ) { 
				do_action( 'it_cart_buddy_save_transaction-' . $slug, $post );
			}   
		}   
	}

	/**
	 * Sets the post transaction_method on post creation
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function set_initial_post_transaction_method( $post ) {
		global $pagenow;
		if ( $transaction = it_cart_buddy_get_transaction( $post ) ) {
			if ( ! empty( $transaction->transaction_method ) && ! get_post_meta( $transaction->ID, '_it_cart_buddy_transaction_method', true ) )
				update_post_meta( $transaction->ID, '_it_cart_buddy_transaction_method', $transaction->transaction_method );
		}
	}

	/**
	 * Register Transaction Post status everywhere except for post-new.php, post.php, and edit.php for non-transaction post types.
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function register_custom_post_stati() {
		$args = array(
			'label'                     => _x( '_it_cart_buddy_trans_pending', 'Pending', 'LION' ),
			'label_count'               => _n_noop( 'Pending (%s)',  'Pending (%s)', 'LION' ),
			'public'                    => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'exclude_from_search'       => false,
		);
		register_post_status( '_it_cart_buddy_trans_pending', $args );
	}

	/**
	 * Adds the transaction method column to the View All transactions table
	 *
	 * @since 0.3.3
	 * @param array $existing  exisiting columns array
	 * @return array  modified columns array
	*/
	function add_transaction_method_column_to_view_all_table( $existing ) {
		// Insert after title
		foreach ( (array) $existing as $id => $label ) {
			$columns[$id] = $label;
			if ( 'title' == $id )
				$columns['it_cart_buddy_transaction_method_column'] = __( 'Transaction Method', 'LION' );
			if ( 'format' == $id )
				$columns['it_cart_buddy_transaction_status_column'] = __( 'Status', 'LION' );
		}
		// Insert at end if title wasn't found
		if ( empty( $columns['it_cart_buddy_transaction_method_column'] ) )
			$columns['it_cart_buddy_transaction_method_column'] = __( 'Transaction Method', 'LION' );
		// Insert at end if status wasn't found
		if ( empty( $columns['it_cart_buddy_transaction_status_column'] ) )
			$columns['it_cart_buddy_transaction_status_column'] = __( 'Status', 'LION' );

		// Remove Format
		if ( isset( $columns['format'] ) )
			unset( $columns['format'] );

		// Remove Author 
		if ( isset( $columns['author'] ) )
			unset( $columns['author'] );

		// Remove Comments 
		if ( isset( $columns['comments'] ) )
			unset( $columns['comments'] );

		return $columns;
	}

	/**
	 * Makes the transaction_method column added above sortable
	 *
	 * @since 0.3.3
	 * @param array $sortables  existing sortable columns
	 * @return array  modified sortable columnns
	*/
	function make_transaction_method_column_sortable( $sortables ) {
		$sortables['it_cart_buddy_transaction_method_column'] = 'it_cart_buddy_transaction_method_column';
		$sortables['it_cart_buddy_transaction_status_column'] = 'it_cart_buddy_transaction_status_column';
		return $sortables;
	}

	/**
	 * Adds the transaction_method of a transaction to each row of the column added above
	 *
	 * @since 0.3.3
	 * @param string $column  column title
	 * @param integer $post  post ID
	 * @return void
	*/
	function add_transaction_method_info_to_view_all_table_rows( $column ) {
		global $post, $wp_post_statuses;
		switch( $column ) {
			case 'it_cart_buddy_transaction_method_column' :
				$transaction = it_cart_buddy_get_transaction( $post );
				if ( $transaction_method = it_cart_buddy_get_add_on( $transaction->transaction_method ) )
					esc_attr_e( $transaction_method['name'] );
				break;
			case 'it_cart_buddy_transaction_status_column' :
					esc_attr_e( $wp_post_statuses[get_post_status( $post )]->label );
				break;
		}
	}

	/**
	 * This triggers the method to modify what is included in $_wp_post_type_features for the it_cart_buddy_tran post type
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function modify_post_type_features() {
		global $pagenow;
		$post = empty( $_GET['post'] ) ? false : get_post( $_GET['post'] );
		if ( ! $post )
			return false;

		it_cart_buddy_get_transaction( $post );
	}
}
$IT_Cart_Buddy_Transaction_Post_Type = new IT_Cart_Buddy_Transaction_Post_Type();
