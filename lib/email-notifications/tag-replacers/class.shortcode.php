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
	 * @var array
	 */
	private $context;

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

		if ( ! is_array( $context ) && ! $context instanceof ArrayAccess ) {
			throw new InvalidArgumentException( '$context must be an array.' );
		}

		$this->context = $context;

		it_exchange_email_notifications()->transaction_id = empty( $context['transaction'] ) ? false : $context['transaction']->get_ID();
		it_exchange_email_notifications()->customer_id    = empty( $context['customer'] ) ? false : $context['customer']->id;
		it_exchange_email_notifications()->user           = it_exchange_get_customer( it_exchange_email_notifications()->customer_id );

		$GLOBALS['it_exchange']['email-confirmation-data'] = $this->get_data();

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
	 * Get shortcode functions.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	protected function get_shortcode_functions() {

		if ( has_filter( 'it_exchange_email_notification_shortcode_functions' ) ) {
			it_exchange_deprecated_filter( 'it_exchange_email_notification_shortcode_functions', '1.36',
				'IT_Exchange_Email_Tag_Replacer::add_tag' );
		}

		/**
		 * Filter the available shortcode functions.
		 *
		 * @deprecated 1.36
		 *
		 * @since      1.0
		 *
		 * @param array $shortcode_functions
		 */
		return apply_filters( 'it_exchange_email_notification_shortcode_functions', array(), $this->get_data() );
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

		$data = $this->get_data();

		$supported_pairs = array( 'show' => '', 'options' => '' );

		$atts = shortcode_atts( $supported_pairs, $all_atts );
		$show = $atts['show'];

		$opts = explode( ',', $atts['options'] );
		unset( $all_atts['show'], $all_atts['options'] );

		$context = $this->context;

		$functions = $this->get_shortcode_functions();
		$tag       = $this->get_tag( $show );

		$r = false;

		if ( $tag ) {

			if ( count( array_diff( $tag->get_required_context(), array_keys( $context ) ) ) > 0 ) {
				$r = '';
			} else {
				$opts = array_merge( $opts, $all_atts );
				$r    = $tag->render( $context, $opts );
			}

		} elseif ( isset( $functions[ $show ] ) && is_callable( $functions[ $show ] ) ) {
			$r = call_user_func( $functions[ $show ], it_exchange_email_notifications(), $opts, $all_atts, $context );
		}

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
		return apply_filters( "it_exchange_email_notification_shortcode_{$show}", $r, $all_atts, $content, $data, $context );
	}

	/**
	 * Get the data array. This is mainly for back-compat.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	protected function get_data() {
		return array(
			0 => empty( $this->context['transaction'] ) ? null : it_exchange_get_transaction( $this->context['transaction'] ),
			1 => it_exchange_email_notifications()
		);
	}
}