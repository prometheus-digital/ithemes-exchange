<?php
/**
 * This file contains the notice for the email template change.
 * @package IT_Exchange
 * @since   1.36
 */
?>
<div id="it-exchange-email-template-nag" class="it-exchange-nag">
	<p>
		<?php _e( 'Version 1.36.0 of Exchange now includes rich email templates that allow you to create specific branding and styling in your Exchange emails to match your site.', 'it-l10n-ithemes-exchange' ); ?>
		<?php _e('Some templates already include the important information, such as the products ordered, price and shipping address.', 'it-l10n-ithemes-exchange' ); ?>
	</p>
	<p>
		<?php _e( 'To use this new system, we’ve made some changes to how you create your Exchange emails which we think will make email creation easier and faster.', 'it-l10n-ithemes-exchange' ); ?>
		<?php _e( 'For reference, please see your legacy email template alongside the new templates.', 'it-l10n-ithemes-exchange' ); ?>
	</p>
	<a class="btn" href="<?php echo esc_url( admin_url( 'admin.php?page=it-exchange-settings&tab=email' ) ); ?>"><?php _e('Customize', 'it-l10n-ithemes-exchange') ?></a>
</div>
<script type="text/javascript">
	jQuery( document ).ready( function () {
		if ( jQuery( '.wrap > h1' ).length == '1' ) {
			jQuery( "#it-exchange-email-template-nag" ).insertAfter( '.wrap > h1' ).addClass( 'after-h2' );
		}
	} );
</script>
