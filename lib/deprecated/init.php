<?php
/** 
 * This file inits deprecated features of Exchange
 * Some are alwasy included. Others require theme_support
 * or filters to be activated.
 *
 * @since 1.1.0
 * @package IT_Exchange
*/

/**
 * Loads the deprecated template parts
 *
 * @since 1.1.0
 *
 * @return void
*/
function it_exchange_load_deprecated_template_parts( $slug, $name ) {

	// Abandon if not supporting deprecated template parts
	if ( ! current_theme_supports( 'it-exchange-deprecated-template-parts' ) )
		return;

	// Tell exchange to look in our deprecated tempaltes folder for templates
	add_filter( 'it_exchange_possible_template_paths', 'it_exchange_register_deprecated_template_parts_directory' );

	// Enqueue the deprecated template part styles
	wp_enqueue_style( 'it-exchange-deprecated-template-parts', ITUtility::get_url_from_file( dirname( __FILE__ ) . '/templates/deprecated-template-part-styles.css' ) );
	wp_enqueue_style( 'it-exchange-deprecated-template-parts', ITUtility::get_url_from_file( dirname( __FILE__ ) . '/templates/deprecated-super-widget-template-styles.css' ) );

	// Dequeue our new ones
	wp_dequeue_style( 'it-exchange-super-widget-frontend-global' );
	wp_dequeue_style( 'it-exchange-public-css' );
}
add_action( 'it_exchange_get_template_part', 'it_exchange_load_deprecated_template_parts', 10, 2 );

/**
 * This function adds our deprecated templates folder to the list of possible paths for templates
 *
 * @since 1.1.0
 *
 * @param array $possible_locations existing locations
 * @return array
*/
function it_exchange_register_deprecated_template_parts_directory( $possible_locations ) {
	$deprecated_path = dirname( __FILE__ ) . '/templates';
	$possible_locations[] = $deprecated_path;
	return $possible_locations;
}
