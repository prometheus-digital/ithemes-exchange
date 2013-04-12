<?php
/**
 * This file manges the metaboxes for protecting an entire post_type object
 * @since 0.3.8
 * @package IT_Exchange
*/

add_action( 'add_meta_boxes', 'it_exchange_protected_content_addon_add_protected_content_metabox' );
add_action( 'admin_init', 'it_exchange_protected_content_addon_add_protected_content_metabox' ); // Pre WP 3.0
add_action( 'save_post', 'it_exchange_protected_content_addon_save_post_restrictions' );
add_action( 'admin_print_scripts', 'it_exchange_protected_content_addon_enqueue_admin_scripts' );
add_action( 'admin_print_scripts', 'it_exchange_protected_content_addon_init_tinymce' );
add_action( 'wp_ajax_it_exchange_protected_content_addon_get_protected_products', 'it_exchange_protected_content_addon_print_product_checkboxes' );
add_action( 'admin_action_it_exchange_protected_content_addon_doing_popup', 'it_exchange_protected_content_addon_tinymce_popup_intercept' );

/**
 * Registers metabox for protected content
 *
 * @since 0.3.8
 * @return void
*/
function it_exchange_protected_content_addon_add_protected_content_metabox() {
	// Grab all visible post_types
	if ( $visible_post_types = get_post_types( array( 'public' => true, 'show_in_nav_menus' => true ) ) ) {
		$visible_post_types = apply_filters( 'it_exchange_protected_content_addon_metabox_post_types', $visible_post_types );

		// Add the metabox
		foreach( $visible_post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			$singular_name = empty( $post_type_object->labels->singular_name ) ? __( 'post' ) : strtolower( $post_type_object->labels->singular_name );
			add_meta_box( 'it_exchange_protected_content_addon_metabox', __( sprintf( 'Limit acces to this %s', $singular_name ), 'LION' ), 'it_exchange_protected_content_addon_print_metabox_content', $post_type, 'normal' );
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
function it_exchange_protected_content_addon_print_metabox_content( $post ) {
	$options = it_exchange_protected_content_addon_get_protected_content_options_for_post_object( $post->ID );
	?>
	<div id="it-exchange-protected-content-container">
		<div id="it-exchange-protected-content-who-dialog">
			<p>
				Who is authorized to see this post? 
				<select class="it-pc-selector" name="it-exchange-protected-content-is-protected">
					<option value="no" <?php selected( $options['is_protected'], 'no' ); ?> data-dependant-classes="who-everyone">
						<?php _e( 'Everyone', 'LION' ); ?>
					</option>
					<option value="products" <?php selected( $options['is_protected'], 'products' ); ?> data-dependant-classes="who-products">
						<?php _e( 'Customers who have purchased specific products', 'LION' ); ?>
					</option>
					<option value="wp-roles" <?php selected( $options['is_protected'], 'wp_roles' ); ?> data-dependant-classes="who-wp-roles">
						<?php _e( 'Customers who have a specific WordPress role', 'LION' ); ?>
					</option>
				</select>
			</p>
			<div class="it-pc-group <?php echo $display['required_products']; ?> who-products">
				<div>
					<p>
						<?php _e( 'Only customers that have purchased', 'LION' ); ?>
						<select name="it-exchange-protected-content-all-any-products">
							<option value="all" <?php selected( 'all', $options['all_any_products'] ); ?>><?php _e( 'all', 'LION' ); ?></option>
							<option value="any" <?php selected( 'any', $options['all_any_products'] ); ?>><?php _e( 'any', 'LION' ); ?></option>
						</select> 
						<?php _e( 'of the following products can see this post:', 'LION' ); ?>
					</p>
				</div>
				<div>
					<div>
						<a class="it-exchange-protected-content-select-products-link" href="" data-value="search"><?php _e( 'Search for products', 'LION' ); ?></a> | 
						<a class="it-exchange-protected-content-select-products-link" href="" data-value="browse"><?php _e( 'Browse products', 'LION' ); ?></a>
					</div>
					<input type="hidden" data-dependant-classes="it-exchange-protected-content-browse-products" class="it-pc-selector" />
					<div class="it-pc-group it-exchange-protected-content-search-products">
						<input type="text" id="it-exchange-protected-content-what-products-search-input" /> <input type="button" value="<?php _e( 'Add', 'LION' ); ?>" />
					</div>
					<div class="it-pc-group it-exchange-protected-content-browse-products">
						<?php it_exchange_protected_content_addon_print_product_checkboxes(); ?>
					</div>
					<div class="it-pc-group">
						<div class="tagchecklist"><span><a id="post_tag-check-num-0" class="ntdelbutton">X</a>&nbsp;test</span></div>
					</div>
				</div>
			</div>
			<div class="it-pc-group who-wp-roles">
				<p>
					<?php _e( 'Only users with one of the following checked WordPress roles can see this post:', 'LION' ); ?><br />
					<?php
					foreach( (array) get_editable_roles() as $key => $values ) {
						?>
						<label for="it-exchange-protected-content-wp-roles-<?php esc_attr_e( $key ); ?>">
							<input type="checkbox" id="it-exchange-protected-content-wp-roles-<?php esc_attr_e( $key ); ?>" name="it-exchange-protected-content-wp-roles[]" value="<?php esc_attr_e( $key ); ?>" <?php checked( in_array( $key, $options['wp_roles'] ) ); ?>/>&nbsp;<?php esc_attr_e( $values['name'] ); ?>
						</label><br />
						<?php
					}
					?>
				</p>
			</div>
		</div>
		<div class="it-pc-group who-products who-wp-roles">
			<p>
				<?php _e( 'When can authorized viewers see this post?', 'LION' ); ?>
				<select id="it-exchange-protected-content-when-select" name="it-exchange-protected-content-when-protected" class="it-pc-selector">
					<option value="never" <?php selected( 'never', $options['when_protected'] ); ?>>
						<?php _e( 'All the time', 'LION' ); ?>
					</option>
					<option value="time-period" data-dependant-classes="when-time-period" <?php selected( 'time-period', $options['when_protected'] ); ?>>
						<?php _e( 'During a specific time period', 'LION' ); ?>
					</option>
					<!-- NOT CURRENTLY AN OPTION. WILL EVENTUALLY DO DRIP CONTENT 
					<option value="duration-period" data-dependant-classes="when-duration-period" <?php selected( 'duration-period', $options['when_protected'] ); ?>>
						<?php _e( 'A specific time period after purchasing one of the above products', 'LION' ); ?>
					</option>
					-->
				</select>
			</p>
			<div class="it-pc-group when-time-period">
				<p>
					<label for="it-exchange-protected-content-when-time-period-start">
						<input type="checkbox" name="it-exchange-protected-content-when-time-period-start" class="it-pc-selector" id="it-exchange-protected-content-when-time-period-start" data-dependant-classes="when-time-period-start" <?php checked( 'on', $options['when_time_period_start'] ); ?>/>&nbsp;<?php _e( 'Use a start date', 'LION' ); ?><br />
					</label>
					<label for="it-exchange-protected-content-when-time-period-end">
						<input type="checkbox" name="it-exchange-protected-content-when-time-period-end" class="it-pc-selector" id="it-exchange-protected-content-when-time-period-end" data-dependant-classes="when-time-period-end" <?php checked( 'on', $options['when_time_period_end'] ); ?>/>&nbsp;<?php _e( 'Use an end date', 'LION' ); ?>
					</label>
				</p>
				<div>
					<span class="when-time-period-start it-pc-group"><?php _e( 'This content is only accessible after ', 'LION' ); ?>
						<input type="text" name="it-exchange-protected-content-when-time-period-start-date" value="<?php esc_attr_e( $options['when_time_period_start_date'] ); ?>" />
						<br />
					</span>
					<span class="when-time-period-end it-pc-group"><?php _e( 'This content is only accessible before ', 'LION' ); ?>
					<input type="text" name="it-exchange-protected-content-when-time-period-end-date" value="<?php esc_attr_e( $options['when_time_period_end_date'] ); ?>" />
					</span>
				</div>
			</div>
			<div id="it-exchange-protected-content-when-duration-after-purchase" class="it-pc-group when-duration-period">
				<p>
					<label for="it-exchange-protected-content-time-duration-start">	
						<input type="checkbox" class="it-pc-selector" name="it-exchange-protected-content-when-duration-start" id="it-exchange-protected-content-time-duration-start" data-dependant-classes="when-duration-period-start" <?php checked( 'on', $options['when_duration_start'] ); ?>>&nbsp;<?php _e( 'Use a delayed start', 'LION' ); ?>
					</label><br />
					<label for="it-exchange-protected-content_time-duration-end">
						<input type="checkbox" class="it-pc-selector" name="it-exchange-protected-content-when-duration-end" id="it-exchange-protected-content-time-duration-end" data-dependant-classes="when-duration-period-end" <?php checked( 'on', $options['when-duration-end'] ); ?>>&nbsp;<?php _e( 'Use an expiration period', 'LION' ); ?>
					</label>
				</p>
				<p class="when-duration-period-start it-pc-group">
					<?php printf( __( 'This content is not available to the customer %suntil%s ', 'LION' ), '<strong>', '</strong>' ); ?>
					<input name="it-exchange-protected-content-when-duration-start-quantity" value="<?php esc_attr_e( $options['when_duration_start_quantity'] ); ?>" type="text" size="4" /> 
					<select name="it-exchange-protected-content-when-duration-start-units">
						<option value="days" <?php selected( 'days', $options['when_duration_start_units'] ); ?>><?php _e( 'day(s)' ); ?></option>
						<option value="weeks" <?php selected( 'weeks', $options['when_duration_start_units'] ); ?>><?php _e( 'week(s)' ); ?></option>
						<option value="months" <?php selected( 'months', $options['when_duration_start_units'] ); ?>><?php _e( 'month(s)' ); ?></option>
						<option value="years" <?php selected( 'years', $options['when_duration_start_units'] ); ?>><?php _e( 'year(s)' ); ?></option>
					</select>
					&nbsp;<?php _e( 'from their date of purchase.' ); ?>
				</p>
				<p class="when-duration-period-end it-pc-group">
					<?php printf( __( 'This content is not available to the customer %safter%s ', 'LION' ), '<strong>', '</strong>' ); ?>
					<input type="text" name="it-exchange-protected-content-when-duration-end-quantity" size="4" value="<?php esc_attr_e( $options['when_duration_end_quantity'] ); ?>"/> 
					<select name="it-exchange-protected-content-when-duration-end-units">
						<option value="days" <?php selected( 'days', $options['when_duration_end_units'] ); ?>><?php _e( 'day(s)' ); ?></option>
						<option value="weeks" <?php selected( 'weeks', $options['when_duration_end_units'] ); ?>><?php _e( 'week(s)' ); ?></option>
						<option value="months" <?php selected( 'months', $options['when_duration_end_units'] ); ?>><?php _e( 'month(s)' ); ?></option>
						<option value="years" <?php selected( 'years', $options['when_duration_end_units'] ); ?>><?php _e( 'year(s)' ); ?></option>
					</select>
					&nbsp;<?php _e( 'from their date of purchase.' ); ?>
				</p>
			</div>
		</div>
		<div id="it-exchange-protected-content-unauthorized-views" class="it-pc-group who-products who-wp-roles">
			<div id="it-exchange-protected-content-unauthorized-singular-views">
				<p>
					<?php _e( 'What should happen if an unauthorized user accesses this page directly?', 'LION' ); ?><br />
					<label for="it-exchange-protected-content-unauthorized-single-redirect">
						<input type="radio" name="it-exchange-protected-content-unauthorized-singular-action" id="it-exchange-protected-content-unauthorized-single-redirect" value="redirect" class="it-pc-selector" data-dependant-classes="unauthorized-single-redirect" <?php checked( 'redirect', $options['unauthorized-singular-action'] ); ?>>
						&nbsp;<?php _e( 'They get redirected to another URL', 'LION' ); ?>
					</label>
					<br />
					<label for="it-exchange-protected-content-unauthorized-single-excerpt">
						<input type="radio" name="it-exchange-protected-content-unauthorized-singular-action" id="it-exchange-protected-content-unauthorized-single-excerpt" value="excerpt" class="it-pc-selector" <?php checked( 'excerpt', $options['unauthorized_singular_action'] ); ?>>
						&nbsp;<?php _e( 'They get shown the post excerpt', 'LION' ); ?>
					</label>
					<br />
					<label for="it-exchange-protected-content-unauthorized-single-custom-message">
						<input type="radio" name="it-exchange-protected-content-unauthorized-singular-action" id="it-exchange-protected-content-unauthorized-single-custom-message" value="custom-message" class="it-pc-selector" data-dependant-classes="unauthorized-single-custom-message" <?php checked( 'custom-message', $options['unauthorized_singular_action'] ); ?>>
						&nbsp;<?php _e( 'They get shown a custom message', 'LION' ); ?>
					</label>
				</p>
				<p id="it-exchange-protected-content-unauthorized-singular-redirect" class="it-pc-group unauthorized-single-redirect">
					<?php _e( 'URL to redirect them to: ', 'LION' ); ?><input type="text" name="it-exchange-protected-content-unauthorized-singular-redirect-url" value="<?php esc_attr_e( $options['unauthorized_singular_redirect_url'] ); ?>" />
				</p>
				<p id="it-exchange-protected-content-unauthroized-singular-redirect-custom-message" class="it-pc-group unauthorized-single-custom-message">
					<?php _e( 'Custom Message for singular views:', 'LION' ); ?><br />
					<textarea name="it-exchange-protected-content-unauthorized-singular-custom-message"><?php esc_html_e( $options['unauthorized_singular_custom_message'] ); ?></textarea>
				</p>
			</div>
			<div>
				<p>
					<?php _e( 'What should happen when this post is appears in an archive or category view for unauthorized users?', 'LION' ); ?><br />
					<!-- Maybe Later
					<label for="it-exchange-protected-content-unauthorized-archive-hide">
						<input type="radio" name="it-exchange-protected-content-unauthorized-archive-action" id="it-exchange-protected-content-unauthorized-archive-hide" value="hide" class="it-pc-selector" <?php checked( 'hide', $options['unauthorized_archive_action'] ); ?>>
						&nbsp;<?php _e( 'Don\'t show it in the archive', 'LION' ); ?>
					</label>
					<br />
					-->
					<label for="it-exchange-protected-content-unauthorized-archive-excerpt">
						<input type="radio" name="it-exchange-protected-content-unauthorized-archive-action" id="it-exchange-protected-content-unauthorized-archive-excerpt" value="excerpt" class="it-pc-selector" <?php checked( 'excerpt', $options['unauthorized_archive_action'] ); ?>>
						&nbsp;<?php _e( 'Show the excerpt if available', 'LION' ); ?>
					</label>
					<br />
					<label for="it-exchange-protected-content-unauthorized-archive-custom-message-radio">
						<input type="radio" name="it-exchange-protected-content-unauthorized-archive-action" id="it-exchange-protected-content-unauthorized-archive-custom-message-radio" value="custom-message" class="it-pc-selector" data-dependant-classes="unauthorized-archive-custom-message" <?php checked( 'custom-message', $options['unauthorized_archive_action'] ); ?>>
						&nbsp;<?php _e( 'Show a custom message', 'LION' ); ?>
					</label>
				</p>
				<p id="it-exchange-protected-content-unauthorized-archive-custom-message" class="unauthorized-archive-custom-message it-pc-group">
					<?php _e( 'Custom Message for archive views:', 'LION' ); ?><br />
					<textarea name="it-exchange-protected-content-unauthorized-archive-custom-message"><?php esc_html_e( $options['unauthorized_archive_custom_message']     ); ?></textarea>
				</p>
			</div>
			<div id="it-exchange-protected-content-unauthorized-search-views">
				<p>
					<?php _e( 'What should happen when this post appears in search results for unauthorized users?', 'LION' ); ?><br />
					<!-- Maybe Later
					<label for="it-exchange-protected-content-unauthorized-search-hide">
						<input type="radio" name="it-exchange-protected-content-unauthorized-search-action" id="it-exchange-protected-content-unauthorized-search-hide" value="hide" class="it-pc-selector" <?php checked( 'hide', $options['unauthorized_search_action'] ); ?>>
						&nbsp;<?php _e( 'Don\'t show it in the search results', 'LION' ); ?>
					</label>
					<br />
					-->
					<label for="it-exchange-protected-content-unauthorized-search-excerpt">
						<input type="radio" name="it-exchange-protected-content-unauthorized-search-action" id="it-exchange-protected-content-unauthorized-search-excerpt" value="excerpt" class="it-pc-selector" <?php checked( 'excerpt', $options['unauthorized_search_action'] ); ?>>
						&nbsp;<?php _e( 'Show the excerpt if available', 'LION' ); ?>
					</label>
					<br />
					<label for="it-exchange-protected-content-unauthorized-search-custom-message">
						<input type="radio" name="it-exchange-protected-content-unauthorized-search-action" id="it-exchange-protected-content-unauthorized-search-custom-message" value="custom-message" class="it-pc-selector" data-dependant-classes="unauthorized-search-custom-message" <?php checked( 'custom-message', $options['unauthorized_search_action'] ); ?>>
						&nbsp;<?php _e( 'Show a custom message', 'LION' ); ?>
					</label>
				</p>
				<p id="it-exchange-protected-content-unauthorized-search-custom-message" class="unauthorized-search-custom-message it-pc-group">
					<?php _e( 'Custom Message for search results:', 'LION' ); ?><br />
					<textarea name="it-exchange-protected-content-unauthorized-search-custom-message"><?php esc_html_e( $options['unauthorized_search_custom-message']     ); ?></textarea>
				</p>
			</div>
			<div id="it-exchange-protected-content-unauthorized-feed-views">
				<p>
					<?php _e( 'What should happen when this post appears in a feed view for unauthorized users?', 'LION' ); ?><br />
					<!-- Maybe Later
					<label for="it-exchange-protected-content-unauthorized-feed-hide">
						<input type="radio" name="it-exchange-protected-content-unauthorized-feed-action" id="it-exchange-protected-content-unauthorized-feed-hide" value="hide" class="it-pc-selector" <?php checked( 'hide', $options['unauthorized_feed_action'] ); ?>>
						&nbsp;<?php _e( 'Don\'t show it in the feed views', 'LION' ); ?>
					</label>
					<br />
					-->
					<label for="it-exchange-protected-content-unauthorized-feed-excerpt">
						<input type="radio" name="it-exchange-protected-content-unauthorized-feed-action" id="it-exchange-protected-content-unauthorized-feed-excerpt" value="excerpt" class="it-pc-selector" <?php checked( 'excerpt', $options['unauthorized_feed_action'] ); ?>>
						&nbsp;<?php _e( 'Show the excerpt if available', 'LION' ); ?>
					</label>
					<br />
					<label for="it-exchange-protected-content-unauthorized-feed-custom-message">
						<input type="radio" name="it-exchange-protected-content-unauthorized-feed-action" id="it-exchange-protected-content-unauthorized-feed-custom-message" value="custom-message" class="it-pc-selector" data-dependant-classes="unauthorized-feed-custom-message" <?php checked( 'custom-message', $options['unauthorized_feed_action'] ); ?>>
						&nbsp;<?php _e( 'Show a custom message', 'LION' ); ?>
					</label>
				</p>
				<p id="it-exchange-protected-content-unauthorized-feed-custom-message" class="unauthorized-feed-custom-message it-pc-group">
					<?php _e( 'Custom Message for feed views:', 'LION' ); ?><br />
					<textarea name="it-exchange-protected-content-unauthorized-feed-custom-message"><?php esc_html_e( $options['unauthorized_feed_custom_message']     ); ?></textarea>
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
function it_exchange_protected_content_addon_print_product_checkboxes() {
	// Get all product types
	$is_ajax            = false;
	$protected_products = (array) it_exchange_get_option( 'protected_source_products' );
	
	if ( ! empty( $protected_products ) ) {
		global $post;
		$is_ajax = ( ! empty( $_GET['action'] ) && 'it_exchange_protected_content_addon_get_protected_products' == $_GET['action'] ) ? true : false;

		// Grab products already selected
		$options = get_post_meta( $post->ID, '_it_exchange_protected_content_options', true );
		$selected_products = empty( $options['selected_products'] ) ? false : (array) $options['selected_products'];
		if ( $is_ajax || false === $selected_products )
			$selected_products = array();

		// Loop through products and create checkboxes
		foreach( $protected_products as $product_id => $product_title ) { 
			?>  
			<label for="it-exchange-protected-content-protect-product-<?php esc_attr_e( $product_id ); ?>">
			<input type="checkbox" id="it-exchange-protected-content-protect-product-<?php esc_attr_e( $product_id ); ?>" name="it-exchange-protected-content-selected-products[]" value="<?php esc_attr_e( $product_id ); ?>" <?php checked( in_array( $product_id, $selected_products ) ); ?>>&nbsp;<?php esc_attr_e( apply_filters( 'the_title', $product_title ) ); ?>
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
function it_exchange_protected_content_addon_save_post_restrictions( $post ) {
	if ( ! isset( $_POST['it-exchange-protected-content-is-protected'] ) )
		return;

	$option_keys = array(
		'is-protected', 'all-any-products', 'when-protected', 'wp-roles', 'selected-products', 
		'when-time-period-start', 'when-time-period-end', 'when-time-period-start-date', 
		'when-time-period-end-date', 'when-duration-start', 'when-duration-end', 'when-duration-start-quantity', 'when-duration-start-units', 
		'when-duration-end-quantity', 'when-duration-end-units', 'unauthorized-singular-action', 
		'unauthorized-singular-redirect-url', 'unauthorized-singular-custom-message', 
		'unauthorized-archive-action', 'unauthorized-archive-custom-message', 'unauthorized-search-action', 
		'unauthorized-search-custom-message', 'unauthorized-feed-action', 'unauthorized-feed-custom-message',
	);

	$new_options = array();
	foreach( $option_keys as $key ) {
		if ( isset( $_POST['it-exchange-protected-content-' . $key] ) )
			$new_options[$key] = $_POST['it-exchange-protected-content-' . $key];
	}

	// Validate redirect URL
	if ( isset( $new_options['unauthorized_singular_redirect_url'] ) && ! esc_url( $new_options['unauthorized_singular_redirect_url'] ) )
		unset( $new_options['unauthorized_singular_redirect_url'] );

	update_post_meta( $post, '_it_exchange_protected_content_options', $new_options ); 
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
function it_exchange_protected_content_addon_get_protected_content_options_for_post_object( $post_id ) {
	if ( false === ( $options = get_post_meta( $post_id, '_it_exchange_protected_content_options', true ) ) )
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
		'unauthorized_archive_action'          => 'excerpt',
		'unauthorized_archive_custom_message'  => __( 'Premium Content', 'LION' ),
		'unauthorized_search_action'           => 'excerpt',
		'unauthorized_search_custom_message'   => __( 'Premium Content', 'LION' ),
		'unauthorized_feed_action'             => 'excerpt',
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
function it_exchange_protected_content_addon_enqueue_admin_scripts() {
	global $current_screen;
	$addon_url = ITUtility::get_url_from_file( dirname( __FILE__ ) );
	if ( 'post' != $current_screen->base )
		return;
	wp_enqueue_script( 'it_exchange_protected_content_addon-admin-js', $addon_url . '/js/admin.js', array( 'jquery' ) );
	wp_enqueue_script( 'ithemesNestedFormToggles', $addon_url . '/js/jquery.iThemesNestedFormToggles.js', array( 'jquery' ) );
}

/**
 * Listens for the tinymce call for a popup dialog and inserts it when triggered
 *
 * @since 0.3.8
 * @return void
*/
function it_exchange_protected_content_addon_tinymce_popup_intercept() {
	include_once( dirname( __FILE__ ) . '/js/dialog.php' );
	die();
}

/**  
 * Initializes the Protected Content TinyMCE plugin
 *
 * @since 0.3.8
 * @return void
*/
function it_exchange_protected_content_addon_init_tinymce () {
	global $current_screen;

	// Don't bother if we're not on a post type page
	if ( 'post' != $current_screen->base )
		return;

	// Don't init if user doesn't have correct permissions
	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) )
		return;

	// Add TinyMCE buttons when using rich editor
	if ( 'true' == get_user_option( 'rich_editing' ) ) {
		wp_localize_script( 'it_exchange_protected_content_addon-admin-js', 'ITExchangeProtectedContentAddonDialog', array(
			'title' => __( 'Protect the Selected Content', 'LION' ),
			'desc'  => __( 'Highlight some text to restrict who sees it...', 'LION' ),
		));

		add_filter( 'mce_external_plugins', 'it_exchange_protected_content_addon_register_mceplugin', 6 );
		add_filter( 'mce_buttons', 'it_exchange_protected_content_addon_add_mcebutton', 6 );
	}
}

/**  
 * Adds the TinyMCE plugin to the list of loaded plugins
 *
 * @since 0.3.8
 * @param array $plugins The current list of plugins to load
 * @return array The updated list of plugins to laod
*/
function it_exchange_protected_content_addon_register_mceplugin ( $plugins ) {
	$addon_url = ITUtility::get_url_from_file( dirname( __FILE__ ) );
	// Add a changing query string to keep the TinyMCE plugin from being cached & breaking TinyMCE in Safari/Chrome
	$plugins['ITExchangeProtectedContentAddon'] = $addon_url . '/js/tinymce.js?ver='.time();
	return $plugins;
}

/**
 * Adds the button to the TinyMCE editor
 *
 * @since 0.3.8
 * @param array $buttons The current list of buttons in the editor
 * @return array The updated list of buttons in the editor
*/
function it_exchange_protected_content_addon_add_mcebutton ( $buttons ) {
	array_push( $buttons, '|', 'ITExchangeProtectedContentAddon' );
	return $buttons;
}
