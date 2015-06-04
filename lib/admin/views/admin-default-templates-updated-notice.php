<?php
/**
 * This file contains the notice for the Wizard setup
 * @package IT_Exchange
 * @since 0.4.0
*/
// Just adding internal CSS rule here since it won't be around long.
?>
<div id="it-exchange-updated-templates-nag" class="it-exchange-nag">
	<?php printf( __( 'iThemes Exchange default template parts have been updated. View %sour codex%s for more information.' ), '<a href="' . esc_url( $codex_url ). '">', '</a>' ) ?>
	<a class="dismiss btn" href="<?php echo esc_url( $dismiss_url ); ?>">&times;</a>
</div>
<script type="text/javascript">
	jQuery( document ).ready( function() {
		if ( jQuery( '.wrap > h2' ).length == '1' ) {
			jQuery("#it-exchange-updated-templates-nag").insertAfter('.wrap > h2').addClass( 'after-h2' );
		}
	});
</script>
