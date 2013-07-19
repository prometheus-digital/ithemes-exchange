<?php do_action( 'it_exchange_super_widget_checkout_items_before_wrapper' ); ?>
<div class="cart-items-wrapper">
	<?php do_action( 'it_exchange_super_widget_checkout_items_begin_wrapper' ); ?>

	<?php do_action( 'it_exchange_super_widget_checkout_before_items_loop' ); ?>
	<?php while( it_exchange( 'cart', 'cart-items' ) ) : ?>
		<?php $GLOBALS['it_exchange']['can_edit_purchase_quantity'] = it_exchange( 'cart-item', 'supports-purchase-quantity' ) && ( it_exchange_get_cart_products_count() < 2 ); ?>
		<?php do_action( 'it_exchange_super_widget_checkout_begin_items_loop' ); ?>

		<?php do_action( 'it_exchange_super_widget_checkout_before_item' ); ?>
		<div class="cart-item">
			<?php do_action( 'it_exchange_super_widget_checkout_begin_item' ); ?>
			<div class="title-remove">
				<?php foreach( it_exchange_get_template_part_elements( 'super_widget_checkout', 'items', array( 'title', 'remove' ) ) as $detail ) : ?>
					<?php it_exchange_get_template_part( 'super-widget-checkout/elements/' . $detail ); ?>
				<?php endforeach; ?>
			</div>
			<div class="item-info">
				<?php foreach( it_exchange_get_template_part_elements( 'super_widget_checkout', 'items', array( 'price' ) ) as $detail ) : ?>
					<?php it_exchange_get_template_part( 'super-widget-checkout/elements/' . $detail ); ?>
				<?php endforeach; ?>
			</div>
			<?php do_action( 'it_exchange_super_widget_checkout_end_item' ); ?>
		</div>
		<?php do_action( 'it_exchange_super_widget_checkout_after_item' ); ?>

		<?php do_action( 'it_exchange_super_widget_checkout_end_items_loop' ); ?>
	<?php endwhile; ?>
	<?php do_action( 'it_exchange_super_widget_checkout_after_items_loop' ); ?>

	<?php 
	if ( it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) )
		it_exchange_get_template_part( 'super-widget-checkout/loops/discounts' );
	?>

	<?php do_action( 'it_exchange_super_widget_checkout_items_end_wrapper' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_checkout_items_after_wrapper' ); ?>
