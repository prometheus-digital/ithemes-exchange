<?php
/**
 * Creates the post type for Transactions
 *
 * @package IT_Exchange
 * @since   0.3.3
 */

/**
 * Registers the it_exchange_tran post type
 *
 * @since 0.3.3
 */
class IT_Exchange_Transaction_Post_Type {

	/**
	 * @var string
	 */
	var $post_type;

	/**
	 * @var array
	 */
	var $options;

	/**
	 * Class Constructor
	 *
	 * @since 0.3.3
	 */
	public function __construct() {
		$this->init();

		add_action( 'save_post_it_exchange_tran', array( $this, 'save_transaction' ) );

		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'modify_post_type_features' ) );
			add_filter( 'manage_edit-it_exchange_tran_columns', array( $this, 'modify_all_transactions_table_columns' ) );
			add_filter( 'manage_edit-it_exchange_tran_sortable_columns', array( $this, 'make_transaction_custom_columns_sortable' ) );
			add_filter( 'manage_it_exchange_tran_posts_custom_column', array( $this, 'add_transaction_method_info_to_view_all_table_rows' ) );
			add_filter( 'request', array( $this, 'modify_wp_query_request_on_edit_php' ) );
			add_filter( 'it_exchange_transaction_metabox_callback', array( $this, 'register_transaction_details_admin_metabox' ) );
			add_filter( 'post_row_actions', array( $this, 'rename_edit_to_details' ), 10, 2 );
			add_filter( 'page_row_actions', array( $this, 'rename_edit_to_details' ), 10, 2 );
			add_filter( 'screen_layout_columns', array( $this, 'modify_details_page_layout' ) );
			add_filter( 'get_user_option_screen_layout_it_exchange_tran', array( $this,	'update_user_column_options' ) );
			add_filter( 'bulk_actions-edit-it_exchange_tran', array( $this, 'edit_bulk_actions' ) );
			add_action( 'wp_ajax_it-exchange-update-transaction-status', array( $this, 'ajax_update_status' ) );
			add_action( 'wp_ajax_it-exchange-add-note', array( $this, 'ajax_add_note' ) );
			add_action( 'wp_ajax_it-exchange-remove-activity', array( $this, 'ajax_remove_activity' ) );
			add_filter( 'heartbeat_received', array( $this, 'activity_heartbeat' ), 10, 2 );
		}
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	public function IT_Exchange_Transaction_Post_Type() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Initialize the post type.
	 *
	 * @since 1.0
	 */
	public function init() {

		$this->post_type = 'it_exchange_tran';

		$labels = array(
			'name'          => __( 'Payments', 'it-l10n-ithemes-exchange' ),
			'singular_name' => __( 'Payment', 'it-l10n-ithemes-exchange' ),
			'edit_item'     => __( 'Payment Details', 'it-l10n-ithemes-exchange' ),
			'search_items'  => __( 'Search Payments', 'it-l10n-ithemes-exchange' )
		);

		$this->options = array(
			'labels'               => $labels,
			'description'          => __( 'An iThemes Exchange Post Type for storing all Payments in the system', 'it-l10n-ithemes-exchange' ),
			'public'               => false,
			'show_ui'              => true,
			'show_in_nav_menus'    => false,
			'show_in_menu'         => false,
			// We will be adding it manually with various labels based on available product-type add-ons
			'show_in_admin_bar'    => false,
			'hierarchical'         => apply_filters( 'it_exchange_transactions_post_type_hierarchical', false ),
			'register_meta_box_cb' => array( $this, 'meta_box_callback' ),
			'delete_with_user'     => false,
			'supports'             => array( // Support everything but page-attributes for add-on flexibility
				'title',
				'author',
				'custom-fields',
				'post-formats',
			),
			'capabilities'         => array(
				'create_posts' => apply_filters( 'it_exchange_tran_create_posts_capabilities', 'do_not_allow' ),
			),
			'capability_type' => IT_Exchange_Capabilities::TRANSACTION,
			'map_meta_cap'    => true
		);

		add_action( 'init', array( $this, 'register_the_post_type' ) );
	}

	/**
	 * Change 'Edit Transaction' to 'View Details' in All Payments Table
	 *
	 * @since 0.4.0
	 *
	 * @param array   $actions actions array
	 * @param WP_Post $post    object
	 *
	 * @return array
	 */
	public function rename_edit_to_details( $actions, $post ) {
		if ( 'it_exchange_tran' === $post->post_type ) {
			$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' .
			                   esc_attr( __( 'View the transaction details', 'it-l10n-ithemes-exchange' ) ) . '">' .
			                   __( 'Details', 'it-l10n-ithemes-exchange' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Set the max columns option for the add / edit product page.
	 *
	 * @since 0.4.0
	 *
	 * @param array $columns Existing array of how many columns to show for a post type
	 *
	 * @return array Filtered array
	 */
	public function modify_details_page_layout( $columns ) {
		$columns['it_exchange_tran'] = 1;

		return $columns;
	}

	/**
	 * Updates the user options for number of columns to use on transaction details page
	 *
	 * @since 0.4.0
	 *
	 * @param int $existing
	 *
	 * @return int
	 */
	public function update_user_column_options( $existing ) {
		return 1;
	}

	/**
	 * Actually registers the post type
	 *
	 * @since 0.3.3
	 *
	 * @return void
	 */
	public function register_the_post_type() {
		register_post_type( $this->post_type, $this->options );
	}

	/**
	 * Callback hook for transaction post type admin views
	 *
	 * @since 0.3.3
	 * @uses  it_exchange_get_enabled_add_ons()
	 *
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function meta_box_callback( $post ) {
		$transaction = it_exchange_get_transaction( $post );

		// Add action for current product type
		if ( $transaction_methods = it_exchange_get_enabled_addons( array( 'category' => array( 'transaction-method' ) ) ) ) {
			foreach ( $transaction_methods as $addon_slug => $params ) {
				if ( $addon_slug == $transaction->transaction_method ) {
					do_action( 'it_exchange_transaction_metabox_callback_' . $addon_slug, $transaction );
				}
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
	 *
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function save_transaction( $post ) {

		// Exit if not it_exchange_prod post_type
		if ( ! 'it_exchange_tran' == get_post_type( $post ) ) {
			return;
		}

		// Grab enabled transaction-method add-ons
		$transaction_method_addons = it_exchange_get_enabled_addons( array( 'category' => 'transaction-method' ) );

		// Grab current post's transaction-method
		$transaction_method = it_exchange_get_transaction_method();

		// These hooks fire off any time a it_exchange_tran post is saved w/o validations
		do_action( 'it_exchange_save_transaction_unvalidated', $post );

		foreach ( (array) $transaction_method_addons as $slug => $params ) {
			if ( $slug == $transaction_method ) {
				do_action( 'it_exchange_save_transaction_unvalidated_' . $slug, $post );
			}
		}

		// Fire off actions with validations that most instances need to use.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post ) ) {
			return;
		}

		// This is called any time save_post hook
		do_action( 'it_exchange_save_transaction', $post );
		foreach ( (array) $transaction_method_addons as $slug => $params ) {
			if ( $slug == $transaction_method ) {
				do_action( 'it_exchange_save_transaction_' . $slug, $post );
			}
		}
	}

	/**
	 * Sets the post transaction_method on post creation
	 *
	 * @since 0.3.3
	 *
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function set_initial_post_transaction_method( $post ) {

		if ( $transaction = it_exchange_get_transaction( $post ) ) {
			if ( ! empty( $transaction->transaction_method ) && ! get_post_meta( $transaction->ID, '_it_exchange_transaction_method', true ) ) {
				update_post_meta( $transaction->ID, '_it_exchange_transaction_method', $transaction->transaction_method );
			}
		}
	}

	/**
	 * Adds the transaction method column to the View All transactions table
	 *
	 * @since 0.3.3
	 *
	 * @param array $existing exisiting columns array
	 *
	 * @return array  modified columns array
	 */
	public function modify_all_transactions_table_columns( $existing ) {

		// Add a filter to replace the title text with the Date
		add_filter( 'the_title', array( $this, 'replace_transaction_title_with_order_number' ), 10, 2 );

		// Remove Checkbox - adding it back below
		if ( isset( $existing['cb'] ) ) {
			$check = $existing['cb'];
			unset( $existing['cb'] );
		}

		unset( $existing['title'], $existing['format'], $existing['author'], $existing['comments'], $existing['date'], $existing['builder_layout'] );

		// All Core should be removed at this point. Build ours back (including date from core)
		$exchange_columns = array(
			'cb'                                      => isset( $check ) ? $check : '',
			'title'                                   => __( 'Order Number', 'it-l10n-ithemes-exchange' ),
			'it_exchange_transaction_total_column'    => __( 'Total', 'it-l10n-ithemes-exchange' ),
			'it_exchange_transaction_status_column'   => __( 'Status', 'it-l10n-ithemes-exchange' ),
			'it_exchange_transaction_customer_column' => __( 'Customer', 'it-l10n-ithemes-exchange' ),
			'it_exchange_transaction_method_column'   => __( 'Method', 'it-l10n-ithemes-exchange' ),
			'it_exchange_transaction_date_column'     => __( 'Date', 'it-l10n-ithemes-exchange' ),
		);

		// Merge ours back with existing to preserve any 3rd party columns
		$columns = array_merge( $exchange_columns, $existing );

		return $columns;
	}

	/**
	 * Replace the title with the order_number
	 *
	 * @since 0.4.0
	 *
	 * @param string $title the real title
	 * @param int    $ID
	 *
	 * @return string
	 */
	public function replace_transaction_title_with_order_number( $title, $ID ) {
		return it_exchange_get_transaction_order_number( $ID );
	}

	/**
	 * Makes some of the custom transaction columns added above sortable
	 *
	 * @since 0.3.3
	 *
	 * @param array $sortables existing sortable columns
	 *
	 * @return array  modified sortable columnns
	 */
	public function make_transaction_custom_columns_sortable( $sortables ) {

		$sortables['it_exchange_transaction_date_column']   = 'date';
		$sortables['it_exchange_transaction_method_column'] = 'it_exchange_transaction_method_column';

		return $sortables;
	}

	/**
	 * Adds the values to each row of the custom columns added above
	 *
	 * @since 0.3.3
	 *
	 * @param string  $column column title
	 *
	 * @return void
	 */
	function add_transaction_method_info_to_view_all_table_rows( $column ) {
		global $post;

		$transaction = it_exchange_get_transaction( $post );

		switch ( $column ) {
			case 'it_exchange_transaction_method_column' :
				$method_name = esc_attr( it_exchange_get_transaction_method_name( $transaction ) );
				echo empty( $method_name ) ? $transaction->transaction_method : $method_name;
				break;
			case 'it_exchange_transaction_status_column' :
				echo it_exchange_get_transaction_status_label( $post );
				break;
			case 'it_exchange_transaction_customer_column' :
				echo it_exchange_get_transaction_customer_display_name( $transaction );
				break;
			case 'it_exchange_transaction_total_column' :
				echo it_exchange_get_transaction_total( $transaction );
				break;
			case 'it_exchange_transaction_date_column' :

				$m_time = $transaction->post_date;
				$time   = get_post_time( 'G', true, $post );

				$time_diff = time() - $time;

				if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
					$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
				} else {
					$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
				}

				echo $h_time;
				break;
		}
	}

	/**
	 * Modify sort of transactions in edit.php for custom columns
	 *
	 * @since 0.4.0
	 *
	 * @param string $request original request
	 *
	 * @return array
	 */
	function modify_wp_query_request_on_edit_php( $request ) {
		global $hook_suffix;

		if ( 'edit.php' === $hook_suffix && 'it_exchange_tran' === $request['post_type'] && isset( $request['orderby'] ) ) {
			switch ( $request['orderby'] ) {
				case 'title':
					$request['orderby'] = 'ID';
					break;
				case 'it_exchange_transaction_status_column':
					$request['orderby']  = 'meta_value';
					$request['meta_key'] = '_it_exchange_transaction_status';
					break;
				case 'it_exchange_transaction_method_column':
					$request['orderby']  = 'meta_value';
					$request['meta_key'] = '_it_exchange_transaction_method';
					break;
			}
		}

		return $request;
	}

	/**
	 * This triggers the method to modify what is included in $_wp_post_type_features for the it_exchange_tran post type
	 *
	 * @since 0.3.3
	 * @return void
	 */
	public function modify_post_type_features() {
	
		global $pagenow;

		$post = empty( $_GET['post'] ) ? false : get_post( $_GET['post'] );

		if ( $post && $post->post_type === 'it_exchange_tran' ) {
			$transaction = it_exchange_get_transaction( $post );
			
			$supports = array(
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
			);
	
			// If is_admin and is post-new.php or post.php, only register supports for current transaction-method
			if ( 'post-new.php' != $pagenow && 'post.php' != $pagenow ) {
				return;
			} // Don't remove any if not on post-new / or post.php
	
			if ( $addon = it_exchange_get_addon( $transaction->transaction_method ) ) {
				// Remove any supports args that the transaction add-on does not want.
				foreach ( $supports as $option ) {
					if ( empty( $addon['options']['supports'][ $option ] ) ) {
						remove_post_type_support( 'it_exchange_tran', $option );
					}
				}
			} else {
				// Can't find the transaction - remove everything
				foreach ( $supports as $option ) {
					remove_post_type_support( 'it_exchange_tran', $option );
				}
			}
		}
	}

	/**
	 * Registers the transaction details meta box
	 *
	 * @since 0.4.0
	 *
	 * @param WP_Post $post post object
	 *
	 * @return void
	 */
	function register_transaction_details_admin_metabox( $post ) {

		// Remove Publish metabox
		remove_meta_box( 'submitdiv', 'it_exchange_tran', 'side' );

		// Remove Slug metabox
		remove_meta_box( 'slugdiv', 'it_exchange_tran', 'normal' );

		// Remove screen options tab
		add_filter( 'screen_options_show_screen', '__return_false' );

		add_action( 'edit_form_after_editor', array( $this, 'print_transaction_details_metabox' ) );
	}

	/**
	 * Prints the transaction details metabox
	 *
	 * @since 0.4.0
	 *
	 * @param object $post post object
	 *
	 * @return void
	 */
	public function print_transaction_details_metabox( $post ) {
		require_once IT_Exchange::$dir . '/lib/admin/views/transaction/single.php';
	}

	/**
	 * Print the activity meta box.
	 *
	 * @since 1.34
	 *
	 * @param WP_Post $post
	 */
	public static function print_activity( $post ) {

		$factory = it_exchange_get_txn_activity_factory();
		?>

		<div id="it-exchange-transaction-activity">

			<div class="exchange-activity-stream-header">

				<label for="exchange-activity-filter" class="screen-reader-text">
					<?php _e( 'Filter by Activity Type', 'it-l10n-ithemes-exchange' ); ?>
				</label>

				<div class="exchange-filter-action-container">
					<select id="exchange-activity-filter">
						<option value=""><?php _e( 'All Activity', 'it-l10n-ithemes-exchange' ); ?></option>

						<?php foreach ( $factory->get_types() as $slug => $type ): ?>
							<option value="<?php echo esc_attr( $slug ); ?>"><?php echo $type['label']; ?></option>
						<?php endforeach; ?>
					</select>

					<button id="exchange-add-note" class="button button-secondary">
						<?php _e( 'Add Note', 'it-l10n-ithemes-exchange' ); ?>
					</button>
				</div>

				<div class="exchange-note-writing-container">

					<button id="exchange-close-note" class="dashicons-before dashicons-no-alt">
						<span class="screen-reader-text"><?php _e( 'Close Editor', 'it-l10n-ithemes-exchange' ); ?></span>
					</button>

					<textarea id="exchange-note-editor" placeholder="<?php _e( 'Type your message here...', 'it-l10n-ithemes-exchange' ); ?>"></textarea>

					<span class="exchange-note-writing-meta">

						<label for="exchange-notify-customer">
							<input type="checkbox" id="exchange-notify-customer">
							<?php _e( 'Notify customer', 'it-l10n-ithemes-exchange' ); ?>
						</label>

						<button id="exchange-post-note" class="button button-primary"><?php _e( 'Post', 'it-l10n-ithemes-exchange' ); ?></button>
					</span>
				</div>
			</div>

			<p id="exchange-no-activity-found"><?php _e( 'No activity found.', 'it-l10n-ithemes-exchange' ); ?></p>

			<ul id="activity-stream"></ul>
		</div>

		<script type="text/template" id="exchange-activity-tpl">
			<li id="activity-item-<%= a.getID() %>" class="<%= 'type-' + a.getType() %> <%= a.isPublic() ? 'is-public' : '' %>">
				<header><%= moment( a.getTime() ).calendar() %></header>
				<article>
					<p><%= a.getDescription() %></p>

					<% if ( a.hasActor() ) { %>
						<%= a.getActor().html() %>
					<% } %>
				</article>

				<a href="#" class="exchange-delete-activity">
					<?php _e( 'Delete', 'it-l10n-ithemes-exchange' ); ?>
				</a>
			</li>
		</script>

		<script type="text/template" id="exchange-activity-actor-tpl">
			<footer>
				<% if ( a.hasIcon() ) { %>
					<%= a.getIcon().html() %>
				<% } %>

				<% if ( a.getURL() ) { %>
					<a href="<%= a.getURL() %>"><%= a.getName() %></a>
				<% } else { %>
					<%= a.getName() %>
				<% } %>
			</footer>
		</script>

		<script type="text/template" id="exchange-icon-tpl">
			<img class="exchange-icon" src="<%= url %>">
		</script>
		<?php
	}

	/**
	 * Update transaction status on AJAX calls
	 *
	 * @since 0.4.11
	 *
	 * @return void
	 */
	public function ajax_update_status() {
		$transaction_id = empty( $_POST['it-exchange-transaction-id'] ) ? false : absint( $_POST['it-exchange-transaction-id'] );
		$nonce          = empty( $_POST['it-exchange-nonce'] ) ? false : $_POST['it-exchange-nonce'];
		$current_status = empty( $_POST['it-exchange-current-status'] ) ? false : $_POST['it-exchange-current-status'];
		$new_status     = empty( $_POST['it-exchange-new-status'] ) ? false : $_POST['it-exchange-new-status'];

		// Fail if we don't have all the data
		if ( ! $transaction_id || ! $nonce || ! $current_status || ! $new_status ) {
			die( 'failed' );
		}

		// Fail if we don't have a valid nonce
		if ( ! wp_verify_nonce( $nonce, 'update-transaction-status' . $transaction_id ) ) {
			die( 'failed' );
		}

		if ( ! current_user_can( 'edit_it_transaction', $transaction_id ) ) {
			die( 'failed' );
		}

		// Fail if status is the same as old status
		if ( $current_status == $new_status ) {
			die( 'failed' );
		}

		// Fail if transaction isn't found
		if ( ! $transaction = it_exchange_get_transaction( $transaction_id ) ) {
			die( 'failed' );
		}

		// Attempt to change status
		if ( it_exchange_update_transaction_status( $transaction, $new_status ) ) {
			die( 'success' );
		} else {
			die( 'failed' );
		}
	}

	/**
	 * Add a note via AJAX.
     *
	 * @since 1.34
	 */
	public function ajax_add_note() {

		$nonce = empty( $_POST['nonce'] ) ? '' : $_POST['nonce'];
		$note = empty( $_POST['note'] ) ? '' : wp_kses( stripslashes( $_POST['note'] ), wp_kses_allowed_html() );
		$public = empty( $_POST['isPublic'] ) ? false : (bool) $_POST['isPublic'];
		$txn = empty( $_POST['txn'] ) ? false : it_exchange_get_transaction( $_POST['txn'] );

		if ( ! wp_verify_nonce( $nonce, 'it-exchange-add-note' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Request Expired. Please refresh and try again.', 'it-l10n-ithemes-exchange' )
			) );
		}

		if ( ! current_user_can( 'edit_it_transaction', $txn->ID ) ) {
			wp_send_json_error( array(
				'message' => __( 'You don\'t have permission to do that.', 'it-l10n-ithemes-exchange' )
			) );
		}

		if ( empty( $note ) || ! $txn instanceof IT_Exchange_Transaction ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid response format.', 'it-l10n-ithemes-exchange' )
			) );
		}

		$builder = new IT_Exchange_Txn_Activity_Builder( $txn, 'note' );
		$builder->set_public( $public );
		$builder->set_description( $note );
		$builder->set_actor( new IT_Exchange_Txn_Activity_User_Actor( wp_get_current_user() ) );
		$note = $builder->build( it_exchange_get_txn_activity_factory() );

		wp_send_json_success( array(
			'activity' => $note->to_array()
		) );
	}

	/**
	 * Remove a note via AJAX.
     *
	 * @since 1.34
	 */
	public function ajax_remove_activity() {

		$nonce = empty( $_POST['nonce'] ) ? '' : $_POST['nonce'];
		$ID = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		$txn = empty( $_POST['txn'] ) ? false : it_exchange_get_transaction( $_POST['txn'] );

		if ( ! wp_verify_nonce( $nonce, 'it-exchange-add-note' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Request Expired. Please refresh and try again.', 'it-l10n-ithemes-exchange' )
			) );
		}

		if ( ! current_user_can( 'edit_it_transaction', $txn->ID ) ) {
			wp_send_json_error( array(
				'message' => __( 'You don\'t have permission to do that.', 'it-l10n-ithemes-exchange' )
			) );
		}

		if ( empty( $ID ) || ! $txn instanceof IT_Exchange_Transaction ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid response format.', 'it-l10n-ithemes-exchange' )
			) );
		}

		try {

			if ( it_exchange_get_txn_activity( $ID )->delete() ) {
				wp_send_json_success();
			} else {
				wp_send_json_error( array(
					'message' => __( 'An unexpected error occurred.', 'it-l10n-ithemes-exchange' )
				) );
			}

		} catch ( UnexpectedValueException $e ) {
			wp_send_json_error( array(
				'message' => $e->getMessage()
			) );
		}
	}

	/**
	 * Add new activity items to the heartbeat pulse.
     *
     * @since 1.34
     *
	 * @param array $response
	 * @param array $data
	 *
	 * @return array
     */
	public function activity_heartbeat( $response, $data ) {

		if ( isset( $data['it-exchange-txn-activity'] ) && ! empty( $data['it-exchange-txn-activity']['txn'] ) ) {
			$txn = $data['it-exchange-txn-activity']['txn'];
		} else {
			$txn = false;
		}

		if ( $txn && current_user_can( 'edit_it_transaction', $txn ) ) {

			$latest      = $data['it-exchange-txn-activity']['latest'];
			$transaction = it_exchange_get_transaction( $data['it-exchange-txn-activity']['txn'] );

			if ( $latest ) {
				$args = array(
					'date_query' => array(
						'after' => get_date_from_gmt( $latest )
					)
				);
			} else {
				$args = array();
			}

			$collection = new IT_Exchange_Txn_Activity_Collection( $transaction, $args );
			$activity   = $collection->get_activity();

			if ( ! empty( $activity ) ) {
				foreach ( $activity as $item ) {

					if ( $item ) {
						$response['it-exchange-txn-activity']['items'][] = $item->to_array();
					}
				}
			}
		}

		return $response;
	}

	/**
	 * Remove Edit from 'Bulk Actions'
	 *
	 * @since 1.10.0
	 *
	 * @param  array $actions incoming options
	 *
	 * @return array
	 */
	public function edit_bulk_actions( $actions ) {

		if ( isset( $actions['edit'] ) ) {
			unset( $actions['edit'] );
		}

		return $actions;
	}
}

$IT_Exchange_Transaction_Post_Type = new IT_Exchange_Transaction_Post_Type();
