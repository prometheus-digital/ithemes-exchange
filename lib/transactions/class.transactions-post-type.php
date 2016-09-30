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
			add_filter( 'manage_edit-it_exchange_tran_columns', array(
				$this,
				'modify_all_transactions_table_columns'
			) );
			add_filter( 'manage_edit-it_exchange_tran_sortable_columns', array(
				$this,
				'make_transaction_custom_columns_sortable'
			) );
			add_filter( 'manage_it_exchange_tran_posts_custom_column', array(
				$this,
				'add_transaction_method_info_to_view_all_table_rows'
			) );
			add_filter( 'request', array( $this, 'modify_wp_query_request_on_edit_php' ) );
			add_filter( 'it_exchange_transaction_metabox_callback', array(
				$this,
				'register_transaction_details_admin_metabox'
			) );
			add_filter( 'post_row_actions', array( $this, 'rename_edit_to_details' ), 10, 2 );
			add_filter( 'page_row_actions', array( $this, 'rename_edit_to_details' ), 10, 2 );
			add_filter( 'screen_layout_columns', array( $this, 'modify_details_page_layout' ) );
			add_filter( 'get_user_option_screen_layout_it_exchange_tran', array(
				$this,
				'update_user_column_options'
			) );
			add_filter( 'bulk_actions-edit-it_exchange_tran', array( $this, 'edit_bulk_actions' ) );
			add_action( 'wp_ajax_it-exchange-update-transaction-status', array( $this, 'ajax_update_status' ) );
			add_action( 'wp_ajax_it-exchange-add-note', array( $this, 'ajax_add_note' ) );
			add_action( 'wp_ajax_it-exchange-remove-activity', array( $this, 'ajax_remove_activity' ) );
			add_filter( 'heartbeat_received', array( $this, 'activity_heartbeat' ), 10, 2);
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
				'edit_posts'        => 'edit_posts',
				'create_posts'      => apply_filters( 'it_exchange_tran_create_posts_capabilities', 'do_not_allow' ),
				'edit_others_posts' => 'edit_others_posts',
				'publish_posts'     => 'publish_posts',
			),
			'map_meta_cap'         => true,
			'capability_type'      => 'post',
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

		// Remove Title - adding it back below
		if ( isset( $existing['title'] ) ) {
			unset( $existing['title'] );
		}

		// Remove Format
		if ( isset( $existing['format'] ) ) {
			unset( $existing['format'] );
		}

		// Remove Author
		if ( isset( $existing['author'] ) ) {
			unset( $existing['author'] );
		}

		// Remove Comments
		if ( isset( $existing['comments'] ) ) {
			unset( $existing['comments'] );
		}

		// Remove Date
		if ( isset( $existing['date'] ) ) {
			unset( $existing['date'] );
		}

		// Remove Builder
		if ( isset( $existing['builder_layout'] ) ) {
			unset( $existing['builder_layout'] );
		}


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
	 * @param integer $post   post ID
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
				echo esc_html( it_exchange_get_transaction_customer_display_name( $transaction ) );
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

		if ( 'edit.php' === $hook_suffix ) {
			if ( 'it_exchange_tran' === $request['post_type'] && isset( $request['orderby'] ) ) {

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

		$post = empty( $_GET['post'] ) ? false : get_post( $_GET['post'] );

		if ( $post && $post->post_type === 'it_exchange_tran' ) {
			it_exchange_get_transaction( $post );
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
	function print_transaction_details_metabox( $post ) {

		do_action( 'it_exchange_before_payment_details', $post );

		?>
		<div class="postbox" id="it-exchange-transaction-details">
		<div class="inside">
		<div class="transaction-stamp hidden <?php esc_attr_e( strtolower( it_exchange_get_transaction_status_label( $post ) ) ); ?>">
			<?php esc_attr_e( it_exchange_get_transaction_status_label( $post ) ); ?>
		</div>

		<?php if ( $post->post_parent ): ?>
			<div class="spacing-wrapper parent-txn-link bottom-border">
				<span class="dashicons dashicons-arrow-left-alt2"></span>
				<a href="<?php echo esc_url( get_edit_post_link( $post->post_parent ) ); ?>">
					<?php printf(
						__( 'View Parent Subscription Payment %s', 'it-l10n-ithemes-exchange' ),
						it_exchange_get_transaction_order_number( $post->post_parent )
					); ?>
				</a>
			</div>
		<?php endif; ?>

		<?php do_action( 'it_exchange_transaction_details_before_customer_data', $post ); ?>

		<div class="customer-data spacing-wrapper">
			<div class="customer-avatar left">
				<?php echo get_avatar( it_exchange_get_transaction_customer_id( $post->ID ), 80 ); ?>
			</div>
			<div class="transaction-data right">
				<div class="transaction-order-number">
					<?php esc_attr_e( it_exchange_get_transaction_order_number( $post ) ); ?>
				</div>
				<div class="transaction-date">
					<?php esc_attr_e( it_exchange_get_transaction_date( $post ) ); ?>
				</div>
				<div class="transaction-status <?php esc_attr_e( strtolower( it_exchange_get_transaction_status_label( $post ) ) ); ?>">
					<?php esc_attr_e( it_exchange_get_transaction_status_label( $post ) ); ?>
				</div>
			</div>
			<div class="customer-info">
				<h2 class="customer-display-name">
					<?php esc_attr_e( it_exchange_get_transaction_customer_display_name( $post ) ); ?>
				</h2>
				<div class="customer-email">
					<?php esc_attr_e( it_exchange_get_transaction_customer_email( $post ) ); ?>
				</div>

				<?php if ( ! $post->post_parent ) : ?>
					<div class="customer-ip-address">
						<?php esc_attr_e( it_exchange_get_transaction_customer_ip_address( $post ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( apply_filters( 'it_exchange_transaction_detail_has_customer_profile', true, $post ) ) : ?>
					<div class="customer-profile">
						<a href="<?php esc_attr_e( it_exchange_get_transaction_customer_admin_profile_url( $post ) ); ?>">
							<?php _e( 'View Customer Data', 'it-l10n-ithemes-exchange' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<?php do_action( 'it_exchange_transaction_details_after_customer_data', $post ); ?>
		<?php do_action( 'it_exchange_transaction_details_before_shipping_and_billing', $post ); ?>

		<?php
		$shipping_address = it_exchange_get_transaction_shipping_address( $post->ID );
		$shipping_address = array_filter( (array) $shipping_address ); // Make it false if all values are empty
		$shipping_address = it_exchange_transaction_includes_shipping( $post ) ? $shipping_address : false;
		$billing_address  = it_exchange_get_transaction_billing_address( $post->ID );
		$billing_address  = array_filter( (array) $billing_address ); // Make it false if all values are empty

		if ( $shipping_address || $billing_address ) : ?>
			<div class="billing-shipping-wrapper columns-wrapper">

				<?php if ( $shipping_address ) : ?>
					<div class="shipping-address column">
						<div class="column-inner">
							<div class="shipping-address-label address-label"><?php _e( 'Shipping Address', 'it-l10n-ithemes-exchange' ); ?></div>
							<p><?php echo it_exchange_get_formatted_shipping_address( $shipping_address ); ?></p>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $billing_address ) : ?>
					<div class="billing-address column">
						<div class="column-inner">
							<div class="billing-address-label address-label"><?php _e( 'Billing Address', 'it-l10n-ithemes-exchange' ); ?></div>
							<p><?php echo it_exchange_get_formatted_billing_address( $billing_address ); ?></p>
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php do_action( 'it_exchange_transaction_details_after_shipping_and_bililng', $post ); ?>
		<?php do_action( 'it_exchange_transaction_details_before_products', $post ); ?>

		<div class="products bottom-border">
			<div class="products-header spacing-wrapper bottom-border">
				<span><?php _e( 'Products', 'it-l10n-ithemes-exchange' ); ?></span>
				<span class="right"><?php _e( 'Amount', 'it-l10n-ithemes-exchange' ); ?></span>
			</div>
			<?php
			// Grab products attached to transaction
			$transaction_products = it_exchange_get_transaction_products( $post );

			if ( empty( $transaction_products ) && $post->post_parent ) {
				$transaction_products = it_exchange_get_transaction_products( $post->post_parent );
			}
			?>

			<?php foreach ( $transaction_products as $transaction_product ) : ?>
				<div class="product spacing-wrapper">
					<div class="product-header clearfix">
						<?php do_action( 'it_exchange_transaction_details_begin_product_header', $post, $transaction_product ); ?>
						<div class="product-title left">
							<?php do_action( 'it_exchange_transaction_print_metabox_before_product_feature_title', $post, $transaction_product ); ?>
							<?php echo it_exchange_get_transaction_product_feature( $transaction_product, 'title' ); ?> (<?php echo it_exchange_get_transaction_product_feature( $transaction_product, 'count' ); ?>)
							<?php do_action( 'it_exchange_transaction_print_metabox_after_product_feature_title', $post, $transaction_product ); ?>
						</div>
						<div class="product-subtotal right">
							<?php do_action( 'it_exchange_transaction_print_metabox_before_product_feature_subtotal', $post, $transaction_product ); ?>
							<?php esc_attr_e( it_exchange_format_price( it_exchange_get_transaction_product_feature( $transaction_product, 'product_subtotal' ) ) ); ?>
							<?php do_action( 'it_exchange_transaction_print_metabox_after_product_feature_subtotal', $post, $transaction_product ); ?>
						</div>
						<?php do_action( 'it_exchange_transaction_details_end_product_header', $post, $transaction_product ); ?>
					</div>
					<div class="product-details">
						<?php do_action( 'it_exchange_transaction_details_begin_product_details', $post, $transaction_product ); ?>

						<?php if ( it_exchange_transaction_includes_shipping( $post ) && it_exchange_product_has_feature( $transaction_product['product_id'], 'shipping' ) ) : ?>
							<div class="product-shipping-method">
								<?php printf( __( 'Ship this product with %s.', 'it-l10n-ithemes-exchange' ), it_exchange_get_transaction_shipping_method_for_product( $post, $transaction_product['product_cart_id'] ) ); ?>
							</div>
						<?php endif; ?>

						<?php if ( $product_downloads = it_exchange_get_product_feature( $transaction_product['product_id'], 'downloads' ) ) : ?>
							<?php foreach ( $product_downloads as $download_id => $download_data ) : ?>
								<div class="product-download product-download-<?php esc_attr_e( $download_id ); ?>">
									<h4 class="product-download-title">
										<?php do_action( 'it_exchange_transaction_print_metabox_before_product_feature_download_title', $post, $download_id, $download_data ); ?>
										<?php echo __( 'Download:', 'it-l10n-ithemes-exchange' ) . ' ' . get_the_title( $download_id ); ?>
										<?php do_action( 'it_exchange_transaction_print_metabox_after_product_feature_download_title', $post, $download_id, $download_data ); ?>
									</h4>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
						<?php do_action( 'it_exchange_transaction_details_end_product_details', $post, $transaction_product ); ?>
					</div>
					<?php do_action( 'it_exchange_transaction_details_end_product_container', $post, $transaction_product ); ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php do_action( 'it_exchange_transaction_details_after_products', $post ); ?>
		<?php do_action( 'it_exchange_transaction_details_before_costs', $post ); ?>

		<div class="transaction-costs clearfix spacing-wrapper bottom-border">

			<div class="transaction-costs-subtotal right clearfix">
				<div class="transaction-costs-subtotal-label left"><?php _e( 'Subtotal', 'it-l10n-ithemes-exchange' ); ?></div>
				<div class="transaction-costs-subtotal-price">
					<?php do_action( 'it_exchange_transaction_print_metabox_before_transaction_subtotal', $post ); ?>
					<?php esc_attr_e( it_exchange_get_transaction_subtotal( $post ) ); ?>
					<?php do_action( 'it_exchange_transaction_print_metabox_after_transaction_subtotal', $post ); ?>
				</div>
			</div>

			<?php if ( $coupons = it_exchange_get_transaction_coupons( $post ) ) : ?>
				<div class="transaction-costs-coupons right">
					<div class="transaction-costs-coupon-total-label left"><?php _e( 'Total Discount', 'it-l10n-ithemes-exchange' ); ?></div>
					<div class="transaction-costs-coupon-total-amount">
						<?php do_action( 'it_exchange_transaction_print_metabox_before_coupons_total_discount', $post ); ?>
						<?php esc_attr_e( it_exchange_get_transaction_coupons_total_discount( $post ) ); ?>
						<?php do_action( 'it_exchange_transaction_print_metabox_after_coupons_total_discount', $post ); ?>
					</div>
				</div>
				<label><strong><?php _e( 'Coupons', 'it-l10n-ithemes-exchange' ); ?></strong></label>
				<?php foreach ( $coupons as $type => $coupon ) : ?>
					<?php foreach ( $coupon as $data ) : ?>
						<div class="transaction-cost-coupon">
							<span class="code"><?php echo $data['code'] ?></span>
						</div>
					<?php endforeach; ?>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if ( $refunds = it_exchange_get_transaction_refunds( $post ) ) : ?>
				<div class="transaction-costs-refunds right">
					<div class="transaction-costs-refund-total">
						<div class="transaction-costs-refund-total-label left"><?php _e( 'Total Refund', 'it-l10n-ithemes-exchange' ); ?></div>
						<div class="transaction-costs-refund-total-amount">
							<?php do_action( 'it_exchange_transaction_print_metabox_before_transaction_refunds_total', $post ); ?>
							<?php esc_attr_e( it_exchange_get_transaction_refunds_total( $post ) ); ?>
							<?php do_action( 'it_exchange_transaction_print_metabox_after_transaction_refunds_total', $post ); ?>
						</div>
					</div>
				</div>
				<div class="transaction-refunds-list">
					<label><strong><?php _e( 'Refunds', 'it-l10n-ithemes-exchange' ); ?></strong></label>
					<?php foreach ( $refunds as $refund ) : ?>
						<div class="transaction-costs-refund">
							<span class="code"><?php echo esc_attr( it_exchange_format_price( $refund['amount'] ) ) . ' ' . __( 'on', 'it-l10n-ithemes-exchange' ) . ' ' . esc_attr( $refund['date'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( it_exchange_transaction_includes_shipping( $post ) ) : ?>
			<div class="transaction-shipping-summary clearfix spacing-wrapper bottom-border">
				<div class="payment-shipping left">
					<div class="payment-shipping-label"><?php _e( 'Shipping Method', 'it-l10n-ithemes-exchange' ); ?></div>
					<div class="payment-shipping-name">
						<?php do_action( 'it_exchange_transaction_print_metabox_before_transaction_shipping_name', $post ); ?>
						<?php esc_attr_e( empty( it_exchange_get_transaction_shipping_method( $post )->label ) ? __( 'Unknown Shipping Method', 'it-l10n-ithemes-exchange' ) : it_exchange_get_transaction_shipping_method( $post )->label ); ?>
						<?php do_action( 'it_exchange_transaction_print_metabox_after_transaction_shipping_name', $post ); ?>
					</div>
				</div>

				<div class="payment-shipping-total right clearfix">
					<div class="payment-shipping-total-label left"><?php _e( 'Shipping', 'it-l10n-ithemes-exchange' ); ?></div>
					<div class="payment-shipping-total-amount">
						<?php do_action( 'it_exchange_transaction_print_metabox_before_shipping_total', $post ); ?>
						<?php echo it_exchange_format_price( it_exchange_get_transaction_shipping_total( $post ) ); ?>
						<?php do_action( 'it_exchange_transaction_print_metabox_after_shipping_total', $post ); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="transaction-summary clearfix spacing-wrapper bottom-border">
			<div class="payment-method left">
				<div class="payment-method-label"><?php _e( 'Payment Method', 'it-l10n-ithemes-exchange' ); ?></div>
				<div class="payment-method-name">
					<?php do_action( 'it_exchange_transaction_print_metabox_before_transaction_method_name', $post ); ?>
					<?php esc_attr_e( it_exchange_get_transaction_method_name( $post ) ); ?>
					<code><?php echo it_exchange_get_transaction_method_id( $post ); ?></code>
					<?php do_action( 'it_exchange_transaction_print_metabox_after_transaction_method_name', $post ); ?>
				</div>
			</div>
			<div class="payment-total right clearfix">
				<div class="payment-total-label left"><?php _e( 'Total', 'it-l10n-ithemes-exchange' ); ?></div>
				<div class="payment-total-amount">
					<?php do_action( 'it_exchange_transaction_print_metabox_before_transaction_total', $post ); ?>
					<?php _e( it_exchange_get_transaction_total( $post ) ); ?>
					<?php do_action( 'it_exchange_transaction_print_metabox_after_transaction_total', $post ); ?>
				</div>

				<?php if ( $refunds = it_exchange_get_transaction_refunds( $post ) ) : ?>
					<div class="payment-original-total-label left"><?php _e( 'Total before refunds', 'it-l10n-ithemes-exchange' ); ?></div>
					<div class="payment-original-total-amount">
						<?php do_action( 'it_exchange_transaction_print_metabox_before_transaction_total_before_refunds', $post ); ?>
						<?php _e( it_exchange_get_transaction_total( $post, true, false ) ); ?>
						<?php do_action( 'it_exchange_transaction_print_metabox_after_transaction_total_before_refunds', $post ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( it_exchange_transaction_status_can_be_manually_changed( $post ) ) : ?>
			<div class="transaction-status-update clearfix spacing-wrapper hide-if-no-js bottom-border">
				<div class="update-status-label left">
					<label for="it-exchange-update-transaction-status">
						<?php _e( 'Change Status', 'it-l10n-ithemes-exchange' ); ?>
					</label>
					<span class="tip" title="<?php _e( 'The customer will receive an email When this is changed from a status that is not cleared for delivery to a status that is cleared for delivery', 'it-l10n-ithemes-exchange' ); ?>">i</span>
				</div>
				<div class="update-status-setting right">
					<select id='it-exchange-update-transaction-status'>
						<?php
						if ( $options = it_exchange_get_status_options_for_transaction( $post ) ) {
							$current_status = it_exchange_get_transaction_status( $post );
							foreach ( $options as $key => $label ) {
								$status_label = it_exchange_get_transaction_status_label( $post, array( 'status' => $key ) );
								?>
								<option value="<?php esc_attr_e( $key ); ?>" <?php selected( $key, $current_status ); ?>>
									<?php esc_attr_e( $status_label ); ?>
								</option>
								<?php
							}
						}
						?>
					</select>
					<?php wp_nonce_field( 'update-transaction-status' . $post->ID, 'it-exchange-update-transaction-nonce' ); ?>
					<input type="hidden" id="it-exchange-update-transaction-current-status" value="<?php esc_attr_e( $current_status ); ?>" />
					<input type="hidden" id="it-exchange-update-transaction-id" value="<?php esc_attr_e( $post->ID ); ?>" />
					<div id="it-exchange-update-transaction-status-failed"><?php _e( 'Not Saved.', 'it-l10n-ithemes-exchange' ); ?></div>
					<div id="it-exchange-update-transaction-status-success"><?php _e( 'Saved!', 'it-l10n-ithemes-exchange' ); ?></div>
				</div>
			</div>
		<?php endif; ?>

		<?php
		do_action( 'it_exchange_after_payment_details', $post );
		echo '</div></div>';

		$this->print_activity( $post );
	}

	/**
	 * Print the activity meta box.
	 *
	 * @since 1.34
	 *
	 * @param WP_Post $post
	 */
	public function print_activity( $post ) {

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

		// Fail if status is the same as old status
		if ( $current_status == $new_status ) {
			die( 'failed' );
		}

		// Fail if transaction isn't found
		if ( ! $transaction = it_exchange_get_transaction( $transaction_id ) ) {
			die( 'failed' );
		}

		// Attempt to change status
		if ( $current_status != it_exchange_update_transaction_status( $transaction, $new_status ) ) {
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

		if ( ! current_user_can( 'manage_options' ) ) {
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

		if ( ! current_user_can( 'manage_options' ) ) {
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

		if ( isset( $data['it-exchange-txn-activity'] ) && current_user_can( 'manage_options' ) ) {

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
