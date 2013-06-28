// JavaScript Document
function it_exchange_stripe_processing_payment_popup() {
	var style = "position: fixed; " +
				"display: none; " +
				"z-index: 1000; " +
				"top: 50%; " +
				"left: 50%; " +
				"background-color: #E8E8E8; " +
				"border: 1px solid #555; " +
				"padding: 15px; " +
				"width: 500px; " +
				"min-height: 80px; " +
				"margin-left: -250px; " + 
				"margin-top: -150px;" +
				"text-align: center;" +
				"vertical-align: middle;";
	jQuery('body').append("<div id='results' style='" + style + "'></div>");
	jQuery('#results').html("<p>" + stripeAddonL10n.processing_payment_text + "</p>" +
							"<p><img src='/wp-includes/js/thickbox/loadingAnimation.gif' /></p>");
	jQuery('#results').show();
}