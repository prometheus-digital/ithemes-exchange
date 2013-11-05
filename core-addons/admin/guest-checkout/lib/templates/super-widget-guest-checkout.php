<?php
/**
 * The guest-checkout template for the Super Widget.
 *
 * @since CHANGEME
 * @version CHANGEME
 * @link http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
*/
?>
<?php do_action( 'it_exchange_super_widget_guest_checkout_before_wrap' ); ?>
<div class="guest-checkout it-exchange-sw-processing-guest-checkout it-exchange-sw-processing">
	<?php do_action( 'it_exchange_super_widget_guest_checkout_begin_wrap' ); ?>
	<?php it_exchange_get_template_part( 'messages' ); ?>
	<?php echo it_exchange_guest_checkout_get_heading(); ?>
	<div class="it-exchange-guest-checkout-form-wrapper it-exchange-clearfix">
		<form class="it-exchange-guest-checkout-form" method="post" action="">
			<?php it_exchange_get_template_part( 'super-widget-guest-checkout/loops/fields' ); ?>
			<?php it_exchange_get_template_part( 'super-widget-guest-checkout/loops/actions' ); ?>
		</form>
	</div>
	<?php do_action( 'it_exchange_super_widget_guest_checkout_end_wrap' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_guest_checkout_after_wrap' ); ?>
