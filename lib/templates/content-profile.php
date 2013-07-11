<?php
/**
 * Default template for displaying the an exchange
 * customer's profile.
 * 
 * @since 0.4.0
 * @version 1.0.0
 * @link http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
 * 
 * Example: theme/exchange/content-profile.php
*/
?>

<?php it_exchange_get_template_part( 'messages' ); ?>

<div id="it-exchange-customer">
	<?php it_exchange( 'customer', 'menu' ); ?>
	<div class="customer-info">
		<?php it_exchange( 'customer', 'formopen' ); ?>
			<div class="customer-name">
				<div class="first-name">
					<?php it_exchange( 'customer', 'firstname' ); ?>
				</div>
				<div class="last-name">
					<?php it_exchange( 'customer', 'lastname' ); ?>
				</div>
			</div>
			<div class="customer-email">
				<?php it_exchange( 'customer', 'email' ); ?>
			</div>
			<div class="customer-website">
				<?php it_exchange( 'customer', 'website' ); ?>
			</div>
			<div class="customer-password">
				<div class="password-1">
					<?php it_exchange( 'customer', 'password1' ); ?>
				</div>
				<div class="password-2">
					<?php it_exchange( 'customer', 'password2' ); ?>
				</div>
			</div>
			<div class="customer-save">
				<?php it_exchange( 'customer', 'save' ); ?>
			</div>
		<?php it_exchange( 'customer', 'formclose' ); ?>
	</div>
</div>
