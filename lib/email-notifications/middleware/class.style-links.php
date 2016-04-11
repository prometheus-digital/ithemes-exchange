<?php
/**
 * Contains middleware to auto style links.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Middleware_Style_Links
 */
class IT_Exchange_Email_Middleware_Style_Links implements IT_Exchange_Email_Middleware {

	/**
	 * Handle a sendable object before it has been sent.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Sendable_Mutable_Wrapper $sendable
	 *
	 * @return bool True to continue, false to stop email sending.
	 */
	public function handle( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {

		$sendable->override_body(
			preg_replace_callback( "#(<a[^>]+?)>#is", array( $this, '_replace_link' ), $sendable->get_body() )
		);

		return true;
	}

	/**
	 * Preg replace callback.
	 *
	 * @internal
	 *
	 * @since 1.36
	 *
	 * @param array $match
	 *
	 * @return string
	 */
	public function _replace_link( $match ) {

		list( $full, $part ) = $match;

		$highlight = it_exchange( 'email', 'get-body-highlight-color' );

		$style = preg_replace_callback( '/style=[\'"](.*)[\'"]/is', array( $this, '_replace_style' ), $part, -1, $count );

		// if we already modified the style attribute to add our color tag
		if ( $style != $part ) {
			$ret = "$style>";
		} elseif ( ! $count ) { // no style attributes were found
			$ret = "$part style=\"color: $highlight;\">";
		} else { // a style attribute was found with a color tag already
			$ret = $full;
		}

		return $ret;
	}

	/**
	 * Replace the style attribute.
	 *
	 * @param array $existing_styles
	 *
	 * @return string
	 */
	public function _replace_style( $existing_styles ) {

		list( $full, $match ) = $existing_styles;

		// if a color attribute isn't already present
		if ( ! preg_match( '/(?:[;\s]|^)color\s*:/is', $match ) ) {

			$highlight = it_exchange( 'email', 'get-body-highlight-color' );

			return "style=\"color: $highlight;$match\"";
		}

		return $full;
	}
}