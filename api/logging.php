<?php
/**
 * Logging API functions.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Get the logger instance.
 *
 * @since 2.0.0
 *
 * @return \PSR\Log\LoggerInterface
 */
function it_exchange_logger() {

	static $logger;

	if ( $logger ) {
		return $logger;
	}

	$mode = it_exchange_get_logging_mode();

	if ( $mode['type'] === 'file' ) {
		it_classes_load( 'it-file-utility.php' );
		$directory = ITFileUtility::get_writable_directory( 'it-exchange-logs' );

		if ( is_string( $directory ) ) {
			$logger = new ITE_File_Logger( $directory, $mode['level'] );
		}

	} elseif ( $mode['type'] === 'db' ) {
		$logger = new ITE_DB_Logger( \IronBound\DB\Manager::get( 'ite-logs' ), $GLOBALS['wpdb'], $mode['level'] );
	}

	if ( ! $logger ) {
		$logger = new \Psr\Log\NullLogger();
	}

	/**
	 * Filter the logger used in Exchange.
	 *
	 * @since 2.0.0
	 *
	 * @param \Psr\Log\LoggerInterface $logger
	 * @param array                    $mode
	 */
	$_logger = apply_filters( 'it_exchange_logger', $logger, $mode );

	if ( ! $_logger instanceof \PSR\Log\LoggerInterface ) {
		throw new UnexpectedValueException( "'it_exchange_logger' filter must return a LoggerInterface instance." );
	}

	$logger = $_logger;

	return $logger;
}

/**
 * Log a message to the log.
 *
 * @since 2.0.0
 *
 * @param string $message          Log message.
 * @param string $level_or_context Either the log level, defaults to 'warning' or the context for interpolation.
 * @param array  $context          The context for interpolation.
 */
function it_exchange_log( $message, $level_or_context = ITE_Log_Levels::ERROR, array $context = array() ) {

	if ( is_array( $level_or_context ) ) {
		$context = $level_or_context;
		$level   = \Psr\Log\LogLevel::WARNING;
	} else {
		$level = $level_or_context;
	}

	it_exchange_logger()->log( $level, $message, $context );
}

/**
 * Get the available logging modes.
 *
 * @since 2.0.0
 *
 * @return array
 */
function it_exchange_get_available_logging_modes() {
	$modes = array(
		'production' => array(
			'label' => __( 'Production', 'it-l10n-ithemes-exchange' ),
			'type'  => 'file',
			'level' => ITE_Log_Levels::WARNING,
		),
		'testing'    => array(
			'label' => __( 'Testing', 'it-l10n-ithemes-exchange' ),
			'type'  => 'db',
			'level' => ITE_Log_Levels::NOTICE
		),
		'debug'      => array(
			'label' => __( 'Debug', 'it-l10n-ithemes-exchange' ),
			'type'  => 'db',
			'level' => ITE_Log_Levels::DEBUG
		),
	);

	/**
	 * Filter the available logging modes.
	 *
	 * @since 2.0.0
	 *
	 * @param array[] $modes
	 */
	return apply_filters( 'it_exchange_get_available_logging_modes', $modes );
}

/**
 * Can the logging mode be changed in the admin, or is it defined in wp-config.php
 *
 * @since 2.0.0
 *
 * @return bool
 */
function it_exchange_is_logging_mode_changeable() {
	return
		! defined( 'IT_EXCHANGE_DISABLE_LOGS' ) &&
		! defined( 'IT_EXCHANGE_LOG_TYPE' ) &&
		! defined( 'IT_EXCHANGE_LOG_LEVEL' ) &&
		! defined( 'IT_EXCHANGE_LOGGING_MODE' );
}

/**
 * Get the current logging mode.
 *
 * @since 2.0.0
 *
 * @return array
 */
function it_exchange_get_logging_mode() {

	$mode  = array();
	$modes = it_exchange_get_available_logging_modes();

	if ( defined( 'IT_EXCHANGE_DISABLE_LOGS' ) ) {
		$mode = array(
			'type'  => 'null',
			'level' => '',
			'label' => __( 'Disabled', 'it-l10n-ithemes-exchange' ),
		);
	}

	if ( ! $mode && defined( 'IT_EXCHANGE_LOGGING_MODE' ) && isset( $modes[ IT_EXCHANGE_LOGGING_MODE ] ) ) {
		$mode = $modes[ IT_EXCHANGE_LOGGING_MODE ];
	}

	if ( ! $mode ) {

		$type           = 'file';
		$level          = ITE_Log_Levels::WARNING;
		$check_settings = true;

		if ( defined( 'IT_EXCHANGE_LOG_TYPE' ) ) {
			$type           = IT_EXCHANGE_LOG_TYPE;
			$check_settings = false;
		}

		if ( defined( 'IT_EXCHANGE_LOG_LEVEL' ) ) {
			$level          = IT_EXCHANGE_LOG_LEVEL;
			$check_settings = false;
		}

		if ( ! $check_settings ) {
			$mode = array(
				'label' => __( 'Custom', 'it-l10n-ithemes-exchange' ),
				'type'  => $type,
				'level' => $level,
			);
		}
	}

	if ( ! $mode ) {
		$general = it_exchange_get_option( 'settings_general' );
		$slug    = isset( $general['logging-mode'] ) ? $general['logging-mode'] : 'production';

		if ( isset( $modes[ $slug ] ) ) {
			$mode = $modes[ $slug ];
		} else {
			$mode = reset( $modes );
		}
	}

	/**
	 * Filter the selected logging mode.
	 *
	 * @since 2.0.0
	 *
	 * @param array $mode
	 */
	return apply_filters( 'it_exchange_get_logging_mode', $mode );
}