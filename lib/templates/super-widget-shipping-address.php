<?php
/**
 * The shipping address template for the Super Widget.
 *
 * @since 1.3.0
 * @version 1.3.0
 * @link http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
*/
?>
<?php do_action( 'it_exchange_super_widget_shipping_address_before_wrap' ); ?>
<div class="shipping-address it-exchange-sw-processing-shipping-address">
	<?php do_action( 'it_exchange_super_widget_shipping_address_begin_wrap' ); ?>
	<?php it_exchange_get_template_part( 'messages' ); ?>
	<h3><?php _e( 'Shipping Address', 'it-l10n-ithemes-exchange' ); ?></h3>
	<form class="it-exchange-sw-shipping-address">
	<?php it_exchange_get_template_part( 'super-widget-shipping-address/loops/fields' ); ?>
	<?php it_exchange_get_template_part( 'super-widget-shipping-address/loops/actions' ); ?>
	</form>
	<?php do_action( 'it_exchange_super_widget_shipping_address_end_wrap' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_shipping_address_after_wrap' ); ?>
