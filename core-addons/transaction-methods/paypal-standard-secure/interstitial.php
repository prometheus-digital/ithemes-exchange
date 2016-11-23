<html>
<head>
	<title><?php bloginfo( 'name' ); ?></title>
	<style>
		html {font-family: sans-serif;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background: #efefef;}
		body {margin: 0;background: #efefef;padding: 1em;}

		a {background-color: transparent;-webkit-text-decoration-skip: objects}
		a:active,
		a:hover {outline-width: 0}

		h1 {font-size: 2em;margin: .67em 0}

		button,
		input {font: inherit;margin: 0;overflow: visible}
		button {text-transform: none}

		[type=submit],
		button,
		html [type=button] {-webkit-appearance: button}

		[type=button]::-moz-focus-inner,
		[type=reset]::-moz-focus-inner,
		[type=submit]::-moz-focus-inner,
		button::-moz-focus-inner {border-style: none;padding: 0}

		[type=button]:-moz-focusring,
		[type=reset]:-moz-focusring,
		[type=submit]:-moz-focusring,
		button:-moz-focusring {outline: ButtonText dotted 1px}

		.wrapper {width: 100%;max-width: 400px;background: #fff;padding: 2em;margin: 0 auto 1em auto;box-shadow: 4px 4px 0px #ddd;box-sizing: border-box;}
		h1 {margin: 0 0 0 0;color:#222}
		form {margin: 0;}
		input[type="submit"] {border: none;background: #444;color: #fff;padding: 10px 15px;margin: 0;}
		input[type="submit"]:hover,
		input[type="submit"]:focus {background: #555;cursor:pointer;}
	</style>
</head>
<body>
<div class="wrapper">
	<h1><?php bloginfo( 'name' ); ?></h1>
	<p><?php _e( 'You are being redirected to PayPal to complete your transaction.', 'it-l10n-ithemes-exchange' ); ?></p>
	<form id="payment" action="<?php echo esc_url( $paypal_payment_url ); ?>" method="post">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="encrypted" value="<?php echo $encrypted; ?>">
		<input type="submit" value="<?php esc_attr_e( 'Continue', 'it-l10n-ithemes-exchange' ); ?>" autofocus>
	</form>
</div>
<script type="text/javascript">
	document.getElementById( "payment" ).submit();
</script>
</body>
</html>
