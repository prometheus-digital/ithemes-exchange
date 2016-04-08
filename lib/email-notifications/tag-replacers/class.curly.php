<?php
/**
 * Contains the curly tag replacer.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Curly_Tag_Replacer
 */
class IT_Exchange_Email_Curly_Tag_Replacer extends IT_Exchange_Email_Tag_Replacer_Base {

	/**
	 * Format a tag.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Tag|string $tag
	 *
	 * @return string
	 */
	public function format_tag( $tag ) {

		if ( ! $tag instanceof IT_Exchange_Email_Tag ) {
			$tag = $this->get_tag( $tag );
		}

		if ( ! $tag ) {
			return '';
		}

		return "{{{$tag->get_tag()}}}";
	}

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
	public function replace( $content, $context ) {
		$content = parent::replace( $content, $context );

		return preg_replace_callback( '/{{(.+?)}}/i', array( $this, '_replace' ), $content );
	}

	/**
	 * Get a map of tags to their replacements.
	 *
	 * @since 1.36
	 *
	 * @param string $content
	 * @param array  $context
	 *
	 * @return array
	 */
	public function get_replacement_map( $content, $context ) {

		$content = parent::replace( $content, $context );

		preg_match_all( '/{{(.+?)}}/i', $content, $matches );

		if ( empty( $matches ) || ! is_array( $matches ) || ! isset( $matches[0], $matches[1] ) ) {
			return array();
		}

		$replaced = array();

		foreach ( $matches[0] as $i => $match ) {

			if ( empty( $match ) ) {
				continue;
			}

			$replaced[ $matches[1][ $i ] ] = $this->_replace( array( $match, $matches[1][ $i ] ) );
		}

		return $replaced;
	}

	/**
	 * Replace tags.
	 *
	 * @since 1.36
	 *
	 * @param array $matches
	 *
	 * @return string
	 */
	public function _replace( $matches ) {

		list( $full, $contents ) = $matches;

		$split = explode( ':', $contents );

		$tag = $split[0];

		if ( empty( $split[1] ) ) {
			$options = array();
		} else {
			$options = explode( ',', $split[1] );
		}

		if ( $_tag = $this->get_tag( $tag ) ) {
			return $this->replace_tag( $_tag, $this->context, $options );
		} else {
			return $this->replace_legacy( $tag, $options );
		}
	}

	/**
	 * Transform all tags in a set of content to another format.
	 *
	 * Used when passing content to the templating system of the mail provider.
	 *
	 * @since 1.36
	 *
	 * @param string $open_tag  Format to be used for opening a tag.
	 * @param string $close_tag Format to be used for closing a tag.
	 * @param string $content   Content to be operated on.
	 *
	 * @return string
	 */
	public function transform_tags_to_format( $open_tag, $close_tag, $content ) {
		return preg_replace( '/{{(.+?)}}/i', $open_tag . '$1' . $close_tag, $content );
	}
}