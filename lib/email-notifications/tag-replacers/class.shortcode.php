<?php
/**
 * Contains the email tag replacer class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Shortcode_Tag_Replacer
 */
class IT_Exchange_Email_Shortcode_Tag_Replacer extends IT_Exchange_Email_Tag_Replacer_Base {

	/**
	 * IT_Exchange_Email_Tag_Replacer constructor.
	 */
	public function __construct() {
		add_shortcode( 'it_exchange_email', array( $this, 'shortcode' ) );
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
	public function replace( $content, $context = array() ) {

		$content = parent::replace( $content, $context );

		return do_shortcode( $content );
	}

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

		return "[it_exchange_email show={$tag->get_tag()}]";
	}

	/**
	 * Shortcode callback.
	 *
	 * @since 1.36
	 *
	 * @param array $all_atts
	 *
	 * @return string
	 */
	public function shortcode( $all_atts ) {

		$supported_pairs = array( 'show' => '', 'options' => '' );

		$atts = shortcode_atts( $supported_pairs, $all_atts );
		$show = $atts['show'];

		$opts    = explode( ',', $atts['options'] );
		$context = $this->context;

		if ( $tag = $this->get_tag( $show ) ) {
			$r = $this->replace_tag( $tag, $context, $opts );
		} else {
			$r = $this->replace_legacy( $show, $opts );
		}

		return $r;
	}
}