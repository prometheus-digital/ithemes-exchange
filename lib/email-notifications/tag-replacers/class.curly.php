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
}