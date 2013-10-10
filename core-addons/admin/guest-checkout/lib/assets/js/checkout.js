jQuery(function($){
	$('.it-exchange-login-requirement-guest-checkout').on('click', function(event){
		event.preventDefault();
		$('.checkout-purchase-requirement-login-options').addClass( 'it-exchange-hidden');
		$('.checkout-purchase-requirement-login').addClass('it-exchange-hidden');
		$('.checkout-purchase-requirement-registration').addClass('it-exchange-hidden');
		$('.checkout-purchase-requirement-guest-checkout').removeClass('it-exchange-hidden');
	});

	$('.it-exchange-login-requirement-login, .it-exchange-login-requirement-registration').on('click', function(event){
		$('.checkout-purchase-requirement-guest-checkout').addClass('it-exchange-hidden');
	});
});
