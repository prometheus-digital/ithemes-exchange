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
	 * @param array  $all_atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function shortcode( $all_atts, $content = '' ) {

		$supported_pairs = array( 'show' => '', 'options' => '' );

		$atts = shortcode_atts( $supported_pairs, $all_atts );
		$show = $atts['show'];

		$opts = explode( ',', $atts['options'] );
		unset( $all_atts['show'], $all_atts['options'] );

		$context = $this->context;

		$functions = $this->get_legacy_functions();
		$tag       = $this->get_tag( $show );

		$r = false;

		if ( $tag ) {
			$r = $this->replace_tag( $tag, $context, array_merge( $opts, $all_atts ) );
		} elseif ( isset( $functions[ $show ] ) && is_callable( $functions[ $show ] ) ) {
			$r = call_user_func( $functions[ $show ], it_exchange_email_notifications(), $opts, $all_atts, $context );

			$data = $this->get_data();

			/**
			 * Filter the shortcode response.
			 *
			 * @since 1.0
			 *
			 * @param string $r
			 * @param array  $atts
			 * @param string $content
			 * @param array  $data
			 * @param array  $context
			 */
			$r = apply_filters( "it_exchange_email_notification_shortcode_{$show}", $r, $all_atts, $content, $data );
		}

		return $r;
	}
}