<?php
/**
 * Titles column for cart totals
*/
?>
<div class="totals-column totals-titles cart-column">
	<?php foreach( it_exchange_get_cart_totals_column_rows() as $row ) : ?>
		<?php it_exchange_get_template_part( 'cart/cart-totals-column-rows/titles-' . $row ); ?>
	<?php endforeach; ?>
</div>
