<?php
/**
 * Creates the post type for Products
 *
 * @package IT_Exchange
 * @since 0.3.0
*/

/**
 * Registers the it_exchange_prod post type
 *
 * @since 0.3.0
*/
class IT_Exchange_Product_Post_Type {
	
	/**
	 * Class Constructor
	 *
	 * @todo Filter some of these options. Not all.
	 * @since 0.3.0
	 * @return void
	*/
	function IT_Exchange_Product_Post_Type() {
		$this->init();
		
		add_action( 'init', array( $this, 'register_post_status' ) );
		add_action( 'save_post', array( $this, 'save_product' ) );
		add_action( 'admin_init', array( $this, 'set_add_new_item_label' ) );
		add_action( 'admin_init', array( $this, 'set_edit_item_label' ) );
		add_action( 'it_exchange_save_product_unvalidated', array( $this, 'set_initial_post_product_type' ) );
		add_action( 'admin_head-edit.php', array( $this, 'modify_post_new_file' ) );
		add_action( 'admin_head-post.php', array( $this, 'modify_post_new_file' ) );
		add_filter( 'manage_edit-it_exchange_prod_columns', array( $this, 'it_exchange_product_columns' ), 999 );
		add_filter( 'manage_edit-it_exchange_prod_sortable_columns', array( $this, 'it_exchange_product_sortable_columns' ) );
		add_filter( 'manage_it_exchange_prod_posts_custom_column', array( $this, 'it_exchange_prod_posts_custom_column_info' ) );
		add_action( 'it_exchange_add_on_enabled', array( $this, 'maybe_enable_product_type_posts' ) );
		add_action( 'it_exchange_add_on_disabled', array( $this, 'maybe_disable_product_type_posts' ) );
		add_filter( 'request', array( $this, 'modify_wp_query_request_on_edit_php' ) );		
	}
	
	/**
	 * Sets up the object
	 *
	 * @since 0.3.0
	 *
	 * @return void
	*/
	function init() {
		$this->post_type = 'it_exchange_prod';
		$labels    = array(
			'name'          => __( 'Products', 'LION' ),
			'singular_name' => __( 'Product', 'LION' ),
		);
		$this->options = array(
			'labels' => $labels,
			'description' => __( 'An iThemes Exchange Post Type for storing all Products in the system', 'LION' ),
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
			'rewrite' => array(
				'slug' => 'product',
			),
		);

		add_action( 'init', array( $this, 'set_rewrite_slug' ), 9 );
		add_action( 'init', array( $this, 'register_the_post_type' ) );
	}

	/**
	 * Set rewrite rules according to settings
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function set_rewrite_slug() {
		$pages = it_exchange_get_option( 'settings_pages', true );
		if ( ! empty( $pages['product-slug'] ) ) {
			$this->options['rewrite']['slug'] = $pages['product-slug'];
			$this->options['query_var'] = $pages['product-slug'];
		}
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
	 * @uses it_exchange_get_enabled_add_ons()
	 * @return void
	*/
	function meta_box_callback( $post ) {
		$product = it_exchange_get_product( $post );

		// Add action for current product type
		if ( $product_types = it_exchange_get_enabled_addons( array( 'category' => array( 'product-type' ) ) ) ) {
			foreach( $product_types as $addon_slug => $params ) {
				if ( $addon_slug == $product->product_type )
					do_action( 'it_exchange_product_metabox_callback_' . $addon_slug, $product );
			}
		}
		
		remove_meta_box( 'submitdiv', __( 'Publish' ), 'post_submit_meta_box', null, 'it_exchange_advanced', 'core' );
		add_meta_box( 'submitdiv', __( 'Publish' ), array( $this, 'post_submit_meta_box' ), 'it_exchange_prod', 'it_exchange_side', 'high' );

		// Do action for any product type
		do_action( 'it_exchange_product_metabox_callback', $product );
	}
	
	function post_submit_meta_box( $post ) {
			
		global $action;
	
		$post_type = $post->post_type;
		$post_type_object = get_post_type_object($post_type);
		$can_publish = current_user_can($post_type_object->cap->publish_posts);
		
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the visibility for this product
		$product_visibility = get_post_meta( $post->ID, '_it-exchange-visibility', true );
		
		?>
        <div id="it-exchange-submit-box">
			<?php do_action('post_submitbox_start'); ?>
			<div style="display:none;">
				<?php submit_button( __( 'Save' ), 'button', 'save' ); ?>
			</div>
			<div class="publishing-actions">
				<div id="save-action">
					<?php if ( 'publish' != $post->post_status && 'future' != $post->post_status && 'pending' != $post->post_status ) : ?>
						<input <?php if ( 'private' == $post->post_status ) { ?>style="display:none"<?php } ?> type="submit" name="save" id="save-post" value="<?php esc_attr_e( 'Save Draft' ); ?>" class="button button-large" />
					<?php elseif ( 'pending' == $post->post_status && $can_publish ) : ?>
						<input type="submit" name="save" id="save-post" value="<?php esc_attr_e('Save as Pending'); ?>" class="button button-large" />
					<?php endif; ?>
					<span class="spinner"></span>
				</div>
				<?php if ( $post_type_object->public ) : ?>
					<div id="preview-action">
						<?php
							if ( 'publish' == $post->post_status ) {
								$preview_link = esc_url( get_permalink( $post->ID ) );
								$preview_button = __( 'Preview Changes' );
							} else {
								$preview_link = set_url_scheme( get_permalink( $post->ID ) );
								$preview_link = esc_url( apply_filters( 'preview_post_link', add_query_arg( 'preview', 'true', $preview_link ) ) );
								$preview_button = __( 'Preview' );
							}
						?>
						<a class="preview button button-large" href="<?php echo $preview_link; ?>" target="wp-preview" id="post-preview"><?php echo $preview_button; ?></a>
						<input type="hidden" name="wp-preview" id="wp-preview" value="" />
					</div>
				<?php endif; ?>
				<div id="publishing-action">
					<span class="spinner"></span>
					<?php if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ) ) || 0 == $post->ID ) : ?>
						<?php if ( $can_publish ) : ?>
							<?php if ( !empty($post->post_date_gmt) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) : ?>
								<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Schedule' ) ?>" />
								<?php submit_button( __( 'Schedule' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
							<?php else : ?>
								<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
								<?php submit_button( __( 'Publish' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
							<?php endif; ?>
						<?php else : ?>
							<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Submit for Review') ?>" />
							<?php submit_button( __( 'Submit for Review' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
						<?php endif; ?>
					<?php else : ?>
						<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update' ) ?>" />
						<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Update' ) ?>" />
					<?php endif; ?>
				</div>
			</div>
			<div class="modifying-actions">
				<div id="advanced-action">
					<a class="advanced-status-option-link advanced-hidden" href data-hidden="<?php _e( 'Show Advanced', 'LION' ); ?>" data-visible="<?php _e( 'Hide Advanced', 'LION' ); ?>"><?php _e( 'Show Advanced', 'LION' ); ?></a>
				</div>
				<div id="delete-action">
					<?php if ( current_user_can( "delete_post", $post->ID ) ) : ?>
						<?php
							if ( ! EMPTY_TRASH_DAYS )
								$delete_text = __( 'Delete Permanently' );
							else
								$delete_text = __('Move to Trash');
						?>
						<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php echo $delete_text; ?></a>
					<?php endif; ?>
				</div>
			</div>
			<div class="advanced-actions hide-if-js">
				<div id="misc-publishing-actions">
					<div class="misc-pub-section">
						<label for="post_status"><?php _e( 'Status:' ) ?></label>
						<span id="post-status-display">
							<?php
								switch ( $post->post_status ) {
									case 'private':
										_e('Privately Published');
										break;
									case 'publish':
										_e('Published');
										break;
									case 'future':
										_e('Scheduled');
										break;
									case 'pending':
										_e('Pending Review');
										break;
									case 'draft':
									case 'auto-draft':
										_e('Draft');
										break;
								}
							?>
						</span>
						<?php if ( 'publish' == $post->post_status || 'private' == $post->post_status || $can_publish ) : ?>
							<a href="#post_status" <?php if ( 'private' == $post->post_status ) { ?>style="display:none;" <?php } ?>class="edit-post-status hide-if-no-js"><?php _e( 'Edit', 'LION' ) ?></a>
							<div id="post-status-select" class="hide-if-js">
								<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr( ('auto-draft' == $post->post_status ) ? 'draft' : $post->post_status); ?>" />
								<select name='post_status' id='post_status'>
									<?php if ( 'publish' == $post->post_status ) : ?>
										<option<?php selected( $post->post_status, 'publish' ); ?> value='publish'><?php _e( 'Published', 'LION' ); ?></option>
									<?php endif; ?>
										<option<?php selected( $post->post_status, 'pending' ); ?> value='pending'><?php _e( 'Pending Review', 'LION' ); ?></option>
									<?php if ( 'auto-draft' == $post->post_status ) : ?>
										<option<?php selected( $post->post_status, 'auto-draft' ); ?> value='draft'><?php _e( 'Draft', 'LION' ); ?></option>
									<?php else : ?>
										<option<?php selected( $post->post_status, 'draft' ); ?> value='draft'><?php _e('Draft') ?></option>
									<?php endif; ?>
								</select>
								 <a href="#post_status" class="save-post-status hide-if-no-js button"><?php _e('OK'); ?></a>
								 <a href="#post_status" class="cancel-post-status hide-if-no-js"><?php _e('Cancel'); ?></a>
							 </div>
						<?php endif; ?>
					</div>
					
					<div class="misc-pub-section">
						<label for="visibility"><?php _e( 'Visibility:', 'LION' ) ?></label>
						<span id="product-visibility-display">
							<?php
								switch ( $product_visibility ) {
									case 'hidden':
										_e( 'Hide from Store', 'LION' );
										break;
									case 'visible':
									default:
										_e( 'Show in Store', 'LION' );
										break;
								}
							?>
						</span>
						<?php if ( 'visible' == $product_visibility || 'hidden' == $product_visibility || $can_publish ) : ?>
							<a href="#product_visibility" class="edit-product-visibility hide-if-no-js"><?php _e('Edit') ?></a>
							<div id="product-visibility-select" class="hide-if-js">
								<input type="hidden" name="hidden_it-exchange-visibility" id="hidden_it-exchange-visibility" value="<?php echo esc_attr( ('hidden' == $post->post_status ) ? 'hidden' : $product_visibility); ?>" />
								<select name='it-exchange-visibility' id='it-exchange-visibility'>
										<option<?php selected( $product_visibility, 'visible' ); ?> value='visible'><?php _e( 'Show in Store', 'LION' ) ?></option>
										<option<?php selected( $product_visibility, 'hidden' ); ?> value='hidden'><?php _e( 'Hide from Store', 'LION' ) ?></option>
								</select>
								<a href="#product_visibility" class="save-product_visibility hide-if-no-js button"><?php _e('OK'); ?></a>
								<a href="#product_visibility" class="cancel-product_visibility hide-if-no-js"><?php _e('Cancel'); ?></a>
							</div>
						<?php endif; ?>
					</div>
					
					<?php
						if ( 'private' == $post->post_status ) {
							$post->post_password = '';
							$visibility = 'private';
							$visibility_trans = __( 'Private' );
						} elseif ( !empty( $post->post_password ) ) {
							$visibility = 'password';
							$visibility_trans = __('Password protected');
						} elseif ( $post_type == 'post' && is_sticky( $post->ID ) ) {
							$visibility = 'public';
							$visibility_trans = __('Public, Sticky');
						} else {
							$visibility = 'public';
							$visibility_trans = __('Public');
						}
					?>
					<input type="hidden" name="hidden_post_visibility" id="hidden-post-visibility" value="<?php echo esc_attr( $visibility ); ?>" />
					
					<?php do_action('post_submitbox_misc_actions'); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Generates the Add New Product Label for a new Product 
	 *
	 * @since 0.3.0
	 * @return string $label Label for add new product page.
	*/
	function set_add_new_item_label() {
		global $pagenow, $wp_post_types;
		if ( $pagenow != 'post-new.php' || empty( $_GET['post_type'] ) || 'it_exchange_prod' != $_GET['post_type'] )
			return apply_filters( 'it_exchange_add_new_product_label', __( 'Add New Product', 'LION' ) );

		if ( empty( $wp_post_types['it_exchange_prod'] ) )
			return;
			
		$product_add_ons = it_exchange_get_enabled_addons( array( 'category' => array( 'product-type' ) ) );
		$product = array();

		// Isolate the product type
		if ( 1 == count( $product_add_ons ) ) {
			$product = reset( $product_add_ons );
		} else {
			$product_type = it_exchange_get_product_type();
			if ( ! empty( $product_type ) && ! empty( $product_add_ons[$product_type] ) )
				$product = $product_add_ons[$product_type];
			else
				$product['options']['labels']['singular_name'] = 'Product';

		}
		$singular = empty( $product['options']['labels']['singular_name'] ) ? $product['name'] : $product['options']['labels']['singular_name'];
		$label = apply_filters( 'it_exchange_add_new_product_label_' . $product['slug'], __( 'Add New ', 'LION' ) . $singular );
		$wp_post_types['it_exchange_prod']->labels->add_new_item = $label;
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

		if ( empty( $wp_post_types['it_exchange_prod'] ) )
			return;
			
		if ( 'it_exchange_prod' != get_post_type( $post ) )
			return;

		$product_type = it_exchange_get_product_type( $post );

		$product_add_ons = it_exchange_get_enabled_addons( array( 'category' => array( 'product-type' ) ) );
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
		$label = apply_filters( 'it_exchange_edit_product_label_' . $product['slug'], __( 'Edit ', 'LION' ) . $singular );
		$wp_post_types['it_exchange_prod']->labels->edit_item = $label;
	}

	/**
	 * Provides specific hooks for when iThemes Exchange products are saved.
	 *
	 * This method is hooked to save_post. It provides hooks for add-on developers
	 * that will only be called when the post being saved is an iThemes Exchange product. 
	 * It provides the following 4 hooks:
	 * - it_exchange_save_product_unvalidated                // Runs every time an iThemes Exchange product is saved.
	 * - it_exchange_save_product_unavalidate-[product-type] // Runs every time a specific iThemes Exchange product type is saved.
	 * - it_exchange_save_product                            // Runs every time an iThemes Exchange product is saved if not an autosave and if user has permission to save post
	 * - it_exchange_save_product-[product-type]             // Runs every time a specific iThemes Exchange product-type is saved if not an autosave and if user has permission to save post
	 *
	 * @since 0.3.1
	 * @param int $post_id WordPress Post ID
	 * @return void
	*/
	function save_product( $post_id ) { 

		// Exit if not it_exchange_prod post_type
		if ( ! 'it_exchange_prod' === get_post_type( $post_id ) ) 
			return; 

		// Fire off actions with validations that most instances need to use.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( ! current_user_can( 'edit_post', $post_id ) ) 
			return;  
				
		if ( isset( $_POST['it-exchange-visibility'] ) )
			update_post_meta( $post_id, '_it-exchange-visibility', $_POST['it-exchange-visibility'] );

		// Grab enabled product add-ons
		$product_type_addons = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		
		// Grab current post's product_type
		$product_type = it_exchange_get_product_type();

		// These hooks fire off any time a it_exchange_prod post is saved w/o validations
		do_action( 'it_exchange_save_product_unvalidated', $post_id );
		foreach( (array) $product_type_addons as $slug => $params ) { 
			if ( $slug == $product_type ) { 
				do_action( 'it_exchange_save_product_unvalidated_' . $slug, $post_id );
			}   
		}  
		
		// This is called any time save_post hook
		do_action( 'it_exchange_save_product', $post_id );
		foreach( (array) $product_type_addons as $slug => $params ) { 
			if ( $slug == $product_type ) { 
				do_action( 'it_exchange_save_product_' . $slug, $post_id );
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
		if ( $product = it_exchange_get_product( $post ) ) {
			if ( ! empty( $product->product_type ) && ! get_post_meta( $product->ID, '_it_exchange_product_type', true ) )
				update_post_meta( $product->ID, '_it_exchange_product_type', $product->product_type );
		}
	}

	/**
	 * Register Hidden Disabled Product Post status
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function register_post_status() {
		$args = array(
			'label'                     => _x( '_it_exchange_disab', 'Status General Name', 'LION' ),
			'label_count'               => _n_noop( 'Disabled Product (%s)',  'Disabled Products (%s)', 'LION' ),
			'public'                    => false,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => false,
			'exclude_from_search'       => true,
		);
		register_post_status( '_it_exchange_disab', $args );
	}

	
	/**
	 * Modifies the value of $post_new_file to change the link attached to the Add New button next to the H2 on all / edit products
	 *
	 * I'm not proud of this. Don't copy it. ^gta
	 *
	 * @since 0.3.10
	 * @return void
	*/
	function modify_post_new_file() {
		
		global $current_screen, $post_new_file;

		if ( 'edit-it_exchange_prod' == $current_screen->id || 'it_exchange_prod' == $current_screen->id ) {
			
			$product_type = it_exchange_get_product_type();
			
			// Hackery. The 'Add New button in the H2 isn't going to work if we have multiple product types
			$product_addons = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
			$hide_it = '<style type="text/css">.add-new-h2 { display:none; }</style>';
			if ( empty( $product_type ) && ( count( $product_addons ) > 1  ) ) {
				echo $hide_it;
			} else if ( empty( $product_type ) && ! it_exchange_get_products() ) {
				// If we made it here, we only have one product type, but there are no products. Won't happen that often.
				$product_addon = reset( array_keys( $product_addons ) );
				$product_type = empty( $product_addon ) ? false : $product_addon;
			}
	
			if ( ! empty( $post_new_file) && ! empty( $product_type ) )
				$post_new_file = add_query_arg( array( 'it-exchange-product-type' => $product_type ), $post_new_file );
			
		}
		
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
	 * 1 - Find all product posts for this product type with a post_status of _it_exchange_disab
	 * 2 - Foreach product, pass to enable_product_post() method
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function enable_product_type_posts( $product_type ) {

		// Grab all products for this product-type
		$args = array(
			'post_status'  => '_it_exchange_disab',
			'product_type' => $product_type,
			'number_posts' => -1,
		);
		if ( $products = it_exchange_get_products( $args ) ) {
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
		if ( $previous_status = get_post_meta( $post->ID, '_it_exchange_enabled_status', true ) ) {
			delete_post_meta( $post->ID, '_it_exchange_enabled_status' );
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
		if ( isset( $post_stati['_it_exchange_disab'] ) )
			unset( $post_stati['_it_exchange_disab'] );

		// Grab all products for this product-type
		$args = array(
			'post_status'  => array_keys( $post_stati ),
			'product_type' => $product_type,
			'number_posts' => -1,
		);
		if ( $products = it_exchange_get_products( $args ) ) {
			foreach( $products as $product ) {
				$this->disable_product_post( $product );
			}
		}
	}

	/**
	 * Disable a single product type by changing post_status to _it_exchange_disab.
	 *
	 * Changing the post_status will prevent it from showing in WP queries
	 * 1 - Save current post_status to post_meta: _it_exchange_enabled_status
	 * 2 - Change post status to _it_exchange_disab
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function disable_product_post( $post ) {
		update_post_meta( $post->ID, '_it_exchange_enabled_status', $post->post_status );
		$args = array( 'ID' => $post->ID, 'post_status' => '_it_exchange_disab' );
		wp_update_post( $args );
	}

	/**
	 * Adds the product type column to the View All products table
	 *
	 * @since 0.3.3
	 * @param array $existing  exisiting columns array
	 * @return array  modified columns array
	*/
	function it_exchange_product_columns( $existing ) {
		$columns['cb']                                = '<input type="checkbox" />';
		$columns['title']                             = __( 'Title', 'LION' );
		$columns['it_exchange_product_price']         = __( 'Price', 'LION' );
		$columns['it_exchange_product_show_in_store'] = __( 'Show in Store', 'LION' );
		$columns['it_exchange_product_purchases']     = __( 'Purchases', 'LION' );

		return $columns;
	}

	/**
	 * Makes the product_type column added above sortable
	 *
	 * @since 0.3.3
	 * @param array $sortables  existing sortable columns
	 * @return array  modified sortable columnns
	*/
	function it_exchange_product_sortable_columns( $sortables ) {
		$sortables['it_exchange_product_price']         = 'it-exchange-product-price';
		$sortables['it_exchange_product_show_in_store'] = 'it-exchange-product-show-in-store';
		$sortables['it_exchange_product_purchases']     = 'it-exchange-product-purchases';
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
	function it_exchange_prod_posts_custom_column_info( $column ) {
		global $post;
		$product = it_exchange_get_product( $post );
		
		switch( $column ) {
			case 'it_exchange_product_price':
				esc_attr_e( it_exchange_format_price( it_exchange_get_product_feature( $post->ID, 'base-price' ) ) );
				break;
			case 'it_exchange_product_show_in_store':
				$product_visibility = get_post_meta( $post->ID, '_it-exchange-visibility', true );
				echo ucwords( $product_visibility );
				break;
			case 'it_exchange_product_purchases':
				esc_attr_e( it_exchange_get_product_feature( $post->ID, 'purchases' ) );
				break;
		}
	}

	/**
	 * Modify sort of products in edit.php for custom columns
	 *
	 * @since 0.4.0
	 *
	 * @param string $request original request
	 */
	function modify_wp_query_request_on_edit_php( $request ) {
		global $hook_suffix;
		
		if ( 'edit.php' === $hook_suffix ) {
			if ( 'it_exchange_prod' === $request['post_type'] && isset( $request['orderby'] ) ) {
				switch( $request['orderby'] ) {
					case 'it-exchange-product-price':
						$request['orderby'] = 'meta_value_num';
						$request['meta_key'] = '_it-exchange-base-price';
						break;
					case 'it-exchange-product-show-in-store':
						$request['orderby'] = 'meta_value';
						$request['meta_key'] = '_it-exchange-visibility';
						break;
				}
			}
		}
		
		return $request;
	}
}
$IT_Exchange_Product_Post_Type = new IT_Exchange_Product_Post_Type();
