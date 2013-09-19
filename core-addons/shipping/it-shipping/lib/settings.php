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
				'prefix'       => 'addon-shipping-general',
				'form-options' => array(
					'action' => add_query_arg( array( 'page' => 'it-exchange-settings', 'tab' => 'shipping' ), admin_url( 'admin' ) ),
				),
				'country-states-js' => array(
					'country-id'        => 'product-ships-from-country',
					'states-id'         => '#product-ships-from-states',
					'states-wrapper'    => '#product-ships-from-states-wrapper',
				),
				'form-fields'  => array(
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
						'default' => 'US',
						'options' => it_exchange_get_data_set( 'countries' ),
					),
					array(
						'type'    => 'drop_down',
						'label'   => '',
						'slug'    => 'product-ships-from-states',
						'default' => 'NC',
						'options' => it_exchange_get_data_set( 'states', array( 'country' => 'US' ) ),
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
						'default' => '1',
					),
				),
			);
			it_exchange_print_admin_settings_form( $options );
			?>
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
