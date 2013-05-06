<?php
/**
 * This file prints the add-ons page in the Admin
 *
 * @since 0.3.6
 * @package IT_Exchange
*/
?>
<div class="wrap">
	<!-- temp icon --> 
	<?php screen_icon( 'page' );  ?>
    
	<h2>Add-ons</h2>
    <p><?php _e( 'Add-Ons are features that you can add or remove depending on your needs. Selling your stuff should only be as complicated as you need it to be. Visit the Get More tab to see what else Exchange can do.', 'LION' ); ?></p>
    
	<?php
	$this->print_add_ons_page_tabs(); 
	do_action( 'it_exchange_add_ons_page_top' );
	
	$addons = it_exchange_get_more_addons();
	$addons = it_exchange_featured_addons_on_top( $addons );

	if ( !empty( $addons ) ) { 
	
		$class = '';
	
		foreach( (array) $addons as $addon ) {
		
			if ( $addon['featured'] )
				$class .= ' featured';
			
			if ( $addon['new'] )
				$class .= ' new';
				
			if ( $addon['sale'] )
				$class .= ' sale';
				
			if ( is_it_exchange_addon_installed( $addon['slug'] ) )
				$class .= ' install';
		
			echo '<div class="add-on-block' . $class . '">';
			echo '<div class="add-on-icon"><img src="' . $addon['icon'] . '" /></div>';
			echo '<h4>' . $addon['name'] . '</h4>';
			echo '<span class="add-on-author">by <a href="' . $addon['author_url'] . '">' . $addon['author'] . '</a></span>';
			echo '<p class="add-on-description">' . $addon['description'] . '</p>';
			
			if ( is_it_exchange_addon_installed( $addon['slug'] ) )
				echo '<div class="add-on-installed">Installed</div>';
			else {
				echo '<div class="regular-price ' . $class . '">' . $addon['price'] . '</div>';
				
				if ( $addon['sale'] ) 
					echo '<div class="sale-price">' . $addon['sale'] . '</div>';
					
				echo '<div class="add-on-buy-now"><a href="' . $addon['addon_url'] . '">' . __( 'Buy Now', 'LION' ) . '</a></div>';
			}
				
			echo '</div>';
			
		}   
		
	} else {
		
		echo '<p>' . __( 'No Add-ons currently enabled', 'LION' ) . '</p>';
		
	}
	 
	?> 
</div>