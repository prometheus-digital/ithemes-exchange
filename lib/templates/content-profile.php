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
	<?php do_action( 'it_exchange_content_profile_before_form' ); ?>
	<?php it_exchange( 'customer', 'formopen' ); ?>
		<?php it_exchange_get_template_part( 'content-profile/fields/loop' ); ?>
		<?php foreach( it_exchange_get_content_profile_actions() as $action ) : ?>
			<?php it_exchange_get_template_part( 'content-profile/actions/' . $action ); ?>
		<?php endforeach; ?>
	<?php it_exchange( 'customer', 'formclose' ); ?>
	<?php do_action( 'it_exchange_content_profile_after_form' ); ?>
</div>
