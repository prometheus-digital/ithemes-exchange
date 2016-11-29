<?php
/**
 * File Upgrade Skin.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Upgrade_Skin_File
 */
class ITE_Upgrade_Skin_File implements IT_Exchange_Upgrade_SkinInterface {

	/** @var string */
	private $file_path;

	/** @var resource */
	private $fh;

	/**
	 * ITE_Upgrade_Skin_File constructor.
	 *
	 * @param string $file_path
	 */
	public function __construct( $file_path ) { $this->file_path = $file_path; }

	/**
	 * Create a file upgrade skin without specifying the log file.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_UpgradeInterface $upgrade
	 *
	 * @return static
	 */
	public static function auto_create_file( IT_Exchange_UpgradeInterface $upgrade ) {

		$directory = ITFileUtility::get_writable_directory( array(
			'random' => true,
			'name'   => 'it-exchange-upgrade'
		) );

		if ( is_wp_error( $directory ) ) {
			throw new UnexpectedValueException( $directory->get_error_message() );
		}

		$path = trailingslashit( $directory ) . $upgrade->get_slug() . '.txt';

		if ( ! ITFileUtility::is_file_writable( $path ) ) {
			throw new UnexpectedValueException( 'Unable to create writable log file.' );
		}

		@chmod( $path, 0644 );

		return new static( $path );
	}

	/**
	 * @inheritDoc
	 */
	public function debug( $message ) {
		$this->write( $message );
	}

	/**
	 * @inheritDoc
	 */
	public function warn( $message ) {
		$this->write( "Warning: $message" );
	}

	/**
	 * @inheritDoc
	 */
	public function error( $message ) {
		$this->write( "ERROR: $message" );
	}

	/**
	 * Write a message to the file.
	 *
	 * @since 2.0.0
	 *
	 * @param string $message
	 */
	protected function write( $message ) {

		if ( ! $this->fh ) {
			$this->fh = fopen( $this->get_file(), 'a' );
		}

		fwrite( $this->fh, $message . '\r\n' );
	}

	/**
	 * @inheritDoc
	 */
	public function tick( $amount = 1 ) {

	}

	/**
	 * @inheritDoc
	 */
	public function finish() {
		$this->complete_writing();
	}

	/**
	 * Get the file to write to.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_file() {
		return $this->file_path;
	}

	/**
	 * Complete writing to the file.
	 *
	 * @since 2.0.0
	 */
	public function complete_writing() {
		if ( $this->fh ) {
			fclose( $this->fh );
		}
	}
}