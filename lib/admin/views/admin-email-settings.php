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
	do_action( 'it_exchange_general_settings_email_page_top' );
	$form->start_form( $form_options, 'exchange-email-settings' );
	do_action( 'it_exchange_general_settings_email_form_top' );

	$h     = version_compare( $wp_version, '4.4', '>=' ) ? '1' : '2';
	$class = version_compare( $wp_version, '4.4', '>=' ) ? 'page-title-action' : 'add-new-h2';
	?>

	<h<?php echo $h; ?>>
		<?php _e( 'Emails', 'it-l10n-ithemes-exchange' ); ?>
		<a href="<?php echo( IT_Exchange_Email_Customizer::get_link() ); ?>" class="<?php echo $class; ?>">
			<?php _e( 'Customize Appearance', 'it-l10n-ithemes-exchange' ); ?>
		</a>
	</h<?php echo $h; ?>>

	<?php $this->print_general_settings_tabs(); ?>

	<?php if ( count( $email_notifications->get_groups() ) > 1 ): ?>
		<ul class="subsubsub">
			<li>
				<a href="javascript:" class="current" data-group="all">
					<?php _e( 'All', 'it-l10n-ithemes-exchange' ); ?>
				</a>
			</li>
			<?php foreach ( $email_notifications->get_groups() as $group ): ?>
				<li>
					| <a href="javascript:" data-group="<?php echo $group; ?>">
						<?php echo $group; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<table class="widefat striped emails">
		<thead>
		<tr>
			<th style="width: 40%"><?php _e( 'Email', 'it-l10n-ithemes-exchange' ); ?></th>
			<th style="width: 75px"><?php _e( 'Group', 'it-l10n-ithemes-exchange' ); ?></th>
			<th style="width: auto"><?php _e( 'Recipient', 'it-l10n-ithemes-exchange' ); ?></th>
			<th style="width: 30px;"><?php _e( 'Active', 'it-l10n-ithemes-exchange' ); ?></th>
			<th style="width: 30px"></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $email_notifications->get_notifications() as $notification ) : ?>
			<tr data-group="<?php echo $notification->get_group(); ?>">
				<td><?php echo $notification->get_name(); ?></td>
				<td><?php echo $notification->get_group(); ?></td>
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
	<p style="float:right;">
		<a href="<?php echo IT_Exchange_Email_Customizer::get_link(); ?>" class="button">
			<?php _e( 'Customize Appearance', 'it-l10n-ithemes-exchange' ); ?>
		</a>
	</p>

	<?php foreach ( $email_notifications->get_notifications() as $notification ) : ?>

		<div class="email-<?php echo $notification->get_slug(); ?> email-settings-container hide-if-js">

			<h3><?php echo $notification->get_name(); ?></h3>

			<?php if ( $notification->has_description() ): ?>
				<p class="description"><?php echo $notification->get_description(); ?></p>
			<?php endif; ?>

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
				<?php if ( $notification instanceof IT_Exchange_Admin_Email_Notification ): ?>
					<tr valign="top">
						<th scope="row">
							<label for="email-<?php echo $notification->get_slug(); ?>-emails">
								<?php _e( 'Email Address', 'it-l10n-ithemes-exchange' ); ?>
							</label>
						</th>
						<td>
							<input type="text" id="email-<?php echo $notification->get_slug(); ?>-emails" name="email[<?php echo $notification->get_slug() ?>][emails]" value="<?php echo implode( ',', $notification->get_emails() ); ?>">
							<p class="description"><?php _e( 'Email address used for admin notification emails.', 'it-l10n-ithemes-exchange' ); ?></p>
						</td>
					</tr>
				<?php endif ?>
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

							foreach ( $email_notifications->get_replacer()->get_tags_for( $notification ) as $tag ) {
								echo "<li><code>{$tag->get_tag()}</code> &ndash; {$tag->get_description()}</li>";
							}

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
				<label for="receipt-email-address"><?php _e( 'Email From Address', 'it-l10n-ithemes-exchange' ) ?></label>
			</th>
			<td>
				<?php $form->add_text_box( 'receipt-email-address', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( 'Email address used for customer emails.', 'it-l10n-ithemes-exchange' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="receipt-email-name"><?php _e( 'Email From Name', 'it-l10n-ithemes-exchange' ) ?></label>
			</th>
			<td>
				<?php $form->add_text_box( 'receipt-email-name', array( 'class' => 'normal-text' ) ); ?>
				<br /><span class="description"><?php _e( 'Name used for account that sends customer emails.', 'it-l10n-ithemes-exchange' ); ?></span>
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
