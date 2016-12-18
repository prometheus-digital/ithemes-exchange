<?php
/**
 * Functions / hooks only needed in the admin
 * @package IT_Exchange
 * @since 0.4.0
*/

/**
 * Enqueues CSS / JS on add / edit page
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_enqueue_js_css() {
	$screen = get_current_screen();

	// Abort if screen wasn't found
	if ( empty( $screen ) )
		return;

	// Abort if not adding, editing or on the coupons list screen.
	if ( 'exchange_page_it-exchange-edit-basic-coupon' == $screen->base || 'exchange_page_it-exchange-add-basic-coupon' == $screen->base ) {

		// JS
		$deps = array( 'jquery', 'jquery-ui-tooltip', 'jquery-ui-datepicker', 'jquery-ui-tabs', 'it-exchange-select2' );
		wp_enqueue_script( 'it-exchange-add-edit-coupon', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/add-edit-coupon.js', $deps );
		wp_localize_script( 'it-exchange-add-edit-coupon', 'IT_EXCHANGE', array(
			'productPlaceholder' => __( 'Select a product', 'it-l10n-ithemes-exchange' )
		) );

		// CSS
		$deps = array( 'it-exchange-select2' );
		wp_enqueue_style( 'it-exchange-add-edit-coupon', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/css/add-edit-coupon.css', $deps );
	} else if ( $screen->post_type === 'it_exchange_coupon' ) {

		$deps = array( 'jquery-ui-datepicker', 'jquery-ui-tooltip' );

		wp_enqueue_script( 'it-exchange-list-table-coupons', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/list-table.js', $deps );
		wp_enqueue_style( 'it-exchange-list-table-coupons', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/css/list-table.css' );
	}
}
add_action( 'admin_enqueue_scripts', 'it_exchange_basic_coupons_enqueue_js_css' );

/**
 * Adds Basic Coupons post type to list of post type to remove the quick edit
 *
 * @since 0.4.5
 *
 * @param array $post_types
 *
 * @return array list of post types
*/
function it_exchange_remove_quick_edit_from_basic_coupons( $post_types ) {
	$post_types[] = 'it_exchange_coupon';

	return $post_types;
}
add_filter( 'it_exchange_remove_quick_edit_from_post_types', 'it_exchange_remove_quick_edit_from_basic_coupons', 10 );

/**
 * Saves a coupon
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_save_coupon() {
	if ( empty( $_POST['it-exchange-basic-coupons-add-edit-coupon'] ) )
		return;

	// Redirect to All coupons if cancel button was submited
	if ( ! empty( $_POST['cancel'] ) ) {
		wp_safe_redirect( add_query_arg( array( 'post_type' => 'it_exchange_coupon' ), 'edit.php' ) );
		die();
	}

	$nonce = empty( $_POST['_wpnonce'] ) ? false : $_POST['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'it-exchange-basic-coupons-add-edit-coupon' ) )
		return;

	$data = ITForm::get_post_data();

	if ( ! it_exchange_basic_coupons_data_is_valid() )
		return;

	// Remove hidden field
	unset( $data['add-edit-coupon'] );

	// Convert name to post_title
	$data['post_title'] = $data['name'];
	$data['post_content'] = $data['code'];
	unset( $data['name'] );

	// Update message or added message
	$msg = empty( $data['ID'] ) ? 'added' : 'updated';

	// Convert code, amount-number, amount-type, start-date, end-date to meta
	$data['post_meta']['_it-basic-code']               = $data['code'];
	$data['post_meta']['_it-basic-amount-number']      = it_exchange_convert_to_database_number( $data['amount-number'] );
	$data['post_meta']['_it-basic-amount-type']        = $data['amount-type'];
	$data['post_meta']['_it-basic-start-date']         = ! empty( $data['start-date'] ) ? date( 'Y-m-d H:i:s', strtotime( $data['start-date'] ) ) : '';
	$data['post_meta']['_it-basic-end-date']           = ! empty( $data['end-date'] ) ? date( 'Y-m-d H:i:s', strtotime( $data['end-date'] ) ) : '';
	$data['post_meta']['_it-basic-apply-discount']     = $data['apply-discount'];
	$data['post_meta']['_it-basic-limit-quantity']     = $data['limit-quantity'];
	$data['post_meta']['_it-basic-allotted-quantity']  = $data['quantity'];
	$data['post_meta']['_it-basic-limit-product']      = $data['limit-product'];
	$data['post_meta']['_it-basic-product-categories'] = $data['product-category'];
	$data['post_meta']['_it-basic-product-id']         = $data['product-id'];
	$data['post_meta']['_it-basic-excluded-products']  = $data['excluded-products'];
	$data['post_meta']['_it-basic-sales-excluded']     = $data['sales-excluded'];
	$data['post_meta']['_it-basic-limit-frequency']    = $data['limit-frequency'];
	$data['post_meta']['_it-basic-frequency-times']    = $data['frequency-times'];
	$data['post_meta']['_it-basic-frequency-length']   = $data['frequency-length'];
	$data['post_meta']['_it-basic-frequency-units']    = $data['frequency-units'];
	$data['post_meta']['_it-basic-customer']           = $data['customer'];
	$data['post_meta']['_it-basic-limit-customer']     = $data['limit-customer'];
	unset( $data['code'] );
	unset( $data['amount-number'] );
	unset( $data['amount-type'] );
	unset( $data['start-date'] );
	unset( $data['end-date'] );
	unset( $data['limit-quantity'] );
	unset( $data['quantity'] );
	unset( $data['limit-product'] );
	unset( $data['product-id'] );
	unset( $data['limit-frequency'] );
	unset( $data['frequency-times'] );
	unset( $data['frequency-length'] );
	unset( $data['frequency-units'] );

	if ( empty( $data['ID'] ) ) {
		$data['post_meta']['_it-basic-quantity'] = $data['post_meta']['_it-basic-allotted-quantity'];
	} else {
		/** @var IT_Exchange_Cart_Coupon $coupon */
		$coupon = it_exchange_get_coupon( $data['ID'], 'cart' );

		$prev_allotted = $coupon->get_allotted_quantity();
	}

	/**
	 * Allow for addon's to save additional coupon data.
	 *
	 * @param $data array
	 */
	$data = apply_filters( 'it_exchange_basic_coupons_save_coupon', $data );

	if ( $post_id = it_exchange_add_coupon( $data ) ) {

		if ( isset( $prev_allotted ) ) {
			/** @var IT_Exchange_Cart_Coupon $coupon */
			$coupon = it_exchange_get_coupon( $post_id, 'cart' );

			if ( $prev_allotted !== $coupon->get_allotted_quantity() ) {
				$coupon->modify_quantity_available( $coupon->get_allotted_quantity() - $prev_allotted );
			}
		}

		/**
		 * Fires when a coupon is successfully saved.
		 *
		 * @param $post_id int
		 * @param $data    array
		 */
		do_action( 'it_exchange_basic_coupons_saved_coupon', $post_id, $data );
		wp_safe_redirect( add_query_arg( array( 'post_type' => 'it_exchange_coupon' ), get_admin_url() . 'edit.php' ) );
	}
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
	$data = ITForm::get_post_data();
	if ( empty( $data['name'] ) )
		it_exchange_add_message( 'error', __( 'Coupon Name cannot be left empty', 'it-l10n-ithemes-exchange' ) );
	if ( empty( $data['code'] ) )
		it_exchange_add_message( 'error', __( 'Coupon Code cannot be left empty', 'it-l10n-ithemes-exchange' ) );
	if ( empty( $data['amount-number'] ) )
		it_exchange_add_message( 'error', __( 'Coupon Discount cannot be left empty', 'it-l10n-ithemes-exchange' ) );
	if ( ! is_numeric( $data['amount-number'] ) || trim( $data['amount-number'] ) < 1 )
		it_exchange_add_message( 'error', __( 'Coupon Discount must be a postive number', 'it-l10n-ithemes-exchange' ) );
	if ( ! empty( $data['limit-quantity'] ) && ! is_numeric( $data['quantity'] ) )
		it_exchange_add_message( 'error', __( 'Available Coupons must be a number', 'it-l10n-ithemes-exchange' ) );
	if ( ! empty( $data['limit-product'] ) && empty( $data['product-id'] ) && empty( $data['excluded-products'] ) && empty( $data['product-category']) )
		it_exchange_add_message( 'error', __( 'Please select a product.', 'it-l10n-ithemes-exchange' ) );
	if ( ! empty( $data['limit-frequency'] ) && ! is_numeric( $data['frequency-times'] ) && ! is_numeric( $data['frequency-length'] ) )
		it_exchange_add_message( 'error', __( 'Please select a frequency limitation', 'it-l10n-ithemes-exchange' ) );

	do_action( 'it_exchange_basic_coupons_data_is_valid', $data );

	return ! it_exchange_has_messages( 'error' );
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
		$func = 'it_exchange_basic_coupons_print_add_edit_coupon_screen';
		add_submenu_page( 'it-exchange', __( 'Add Coupon', 'it-l10n-ithemes-exchange' ), __( 'Add Coupon', 'it-l10n-ithemes-exchange' ), it_exchange_get_admin_menu_capability( 'it-exchange-add-basic-coupon' ), $slug, $func );
	} else if ( ! empty( $_GET['page'] ) && 'it-exchange-edit-basic-coupon' == $_GET['page'] ) {
		$slug = 'it-exchange-edit-basic-coupon';
		$func = 'it_exchange_basic_coupons_print_add_edit_coupon_screen';
		add_submenu_page( 'it-exchange', __( 'Edit Coupon', 'it-l10n-ithemes-exchange' ), __( 'Edit Coupon', 'it-l10n-ithemes-exchange' ), it_exchange_get_admin_menu_capability( 'it-exchange-edit-basic-coupon' ), $slug, $func );
	}
	$url = add_query_arg( array( 'post_type' => 'it_exchange_coupon' ), 'edit.php' );
	add_submenu_page( 'it-exchange', __( 'Coupons', 'it-l10n-ithemes-exchange' ), __( 'Coupons', 'it-l10n-ithemes-exchange' ), it_exchange_get_admin_menu_capability( 'it-exchange-all-basic-coupons' ), $url );
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
	$pagenow   = empty( $GLOBALS['pagenow'] ) ? false : $GLOBALS['pagenow'];
	$post_type = empty( $_GET['post_type'] ) ? false : $_GET['post_type'];
	$post_id   = empty( $_GET['post'] ) ? false : $_GET['post'];
	$action    = empty( $_GET['action'] ) ? false : $_GET['action'];

	if ( ! $pagenow || ( 'post-new.php' != $pagenow && 'post.php' != $pagenow ) )
		return;

	// Redirect for add new screen
	if ( 'post-new.php' == $pagenow && 'it_exchange_coupon' == $post_type ) {
		wp_safe_redirect( add_query_arg( array( 'page' => 'it-exchange-add-basic-coupon' ), get_admin_url() . 'admin.php' ) );
		die();
	}

	// Redirect for edit screen
	if ( in_array( $action, array( 'delete', 'trash', 'untrash' ) ) )
		return;

	$coupon = it_exchange_get_coupon( $post_id );
	if ( 'post.php' == $pagenow ) {
		if ( $post_id && $coupon ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'it-exchange-edit-basic-coupon', 'post' => $post_id ), get_admin_url() . 'admin.php' ) );
			die();
		}
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
function it_exchange_basic_coupons_print_add_edit_coupon_screen() {
	// Setup add / edit variables
	$post_id = empty( $_GET['post'] ) ? false : $_GET['post'];
	$heading = $post_id ? __( 'Edit Coupon', 'it-l10n-ithemes-exchange' ) : __( 'Add Coupon', 'it-l10n-ithemes-exchange' );
	$form_action = $post_id ? add_query_arg( array( 'page' => 'it-exchange-edit-basic-coupon', 'post' => $post_id ), get_admin_url() . 'admin.php' ) : add_query_arg( array( 'page' => 'it-exchange-add-basic-coupon' ), get_admin_url() . 'admin.php' );

	$coupon = it_exchange_get_coupon( $post_id );

	// Set form values
	if ( $coupon instanceof IT_Exchange_Cart_Coupon ) {

		$amount =  $coupon->get_amount_number();

		if ( IT_Exchange_Cart_Coupon::TYPE_FLAT == $coupon->get_amount_type() ) {
			$amount = it_exchange_format_price( $amount, false );
		}

		$values['name']              = $coupon->get_title( true );
		$values['code']              = $coupon->get_code();
		$values['amount-number']     = $amount;
		$values['amount-type']       = $coupon->get_amount_type();
		$values['start-date']        = $coupon->get_start_date() ? $coupon->get_start_date()->format( 'm/d/Y' ) : '';
		$values['end-date']          = $coupon->get_end_date() ? $coupon->get_end_date()->format( 'm/d/Y' ) : '';
		$values['apply-discount']    = $coupon->get_application_method();
		$values['limit-quantity']    = $coupon->is_quantity_limited();
		$values['quantity']          = $coupon->get_allotted_quantity();
		$values['limit-product']     = $coupon->is_product_limited();
		$values['product-id']        = $coupon->get_limited_products();
		$values['excluded-products'] = $coupon->get_excluded_products();
		$values['product-category']  = $coupon->get_product_categories( true );
		$values['sales-excluded']    = $coupon->is_sale_item_excluded();
		$values['limit-frequency']   = $coupon->is_frequency_limited();
		$values['frequency-times']   = $coupon->get_frequency_times();
		$values['frequency-length']  = $coupon->get_frequency_length();
		$values['frequency-units']   = $coupon->get_frequency_units();
		$values['limit-customer']    = $coupon->is_customer_limited();
		$values['customer']          = $coupon->get_customer() ? $coupon->get_customer()->id : 0;
	}

	$errors = it_exchange_get_messages( 'error' );
	if ( ! empty( $errors ) ) {
		foreach( $errors as $error ) {
			ITUtility::show_error_message( $error );
		}
	} else if ( ! empty( $_GET['added'] ) ) {
		ITUtility::show_status_message( __( 'Coupon Added', 'it-l10n-ithemes-exchange' ) );
	} else if ( ! empty( $_GET['updated'] ) ) {
		ITUtility::show_status_message( __( 'Coupon Updated', 'it-l10n-ithemes-exchange' ) );
	}

	$form_values  = empty( $values ) ? ITForm::get_post_data() : $values;
	$form_values  = ! empty( $errors ) ? ITForm::get_post_data() : $form_values;
	$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-basic-coupons' ) );
	$form_options = array(
		'id'      => apply_filters( 'it-exchange-basic-coupons_form_id', 'it-exchange-basic-coupons' ),
		'enctype' => apply_filters( 'it-exchange-basic-coupons_enctype', false ),
		'action'  => $form_action,
	);
	?>
	<div class="wrap">
		<?php
		ITUtility::screen_icon( 'it-exchange-coupons' );
		echo '<h2>' . $heading . '</h2>';
		$form->start_form( $form_options, 'it-exchange-basic-coupons-add-edit-coupon' );

		if ( $post_id )
			$form->add_hidden( 'ID', $post_id );
		$form->add_hidden( 'add-edit-coupon', true );
		?>
		<div class="it-exchange-add-basic-coupon">
			<div class="fields">

				<div class="field">
					<label for="name"><?php _e( 'Name', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'What do you want to call this coupon? This is just for your reference.', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
					<?php $form->add_text_box( 'name' ); ?>
				</div>
				<div class="field coupon-code">
					<label for="code"><?php _e( 'Code', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'Try something cool like EXCHANGERULEZ5000! Or click the dice to generate a random code.', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
					<?php $form->add_text_box( 'code', array( 'class' => 'emptycode' ) ); ?>
					<a href class="dice" title="Generate a random code."><img src="<?php echo esc_attr( ITUtility::get_url_from_file( dirname( __FILE__ ) ) ); ?>/images/dice-t.png" /></a>
				</div>

				<div class="field amount">
					<label for="amount-number"><?php _e( 'Amount', 'it-l10n-ithemes-exchange' ); ?></label>
					<?php $form->add_text_box( 'amount-number', array( 'type' => 'number' ) ); ?>
					<?php
					$settings = it_exchange_get_option( 'settings_general' );
					$currency = $settings['default-currency'];
					$symbol   = it_exchange_get_currency_symbol( $currency );
					?>
					<?php $form->add_drop_down( 'amount-type', array( '%' => __( '% Percent', 'it-l10n-ithemes-exchange' ), 'amount' => $symbol . ' ' . $currency ) ); ?>
				</div>

				<?php do_action( 'it_exchange_basics_coupon_coupon_edit_screen_begin_fields', $form ); ?>

				<div id="it-exchange-advanced-tabs">
					<ul id="it-exchange-advanced-tab-nav">
						<li><a href="#general"><?php _e( 'General', 'it-l10n-ithemes-exchange' ); ?></a></li>
						<li><a href="#usage"><?php _e( 'Usage', 'it-l10n-ithemes-exchange' ); ?></a></li>
						<li><a href="#customer"><?php _e( 'Customers', 'it-l10n-ithemes-exchange' ); ?></a></li>
						<li><a href="#product"><?php _e( 'Product', 'it-l10n-ithemes-exchange' ); ?></a></li>

						<?php do_action( 'it_exchange_basic_coupons_coupon_edit_tabs', $form ); ?>
					</ul>
					<div id="general">
						<div class="inner">
							<div class="inside">
								<div class="field date" data-alert="<?php _e( 'Please select an end date that is after the start date.', 'it-l10n-ithemes-exchange' ); ?>">
									<div class="start-date">
										<label for="start-date"><?php _e( 'Start Date', 'it-l10n-ithemes-exchange' ); ?></label>
										<?php $form->add_text_box( 'start-date', array( 'class' => 'datepicker', 'data-append' => 'end-date' ) ); ?>
									</div>
									<div class="end-date">
										<label for="end-date"><?php _e( 'End Date', 'it-l10n-ithemes-exchange' ); ?></label>
										<?php $form->add_text_box( 'end-date', array( 'class' => 'datepicker', 'data-append' => 'start-date' ) ); ?>
									</div>
								</div>

								<div class="field discount-method">
									<label for="discount-method"><?php _e( 'Apply Discount', 'it-l10n-ithemes-exchange' ); ?></label>
									<?php $form->add_drop_down( 'apply-discount', array( 'cart' => __( 'Entire Cart', 'it-l10n-ithemes-exchange' ), 'product' => __( 'Per-product', 'it-l10n-ithemes-exchange' ) ) ); ?>
								</div>

								<?php do_action( 'it_exchange_basic_coupons_coupon_edit_tab_general', $form ); ?>
							</div>
						</div>
					</div>

					<div id="usage">
						<div class="inner">
							<div class="inside">
								<div class="field limit-quantity">
									<?php $form->add_check_box( 'limit-quantity' ); ?>
									<label for="limit-quantity">
										<?php _e( 'Limit number of coupons', 'it-l10n-ithemes-exchange' ); ?>
										<span class="tip" title="<?php esc_attr_e( __( 'Check to limit the number of times this coupon can be used. This limit is applied globally.', 'it-l10n-ithemes-exchange' ) ); ?>">i</span>
									</label>
								</div>

								<div class="field quantity">
									<?php $form->add_text_box( 'quantity', array( 'type' => 'number' ) ); ?>
									<span class="tip" title="<?php _e( 'How many times can this coupon be used before it is disabled?', 'it-l10n-ithemes-exchange' ); ?>">i</span>

									<?php if ( $coupon && $coupon->get_total_uses() ): ?>
										<p class="description">
											<?php printf( _n(
												'This coupon has been used %d time.',
												'This coupon has been used %d times.',
												$coupon->get_total_uses(),
												'it-l10n-ithemes-exchange'),
												$coupon->get_total_uses()
											); ?>
										</p>
									<?php endif; ?>
								</div>

								<?php do_action( 'it_exchange_basic_coupons_coupon_edit_tab_usage', $form ); ?>
							</div>
						</div>
					</div>

					<div id="customer">
						<div class="inner">
							<div class="inside">
								<div class="field limit-customer">
									<?php $form->add_check_box( 'limit-customer' ); ?>
									<label for="limit-customer">
										<?php _e( 'Limit to a specific customer', 'it-l10n-ithemes-exchange' ); ?>
										<span class="tip" title="<?php esc_attr_e( __( 'Check to limit the coupon discount to a specific customer.', 'it-l10n-ithemes-exchange' ) ); ?>">i</span>
									</label>
								</div>

								<div class="field customer">
									<?php
									$customer_options = array( 0 => __( 'Select a customer', 'it-l10n-ithemes-exchange' ) );
									$customers        = get_users( array( 'number' => -1 ) );
									foreach( (array) $customers as $customer ) {
										$customer_options[$customer->ID] = $customer->display_name;
									}
									?>
									<?php $form->add_drop_down( 'customer', $customer_options ); ?>
								</div>

								<div class="field limit-frequency">
									<?php $form->add_check_box( 'limit-frequency' ); ?>
									<label for="limit-frequency">
										<?php _e( 'Limit frequency of use per customer', 'it-l10n-ithemes-exchange' ); ?>
										<span class="tip" title="<?php esc_attr_e( __( 'Check to limit the number of times each customer can use the coupon during a specified time frame', 'it-l10n-ithemes-exchange' ) ); ?>">i</span>
									</label>
								</div>

								<div class="field frequency-limitations">
									<?php
									$thirty = array();
									for( $i=1;$i<=30;$i++ ) {
										$thirty[$i] = $i;
									}
									$frequency_times  = apply_filters( 'it_exchange_limit_coupon_freqency_times_options', $thirty );
									$frequency_length = apply_filters( 'it_exchange_limit_coupon_freqency_length_options', $thirty );
									$frequency_units  = array( 'day' => __( 'Day(s)', 'it-l10n-ithemes-exchange' ), 'week' =>  __( 'Week(s)', 'it-l10n-ithemes-exchange' ), 'year' => __( 'Year(s)', 'it-l10n-ithemes-exchange' ) );
									_e( 'Limit this coupon to ', 'it-l10n-ithemes-exchange' );
									$form->add_drop_down( 'frequency-times', $frequency_times );
									_e( ' use(s) per customer for every ', 'it-l10n-ithemes-exchange' );
									$form->add_drop_down( 'frequency-length', $frequency_length );
									$form->add_drop_down( 'frequency-units', $frequency_units );
									?>
								</div>

								<?php do_action( 'it_exchange_basic_coupons_coupon_edit_tab_customer', $form ); ?>
							</div>
						</div>
					</div>
					<div id="product">
						<div class="inner">
							<div class="inside">
								<div class="field limit-product">
									<?php $form->add_check_box( 'limit-product' ); ?>
									<label for="limit-product">
										<?php _e( 'Limit to certain products', 'it-l10n-ithemes-exchange' ); ?>
										<span class="tip" title="<?php esc_attr_e( __( 'Check to limit the discount to a specific product\'s price <em>instead</em> of the cart total.', 'it-l10n-ithemes-exchange' ) ); ?>">i</span>
									</label>
								</div>

								<?php if ( taxonomy_exists( 'it_exchange_category' ) && ( $terms = get_terms( 'it_exchange_category' ) ) ): ?>

									<div class="field product-category">
										<label for="product-category"><?php _e( 'Product Categories', 'it-l10n-ithemes-exchange' ); ?></label>
										<?php
										$category_options = array();

										foreach( (array) $terms as $term ) {
											$category_options[ $term->term_id ] = $term->name;
										}
										?>
										<?php $form->add_drop_down( 'product-category[]', array( 'value' => $category_options, 'multiple' => true ) ); ?>
									</div>

								<?php endif; ?>

								<div class="field product-id">
									<label for="product-id"><?php _e( 'Included Products', 'it-l10n-ithemes-exchange' ); ?></label>
									<?php
									$product_options = array();
									$products        = it_exchange_get_products( array( 'show_hidden' => true, 'posts_per_page' => -1 ) );
									foreach( (array) $products as $id => $product ) {
										$product_options[$product->ID] = $product->post_title;
									}
									?>
									<?php $form->add_drop_down( 'product-id[]', array( 'value' => $product_options, 'multiple' => true ) ); ?>
								</div>

								<div class="field excluded-products">
									<label for="excluded-products"><?php _e( 'Excluded Products', 'it-l10n-ithemes-exchange' ); ?></label>
									<?php
									$product_options = array();
									$products        = it_exchange_get_products( array( 'show_hidden' => true, 'posts_per_page' => -1 ) );
									foreach( (array) $products as $id => $product ) {
										$product_options[$product->ID] = $product->post_title;
									}
									?>
									<?php $form->add_drop_down( 'excluded-products[]', array( 'value' => $product_options, 'multiple' => true ) ); ?>
								</div>

								<div class="field sales-excluded">
									<?php $form->add_check_box( 'sales-excluded' ); ?>
									<label for="sales-excluded">
										<?php _e( 'Exclude sale items', 'it-l10n-ithemes-exchange' ); ?>
									</label>
								</div>

								<?php do_action( 'it_exchange_basic_coupons_coupon_edit_tab_product', $form ); ?>
							</div>
						</div>
					</div>

					<?php do_action( 'it_exchange_basic_coupons_coupon_edit_tabs_end', $form ); ?>
				</div>
				<?php do_action( 'it_exchange_basics_coupon_coupon_edit_screen_end_fields', $form ); ?>

				<div class="field">
					<?php $form->add_submit( 'cancel', array( 'class' => 'button-large button', 'value' => __( 'Cancel', 'it-l10n-ithemes-exchange' ) ) ); ?>
					<?php $form->add_submit( 'submit', array( 'class' => 'button-large button-primary button', 'value' => __( 'Save', 'it-l10n-ithemes-exchange' ) ) ); ?>
				</div>
			</div>
		</div>
		<?php
		$form->end_form();
		?>
	</div>
	<?php
}

/**
 * Remove Custom add coupon and edit coupon links from submenu
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_basic_coupons_remove_submenu_links() {
	if ( ! empty( $GLOBALS['submenu']['it-exchange'] ) ) {
		foreach( $GLOBALS['submenu']['it-exchange'] as $key => $sub ) {
			if ( 'it-exchange-add-basic-coupon' == $sub[2] || 'it-exchange-edit-basic-coupon' == $sub[2] ) {
				// Remove the extra coupons submenu item
				unset( $GLOBALS['submenu']['it-exchange'][$key] );
				// Mark the primary coupons submenu item as current
				$GLOBALS['submenu_file'] = 'edit.php?post_type=it_exchange_coupon';
			}
		}
	}
}
add_action( 'admin_head', 'it_exchange_basic_coupons_remove_submenu_links' );

/**
 * Adds the coupon specific columns to the View All Coupons table
 *
 * @since 0.4.0
 * @param array $existing  exisiting columns array
 * @return array  modified columns array
*/
function it_exchange_basic_coupons_product_columns( $existing ) {
	$columns['cb']    = '<input type="checkbox" />';
	$columns['title'] = __( 'Title', 'it-l10n-ithemes-exchange' );
	$columns['it_exchange_coupon_code']       = __( 'Coupon Code', 'it-l10n-ithemes-exchange' );
	$columns['it_exchange_coupon_discount']   = __( 'Discount', 'it-l10n-ithemes-exchange' );
	$columns['it_exchange_coupon_date']       = __( 'Available', 'it-l10n-ithemes-exchange' );
	$columns['it_exchange_coupon_quantity']   = __( 'Uses', 'it-l10n-ithemes-exchange' );
	$columns['it_exchange_coupon_product_id'] = __( 'Product', 'it-l10n-ithemes-exchange' );
	$columns['it_exchange_coupon_customer']   = __( 'Customer', 'it-l10n-ithemes-exchange' );

	return $columns;
}
add_filter( 'manage_edit-it_exchange_coupon_columns', 'it_exchange_basic_coupons_product_columns', 999 );

/**
 * Makes the custom columns added above sortable
 *
 * @since 0.4.0
 * @param array $sortables  existing sortable columns
 * @return array  modified sortable columnns
*/
function it_exchange_basic_coupons_sortable_columns( $sortables ) {
	$sortables['it_exchange_coupon_code']       = 'it-exchange-coupon-code';
	$sortables['it_exchange_coupon_discount']   = 'it-exchange-coupon-discount';

	return $sortables;
}
add_filter( 'manage_edit-it_exchange_coupon_sortable_columns', 'it_exchange_basic_coupons_sortable_columns' );

/**
 * Adds the data to the custom columns
 *
 * @since 0.4.0
 * @param string $column  Column title
 *
 * @return void
*/
function it_exchange_basic_coupons_custom_column_info( $column ) {
	global $post;

	$coupon = it_exchange_get_coupon( $post );

	if ( ! $coupon instanceof IT_Exchange_Cart_Coupon ) {
		return;
	}

	$format = str_replace( 'F', 'M', get_option( 'date_format' ) );

	switch( $column ) {
		case 'it_exchange_coupon_code':
			esc_attr_e( (string) $coupon );
			break;
		case 'it_exchange_coupon_discount':
			echo esc_attr( it_exchange_get_coupon_discount_label( $coupon ) );
			break;
		case 'it_exchange_coupon_date':

			$start = $coupon->get_start_date();
			$end   = $coupon->get_end_date();

			if ( $start && $end ) {

				$same_year = $start->format( 'Y' ) === $end->format( 'Y' );
				$same_month = $start->format( 'n' ) === $end->format( 'n' ) && $same_year;

				if ( $same_month ) {
					$start = $start->format( 'M d' );
					$end = $end->format( 'd, Y' );
				} else if ( $same_year ) {
					$start = $start->format( 'M d' );
					$end = $end->format( 'M d, Y' );
				} else {
					$start = $start->format( 'M d, Y' );
					$end   = $end->format( 'M d, Y' );
				}

				echo esc_attr( $start . ' – ' . $end );
			} elseif ( $start ) {

				$now = new DateTime();

				if ( $start < $now ) {
					echo esc_attr( sprintf( __( 'Started %s', 'it-l10n-ithemes-exchange' ), $start->format( $format ) ) );
				} else {
					echo esc_attr( sprintf( __( 'Starts %s', 'it-l10n-ithemes-exchange' ), $start->format( $format ) ) );
				}
			} elseif ( $end ) {

				$now = new DateTime();

				if ( $end < $now ) {
					echo esc_attr( sprintf( __( 'Ended %s', 'it-l10n-ithemes-exchange' ), $end->format( $format ) ) );
				} else {
					echo esc_attr( sprintf( __( 'Ends %s', 'it-l10n-ithemes-exchange' ), $end->format( $format ) ) );
				}
			} else {
				echo '&ndash;';
			}

			break;
		case 'it_exchange_coupon_quantity':

			if ( ! $coupon->is_quantity_limited() ) {
				$out_of = '&infin;';
			} else {
				$out_of = $coupon->get_allotted_quantity();
			}

			printf( '%d &frasl; %s', $coupon->get_total_uses(), $out_of );

			break;
		case 'it_exchange_coupon_product_id':

			if ( ! $coupon->is_product_limited() ) {
				esc_attr_e( 'All Products', 'it-l10n-ithemes-exchange' );
			} else {

				$category_names = array();

				foreach ( $coupon->get_product_categories() as $category ) {
					$category_names[] = $category->name;
				}

				$included_names = array();

				foreach ( $coupon->get_limited_products() as $product ) {
					$included_names[] = $product->post_title;
				}

				$excluded_names = array();

				foreach ( $coupon->get_excluded_products() as $product ) {
					$excluded_names[] = $product->post_title;
				}

				if ( $included_names && ! $category_names && ! $excluded_names ) {

					if ( count( $included_names ) === 1) {
						$label = reset( $included_names );
					} else {
						$label = __( 'Multiple', 'it-l10n-ithemes-exchange' );
						$tip = implode( ', ', $included_names );
					}
				} else if ( $category_names && ! $included_names && ! $excluded_names ) {

					if ( count( $category_names ) === 1 ) {
						$label = sprintf( __( '%s category', 'it-l10n-ithemes-exchange' ), reset( $category_names ) );
					} else {
						$label = __( 'Categories', 'it-l10n-ithemes-exchange' );
						$tip = implode( ', ', $category_names );
					}
				} else if ( $excluded_names && ! $included_names && ! $category_names ) {

					if ( count( $excluded_names ) === 1 ) {
						$label = sprintf( __( 'Excluded: %s', 'it-l10n-ithemes-exchange' ), reset( $excluded_names ) );
					} else {
						$label = __( 'Excluded', 'it-l10n-ithemes-exchange' );
						$tip = implode( ', ', $excluded_names );
					}
				} else {

					$label = __( 'Complex', 'it-l10n-ithemes-exchange' );

					$tip = '';

					if ( $category_names ) {
						$tip .= sprintf( __( 'Categories: %s.', 'it-l10n-ithemes-exchange' ), implode( ', ', $category_names ) ) . ' ';
					}

					if ( $included_names ) {
						$tip .= sprintf( __( 'Included: %s.', 'it-l10n-ithemes-exchange' ), implode( ', ', $included_names ) ) . ' ';
					}

					if ( $excluded_names ) {
						$tip .= sprintf( __( 'Excluded: %s.', 'it-l10n-ithemes-exchange' ), implode( ', ', $excluded_names ) );
					}
				}

				if ( isset( $tip, $label ) ) {
					echo "{$label} <span class='tip' title='{$tip}'>i</span>";
				} else {
					echo $label;
				}
			}
			break;
		case 'it_exchange_coupon_customer':

			if ( ! $coupon->is_customer_limited() ) {
				$customer = __( 'Any Customer', 'it-l10n-ithemes-exchange' );
			} else {
				$customer = $coupon->get_customer() ? $coupon->get_customer()->wp_user->display_name : '';
			}

			esc_attr_e( $customer );
			break;
	}
}
add_filter( 'manage_it_exchange_coupon_posts_custom_column', 'it_exchange_basic_coupons_custom_column_info' );

/**
 * Modify sort of coupons in edit.php for custom columns
 *
 * @since 0.4.0F
 *
 * @param array $request original request
 *
 * @return array
 */
function it_exchange_basic_coupons_modify_wp_query_request_on_edit_php( $request ) {
	global $hook_suffix;

	if ( 'edit.php' === $hook_suffix ) {
		if ( 'it_exchange_coupon' === $request['post_type'] ) {

			if ( isset( $request['orderby'] ) ) {
				switch ( $request['orderby'] ) {
					case 'it-exchange-coupon-code' :
						$request['orderby']  = 'meta_value';
						$request['meta_key'] = '_it-basic-code';
						break;
					case 'it-exchange-coupon-discount':
						$request['orderby']  = 'meta_value_num';
						$request['meta_key'] = '_it-basic-amount-number';
						break;
				}
			}

			$meta_query = ! empty( $request['meta_query'] ) ? $request['meta_query'] : array();
			$meta_query['relation'] = 'AND';

			if ( ! empty( $_GET['start_date'] ) && ( $t = strtotime( $_GET['start_date'] ) ) ) {
				$meta_query[] = array(
					'key'       => '_it-basic-start-date',
					'value'     => date( 'Y-m-d', $t ),
					'compare'   => '>',
					'type'      => 'DATETIME'
				);
			}

			if ( ! empty( $_GET['end_date'] ) && ( $t = strtotime( $_GET['end_date'] ) ) ) {
				$meta_query[] = array(
					'key'       => '_it-basic-end-date',
					'value'     => date( 'Y-m-d', $t ),
					'compare'   => '<',
					'type'      => 'DATETIME'
				);
			}

			$request['meta_query'] = $meta_query;
		}
	}

	return $request;
}
add_filter( 'request', 'it_exchange_basic_coupons_modify_wp_query_request_on_edit_php' );

/**
 * Register our pages as an exchange pages so that exchange CSS class is applied to admin body
 *
 * @since 0.4.17
 *
 * @param array $pages existing pages
 * @return array
*/
function it_exchange_basic_coupons_register_exchange_admin_page( $pages ) {
	$pages[] = 'it-exchange-add-basic-coupon';

	return $pages;
}
add_filter( 'it_exchange_admin_pages', 'it_exchange_basic_coupons_register_exchange_admin_page' );

/**
 * Add a coupon availability filter to the coupons list table.
 *
 * @since 1.33
 *
 * @param string $post_type
 */
function it_exchange_basic_coupons_add_availability_filter( $post_type ) {

	if ( $post_type !== 'it_exchange_coupon' ) {
		return;
	}

	$s = isset( $_GET['start_date'] ) ? $_GET['start_date'] : '';
	$e = isset( $_GET['end_date'] ) ? $_GET['end_date'] : '';

	$start = "<input type=\"text\" class=\"datepicker\" name=\"start_date\" id=\"start-date\" value=\"{$s}\">";
	$end = "<input type=\"text\" class=\"datepicker\" name=\"end_date\" id=\"end-date\" value=\"{$e}\">";
	?>

	<label for="start-date" class="screen-reader-text"><?php _e( 'Availability Start Date', 'it-l10n-ithemes-exchange' ); ?></label>
	<label for="end-date" class="screen-reader-text"><?php _e( 'Availability End Date', 'it-l10n-ithemes-exchange' ); ?></label>

	<?php printf( __( 'Available between %s and %s', 'it-l10n-ithemes-exchange' ), $start, $end ); ?>

<?php
}

add_action( 'restrict_manage_posts', 'it_exchange_basic_coupons_add_availability_filter' );