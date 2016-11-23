<?php
/**
 * Settings Controller class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Settings_Controller
 */
class ITE_Settings_Controller {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array|null
	 */
	private $settings = null;

	/**
	 * ITE_Settings_Controller constructor.
	 *
	 * @param string $name
	 */
	public function __construct( $name ) {
		$this->name = $name;
	}

	/**
	 * Lazy-load settings.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function lazy_load_settings() {

		if ( ! $this->settings ) {
			$this->settings = it_exchange_get_option( $this->name );
		}

		return $this->settings;
	}

	/**
	 * Get a setting.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 *
	 * @return mixed
	 *
	 * @throws OutOfBoundsException If invalid $key requested.
	 */
	public function get( $key ) {

		$settings = $this->lazy_load_settings();

		if ( array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		}

		throw new OutOfBoundsException( "Key '$key' does not exist.'" );
	}

	/**
	 * Check if a setting exists.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has( $key ) {
		return array_key_exists( $key, $this->lazy_load_settings() );
	}

	/**
	 * Get all settings values.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function all() {
		return $this->lazy_load_settings();
	}

	/**
	 * Alter a setting value.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 * @param mixed  $val
	 * @param bool   $save
	 *
	 * @return bool
	 */
	public function set( $key, $val, $save = true ) {

		$settings         = $this->lazy_load_settings();
		$settings[ $key ] = $val;
		$this->settings   = $settings;

		if ( $save ) {
			return it_exchange_save_option( $this->name, $settings, true );
		} else {
			return true;
		}
	}

	/**
	 * Save settings.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function save() {

		if ( ! $this->settings ) {
			return false;
		}

		return it_exchange_save_option( $this->name, $this->settings, true );
	}
}
