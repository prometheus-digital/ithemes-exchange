<?php
/**
 * This file includes all of the calls that allow add-ons to interact with the template API
 * @since 1.1.0
 * @package IT_Exchange
*/

/**
 * Returns an array of template part slugs, filterable by add-ons
 *
 * @since 1.1.0 
 *
 * @return array
*/
function it_exchange_get_template_part_elements( $context, $detail, $parts ) {
	$details = apply_filters( 'it_exchange_get_' . $context . '_' . $detail . '_elements', $parts );
	return (array) $details;
}