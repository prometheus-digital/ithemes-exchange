<?php
/**
 * Callback function for add-on settings
 *
 * We are using this differently than most add-ons. We want the gear
 * to appear on the add-ons screen so we are registering the callback.
 * It will be intercepted though if the user clicks on it and redirected to 
 * The Exchange settings --> shipping tab.
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_shipping_settings_callback() {
	// Store Owners should never arrive here. Add a link just in case the do somehow
	?>
	<div class="wrap">
		<?php screen_icon( 'it-exchange' ); ?>
		<h2><?php _e( 'Shipping', 'LION' ); ?></h2>
		<?php
		$url = add_query_arg( array( 'page' => 'it-exchange-settings', 'tab' => 'shipping' ), esc_url( admin_url( 'admin.php' ) ) );
		?><p><?php printf( __( 'Settings are located in the %sShipping tab%s on the Exchange Settings page.', 'LION' ), '<a href="' . $url . '">', '</a>' ); ?></p>
	</div>
	<?php
}

function it_exchange_addon_shipping_print_settings_tab_link( $current_tab ) {
	$active = 'shipping' == $current_tab ? 'nav-tab-active' : '';
	?><a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-exchange-settings&tab=shipping' ); ?>"><?php _e( 'Shipping', 'LION' ); ?></a><?php
}
add_action( 'it_exchange_print_general_settings_tab_links', 'it_exchange_addon_shipping_print_settings_tab_link' );

function it_exchange_addon_shipping_print_settings_tab() {
	$settings = it_exchange_get_option( 'addon_shipping', true );

	if ( it_exchange_has_messages( 'notice' ) ) {
		foreach( (array) it_exchange_get_messages( 'notice' ) as $message ) {
			ITUtility::show_status_message( $message );
		}
	}
	if ( it_exchange_has_messages( 'error' ) ) {
		foreach( (array) it_exchange_get_messages( 'error' ) as $message ) {
			ITUtility::show_error_message( $message );
		}
	}
	?>
	<div class="wrap">

		<?php $GLOBALS['IT_Exchange_Admin']->print_general_settings_tabs(); ?>

		<?php it_exchange_print_shipping_provider_settings_tabs(); ?>

		<?php 
		$provider_settings_printed = false;
		if ( ! empty( $_GET['provider'] ) && it_exchange_is_shipping_provider_registered( $_GET['provider'] ) ) {
			include_once( dirname( __FILE__ ) . '/class-provider-settings-page.php' );
			$provider_settings = new IT_Exchange_Provider_Settings_Page( $_GET['provider'] );
			if ( is_callable( array( $provider_settings, 'print_settings_page' ) ) ) {
				$provider_settings->print_settings_page();
				$provider_settings_printed = true;
			}
		}
		?>

		<?php if ( ! $provider_settings_printed ) : ?>
			<?php
			$options = array(
				'prefix'      => 'addon-shipping-general',
				'form-fields' => array(
					array(
						'type'  => 'heading',
						'label' => __( 'General Shipping Settings', 'LION' ),
						'slug'  => 'general-shipping-label',
					),
					array(
						'type'    => 'text_box',
						'label'   => __( 'Products Ship From', 'LION' ),
						'slug'    => 'product-ships-from-address1',
						'tooltip' => __( 'The default from address used when shipping your products.', 'LION' ),
						'default' => '',
						'options' => array(
							'class'       => 'large-text',
							'placeholder' => __( 'Address 1', 'LION' ),
						),
					),
					array(
						'type'    => 'text_box',
						'label'   => '',
						'slug'    => 'product-ships-from-address2',
						'default' => '',
						'options' => array(
							'class'       => 'large-text',
							'placeholder' => __( 'Address 2', 'LION' ),
						),
					),
					array(
						'type'    => 'text_box',
						'label'   => '',
						'slug'    => 'product-ships-from-city',
						'default' => '',
						'options' => array(
							'class'       => 'large-text',
							'placeholder' => __( 'City', 'LION' ),
						),
					),
					array(
						'type'    => 'drop_down',
						'label'   => '',
						'slug'    => 'product-ships-from-country',
						'default' => '',
						'options' => array(
							it_exchange_get_data_set( 'countries' ),	
						),
					),
					array(
						'type'    => 'drop_down',
						'label'   => '',
						'slug'    => 'product-ships-from-states',
						'default' => '',
						'options' => array(
							it_exchange_get_data_set( 'states', array( 'country' => 'US' ) ),	
						),
					),
					array(
						'type'    => 'text_box',
						'label'   => '',
						'slug'    => 'product-ships-from-zip',
						'default' => '',
						'options' => array(
							'class'       => 'normal-text',
							'placeholder' => __( 'Zip', 'LION' ),
						),
					),
					array(
						'type'    => 'yes_no_drop_down',
						'label'   => __( 'Can products override default ships from address?', 'LION' ),
						'slug'    => 'products-can-override-ships-from',
						'tooltip' => __( 'Selecting "yes" will place these fields on the Add/Edit product screen.', 'LION' ),
						'default' => '',
					),
				),
			);
			it_exchange_print_settings_form( $options );
			?>
		<?php endif; ?>
	</div>
	<?php
}

function oldcode() {
	if ( 'just creating an opening if for bottom closing if' ) : 
			$form_values  = ! it_exchange_has_messages( 'error' ) ? $settings : ITForm::get_post_data();
			$form_options = array(
				'id'      => 'it-exchange-add-on-shipping-settings',
				'enctype' => false,
				'action'  => add_query_arg( array( 'page' => 'it-exchange-settings', 'tab' => 'shipping' ), admin_url( 'admin' ) ),
			);
			$form = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-shipping' ) );
			$form->start_form( $form_options, 'it-exchange-shipping-settings' );
			?>
			<table class="form-table">
				<?php do_action( 'it_exchange_general_settings_shipping_top' ); ?>
				<tr valign="top">
					<th scope="row"><strong><?php _e( 'General Shipping Settings', 'LION' ); ?></strong></th>
					<td></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="product-ships-from"><?php _e( 'Products Ship From', 'LION' ) ?></label></th>
					<td>
						<p><?php $form->add_text_box( 'product-ships-from-address1', array( 'class' => 'large-text', 'placeholder' => __( 'Address 1', 'LION' ) ) ); ?></p>
						<p><?php $form->add_text_box( 'product-ships-from-address2', array( 'class' => 'large-text', 'placeholder' => __( 'Address 2', 'LION' ) ) ); ?></p>
						<p>
							<?php $form->add_text_box( 'product-ships-from-city', array( 'class' => 'large-text', 'placeholder' => __( 'City', 'LION' ) ) ); ?>
							<?php 
							$country = $form->get_option( 'product-ships-from-country' );
							$states  = it_exchange_get_data_set( 'states', array( 'country' => $country ) );
							if ( ! empty( $states ) ) { 
								$form->add_drop_down( 'company-base-state', $states );
							} else {
								$form->add_text_box( 'company-base-state', array( 'class' => 'small-text', 'maxlength' => 3, 'placeholder' => __( 'State', 'LION' ) ) ); ?>
								<span class="description"><?php printf( __( 'Please use the 2-3 character %sISO abbreviation%s for country subdivisions', 'LION' ), '<a href="http://en.wikipedia.org/wiki/ISO_3166-2" target="_blank">', '</a>' ); ?></span><?php
							}
							?> 
						</p>
						<p><?php $form->add_drop_down( 'product-ships-from-country', it_exchange_get_data_set( 'countries' ) ); ?></p>
						<p><?php $form->add_text_box( 'product-ships-from-zip', array( 'class' => 'large-text', 'placeholder' => __( 'Zip / Postal Code', 'LION' ) ) ); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="products-can-override-ships-from"><?php _e( 'Can Products Override Ships From Address?', 'LION' ) ?></label></th>
					<td>
						<?php $form->add_yes_no_drop_down( 'products-can-override-ships-from' ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="address-format"><?php _e( 'Address Format', 'LION' ) ?></label></th>
					<td>
						<?php $form->add_drop_down( 'address-format', it_exchange_get_data_set( 'address-formats' ) ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="measurements-format"><?php _e( 'Measurements Format', 'LION' ) ?></label></th>
					<td>
						<?php $form->add_drop_down( 'measurements-format', it_exchange_get_data_set( 'measurement-formats' ) ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="product-types"><?php _e( 'Product Types with Shipping', 'LION' ) ?></label></th>
					<td>
						<?php foreach( (array) it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) ) as $product_type => $attributes ) {
							$form->add_check_box( 'product-types[' . $product_type . ']' );
							echo '&nbsp;' . $attributes['name'] . '<br />';
						} ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="default-shipping-method"><?php _e( 'Default Shipping Method', 'LION' ) ?></label></th>
					<td>
						<?php if ( count( it_exchange_get_enabled_shipping_methods() ) > 1 ) : ?>
							<?php $form->add_drop_down( 'default-shipping-method', it_exchange_get_enabled_shipping_methods() ); ?>
						<?php else : ?>
							<?php $form->add_hidden( 'default-shipping-method', 'exchange-standard-shipping' ); ?>
							Exchange - <?php _e( 'Standard Shipping', 'LION' ); ?>
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<p class="submit">
				<?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'LION' ), 'class' => 'button button-primary button-large' ) ); ?>
				</p>
			<?php $form->end_form(); ?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Register the callback for the settings page
 *
 * I hate that this was setup like this. Seems like an uneeded function
 *
 * @since 1.0.0
 *
 * @return string the callback
*/
function it_exchange_addon_shipping_register_settings_tab_callback() {
	return 'it_exchange_addon_shipping_print_settings_tab';
}
add_filter( 'it_exchange_general_settings_tab_callback_shipping', 'it_exchange_addon_shipping_register_settings_tab_callback' );

/**
 * Save settings
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_addon_save_shipping_settings() {
	$defaults = it_exchange_get_option( 'addon_shipping' );
	$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

	// Return if not on our page or POST isn't set.
	if ( empty( $_POST ) || empty( $_GET['page'] ) || 'it-exchange-settings' != $_GET['page'] || empty( $_GET['tab'] ) || 'shipping' != $_GET['tab'] )
		return;

	// Check nonce
	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-shipping-settings' ) ) {
		it_exchange_add_message( 'error', __( 'Error. Please try again', 'LION' ) );
		return;
	}

	it_exchange_save_option( 'addon_shipping', $new_values );
	it_exchange_add_message( 'notice', __( 'Settings Saved', 'LION' ) );
}
add_action( 'admin_init', 'it_exchange_addon_save_shipping_settings' );
