<?php
/**
 * Email Form for the confirmation page.
 *
 * @since   1.36.0
 * @license GPLv2
 */
$email  = isset( $_POST['it-exchange-guest-email'] ) ? $_POST['it-exchange-guest-email'] : '';
$action = it_exchange_get_page_url( 'confirmation' );
?>

<form method="POST" action="<?php echo $action; ?>" class="it-exchange-guest-transaction-email-confirmation">

	<?php if ( $email ) : ?>
		<ul class="it-exchange-messages it-exchange-errors">
			<li><?php _e( 'Invalid email address.', 'it-l10n-ithemes-exchange' ); ?></li>
		</ul>
	<?php endif; ?>

	<label>
		<span><?php _e( 'Please confirm your email address.', 'it-l10n-ithemes-exchange' ); ?></span>
		<input type="email" name="it-exchange-guest-email" value="<?php echo $email; ?>">
	</label>

	<p>
		<input type="submit" value="<?php _e( 'Proceed', 'it-l10n-ithemes-exchange' ); ?>">
	</p>
</form>