<?php
/**
 * Contains the contents of the tools page.
 *
 * @since   2.0.0
 * @license GPLv2
 * @var IT_Exchange_Admin $this
 */

?>
<?php wp_nonce_field( 'wp_rest', '_wpnonce', false ); ?>
<div class="wrap tools-wrap">
	<?php ITUtility::screen_icon( 'it-exchange' ); ?>
    <h1><?php _e( 'Tools', 'it-l10n-ithemes-exchange' ); ?></h1>

	<?php $this->print_tools_page_tabs(); ?>

    <div class="tool clear-sessions-tool">
        <label for="clear-sessions-type"><?php _e( 'Clear Sessions', 'it-l10n-ithemes-exchange' ); ?></label>
        <select id="clear-sessions-type">
            <option value="expired"><?php _e( 'Expired', 'it-l10n-ithemes-exchange' ); ?></option>
            <option value="active"><?php _e( 'Active', 'it-l10n-ithemes-exchange' ); ?></option>
            <option value="expired-active"><?php _e( 'Active + Expired', 'it-l10n-ithemes-exchange' ); ?></option>
            <option value="all"><?php _e( 'All', 'it-l10n-ithemes-exchange' ); ?></option>
        </select>
        <button class="button button-secondary" id="clear-sessions"
                data-route="<?php echo esc_attr( wp_nonce_url( rest_url( 'it_exchange/v1/tools/clear-sessions' ), 'wp_rest' ) ); ?>">
			<?php _e( 'Clear', 'it-l10n-ithemes-exchange' ); ?>
        </button>
        <p class="description hidden" id="all-sessions-warning">
			<?php _e( 'This will delete all sessions, including in-progress payments. This is not recommended in most cases.', 'it-l10n-ithemes-exchange' ); ?>
        </p>
    </div>
</div>

