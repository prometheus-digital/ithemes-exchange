<?php
/**
 * CartBuddy admin class.
 *
 * This class manages the admin side of the plugin
 *
 * @package IT_Cart_Buddy
 * @since 0.1
*/
class IT_Cart_Buddy_Admin {

	/**
	 * Parent Class
	 * @var _parent object Parent Class
	 * @since 0.1
	*/
	var $_parent;

	/**
	 * Class constructor
	 *
	 * @uses add_action()
	 * @since 0.1
	 * @return void 
	*/
	function IT_Cart_Buddy_Admin( &$parent ) {

		// Set parent property
		$this->_parent = $parent;

		// Add actions for iThemes registration
		add_action( 'admin_menu', array( $this, 'add_cart_buddy_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'enable_disable_registered_add_on' ) );
	}

	/**
	 * Adds the main Cart Buddy menu item to the WP admin menu
	 *
	 * @since 0.2
	 * @return void
	*/
	function add_cart_buddy_admin_menu() {
		$capability = apply_filters( 'it_cart_buddy_admin_menu_capability', 'read' );
		add_menu_page( 'Cart Buddy', 'Cart Buddy', $capability, 'it-cart-buddy', array( $this, 'print_cart_buddy_setup_page' ) );
		add_submenu_page( 'it-cart-buddy', 'Cart Buddy Setup', 'Setup', $capability, 'it-cart-buddy-setup', array( $this, 'print_cart_buddy_setup_page' ) );
		remove_submenu_page( 'it-cart-buddy', 'it-cart-buddy' );
		add_submenu_page( 'it-cart-buddy', 'Cart Buddy Add-ons', 'Add-ons', $capability, 'it-cart-buddy-addons', array( $this, 'print_cart_buddy_add_ons_page' ) );
	}

	/**
	 * Prints the setup page for cart buddy
	 *
	 * @since 0.2
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
	 * @since 0.2
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
	 * @since 0.2
	*/
	function enable_disable_registered_add_on() {
		$enable_addon = empty( $_GET['it-cart-buddy-enable-addon'] ) ? false : $_GET['it-cart-buddy-enable-addon'];
		$disable_addon = empty( $_GET['it-cart-buddy-disable-addon'] ) ? false : $_GET['it-cart-buddy-disable-addon'];
		$registered = it_cart_buddy_get_add_ons();
		
		if ( ! $enable_addon && ! $disable_addon ) 
			return false;

		$enabled = get_option( 'it_cart_buddy_enabled_add_ons' );

		if ( $enable_addon && in_array( $enable_addon, array_keys( $registered ) ) ) {
			$enabled[$enable_addon] = $registered[$enable_addon]['file'];
		} else if ( $disable_addon && ! empty( $enabled[$disable_addon] ) ) {
			unset( $enabled[$disable_addon] );
		}

		update_option( 'it_cart_buddy_enabled_add_ons', $enabled );

	}
}
if ( is_admin() )
	$IT_Cart_Buddy_Admin = new IT_Cart_Buddy_Admin( $this );
