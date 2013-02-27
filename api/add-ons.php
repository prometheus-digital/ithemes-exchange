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
function it_cart_buddy_register_addon( $slug, $name, $description, $file, $options = array() ) {
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
		it_cart_buddy_merge_addon_supports_with_defaults( $options['category'], $slug );
}

/**
 * Register an Add-on category with CartBuddy
 *
 * When registering an add-on category, you can set required/default support features that any add-on
 * in this category will be required to have. If add-ons register in this category without a key
 * they will be provided with a the value registered to the add-on category by default.
 * - eg: $options['supports'] = array( 'feature' => 'default_value' );
 *
 * @param string $slug         var for identifying the add-on in code
 * @param string $name         name of add-on used in UI
 * @param string $description  description of the add-on
 * @param array  $options      key / value pairs.
*/
function it_cart_buddy_register_addon_category( $slug, $name, $description, $options = array() ) {
	// Basic Validation
	$slug                     = empty( $slug ) ? false : sanitize_key( $slug );
	$name                     = empty( $name ) ? false : sanitize_text_field( $name );
	$options['supports']      = empty( $options['supports'] ) ? array() : $options['supports'];

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

	// If add-ons have previously been registered with this category, merge their registered options with the supports 
	if ( ! empty( $options['supports'] ) && count( it_cart_buddy_get_addons( array( 'category' => $slug ) ) ) )
		it_cart_buddy_merge_addon_supports_with_defaults( $slug );
}

/**
 * Register a bundle of add-ons as a ‘Set’ with CartBuddy
 *
 * @param string $slug         var for identifying the add-on set in code
 * @param string $name         name of add-on set used in UI
 * @param string $description  description of the add-on set
 * @param array  $options      key / value pairs.
*/
function it_cart_buddy_register_addon_set( $slug, $name, $description, $options = array() ) {}

/**
 * Returns an array of registered add-ons
 *
 * @since 0.2.0
 * @param array $options  For filtering by category, use $options['category'] = array( 'cat1', 'cat2', 'etc' );
 * @return array  registered add-ons
*/
function it_cart_buddy_get_addons( $options=array() ) {
	if ( empty( $GLOBALS['it_cart_buddy']['add_ons']['registered'] ) )
		return array();
	else
		$add_ons = $GLOBALS['it_cart_buddy']['add_ons']['registered'];

	// Possibly filter by category
	if ( ! empty( $options['category'] ) )
		$add_ons = it_cart_buddy_filter_addons_by_category( $add_ons, $options['category'] );
	
	return $add_ons;
}

/**
 * Returns a specific add-on by its slug
 *
 * @since 0.3.2
 * @param string $slug  the add-on's slug
 * @return array  the add_on array
*/
function it_cart_buddy_get_addon( $slug ) {
	if ( $add_ons = it_cart_buddy_get_addons() ) {
		if ( ! empty( $add_ons[$slug] ) )
			return $add_ons[$slug];
	}
	return false;
}

/**
 * Returns an array of registered add-on categories
 *
 * @since 0.2.0
 * @return array  registered add-on categories
*/
function it_cart_buddy_get_addon_categories() {
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
 * @return array  Enabled add-ons
*/
function it_cart_buddy_get_enabled_addons( $options=array() ) {
	// Grab all registered add-ons
	$registered = it_cart_buddy_get_addons();

	// Grab enabled add-ons from options
	if ( false === $enabled = it_cart_buddy_get_option( 'cart_buddy_enabled_add_ons' ) )
		$enabled = array();

	// Set each enabled with registered params
	foreach ( $enabled as $slug => $file ) {
		if ( ! empty( $registered[$slug] ) )
			$enabled[$slug] = $registered[$slug];
	}

	if ( ! empty( $options['category'] ) )
		$enabled = it_cart_buddy_filter_addons_by_category( $enabled, $options['category'] );

	return empty( $enabled ) ? array() : $enabled;
}

/**
 * Takes an array of add-ons and filters by passed category
 *
 * @since 0.3.0
 * @param array $add_ons  an array of add-ons formatted like $GLOBALS['it_cart_buddy']['add_ons'] array
 * @param array $categories  contains categories we want filters: array( 'cat1', 'cat2', 'etc' );
 * @return array  Filtered add-ons
*/
function it_cart_buddy_filter_addons_by_category( $add_ons, $categories ) {
	foreach( $add_ons as $slug => $params ) {
		if ( ! empty( $params['options']['category'] ) ) {
			if ( ! in_array( $params['options']['category'], (array) $categories ) )
				unset( $add_ons[$slug] );
		}
	}
	return $add_ons;
}

/**
 * Merges required/default add-on options with those registered by the add-on
 *
 * When registering an add-on category, an array of required/default meta_data can be set for all add-ons
 * in that category. This function merges defaults with those registered by specific add-ons. It is called
 * during it_cart_buddy_register_addon if the add-on category already exists. It is also called after 
 * it_cart_buddy_register_addon_category() if an add-on has previously been registered in that category.
 *
 * @since 0.3.2
 * @return void
*/
function it_cart_buddy_merge_addon_supports_with_defaults( $add_on_category_slug, $add_on_slug=false ) {
	$add_on_categories = it_cart_buddy_get_addon_categories();
	$add_on            = empty( $add_on_slug ) ? false : it_cart_buddy_get_addon( $add_on_slug );
	$add_on_category   = empty( $add_on_categories[$add_on_category_slug] ) ? false : $add_on_categories[$add_on_category_slug];

	// If we weren't passed a category that's been registered yet, return;
	if ( ! $add_on_category )
		return;

	// If the category is does not have any required / default options, return;
	if ( empty( $add_on_category['options']['supports'] ) )
		return;

	// Set default required options
	$default_supports = array();
	foreach( $add_on_category['options']['supports'] as $feature => $params ) {
		$default_supports[$feature] = $params;
	}

	// Set array of add-ons to perform merges on
	if ( $add_on )
		$add_ons = array( $add_on['slug'] => $add_on );
	else
		$add_ons = it_cart_buddy_get_addons( array( 'category' => array( $add_on_category_slug ) ) );

	// Load ITUtility
	it_classes_load( 'it-utility.php' );

	// Foreach add_on, merge their registered options, with the default options for the passed category
	foreach( $add_ons as $slug => $add_on_params ) {
		$supports = empty( $add_on_params['options']['supports'] ) ? array() : $add_on_params['options']['supports'];

		// Allow add-ons to remove support for a feature supported by its category
		foreach( $supports as $key => $value ) {
			if ( empty( $value ) && is_array( $default_supports[$key] ) ) {
				unset( $default_supports[$key] );
				unset( $supports[$key] );
			}
		}
		$merged = ITUtility::merge_defaults( $supports, $default_supports );
		$GLOBALS['it_cart_buddy']['add_ons']['registered'][$slug]['options']['supports'] = $merged;
	}

}

/**
 * Enable a registerd add_on
 *
 * @todo Add nonce
 * @since 0.3.2
 * @param string $add_on  add_on to enable
 * @return void
*/
function it_cart_buddy_enable_addon( $add_on ) {
	$registered = it_cart_buddy_get_addons();
	$enabled = it_cart_buddy_get_option( 'cart_buddy_enabled_add_ons' );

	if ( in_array( $add_on, array_keys( $registered ) ) ) {
		$enabled[$add_on] = $registered[$add_on]['file'];
		if ( it_cart_buddy_save_option( 'cart_buddy_enabled_add_ons', $enabled ) ) {
			require( $registered[$add_on]['file'] );
			do_action( 'it_cart_buddy_add_on_enabled', $registered[$add_on] );
			return $enabled;
		}
	}
	return false;
}

/**
 * Disable a registerd add_on
 *
 * @todo Add nonce
 * @since 0.3.2
 * @param string $add_on  add_on to disable
 * @return void
*/
function it_cart_buddy_disable_addon( $add_on ) {
	$registered = it_cart_buddy_get_addons();
	$enabled = it_cart_buddy_get_option( 'cart_buddy_enabled_add_ons' );

	if ( ! empty( $enabled[$add_on] ) ) {
		unset( $enabled[$add_on] );
		if ( it_cart_buddy_save_option( 'cart_buddy_enabled_add_ons', $enabled ) ) {
			do_action( 'it_cart_buddy_add_on_disabled', $registered[$add_on] );
			return $enabled;
		}
	}
	return false;
}

/**
 * Does the given add-on support a specific feature?
 *
 * @since 0.3.3
 * @param string $add_on   add_on slug
 * @param string $feature type of feature we are testing for support
 * @return boolean
*/
function it_cart_buddy_addon_supports( $add_on, $feature ) {
	$add_ons = it_cart_buddy_get_addons();

	// Return false if add-on is not registered
	if ( ! isset( $add_ons[$add_on] ) )
		return false;

	// Return false if feature is not supported
	if ( empty( $add_ons[$add_on]['options']['supports'][$feature] ) )
		return false;

	return true;
}

/**
 * Add's add-on support for a specific feature
 *
 * @since 0.3.3
 * @param string $add_on   the slug for the add-on being targeted
 * @param string $feature the feature slug that needs to be enabled
 * @return void
*/
function it_cart_buddy_add_addon_support( $add_on, $feature ) {
	$add_ons = it_cart_buddy_get_addons();

	// Return false if add-on is not registered
	if ( ! isset( $add_ons[$add_on] ) )
		return false;

	// Set add-on support to true for this add-on / feature combo
	if ( empty( $add_ons[$add_on]['options'] ) )
		$GLOBALS['it_cart_buddy']['add_ons']['registered'][$add_on]['options']['supports'][$feature] = true;
}

/**
 * Remove's add-on support for a specific feature
 *
 * @since 0.3.3
 * @param string $add_on   the slug for the add-on being targeted
 * @param string $feature the feature slug that needs to be enabled
 * @return void
*/
function it_cart_buddy_remove_addon_support( $add_on, $feature ) {
	$add_ons = it_cart_buddy_get_addons();

	// Return false if add-on is not registered
	if ( ! isset( $add_ons[$add_on] ) )
		return false;

	// Set add-on support to false for this add-on / feature combo
	if ( empty( $add_ons[$add_on]['options'] ) )
		$GLOBALS['it_cart_buddy']['add_ons']['registered'][$add_on]['options']['supports'][$feature] = false;
}

/**
 * Return the default values for an add-on support key
 *
 * @since 0.3.3
 * @param string $add_on the slug for the add-on being targeted
 * @param string $feature the feature the slug is targeting
 * @return mixed the value of the key
*/
function it_cart_buddy_get_addon_support( $add_on, $feature ) {
	$add_ons = it_cart_buddy_get_addons();

	// Return false if feature isn't recorded
	if ( empty( $add_ons[$add_on]['options']['supports'][$feature] ) )
		return false;

	return $add_ons[$add_on]['options']['supports'][$feature];
}
