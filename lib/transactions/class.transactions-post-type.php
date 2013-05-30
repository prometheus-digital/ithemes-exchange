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
		add_filter( 'manage_edit-it_exchange_tran_columns', array( $this, 'modify_all_transactions_table_columns' ) );
		add_filter( 'manage_edit-it_exchange_tran_sortable_columns', array( $this, 'make_transaction_custom_columns_sortable' ) );
		add_filter( 'manage_it_exchange_tran_posts_custom_column', array( $this, 'add_transaction_method_info_to_view_all_table_rows' ) );
		add_filter( 'it_exchange_transaction_metabox_callback', array( $this, 'register_transaction_details_admin_metabox' ) );
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
	function modify_all_transactions_table_columns( $existing ) {

		// Add a filter to replace the title text with the Date
		add_filter( 'the_title', array( $this, 'replace_transaction_title_with_date' ) );

		// Remove Checkbox - adding it back below
		if ( isset( $existing['cb'] ) ) {
			$check = $existing['cb'];
			unset( $existing['cb'] );
		}

		// Remove Title - adding it back below
		if ( isset( $existing['title'] ) )
			unset( $existing['title'] );

		// Remove Format
		if ( isset( $existing['format'] ) )
			unset( $existing['format'] );

		// Remove Author 
		if ( isset( $existing['author'] ) )
			unset( $existing['author'] );

		// Remove Comments 
		if ( isset( $existing['comments'] ) )
			unset( $existing['comments'] );

		// Remove Date
		if ( isset( $existing['date'] ) )
			unset( $existing['date'] );

		// All Core should be removed at this point. Build ours back (including date from core)
		$exchange_columns = array(
			'cb'                                      => $check,
			'title'                                   => __( 'Date', 'LION' ),
			'it_exchange_transaction_total_column'    => __( 'Payment Total', 'LION' ),
			'it_exchange_transaction_status_column'   => __( 'Payment Status', 'LION' ),
			'it_exchange_transaction_customer_column' => __( 'Customer', 'LION' ),
			'it_exchange_transaction_method_column'   => __( 'Payment Method', 'LION' ),
		);

		// Merge ours back with existing to preserve any 3rd party columns
		$columns = array_merge( $exchange_columns, $existing );
		return $columns;
	}

	/**
	 * Replace the title with the date
	 *
	 * @since 0.4.0
	 *
	 * @param string $title the real title
	 * @return string
	*/
	function replace_transaction_title_with_date( $title ) {
		global $post;

		if ( '0000-00-00 00:00:00' == $post->post_date ) {
			$t_time = $h_time = __( 'Unknown', 'LION' );
			$time_diff = 0; 
		} else {
			$t_time = get_the_time( __( 'Y/m/d g:i:s A' ) ); 
			$m_time = $post->post_date;
			$time = get_post_time( 'G', true, $post );

			$time_diff = time() - $time;

			if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS )
				$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) ); 
			else 
				$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
		}

		return $h_time;
	}

	/**
	 * Makes some of the custom transaction columns added above sortable
	 *
	 * @since 0.3.3
	 * @param array $sortables  existing sortable columns
	 * @return array  modified sortable columnns
	*/
	function make_transaction_custom_columns_sortable( $sortables ) {
		$sortables['title']                                   = array( 'date', true );
		$sortables['it_exchange_transaction_method_column']   = 'it_exchange_transaction_method_column';
		$sortables['it_exchange_transaction_status_column']   = 'it_exchange_transaction_status_column';
		$sortables['it_exchange_transaction_customer_column'] = 'it_exchange_transaction_customer_column';
		$sortables['it_exchange_transaction_total_column']    = 'it_exchange_transaction_total_column';
		return $sortables;
	}

	/**
	 * Adds the values to each row of the custom columns added above
	 *
	 * @since 0.3.3
	 * @param string $column  column title
	 * @param integer $post  post ID
	 * @return void
	*/
	function add_transaction_method_info_to_view_all_table_rows( $column ) {
		global $post, $wp_post_statuses;
		$transaction = it_exchange_get_transaction( $post );
		switch( $column ) {
			case 'it_exchange_transaction_method_column' :
				esc_attr_e( it_exchange_get_transaction_method_name( $transaction ) );
				break;
			case 'it_exchange_transaction_status_column' :
				esc_attr_e( it_exchange_get_transaction_status_label( $post ) );
				break;
			case 'it_exchange_transaction_customer_column' :
				if ( $customer = it_exchange_get_transaction_customer( $transaction ) )
					esc_attr_e( empty( $customer->wp_user->display_name ) ? $customer->wp_user->user_login : $customer->wp_user->display_name );
				else
					esc_attr_e( __( 'Unknown', 'LION' ) );
				break;
			case 'it_exchange_transaction_total_column' :
				esc_attr_e( it_exchange_get_transaction_total( $transaction ) );		
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

	/**
	 * Registers the transaction details meta box
	 *
	 * @since 0.4.0
	 *
	 * @param object $post post object
	 * @return void
	*/
	function register_transaction_details_admin_metabox( $post ) {
		$title     = __( 'Transaction Details', 'LION' );
		$callback  = array( $this, 'print_transaction_details_metabox' );
		$post_type = 'it_exchange_tran';
		add_meta_box( 'it-exchange-transaction-details', $title, $callback, $post_type, 'normal', 'high' );
	}

	/**
	 * Prints the transaction details metabox
	 *
	 * @since 0.4.0
	 * @param object $post post object
	 * @return void
	*/
	function print_transaction_details_metabox( $post ) {
		?>
		<div class="customer_data">
			<p>
				<strong><?php _e( 'Customer', 'LION' ); ?></strong><br />
				<?php esc_attr_e( it_exchange_get_transaction_customer_display_name( $post ) ); ?><br />
				<?php esc_attr_e( it_exchange_get_transaction_customer_email( $post ) ); ?><br />
				<a href="<?php esc_attr_e( it_exchange_get_transaction_customer_admin_profile_url( $post ) ); ?>">
					<?php _e( 'Full Profile', 'LION' ); ?>
				</a>
			</p>
		</div>
		<hr />
		<div>
			<p>
				<strong><?php _e( 'Transaction', 'LION' ); ?></strong><br />
				<?php _e( 'ID: ', 'LION' ); ?> <?php esc_attr_e( $post->ID ); ?><br />
				<?php _e( 'Date: ', 'LION' ); ?> <?php esc_attr_e( it_exchange_get_transaction_date( $post ) ); ?><br />
				<?php _e( 'Status: ', 'LION' ); ?> <?php esc_attr_e( it_exchange_get_transaction_status_label( $post ) ); ?><br />
				<?php _e( 'Method: ', 'LION' ); ?> <?php esc_attr_e( it_exchange_get_transaction_method_name( $post ) ); ?><br />
				<?php _e( 'Currency: ', 'LION' ); ?> <?php esc_attr_e( it_exchange_get_transaction_currency( $post ) ); ?><br />
				<?php _e( 'Subtotal: ', 'LION' ); ?> <?php esc_attr_e( it_exchange_get_transaction_subtotal( $post ) ); ?><br />

				<?php
				if ( $coupons = it_exchange_get_transaction_coupons( $post ) ) {
					echo 'Coupon(s):<ul>';
					foreach ( $coupons as $coupon => $data ) {
						echo '<li>' . $coupon['code'] . ': ' . $coupon['amount'] . ' ' . $coupon['type'] . '</li>';
					}
					echo '</ul>';
					echo 'Total Discount from Coupons: ' . it_exchange_get_transaction_coupons_total_discount( $post ) . '<br />';
				}
				?>
				<?php _e( 'Total: ', 'LION' ); ?> <?php esc_attr_e( it_exchange_get_transaction_total( $post ) ); ?><br />
			</p>
		<?php
	}
}
$IT_Exchange_Transaction_Post_Type = new IT_Exchange_Transaction_Post_Type();
