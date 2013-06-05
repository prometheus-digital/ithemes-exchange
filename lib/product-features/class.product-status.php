<?php
/**
 * This will associate a visiblity with all product types.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 0.4.0
 * @package IT_Exchange
*/


class IT_Exchange_Product_Visibility {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 0.4.0
	 * @return void
	 * @todo remove it_exchange_enabled_addons_loaded action???
	*/
	function IT_Exchange_Product_Visibility() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_update_product_feature_visibility', array( $this, 'save_feature' ), 9, 2 );
		add_filter( 'it_exchange_get_product_feature_visibility', array( $this, 'get_feature' ), 9, 2 );
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_filter( 'it_exchange_product_has_feature_visibility', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_visibility', array( $this, 'product_supports_feature') , 9, 2 );
	}

	/**
	 * Register the product and add it to enabled product-type addons
	 *
	 * @since 0.4.0
	*/
	function add_feature_support_to_product_types() {
		// Register the visibility_addon
		$slug        = 'visibility';
		$description = 'The visibility of a product';
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) {
			it_exchange_add_feature_support_to_product_type( 'visibility', $params['slug'] );
		}
	}

	/**
	 * Register's the metabox for any product type that supports the visibility feature
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function init_feature_metaboxes() {
		
		global $post;
		
		if ( isset( $_REQUEST['post_type'] ) ) {
			$post_type = $_REQUEST['post_type'];
		} else {
			if ( isset( $_REQUEST['post'] ) )
				$post_id = (int) $_REQUEST['post'];
			elseif ( isset( $_REQUEST['post_ID'] ) )
				$post_id = (int) $_REQUEST['post_ID'];
			else
				$post_id = 0;

			if ( $post_id )
				$post = get_post( $post_id );

			if ( isset( $post ) && !empty( $post ) )
				$post_type = $post->post_type;
		}
			
		if ( !empty( $_REQUEST['it-exchange-product-type'] ) )
			$product_type = $_REQUEST['it-exchange-product-type'];
		else
			$product_type = it_exchange_get_product_type( $post );
		
		if ( !empty( $post_type ) && 'it_exchange_prod' === $post_type ) {
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'visibility' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array( $this, 'register_metabox' ) );
		}
		
	}

	/**
	 * Registers the visibility metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports visibility
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function register_metabox() {
		remove_meta_box( 'submitdiv', __( 'Publish' ), 'post_submit_meta_box', null, 'it_exchange_advanced', 'core' );
		add_meta_box( 'submitdiv', __( 'Publish' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_side', 'high' );
	}

	/**
	 * This echos the Visibility metabox.
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_metabox( $post ) {
		
		//return post_submit_meta_box( $post );
			
		global $action;
	
		$post_type = $post->post_type;
		$post_type_object = get_post_type_object($post_type);
		$can_publish = current_user_can($post_type_object->cap->publish_posts);
		
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the visibility for this product
		$product_visibility = it_exchange_get_product_feature( $product->ID, 'visibility' );

		// Set description
		$description = __( 'Visibility', 'LION' );
		$description = apply_filters( 'it_exchange_visibility_addon_metabox_description', $description );
		
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
						<label for="visibility"><?php _e('Visibility:','LION') ?></label>
						<span id="product-visibility-display">
							<?php
								switch ( $product_visibility ) {
									case 'visible':
									default:
										_e('Show in Store', 'LION');
										break;
									case 'hidden':
										_e('Hide from Store','LION');
										break;
								}
							?>
						</span>
						<?php if ( 'visible' == $product_visibility || 'private' == $post->post_status || $can_publish ) : ?>
							<a href="#product_visibility" class="edit-product-visibility hide-if-no-js"><?php _e('Edit') ?></a>
							<div id="product-visibility-select" class="hide-if-js">
								<input type="hidden" name="hidden_it-exchange-visibility" id="hidden_it-exchange-visibility" value="<?php echo esc_attr( ('hidden' == $post->post_status ) ? 'hidden' : $product_visibility); ?>" />
								<select name='it-exchange-visibility' id='it-exchange-visibility'>
										<option<?php selected( $product_visibility, 'visible' ); ?> value='visible'><?php _e('Show in Store','LION') ?></option>
										<option<?php selected( $product_visibility, 'hidden' ); ?> value='hidden'><?php _e('Hide from Store') ?></option>
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
	 * This saves the Visibility value
	 *
	 * @todo Convert to use product feature API
	 *
	 * @since 0.4.0
	 * @param object $post wp post object
	 * @return void
	*/
	function save_feature_on_product_save() {
		// Abort if we can't determine a product type
		if ( ! $product_type = it_exchange_get_product_type() )
			return;

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		if ( ! $product_id )
			return;

		// Abort if this product type doesn't support visibility
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'visibility' ) )
			return;

		// Abort if key for visibility option isn't set in POST data
		if ( ! isset( $_POST['it-exchange-visibility'] ) )
			return;

		// Get new value from post
		$new_visibility = $_POST['it-exchange-visibility'];
		
		// Save new value
		it_exchange_update_product_feature( $product_id, 'visibility', $new_visibility );
	}

	/**
	 * This updates the Visibility for a product
	 *
	 * @todo Validate product id and new visibilty
	 *
	 * @since 0.4.0
	 * @param integer $product_id the product id
	 * @param mixed $new_visibility the new visibility
	 * @return bolean
	*/
	function save_feature( $product_id, $new_visibility ) {
		update_post_meta( $product_id, '_it-exchange-visibility', $new_visibility );
	}

	/**
	 * Return the product's Visibility
	 *
	 * @since 0.4.0
	 * @param mixed $visibility the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string visibility
	*/
	function get_feature( $visibility, $product_id ) {
		$visibility = get_post_meta( $product_id, '_it-exchange-visibility', true );
		return $visibility;
	}

	/**
	 * Does the product have a Visibility?
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_has_feature( $result, $product_id ) {
		// Does this product type support this feature?
		if ( false === $this->product_supports_feature( false, $product_id ) )
			return false;
		return (boolean) $this->get_feature( false, $product_id );
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can 
	 * support a feature but might not have the feature set.
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_supports_feature( $result, $product_id ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		return it_exchange_product_type_supports_feature( $product_type, 'visibility' );
	}
}
$IT_Exchange_Product_Visibility = new IT_Exchange_Product_Visibility();
