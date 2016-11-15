<?php
/**
 * Contains the contents of the system info page.
 *
 * @since   2.0.0
 * @license GPLv2
 */

$sysinfo = it_exchange_get_system_info();
?>
<div class="wrap tools-wrap">
	<?php ITUtility::screen_icon( 'it-exchange' ); ?>
	<h1><?php _e( 'System Info', 'it-l10n-ithemes-exchange' ); ?></h1>

	<?php $this->print_tools_tab(); ?>

	<label for="system-info" class="screen-reader-text">
		<?php _e( 'System Info', 'it-l10n-ithemes-exchange' ); ?>
	</label>

	<p class="description" style="padding: 20px 0 5px">
		<?php _e( 'Please include the following information in your ticket when contacting support.', 'it-l10n-ithemes-exchange' ); ?>
	</p>

<textarea readonly id="system-info">
<?php foreach ( $sysinfo as $category => $info ): ?>

### <?php echo $category; ?> ###

<?php foreach ( $info as $label => $value ): ?>
<?php echo $label; ?>: <?php echo $value; ?>

<?php endforeach; ?>
<?php endforeach; ?>
</textarea>

</div>

