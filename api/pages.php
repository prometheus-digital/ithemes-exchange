<?php
/**
 * This file contains API methods used with Exchange frontend pages and product pages
 * @package IT_Exchange
 * @since 0.4.4
*/

/**
 * Returns a list of all registered IT Exchange pages (including the products slug)
 *
 * @since 0.4.4
 *
 * @param boolean $break_cache pages come from it_storage which caches options. Set this to true to not retreived cached pages
 * @return array
*/
function it_exchange_get_pages( $break_cache=false, $options=array() ) {
	// Grab registered pages
	$registered = it_exchange_get_registered_pages( $options );
	$merged     = array();

	// Grab existing DB data if its present
	if ( ! $pages = it_exchange_get_option( 'settings_pages', $break_cache ) )
		$pages = array();

	// Merge DB data with registered defaults
	foreach( $registered as $page => $default_params ) {
		$db_params = array();
		$db_params['slug'] = empty( $pages[$page . '-slug'] ) ? 0 : $pages[$page . '-slug'];
		$db_params['name'] = empty( $pages[$page . '-name'] ) ? 0 : $pages[$page . '-name'];
		$db_params['type'] = empty( $pages[$page . '-type'] ) ? 0 : $pages[$page . '-type'];
		$db_params['wpid'] = empty( $pages[$page . '-wpid'] ) ? 0 : $pages[$page . '-wpid'];
		$merged[$page] = ITUtility::merge_defaults( $db_params, $default_params );
	}

	return apply_filters( 'it_exchange_get_pages', $merged, $break_cache );
}

/**
 * Get name for page
 *
 * @since 0.4.0
 *
 * @param string $page page var
 * @return string url
*/
function it_exchange_get_page_name( $page, $break_cache=false ) { 
	$pages     = it_exchange_get_pages( $break_cache );
	$page_name = empty( $pages[$page]['name'] ) ? false : $pages[$page]['name'];
	return apply_filters( 'it_exchange_get_page_name', $page_name, $page, $break_cache );
}

/**
 * Get editable slug for page
 *
 * @since 0.4.4
 *
 * @param string $page page var
 * @return string
*/
function it_exchange_get_page_slug( $page, $break_cache=false ) { 
	$pages     = it_exchange_get_pages( $break_cache );
	$page_slug = empty( $pages[$page]['slug'] ) ? false : $pages[$page]['slug'];
	return apply_filters( 'it_exchange_get_page_slug', $page_slug, $page, $break_cache );
}

/**
 * Get editable type for page
 *
 * @since 0.4.4
 *
 * @param string $page page var
 * @return string
*/
function it_exchange_get_page_type( $page, $break_cache=false ) { 
	$pages     = it_exchange_get_pages( $break_cache );
	$page_type = empty( $pages[$page]['type'] ) ? false : $pages[$page]['type'];
	return apply_filters( 'it_exchange_get_page_type', $page_type, $page, $break_cache );
}

/**
 * Get editable WordPress ID (wpid) for page (only used if type is 'wordpress')
 *
 * @since 0.4.4
 *
 * @param string $page page var
 * @return string
*/
function it_exchange_get_page_wpid( $page, $break_cache=false ) { 
	$pages     = it_exchange_get_pages( $break_cache );
	$page_wpid = empty( $pages[$page]['wpid'] ) ? '0' : $pages[$page]['wpid'];
	return apply_filters( 'it_exchange_get_page_wpid', $page_wpid, $page, $break_cache );
}
/**
 * Is the page using a ghost page?
 *
 * @since 0.4.4
 *
 * @param string $page page setting
 * @return boolean
*/
function it_exchange_is_page_ghost_page( $page, $break_cache=false ) {
	$pages    = it_exchange_get_pages( $break_cache );
	$is_ghost = ( 'exchange' == it_exchange_get_page_type( $page, $break_cache ) );
	return apply_filters( 'it_exchange_is_page_ghost_page', $is_ghost, $page, $break_cache );
}

/**
 * Is the the current page what we're looking for? 
 *
 * @since 0.4.0
 * @todo add filters/actions
 *
 * @param string $page the exchange page were checking for
 * @return boolean
*/
function it_exchange_is_page( $page ) {
	global $wpdb;

	// Get slug for page
	$slug = it_exchange_get_page_slug( $page );

	// Get query var
	if ( ! $query_var = get_query_var( $slug ) )
		return false;

	// Return true if set and not product
	if ( $query_var && 'product' != $page )
		return true;

	// Are we doing AJAX, if so, grab product ID from it.
	if ( ! empty( $_GET['it-exchange-sw-ajax'] ) && ! empty( $_GET['sw-product'] ) ) {
		return (boolean) it_exchange_get_product( $_GET['sw-product'] );
	} else {
		// Try to get the post from the slug
		$sql = $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type = "it_exchange_prod" AND post_status = "publish" AND post_name = "%s"', $query_var );
		if ( $id = $wpdb->get_var( $sql ) )
			return true;
	}
	return false;
}

/**
 * Get permalink for ghost page
 *
 * @since 0.4.0
 * @todo add filters/actions
 *
 * @param string $page page setting
 * @return string url
*/
function it_exchange_get_page_url( $page, $clear_settings_cache=false ) {
    $pages = it_exchange_get_pages( $clear_settings_cache );

    if ( empty( $pages[$page]['url'] ) || ! is_callable( $pages[$page]['url'] ) )
        return false;

    if ( ! $url = call_user_func( $pages[$page]['url'], $page ) )
        return false;

    return $url;
}

/**
 * Registers a page with IT Exchange.
 *
 * Registering a page with Exchange does the following:
 *  - Creates a Ghost page for your page
 *  - Allows WP Admin to rename the slug and the display name of the page
 *  - Allows WP Admin to turn off its associated Ghost page and replace it with a WP page / shortcode
 *  - Allows it to be added to our nav list in Appearance -> menus
 *  - Allows template files to be added to themefolder/exchange/page-slug.php
 *  - Allows for content-page-slug.php template parts to be added to the list of possible template_names
 *  - Allows 3rd party add-ons to tell Exchange where to find the template parts
 *
 * Options:
 *  - slug          required. eg: store
 *  - name          required. eg: __( 'Store', 'LION' )
 *  - rewrite-rules required. an array. 1st element is priority within all exchange page rewrites. 2nd element is callback that will provide the rewrite array.
 *  - url           required. callback that will provide the url for the page. Make sure to check for permalinks
 *  - settings-name optional. The title given to the setting on Settings -> Pages
 *  - type          optional. the default value of the select box.
 *  - menu          optional. include this in the Exchange menu options under Appearances -> Menus?
 *  - optional      optional. Is the page requried? If not optional, Disable is removed from dropdown for type on Settings page
 *
 * Rewrites and URL options:
 *  - For working examples see it_exchange_register_core_pages() in ithemes-exchange/lib/functions/function.php
 *
 * @since 0.4.4
 *
 * @param string $page unique name it-exchange uses to refer to this page
 * @param array  $options page options
 * @return boolean
*/
function it_exchange_register_page( $page, $options ) {
	$pages = empty( $GLOBALS['it_exchange']['registered_pages'] ) ? array() : (array) $GLOBALS['it_exchange']['registered_pages'];

	// Page needs to be sanatized with underscores
	$page = str_replace( '-', '_', sanitize_title( $page ) );

	// Validate we have the data we need
	if ( empty( $options['slug'] ) || empty( $options['name'] ) || empty( $options['url'] ) )
		return false;

	// Defaults
	$defaults = array(
		'settings-name' => ucwords( $options['name'] ),
		'type'          => 'exchange',
		'wpid'          => 0,
		'menu'          => true,
		'optional'      => true,
	);

	// Merge with defaults
	$options = ITUtility::merge_defaults( $options, $defaults );

	$pages[sanitize_title( $page )] = $options;
	$GLOBALS['it_exchange']['registered_pages'] = $pages;
	do_action( 'it_exchnage_register_page', $page, $options );
	return true;
}

/**
 * Returns a list of registerd pages
 *
 * This returns pages that are registered, with their defaults. 
 * It DOES NOT RETURN THE ADMIN'S SETTINGS for those pages
 * For the admin's settings, use it_exchange_get_pages()
 *
 * @since 0.4.4
 * @return array
*/
function it_exchange_get_registered_pages( $options=array() ) {
	$pages = empty( $GLOBALS['it_exchange']['registered_pages'] ) ? array() : (array) $GLOBALS['it_exchange']['registered_pages'];
	if ( ! empty( $options['type'] ) ) {
		foreach( $pages as $page => $page_options ) {
			if ( $options['type'] != it_exchange_get_page_type( $page ) )
				unset( $pages[$page] );
		}
	}
	return $pages;
}

/**
 * Returns an array of WP page IDs to page names
 *
 * @since 0.4.0
 *
 * @return array
*/
function it_exchange_get_wp_pages( $options=array() ) {
	$defaults = array(
		'post_type' => 'page',
		'posts_per_page' => -1,
	);
	$options = ITUtility::merge_defaults( $options, $defaults );

	if ( ! $pages = get_posts( $options ) )
		$return = array();

	foreach( $pages as $page ) {
		$return[$page->ID] = get_the_title( $page->ID );
	}
	return apply_filters( 'it_exchange_get_wp_pages', $return, $options );	
}
