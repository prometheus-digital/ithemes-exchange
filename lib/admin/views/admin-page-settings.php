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
	screen_icon( 'page' );
	$this->print_general_settings_tabs();
	echo do_action( 'it_exchange_general_settings_page_page_top' );
	$form->start_form( $form_options, 'exchange-page-settings' );
	echo do_action( 'it_exchange_general_settings_page_form_top' );
	?>
	<table class="form-table">
		<?php do_action( 'it_exchange_general_settings_page_top' ); ?>
		<tr valign="top">
			<th scope="row"><strong>Page Settings</strong></th>
			<td></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="it-exchange-page-store"><?php _e( 'Store Slug' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'store', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( sprintf( 'The URL for your store: %s', '<br />' . get_home_url() . '/' . esc_attr( $form->get_option( 'store' ) ) . '/' ), 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="it-exchange-page-product"><?php _e( 'Single Product' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'product', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( sprintf( 'The URL for a single product: %s', '<br />' . get_home_url() . '/' . esc_attr( $form->get_option( 'product' ) ) . '/shiny-widget/' ), 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="it-exchange-page-account"><?php _e( 'Account Slug' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'account', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( sprintf( 'The URL for your customer account dashbaord: %s', '<br />' . get_home_url() . '/' . esc_attr( $form->get_option( 'account' ) ) . '/' ), 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="it-exchange-page-profile"><?php _e( 'Profile Slug' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'profile', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( sprintf( 'The URL for your customer profiles: %s', '<br />' . get_home_url() . '/' . esc_attr( $form->get_option( 'account' ) ) . '/' . esc_attr( $form->get_option( 'profile' ) ) . '/' ), 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="it-exchange-page-profile-edit"><?php _e( 'Edit Profile Slug' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'profile-edit', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( sprintf( 'The URL used by your customers to edit thier profiles: %s', '<br />' . get_home_url() . '/' . esc_attr( $form->get_option( 'account' ) ) . '/' . esc_attr( $form->get_option( 'profile' ) ) . '/' . esc_attr( $form->get_option( 'profile-edit' ) ) . '/' ), 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="it-exchange-page-profile-log-in"><?php _e( 'Customer Log in Slug' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'log-in', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( sprintf( 'The URL used by your customers to log in to your store: %s', '<br />' . get_home_url() . '/' . esc_attr( $form->get_option( 'account' ) ) . '/' .  esc_attr( $form->get_option( 'log-in' ) ) . '/' ), 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="it-exchange-page-profile-purchases"><?php _e( 'Customer Purchases Slug' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'purchases', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( sprintf( 'The URL used by your customers to view their purchases: %s', '<br />' . get_home_url() . '/' . esc_attr( $form->get_option( 'account' ) ) . '/' .  esc_attr( $form->get_option( 'purchases' ) ) . '/' ), 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="it-exchange-page-profile-downloads"><?php _e( 'Customer Downloads Slug' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'downloads', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( sprintf( 'The URL used by your customers to view their downloads: %s', '<br />' . get_home_url() . '/' . esc_attr( $form->get_option( 'account' ) ) . '/' . esc_attr( $form->get_option( 'downloads' ) ) . '/' ), 'LION' ); ?></span>
			</td>
		</tr>
		<?php do_action( 'it_exchange_general_settings_page_table_bottom' ); ?>
	</table>
	<?php wp_nonce_field( 'save-page-settings', 'exchange-page-settings' ); ?>
	<p class="submit"><input type="submit" value="<?php _e( 'Save Changes', 'LION' ); ?>" class="button button-primary" /></p>
	<?php
	do_action( 'it_exchange_general_settings_page_form_bottom' );
	$form->end_form();
	do_action( 'it_exchange_general_settings_page_page_bottom' );
	?>
</div>
<?php
