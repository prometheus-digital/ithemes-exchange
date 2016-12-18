<?php
/**
 * Ajax handler.
 *
 * @since   1.33
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Upgrade_Handler_Ajax
 */
class IT_Exchange_Upgrade_Handler_Ajax {

	/**
	 * @var IT_Exchange_Upgrader
	 */
	private $upgrader;

	/**
	 * IT_Exchange_Upgrade_Handler_Ajax constructor.
	 *
	 * @since 1.33
	 *
	 * @param IT_Exchange_Upgrader $upgrader
	 */
	public function __construct( IT_Exchange_Upgrader $upgrader ) {
		$this->upgrader = $upgrader;
	}

	/**
	 * Add ajax hooks.
	 *
	 * @since 1.33
	 */
	public function hooks() {
		add_action( 'wp_ajax_it-exchange-begin-upgrade', array( $this, 'begin' ) );
		add_action( 'wp_ajax_it-exchange-do-upgrade-step', array( $this, 'do_step' ) );
		add_action( 'wp_ajax_it-exchange-complete-upgrade', array( $this, 'complete' ) );
	}

	/**
	 * Begin callback.
	 *
	 * @since 1.33
	 */
	public function begin() {

		$upgrade = isset( $_POST['upgrade'] ) ? $_POST['upgrade'] : '';
		$nonce   = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';

		if ( ! $upgrade || ! $nonce ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid Request Format', 'it-l10n-ithemes-exchange' )
			) );
		}

		if ( ! wp_verify_nonce( $nonce, 'it-exchange-upgrade' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Request expired. Please refresh and try again.', 'it-l10n-ithemes-exchange' )
			) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'You don\'t have permission to do this.', 'it-l10n-ithemes-exchange' )
			) );
		}

		$upgrade = $this->upgrader->get_upgrade( $upgrade );

		if ( ! $upgrade ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid upgrade.', 'it-l10n-ithemes-exchange' )
			) );
		}

		$rate = $upgrade->get_suggested_rate();

		/**
		 * Filter the rate at which upgrade reords should be processed.
		 *
		 * @since 1.33
		 *
		 * @param int                          $rate
		 * @param IT_Exchange_UpgradeInterface $upgrade
		 */
		$rate = apply_filters( 'it_exchange_ajax_upgrade_rate', $rate, $upgrade );

		$this->upgrader->begin( $upgrade );

		wp_send_json_success( array(
			'slug'      => $upgrade->get_slug(),
			'itemCount' => $upgrade->get_total_records_to_process(),
			'rate'      => absint( $rate )
		) );
	}

	/**
	 * Do step callback.
	 *
	 * @since 1.33
	 */
	public function do_step() {

		$upgrade = isset( $_POST['upgrade'] ) ? $_POST['upgrade'] : '';
		$config  = isset( $_POST['config'] ) ? (array) $_POST['config'] : array();
		$nonce   = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';

		if ( ! $upgrade || ! $config || ! $nonce ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid Request Format', 'it-l10n-ithemes-exchange' )
			) );
		}

		if ( ! isset( $config['step'] ) || ! isset( $config['number'] ) || ! isset( $config['verbose'] ) ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid Request Format.', 'it-l10n-ithemes-exchange' )
			) );
		}

		if ( ! wp_verify_nonce( $nonce, 'it-exchange-upgrade' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Request expired. Please refresh and try again.', 'it-l10n-ithemes-exchange' )
			) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'You don\'t have permission to do this.', 'it-l10n-ithemes-exchange' )
			) );
		}

		$upgrade = $this->upgrader->get_upgrade( $upgrade );

		if ( ! $upgrade ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid upgrade.', 'it-l10n-ithemes-exchange' )
			) );
		}

		$config = new IT_Exchange_Upgrade_Config( $config['step'], $config['number'], $config['verbose'] );
		$skin   = new IT_Exchange_Upgrade_Skin_Ajax();

		try {
			$upgrade->upgrade( $config, $skin );
		}
		catch ( IT_Exchange_Upgrade_Exception $e ) {
			wp_send_json_error( array(
				'message' => $e->getMessage()
			) );
		}

		wp_send_json_success( $skin->out() );
	}

	/**
	 * Complete an upgrade.
	 *
	 * @since 1.33
	 */
	public function complete() {

		$upgrade = isset( $_POST['upgrade'] ) ? $_POST['upgrade'] : '';
		$nonce   = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';

		if ( ! $upgrade || ! $nonce ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid Request Format', 'it-l10n-ithemes-exchange' )
			) );
		}

		if ( ! wp_verify_nonce( $nonce, 'it-exchange-upgrade' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Request expired. Please refresh and try again.', 'it-l10n-ithemes-exchange' )
			) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'You don\'t have permission to do this.', 'it-l10n-ithemes-exchange' )
			) );
		}

		$upgrade = $this->upgrader->get_upgrade( $upgrade );

		if ( ! $upgrade ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid upgrade.', 'it-l10n-ithemes-exchange' )
			) );
		}

		$this->upgrader->complete( $upgrade );

		wp_send_json_success();
	}
}