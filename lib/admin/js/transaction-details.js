jQuery( document ).ready( function( $ ) {
	$('.handlediv, .hndle').remove();

	// do transaction status update
	$('#it-exchange-update-transaction-status').on('change', function() {
		var nonce         = $('#it-exchange-update-transaction-nonce').val();
		var currentStatus = $('#it-exchange-update-transaction-current-status').val();
		var newStatus     = $('#it-exchange-update-transaction-status').find(":selected").val();
		var txnID         = $('#it-exchange-update-transaction-id').val();

		var data = {
			'action': 'it-exchange-update-transaction-status',
			'it-exchange-nonce': nonce,
			'it-exchange-current-status': currentStatus,
			'it-exchange-new-status': newStatus,
			'it-exchange-transaction-id': txnID
		}
		$.post( ajaxurl, data, function(response) {
			console.log(response);
			if ( 'failed' == response )
				$('#it-exchange-update-transaction-status-failed').show().delay(200).fadeOut(200);
			else
				$('#it-exchange-update-transaction-status-success').show().delay(1000).fadeOut(800);
		}).fail( function(){
				$('#it-exchange-update-transaction-status-failed').show().delay(1000).fadeOut(800);
		});
	});
});
