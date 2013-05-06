<?php
/**
 * This file contains the notice for the Wizard setup
 * @package IT_Exchange
 * @since 0.4.0
*/
// Just adding internal CSS rule here since it won't be around long.
?>
<style type="text/css">
	.it-exchange-wizard-notice { background: lightblue; padding: 20px; color: #fff; }
</style>
<div class="it-exchange-wizard-notice">
	<?php
	$wizard_link    = add_query_arg( array( 'page' => 'it-exchange-setup' ), admin_url( 'admin.php' ) );
	$wizard_dismiss = add_query_arg( array( 'it-exchange-dismiss-wizard-nag' => true ) );
	?>
	<p>
		iThemes Exchange needs to be <a href="<?php echo $wizard_link; ?>">setup</a>, punk!
		<a href="<?php echo $wizard_dismiss; ?>"><?php _e( 'Hide this', 'LION' ); ?></a>
	</p>
</div>
