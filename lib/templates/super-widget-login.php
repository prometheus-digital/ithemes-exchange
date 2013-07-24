<?php
/**
 * The login template for the Super Widget.
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
*/
?>

<div class="login it-exchange-sw-processing-login">
	<?php it_exchange_get_template_part( 'messages' ); ?>
	<?php do_action( 'it_exchange_super_widget_login_before_form' ); ?>
	<?php it_exchange( 'login', 'form-open' ); ?>
		<?php do_action( 'it_exchange_super_widget_login_begin_form' ); ?>
		<?php it_exchange_get_template_part( 'super-widget-login/loops/fields' ); ?>
		<?php it_exchange_get_template_part( 'super-widget-login/loops/actions' ); ?>
		<?php do_action( 'it_exchange_super_widget_login_end_form' ); ?>
	<?php it_exchange( 'login', 'form-close' ); ?>
	<?php do_action( 'it_exchange_super_widget_login_after_form' ); ?>
</div>
