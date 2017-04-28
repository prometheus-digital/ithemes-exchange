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

	if ( defined( 'IT_EXCHANGE_DISABLE_LOGS' ) && IT_EXCHANGE_DISABLE_LOGS ) {
		$logger = new  \Psr\Log\NullLogger();
	} else {
		$logger = new ITE_DB_Logger( \IronBound\DB\Manager::get( 'ite-logs' ), $GLOBALS['wpdb']	);
	}

	/**
	 * Filter the logger used in Exchange.
	 *
	 * @since 2.0.0
	 *
	 * @param \Psr\Log\LoggerInterface
	 */
	$_logger = apply_filters( 'it_exchange_logger', $logger );

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
function it_exchange_log( $message, $level_or_context = \Psr\Log\LogLevel::WARNING, array $context = array() ) {

	if ( is_array( $level_or_context ) ) {
		$context = $level_or_context;
		$level   = \Psr\Log\LogLevel::WARNING;
	} else {
		$level = $level_or_context;
	}

	it_exchange_logger()->log( $level, $message, $context );
}