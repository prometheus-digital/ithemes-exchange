<?php
/**
 * This file prints the Page Settings tab in the admin
 *
 * @scine 0.3.7
 * @package IT_Exchange
*/
?>
<div class="wrap">
	<?php
	screen_icon( 'it-exchange' );
	$this->print_general_settings_tabs();
	do_action( 'it_exchange_general_settings_page_page_top' );
	$form->start_form( $form_options, 'exchange-page-settings' );
	do_action( 'it_exchange_general_settings_page_form_top' );
	?>
	<table class="form-table">
		<?php do_action( 'it_exchange_general_settings_page_top' ); ?>
		<thead>
			<tr valign="top">
				<th scope="row"><?php _e( 'Page', 'LION' ); ?></th>
				<th scope="row"><?php _e( 'Page Type', 'LION' ); ?></th>
				<th scope="row"><?php _e( 'Page Title', 'LION' ); ?></th>
				<th scope="row"><?php _e( 'Page Slug', 'LION' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$pages    = it_exchange_get_registered_pages();
			$wp_pages = array( 0 => __( 'Select a Page', 'LION' ) )+it_exchange_get_wp_pages();
			foreach( $pages as $page => $data ) {
				/**
				 * Don't show options for transactions at all.
				 * @todo remove transaction from pages and use query args
				*/
				if ( 'transaction' == $page ) 
					continue

				$options = array();
				$url = '';
				?>
				<tr valign="top">
					<td>
						<label for="<?php esc_attr_e( $page ); ?>-name"><?php esc_attr_e( $data['settings-name'] ); ?></label>
						<br /><span class="page-var">(<?php esc_attr_e( $page ); ?>)</span>
					</td>
					<td>
						<?php 
						// Build options. Everyone gets Exchange
						$options['exchange'] = __( 'Exchange', 'LION' );

						// Products don't get WordPress
						if ( 'product' != $page )
							$options['wordpress'] = __( 'WordPress', 'LION' );

						// Only optional pages get Disabled
						if ( $data['optional'] )
							$options['disabled'] = __( 'Disabled', 'LION' );
						
						// If count is 1, just print it and create a hidden field
						if ( count( $options ) < 2 ) {
							$form->add_hidden( $page . '-type' );
							esc_attr_e( reset( array_values( $options ) ) );
						} else {
							$form->add_drop_down( $page . '-type', $options );
						}
						?>
					</td>
					<td>
						<?php $form->add_text_box( $page . '-name', array( 'class' => 'normal-text' ) ); ?>
						<?php $form->add_drop_down( $page . '-wpid', $wp_pages ); ?>
					</td>
					<td>
						<?php $form->add_text_box( $page . '-slug', array( 'class' => 'normal-text' ) ); ?>
						<?php $url = esc_attr( it_exchange_get_page_url( $page ) );
						if ( 'product' == $page )
							$url = ( false == get_option( 'permalink_structure' ) ) ? get_home_url() . '?' . esc_attr( $form->get_option( 'product-slug' ) ) . '=product-name' : get_home_url() . '/' . esc_attr( $form->get_option( 'product-slug' ) ) . '/product-name';
						?>
						<br /><?php echo $url; ?>
						<?php
						if ( 'product' != $page ) {
							echo "<br /><code>[it-exchange-page page='" . esc_attr( $page ) . "']</code>";
						}
						?>
					</td>
				</tr>
				<?php
			}
			?>
		</tbody>
		<?php do_action( 'it_exchange_general_settings_page_table_bottom' ); ?>
	</table>
	<?php wp_nonce_field( 'save-page-settings', 'exchange-page-settings' ); ?>
	<p class="submit"><input type="submit" value="<?php _e( 'Save Changes', 'LION' ); ?>" class="button button-primary button-large" /></p>
	<?php
	do_action( 'it_exchange_general_settings_page_form_bottom' );
	$form->end_form();
	do_action( 'it_exchange_general_settings_page_page_bottom' );
	?>
</div>
