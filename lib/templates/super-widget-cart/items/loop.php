<?php do_action( 'it_exchange_super_widget_cart_items_before_item' ); ?>
<div class="cart-item">
	<?php do_action( 'it_exchange_super_widget_cart_items_begin_item' ); ?>
	<div class="title-remove">
		<?php foreach( it_exchange_get_template_part_elements( 'super_widget_cart', 'items', array( 'title', 'remove' ) ) as $detail ) : ?>
			<?php it_exchange_get_template_part( 'super-widget-cart/items/details/' . $detail ); ?>
		<?php endforeach; ?>
	</div>
	<div class="item-info">
		<?php foreach( it_exchange_get_template_part_elements( 'super_widget_cart', 'items', array( 'price' ) ) as $detail ) : ?>
			<?php it_exchange_get_template_part( 'super-widget-cart/items/details/' . $detail ); ?>
		<?php endforeach; ?>
	</div>
	<?php do_action( 'it_exchange_super_widget_cart_items_end_item' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_cart_items_after_item' ); ?>
