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
	
	$options['is_protected'] = 'products';
	// Set initial display values
	$display['protected']['no']       = empty( $options['is_protected'] ) ? '' : 'hide-if-js';
	$display['protected']['products'] = ( 'products' == $options['is_protected'] ) ? '' : 'hide-if-js';
	$display['protected']['wp_roles'] = ( 'wp_roles' == $options['is_protected'] ) ? '' : 'hide-if-js';

	$display['ul_dialog']['everyone'] = $display['protected']['no'];
	$display['ul_dialog']['products'] = $display['protected']['products'];
	$display['ul_dialog']['wp_roles'] = $display['protected']['wp_roles'];

	$display['ul_dialog']['when']     = empty( $display['protected']['no'] ) ? 'hide-if-js' : '';
	$display['ul_dialog']['singular'] = empty( $display['protected']['no'] ) ? 'hide-if-js' : '';
	$display['ul_dialog']['archives'] = empty( $display['protected']['no'] ) ? 'hide-if-js' : '';
	$display['ul_dialog']['search']   = empty( $display['protected']['no'] ) ? 'hide-if-js' : '';
	$display['ul_dialog']['feeds']    = empty( $display['protected']['no'] ) ? 'hide-if-js' : '';

	$display['required_products'] = $display['protected']['products'];
	?>
	<div id="it_cart_buddy_protected_content_ul_dialog">
		<ul>
			<li class="<?php echo $display['ul_dialog']['everyone']; ?>">
				Everyone can see this post 
				<a id="edit-is-protected" href="#" class="edit-protected-content-setting"><?php _e( 'edit', 'LION' ); ?></a>
			</li>
			<li class="<?php echo $display['ul_dialog']['products']; ?>">
				Only customers who purchased one of the following products can see this post: 
				<a href="#" class="edit-protected-content-setting"><?php _e( 'edit', 'LION' ); ?></a>
				<ul>
					<li>Product one</li><li>Product two</li>
				</ul>
			</li>
			<li class="<?php echo $display['ul_dialog']['wp_roles']; ?>">
				Only customers who have one of the following WordPress roles can see this post: 
				<a href=""><?php _e( 'edit', 'LION' ); ?></a>
			</li>
			<li class="<?php echo $display['ul_dialog']['when']; ?>">
				Authorized viewers can see the post from the time they purchase one of the above products until 30 days after their purchase date. 
				<a href=""><?php _e( 'edit', 'LION' ); ?></a>
			</li>
			<li class="<?php echo $display['ul_dialog']['singular']; ?>">
				If an unauthorized viewer gets a direct link to this post, they will be redirected to <?php echo get_home_url(); ?> 
				<a href=""><?php _e( 'edit', 'LION' ); ?></a>
			</li>
			<li class="<?php echo $display['ul_dialog']['archives']; ?>">
				This post will not show up in archive pages if the current viewer is not authorized to see it. 
				<a href=""><?php _e( 'edit', 'LION' ); ?></a>
			</li>
			<li class="<?php echo $display['ul_dialog']['search']; ?>">
				This post will not show up in search results if the current viewer is not authorized to see it.  
				<a href=""><?php _e( 'edit', 'LION' ); ?></a>
			</li>
			<li class="<?php echo $display['ul_dialog']['feeds']; ?>">
				This post will not show up in RSS feeds. 
				<a href=""><?php _e( 'edit', 'LION' ); ?></a>
			</li>
		</ul>
	</div>
	<div id="it_cart_buddy_protected_content_who_dialog" class="hide-if-js">
		<p>
			Who is authorized to see this post? 
			<select name="it_cart_buddy_protected_content_is_protected" id="it_cart_buddy_protected_content_is_protected">
				<option value="no" <?php selected( $options['is_protected'], 'no' ); ?>><?php _e( 'Everyone', 'LION' ); ?></option>
				<option value="products" <?php selected( $options['is_protected'], 'products' ); ?>><?php _e( 'Customers who have purchased specific products', 'LION' ); ?></option>
				<option value="wp_roles" <?php selected( $options['is_protected'], 'wp_roles' ); ?>><?php _e( 'Customers who have a specific WordPress role', 'LION' ); ?></option>
			</select>
		</p>
		<div id="it_cart_buddy_protected_content_required_products" class="<?php echo $display['required_products']; ?>">
			<div id="it_cart_buddy_protected_content_all_any_products" class="hide-if-js">
				<p>
					Only customers that have purchased 
					<select>
						<option>all</option>
						<option>at least one</option>
					</select> 
					of the following products can see this post:
				</p>
			</div>
			<div id="it_cart_buddy_protected_content_what_products" class="hide-if-js">
				<div id="it_cart_buddy_protected_content_what_product_tabs">Search for products | Browse products</div>
				<div id="it_cart_buddy_protected_content_what_products_search_div">
					<input type="text" id="it_cart_buddy_protected_content_what_products_search_input" /> <input type="button" value="Add" />
				</div>
				<div id="it_cart_buddy_protected_content_what_products_browse_div">
					<?php it_cart_buddy_protected_content_addon_print_product_checkboxes(); ?>
				</div>
				<div id="it_cart_buddy_protected_content_what_products_visual_feedback">
					<div class="tagchecklist"><span><a id="post_tag-check-num-0" class="ntdelbutton">X</a>&nbsp;test</span></div>
				</div>
			</div>
		</div>
		<div id="it_cart_buddy_protected_content_what_wp_roles" class="hide-if-js">
			<p>
				Only users with one of the following WordPress roles can see this post:<br />
				<input type="checkbox" name="it_cart_buddy_protected_content_wp_roles[]" value="subscriber" /> Subscriber<br />
				<input type="checkbox" name="it_cart_buddy_protected_content_wp_roles[]" value="contributor" /> Contributor<br />
				<input type="checkbox" name="it_cart_buddy_protected_content_wp_roles[]" value="administrator" /> Administrator
			</p>
		</div>
		</div>
	<div id="it_cart_buddy_protected_content_when_dialog" class="hide-if-js">
		<p>
			When can authorized viewers see this post? 
			<select>
				<option>All the time</option>
				<option>During a specific time period</option>
				<option>A specific time period after purchasing one of the above products</option>
			</select>
		</p>
		<div id="it_cart_buddy_protected_content_when_time_period">
			<p>
				<input type="checkbox"> Use a start date<br />
				<input type="checkbox"> Use an end date
			</p>
			<p>
				This content is only accessible after <input type="text" /> and before <input type="text" />.
			</p>
		</div>
		<div id="it_cart_buddy_protected_content_when_duration_after_purchase">
			<p>
				<input type="checkbox"> Use a delayed start<br />
				<input type="checkbox"> Use an expiration period
			</p>
			<p>
				This content is not available to the customer <strong>until</strong> <input type="text" size="4" /> 
				<select>
					<option>day(s)</option>
					<option>week(s)</option>
					<option>month(s)</option>
					<option>year(s)</option>
				</select>
				 from their date of purchase.
			</p>
			<p>
				This content is not available to the customer <strong>after</strong> <input type="text" size="4" /> 
				<select>
					<option>day(s)</option>
					<option>week(s)</option>
					<option>month(s)</option>
					<option>year(s)</option>
				</select>
				 from their date of purchase.
			</p>
		</div>
	</div>
	<div id="it_cart_buddy_protected_content_unauthorized_singular_views" class="hide-if-js">
		<p>
			What should happen if an unauthorized user tries to access this page directly?<br />
			<input type="radio">&nbsp;They get redirected to another URL<br />
			<input type="radio">&nbsp;They get shown the post excerpt<br />
			<input type="radio">&nbsp;They get shown a custom message
		</p>
		<p id="it_cart_buddy_protected_content_unauthorized_singular_redirect">
			URL to redirect them to: <input type="text" name="it_cart_buddy_protected_content_unauthorized_singular_redirect_url" />
		</p>
		<p id="it_cart_buddy_protected_content_unauthroized_singular_redirect_custom_message">
			Custom Message for singular views:<br />
			<textarea name="it_cart_buddy_protected_content_unauthorized_singular_custom_message"></textarea>
		</p>
	</div>
	<div id="it_cart_buddy_protected_content_unauthorized_archive_views" class="hide-if-js">
		<p>
			What should happen when this post is supposed to appear in an archive or category view for unauthorized users?<br />
			<input type="radio">&nbsp;Don't show it at all <br />
			<input type="radio">&nbsp;Show the title without a link and replace the excerpt / content with a custom message
		</p>
		<p id="it_cart_buddy_protected_content_unauthorized_archive_custom_message">
			Custom Message for archive results:<br />
			<textarea name="it_cart_buddy_protected_content_unauthorized_archive_custom_message"></textarea>
		</p>
	</div>
	<div id="it_cart_buddy_protected_content_unauthorized_search_views" class="hide-if-js">
		<p>
			What should happen when this post is supposed to appear in search results for unauthorized users?<br />
			<input type="radio">&nbsp;Don't show it at all<br />
			<input type="radio">&nbsp;Show the title without a link and replace the excerpt / content with a custom message
		</p>
		<p id="it_cart_buddy_protected_content_unauthorized_search_custom_message">
			Custom Message for search results:<br />
			<textarea name="it_cart_buddy_protected_content_unauthorized_search_custom_message"></textarea>
		</p>
	</div>
	<div id="it_cart_buddy_protected_content_unauthorized_feed_views" class="hide-if-js">
		<p>
			What should happen when this post is supposed to appear in site feeds (like RSS) for unauthorized users?<br />
			<input type="radio">&nbsp;Don't show it at all<br />
			<input type="radio">&nbsp;Show the title without a link and replace the excerpt / content with a custom message
		</p>
		<p id="it_cart_buddy_protected_content_unauthorized_feed_custom_message">
			Custom Message for feed (RSS) results:<br />
			<textarea name="it_cart_buddy_protected_content_unauthorized_feed_custom_message"></textarea>
		</p>
	</div>
	<?php
}

function it_cart_buddy_protected_content_addon_print_metabox_content_old( $post ) {
	$is_protected = get_post_meta( $post->ID, '_it_cart_buddy_protected_content_addon_post_is_protected', true );
	$redirect_to  = get_post_meta( $post->ID, '_it_cart_buddy_protected_content_addon_post_protected_redirect', true );
	if ( empty( $redirect_to ) )
		$redirect_to = get_site_url();
	$post_type_object = get_post_type_object( $post->post_type );
	$singular_name = empty( $post_type_object->labels->singular_name ) ? __( 'post' ) : strtolower( $post_type_object->labels->singular_name );
	?>
	<p> <?php _e( sprintf( 'Restrict this %s to members who have purchased Cart Buddy products that support protected content?', $singular_name ), 'LION' ); ?></p>
	<select id="it_cart_buddy_protected_content_addon_post_is_protected" name="_it_cart_buddy_protected_content_addon_post_is_protected">
		<option value="0" <?php selected( 0, $is_protected ); ?>>No</option>
		<option value="1" <?php selected( 1, $is_protected ); ?>>Yes</option>
	</select>

	<div class="hide-if-js" id="it_cart_buddy_protected_content_addon_select_post_restrictions">
		<p><?php _e( 'A member must have purchased at least one of the checked products below to see this post', 'LION' ); ?></p>
		<p><?php _e( 'If no products are checked, nobody will have access to this post.', 'LION' ); ?></p>
		<?php it_cart_buddy_protected_content_addon_print_product_checkboxes(); ?>
		<p><?php _e( "Where should members who haven't bought one of the above products be redirected?", 'LION' ); ?></p>
		<input type="text" style="width:100%" name="_it_cart_buddy_protected_content_addon_post_protected_redirect" value="<?php esc_attr_e( esc_url( $redirect_to ) ); ?>" />
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
		if ( $is_ajax || false === $selected_products = get_post_meta( $post->ID, '_it_cart_buddy_protected_content_addon_selected_products', true ) ) 
			$selected_products = array();

		// Loop through products and create checkboxes
		foreach( $protected_products as $product_id => $product_title ) { 
			?>  
			<label for="it_cart_buddy_protected_content_protect_product_<?php esc_attr_e( $product_id ); ?>">
			<input type="checkbox" id="it_cart_buddy_protected_content_protect_product_<?php esc_attr_e( $product_id ); ?>" name="_it_cart_buddy_protected_content_addon_selected_products[]" value="<?php esc_attr_e( $product_id ); ?>" <?php checked( in_array( $product_id, $selected_products ) ); ?>>&nbsp;<?php esc_attr_e( apply_filters( 'the_title', $product_title ) ); ?>
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
	if ( ! isset( $_POST['_it_cart_buddy_protected_content_addon_post_is_protected'] ) )
		return;

	$redirect_to = empty( $_POST['_it_cart_buddy_protected_content_addon_post_protected_redirect'] ) ? get_site_url() : esc_url( $_POST['_it_cart_buddy_protected_content_addon_post_protected_redirect'] );
	update_post_meta( $post, '_it_cart_buddy_protected_content_addon_post_is_protected', $_POST['_it_cart_buddy_protected_content_addon_post_is_protected'] );
	update_post_meta( $post, '_it_cart_buddy_protected_content_addon_selected_products', $_POST['_it_cart_buddy_protected_content_addon_selected_products'] );
	update_post_meta( $post, '_it_cart_buddy_protected_content_addon_post_protected_redirect', $redirect_to );
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
		'when_timeperiod_start'                => false,
		'when_timeperiod_end'                  => false,
		'when_timeperiod_start_date'           => date( 'Y-m-d' ),
		'when_timeperiod_end_date'             => '',
		'when_duration_start'                  => false,
		'when_duration_end'                    => false,
		'when_duration_start_date'             => date( 'Y-m-d' ),
		'when_duration_end_date'               => '',
		'unauthorized_singular_action'         => 'redirect',
		'unauthorized_singular_redirect_url'   => get_home_url(),
		'unauthorized_singular_custom_message' => '',
		'unauthorized_archive_action'          => 'custom_message',
		'unauthorized_archive_custom_message'  => __( 'Premium Content', 'LION' ),
		'unauthorized_search_action'           => 'custom_message',
		'unauthorized_search_custom_message'   => __( 'Premium Content', 'LION' ),
		'unauthorized_feed_action'             => 'custom_message',
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
