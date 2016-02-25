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
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'init', array( $this, 'dequeue_scripts_and_actions' ) );

		$this->capability = it_exchange_get_admin_menu_capability( 'email-customize' );
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
			new WP_Customize_Section( $customizer, 'it-exchange-email-colors', array(
				'capability'      => $this->capability,
				'active_callback' => array( __CLASS__, 'is_active' ),
				'title'           => __( 'Colors', 'it-l10n-ithemes-exchange' ),
				'priority'        => 1
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
			new WP_Customize_Setting( $customizer, 'it-exchange-email-colors-background', array(
				'capability' => $this->capability,
				'type'       => 'option',
				'default'    => '#F0F0F0'
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
			new WP_Customize_Color_Control( $customizer, 'it-exchange-email-colors-background', array(
				'capability' => $this->capability,
				'section'    => 'it-exchange-email-colors',
				'label'      => __( 'Background Color', 'it-l10n-ithemes-exchange' )
			) )
		);
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

		$tpl = it_exchange_get_template_part( 'email', null, false );

		return $tpl;
	}

	/**
	 * Enqueue our control script when the email customizer is loaded.
	 *
	 * @since 1.36
	 */
	public function enqueue_scripts() {
		if ( self::is_active() ) {
			wp_enqueue_script( 'it-exchange-email-customizer', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/email-customizer.js' );
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
}