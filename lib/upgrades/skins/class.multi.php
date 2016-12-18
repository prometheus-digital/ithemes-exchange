<?php
/**
 * Multi-skin wrapper.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Upgrade_Skin_Multi
 */
class ITE_Upgrade_Skin_Multi implements IT_Exchange_Upgrade_SkinInterface {

	/** @var IT_Exchange_Upgrade_SkinInterface[] */
	private $skins = array();

	/**
	 * ITE_Upgrade_Skin_Multi constructor.
	 *
	 * @param IT_Exchange_Upgrade_SkinInterface[] $skins
	 */
	public function __construct( array $skins ) { $this->skins = $skins; }

	/**
	 * @inheritDoc
	 */
	public function debug( $message ) {
		foreach ( $this->skins as $skin ) {
			$skin->debug( $message );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function warn( $message ) {
		foreach ( $this->skins as $skin ) {
			$skin->warn( $message );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function error( $message ) {
		foreach ( $this->skins as $skin ) {
			$skin->error( $message );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function tick( $amount = 1 ) {
		foreach ( $this->skins as $skin ) {
			$skin->tick( $amount );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function finish() {
		foreach ( $this->skins as $skin ) {
			$skin->finish();
		}
	}

	/**
	 * Get the skins that are being written to.
	 *
	 * @since 2.0.0
	 *
	 * @return IT_Exchange_Upgrade_SkinInterface[]
	 */
	public function get_skins() {
		return $this->skins;
	}
}