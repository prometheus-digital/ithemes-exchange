<?php
/**
 * This file prints the add-ons page in the Admin
 *
 * @since 0.3.6
 * @package IT_Exchange
*/
?>
<div id="it-exchange-add-ons-wrap" class="wrap">
	<?php screen_icon( 'it-exchange-add-ons' );  ?>
	<h2>Add-ons</h2>
	<p class="top-description"><?php _e( 'Add-Ons are features that you can add or remove depending on your needs. Selling your stuff should only be as complicated as you need it to be. Visit the Get More tab to see what else Exchange can do.', 'LION' ); ?></p>
	
	<?php $this->print_add_ons_page_tabs(); ?>
	<?php do_action( 'it_exchange_add_ons_page_top' ); ?>

	<?php
		$tab = ! empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'all';
		
		switch ( $tab ) {
			case 'enabled':
				$addons = it_exchange_get_enabled_addons( array( 'show_required' => false ) );
				break;
			
			case 'disabled':
				$addons = it_exchange_get_disabled_addons( array( 'show_required' => false ) );
				break;
			
			case 'all':
			default:
				$addons = it_exchange_get_addons( array( 'show_required' => false ) );
				break;
		}
	?>
	<div class="add-ons-wrapper">
		<?php if ( ! empty( $addons ) ) : ?>
			
			<?php $default_icon = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/default-add-on-icon.png' ); ?>
			
			<?php foreach( (array) $addons as $addon ) : ?>
            	
				<?php
				
				if ( !empty( $addon['options']['tag'] ) && 'required' === $addon['options']['tag'] )
					continue;
				
				?>
            	
				<?php $icon = empty( $addon['options']['icon'] ) ? $default_icon : $addon['options']['icon']; ?>
				<div class="add-on-block">
					<div class="add-on-icon">
						<div class="image-wrapper">
							<img src="<?php echo $icon; ?>" alt="" />
						</div>
					</div>
					<div class="add-on-info">
						<h4><?php echo $addon['name']; ?></h4>
						<span class="add-on-author">by <a href="<?php echo $addon['author_url']; ?>"><?php echo $addon['author']; ?></a></span>
                        <?php if ( !empty( $addon['options']['tag'] ) ) { ?>
						<span class="add-on-tag"><?php echo $addon['options']['tag']; ?></span>
                        <?php } ?>
						<p class="add-on-description"><?php echo $addon['description']; ?></p>
					</div>
					<div class="add-on-actions">
						<?php if ( it_exchange_is_addon_enabled( $addon['slug'] ) ) : ?>
							<?php $url = it_exchange_is_core_addon( $addon['slug'] ) ? wp_nonce_url( get_site_url() . '/wp-admin/admin.php?page=it-exchange-addons&it-exchange-disable-addon=' . $addon['slug'] . '&tab=' . $tab, 'exchange-disable-add-on' ) : admin_url() . 'plugins.php'; ?>
							<div class="add-on-enabled"><a href="<?php echo $url; ?>" data-text-disable="&times;&nbsp; Disable" data-text-enabled="&#x2714;&nbsp; Enabled">&#x2714;&nbsp; Enabled</a></div>
						<?php else : ?>
							<div class="add-on-disabled"><a href="<?php echo wp_nonce_url( get_site_url() . '/wp-admin/admin.php?page=it-exchange-addons&it-exchange-enable-addon=' . $addon['slug'] . '&tab=' . $tab, 'exchange-enable-add-on' ); ?>" data-text-enable="&#x2714;&nbsp; Enable" data-text-disabled="&times;&nbsp; Disabled">-&nbsp; Disabled</a></div>
						<?php endif; ?>
						
						<?php if ( ! empty( $addon['options']['settings-callback'] ) && is_callable( $addon['options']['settings-callback'] ) ) : ?>
							<div class="add-on-settings"><a href="<?php echo admin_url( 'admin.php?page=it-exchange-addons&add-on-settings=' . $addon['slug'] ); ?>">S</a></div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?> 
		<?php else : ?>
			<?php 
			if ( 'all' == $tab ) {
				$vars = array( 
					'target' => add_query_arg( 'tab', 'get-more' ),
					'text'   => __( 'No add-ons installed. How\'d you do that?', 'LION' ),
					'link'   => __( 'Go get some now', 'LION' )
				);
			} else if ( 'enabled' == $tab ) {
				$vars = array( 
					'target' => add_query_arg( 'tab', 'disabled' ),
					'text'   => __( 'No add-ons are currently enabled', 'LION' ),
					'link'   => __( 'Go enable some now', 'LION' )
				);
			} else if ( 'disabled' == $tab ) {
				$vars = array( 
					'target' => add_query_arg( 'tab', 'get-more' ),
					'text'   => __( 'All add-ons are currently enabled', 'LION' ),
					'link'   => __( 'Go get more', 'LION' )
				);
			}
			?>
			<div class="addons-achievement">
				<h2><?php echo $vars['text']; ?></h2>
				<p>
					<a href="<?php echo $vars['target']; ?>" class="it-exchange-button"><?php echo $vars['link'] ?></a>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>
