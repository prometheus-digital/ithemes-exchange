<div class="it-exchange-order-total">
	<?php _e( 'Order Total:', 'LION' ); ?> <?php it_exchange( 'cart', 'total' ); ?>
	<?php it_exchange( 'cart', 'empty', 'format=link&label=' . __( 'Cancel', 'LION' ) ); ?>
	<?php it_exchange( 'checkout', 'cancel', 'label=' . __( 'Details', 'LION' ) ); ?>
</div>
<div class="it-exchange-payment-methods">
    <?php if ( ! it_exchange( 'checkout', 'has-transaction-methods' ) ) : ?>
        <p><?php _e( 'No Payment add-ons enabled.', 'LION' ); ?></p>
    <?php else : ?>
        <?php while( it_exchange( 'checkout', 'transaction-methods' ) ) : ?>
            <?php it_exchange( 'transaction-method', 'make-payment' ); ?>
        <?php endwhile; ?>
    <?php endif; ?>
</div>
