<?php
/**
 * The default product-info loop for the content-purchases.php template part
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_purchases_before_product_info_loop' ); ?>
<h4>
<?php foreach( it_exchange_get_template_part_elements( 'content_purchases', 'product_fields', array( 'product-title', 'product-subtotal', 'product-count' ) ) as $detail ): ?>
	<?php it_exchange_get_template_part( 'content-purchases/details/product-fields/' . $detail ); ?>
<?php endforeach; ?>
</h4>
<p>
<?php foreach( it_exchange_get_template_part_elements( 'content_purchases', 'fields', array( 'product-description' ) ) as $detail ): ?>
	<?php it_exchange_get_template_part( 'content-purchases/details/product-fields/' . $detail ); ?>
<?php endforeach; ?>
</p>
<?php do_action( 'it_exchange_content_purchases_after_product_info_loop' ); ?>