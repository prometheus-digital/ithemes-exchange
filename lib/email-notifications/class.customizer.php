<?php
/**
 * Contains the Email Customizer class.
 *
 * @since   1.36
 * @license GPLV2
 */

/**
 * Class IT_Exchange_Email_Customizer
 */
class IT_Exchange_Email_Customizer {

	/**
	 * @var string
	 */
	private $capability = '';

	/**
	 * @var WP_Customize_Section[]
	 */
	private $sections = array();

	/**
	 * @var WP_Customize_Setting[]
	 */
	private $settings = array();

	/**
	 * @var WP_Customize_Control[]
	 */
	private $controls = array();

	/**
	 * IT_Exchange_Email_Customizer constructor.
	 *
	 * @since 1.36
	 */
	public function __construct() {

		add_action( 'customize_register', array( $this, 'on_register' ), 20 );
		add_filter( 'customize_loaded_components', array( $this, 'restrict_components' ) );
		add_filter( 'customize_section_active', array( $this, 'restrict_sections' ), 20, 2 );
		add_filter( 'template_include', array( $this, 'overload_template' ), 100 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_control_scripts' ) );
		add_action( 'customize_preview_init', array( $this, 'enqueue_preview_scripts' ) );
		add_action( 'init', array( $this, 'dequeue_scripts_and_actions' ) );

		$this->capability = it_exchange_get_admin_menu_capability( 'email-customize' );
	}

	/**
	 * Retrieve the email settings.
	 *
	 * @since 1.36
	 *
	 * @param string $setting Optionally, provide a specific setting to retrieve.
	 *
	 * @return mixed|null
	 */
	public static function get_setting( $setting = '' ) {

		$settings = get_option( 'it-exchange-email', array() );

		if ( ! $setting ) {
			return $settings;
		}

		if ( isset( $settings[ $setting ] ) ) {
			return $settings[ $setting ];
		} elseif ( ( $default = self::get_default( $setting ) ) !== '' ) {
			return $default;
		}

		return null;
	}

	/**
	 * Fires when the customizer is registered.
	 *
	 * @since 1.36
	 *
	 * @param WP_Customize_Manager $customizer
	 */
	public function on_register( WP_Customize_Manager $customizer ) {

		$this->sections = $this->get_sections( $customizer );

		foreach ( $this->sections as $section ) {
			$customizer->add_section( $section );
		}

		$this->settings = $this->get_settings( $customizer );

		foreach ( $this->settings as $setting ) {

			if ( empty( $setting->default ) ) {
				$setting->default = $this->get_default( $setting->id );
			}

			$customizer->add_setting( $setting );
		}

		$this->controls = $this->get_controls( $customizer );

		foreach ( $this->controls as $control ) {
			$customizer->add_control( $control );
		}
	}

	/**
	 * Get all customizer sections.
	 *
	 * @since 1.36
	 *
	 * @param WP_Customize_Manager $customizer
	 *
	 * @return WP_Customize_Section[]
	 */
	protected function get_sections( WP_Customize_Manager &$customizer ) {
		return array(
			new WP_Customize_Section( $customizer, 'it-exchange-email-structure', array(
				'capability'      => $this->capability,
				'active_callback' => array( __CLASS__, 'is_active' ),
				'title'           => __( 'Structure', 'it-l10n-ithemes-exchange' ),
				'priority'        => 1
			) ),
			new WP_Customize_Section( $customizer, 'it-exchange-email-header', array(
				'capability'      => $this->capability,
				'active_callback' => array( __CLASS__, 'is_active' ),
				'title'           => __( 'Header', 'it-l10n-ithemes-exchange' ),
				'priority'        => 5
			) ),
			new WP_Customize_Section( $customizer, 'it-exchange-email-body', array(
				'capability'      => $this->capability,
				'active_callback' => array( __CLASS__, 'is_active' ),
				'title'           => __( 'Body', 'it-l10n-ithemes-exchange' ),
				'priority'        => 10
			) ),
			new WP_Customize_Section( $customizer, 'it-exchange-email-footer', array(
				'capability'      => $this->capability,
				'active_callback' => array( __CLASS__, 'is_active' ),
				'title'           => __( 'Footer', 'it-l10n-ithemes-exchange' ),
				'priority'        => 15
			) ),
			new WP_Customize_Section( $customizer, 'it-exchange-email-background', array(
				'capability'      => $this->capability,
				'active_callback' => array( __CLASS__, 'is_active' ),
				'title'           => __( 'Background', 'it-l10n-ithemes-exchange' ),
				'priority'        => 20
			) ),
			new WP_Customize_Section( $customizer, 'it-exchange-email-presets', array(
				'capability'      => $this->capability,
				'active_callback' => array( __CLASS__, 'is_active' ),
				'title'           => __( 'Presets', 'it-l10n-ithemes-exchange' ),
				'priority'        => 100
			) )
		);
	}

	/**
	 * Get all customizer settings.
	 *
	 * @since 1.36
	 *
	 * @param WP_Customize_Manager $customizer
	 *
	 * @return WP_Customize_Setting[]
	 */
	protected function get_settings( WP_Customize_Manager &$customizer ) {
		return array(
			new WP_Customize_Setting( $customizer, 'it-exchange-email[logo]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[layout]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage',
				'default'    => 'boxed'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[header_show_logo]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[header_logo_size]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[header_show_store_name]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[header_store_name_font]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[header_store_name_size]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[header_store_name_color]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[header_image]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[header_background]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[body_font]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[body_text_color]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[body_highlight_color]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[body_font_size]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[body_background_color]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[footer_text]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage',
				'default'    => $this->get_default_footer_text()
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[footer_text_color]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[footer_show_logo]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[footer_logo_size]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage',
				'default'    => 75
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[footer_background]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage',
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[background_color]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[background_image]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[background_image_position]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage',
				'default'    => 'center'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[background_image_repeat]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage',
				'default'    => 'repeat'
			) ),
			new WP_Customize_Setting( $customizer, 'it-exchange-email[preset]', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'transport'  => 'postMessage',
			) )
		);
	}

	/**
	 * Get all customizer controls.
	 *
	 * @since 1.36
	 *
	 * @param WP_Customize_Manager $customizer
	 *
	 * @return WP_Customize_Control[]
	 */
	protected function get_controls( WP_Customize_Manager &$customizer ) {
		return array(

			/** Structure */

			new WP_Customize_Image_Control( $customizer, 'it-exchange-email[logo]', array(
				'capability' => $this->capability,
				'section'    => 'it-exchange-email-structure',
				'label'      => __( 'Logo', 'it-l10n-ithemes-exchange' )
			) ),

			new WP_Customize_Control( $customizer, 'it-exchange-email[layout]', array(
				'capability' => $this->capability,
				'section'    => 'it-exchange-email-structure',
				'label'      => __( 'Layout', 'it-l10n-ithemes-exchange' ),
				'type'       => 'radio',
				'choices'    => array(
					'full'  => __( 'Full-width', 'it-l10n-ithemes-exchange' ),
					'boxed' => __( 'Boxed', 'it-l10n-ithemes-exchange' ),
				)
			) ),

			/** Header */

			new WP_Customize_Control( $customizer, 'it-exchange-email[header_show_logo]', array(
				'capability' => $this->capability,
				'section'    => 'it-exchange-email-header',
				'label'      => __( 'Show Logo', 'it-l10n-ithemes-exchange' ),
				'type'       => 'checkbox'
			) ),
			new WP_Customize_Control( $customizer, 'it-exchange-email[header_logo_size]', array(
				'capability'      => $this->capability,
				'section'         => 'it-exchange-email-header',
				'label'           => __( 'Logo Size', 'it-l10n-ithemes-exchange' ),
				'type'            => 'range',
				'input_attrs'     => array(
					'min' => 50,
					'max' => 350
				),
				'active_callback' => array(
					new IT_Exchange_Email_Customize_Active_Callback( 'header_show_logo' ),
					'active'
				)
			) ),
			new WP_Customize_Control( $customizer, 'it-exchange-email[header_show_store_name]', array(
				'capability' => $this->capability,
				'section'    => 'it-exchange-email-header',
				'label'      => __( 'Show Store Name', 'it-l10n-ithemes-exchange' ),
				'type'       => 'checkbox'
			) ),
			new WP_Customize_Control( $customizer, 'it-exchange-email[header_store_name_font]', array(
				'capability'      => $this->capability,
				'section'         => 'it-exchange-email-header',
				'label'           => __( 'Store Name Font', 'it-l10n-ithemes-exchange' ),
				'type'            => 'select',
				'choices'         => array(
					'serif'      => 'Serif',
					'sans-serif' => 'Sans-serif',
					'monospace'  => 'Monospace'
				),
				'active_callback' => array(
					new IT_Exchange_Email_Customize_Active_Callback( 'header_show_store_name' ),
					'active'
				)
			) ),
			new WP_Customize_Control( $customizer, 'it-exchange-email[header_store_name_size]', array(
				'capability'      => $this->capability,
				'section'         => 'it-exchange-email-header',
				'label'           => __( 'Store Name Size', 'it-l10n-ithemes-exchange' ),
				'type'            => 'range',
				'input_attrs'     => array(
					'min' => 12,
					'max' => 96
				),
				'active_callback' => array(
					new IT_Exchange_Email_Customize_Active_Callback( 'header_show_store_name' ),
					'active'
				)
			) ),
			new WP_Customize_Color_Control( $customizer, 'it-exchange-email[header_store_name_color]', array(
				'capability'      => $this->capability,
				'section'         => 'it-exchange-email-header',
				'label'           => __( 'Store Name Color', 'it-l10n-ithemes-exchange' ),
				'active_callback' => array(
					new IT_Exchange_Email_Customize_Active_Callback( 'header_show_store_name' ),
					'active'
				)
			) ),
			new WP_Customize_Cropped_Image_Control( $customizer, 'it-exchange-email[header_image]', array(
				'capability'  => $this->capability,
				'section'     => 'it-exchange-email-header',
				'label'       => __( 'Header Image', 'it-l10n-ithemes-exchange' ),
				'width'       => 640,
				'height'      => 225,
				'flex-height' => true
			) ),
			new WP_Customize_Color_Control( $customizer, 'it-exchange-email[header_background]', array(
				'capability'  => $this->capability,
				'section'     => 'it-exchange-email-header',
				'label'       => __( 'Background Color', 'it-l10n-ithemes-exchange' ),
				'description' => __( 'Leave blank to make the background transparent.', 'it-l10n-ithemes-exchange' )
			) ),

			/** Body */

			new WP_Customize_Control( $customizer, 'it-exchange-email[body_font]', array(
				'capability' => $this->capability,
				'section'    => 'it-exchange-email-body',
				'label'      => __( 'Font', 'it-l10n-ithemes-exchange' ),
				'type'       => 'select',
				'choices'    => array(
					'serif'      => 'Serif',
					'sans-serif' => 'Sans-serif',
					'monospace'  => 'Monospace'
				)
			) ),
			new WP_Customize_Control( $customizer, 'it-exchange-email[body_font_size]', array(
				'capability'  => $this->capability,
				'section'     => 'it-exchange-email-body',
				'label'       => __( 'Font Size', 'it-l10n-ithemes-exchange' ),
				'type'        => 'range',
				'input_attrs' => array(
					'min' => 8,
					'max' => 18
				),
			) ),
			new WP_Customize_Color_Control( $customizer, 'it-exchange-email[body_text_color]', array(
				'capability' => $this->capability,
				'section'    => 'it-exchange-email-body',
				'label'      => __( 'Text Color', 'it-l10n-ithemes-exchange' )
			) ),
			new WP_Customize_Color_Control( $customizer, 'it-exchange-email[body_highlight_color]', array(
				'capability' => $this->capability,
				'section'    => 'it-exchange-email-body',
				'label'      => __( 'Highlight Color', 'it-l10n-ithemes-exchange' )
			) ),
			new WP_Customize_Color_Control( $customizer, 'it-exchange-email[body_background_color]', array(
				'capability'  => $this->capability,
				'section'     => 'it-exchange-email-body',
				'label'       => __( 'Background Color', 'it-l10n-ithemes-exchange' ),
				'description' => __( 'Leave blank to make the background transparent.', 'it-l10n-ithemes-exchange' )
			) ),

			/** Footer */

			new WP_Customize_Control( $customizer, 'it-exchange-email[footer_text]', array(
				'capability' => $this->capability,
				'section'    => 'it-exchange-email-footer',
				'type'       => 'textarea',
				'label'      => __( 'Footer Text', 'it-l10n-ithemes-exchange' ),
			) ),
			new WP_Customize_Color_Control( $customizer, 'it-exchange-email[footer_text_color]', array(
				'capability'      => $this->capability,
				'section'         => 'it-exchange-email-footer',
				'label'           => __( 'Text Color', 'it-l10n-ithemes-exchange' ),
				'active_callback' => array(
					new IT_Exchange_Email_Customize_Active_Callback( 'footer_text' ),
					'active'
				)
			) ),
			new WP_Customize_Control( $customizer, 'it-exchange-email[footer_show_logo]', array(
				'capability' => $this->capability,
				'section'    => 'it-exchange-email-footer',
				'label'      => __( 'Show Logo', 'it-l10n-ithemes-exchange' ),
				'type'       => 'checkbox'
			) ),
			new WP_Customize_Control( $customizer, 'it-exchange-email[footer_logo_size]', array(
				'capability'      => $this->capability,
				'section'         => 'it-exchange-email-footer',
				'label'           => __( 'Logo Size', 'it-l10n-ithemes-exchange' ),
				'type'            => 'range',
				'input_attrs'     => array(
					'min' => 16,
					'max' => 350
				),
				'active_callback' => array(
					new IT_Exchange_Email_Customize_Active_Callback( 'footer_show_logo' ),
					'active'
				)
			) ),
			new WP_Customize_Color_Control( $customizer, 'it-exchange-email[footer_background]', array(
				'capability'  => $this->capability,
				'section'     => 'it-exchange-email-footer',
				'label'       => __( 'Background Color', 'it-l10n-ithemes-exchange' ),
				'description' => __( 'Leave blank to make the background transparent.', 'it-l10n-ithemes-exchange' )
			) ),

			/** Background */

			new WP_Customize_Color_Control( $customizer, 'it-exchange-email[background_color]', array(
				'capability'  => $this->capability,
				'section'     => 'it-exchange-email-background',
				'label'       => __( 'Background Color', 'it-l10n-ithemes-exchange' ),
				'description' =>
					__( 'For best compatibility, always provide a background color. Even when a background image is in use. ', 'it-l10n-ithemes-exchange' )
			) ),
			new WP_Customize_Image_Control( $customizer, 'it-exchange-email[background_image]', array(
				'capability'  => $this->capability,
				'section'     => 'it-exchange-email-background',
				'label'       => __( 'Background Image', 'it-l10n-ithemes-exchange' ),
				'description' =>
					sprintf( __( 'We recommend using a %ssubtle pattern%s as a background image.', 'it-l10n-ithemes-exchange' ),
						'<a href="http://subtlepatterns.com">', '</a>' )
			) ),
			new WP_Customize_Control( $customizer, 'it-exchange-email[background_image_position]', array(
				'capability'      => $this->capability,
				'section'         => 'it-exchange-email-background',
				'label'           => __( 'Background Position', 'it-l10n-ithemes-exchange' ),
				'type'            => 'radio',
				'choices'         => array(
					'left'   => __( 'Left', 'it-l10n-ithemes-exchange' ),
					'center' => __( 'Center', 'it-l10n-ithemes-exchange' ),
					'right'  => __( 'Right', 'it-l10n-ithemes-exchange' ),
				),
				'active_callback' => array(
					new IT_Exchange_Email_Customize_Active_Callback( 'background_image' ),
					'active'
				)
			) ),
			new WP_Customize_Control( $customizer, 'it-exchange-email[background_image_repeat]', array(
				'capability'      => $this->capability,
				'section'         => 'it-exchange-email-background',
				'label'           => __( 'Background Repeat', 'it-l10n-ithemes-exchange' ),
				'type'            => 'radio',
				'choices'         => array(
					'no-repeat' => __( 'No Repeat', 'it-l10n-ithemes-exchange' ),
					'repeat'    => __( 'Tile', 'it-l10n-ithemes-exchange' ),
					'repeat-x'  => __( 'Tile Horizontally', 'it-l10n-ithemes-exchange' ),
					'repeat-y'  => __( 'Tile Vertically', 'it-l10n-ithemes-exchange' ),
				),
				'active_callback' => array(
					new IT_Exchange_Email_Customize_Active_Callback( 'background_image' ),
					'active'
				)
			) ),

			/** Presets */

			new WP_Customize_Control( $customizer, 'it-exchange-email[preset]', array(
				'capability' => $this->capability,
				'section'    => 'it-exchange-email-presets',
				'type'       => 'select',
				'label'      => __( 'Preset', 'it-l10n-ithemes-exchange' ),
				'choices'    => $this->get_preset_choices()
			) )
		);
	}

	/**
	 * Get the default footer text.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	protected function get_default_footer_text() {

		$settings = it_exchange_get_option( 'settings_general' );
		$name     = $settings['company-name'];

		if ( it_exchange_get_page_type( 'store' ) === 'disabled' ) {
			$url = site_url();
		} else {
			$url = it_exchange_get_page_url( 'store' );
		}

		$link = "<b><u><i><a href='$url'>$name</a></i></u></b>";

		return sprintf( __( '%s - %sPowered by iThemes Exchange%s', 'it-l10n-ithemes-exchange' ), $link, '<i>', '</i>' );
	}

	/**
	 * Get the available presets.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	protected static function get_presets() {

		$presets = array(
			'default'  => array(
				'label'    => __( 'Default', 'it-l10n-ithemes-exchange' ),
				'settings' => array(
					'layout'                  => 'boxed',
					'header_show_store_name'  => true,
					'header_store_name_font'  => 'serif',
					'header_store_name_size'  => 64,
					'header_store_name_color' => '#666666',
					'header_background'       => '',
					'header_image'            => '',
					'body_font'               => 'serif',
					'body_text_color'         => '#444444',
					'body_highlight_color'    => '#dddddd',
					'body_font_size'          => '14',
					'body_background_color'   => '#efefef',
					'footer_text_color'       => '#444444',
					'footer_show_logo'        => false,
					'footer_background'       => '',
					'background_color'        => '#efefef',
					'background_image'        => '',
				)
			),
			'dark'     => array(
				'label'    => __( 'Dark', 'it-l10n-ithemes-exchange' ),
				'settings' => array(
					'layout'                  => 'boxed',
					'header_image'            => self::sideload( ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/header-image1.jpg' ) ),
					'header_show_store_name'  => false,
					'header_store_name_font'  => 'monospace',
					'header_store_name_size'  => 64,
					'header_store_name_color' => '#ffffff',
					'header_background'       => '#383e43',
					'body_font'               => 'sans-serif',
					'body_text_color'         => '#ffffff',
					'body_highlight_color'    => '#dddddd',
					'body_font_size'          => '14',
					'body_background_color'   => '#2a2f33',
					'footer_text_color'       => '#ffffff',
					'footer_show_logo'        => false,
					'footer_background'       => '',
					'background_color'        => '#2a2f33',
					'background_image'        => '',
				)
			),
			'white'    => array(
				'label'    => __( 'White', 'it-l10n-ithemes-exchange' ),
				'settings' => array(
					'layout'                  => 'boxed',
					'header_image'            => self::sideload( ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/header-image1.jpg' ) ),
					'header_show_store_name'  => false,
					'header_store_name_font'  => 'serif',
					'header_store_name_size'  => 64,
					'header_store_name_color' => '#efefef',
					'header_background'       => '#444444',
					'body_font'               => 'sans-serif',
					'body_text_color'         => '#444444',
					'body_highlight_color'    => '#dedede',
					'body_font_size'          => '14',
					'body_background_color'   => '#ffffff',
					'footer_text_color'       => '#444444',
					'footer_show_logo'        => false,
					'footer_background'       => '',
					'background_color'        => '#ffffff',
					'background_image'        => '',
				)
			),
			'ocean'    => array(
				'label'    => __( 'Ocean', 'it-l10n-ithemes-exchange' ),
				'settings' => array(
					'layout'                  => 'boxed',
					'header_image'            => self::sideload( ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/header-image2.jpg' ) ),
					'header_show_store_name'  => false,
					'header_store_name_font'  => 'sans-serif',
					'header_store_name_size'  => 64,
					'header_store_name_color' => '#efefef',
					'header_background'       => '#006186',
					'body_font'               => 'sans-serif',
					'body_text_color'         => '#40636f',
					'body_highlight_color'    => '#eae1c1',
					'body_font_size'          => '14',
					'body_background_color'   => '#ffffff',
					'footer_text_color'       => '#ffffff',
					'footer_show_logo'        => false,
					'footer_background'       => '',
					'background_color'        => '#338ea6',
					'background_image'        => '',
				)
			),
			'gold'     => array(
				'label'    => __( 'Gold', 'it-l10n-ithemes-exchange' ),
				'settings' => array(
					'layout'                  => 'boxed',
					'header_image'            => self::sideload( ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/header-image4.jpg' ) ),
					'header_show_store_name'  => false,
					'header_store_name_font'  => 'sans-serif',
					'header_store_name_size'  => 64,
					'header_store_name_color' => '#efefef',
					'header_background'       => '#bc9400',
					'body_font'               => 'sans-serif',
					'body_text_color'         => '#40636f',
					'body_highlight_color'    => '#eae1c1',
					'body_font_size'          => '14',
					'body_background_color'   => '#ffffff',
					'footer_text_color'       => '#40636f',
					'footer_show_logo'        => false,
					'footer_background'       => '',
					'background_color'        => '#e9e0bf',
					'background_image'        => '',
				)
			),
			'floral'   => array(
				'label'    => __( 'Floral', 'it-l10n-ithemes-exchange' ),
				'settings' => array(
					'layout'                  => 'boxed',
					'header_image'            => self::sideload( ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/header-image3.jpg' ) ),
					'header_show_store_name'  => false,
					'header_store_name_font'  => 'serif',
					'header_store_name_size'  => '51',
					'header_store_name_color' => '#ffffff',
					'header_background'       => '#192936',
					'body_font'               => 'serif',
					'body_highlight_color'    => '#a1d7ca',
					'body_background_color'   => '#ffffff',
					'body_text_color'         => '#124340',
					'footer_text_color'       => '#112a31',
					'footer_show_logo'        => true,
					'footer_logo_size'        => '80',
					'footer_background'       => '',
					'background_color'        => '#bbccc6',
					'background_image'        => '',
				)
			),
			'homebrew' => array(
				'label'    => __( 'Homebrew', 'it-l10n-ithemes-exchange' ),
				'settings' => array(
					'layout'                  => 'boxed',
					'header_show_store_name'  => true,
					'header_store_name_font'  => 'monospace',
					'header_store_name_size'  => '50',
					'header_store_name_color' => '#01ff00',
					'header_image'            => '',
					'header_background'       => '',
					'body_highlight_color'    => '#3a3a3a',
					'body_background_color'   => '',
					'body_text_color'         => '#ffffff',
					'body_font'               => 'monospace',
					'body_font_size'          => '13',
					'footer_text_color'       => '#ffffff',
					'footer_show_logo'        => true,
					'footer_logo_size'        => '80',
					'footer_background'       => '',
					'header_show_logo'        => false,
					'background_color'        => '#161616',
					'background_image'        => '',
				)
			)
		);

		/**
		 * Filter the presets available.
		 *
		 * @since 1.36
		 *
		 * @param array $presets
		 */
		return apply_filters( 'it_exchange_email_template_presets', $presets );
	}

	/**
	 * Sideload an image.
	 *
	 * @since 1.36
	 *
	 * @param string $file
	 *
	 * @return int|string
	 */
	protected static function sideload( $file ) {

		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}

		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . '/wp-admin/includes/media.php';
		}

		if ( ! function_exists( 'wp_read_image_metadata' ) ) {
			require_once ABSPATH . '/wp-admin/includes/image.php';
		}

		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );

		if ( ! $matches ) {
			return $file;
		}

		$basename = basename( $matches[0] );

		$uploaded = get_option( 'it-exchange-email-customizer-uploads', array() );

		if ( isset( $uploaded[ $basename ] ) ) {
			return $uploaded[ $basename ];
		}

		$file_array = array();

		$file_array['name'] = basename( $matches[0] );

		// Download file to temp location.
		$file_array['tmp_name'] = download_url( $file );

		// Do the validation and storage stuff.
		$id = media_handle_sideload( $file_array, 0 );

		if ( is_wp_error( $id ) ) {
			return $file;
		}

		$uploaded[ $basename ] = $id;

		update_option( 'it-exchange-email-customizer-uploads', $uploaded );

		return $id;
	}

	/**
	 * Get the preset choices.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	private function get_preset_choices() {

		$presets = $this->get_presets();

		$keys   = array_keys( $presets );
		$labels = wp_list_pluck( $presets, 'label' );

		return array_combine( $keys, $labels );
	}

	/**
	 * Get the default value for a setting.
	 *
	 * This is based off the default preset.
	 *
	 * @since 1.36
	 *
	 * @param string $setting
	 *
	 * @return string
	 */
	private static function get_default( $setting ) {

		$setting = str_replace( array( 'it-exchange-email[', ']' ), '', $setting );

		$presets = self::get_presets();
		$default = $presets['default'];

		if ( isset( $default['settings'][ $setting ] ) ) {
			return $default['settings'][ $setting ];
		}

		return '';
	}

	/**
	 * Restrict the customizer components loaded when the Exchange Email customizer is active.
	 *
	 * We only remove widgets, because widgets is sticky. If both widgets and nav-menus are hidden,
	 * no components appear.
	 *
	 * @since 1.36
	 *
	 * @param array $components
	 *
	 * @return array
	 */
	public function restrict_components( $components ) {

		if ( ! $this->is_active() ) {
			return $components;
		}

		if ( ( $i = array_search( 'widgets', $components ) ) !== false ) {
			unset( $components[ $i ] );
		}

		if ( ( $i = array_search( 'nav_menus', $components ) ) !== false ) {
			unset( $components[ $i ] );
		}

		return $components;
	}

	/**
	 * Restrict the sections loaded in the customizer if we are doing the Exchange Emails Preview.
	 *
	 * @since 1.36
	 *
	 * @param bool                 $active
	 * @param WP_Customize_Section $section
	 *
	 * @return array
	 */
	public function restrict_sections( $active, $section ) {

		if ( ! $this->is_active() ) {
			return $active;
		}

		return in_array( $section->id, wp_list_pluck( $this->sections, 'id' ), true );
	}

	/**
	 * Overload the template to use the Exchange email template whenever we are in the customizer.
	 *
	 * @since 1.36
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public function overload_template( $template ) {

		if ( ! is_customize_preview() || ! $this->is_active() ) {
			return $template;
		}

		$GLOBALS['it_exchange']['demo-receipt'] = true;

		$tpl = new IT_Exchange_Email_Template( 'receipt' );

		return $tpl->get_file( array() );
	}

	/**
	 * Enqueue control scripts.
	 *
	 * @since 1.36
	 */
	public function enqueue_control_scripts() {
		wp_enqueue_script( 'it-exchange-email-customizer-controls', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/assets/controls.js', array(
			'jquery',
			'underscore',
			'customize-controls',
		) );
		wp_localize_script( 'it-exchange-email-customizer-controls', '_exchangeEmailCustomizer', array(
			'presets'             => $this->get_presets(),
			'sendsAttachmentData' => version_compare( $GLOBALS['wp_version'], '4.5', '>=' )
		) );
	}

	/**
	 * Enqueue our control script when the email customizer is loaded.
	 *
	 * @since 1.36
	 */
	public function enqueue_preview_scripts() {
		if ( self::is_active() ) {
			wp_enqueue_script( 'it-exchange-email-customizer-preview', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/assets/preview.js', array(
				'jquery',
				'underscore'
			) );
			wp_enqueue_style( 'it-exchange-email-customizer-preview', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/assets/email-customizer.css' );

			$general = it_exchange_get_option( 'settings_general' );

			wp_localize_script( 'it-exchange-email-customizer-preview', '_exchangeEmailCustomizer', array(
				'presets'   => $this->get_presets(),
				'storeName' => $general['company-name']
			) );
		}
	}

	/**
	 * Dequeue scripts and actions when we are in the email customizer.
	 *
	 * @since 1.36
	 */
	public function dequeue_scripts_and_actions() {

		if ( ! self::is_active() ) {
			return;
		}

		global $wp_scripts, $wp_styles;

		$exceptions = array(
			'jquery',
			'query-monitor',
			'customize-preview',
			'customize-controls',
			'it-exchange-email-customizer'
		);

		if ( is_object( $wp_scripts ) && isset( $wp_scripts->queue ) && is_array( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( in_array( $handle, $exceptions ) ) {
					continue;
				}
				wp_dequeue_script( $handle );
			}
		}

		if ( is_object( $wp_styles ) && isset( $wp_styles->queue ) && is_array( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				if ( in_array( $handle, $exceptions ) ) {
					continue;
				}
				wp_dequeue_style( $handle );
			}
		}

		// Now remove actions
		$action_exceptions = array(
			'wp_print_footer_scripts',
			'wp_admin_bar_render',
		);

		// No core action in header
		remove_all_actions( 'wp_header' );

		global $wp_filter;
		foreach ( $wp_filter['wp_footer'] as $priority => $handle ) {
			if ( in_array( key( $handle ), $action_exceptions ) ) {
				continue;
			}
			unset( $wp_filter['wp_footer'][ $priority ] );
		}
	}

	/**
	 * Check if the email customizer is active.
	 *
	 * @since 1.36
	 *
	 * @return bool
	 */
	public static function is_active() {
		return isset( $_GET['it-exchange-customizer'] ) && $_GET['it-exchange-customizer'] === 'emails';
	}

	/**
	 * Get a link to the customizer preview.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public static function get_link() {
		return add_query_arg( array(
			'url'    => urlencode( add_query_arg( 'it-exchange-customizer', 'emails', site_url() ) ),
			'return' => rawurlencode( admin_url( 'admin.php?page=it-exchange-settings&tab=email' ) ),
		), admin_url( 'customize.php' ) );
	}

	/**
	 * Check if the email customizer is available.
	 *
	 * Ensures that the user is running WordPress 4.3+
	 *
	 * @since 1.36
	 *
	 * @return bool
	 */
	public static function is_available() {
		return version_compare( $GLOBALS['wp_version'], '4.3.0', '>=' );
	}
}