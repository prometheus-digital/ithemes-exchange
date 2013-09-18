<?php
/**
 * Register's a provider
 *
 * @since CHANGEME
 *
 * @param string $slug    provider slug
 * @param array  $options options for the provider
 * @return object
*/
function it_exchange_register_shipping_provider( $slug, $options ) {
	include_once( dirname( dirname( __FILE__ ) ) . '/lib/class-provider.php' );
	$GLOBALS['it_exchange']['shipping']['providers'][$slug] = new IT_Exchange_Shipping_Provider( $slug, $options );
	return it_exchange_get_shipping_provider( $slug );
}

/**
 * Returns all registered shipping providers
 *
 * @since CHANGEME
 *
 * @return array an array of objects
*/
function it_exchange_get_registered_shipping_providers() {
	return empty( $GLOBALS['it_exchange']['shipping']['providers'] ) ? array() : $GLOBALS['it_exchange']['shipping']['providers'];
}

/**
 * Returns a shipping provider object
 *
 * @since CHANGEME
 *
 * @param  string $slug the registerd slug
 * @return mixed  false or object
*/
function it_exchange_get_shipping_provider( $slug ) {
	if ( ! empty( $GLOBALS['it_exchange']['shipping']['providers'][$slug] ) )
		return $GLOBALS['it_exchange']['shipping']['providers'][$slug];

	return false;
}

/**
 * Is the requested shipping provider registered?
 *
 * @since CHANGEME
 *
 * @param  string  $slug the registerd slug
 * @return boolean
*/
function it_exchange_is_shipping_provider_registered( $slug ) {
	return (boolean) it_exchange_get_shipping_provider( $slug );
}

function it_exchange_get_shipping_provider_setting_values( $slug ) {
	if ( ! $provider = it_exchange_get_shipping_provider( $slug ) )
		return false;
}

/**
 * Prints the tabs for all registered shipping providers
 *
 * @since CHANGEME
 *
 * @return html
*/
function it_exchange_print_shipping_provider_settings_tabs() {
	if ( ! $providers = it_exchange_get_registered_shipping_providers() )
		return '';

	$current = empty( $_GET['provider'] ) ? false : $_GET['provider'];

	?>
	<div class="it-exchange-secondary-tabs it-exchange-shipping-provider-tabs">
		<?php if ( ! empty( $current ) && it_exchange_is_shipping_provider_registered( $current ) ) : ?>
			<a class="shipping-provider-link" href="<?php esc_attr_e( add_query_arg( array( 'page' => 'it-exchange-settings', 'tab' => 'shipping' ), admin_url( 'admin' ) ) ); ?>">
				<?php _e( 'General', 'LION' ); ?>
			</a>
		<?php endif; ?>
		<?php foreach( $providers as $provider ) : ?>
			<?php if ( empty( $provider->has_settings_page ) ) continue; ?>
			<?php $url = add_query_arg( array( 'page' => 'it-exchange-settings', 'tab' => 'shipping', 'provider' => $provider->get_slug() ), admin_url( 'admin' ) ); ?>
			<a class="shipping-provider-link<?php echo ( $current == $provider->get_slug() ) ? ' it-exchange-current' : ''; ?>" href="<?php echo $url; ?>"><?php esc_html_e( $provider->get_label() ); ?></a>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Returns all registered shipping methods
 *
 * @since CHANGEME
 *
 * @param  mixed $providers a string or an array of strings to limit returned methods to specific providers
 * @return array
*/
function it_exchange_get_registered_shipping_methods( $providers=array() ) {
	$registered_providers = it_exchange_get_registered_shipping_providers();
	$requested_providers  = empty( $providers ) ? array_keys( $registered_providers ) : (array) $providers;

	$methods = array();
	foreach( $requested_providers as $provider ) {
		if ( ! empty( $registered_providers[$provider] ) )
			$methods = array_merge( $methods, $registered_providers[$provider]->get_shipping_methods() );
	}

	return $methods;
}
