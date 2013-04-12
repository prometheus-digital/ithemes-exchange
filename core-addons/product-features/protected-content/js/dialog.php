<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php _e( 'Protect the Selected Content', 'LION' ); ?></title>
	<script language="javascript" type="text/javascript" src="<?php echo get_site_url() . '/' . WPINC; ?>/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_site_url() . '/' . WPINC; ?>/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_site_url() . '/' . WPINC; ?>/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_site_url() . '/' . WPINC; ?>/js/jquery/jquery.js"></script>

	<style type="text/css">
		body { font-family: Arial, Helvetica; }
		table th { vertical-align: top; }
		.panel_wrapper { border-top: 1px solid #909B9C; }
		.panel_wrapper div.current { height:auto !important; }
	</style>

</head>
	<body>
		<div id="wpwrap">
		<p><?php _e( 'A member must have purchased at least one of the checked products below to see this content.', 'LION' ); ?></p>
		<p><?php _e( 'If no products are checked, nobody will have access to this content.', 'LION' ); ?></p>
		<form action="#" id="dialog">
			<div class="panel_wrapper">
				<table border="0" cellpadding="4" cellspacing="0">
				<tr id="product-selector">
					<th nowrap="nowrap"><?php _e('Products', 'LION'); ?></th>
					<td id="product-checkboxes"><?php _e( 'Loading...', 'LION' );?></td>
				</tr>
				</table>
			</div>

			<div class="mceActionPanel">
				<div style="float: left">
					<input type="button" id="cancel" name="cancel" value="{#cancel}" />
				</div>

				<div style="float: right">
					<input type="button" id="insert" name="insert" value="{#insert}" />
				</div>
			</div>
		</form>
		</div>

		<script language="javascript" type="text/javascript">
		/* <![CDATA[ */
		tinyMCEPopup.onInit.add(function(ed) {
			jQuery.noConflict()(function($){
				var pc = $('#product-checkboxes'),

					insert = $('#insert').click(function() {
						var selection = ed.selection.getContent(),
						products = [];

						$('#product-checkboxes :checked').each(function() {
							products.push( $(this).val() );
						});

						if (window.tinyMCE) {
							ed.selection.setContent( '[it-exchange-protected-content products="' + products.join() + '"]' + selection + '[/it-exchange-protected-content]' );
							tinyMCEPopup.close();
						}
					}),

					cancel = jQuery('#cancel').click(function () {
						tinyMCEPopup.close();
					}),
					
					checkboxes = $.get("<?php echo wp_nonce_url(admin_url('admin-ajax.php'),'wp_ajax_it_exchange_protected_content_addon_get_products'); ?>&action=it_exchange_protected_content_addon_get_protected_products",{},function (r) { 
						pc.empty().html(r); },'html');
			});
		});

		/* ]]> */
		</script>
	</body>
</html>
