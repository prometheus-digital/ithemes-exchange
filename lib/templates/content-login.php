<?php
/**
 * Default template for displaying the user login.
 * page.
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
 * Example: theme/exchange/content-login.php
*/
?>

<?php it_exchange_get_template_part( 'messages' ); ?>

<div id="it-exchange-customer">
	<div class="login">
		<?php do_action( 'it_exchange_content_login_before_form' ); ?>
		<?php it_exchange( 'login', 'form-open' ); ?>
			<?php do_action( 'it_exchange_content_login_begin_form' ); ?>
			<?php foreach( it_exchange_get_content_login_field_details() as $detail ) : ?>
				<?php 
				/** 
				 * Theme and add-on devs should add code to this loop by 
				 * hooking into it_exchange_get_content_login_field_details filter
				 * and adding the appropriate template file to their theme or add-on
				 */
				it_exchange_get_template_part( 'content-login/details/' . $detail ); ?>
			<?php endforeach; ?>
			<?php do_action( 'it_exchange_content_login_end_form' ); ?>
		<?php it_exchange( 'login', 'form-close' ); ?>
		<?php do_action( 'it_exchange_content_login_after_form' ); ?>
	</div>
</div>
