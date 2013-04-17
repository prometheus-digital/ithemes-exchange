<?php
/**
 * API Functions used to register / retrieve Exchange Add-ons
 *
 * @package IT_Exchange
 * @since 0.2.0
*/

/**
 * Register an Add-on with iThemes Exchange
 *
 * Core ‘category’ options for type of add-on
 * - product-type        Add-ons that create product types. eg: Digital, Membership
 * - transaction-method  Add-ons that create transactions. eg: Stripe, Tweet
 * - admin               General purpose admin functionality. eg: Reports, Export
 * - other               Everything else
 *
 * @param string $slug         string for identifying the add-on in code
 * @param string $name         name of add-on used in UI
 * @param string $description  description of the add-on
 * @param string $file         init file for add-on
 * @param array  $options      key / value pairs.
*/
function it_exchange_register_addon( $slug, $name, $description, $file, $options = array() ) {
	// Basic Validation
	$slug = empty( $slug )       ? false : sanitize_key( $slug );
	$name = empty( $name )       ? false : sanitize_text_field( $name );
	$file = file_exists( $file ) ? $file : false;

	if ( ! $slug  )
		return new WP_Error( 'it_exchange_add_registration_error', __( 'All iThemes Excahnge Add-ons require a slug paramater.', 'LION' ) );

	if ( ! $name )
		return new WP_Error( 'it_exchange_add_registration_error', __( 'All iThemes Excahnge Add-ons require a name parameter.', 'LION' ) );

	if ( ! $file )
		return new WP_Error( 'it_exchange_add_registration_error', __( 'All iThemes Excahnge Add-ons require a file paramater.', 'LION' ) );

	if ( empty( $options['category'] ) )
		$options['category'] = 'other';

	// Add the add-on to our Global
	$GLOBALS['it_exchange']['add_ons']['registered'][$slug] = array(
		'slug'        => $slug,
		'name'        => $name,
		'description' => $description,
		'file'        => $file,
		'options'     => $options,
	);
}

/**
 * Register an Add-on category with iThemes Exchange
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
function it_exchange_register_addon_category( $slug, $name, $description, $options = array() ) {
	// Basic Validation
	$slug                     = empty( $slug ) ? false : sanitize_key( $slug );
	$name                     = empty( $name ) ? false : sanitize_text_field( $name );
	$options['supports']      = empty( $options['supports'] ) ? array() : $options['supports'];

	if ( ! $slug  )
		return new WP_Error( 'it_exchange_add_registration_error', __( 'All iThemes Excahnge Add-ons require a slug paramater.', 'LION' ) );

	if ( ! $name )
		return new WP_Error( 'it_exchange_add_registration_error', __( 'All iThemes Excahnge Add-ons require a name parameter.', 'LION' ) );

	// Add the add-on to our Global
	$GLOBALS['it_exchange']['add_on_categories'][$slug] = array(
		'slug'        => $slug,
		'name'        => $name,
		'description' => $description,
		'options'     => $options,
	);
}

/**
 * Register a bundle of add-ons as a ‘Set’ with iThemes Exchange
 *
 * @param string $slug         var for identifying the add-on set in code
 * @param string $name         name of add-on set used in UI
 * @param string $description  description of the add-on set
 * @param array  $options      key / value pairs.
*/
function it_exchange_register_addon_set( $slug, $name, $description, $options = array() ) {}

/**
 * Returns an array of registered add-ons
 *
 * @since 0.2.0
 * @param array $options  For filtering by category, use $options['category'] = array( 'cat1', 'cat2', 'etc' );
 * @return array  registered add-ons
*/
function it_exchange_get_addons( $options=array() ) {
	if ( empty( $GLOBALS['it_exchange']['add_ons']['registered'] ) )
		return array();
	else
		$add_ons = $GLOBALS['it_exchange']['add_ons']['registered'];

	// Possibly filter by category
	if ( ! empty( $options['category'] ) )
		$add_ons = it_exchange_filter_addons_by_category( $add_ons, $options['category'] );
	
	return $add_ons;
}

/**
 * Returns a specific add-on by its slug
 *
 * @since 0.3.2
 * @param string $slug  the add-on's slug
 * @return array  the add_on array
*/
function it_exchange_get_addon( $slug ) {
	if ( $add_ons = it_exchange_get_addons() ) {
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
function it_exchange_get_addon_categories() {
	if ( empty( $GLOBALS['it_exchange']['add_on_categories'] ) )
		return array();
	else
		return $GLOBALS['it_exchange']['add_on_categories'];
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
function it_exchange_get_enabled_addons( $options=array() ) {
	// Grab all registered add-ons
	$registered = it_exchange_get_addons();

	// Grab enabled add-ons from options
	if ( false === $enabled = it_exchange_get_option( 'exchange_enabled_add_ons' ) )
		$enabled = array();

	// Set each enabled with registered params
	foreach ( $enabled as $slug => $file ) {
		if ( ! empty( $registered[$slug] ) )
			$enabled[$slug] = $registered[$slug];
	}

	if ( ! empty( $options['category'] ) )
		$enabled = it_exchange_filter_addons_by_category( $enabled, $options['category'] );

	return empty( $enabled ) ? array() : $enabled;
}

/**
 * Takes an array of add-ons and filters by passed category
 *
 * @since 0.3.0
 * @param array $add_ons  an array of add-ons formatted like $GLOBALS['it_exchange']['add_ons'] array
 * @param array $categories  contains categories we want filters: array( 'cat1', 'cat2', 'etc' );
 * @return array  Filtered add-ons
*/
function it_exchange_filter_addons_by_category( $add_ons, $categories ) {
	foreach( $add_ons as $slug => $params ) {
		if ( ! empty( $params['options']['category'] ) ) {
			if ( ! in_array( $params['options']['category'], (array) $categories ) )
				unset( $add_ons[$slug] );
		}
	}
	return $add_ons;
}

/**
 * Enable a registerd add_on
 *
 * @todo Add nonce
 * @since 0.3.2
 * @param string $add_on  add_on to enable
 * @return void
*/
function it_exchange_enable_addon( $add_on ) {
	$registered = it_exchange_get_addons();
	$enabled = it_exchange_get_option( 'exchange_enabled_add_ons' );

	if ( in_array( $add_on, array_keys( $registered ) ) ) {
		$enabled[$add_on] = $registered[$add_on]['file'];
		if ( it_exchange_save_option( 'exchange_enabled_add_ons', $enabled ) ) {
			require( $registered[$add_on]['file'] );
			do_action( 'it_exchange_add_on_enabled', $registered[$add_on] );
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
function it_exchange_disable_addon( $add_on ) {
	$registered = it_exchange_get_addons();
	$enabled = it_exchange_get_option( 'exchange_enabled_add_ons' );

	if ( ! empty( $enabled[$add_on] ) ) {
		unset( $enabled[$add_on] );
		if ( it_exchange_save_option( 'exchange_enabled_add_ons', $enabled ) ) {
			if ( ! empty( $registered[$add_on] ) )
				do_action( 'it_exchange_add_on_disabled', $registered[$add_on] );
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
function it_exchange_addon_supports( $add_on, $feature ) {
	$add_ons = it_exchange_get_addons();

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
function it_exchange_add_addon_support( $add_on, $feature ) {
	$add_ons = it_exchange_get_addons();

	// Return false if add-on is not registered
	if ( ! isset( $add_ons[$add_on] ) )
		return false;

	// Set add-on support to true for this add-on / feature combo
	if ( empty( $add_ons[$add_on]['options'] ) )
		$GLOBALS['it_exchange']['add_ons']['registered'][$add_on]['options']['supports'][$feature] = true;
}

/**
 * Remove's add-on support for a specific feature
 *
 * @since 0.3.3
 * @param string $add_on   the slug for the add-on being targeted
 * @param string $feature the feature slug that needs to be enabled
 * @return void
*/
function it_exchange_remove_addon_support( $add_on, $feature ) {
	$add_ons = it_exchange_get_addons();

	// Return false if add-on is not registered
	if ( ! isset( $add_ons[$add_on] ) )
		return false;

	// Set add-on support to false for this add-on / feature combo
	if ( empty( $add_ons[$add_on]['options'] ) )
		$GLOBALS['it_exchange']['add_ons']['registered'][$add_on]['options']['supports'][$feature] = false;
}

/**
 * Return the default values for an add-on support key
 *
 * @since 0.3.3
 * @param string $add_on the slug for the add-on being targeted
 * @param string $feature the feature the slug is targeting
 * @return mixed the value of the key
*/
function it_exchange_get_addon_support( $add_on, $feature ) {
	$add_ons = it_exchange_get_addons();

	// Return false if feature isn't recorded
	if ( empty( $add_ons[$add_on]['options']['supports'][$feature] ) )
		return false;

	return $add_ons[$add_on]['options']['supports'][$feature];
}
