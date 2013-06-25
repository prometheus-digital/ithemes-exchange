<?php it_exchange_get_template_part( 'messages' ); ?>
<?php if ( it_exchange( 'product', 'found' ) ) : ?>
<div class="it-exchange-sw-product it-exchange-sw-processing-product">

	<?php if ( it_exchange_is_multi_item_cart_allowed() ) : ?>
   		<?php it_exchange_get_template_part( 'super-widget', 'cart' ); ?>
    <?php endif; ?>

    <?php if ( it_exchange_is_page( 'product' ) 
			&& ( !it_exchange_is_multi_item_cart_allowed() || !it_exchange_is_current_product_in_cart() ) ) : ?>
        <div class="purchase-options">
            <?php it_exchange( 'product', 'purchase-options', array( 'add-to-cart-edit-quantity' => false, 'buy-now-edit-quantity' => false ) ); ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>
