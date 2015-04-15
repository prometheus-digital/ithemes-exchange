<?php
/**
 * This file contains the notice for the Sync Integration Notice
 * @package IT_Exchange
 * @since 1.10.0
*/
// Just placing internal JS here since it won't be around long.
?>
<div id="it-exchange-ithemes-sync-integration-nag" class="it-exchange-nag">
	<?php printf( __( 'New! Track Your Sales Remotely with iThemes Sync. %sCheck it Out Now%s.' ), '<a target="_blank" href="' . esc_url( $more_info_url ) . '">', '</a>' ) ?>
	<a class="dismiss btn" href="<?php echo esc_url( $dismiss_url ); ?>">&times;</a>
</div>
<script type="text/javascript">
	jQuery( document ).ready( function() {
		if ( jQuery( '.wrap > h2' ).length == '1' ) {
			jQuery("#it-exchange-ithemes-sync-integration-nag").insertAfter('.wrap > h2').addClass( 'after-h2' );
		}
	});
</script>
