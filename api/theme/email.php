<?php
/**
 * Contains the email theme API class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Theme_API_Email
 */
class IT_Theme_API_Email implements IT_Theme_API {

	/**
	 * @var array
	 */
	private $context;

	/**
	 * IT_Theme_API_Email constructor.
	 */
	public function __construct() {
		$this->context = isset( $GLOBALS['it_exchange']['email_context'] ) ? $GLOBALS['it_exchange']['email_context'] : array();
	}

	/**
	 * @var array
	 */
	public $_tag_map = array(
		'message'                 => 'message',
		'logo'                    => 'logo',
		'headerlogo'              => 'header_logo',
		'headerlogosize'          => 'header_logo_size',
		'headerstorename'         => 'header_store_name',
		'headerstorenamefont'     => 'header_store_name_font',
		'headerstorenamesize'     => 'header_store_name_size',
		'headerstorenamecolor'    => 'header_store_name_color',
		'headerimage'             => 'header_image',
		'headerbackground'        => 'header_background',
		'bodyfont'                => 'body_font',
		'bodytextcolor'           => 'body_text_color',
		'bodyhighlightcolor'      => 'body_highlight_color',
		'bodybuttoncolor'         => 'body_button_color',
		'bodyfontsize'            => 'body_font_size',
		'bodybackgroundcolor'     => 'body_background_color',
		'bodybordercolor'         => 'body_border_color',
		'footertext'              => 'footer_text',
		'footertextcolor'         => 'footer_text_color',
		'footerlogo'              => 'footer_logo',
		'footerlogosize'          => 'footer_logo_size',
		'backgroundcolor'         => 'background_color',
		'backgroundimage'         => 'background_image',
		'backgroundimageposition' => 'background_image_position',
		'backgroundimagerepeat'   => 'background_image_repeat'
	);

	/**
	 * Retrieve the API context.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_api_context() {
		return 'email';
	}

	/**
	 * Returns the custom message for the email.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return bool|string
	 */
	public function message( $options = array() ) {

		$message = empty( $this->context['message'] ) ? '' : trim( $this->context['message'] );

		if ( ! empty( $options['has'] ) ) {
			return (bool) $message;
		}

		return $message;
	}

	/**
	 * Retrieve the logo.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return bool|string
	 */
	public function logo( $options = array() ) {

		$options = ITUtility::merge_defaults( $options, array(
			'size' => 'full'
		) );

		$attachment = IT_Exchange_Email_Customizer::get_setting( 'logo' );
		$url        = is_numeric( $attachment ) ? wp_get_attachment_image_url( $attachment, $options['size'] ) : $attachment;

		if ( $options['has'] ) {
			return (bool) $url;
		}

		return $url;
	}

	/**
	 * Retrieve the header logo.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return bool|string
	 */
	public function header_logo( $options = array() ) {

		$show = IT_Exchange_Email_Customizer::get_setting( 'header_show_logo' );
		$logo = $this->logo( $options );

		if ( $options['has'] ) {
			return $show && $logo;
		}

		return $show ? $logo : '';
	}

	/**
	 * Retrieve the header logo size.
	 *
	 * @since 1.36
	 *
	 * @return int
	 */
	public function header_logo_size() {
		return IT_Exchange_Email_Customizer::get_setting( 'header_logo_size' );
	}

	/**
	 * Retrieve the header store name.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function header_store_name( $options = array() ) {

		if ( $options['has'] ) {
			return IT_Exchange_Email_Customizer::get_setting( 'header_show_store_name' );
		}

		$general = it_exchange_get_option( 'settings_general' );

		return $general['company-name'];
	}

	/**
	 * Retrieve the font to use for the header.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function header_store_name_font( $options = array() ) {
		return $this->make_font_stack( IT_Exchange_Email_Customizer::get_setting( 'header_store_name_font' ) );
	}

	/**
	 * Get the store name font size.
	 *
	 * @since 1.36
	 *
	 * @return int
	 */
	public function header_store_name_size() {
		return IT_Exchange_Email_Customizer::get_setting( 'header_store_name_size' );
	}

	/**
	 * Get the store name color.
	 *
	 * @since 1.36
	 *
	 * @return int
	 */
	public function header_store_name_color() {
		return IT_Exchange_Email_Customizer::get_setting( 'header_store_name_color' );
	}

	/**
	 * Get the header image.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return bool|string
	 */
	public function header_image( $options = array() ) {

		$options = ITUtility::merge_defaults( $options, array(
			'size' => 'full'
		) );

		$attachment = IT_Exchange_Email_Customizer::get_setting( 'header_image' );

		if ( $options['has'] ) {
			return (bool) $attachment;
		}

		return wp_get_attachment_image_url( $attachment, $options['size'] );
	}

	/**
	 * Get the header background color.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function header_background( $options = array() ) {
		$color = IT_Exchange_Email_Customizer::get_setting( 'header_background' );

		return $color ? $color : 'transparent';
	}

	/**
	 * Get the body font.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function body_font( $options = array() ) {
		return $this->make_font_stack( IT_Exchange_Email_Customizer::get_setting( 'body_font' ) );
	}

	/**
	 * Get the body text color.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function body_text_color( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'body_text_color' );
	}

	/**
	 * Get the body highlight color.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function body_highlight_color( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'body_highlight_color' );
	}

	/**
	 * Get the body button color.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function body_button_color( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'body_button_color' );
	}

	/**
	 * Get the body font size.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function body_font_size( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'body_font_size' );
	}

	/**
	 * Get the body background color.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function body_background_color( $options = array() ) {
		$color = IT_Exchange_Email_Customizer::get_setting( 'body_background_color' );

		return $color ? $color : 'transparent';
	}

	/**
	 * Get the body border color.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function body_border_color( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'body_border_color' );
	}

	/**
	 * Get the footer text.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function footer_text( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'footer_text' );
	}

	/**
	 * Get the footer text color.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function footer_text_color( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'footer_text_color' );
	}

	/**
	 * Get the footer logo.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function footer_logo( $options = array() ) {

		$show = IT_Exchange_Email_Customizer::get_setting( 'footer_show_logo' );
		$logo = $this->logo( $options );

		if ( $options['has'] ) {
			return $show && $logo;
		}

		return $logo;
	}

	/**
	 * Get the footer logo size.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function footer_logo_size( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'footer_logo_size' );
	}

	/**
	 * Get the background color.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function background_color( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'background_color' );
	}

	/**
	 * Get the background image.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return bool|string
	 */
	public function background_image( $options = array() ) {

		$options = ITUtility::merge_defaults( $options, array(
			'size' => 'full'
		) );

		$attachment = IT_Exchange_Email_Customizer::get_setting( 'background_image' );

		if ( $options['has'] ) {
			return (bool) $attachment;
		}

		return wp_get_attachment_image_url( $attachment, $options['size'] );
	}

	/**
	 * Get the background image position.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function background_image_position( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'background_image_position' );
	}

	/**
	 * Get the background image repeat.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function background_image_repeat( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'background_image_repeat' );
	}

	/**
	 * Make a font stack from a choice.
	 *
	 * @since 1.36
	 *
	 * @param string $choice
	 *
	 * @return string
	 */
	protected function make_font_stack( $choice ) {

		switch ( $choice ) {
			case 'serif':
				return "'Georgia', 'Times New Roman', serif";
			case 'sans-serif':
				return "'Helvetica', Arial, sans-serif";
			case 'monospace':
				return 'Courier, Monaco, monospace';
			default:
				return $choice;
		}
	}
}