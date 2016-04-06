<?php
/**
 * This file contains the markup for the email template header.
 *
 * @since   1.36
 * @link    http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
 *
 * Example: theme/exchange/emails/partial/header.php
 */

$layout = it_exchange( 'email', 'get-layout' );

$logo_top  = it_exchange( 'email', 'has-header-logo' ) ? '20px' : '0';
$table_top = $layout == 'boxed' ? '40px' : '0';

$row_bkg     = $layout == 'boxed' ? 'none' : it_exchange( 'email', 'get-header-background' );
$table_bkg   = $layout == 'full' ? 'none' : it_exchange( 'email', 'get-header-background' );
$row_class   = $layout == 'boxed' ? '' : 'header-bkg';
$table_class = $layout == 'full' ? '' : 'header-bkg';
?>
<tr style="background: <?php echo $row_bkg; ?>;" class="header-row <?php echo $row_class; ?>">
	<td align="center">
		<!--[if mso]>
		<center>
			<table>
				<tr>
					<td width="640">
		<![endif]-->
		<table id="header" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 640px; background: <?php echo $table_bkg; ?>; margin: <?php echo $table_top; ?> auto 0 auto; <?php echo it_exchange( 'email', 'has-header-image' ) ? 'min-height:225px;' : ''; ?>" class="<?php echo $table_class; ?>">
			<?php do_action( 'it_exchange_email_template_before_header_row' ); ?>
			<tr>
				<td align="center" valign="top" style="padding: 54px 25px;
					background-image: url(<?php it_exchange( 'email', 'header-image' ); ?>);
					background-position: top center; background-repeat: no-repeat; background-size: cover;
					border-top: 5px solid <?php it_exchange( 'email', 'header-background' ); ?>; border-bottom: 0; border-radius: 5px 5px 0 0;"
				>
					<?php do_action( 'it_exchange_email_template_begin_header' ); ?>

					<?php if ( it_exchange( 'email', 'has-header-logo' ) ): ?>
						<?php do_action( 'it_exchange_email_template_before_header_logo' ); ?>
						<img src="<?php it_exchange( 'email', 'header-logo' ); ?>" width="<?php it_exchange( 'email', 'header-logo-size' ); ?>" />
						<?php do_action( 'it_exchange_email_template_after_header_logo' ); ?>
					<?php endif; ?>

					<?php if ( it_exchange( 'email', 'has-header-store-name' ) ): ?>
						<?php do_action( 'it_exchange_email_template_before_header_store_name' ); ?>
						<h1 style="color: <?php it_exchange( 'email', 'header-store-name-color' ); ?>; font-family: <?php it_exchange( 'email', 'header-store-name-font' ); ?>; font-size: <?php it_exchange( 'email', 'header-store-name-size' ); ?>px; margin: <?php echo $logo_top; ?> 0 0 0;">
							<?php do_action( 'it_exchange_email_template_begin_header_store_name' ); ?>
							<?php it_exchange( 'email', 'header-store-name' ); ?>
							<?php do_action( 'it_exchange_email_template_end_header_store_name' ); ?>
						</h1>
						<?php do_action( 'it_exchange_email_template_after_header_store_name' ); ?>
					<?php endif; ?>

					<?php do_action( 'it_exchange_email_template_end_header' ); ?>
				</td>
			</tr>
			<?php do_action( 'it_exchange_email_template_after_header_row' ); ?>
		</table>
		<!--[if mso]>
		</td></tr></table>
		</center>
		<![endif]-->
	</td>
</tr>
<tr>
	<td style="height: 40px;"></td>
</tr>