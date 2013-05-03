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
	?>

	<?php
	$tab = !empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'all';
	
	switch ( $tab ) {
	
		case 'enabled':
			$addons = it_exchange_get_enabled_addons();
			break;
			
		case 'disabled':
			$addons = it_exchange_get_disabled_addons();
			break;
			
		case 'all':
		default:
			$addons = it_exchange_get_addons();
			break;
		
	}
	
	if ( !empty( $addons ) ) { 
	
		$default_icon = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/default-add-on-icon.png' );
		
		foreach( (array) $addons as $addon ) {
		
			$icon = empty( $addon['options']['icon'] ) ? $default_icon : $addon['options']['icon'];
		
			echo '<div class="add-on-block">';
			echo '<div class="add-on-icon"><img src="' . $icon . '" /></div>';
			echo '<h4>' . $addon['name'] . '</h4>';
			echo '<span class="add-on-author">by <a href="' . $addon['author_url'] . '">' . $addon['author'] . '</a></span>';
			echo '<span class="add-on-tag">' . $addon['options']['tag'] . '</span>';
			echo '<p class="add-on-description">' . $addon['description'] . '</p>';

			if ( ! empty( $addon['options']['settings-callback'] ) && is_callable( $addon['options']['settings-callback'] ) ) 
				echo '<div class="add-on-settings"><a href="' . admin_url( 'admin.php?page=it-exchange-addons&add-on-settings=' . $addon['slug'] ) . '">S</a></div>';
			
			if ( is_it_exchange_addon_enabled( $addon['slug'] ) )
				echo '<div class="add-on-disable"><a href="' . wp_nonce_url( get_site_url() . '/wp-admin/admin.php?page=it-exchange-addons&it-exchange-disable-addon=' . $addon['slug'], 'exchange-disable-add-on' ) . '">Disable</a></div>';
			else
				echo '<div class="add-on-enable"><a href="' . wp_nonce_url( get_site_url() . '/wp-admin/admin.php?page=it-exchange-addons&it-exchange-enable-addon=' . $addon['slug'], 'exchange-enable-add-on' ) . '">Enable</a></div>';
				
			echo '</div>';
			
		}   
		
	} else {
		
		echo '<p>' . __( 'No Add-ons currently enabled', 'LION' ) . '</p>';
		
	}
	 
	?> 
</div>
<?php
