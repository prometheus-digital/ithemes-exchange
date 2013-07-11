<?php
/**
 * This file contains the notice for the Wizard setup
 * @package IT_Exchange
 * @since 0.4.0
*/
// Just adding internal CSS rule here since it won't be around long.
?>
<style type="text/css">
	.it-exchange-wizard-notice {
		background: #f3fce6 url('<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/open-e.png' );?>') .85em center no-repeat;
		border: 1px solid #D8E9C1;
		border-bottom-width: 2px;
		border-radius: 3px;
		font-size: 14px;
		line-height: 1.6;
		padding: 1em .75em 1em 4em;
		margin: 2.5em 1em 1em 0;
		color: #44654e;
	}
	.it-exchange-wizard-notice.no-screen-options {
		margin-top: 1em;
	}
	.it-exchange-wizard-notice a {
		background: #D8E9C1;
		color: #44654e;
		font-weight: bold;
		border: 1px solid #b7cc9b;
		border-bottom: 2px solid #b7cc9b;
		border-radius: 3px;
		padding: 6px 14px;
		-webkit-transition:  all .1s linear;
		-moz-transition:  all .1s linear 0s;
		text-decoration: none;
		position: absolute;
		margin: -8px 0 0 8px;
	}
	.it-exchange-wizard-notice a:hover, .it-exchange-wizard-notice button:hover {
		background: #f9fff0;
	}
	.it-exchange-wizard-notice button:active {
		background: #f3fce6;
	}
	.it-exchange-wizard-notice a:active {
		background: #f3fce6;
		border-bottom-width: 1px;
		margin-top: -7px;
		box-shadow: inset 0 2px 5px -3px rgba(0, 0, 0, 0.5);	
	}
	.it-exchange-wizard-notice button {
		background: #f3fce6;
		border: 1px solid #D8E9C1;
		border-bottom: 2px solid #D8E9C1;
		border-radius: 3px;
		color: #44654e;
		font-weight: bold;
		padding: 5px 10px;
		margin: -4px 0 0 0;
		float: right;
		-webkit-transition:  all .1s linear;
		-moz-transition:  all .1s linear 0s;
	}
	.it-exchange-wizard-notice a.dismiss {
		right: 22px;
	}
</style>
<div class="it-exchange-wizard-notice">
	<?php
	printf( __( 'iThemes Exchange default template parts have been updated. View %sour codex%s for more information.' ), '<a href="' . $codex_url. '">', '</a>' )
	?>
	<a class="dismiss" href="<?php esc_attr_e( $dismiss_url ); ?>">&times;</a>
</div>
<script type="text/javascript">
	if ( ! jQuery('#screen-options-wrap').length ) {
		jQuery('.it-exchange-wizard-notice').addClass('no-screen-options');
	}
</script>
