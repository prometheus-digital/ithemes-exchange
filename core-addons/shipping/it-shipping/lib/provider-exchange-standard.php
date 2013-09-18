<?php

/**
 * Register our Default Shipping Provider
 *
 * @since CHANGEME
 *
 * @return void
*/
function it_exchange_register_exchange_standard_provider() {
	$options = array(
		'label'            => __( 'Exchange', 'LION' ),
		'shipping-methods' => array(
			'exchange-standard' => array(
				'slug'  => 'exchange-standard',
				'label' => __( 'Exchange Standard', 'LION' ),
			),
			'exchange-advanced' => array(
				'slug'             => 'exchange-advanced',
				'label'            => __( 'Exchange Advanced', 'LION' ),
				'product-features' => array(
					'dimensions',
					'weight',
				),
			),
		),
		'provider-settings' => array(
			array(
				'type'  => 'heading',
				'label' => __( 'Default Settings', 'LION' ),
				'slug'  => 'heading_default_settings',
			),
			array(
				'type'    => 'text_box',
				'label'   => __( 'Shipping Label', 'LION' ),
				'slug'    => 'shipping_label',
				'tooltip' => __( 'This changes the title of this Shipping Method for your customers', 'LION' ),
				'default' => __( 'Standard Shipping (3-5 days)', 'LION' ),
			),
			array(
				'type'    => 'text_box',
				'label'   => __( 'Default Shipping Amount', 'LION' ),
				'slug'    => 'default_amount',
				'tooltip' => __( 'The default shipping amount for new products. This can be overridden by individual products.', 'LION' ),
				'default' => 5,
			),
		),
	);

	it_exchange_register_shipping_provider( 'exchange-standard', $options );
}
add_filter( 'it_exchange_enabled_addons_loaded', 'it_exchange_register_exchange_standard_provider' );
