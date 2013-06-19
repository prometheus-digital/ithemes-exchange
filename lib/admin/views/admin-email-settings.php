<?php
/**
 * This file prints the Email Settings tab in the admin
 *
 * @scine 0.3.6
 * @package IT_Exchange
*/

global $wp_version;

?>
<div class="wrap">
	<?php
	screen_icon( 'it-exchange' );
	$this->print_general_settings_tabs();
	do_action( 'it_exchange_general_settings_email_page_top' );
	$form->start_form( $form_options, 'exchange-email-settings' );
	do_action( 'it_exchange_general_settings_email_form_top' );
	?>
	<table class="form-table">
		<?php do_action( 'it_exchange_general_settings_email_top' ); ?>
		<tr valign="top">
			<th scope="row"><strong><?php _e( 'Admin Notifications', 'LION' ); ?></strong></th>
			<td></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="notification-email-address"><?php _e( 'Sales Notification Email Address', 'LION' ) ?></label></th>
			<td>
			<?php $form->add_text_box( 'notification-email-address', array( 'class' => 'large-text' ) ); ?>
            <br /><span class="description"><?php _e( 'Enter the email address(es) that should receive a notification anytime a sale is made, coma seperated', 'LION' ); ?></span>
            </td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="admin-email-address"><?php _e( 'Email Address', 'LION' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'admin-email-address', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( 'Email address used for admin notification emails.', 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="admin-email-name"><?php _e( 'Email Name', 'LION' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'admin-email-name', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( 'Name used for account that sends admin notification emails.', 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="admin-email-subject"><?php _e( 'Notification Subject Line', 'LION' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'admin-email-subject', array( 'class' => 'large-text' ) ); ?>
				<br /><span class="description"><?php _e( 'Subject line used for admin notification emails.', 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="receipt-email-template"><?php _e( 'Notification Email Template', 'LION' ) ?></label></th>
			<td>
				<?php
                if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
                    echo wp_editor( $settings['admin-email-template'], 'admin-email-template', array( 'textarea_name' => 'it_exchange_email_settings-admin-email-template', 'textarea_rows' => 10, 'textarea_cols' => 30, 'editor_class' => 'large-text' ) );
					
					//We do this for some ITForm trickery... just to add receipt-email-template to the used inputs field
					$form->get_text_area( 'admin-email-template', array( 'rows' => 10, 'cols' => 30, 'class' => 'large-text' ) );
                } else {
                    $form->add_text_area( 'admin-email-template', array( 'rows' => 10, 'cols' => 30, 'class' => 'large-text' ) );
                }
                ?>
                <p class="description">
                <?php 
				_e( 'Enter the email that is sent to administrator after a customer completes a successful purchase. HTML is accepted. Available shortcode functions:', 'LION' ); 
				echo '<br />';
				printf( __( 'You call these shortcode functions like this: %s', 'LION' ), '[it_exchange_email show=order_table option=purchase_message]' ); 
				echo '<ul>';
				echo '<li>download_list - ' . __( 'A list of download links for each download purchased', 'LION' ) . '</li>';
				echo '<li>name - ' . __( "The buyer's first name", 'LION' ) . '</li>';
				echo '<li>fullname - ' . __( "The buyer's full name, first and last", 'LION' ) . '</li>';
				echo '<li>username - ' . __( "The buyer's user name on the site, if they registered an account", 'LION' ) . '</li>';
				echo '<li>order_table - ' . __( 'A table of the order details. Accept "purchase_message" option.', 'LION' ) . '</li>';
				echo '<li>purchase_date - ' . __( 'The date of the purchase', 'LION' ) . '</li>';
				echo '<li>total - ' . __( 'The total price of the purchase', 'LION' ) . '</li>';
				echo '<li>payment_id - ' . __( 'The unique ID number for this purchase', 'LION' ) . '</li>';
				echo '<li>receipt_id - ' . __( 'The unique ID number for this transaction', 'LION' ) . '</li>';
				echo '<li>payment_method - ' . __( 'The method of payment used for this purchase', 'LION' ) . '</li>';
				echo '<li>sitename - ' . __( 'Your site name', 'LION' ) . '</li>';
				echo '<li>receipt_link - ' . __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'LION' ) . '</li>';
				do_action( 'it_exchange_email_template_tags_list' );
				echo '</ul>';
				?>
				</p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><strong><?php _e( 'Customer Receipt Emails', 'LION' ); ?></strong></th>
			<td></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="receipt-email-address"><?php _e( 'Email Address', 'LION' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'receipt-email-address', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( 'Email address used for customer receipt emails.', 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="receipt-email-name"><?php _e( 'Email Name', 'LION' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'receipt-email-name', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( 'Name used for account that sends customer receipt emails.', 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="receipt-email-subject"><?php _e( 'Subject Line', 'LION' ) ?></label></th>
			<td>
				<?php $form->add_text_box( 'receipt-email-subject', array( 'class' => 'large-text' ) ); ?>
				<br /><span class="description"><?php _e( 'Subject line used for customer receipt emails.', 'LION' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="receipt-email-template"><?php _e( 'Email Template', 'LION' ) ?></label></th>
			<td>
				<?php
                if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
                    echo wp_editor( $settings['receipt-email-template'], 'receipt-email-template', array( 'textarea_name' => 'it_exchange_email_settings-receipt-email-template', 'textarea_rows' => 10, 'textarea_cols' => 30, 'editor_class' => 'large-text' ) );
					
					//We do this for some ITForm trickery... just to add receipt-email-template to the used inputs field
					$form->get_text_area( 'receipt-email-template', array( 'rows' => 10, 'cols' => 30, 'class' => 'large-text' ) );
                } else {
                    $form->add_text_area( 'receipt-email-template', array( 'rows' => 10, 'cols' => 30, 'class' => 'large-text' ) );
                }
                ?>
                <p class="description">
                <?php 
				_e( 'Enter the email that is sent to users after completing a successful purchase. HTML is accepted. Available shortcode functions:', 'LION' ); 
				echo '<br />';
				printf( __( 'You call these shortcode functions like this: %s', 'LION' ), '[it_exchange_email show=order_table option=purchase_message]' ); 
				echo '<ul>';
				echo '<li>download_list - ' . __( 'A list of download links for each download purchased', 'LION' ) . '</li>';
				echo '<li>name - ' . __( "The buyer's first name", 'LION' ) . '</li>';
				echo '<li>fullname - ' . __( "The buyer's full name, first and last", 'LION' ) . '</li>';
				echo '<li>username - ' . __( "The buyer's user name on the site, if they registered an account", 'LION' ) . '</li>';
				echo '<li>order_table - ' . __( 'A table of the order details. Accept "purchase_message" option.', 'LION' ) . '</li>';
				echo '<li>purchase_date - ' . __( 'The date of the purchase', 'LION' ) . '</li>';
				echo '<li>total - ' . __( 'The total price of the purchase', 'LION' ) . '</li>';
				echo '<li>payment_id - ' . __( 'The unique ID number for this purchase', 'LION' ) . '</li>';
				echo '<li>receipt_id - ' . __( 'The unique ID number for this transaction', 'LION' ) . '</li>';
				echo '<li>payment_method - ' . __( 'The method of payment used for this purchase', 'LION' ) . '</li>';
				echo '<li>sitename - ' . __( 'Your site name', 'LION' ) . '</li>';
				echo '<li>receipt_link - ' . __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'LION' ) . '</li>';
				do_action( 'it_exchange_email_template_tags_list' );
				echo '</ul>';
				?>
				</p>
			</td>
		</tr>
		<?php do_action( 'it_exchange_general_settings_email_table_bottom' ); ?>
	</table>
	<?php wp_nonce_field( 'save-email-settings', 'exchange-email-settings' ); ?>
	<p class="submit"><input type="submit" value="<?php _e( 'Save Changes', 'LION' ); ?>" class="button button-primary" /></p>
	<?php
	do_action( 'it_exchange_general_settings_email_form_bottom' );
	$form->end_form();
	do_action( 'it_exchange_general_settings_email_page_bottom' );
	?>
</div>
