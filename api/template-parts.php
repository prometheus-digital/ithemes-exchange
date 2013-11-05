<?php
/**
 * This file includes all of the calls that allow add-ons to interact with the template API
 * @since 1.1.0
 * @package IT_Exchange
*/

/**
 * Returns an array of template part loop slugs, filterable by add-ons
 *
 * @since 1.1.0
 *
 * @param string $context usually the template-part its being called from
 * @param string $detail usually an identifying slug to indicated where we are on the template-part
 * @param array  $parts   an array of template part files in the loops folder for the $context that we want included
 * @return array
*/
function it_exchange_get_template_part_loops( $context, $detail, $parts ) {
	$details = apply_filters( 'it_exchange_get_' . $context . '_' . $detail . '_loops', $parts );
	return (array) $details;
}

/**
 * Returns an array of template part element slugs, filterable by add-ons
 *
 * @since 1.1.0
 *
 * @param string $context usually the template-part its being called from
 * @param string $detail usually an identifying slug to indicated where we are on the template-part
 * @param array  $parts   an array of template part files in the elements folder for the $context that we want included
 * @return array
*/
function it_exchange_get_template_part_elements( $context, $detail, $parts ) {
	$details = apply_filters( 'it_exchange_get_' . $context . '_' . $detail . '_elements', $parts );
	return (array) $details;
}
