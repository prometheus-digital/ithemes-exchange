<?php
/**
 * This file prints the admin screen used to select which type of new product needs to be created
 *
 * @since 0.3.6
 * @package IT_Cart_Buddy
*/
?>
<div class="wrap">
	<?php screen_icon( 'page' ); ?>
	<h2>Choose an Product Type to add</h2>
	<p>Temp UI...</p>
	<ul>
	<?php
	foreach( it_cart_buddy_get_enabled_add_ons( array( 'category' => array( 'product-type' ) ) ) as $slug => $params ) {
		echo '<li><a href="' . get_site_url() . '/wp-admin/post-new.php?post_type=it_cart_buddy_prod&product_type=' . $slug . '">' . $params['name'] . '</a>';
	}
	?>
	</ul>
</div>
<?php
