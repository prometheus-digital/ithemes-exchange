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
				<th scope="row"><?php _e( 'Page Title', 'LION' ); ?></th>
				<th scope="row"><?php _e( 'Page Slug', 'LION' ); ?></th>
				<!-- <th scope="row"><?php _e( 'Example URL', 'LION' ); ?></th> -->
			</tr>
		</thead>
		<tbody>
			<tr valign="top">
				<td>
					<label for="store-name"><?php _e( 'Store Slug', 'LION' ) ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'store-name', array( 'class' => 'normal-text' ) ); ?>
				<td>
					<?php $form->add_text_box( 'store-slug', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php echo it_exchange_get_page_url( 'store' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<label for="product-name"><?php _e( 'Single Product', 'LION' ) ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'product-name', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php $form->add_text_box( 'product-slug', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php echo get_home_url() . '/' . esc_attr( $form->get_option( 'product-slug' ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<label for="account-name"><?php _e( 'Account Page', 'LION' ) ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'account-name', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php $form->add_text_box( 'account-slug', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php echo it_exchange_get_page_url( 'account' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<label for="profile-name"><?php _e( 'Profile Page', 'LION' ) ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'profile-name', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php $form->add_text_box( 'profile-slug', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php echo it_exchange_get_page_url( 'profile' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<label for="registration-name"><?php _e( 'Customer Registration', 'LION' ) ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'registration-name', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php $form->add_text_box( 'registration-slug', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php echo it_exchange_get_page_url( 'registration', 'LION' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<label for="log-in-name"><?php _e( 'Customer Log in', 'LION' ) ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'log-in-name', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php $form->add_text_box( 'log-in-slug', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php echo it_exchange_get_page_url( 'log-in' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<label for="log-in-name"><?php _e( 'Customer Log out', 'LION' ) ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'log-out-name', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php $form->add_text_box( 'log-out-slug', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php echo it_exchange_get_page_url( 'log-out' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<label for="purchases-name"><?php _e( 'Customer Purchases', 'LION' ) ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'purchases-name', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php $form->add_text_box( 'purchases-slug', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php echo it_exchange_get_page_url( 'purchases' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<label for="downloads-name"><?php _e( 'Customer Downloads', 'LION' ) ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'downloads-name', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php $form->add_text_box( 'downloads-slug', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php echo it_exchange_get_page_url( 'downloads' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<label for="confirmation-name"><?php _e( 'Purchase Confirmation', 'LION' ) ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'confirmation-name', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php $form->add_text_box( 'confirmation-slug', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php echo it_exchange_get_page_url( 'confirmation' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<label for="reports-name"><?php _e( 'Admin Reports', 'LION' ) ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'reports-name', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php $form->add_text_box( 'reports-slug', array( 'class' => 'normal-text' ) ); ?>
				</td>
				<td>
					<?php echo get_home_url() . '/' . esc_attr( $form->get_option( 'store-slug' ) ) . '/' . esc_attr( $form->get_option( 'reports-slug' ) ) . '/'; ?>
				</td>
			</tr>
			<?php
			// Allow add-ons to create their own ghost pages
			$add_on_ghost_pages = apply_filters( 'it_exchange_add_ghost_pages', array(), $this );
			foreach( (array) $add_on_ghost_pages as $page => $data ) {
				if ( empty( $data['include_in_settings_pages'] ) )
					continue;
				$slug = $data['slug'];
				$name = $data['name'];
				?>
				<tr valign="top">
					<td>
						<label for="<?php esc_attr_e( $slug ); ?>-name"><?php _e( $name ); ?></label>
					</td>
					<td>
						<?php $form->add_text_box( $slug . '-name', array( 'class' => 'normal-text' ) ); ?>
					</td>
					<td>
						<?php $form->add_text_box( $slug . '-slug', array( 'class' => 'normal-text' ) ); ?>
					</td>
					<td>
						<?php echo it_exchange_get_page_url( $slug ); ?>
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
