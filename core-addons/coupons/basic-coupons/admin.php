<?php
/**
 * Functions / hooks only needed in the admin
 * @package IT_Exchange
 * @since 0.4.0
*/

/**
 * This adds a menu item to the Exchange menu pointing to the WP All [post_type] table
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_add_menu_item() {
	if ( ! empty( $_GET['page'] ) && 'it-exchange-add-basic-coupon' == $_GET['page'] ) {
		$slug = 'it-exchange-add-basic-coupon';
		$func = 'it_exchange_basic_coupons_print_add_coupon_screen';
		add_submenu_page( 'it-exchange', __( 'Add Coupon', 'LION' ), __( 'Coupons', 'LION' ), 'update_plugins', $slug, $func );
	} else if ( ! empty( $_GET['page'] ) && 'it-exchange-edit-basic-coupon' == $_GET['page'] ) {
		$slug = 'it-exchange-edit-basic-coupon';
		$func = 'it_exchange_basic_coupons_print_edit_coupon_screen';
		add_submenu_page( 'it-exchange', __( 'Edit Coupon', 'LION' ), __( 'Coupons', 'LION' ), 'update_plugins', $slug, $func );
	}
	$slug = 'it-exchange-basic-coupons';
	$url = add_query_arg( array( 'post_type' => 'it_exchange_coupon' ), 'edit.php' );
	add_submenu_page( 'it-exchange', __( 'Coupons', 'LION' ), __( 'Coupons', 'LION' ), 'update_plugins', $url );
}
add_action( 'admin_menu', 'it_exchange_basic_coupons_add_menu_item' );

/**
 * Redirects admin users away from core add / edit post type screens for coupons to our custom ones.
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_redirect_core_add_edit_screens() {
	$pagenow = empty( $GLOBALS['pagenow'] ) ? false : $GLOBALS['pagenow'];
	$post_type = empty( $_GET['post_type'] ) ? false : $_GET['post_type'];
	if ( ! $pagenow || ( 'post-new.php' != $pagenow && 'post.php' != $pagenow ) ) 
		return;

	if ( 'post-new.php' == $pagenow && 'it_exchange_coupon' == $post_type ) {
		wp_safe_redirect( add_query_arg( array( 'page' => 'it-exchange-add-basic-coupon' ), get_admin_url() . 'admin.php' ) );
		die();
	}

	die('here');
}
add_action( 'admin_init', 'it_exchange_basic_coupons_redirect_core_add_edit_screens' );

/**
 * Prints the add coupon screen
 *
 * @since 0.4.0
 *
 * @return void;
*/
function it_exchange_basic_coupons_print_add_coupon_screen() {
	echo "<p>Add Coupon</p>";
}

/**
 * Prints the edit coupon screen
 *
 * @since 0.4.0
 *
 * @return void;
*/
function it_exchange_basic_coupons_print_edit_coupon_screen() {
	echo "<p>Edit Coupon</p>";
}
