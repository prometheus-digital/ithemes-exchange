<?php
/**
 * This file prints the Email Settings tab in the admin
 *
 * @scine   0.3.6
 * @package IT_Exchange
 */

global $wp_version;

$email_notifications = it_exchange_email_notifications();

?>
<div class="wrap">
	<?php
	ITUtility::screen_icon( 'it-exchange' );
	$this->print_general_settings_tabs();
	do_action( 'it_exchange_general_settings_email_page_top' );
	$form->start_form( $form_options, 'exchange-email-settings' );
	do_action( 'it_exchange_general_settings_email_form_top' );

	$h     = version_compare( $wp_version, '4.4', '>=' ) ? '1' : '2';
	$class = version_compare( $wp_version, '4.4', '>=' ) ? 'page-title-action' : 'add-new-h2';
	?>

	<h<?php echo $h; ?>>
		<?php _e( 'Emails', 'it-l10n-ithemes-exchange' ); ?>
		<a href="<?php echo( IT_Exchange_Email_Customizer::get_link() ); ?>" class="<?php echo $class; ?>">
			<?php _e( 'Customizer Appearance', 'it-l10n-ithemes-exchange' ); ?>
		</a>
	</h<?php echo $h; ?>>

	<table class="widefat striped">
		<thead>
		<tr>
			<th style="width: auto"><?php _e( 'Email', 'it-l10n-ithemes-exchange' ); ?></th>
			<th style="width: auto"><?php _e( 'Recipient', 'it-l10n-ithemes-exchange' ); ?></th>
			<th style="width: 30px;"><?php _e( 'Active', 'it-l10n-ithemes-exchange' ); ?></th>
			<th style="width: 30px"></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $email_notifications->get_notifications() as $notification ) : ?>
			<tr>
				<td><?php echo $notification->get_name(); ?></td>
				<td>
					<?php if ( $notification instanceof IT_Exchange_Customer_Email_Notification ): ?>
						<?php _e( 'Customer', 'it-l10n-ithemes-exchange' ); ?>
					<?php elseif ( $notification instanceof IT_Exchange_Admin_Email_Notification ): ?>
						<?php echo implode( ', ', $notification->get_emails() ); ?>
					<?php endif; ?>
				</td>
				<td>
					<span class="dashicons dashicons-<?php echo $notification->is_active() ? 'yes' : 'no'; ?>"></span>
				</td>
				<td>
					<a href="#" data-email="<?php echo $notification->get_slug(); ?>" class="show-email-settings">
						<span class="dashicons dashicons-admin-generic"></span>
						<span class="screen-reader-text"><?php _e( 'Configure', 'it-l10n-ithemes-exchange' ); ?></span>
					</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<?php foreach ( $email_notifications->get_notifications() as $notification ) : ?>

		<div class="email-<?php echo $notification->get_slug(); ?> email-settings-container hide-if-js">

			<h3><?php echo $notification->get_name(); ?></h3>

			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						<label for="email-<?php echo $notification->get_slug(); ?>-active">
							<?php _e( 'Active', 'it-l10n-ithemes-exchange' ); ?>
						</label>
					</th>
					<td>
						<input type="checkbox" id="email-<?php echo $notification->get_slug(); ?>-active" name="email[<?php echo $notification->get_slug() ?>][active]" <?php checked( $notification->is_active() ); ?>>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="email-<?php echo $notification->get_slug(); ?>-subject">
							<?php _e( 'Subject', 'it-l10n-ithemes-exchange' ); ?>
						</label>
					</th>
					<td>
						<input type="text" id="email-<?php echo $notification->get_slug(); ?>-subject" name="email[<?php echo $notification->get_slug() ?>][subject]" value="<?php echo $notification->get_subject(); ?>">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="email-<?php echo $notification->get_slug(); ?>-body">
							<?php _e( 'Body', 'it-l10n-ithemes-exchange' ); ?>
						</label>
					</th>
					<td>
						<?php wp_editor( $notification->get_body(), "email-{$notification->get_slug()}-body", array(
							'textarea_name' => "email[{$notification->get_slug()}][body]",
							'textarea_rows' => 10,
							'editor_height' => 400
						) ); ?>

						<p class="description">
							<?php
							_e( 'HTML is accepted. Available shortcode functions:', 'it-l10n-ithemes-exchange' );
							echo '<br />';
							printf( __( 'You call these shortcode functions like this: %s', 'it-l10n-ithemes-exchange' ), '[it_exchange_email show=order_table option=purchase_message]' );
							echo '<ul>';
							echo '<li>download_list - ' . __( 'A list of download links for each download purchased', 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>name - ' . __( "The buyer's first name", 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>fullname - ' . __( "The buyer's full name, first and last", 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>username - ' . __( "The buyer's username on the site, if they registered an account", 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>email - ' . __( "The buyer's email on the site", 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>order_table - ' . __( 'A table of the order details. Accept "purchase_message" option.', 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>purchase_date - ' . __( 'The date of the purchase', 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>total - ' . __( 'The total price of the purchase', 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>payment_id - ' . __( 'The unique ID number for this purchase', 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>receipt_id - ' . __( 'The unique ID number for this transaction', 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>payment_method - ' . __( 'The method of payment used for this purchase', 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>shipping_address - ' . __( 'The shipping address for this product. Blank if shipping is not required. Also accepts "before" and "after" arguments.', 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>billing_address - ' . __( 'The billing address for this product. Blank if shipping is not required. Also accepts "before" and "after" arguments.', 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>sitename - ' . __( 'Your site name', 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>receipt_link - ' . __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the email correctly.', 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>login_link - ' . __( 'Adds a link to the login page on your website.', 'it-l10n-ithemes-exchange' ) . '</li>';
							echo '<li>account_link - ' . __( 'Adds a link to the customer\'s account page on your website.', 'it-l10n-ithemes-exchange' ) . '</li>';
							do_action( 'it_exchange_email_template_tags_list' );
							echo '</ul>';
							?>
						</p>
					</td>
				</tr>
			</table>
		</div>

	<?php endforeach; ?>

	<h3><?php _e( 'Global Settings', 'it-l10n-ithemes-exchange' ); ?></h3>

	<table class="form-table">
		<?php do_action( 'it_exchange_general_settings_email_top' ); ?>
		<tr valign="top">
			<th scope="row">
				<label for="receipt-email-address"><?php _e( 'Email Sender Address', 'it-l10n-ithemes-exchange' ) ?></label>
			</th>
			<td>
				<?php $form->add_text_box( 'receipt-email-address', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( 'Email address used for customer receipt emails.', 'it-l10n-ithemes-exchange' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="receipt-email-name"><?php _e( 'Email Sender Name', 'it-l10n-ithemes-exchange' ) ?></label>
			</th>
			<td>
				<?php $form->add_text_box( 'receipt-email-name', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( 'Name used for account that sends customer receipt emails.', 'it-l10n-ithemes-exchange' ); ?></span>
			</td>
		</tr>

		<?php do_action( 'it_exchange_general_settings_email_table_bottom' ); ?>
	</table>

	<?php wp_nonce_field( 'save-email-settings', 'exchange-email-settings' ); ?>

	<p class="submit">
		<input type="submit" value="<?php _e( 'Save Changes', 'it-l10n-ithemes-exchange' ); ?>" class="button button-primary" />
	</p>

	<?php
	do_action( 'it_exchange_general_settings_email_form_bottom' );
	$form->end_form();
	do_action( 'it_exchange_general_settings_email_page_bottom' );
	?>
</div>
