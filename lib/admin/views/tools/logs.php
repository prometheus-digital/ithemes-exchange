<?php
/**
 * Contains the contents of the logs page.
 *
 * @since   2.0.0
 * @license GPLv2
 * @var IT_Exchange_Admin  $this
 * @var ITE_Log_List_Table $table
 */

?>
<div class="wrap logs-wrap">
	<?php ITUtility::screen_icon( 'it-exchange' ); ?>
    <h1><?php _e( 'Logs', 'it-l10n-ithemes-exchange' ); ?></h1>

	<?php $this->print_tools_page_tabs(); ?>

    <form method="GET">
        <input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>">
        <input type="hidden" name="tab" value="<?php echo esc_attr( $_GET['tab'] ); ?>">

		<?php $table->views(); ?>
		<?php $table->search_box( __( 'Search', 'it-l10n-ithemes-exchange' ), 'it-exchange-log-search' ); ?>
		<?php $table->display(); ?>
    </form>
</div>

