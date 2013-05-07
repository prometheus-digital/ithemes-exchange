<?php
/**
 * Functions / hooks only needed in the admin
 * @package IT_Exchange
 * @since 0.4.0
*/

/**
 * Saves a coupon
 * 
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_save_coupon() {
	if ( empty( $_POST['it-exchange-basic-coupons-add-coupon'] ) )
		return;

	$nonce = empty( $_POST['_wpnonce'] ) ? false : $_POST['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'it-exchange-basic-coupons-add-coupon' ) )
		return;

	$data = ITForm::get_post_data();

	if ( ! it_exchange_basic_coupons_data_is_valid() )
		return;

	// Remove hidden field
	unset( $data['add-coupon'] );

	// Convert name to post_title
	$data['post_title'] = $data['name'];
	unset( $data['name'] );

	// Convert code, amount-number, amount-type, start-date, end-date to meta
	$data['post_meta']['_it-basic-code']          = $data['code'];
	$data['post_meta']['_it-basic-amount-number'] = $data['amount-number'];
	$data['post_meta']['_it-basic-amount-type']   = $data['amount-type'];
	$data['post_meta']['_it-basic-start-date']    = $data['start-date'];
	$data['post_meta']['_it-basic-end-date']      = $data['end-date'];
	unset( $data['code'] );
	unset( $data['amount-number'] );
	unset( $data['amount-type'] );
	unset( $data['start-date'] );
	unset( $data['end-date'] );
	it_exchange_add_coupon( $data );
}
add_action( 'admin_init', 'it_exchange_basic_coupons_save_coupon' );

/**
 * Vaidates coupon data
 *
 * @since 0.4.0
 *
 * @return boolean
*/
function it_exchange_basic_coupons_data_is_valid() {
	return true;
}

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
	$flush_cache  = ! empty( $_POST );

	//$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
	$form_values = ITForm::get_post_data();
	$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-basic-coupons' ) ); 
	$form_options = array(
		'id'      => apply_filters( 'it-exchange-basic-coupons_form_id', 'it-exchange-basic-coupons' ),
		'enctype' => apply_filters( 'it-exchange-basic-coupons_enctype', false ),
	);   
	?>
	<div class="wrap">
		<?php
		screen_icon( 'page' );
		echo '<h2>' . __( 'Add Coupon', 'LION' ) . '</h2>';
		$form->start_form( $form_options, 'it-exchange-basic-coupons-add-coupon' );
		$form->add_hidden( 'add-coupon', true );
		?>
		<div class="it-exchange-add-basic-coupon">
			<div class="fields">
				<div class="field">
					<label>Name <span class="tip" title="What do you want to call this coupon? Just for your reference.">i</span></label>
					<?php $form->add_text_box( 'name' ); ?>
				</div>
				<div class="field code">
					<label>Code <span class="tip" title="Try something cool like EXCHANGERULEZ5000! Or click the dice to generate a random code.">i</span></label>
					<?php $form->add_text_box( 'code', array( 'class' => 'emptycode' ) ); ?>
					<img class="dice" src="images/dice-t.png" />
				</div>
				
				<div class="clearfix"></div>
				<br>
				<div class="field amount">
					<label>Amount</label>
					<?php $form->add_text_box( 'amount-number' ); ?>
					<?php $form->add_drop_down( 'amount-type', array( '%' => __( '% Percent', 'LION' ), '$' => '$ USD' ) ); ?>
				</div>

				<div class="field date field-float">
					<label>Start Date</label>
					<?php $form->add_text_box( 'start-date', array( 'class' => 'datepicker' ) ); ?>
				</div>

				<div class="field date field-float">
					<label>End Date</label>
					<?php $form->add_text_box( 'end-date', array( 'class' => 'datepicker' ) ); ?>
				</div>

				<div class="clearfix"></div>

				<?php $form->add_submit( 'cancel', __( 'Cancel', 'LION' ) ); ?>
				<?php $form->add_submit( 'submit', __( 'Save', 'LION' ) ); ?>
			</div>
		</div>
		<?php
	$form->end_form();
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
