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
			add_meta_box( 'it_cart_buddy_protected_content_addon_metabox', __( sprintf( 'Limit acces to this %s', $singular_name ), 'LION' ), 'it_cart_buddy_protected_content_addon_print_metabox_content', $post_type, 'side' );
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
	$protected_products = array();
	$is_ajax             = false;
	if ( $product_types = it_cart_buddy_get_enabled_addons( array( 'category' => 'product-type' ) ) ) {
		foreach( $product_types as $product_type ) {
			if ( it_cart_buddy_product_type_supports_feature( $product_type['slug'], 'protected-content' ) ) {
				$product_args = array( 'posts_per_page' => -1, 'product_type' => $product_type['slug'] );
				$protected_products = array_merge( $protected_products, it_cart_buddy_get_products( $product_args ) );
			}
		}
	}
	
	if ( ! empty( $protected_products ) ) {
		global $post;
		$is_ajax = ( ! empty( $_GET['action'] ) && 'it_cart_buddy_protected_content_addon_get_protected_products' == $_GET['action'] ) ? true : false;

		// Grab products already selected
		if ( $is_ajax || false === $selected_products = get_post_meta( $post->ID, '_it_cart_buddy_protected_content_addon_selected_products', true ) ) 
			$selected_products = array();

		// Loop through products and create checkboxes
		foreach( $protected_products as $product ) { 
			?>  
			<label for="it_cart_buddy_protected_content_protect_product_<?php esc_attr_e( $product->ID ); ?>">
			<input type="checkbox" id="it_cart_buddy_protected_content_protect_product_<?php esc_attr_e( $product->ID ); ?>" name="_it_cart_buddy_protected_content_addon_selected_products[]" value="<?php esc_attr_e( $product->ID ); ?>" <?php checked( in_array( $product->ID, $selected_products ) ); ?>>&nbsp;<?php esc_attr_e( apply_filters( 'the_title', $product->post_title ) ); ?>
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
