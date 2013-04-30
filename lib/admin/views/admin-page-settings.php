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
			<th scope="row"><?php _e( 'Page', 'LION' ); ?></th>
			<th scope="row"><?php _e( 'Page Title', 'LION' ); ?></th>
			<th scope="row"><?php _e( 'Page Slug', 'LION' ); ?></th>
			<th scope="row"><?php _e( 'Example URL', 'LION' ); ?></th>
		</tr>
		<tr valign="top">
			<td>
				<label for="it-exchange-page-store"><?php _e( 'Store Slug' ) ?></label>
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
				<label for="it-exchange-page-product"><?php _e( 'Single Product' ) ?></label>
			</td>
			<td>
				<?php $form->add_text_box( 'product-name', array( 'class' => 'normal-text' ) ); ?>
			</td>
			<td>
				<?php $form->add_text_box( 'product-slug', array( 'class' => 'normal-text' ) ); ?>
			</td>
			<td>
				<?php echo get_home_url() . '/' . esc_attr( $form->get_option( 'product-slug' ) ) . '/shiny-widget/'; ?>
			</td>
		</tr>
		<tr valign="top">
			<td>
				<label for="it-exchange-page-account"><?php _e( 'Account Page' ) ?></label>
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
				<label for="it-exchange-page-profile"><?php _e( 'Profile Page' ) ?></label>
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
				<label for="it-exchange-page-profile-edit"><?php _e( 'Edit Profile' ) ?></label>
			</td>
			<td>
				<?php $form->add_text_box( 'profile-edit-name', array( 'class' => 'normal-text' ) ); ?>
			</td>
			<td>
				<?php $form->add_text_box( 'profile-edit-slug', array( 'class' => 'normal-text' ) ); ?>
			</td>
			<td>
				<?php echo it_exchange_get_page_url( 'profile-edit' ); ?>
			</td>
		</tr>
		<tr valign="top">
			<td>
				<label for="it-exchange-page-log-in"><?php _e( 'Customer Log in' ) ?></label>
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
				<label for="it-exchange-page-purchases"><?php _e( 'Customer Purchases' ) ?></label>
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
				<label for="it-exchange-page-downloads"><?php _e( 'Customer Downloads' ) ?></label>
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
				<label for="it-exchange-page-cart"><?php _e( 'Shopping Cart' ) ?></label>
			</td>
			<td>
				<?php $form->add_text_box( 'cart-name', array( 'class' => 'normal-text' ) ); ?>
			</td>
			<td>
				<?php $form->add_text_box( 'cart-slug', array( 'class' => 'normal-text' ) ); ?>
			</td>
			<td>
				<?php echo it_exchange_get_page_url( 'cart' ); ?>
			</td>
		</tr>
		<tr valign="top">
			<td>
				<label for="it-exchange-page-checkout"><?php _e( 'Checkout' ) ?></label>
			</td>
			<td>
				<?php $form->add_text_box( 'checkout-name', array( 'class' => 'normal-text' ) ); ?>
			</td>
			<td>
				<?php $form->add_text_box( 'checkout-slug', array( 'class' => 'normal-text' ) ); ?>
			</td>
			<td>
				<?php echo it_exchange_get_page_url( 'checkout' ); ?>
			</td>
		</tr>
		<tr valign="top">
			<td>
				<label for="it-exchange-page-confirmation"><?php _e( 'Purchase Confirmation' ) ?></label>
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
