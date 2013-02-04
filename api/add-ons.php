<?php
/**
 * API Functions used to register / retrieve Cart Buddy Add-ons
 *
 * @package IT_Cart_Buddy
 * @since 0.2
*/

/**
 * Register an Add-on with CartBuddy
 *
 * Core ‘category’ options for type of add-on
 * - item                Add-ons that create items. eg: Digital, Membership
 * - transaction-method  Add-ons that create transactions. eg: Stripe, Tweet
 * - admin               General purpose admin functionality. eg: Reports, Export
 * - front-end           General purpose front-end functionality. eg: Widgets, Shortcodes
 * - other               Everything else
 *
 * @param string $slug         string for identifying the add-on in code
 * @param string $name         name of add-on used in UI
 * @param string $description  description of the add-on
 * @param string $file         init file for add-on
 * @param array  $options      key / value pairs.
*/
function it_cart_buddy_register_add_on( $slug, $name, $description, $file, $options = array() ) {
	// Basic Validation
	$slug = empty( $slug )       ? false : sanitize_key( $slug );
	$name = empty( $name )       ? false : sanitize_text_field( $name );
	$file = file_exists( $file ) ? $file : false;

	if ( ! $slug  )
		return new WP_Error( 'it_cart_buddy_add_registration_error', __( 'All Cart Buddy Add-ons require a slug paramater.', 'LION' ) );

	if ( ! $name )
		return new WP_Error( 'it_cart_buddy_add_registration_error', __( 'All Cart Buddy Add-ons require a name parameter.', 'LION' ) );

	if ( ! $file )
		return new WP_Error( 'it_cart_buddy_add_registration_error', __( 'All Cart Buddy Add-ons require a file paramater.', 'LION' ) );

	if ( empty( $options['category'] ) )
		$options['category'] = 'other';

	// Add the add-on to our Global
	$GLOBALS['it_cart_buddy']['add_ons']['registered'][$slug] = array(
		'slug'        => $slug,
		'name'        => $name,
		'description' => $description,
		'file'        => $file,
		'options'     => $options,
	);
}

/**
 * Register an Add-on category with CartBuddy
 *
 * @param string $slug         var for identifying the add-on in code
 * @param string $name         name of add-on used in UI
 * @param string $description  description of the add-on
 * @param array  $options      key / value pairs.
*/
function it_cart_buddy_register_add_on_category( $slug, $name, $description, $options = array() ) {
	// Basic Validation
	$slug = empty( $slug )       ? false : sanitize_key( $slug );
	$name = empty( $name )       ? false : sanitize_text_field( $name );

	if ( ! $slug  )
		return new WP_Error( 'it_cart_buddy_add_registration_error', __( 'All Cart Buddy Add-ons require a slug paramater.', 'LION' ) );

	if ( ! $name )
		return new WP_Error( 'it_cart_buddy_add_registration_error', __( 'All Cart Buddy Add-ons require a name parameter.', 'LION' ) );

	// Add the add-on to our Global
	$GLOBALS['it_cart_buddy']['add_on_categories'][$slug] = array(
		'slug'        => $slug,
		'name'        => $name,
		'description' => $description,
		'options'     => $options,
	);
}

/**
 * Register a bundle of add-ons as a ‘Set’ with CartBuddy
 *
 * @param string $slug         var for identifying the add-on set in code
 * @param string $name         name of add-on set used in UI
 * @param string $description  description of the add-on set
 * @param array  $options      key / value pairs.
*/
function it_cart_buddy_register_add_on_set( $slug, $name, $description, $options = array() ) {}

/**
 * Returns an array of registered add-ons
 *
 * @since 0.2
 * @return array  registered add-ons
*/
function it_cart_buddy_get_add_ons() {
	if ( empty( $GLOBALS['it_cart_buddy']['add_ons']['registered'] ) )
		return array();
	else
		return $GLOBALS['it_cart_buddy']['add_ons']['registered'];
}

/**
 * Returns an array of registered add-on categories
 *
 * @since 0.2
 * @return array  registered add-on categories
*/
function it_cart_buddy_get_add_on_categories() {
	if ( empty( $GLOBALS['it_cart_buddy']['add_on_categories'] ) )
		return array();
	else
		return $GLOBALS['it_cart_buddy']['add_on_categories'];
}
