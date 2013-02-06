<?php
/**
 * CartBuddy admin class.
 *
 * This class manages the admin side of the plugin
 *
 * @package IT_Cart_Buddy
 * @since 0.1.0
*/
class IT_Cart_Buddy_Admin {

	/**
	 * Parent Class
	 * @var _parent object Parent Class
	 * @since 0.1.0
	*/
	var $_parent;

	/**
	 * Class constructor
	 *
	 * @uses add_action()
	 * @since 0.1.0
	 * @return void 
	*/
	function IT_Cart_Buddy_Admin( &$parent ) {

		// Set parent property
		$this->_parent = $parent;

		// Admin Menu Capability
		$this->admin_menu_capability = apply_filters( 'it_cart_buddy_admin_menu_capability', 'read' );

		// Open cart buddy menu when on add/edit cartbuddy item post type
		add_action( 'parent_file', array( $this, 'open_cart_buddy_menu_on_post_type_views' ) );

		// Add actions for iThemes registration
		add_action( 'admin_menu', array( $this, 'add_cart_buddy_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'enable_disable_registered_add_on' ) );

		// Redirect to Item selection on Add New if needed
		add_action( 'admin_init', array( $this, 'redirect_post_new_to_item_selection_screen' ) );
	}

	/**
	 * Adds the main Cart Buddy menu item to the WP admin menu
	 *
	 * @since 0.2.0
	 * @return void
	*/
	function add_cart_buddy_admin_menu() {
		add_menu_page( 'Cart Buddy', 'Cart Buddy', $this->admin_menu_capability, 'it-cart-buddy', array( $this, 'print_cart_buddy_setup_page' ) );
		add_submenu_page( 'it-cart-buddy', 'Cart Buddy Setup', 'Setup', $this->admin_menu_capability, 'it-cart-buddy-setup', array( $this, 'print_cart_buddy_setup_page' ) );
		remove_submenu_page( 'it-cart-buddy', 'it-cart-buddy' );
		$this->add_item_submenus();
		add_submenu_page( 'it-cart-buddy', 'Cart Buddy Add-ons', 'Add-ons', $this->admin_menu_capability, 'it-cart-buddy-addons', array( $this, 'print_cart_buddy_add_ons_page' ) );
	}

	/**
	 * Prints the setup page for cart buddy
	 *
	 * @since 0.2.0
	 * @return void
	*/
	function print_cart_buddy_setup_page() {
		?>
		<div class="wrap">
			<?php screen_icon( 'page' ); ?>
			<h2>Cart Buddy</h2>
			<p>Possibly place setup wizzard here</p>
			<p>Definitely replace icon</p>
		</div>
		<?php
	}

	/**
	 * Prints the add-ons page in the admin area
	 *
	 * @since 0.2.0
	 * @return void
	*/
	function print_cart_buddy_add_ons_page() {
		$registered = it_cart_buddy_get_add_ons();
		$add_on_cats = it_cart_buddy_get_add_on_categories();
		?>
		<div class="wrap">
			<!-- temp icon --> 
			<?php screen_icon( 'page' ); ?> 
			<h2>Cart Buddy Add-Ons</h2>

			<h3>Enabled Add-ons</h3>
			<?php
			if ( $enabled = get_option( 'it_cart_buddy_enabled_add_ons' ) ) {
				foreach( (array) $enabled as $slug => $location ) {
					if ( empty( $registered[$slug] ) )
						continue;
					$params = $registered[$slug];
					// TEMPORARY UI
					echo '<div style="height:200px;width:200px;border: 1px solid #444;float:left;margin-right:10px;"><div style="height:20px;background:#999;color:#fff;width:100%;text-align:center;padding:10px 0;">' . $params['name'] . '</div><p style="padding:5px">Category: ' . $add_on_cats[$params['options']['category']]['name'] . '</p><p style="padding:5px;">' . $params['description'] . '</p><p style="margin-left:60px;text-align:center;width:75px;background:#999;border:1px solid #777;padding:2px;"><a href="' . get_site_url() . '/wp-admin/admin.php?page=it-cart-buddy-addons&it-cart-buddy-disable-addon=' . $slug . '" style="text-decoration:none;color:#fff;">Disable</a></p></div>';
				}
			} else {
				echo '<p>' . __( 'No Add-ons currently enabled', 'LION' ) . '</p>';
			}
			?>
			<div style="height:1px;clear:both;-top:10px;"></div>
			<hr />

			<h3>Available Add-ons</h3>
			<?php
			$available_addons = false;
			if ( ( $registered ) ) {
				foreach( $registered as $slug => $params ) {
					if ( ! empty( $enabled[$slug] ) )
						continue;

					$available_addons = true;
					// TEMPORARY UI
					echo '<div style="height:200px;width:200px;border: 1px solid #444;float:left;margin-right:10px;"><div style="height:20px;background:#999;color:#fff;width:100%;text-align:center;padding:10px 0;">' . $params['name'] . '</div><p style="padding:5px">Category: ' . $add_on_cats[$params['options']['category']]['name'] . '</p><p style="padding:5px;">' . $params['description'] . '</p><p style="margin-left:60px;text-align:center;width:75px;background:#999;border:1px solid #777;padding:2px;"><a href="' . get_site_url() . '/wp-admin/admin.php?page=it-cart-buddy-addons&it-cart-buddy-enable-addon=' . $slug . '" style="text-decoration:none;color:#fff;">Enable</a></p></div>';
				}
			}
			if ( ! $available_addons )
				echo '<p>' . __( 'No Add-ons available', 'LION' ) . '</p>';
			?>
		</div>
		<?php
	}

	/**
	 * Adds a registered Add-on to list of enabled add-ons
	 *
	 * @since 0.2.0
	*/
	function enable_disable_registered_add_on() {
		$enable_addon = empty( $_GET['it-cart-buddy-enable-addon'] ) ? false : $_GET['it-cart-buddy-enable-addon'];
		$disable_addon = empty( $_GET['it-cart-buddy-disable-addon'] ) ? false : $_GET['it-cart-buddy-disable-addon'];
		$registered = it_cart_buddy_get_add_ons();
		
		if ( ! $enable_addon && ! $disable_addon ) 
			return false;

		$enabled = get_option( 'it_cart_buddy_enabled_add_ons' );

		if ( $enable_addon && in_array( $enable_addon, array_keys( $registered ) ) ) {
			$enabled[$enable_addon] = $registered[$enable_addon];
		} else if ( $disable_addon && ! empty( $enabled[$disable_addon] ) ) {
			unset( $enabled[$disable_addon] );
		}

		// Lets disable any enabled add-ons that aren't registered any more while we're here.
		foreach( $enabled as $slug => $params ) {
			if ( empty( $registered[$slug] ) )
				unset( $enabled[$slug] );
		}

		update_option( 'it_cart_buddy_enabled_add_ons', $enabled );
		wp_safe_redirect( admin_url( '/admin.php?page=it-cart-buddy-addons' ) );
		die();
	}

	/**
	 * Adds the item submenus based on number of enabled item add-ons
	 *
	 * @since 0.3.0
	 * @return void
	*/
	function add_item_submenus() {
		if ( $enabled_items = it_cart_buddy_get_enabled_add_ons( array( 'category' => array( 'items' ) ) ) ) {
			switch ( count($enabled_items) ) {
				case 0 :
					return;
					break;
				case 1 :
					add_submenu_page( 'it-cart-buddy', 'All Items', 'All Items', $this->admin_menu_capability, 'edit.php?post_type=it_cart_buddy_item' );
					foreach( $enabled_items as $slug => $params ) {
						add_submenu_page( 'it-cart-buddy', 'Add Item', 'Add Item', $this->admin_menu_capability, 'post-new.php?post_type=it_cart_buddy_item&product_type=' . $slug );
					}
					break;
				default :
					add_submenu_page( 'it-cart-buddy', 'All Items', 'All Items', $this->admin_menu_capability, 'edit.php?post_type=it_cart_buddy_item' );
					add_submenu_page( 'it-cart-buddy', 'Add Item', 'Add Item', $this->admin_menu_capability, 'it-cart-buddy-choose-item-type', array( $this, 'print_choose_item_type_admin_page' ) );
					break;
			}
		}
	}

	/**
	 * Page content for adding an item type
	 *
	 * @since 0.3.0
	 * @return void
	*/
	function print_choose_item_type_admin_page() {
		?>
		<div class="wrap">
			<?php screen_icon( 'page' ); ?>
			<h2>Choose an Item Type to add</h2>
			<p>Temp UI...</p>
			<ul>
			<?php
			foreach( it_cart_buddy_get_enabled_add_ons( array( 'category' => array( 'items' ) ) ) as $slug => $params ) {
				echo '<li><a href="' . get_site_url() . '/wp-admin/post-new.php?post_type=it_cart_buddy_item&product_type=' . $slug . '">' . $params['name'] . '</a>';
			}
			?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Opens the Cart Buddy Admin Menu when viewing the Add New page
	 *
	 * @since 0.3.0
	 * @return string
	*/
	function open_cart_buddy_menu_on_post_type_views( $parent_file, $revert=false ) {
		global $submenu_file, $pagenow;
		if ( empty( $_GET['post_type'] ) || 'it_cart_buddy_item' != $_GET['post_type'] )
			return $parent_file;

		if ( 'post-new.php' == $pagenow )
			$submenu_file = 'it-cart-buddy-choose-item-type';

		return 'it-cart-buddy';
	}

	/**
	 * Redirects post-new.php to it-cart-buddy-choose-item-type when needed
	 *
	 * If we have landed on post-new.php?post_type=it_cart_buddy_items without the item-type param
	 * and with multiple item add-ons enabled.
	 *
	 * @since 0.3.1
	 * @return void
	*/
	function redirect_post_new_to_item_selection_screen() {
		global $pagenow;
		$item_add_ons = it_cart_buddy_get_enabled_add_ons( array( 'category' => array( 'items' ) ) );
		$post_type    = empty( $_GET['post_type'] ) ? false : $_GET['post_type'];
		$product_type = empty( $_GET['product_type'] ) ? false : $_GET['product_type'];

		if ( count( $item_add_ons ) > 1 && 'post-new.php' == $pagenow && 'it_cart_buddy_item' == $post_type ) {
			if ( empty( $item_add_ons[$product_type] ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=it-cart-buddy-choose-item-type' ) );
				die();
			}
		}
	}
}
if ( is_admin() )
	$IT_Cart_Buddy_Admin = new IT_Cart_Buddy_Admin( $this );
