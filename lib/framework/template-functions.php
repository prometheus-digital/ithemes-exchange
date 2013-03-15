<?php
/**
 * Templating. Lifted from bbpress... kind of
 * @since 0.3.8
 * @package IT_Cart_Buddy
*/

/**
 * Retrieves a template part
 *
 * @since 0.3.8
 * @param string $slug
 * @param string $name Optional. Default null
 * @return mixed template
 */

function it_cart_buddy_get_template_part( $slug, $name=null, $load=true ) {
    // Execute code for this part
    do_action( 'get_template_part_' . $slug, $slug, $name );

    // Setup possible parts
    $templates = array();
    if ( isset( $name ) )
        $templates[] = $slug . '-' . $name . '.php';
    $templates[] = $slug . '.php';

    // Allow template parst to be filtered
    $templates = apply_filters( 'it_cart_buddy_get_template_part', $templates, $slug, $name );

    // Return the part that is found
    return it_cart_buddy_locate_template( $templates, $load, false );
}

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
 * inherit from a parent theme can just overload one file. If the template is
 * not found in either of those, it looks in the lib/templates folder last.
 *
 * Taken from bbPress
 *
 * @since 0.3.8
 * @param mixed $template_names Template file(s) to search for, in order.
 * @param boolean $load If true the template file will be loaded if it is found.
 * @param boolean $require_once Whether to require_once or require. Default true.
 * @return string The template filename if one is located.
 */
function it_cart_buddy_locate_template( $template_names, $load = false, $require_once = true ) { 
    // No file found yet
    $located = false;

    // Try to find a template file
    foreach ( (array) $template_names as $template_name ) { 

        // Continue if template is empty
        if ( empty( $template_name ) ) 
            continue;

        // Trim off any slashes from the template name
        $template_name = ltrim( $template_name, '/' );

        // Check child theme first
        if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'cart_buddy_templates/' . $template_name ) ) { 
            $located = trailingslashit( get_stylesheet_directory() ) . 'cart_buddy_templates/' . $template_name;
            break;

        // Check parent theme next
        } elseif ( file_exists( trailingslashit( get_template_directory() ) . 'cart_buddy_templates/' . $template_name ) ) { 
            $located = trailingslashit( get_template_directory() ) . 'cart_buddy_templates/' . $template_name;
            break;

        // Check templates folder last
        } elseif ( file_exists( dirname( dirname( __FILE__ ) ) . '/templates/' . $template_name ) ) { 
            $located = dirname( dirname( __FILE__ ) ) . '/templates/' . $template_name;
            break;
        }   
    }   

    if ( ( true == $load ) && ! empty( $located ) ) {
        load_template( $located, $require_once );
		it_cart_buddy_unset_template_part_args( rtrim( $template_name, '.php' ) );
	}

    return $located;
}

/**
 * Sets some variables for use in template parts
 *
 * Stores them in globals, keyed by the template part slug / name
 *
 * @since 0.3.8
 * @param array $args args for the template part.
 * @param string $slug template part slug
 * @param string $name optional name of template part
 * @return void
*/
function it_cart_buddy_set_template_part_args( $args, $slug, $name=false ) {

	// Set the slug
	$key = empty( $name ) ? $slug : $slug . '-' . $name;

	// Store the options
	$GLOBALS['it_cart_buddy']['template_part_args'][$key] = $args;
}

/**
 * Retrieves args for template parts
 *
 * @since 0.3.8
 * @param $template_part key for the template part. File name without .php
 * @return mixed
*/
function it_cart_buddy_get_template_part_args( $template_part ) {
	$args = empty( $GLOBALS['it_cart_buddy']['template_part_args'][$template_part] ) ? false : $GLOBALS['it_cart_buddy']['template_part_args'][$template_part] ;

	return apply_filters( 'it_cart_buddy_template_part_args-' . $template_part, $args );
}

/**
 * This unsets the template part args for a specific template
 *
 * @since 0.3.8
 * @param string $template_name name of template part
 * @return void
*/
function it_cart_buddy_unset_template_part_args( $template_name ) {
	if ( isset( $GLOBALS['it_cart_buddy']['template_part_args'][$template_name] ) )
		unset( $GLOBALS['it_cart_buddy']['template_part_args'][$template_name] );
}
