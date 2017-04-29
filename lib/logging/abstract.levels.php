<?php
/**
 * LogLevels class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Log_Levels
 *
 * @author WooCommerce GPLv2
 */
class ITE_Log_Levels extends \Psr\Log\LogLevel {

	/**
	 * Level strings mapped to integer severity.
	 *
	 * @var array
	 */
	protected static $level_to_severity = array(
		self::EMERGENCY => 800,
		self::ALERT     => 700,
		self::CRITICAL  => 600,
		self::ERROR     => 500,
		self::WARNING   => 400,
		self::NOTICE    => 300,
		self::INFO      => 200,
		self::DEBUG     => 100,
	);

	/**
	 * Severity integers mapped to level strings.
	 *
	 * This is the inverse of $level_severity.
	 *
	 * @var array
	 */
	protected static $severity_to_level = array(
		800 => self::EMERGENCY,
		700 => self::ALERT,
		600 => self::CRITICAL,
		500 => self::ERROR,
		400 => self::WARNING,
		300 => self::NOTICE,
		200 => self::INFO,
		100 => self::DEBUG,
	);


	/**
	 * Validate a level string.
	 *
	 * @param string $level
	 *
	 * @return bool True if $level is a valid level.
	 */
	public static function is_valid_level( $level ) {
		return array_key_exists( strtolower( $level ), self::$level_to_severity );
	}

	/**
	 * Translate level string to integer.
	 *
	 * @param string $level emergency|alert|critical|error|warning|notice|info|debug
	 *
	 * @return int 100 (debug) - 800 (emergency) or 0 if not recognized
	 */
	public static function get_level_severity( $level ) {
		if ( self::is_valid_level( $level ) ) {
			$severity = self::$level_to_severity[ strtolower( $level ) ];
		} else {
			$severity = 0;
		}

		return $severity;
	}

	/**
	 * Translate severity integer to level string.
	 *
	 * @param int $severity
	 *
	 * @return bool|string False if not recognized. Otherwise string representation of level.
	 */
	public static function get_severity_level( $severity ) {
		if ( array_key_exists( $severity, self::$severity_to_level ) ) {
			return self::$severity_to_level[ $severity ];
		} else {
			return false;
		}
	}

	/**
	 * Get a list of the possible log levels with translations.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public static function get_levels() {
		return array(
			self::EMERGENCY => __( 'Emergency', 'it-l10n-ithemes-exchange' ),
			self::ALERT     => __( 'Alert', 'it-l10n-ithemes-exchange' ),
			self::CRITICAL  => __( 'Critical', 'it-l10n-ithemes-exchange' ),
			self::ERROR     => __( 'Error', 'it-l10n-ithemes-exchange' ),
			self::WARNING   => __( 'Warning', 'it-l10n-ithemes-exchange' ),
			self::NOTICE    => __( 'Notice', 'it-l10n-ithemes-exchange' ),
			self::INFO      => __( 'Info', 'it-l10n-ithemes-exchange' ),
			self::DEBUG     => __( 'Debug', 'it-l10n-ithemes-exchange' ),
		);
	}
}