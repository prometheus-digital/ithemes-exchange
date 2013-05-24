<?php
/**
 * Creates the post type for Transactions
 *
 * @package IT_Exchange
 * @since 0.3.3
*/

/**
 * Registers the it_exchange_tran post type
 *
 * @since 0.3.3
*/
class IT_Exchange_Transaction_Post_Type {
	
	/**
	 * Class Constructor
	 *
	 * @todo Filter some of these options. Not all.
	 * @since 0.3.3
	 * @return void
	*/
	function IT_Exchange_Transaction_Post_Type() {
		$this->init();
		
		add_action( 'admin_init', array( $this, 'modify_post_type_features' ) );
		add_action( 'save_post', array( $this, 'save_transaction' ) );
		add_filter( 'manage_edit-it_exchange_tran_columns', array( $this, 'add_transaction_method_column_to_view_all_table' ) );
		add_filter( 'manage_edit-it_exchange_tran_sortable_columns', array( $this, 'make_transaction_method_column_sortable' ) );
		add_filter( 'manage_it_exchange_tran_posts_custom_column', array( $this, 'add_transaction_method_info_to_view_all_table_rows' ) );
	}

	function init() {
		$this->post_type = 'it_exchange_tran';
		$labels    = array(
			'name'          => __( 'Payments', 'LION' ),
			'singular_name' => __( 'Payment', 'LION' ),
			'edit_item'     => __( 'Edit Payment', 'LION' ),
		);
		$this->options = array(
			'labels'               => $labels,
			'description'          => __( 'An iThemes Exchange Post Type for storing all Payments in the system', 'LION' ),
			'public'               => false,
			'show_ui'              => true,
			'show_in_nav_menus'    => false,
			'show_in_menu'         => false, // We will be adding it manually with various labels based on available product-type add-ons
			'show_in_admin_bar'    => false,
			'hierarchical'         => false,
			'register_meta_box_cb' => array( $this, 'meta_box_callback' ),
			'supports'             => array( // Support everything but page-attributes for add-on flexibility
				'title',
				'editor',
				'author',
				'thumbnail',
				'excerpt',
				'trackbacks',
				'custom-fields',
				'comments',
				'revisions',
				'post-formats',
			),
			'capabilities'         => array(
				'edit_posts'        => 'edit_posts',
				'create_posts'      => false,
				'edit_others_posts' => 'edit_others_posts',
				'publish_posts'     => 'publish_posts',
			),
			'map_meta_cap'         => true,
			'capability_type'      => 'post',
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
	 * @uses it_exchange_get_enabled_add_ons()
	 * @return void
	*/
	function meta_box_callback( $post ) {
		$transaction = it_exchange_get_transaction( $post );

		// Add action for current product type
		if ( $transaction_methods = it_exchange_get_enabled_addons( array( 'category' => array( 'transaction-method' ) ) ) ) {
			foreach( $transaction_methods as $addon_slug => $params ) {
				if ( $addon_slug == $transaction->transaction_method )
					do_action( 'it_exchange_transaction_metabox_callback_' . $addon_slug, $transaction );
			}
		}

		// Do action for any product type
		do_action( 'it_exchange_transaction_metabox_callback', $transaction );
	}

	/**
	 * Provides specific hooks for when iThemes Exchange transactions are saved.
	 *
	 * This method is hooked to save_post. It provides hooks for add-on developers
	 * that will only be called when the post being saved is an iThemes Exchange transaction. 
	 * It provides the following 4 hooks:
	 * - it_exchange_save_transaction_unvalidated                    // Runs every time an iThemes Exchange transaction is saved.
	 * - it_exchange_save_transaction_unavalidate-[transaction-method] // Runs every time a specific iThemes Exchange transaction type is saved.
	 * - it_exchange_save_transaction                                // Runs every time an iThemes Exchange transaction is saved if not an autosave and if user has permission to save post
	 * - it_exchange_save_transaction-[transaction-method]             // Runs every time a specific iThemes Exchange transaction-method is saved if not an autosave and if user has permission to save post
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function save_transaction( $post ) { 

		// Exit if not it_exchange_prod post_type
		if ( ! 'it_exchange_tran' == get_post_type( $post ) ) 
			return;

		// Grab enabled transaction-method add-ons
		$transaction_method_addons = it_exchange_get_enabled_addons( array( 'category' => 'transaction-method' ) );
		
		// Grab current post's transaction-method
		$transaction_method = it_exchange_get_transaction_method();

		// These hooks fire off any time a it_exchange_tran post is saved w/o validations
		do_action( 'it_exchange_save_transaction_unvalidated', $post );
		foreach( (array) $transaction_method_addons as $slug => $params ) { 
			if ( $slug == $transaction_method ) { 
				do_action( 'it_exchange_save_transaction_unvalidated_' . $slug, $post );
			}   
		}   

		// Fire off actions with validations that most instances need to use.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( ! current_user_can( 'edit_post', $post ) ) 
			return;

		// This is called any time save_post hook
		do_action( 'it_exchange_save_transaction', $post );
		foreach( (array) $transaction_method_addons as $slug => $params ) { 
			if ( $slug == $transaction_method ) { 
				do_action( 'it_exchange_save_transaction_' . $slug, $post );
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
		if ( $transaction = it_exchange_get_transaction( $post ) ) {
			if ( ! empty( $transaction->transaction_method ) && ! get_post_meta( $transaction->ID, '_it_exchange_transaction_method', true ) )
				update_post_meta( $transaction->ID, '_it_exchange_transaction_method', $transaction->transaction_method );
		}
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
				$columns['it_exchange_transaction_method_column'] = __( 'Payment Method', 'LION' );
			if ( 'format' == $id )
				$columns['it_exchange_transaction_status_column'] = __( 'Payment Status', 'LION' );
		}
		// Insert at end if title wasn't found
		if ( empty( $columns['it_exchange_transaction_method_column'] ) )
			$columns['it_exchange_transaction_method_column'] = __( 'Payment Method', 'LION' );
		// Insert at end if status wasn't found
		if ( empty( $columns['it_exchange_transaction_status_column'] ) )
			$columns['it_exchange_transaction_status_column'] = __( 'Payment Status', 'LION' );

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
		$sortables['it_exchange_transaction_method_column'] = 'it_exchange_transaction_method_column';
		$sortables['it_exchange_transaction_status_column'] = 'it_exchange_transaction_status_column';
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
			case 'it_exchange_transaction_method_column' :
				$transaction = it_exchange_get_transaction( $post );
				if ( $transaction_method = it_exchange_get_addon( $transaction->transaction_method ) )
					esc_attr_e( $transaction_method['name'] );
				break;
			case 'it_exchange_transaction_status_column' :
					$status = it_exchange_get_transaction_status( $post );
					$method = it_exchange_get_transaction_method( $post );
					esc_attr_e( apply_filters( 'it_exchange_transaction_status_label_' . $method, $status ) );
				break;
		}
	}

	/**
	 * This triggers the method to modify what is included in $_wp_post_type_features for the it_exchange_tran post type
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function modify_post_type_features() {
		global $pagenow;
		$post = empty( $_GET['post'] ) ? false : get_post( $_GET['post'] );
		if ( ! $post )
			return false;

		it_exchange_get_transaction( $post );
	}
}
$IT_Exchange_Transaction_Post_Type = new IT_Exchange_Transaction_Post_Type();
