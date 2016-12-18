<?php
/**
 * WP CLI upgrade skin.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Upgrade_Skin_CLI
 */
class ITE_Upgrade_Skin_CLI implements IT_Exchange_Upgrade_SkinInterface {

	/** @var \cli\Progress */
	private $progress;

	/**
	 * ITE_Upgrade_Skin_CLI constructor.
	 *
	 * @param \cli\Progress $progress
	 */
	public function __construct( \cli\Progress $progress = null ) {
		$this->progress   = $progress;
	}

	/**
	 * @inheritDoc
	 */
	public function debug( $message ) {
		WP_CLI::debug( $message );
	}

	/**
	 * @inheritDoc
	 */
	public function warn( $message ) {
		WP_CLI::warning( $message );
	}

	/**
	 * @inheritDoc
	 */
	public function error( $message ) {
		WP_CLI::error( $message, false );
	}

	/**
	 * @inheritDoc
	 */
	public function tick( $amount = 1 ) {
		if ( $this->progress ) {
			$this->progress->increment( $amount );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function finish() {
		if ( $this->progress ) {
			$this->progress->finish();
		}
	}
}