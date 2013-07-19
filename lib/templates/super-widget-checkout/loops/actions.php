<?php
// Two actions or one?
$actions_count = ( ( it_exchange( 'coupons', 'accepting', array( 'type' => 'cart' ) ) || it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) ) && $GLOBALS['it_exchange']['can_edit_purchase_quantity'] ) || ( it_exchange_is_multi_item_cart_allowed() && it_exchange_get_cart_products_count() > 1 ) ? ' two-actions' : '';
?>
<?php do_action( 'it_exchange_super_widget_cart_before_actions_wrapper' ); ?>
<div class="cart-actions-wrapper <?php echo $actions_count; ?>">
	<?php if ( it_exchange_is_multi_item_cart_allowed() && it_exchange_get_cart_products_count() > 1 ) : ?>
		<?php it_exchange_get_template_part( 'super-widget-checkout/elements/multi-item-cancel' ); ?>
		<?php it_exchange_get_template_part( 'super-widget-checkout/elements/multi-item-checkout' ); ?>
	<?php else : ?>
		<?php it_exchange_get_template_part( 'super-widget-checkout/elements/single-item-update-coupons' ); ?>
		<?php it_exchange_get_template_part( 'super-widget-checkout/elements/single-item-update-quantity' ); ?>
	<?php endif; ?>
</div>
<?php do_action( 'it_exchange_super_widget_cart_after_actions_wrapper' ); ?>
