<?php
/**
 * File Description
 *
 * @author    Iron Bound Designs
 * @since     1.0
 * @license   AGPL
 * @copyright Iron Bound Designs, 2016.
 */

/**
 * Class IT_Exchange_Email_Shortcode_Tag_Replacer
 */
interface IT_Exchange_Email_Tag_Replacer {
	/**
	 * Replace the email tags.
	 *
	 * @since 1.36
	 *
	 * @param string $content
	 * @param array  $context
	 *
	 * @return string
	 */
	public function replace( $content, $context );
}