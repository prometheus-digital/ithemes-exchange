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
	public function replace( $content, $context ) {

		$this->context = $context;

		$GLOBALS['it_exchange']['email-confirmation-data'] = $this->get_data();

		it_exchange_email_notifications()->transaction_id = empty( $context['transaction'] ) ? false : $context['transaction']->ID;
		it_exchange_email_notifications()->customer_id    = empty( $context['customer'] ) ? false : $context['customer']->id;
		it_exchange_email_notifications()->user           = it_exchange_get_customer( it_exchange_email_notifications()->customer_id );

		return do_shortcode( $content );
	}

	/**
	 * Get shortcode functions.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	protected function get_shortcode_functions() {

		$shortcode_functions = array();

		/**
		 * Filter the available shortcode functions.
		 *
		 * @since 1.0
		 *
		 * @param array $shortcode_functions
		 */
		return apply_filters( 'it_exchange_email_notification_shortcode_functions', $shortcode_functions, $this->get_data() );
	}

	/**
	 * Shortcode callback.
	 *
	 * @since 1.36
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function shortcode( $atts, $content = '' ) {

		$data = $this->get_data();

		$supported_pairs = array( 'show' => '', 'options' => '' );

		$atts = shortcode_atts( $supported_pairs, $atts );
		$show = $atts['show'];

		unset( $atts['show'] );
		$opts = explode( ',', $atts['options'] );

		$context = $this->context;

		$functions = $this->get_shortcode_functions();
		$tag       = $this->get_tag( $show );

		$r = false;

		if ( $tag ) {

			if ( count( array_diff( $tag->get_required_context(), array_keys( $context ) ) ) > 0 ) {
				$r = '';
			} else {
				$opts = array_merge( $opts, $atts );
				$r    = $tag->render( $context, $opts );
			}

		} elseif ( is_callable( $functions[ $show ] ) ) {
			$r = call_user_func( $functions[ $show ], it_exchange_email_notifications(), $opts, $atts, $context );
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
		return apply_filters( "it_exchange_email_notification_shortcode_{$show}", $r, $atts, $content, $data, $context );
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