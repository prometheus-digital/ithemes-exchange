<?php
/**
 * This file manges the metaboxes for protecting an entire post_type object
 * @since 0.3.8
 * @package IT_Cart_Buddy
*/

add_action( 'add_meta_boxes', 'it_cart_buddy_protected_content_addon_add_protected_content_metabox' );
add_action( 'admin_init', 'it_cart_buddy_protected_content_addon_add_protected_content_metabox' ); // Pre WP 3.0
add_action( 'save_post', 'it_cart_buddy_protected_content_addon_save_post_restrictions' );
add_action( 'admin_print_scripts', 'it_cart_buddy_protected_content_addon_enqueue_admin_scripts' );
add_action( 'admin_print_scripts', 'it_cart_buddy_protected_content_addon_init_tinymce' );
add_action( 'wp_ajax_it_cart_buddy_protected_content_addon_get_protected_products', 'it_cart_buddy_protected_content_addon_print_product_checkboxes' );
add_action( 'admin_action_it-cart-buddy-protected-content-addon-doing-popup', 'it_cart_buddy_protected_content_addon_tinymce_popup_intercept' );

/**
 * Registers metabox for protected content
 *
 * @since 0.3.8
 * @return void
*/
function it_cart_buddy_protected_content_addon_add_protected_content_metabox() {
	// Grab all visible post_types
	if ( $visible_post_types = get_post_types( array( 'public' => true, 'show_in_nav_menus' => true ) ) ) {
		$visible_post_types = apply_filters( 'it_cart_buddy_protected_content_addon_metabox_post_types', $visible_post_types );

		// Add the metabox
		foreach( $visible_post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			$singular_name = empty( $post_type_object->labels->singular_name ) ? __( 'post' ) : strtolower( $post_type_object->labels->singular_name );
			add_meta_box( 'it_cart_buddy_protected_content_addon_metabox', __( sprintf( 'Limit acces to this %s', $singular_name ), 'LION' ), 'it_cart_buddy_protected_content_addon_print_metabox_content', $post_type, 'normal' );
		}
	}
}

/**
 * Prints the content of the metabox
 *
 * @since 0.3.8
 * @param mixed $post wp post object
 * @return void
*/
function it_cart_buddy_protected_content_addon_print_metabox_content( $post ) {
	$options = it_cart_buddy_protected_content_addon_get_protected_content_options_for_post_object( $post->ID );
	?>
	<div id="it_cart_buddy_protected_content_container">
		<div id="it_cart_buddy_protected_content_who_dialog">
			<p>
				Who is authorized to see this post? 
				<select class="it-pc-selector" name="it_cart_buddy_protected_content_is_protected" id="it_cart_buddy_protected_content_is_protected">
					<option value="no" <?php selected( $options['is_protected'], 'no' ); ?> data-dependant-classes="who-everyone">
						<?php _e( 'Everyone', 'LION' ); ?>
					</option>
					<option value="products" <?php selected( $options['is_protected'], 'products' ); ?> data-dependant-classes="who-products">
						<?php _e( 'Customers who have purchased specific products', 'LION' ); ?>
					</option>
					<option value="wp_roles" <?php selected( $options['is_protected'], 'wp_roles' ); ?> data-dependant-classes="who-wp_roles">
						<?php _e( 'Customers who have a specific WordPress role', 'LION' ); ?>
					</option>
				</select>
			</p>
			<div id="it_cart_buddy_protected_content_required_products" class="it-pc-group <?php echo $display['required_products']; ?> who-products">
				<div id="it_cart_buddy_protected_content_all_any_products">
					<p>
						<?php _e( 'Only customers that have purchased', 'LION' ); ?>
						<select name="it_cart_buddy_protected_content_all_any_products">
							<option value="all" <?php selected( 'all', $options['all_any_products'] ); ?>><?php _e( 'all', 'LION' ); ?></option>
							<option value="any" <?php selected( 'any', $options['all_any_products'] ); ?>><?php _e( 'any', 'LION' ); ?></option>
						</select> 
						<?php _e( 'of the following products can see this post:', 'LION' ); ?>
					</p>
				</div>
				<div id="it_cart_buddy_protected_content_what_products">
					<div id="it_cart_buddy_protected_content_what_product_tabs">
						<a class="it_cart_buddy_protected_content_select_products_link" href="" data-value="search"><?php _e( 'Search for products', 'LION' ); ?></a> | 
						<a class="it_cart_buddy_protected_content_select_products_link" href="" data-value="browse"><?php _e( 'Browse products', 'LION' ); ?></a>
					</div>
					<input type="hidden" id="it_cart_buddy_protected_content_select_products" data-dependant-classes="it_cart_buddy_protected_content_browse_products" class="it-pc-selector" />
					<div id="it_cart_buddy_protected_content_what_products_search_div" class="it-pc-group it_cart_buddy_protected_content_search_products">
						<input type="text" id="it_cart_buddy_protected_content_what_products_search_input" /> <input type="button" value="Add" />
					</div>
					<div id="it_cart_buddy_protected_content_what_products_browse_div" class="it-pc-group it_cart_buddy_protected_content_browse_products">
						<?php it_cart_buddy_protected_content_addon_print_product_checkboxes(); ?>
					</div>
					<div id="it_cart_buddy_protected_content_what_products_visual_feedback" class="it-pc-group">
						<div class="tagchecklist"><span><a id="post_tag-check-num-0" class="ntdelbutton">X</a>&nbsp;test</span></div>
					</div>
				</div>
			</div>
			<div id="it_cart_buddy_protected_content_what_wp_roles" class="it-pc-group who-wp_roles">
				<p>
					<?php _e( 'Only users with one of the following checked WordPress roles can see this post:', 'LION' ); ?><br />
					<?php
					foreach( (array) get_editable_roles() as $key => $values ) {
						?>
						<label for="it_cart_buddy_protected_content_wp_roles_<?php esc_attr_e( $key ); ?>">
							<input type="checkbox" id="it_cart_buddy_protected_content_wp_roles_<?php esc_attr_e( $key ); ?>" name="it_cart_buddy_protected_content_wp_roles[]" value="<?php esc_attr_e( $key ); ?>" <?php checked( in_array( $key, $options['wp_roles'] ) ); ?>/>&nbsp;<?php esc_attr_e( $values['name'] ); ?>
						</label><br />
						<?php
					}
					?>
				</p>
			</div>
		</div>
		<div id="it_cart_buddy_protected_content_when_dialog" class="it-pc-group who-products who-wp_roles">
			<p>
				<?php _e( 'When can authorized viewers see this post?', 'LION' ); ?>
				<select id="it_cart_buddy_protected_content_when_select" name="it_cart_buddy_protected_content_when_protected" class="it-pc-selector">
					<option value="never" <?php selected( 'never', $options['when_protected'] ); ?>>
						<?php _e( 'All the time', 'LION' ); ?>
					</option>
					<option value="time-period" data-dependant-classes="when-time_period" <?php selected( 'time-period', $options['when_protected'] ); ?>>
						<?php _e( 'During a specific time period', 'LION' ); ?>
					</option>
					<!-- NOT CURRENTLY AN OPTION. WILL EVENTUALLY DO DRIP CONTENT 
					<option value="duration-period" data-dependant-classes="when-duration_period" <?php selected( 'duration-period', $options['when_protected'] ); ?>>
						<?php _e( 'A specific time period after purchasing one of the above products', 'LION' ); ?>
					</option>
					-->
				</select>
			</p>
			<div id="it_cart_buddy_protected_content_when_time_period" class="it-pc-group when-time_period">
				<p>
					<label for="it_cart_buddy_protected_content_when_time_period_start">
						<input type="checkbox" name="it_cart_buddy_protected_content_when_time_period_start" class="it-pc-selector" id="it_cart_buddy_protected_content_when_time_period_start" data-dependant-classes="when-time_period_start" <?php checked( 'on', $options['when_time_period_start'] ); ?>/>&nbsp;<?php _e( 'Use a start date', 'LION' ); ?><br />
					</label>
					<label for="it_cart_buddy_protected_content_when_time_period_end">
						<input type="checkbox" name="it_cart_buddy_protected_content_when_time_period_end" class="it-pc-selector" id="it_cart_buddy_protected_content_when_time_period_end" data-dependant-classes="when-time_period_end" <?php checked( 'on', $options['when_time_period_end'] ); ?>/>&nbsp;<?php _e( 'Use an end date', 'LION' ); ?>
					</label>
				</p>
				<div>
					<span class="when-time_period_start it-pc-group"><?php _e( 'This content is only accessible after ', 'LION' ); ?>
						<input type="text" name="it_cart_buddy_protected_content_when_time_period_start_date" value="<?php esc_attr_e( $options['when_time_period_start_date'] ); ?>" />
						<br />
					</span>
					<span class="when-time_period_end it-pc-group"><?php _e( 'This content is only accessible before ', 'LION' ); ?>
					<input type="text" name="it_cart_buddy_protected_content_when_time_period_end_date" value="<?php esc_attr_e( $options['when_time_period_end_date'] ); ?>" />
					</span>
				</div>
			</div>
			<div id="it_cart_buddy_protected_content_when_duration_after_purchase" class="it-pc-group when-duration_period">
				<p>
					<label for="it_cart_buddy_protected_content_time_duration_start">	
						<input type="checkbox" class="it-pc-selector" name="it_cart_buddy_protected_content_when_duration_start" id="it_cart_buddy_protected_content_time_duration_start" data-dependant-classes="when-duration_period_start" <?php checked( 'on', $options['when_duration_start'] ); ?>>&nbsp;<?php _e( 'Use a delayed start', 'LION' ); ?>
					</label><br />
					<label for="it_cart_buddy_protected_content_time_duration_end">
						<input type="checkbox" class="it-pc-selector" name="it_cart_buddy_protected_content_when_duration_end" id="it_cart_buddy_protected_content_time_duration_end" data-dependant-classes="when-duration_period_end" <?php checked( 'on', $options['when_duration_end'] ); ?>>&nbsp;<?php _e( 'Use an expiration period', 'LION' ); ?>
					</label>
				</p>
				<p class="when-duration_period_start it-pc-group">
					<?php printf( __( 'This content is not available to the customer %suntil%s ', 'LION' ), '<strong>', '</strong>' ); ?>
					<input name="it_cart_buddy_protected_content_when_duration_start_quantity" value="<?php esc_attr_e( $options['when_duration_start_quantity'] ); ?>" type="text" size="4" /> 
					<select name="it_cart_buddy_protected_content_when_duration_start_units">
						<option value="days" <?php selected( 'days', $options['when_duration_start_units'] ); ?>><?php _e( 'day(s)' ); ?></option>
						<option value="weeks" <?php selected( 'weeks', $options['when_duration_start_units'] ); ?>><?php _e( 'week(s)' ); ?></option>
						<option value="months" <?php selected( 'months', $options['when_duration_start_units'] ); ?>><?php _e( 'month(s)' ); ?></option>
						<option value="years" <?php selected( 'years', $options['when_duration_start_units'] ); ?>><?php _e( 'year(s)' ); ?></option>
					</select>
					&nbsp;<?php _e( 'from their date of purchase.' ); ?>
				</p>
				<p class="when-duration_period_end it-pc-group">
					<?php printf( __( 'This content is not available to the customer %safter%s ', 'LION' ), '<strong>', '</strong>' ); ?>
					<input type="text" name="it_cart_buddy_protected_content_when_duration_end_quantity" size="4" value="<?php esc_attr_e( $options['when_duration_end_quantity'] ); ?>"/> 
					<select name="it_cart_buddy_protected_content_when_duration_end_units">
						<option value="days" <?php selected( 'days', $options['when_duration_end_units'] ); ?>><?php _e( 'day(s)' ); ?></option>
						<option value="weeks" <?php selected( 'weeks', $options['when_duration_end_units'] ); ?>><?php _e( 'week(s)' ); ?></option>
						<option value="months" <?php selected( 'months', $options['when_duration_end_units'] ); ?>><?php _e( 'month(s)' ); ?></option>
						<option value="years" <?php selected( 'years', $options['when_duration_end_units'] ); ?>><?php _e( 'year(s)' ); ?></option>
					</select>
					&nbsp;<?php _e( 'from their date of purchase.' ); ?>
				</p>
			</div>
		</div>
		<div id="it_cart_buddy_protected_content_unauthorized_views" class="it-pc-group who-products who-wp_roles">
			<div id="it_cart_buddy_protected_content_unauthorized_singular_views">
				<p>
					<?php _e( 'What should happen if an unauthorized user tries to access this page directly?', 'LION' ); ?><br />
					<label for="it_cart_buddy_protected_content_unauthorized_single_redirect">
						<input type="radio" name="it_cart_buddy_protected_content_unauthorized_singular_action" id="it_cart_buddy_protected_content_unauthorized_single_redirect" value="redirect" class="it-pc-selector" data-dependant-classes="unauthorized_single_redirect" <?php checked( 'redirect', $options['unauthorized_singular_action'] ); ?>>
						&nbsp;<?php _e( 'They get redirected to another URL', 'LION' ); ?>
					</label>
					<br />
					<label for="it_cart_buddy_protected_content_unauthorized_single_excerpt">
						<input type="radio" name="it_cart_buddy_protected_content_unauthorized_singular_action" id="it_cart_buddy_protected_content_unauthorized_single_excerpt" value="excerpt" class="it-pc-selector" <?php checked( 'excerpt', $options['unauthorized_singular_action'] ); ?>>
						&nbsp;<?php _e( 'They get shown the post excerpt', 'LION' ); ?>
					</label>
					<br />
					<label for="it_cart_buddy_protected_content_unauthorized_single_custom_message">
						<input type="radio" name="it_cart_buddy_protected_content_unauthorized_singular_action" id="it_cart_buddy_protected_content_unauthorized_single_custom_message" value="custom_message" class="it-pc-selector" data-dependant-classes="unauthorized_single_custom_message" <?php checked( 'custom_message', $options['unauthorized_singular_action'] ); ?>>
						&nbsp;<?php _e( 'They get shown a custom message', 'LION' ); ?>
					</label>
				</p>
				<p id="it_cart_buddy_protected_content_unauthorized_singular_redirect" class="it-pc-group unauthorized_single_redirect">
					<?php _e( 'URL to redirect them to: ', 'LION' ); ?><input type="text" name="it_cart_buddy_protected_content_unauthorized_singular_redirect_url" value="<?php esc_attr_e( $options['unauthorized_singular_redirect_url'] ); ?>" />
				</p>
				<p id="it_cart_buddy_protected_content_unauthroized_singular_redirect_custom_message" class="it-pc-group unauthorized_single_custom_message">
					<?php _e( 'Custom Message for singular views:', 'LION' ); ?><br />
					<textarea name="it_cart_buddy_protected_content_unauthorized_singular_custom_message"><?php esc_html_e( $options['unauthorized_singular_custom_message'] ); ?></textarea>
				</p>
			</div>
			<div id="it_cart_buddy_protected_content_unauthorized_archive_views">
				<p>
					<?php _e( 'What should happen when this post is supposed to appear in an archive or category view for unauthorized users?', 'LION' ); ?><br />
					<label for="it_cart_buddy_protected_content_unauthorized_archive_hide">
						<input type="radio" name="it_cart_buddy_protected_content_unauthorized_archive_action" id="it_cart_buddy_protected_content_unauthorized_archive_hide" value="hide" class="it-pc-selector" <?php checked( 'hide', $options['unauthorized_archive_action'] ); ?>>
						&nbsp;<?php _e( 'Don\'t show it in the archive', 'LION' ); ?>
					</label>
					<br />
					<label for="it_cart_buddy_protected_content_unauthorized_archive_excerpt">
						<input type="radio" name="it_cart_buddy_protected_content_unauthorized_archive_action" id="it_cart_buddy_protected_content_unauthorized_archive_excerpt" value="excerpt" class="it-pc-selector" <?php checked( 'excerpt', $options['unauthorized_archive_action'] ); ?>>
						&nbsp;<?php _e( 'Show the title without a link, followed by the excerpt where available', 'LION' ); ?>
					</label>
					<br />
					<label for="it_cart_buddy_protected_content_unauthorized_archive_custom_message_radio">
						<input type="radio" name="it_cart_buddy_protected_content_unauthorized_archive_action" id="it_cart_buddy_protected_content_unauthorized_archive_custom_message_radio" value="custom_message" class="it-pc-selector" data-dependant-classes="unauthorized_archive_custom_message" <?php checked( 'custom_message', $options['unauthorized_archive_action'] ); ?>>
						&nbsp;<?php _e( 'Show the title without a link and replace the excerpt / content with a custom message', 'LION' ); ?>
					</label>
				</p>
				<p id="it_cart_buddy_protected_content_unauthorized_archive_custom_message" class="unauthorized_archive_custom_message it-pc-group">
					<?php _e( 'Custom Message for archive views:', 'LION' ); ?><br />
					<textarea name="it_cart_buddy_protected_content_unauthorized_archive_custom_message"><?php esc_html_e( $options['unauthorized_archive_custom_message']     ); ?></textarea>
				</p>
			</div>
			<div id="it_cart_buddy_protected_content_unauthorized_search_views">
				<p>
					<?php _e( 'What should happen when this post is supposed to appear in search results for unauthorized users?', 'LION' ); ?><br />
					<label for="it_cart_buddy_protected_content_unauthorized_search_hide">
						<input type="radio" name="it_cart_buddy_protected_content_unauthorized_search_action" id="it_cart_buddy_protected_content_unauthorized_search_hide" value="hide" class="it-pc-selector" <?php checked( 'hide', $options['unauthorized_search_action'] ); ?>>
						&nbsp;<?php _e( 'Don\'t show it in the search results', 'LION' ); ?>
					</label>
					<br />
					<label for="it_cart_buddy_protected_content_unauthorized_search_excerpt">
						<input type="radio" name="it_cart_buddy_protected_content_unauthorized_search_action" id="it_cart_buddy_protected_content_unauthorized_search_excerpt" value="excerpt" class="it-pc-selector" <?php checked( 'excerpt', $options['unauthorized_search_action'] ); ?>>
						&nbsp;<?php _e( 'Show the title without a link, followed by the excerpt where available', 'LION' ); ?>
					</label>
					<br />
					<label for="it_cart_buddy_protected_content_unauthorized_search_custom_message">
						<input type="radio" name="it_cart_buddy_protected_content_unauthorized_search_action" id="it_cart_buddy_protected_content_unauthorized_search_custom_message" value="custom_message" class="it-pc-selector" data-dependant-classes="unauthorized_search_custom_message" <?php checked( 'custom_message', $options['unauthorized_search_action'] ); ?>>
						&nbsp;<?php _e( 'Show the title without a link and replace the excerpt / content with a custom message', 'LION' ); ?>
					</label>
				</p>
				<p id="it_cart_buddy_protected_content_unauthorized_search_custom_message" class="unauthorized_search_custom_message it-pc-group">
					<?php _e( 'Custom Message for search results:', 'LION' ); ?><br />
					<textarea name="it_cart_buddy_protected_content_unauthorized_search_custom_message"><?php esc_html_e( $options['unauthorized_search_custom_message']     ); ?></textarea>
				</p>
			</div>
			<div id="it_cart_buddy_protected_content_unauthorized_feed_views">
				<p>
					<?php _e( 'What should happen when this post is supposed to appear in feed view for unauthorized users?', 'LION' ); ?><br />
					<label for="it_cart_buddy_protected_content_unauthorized_feed_hide">
						<input type="radio" name="it_cart_buddy_protected_content_unauthorized_feed_action" id="it_cart_buddy_protected_content_unauthorized_feed_hide" value="hide" class="it-pc-selector" <?php checked( 'hide', $options['unauthorized_feed_action'] ); ?>>
						&nbsp;<?php _e( 'Don\'t show it in the feed views', 'LION' ); ?>
					</label>
					<br />
					<label for="it_cart_buddy_protected_content_unauthorized_feed_excerpt">
						<input type="radio" name="it_cart_buddy_protected_content_unauthorized_feed_action" id="it_cart_buddy_protected_content_unauthorized_feed_excerpt" value="excerpt" class="it-pc-selector" <?php checked( 'excerpt', $options['unauthorized_feed_action'] ); ?>>
						&nbsp;<?php _e( 'Show the title without a link, followed by the excerpt where available', 'LION' ); ?>
					</label>
					<br />
					<label for="it_cart_buddy_protected_content_unauthorized_feed_custom_message">
						<input type="radio" name="it_cart_buddy_protected_content_unauthorized_feed_action" id="it_cart_buddy_protected_content_unauthorized_feed_custom_message" value="custom_message" class="it-pc-selector" data-dependant-classes="unauthorized_feed_custom_message" <?php checked( 'custom_message', $options['unauthorized_feed_action'] ); ?>>
						&nbsp;<?php _e( 'Show the title without a link and replace the excerpt / content with a custom message', 'LION' ); ?>
					</label>
				</p>
				<p id="it_cart_buddy_protected_content_unauthorized_feed_custom_message" class="unauthorized_feed_custom_message it-pc-group">
					<?php _e( 'Custom Message for feed views:', 'LION' ); ?><br />
					<textarea name="it_cart_buddy_protected_content_unauthorized_feed_custom_message"><?php esc_html_e( $options['unauthorized_feed_custom_message']     ); ?></textarea>
				</p>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Prints a list of checkboxes for any products in a product type that supports protected content
 *
 * @since 0.3.8
 * @return void
*/
function it_cart_buddy_protected_content_addon_print_product_checkboxes() {
	// Get all product types
	$is_ajax            = false;
	$protected_products = (array) it_cart_buddy_get_option( 'protected_source_products' );
	
	if ( ! empty( $protected_products ) ) {
		global $post;
		$is_ajax = ( ! empty( $_GET['action'] ) && 'it_cart_buddy_protected_content_addon_get_protected_products' == $_GET['action'] ) ? true : false;

		// Grab products already selected
		$options = get_post_meta( $post->ID, '_it_cart_buddy_protected_content_options', true );
		$selected_products = empty( $options['selected_products'] ) ? false : (array) $options['selected_products'];
		if ( $is_ajax || false === $selected_products )
			$selected_products = array();

		// Loop through products and create checkboxes
		foreach( $protected_products as $product_id => $product_title ) { 
			?>  
			<label for="it_cart_buddy_protected_content_protect_product_<?php esc_attr_e( $product_id ); ?>">
			<input type="checkbox" id="it_cart_buddy_protected_content_protect_product_<?php esc_attr_e( $product_id ); ?>" name="it_cart_buddy_protected_content_selected_products[]" value="<?php esc_attr_e( $product_id ); ?>" <?php checked( in_array( $product_id, $selected_products ) ); ?>>&nbsp;<?php esc_attr_e( apply_filters( 'the_title', $product_title ) ); ?>
			</label><br />
			<?php
		}   
	} else {
		echo "No products found";
	}

	// Die if an ajax request
	if ( $is_ajax )
		die();
}

/**
 * Save post_meta if post restrictions are set
 *
 * @since 0.3.8
 * @return void
*/
function it_cart_buddy_protected_content_addon_save_post_restrictions( $post ) {
	if ( ! isset( $_POST['it_cart_buddy_protected_content_is_protected'] ) )
		return;

	$option_keys = array(
		'is_protected', 'all_any_products', 'when_protected', 'wp_roles', 'selected_products', 
		'when_time_period_start', 'when_time_period_end', 'when_time_period_start_date', 
		'when_time_period_end_date', 'when_duration_start', 'when_duration_end', 'when_duration_start_quantity', 'when_duration_start_units', 
		'when_duration_end_quantity', 'when_duration_end_units', 'unauthorized_singular_action', 
		'unauthorized_singular_redirect_url', 'unauthorized_singular_custom_message', 
		'unauthorized_archive_action', 'unauthorized_archive_custom_message', 'unauthorized_search_action', 
		'unauthorized_search_custom_message', 'unauthorized_feed_action', 'unauthorized_feed_custom_message',
	);

	$new_options = array();
	foreach( $option_keys as $key ) {
		if ( isset( $_POST['it_cart_buddy_protected_content_' . $key] ) )
			$new_options[$key] = $_POST['it_cart_buddy_protected_content_' . $key];
	}

	// Validate redirect URL
	if ( isset( $new_options['unauthorized_singular_redirect_url'] ) && ! esc_url( $new_options['unauthorized_singular_redirect_url'] ) )
		unset( $new_options['unauthorized_singular_redirect_url'] );

	update_post_meta( $post, '_it_cart_buddy_protected_content_options', $new_options ); 
}

/**
 * Get the protected content options for a specific post
 *
 * Returns false if not protected
 *
 * @since 0.3.8
 * @param integer $post_id the id of the wordpress post_type object
 * @return mixed false or an array
*/
function it_cart_buddy_protected_content_addon_get_protected_content_options_for_post_object( $post_id ) {
	if ( false === ( $options = get_post_meta( $post_id, '_it_cart_buddy_protected_content_options', true ) ) )
		$options = array();

	$defaults = array(
		'is_protected'                         => false,
		'all_any_products'                     => 'any',
		'required_products'                    => array(),
		'wp_roles'                             => array(),
		'when_protected'                       => 'never',
		'when_time_period_start'               => true,
		'when_time_period_end'                 => false,
		'when_time_period_start_date'          => date( 'Y-m-d' ),
		'when_time_period_end_date'            => '',
		'when_duration_start'                  => true,
		'when_duration_end'                    => false,
		'when_duration_start_quantity'         => 1,
		'when_duration_start_units'            => 'months',
		'when_duration_end_quantity'           => 1,
		'when_duration_end_units'              => 'months',
		'unauthorized_singular_action'         => 'redirect',
		'unauthorized_singular_redirect_url'   => get_home_url(),
		'unauthorized_singular_custom_message' => __( 'Premium Content', 'LION' ),
		'unauthorized_archive_action'          => 'hide',
		'unauthorized_archive_custom_message'  => __( 'Premium Content', 'LION' ),
		'unauthorized_search_action'           => 'hide',
		'unauthorized_search_custom_message'   => __( 'Premium Content', 'LION' ),
		'unauthorized_feed_action'             => 'hide',
		'unauthorized_feed_custom_message'     => __( 'Premium Content', 'LION' ),
	);

	return ITUtility::merge_defaults( $options, $defaults );
}

/**
 * Enqueue global admin styles
 *
 * @since 0.3.8
 * @uses wp_enqueue_style()
 * @return void 
*/
function it_cart_buddy_protected_content_addon_enqueue_admin_scripts() {
	global $current_screen;
	$addon_url = ITUtility::get_url_from_file( dirname( __FILE__ ) );
	if ( 'post' != $current_screen->base )
		return;
	wp_enqueue_script( 'it_cart_buddy_protected_content_addon-admin-js', $addon_url . '/js/admin.js', array( 'jquery' ) );
	wp_enqueue_script( 'ithemesNestedFormToggles', $addon_url . '/js/jquery.iThemesNestedFormToggles.js', array( 'jquery' ) );
}

/**
 * Listens for the tinymce call for a popup dialog and inserts it when triggered
 *
 * @since 0.3.8
 * @return void
*/
function it_cart_buddy_protected_content_addon_tinymce_popup_intercept() {
	include_once( dirname( __FILE__ ) . '/js/dialog.php' );
	die();
}

/**  
 * Initializes the Protected Content TinyMCE plugin
 *
 * @since 0.3.8
 * @return void
*/
function it_cart_buddy_protected_content_addon_init_tinymce () {
	global $current_screen;

	// Don't bother if we're not on a post type page
	if ( 'post' != $current_screen->base )
		return;

	// Don't init if user doesn't have correct permissions
	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) )
		return;

	// Add TinyMCE buttons when using rich editor
	if ( 'true' == get_user_option( 'rich_editing' ) ) {
		wp_localize_script( 'it_cart_buddy_protected_content_addon-admin-js', 'ITCartBuddyProtectedContentAddonDialog', array(
			'title' => __( 'Protect the Selected Content', 'LION' ),
			'desc'  => __( 'Highlight some text to restrict who sees it...', 'LION' ),
		));

		add_filter( 'mce_external_plugins', 'it_cart_buddy_protected_content_addon_register_mceplugin', 6 );
		add_filter( 'mce_buttons', 'it_cart_buddy_protected_content_addon_add_mcebutton', 6 );
	}
}

/**  
 * Adds the TinyMCE plugin to the list of loaded plugins
 *
 * @since 0.3.8
 * @param array $plugins The current list of plugins to load
 * @return array The updated list of plugins to laod
*/
function it_cart_buddy_protected_content_addon_register_mceplugin ( $plugins ) {
	$addon_url = ITUtility::get_url_from_file( dirname( __FILE__ ) );
	// Add a changing query string to keep the TinyMCE plugin from being cached & breaking TinyMCE in Safari/Chrome
	$plugins['ITCartBuddyProtectedContentAddon'] = $addon_url . '/js/tinymce.js?ver='.time();
	return $plugins;
}

/**
 * Adds the button to the TinyMCE editor
 *
 * @since 0.3.8
 * @param array $buttons The current list of buttons in the editor
 * @return array The updated list of buttons in the editor
*/
function it_cart_buddy_protected_content_addon_add_mcebutton ( $buttons ) {
	array_push( $buttons, '|', 'ITCartBuddyProtectedContentAddon' );
	return $buttons;
}
