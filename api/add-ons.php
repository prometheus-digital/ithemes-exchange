<?php
/**
 * API Functions used to register / retrieve Cart Buddy Add-ons
 *
 * @package IT_Cart_Buddy
 * @since 0.2.0
*/

/**
 * Register an Add-on with CartBuddy
 *
 * Core ‘category’ options for type of add-on
 * - product-type        Add-ons that create product types. eg: Digital, Membership
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

	// If category is already registered, merge registered with any required / default options for this category.
	if ( ! empty( $GLOBALS['it_cart_buddy']['add_on_categories'][$options['category']] ) )
		it_cart_buddy_merge_default_add_on_options( $options['category'], $slug );
}

/**
 * Register an Add-on category with CartBuddy
 *
 * When registering an add-on category, you can set required/default meta_keys that any add-on
 * in this category will be required to have. If add-ons register in this category without a key
 * they will be provided with a the value registered to the add-on category by default.
 * - eg: $options['required_meta'] = array( 'required_meta_key' => 'default_meta_value' );
 *
 * @param string $slug         var for identifying the add-on in code
 * @param string $name         name of add-on used in UI
 * @param string $description  description of the add-on
 * @param array  $options      key / value pairs.
*/
function it_cart_buddy_register_add_on_category( $slug, $name, $description, $options = array() ) {
	// Basic Validation
	$slug                     = empty( $slug ) ? false : sanitize_key( $slug );
	$name                     = empty( $name ) ? false : sanitize_text_field( $name );
	$options['required_meta'] = empty( $options['required_meta'] ) ? array() : $options['required_meta'];

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

	// If add-ons have previously been registered with this category, merge their registered options with the required_meta 
	if ( ! empty( $options['required_meta'] ) && count( it_cart_buddy_get_add_ons( array( 'category' => $slug ) ) ) )
		it_cart_buddy_merge_default_add_on_options( $slug );
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
 * @since 0.2.0
 * @param array $options  For filtering by category, use $options['category'] = array( 'cat1', 'cat2', 'etc' );
 * @return array  registered add-ons
*/
function it_cart_buddy_get_add_ons( $options=array() ) {
	if ( empty( $GLOBALS['it_cart_buddy']['add_ons']['registered'] ) )
		return array();
	else
		$addons = $GLOBALS['it_cart_buddy']['add_ons']['registered'];

	// Possibly filter by category
	if ( ! empty( $options['category'] ) )
		$addons = it_cart_buddy_filter_add_ons_by_category( $addons, $options['category'] );
	
	return $addons;
}

/**
 * Returns a specific add-on by its slug
 *
 * @since 0.3.2
 * @param string $slug  the add-on's slug
 * @return array  the addon array
*/
function it_cart_buddy_get_add_on( $slug ) {
	if ( $addons = it_cart_buddy_get_add_ons() ) {
		if ( ! empty( $addons[$slug] ) )
			return $addons[$slug];
	}
	return false;
}

/**
 * Returns an array of registered add-on categories
 *
 * @since 0.2.0
 * @return array  registered add-on categories
*/
function it_cart_buddy_get_add_on_categories() {
	if ( empty( $GLOBALS['it_cart_buddy']['add_on_categories'] ) )
		return array();
	else
		return $GLOBALS['it_cart_buddy']['add_on_categories'];
}

/**
 * Grabs list of currently enabled add-ons
 *
 * Can optionally filter by categories
 *
 * @since 0.3.0
 * @param array $options  For filtering by category, use $options['category'] = array( 'cat1', 'cat2', 'etc' );
 * @uses get_option  it_cart_buddy_enabled_add_ons
 * @uses it_cart_buddy_filter_add_ons_by_category()
 * @return array  Enabled add-ons
*/
function it_cart_buddy_get_enabled_add_ons( $options=array() ) {
	// Grab all registered add-ons
	$registered = it_cart_buddy_get_add_ons();

	// Grab enabled add-ons from options
	if ( false === $enabled = get_option( 'it_cart_buddy_enabled_add_ons' ) )
		$enabled = array();

	// Set each enabled with registered params
	foreach ( $enabled as $slug => $file ) {
		if ( ! empty( $registered[$slug] ) )
			$enabled[$slug] = $registered[$slug];
	}

	if ( ! empty( $options['category'] ) )
		$enabled = it_cart_buddy_filter_add_ons_by_category( $enabled, $options['category'] );

	return empty( $enabled ) ? array() : $enabled;
}

/**
 * Takes an array of add-ons and filters by passed category
 *
 * @since 0.3.0
 * @param array $addons  an array of add-ons formatted like $GLOBALS['it_cart_buddy']['add_ons'] array
 * @param array $categories  contains categories we want filters: array( 'cat1', 'cat2', 'etc' );
 * @return array  Filtered add-ons
*/
function it_cart_buddy_filter_add_ons_by_category( $addons, $categories ) {
	foreach( $addons as $slug => $params ) {
		if ( ! empty( $params['options']['category'] ) ) {
			if ( ! in_array( $params['options']['category'], (array) $categories ) )
				unset( $addons[$slug] );
		}
	}
	return $addons;
}

/**
 * Merges required/default add-on options with those registered by the add-on
 *
 * When registering an add-on category, an array of required/default meta_data can be set for all add-ons
 * in that category. This function merges defaults with those registered by specific add-ons. It is called
 * during it_cart_buddy_register_add_on if the add-on category already exists. It is also called after 
 * it_cart_buddy_register_add_on_category() if an add-on has previously been registered in that category.
 *
 * @since 0.3.2
 * @return void
*/
function it_cart_buddy_merge_default_add_on_options( $add_on_category_slug, $add_on_slug=false ) {
	$add_on_categories = it_cart_buddy_get_add_on_categories();
	$add_on            = empty( $add_on_slug ) ? false : it_cart_buddy_get_add_on( $add_on_slug );
	$add_on_category   = empty( $add_on_categories[$add_on_category_slug] ) ? false : $add_on_categories[$add_on_category_slug];

	// If we weren't passed a category that's been registered yet, return;
	if ( ! $add_on_category )
		return;
	// If the category is does not have any required / default options, return;
	if ( empty( $add_on_category['options']['required_meta'] ) )
		return;

	// Set default required options
	$required = array();
	foreach( $add_on_category['options']['required_meta'] as $row => $params ) {
		$required[$params[0]] = $params[1];
	}

	// Set array of add-ons to perform merges on
	if ( $add_on )
		$addons = array( $add_on['slug'] => $add_on );
	else
		$addons = it_cart_buddy_get_add_ons( array( 'category' => array( $add_on_category_slug ) ) );

	// Load ITUtility
	it_classes_load( 'it-utility.php' );

	// Foreach addon, merge their registered options, with the required/default options for the passed category
	foreach( $addons as $slug => $params ) {
		$options = empty( $params['options']['default_meta'] ) ? array() : $params['options']['default_meta'];
		$GLOBALS['it_cart_buddy']['add_ons']['registered'][$slug]['options']['default_meta'] = ITUtility::merge_defaults( $options, $required );
	}

}

/**
 * Enable a registerd addon
 *
 * @todo Add nonce
 * @since 0.3.2
 * @param string $addon  addon to enable
 * @return void
*/
function it_cart_buddy_enable_add_on( $addon ) {
	$registered = it_cart_buddy_get_add_ons();
	$enabled = get_option( 'it_cart_buddy_enabled_add_ons' );

	if ( in_array( $addon, array_keys( $registered ) ) ) {
		$enabled[$addon] = $registered[$addon]['file'];
		if ( update_option( 'it_cart_buddy_enabled_add_ons', $enabled ) ) {
			do_action( 'it_cart_buddy_enabled_plugin', $registered[$addon] );
			return $enabled;
		}
	}
	return false;
}

/**
 * Disable a registerd addon
 *
 * @todo Add nonce
 * @since 0.3.2
 * @param string $addon  addon to disable
 * @return void
*/
function it_cart_buddy_disable_add_on( $addon ) {
	$registered = it_cart_buddy_get_add_ons();
	$enabled = get_option( 'it_cart_buddy_enabled_add_ons' );

	if ( ! empty( $enabled[$addon] ) ) {
		unset( $enabled[$addon] );
		if ( update_option( 'it_cart_buddy_enabled_add_ons', $enabled ) ) {
			do_action( 'it_cart_buddy_disabled_plugin', $registered[$addon] );
			return $enabled;
		}
	}
	return false;
}
