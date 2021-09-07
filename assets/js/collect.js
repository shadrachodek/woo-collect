jQuery( function( $ ) {

	wcCollectFormHandler();

	function wcCollectFormHandler() {

		$( '#wc-collect-form' ).hide();

		let amount = Number( wc_collect_params.amount );

		const CollectCheckoutService = window.CollectCheckout;

		const checkout = new CollectCheckoutService({
			email: wc_collect_params.email,
			firstName: wc_collect_params.first_name,
			lastName: wc_collect_params.last_name,
			reference: wc_collect_params.txnref,
			amount: amount,
			callback_url: wc_collect_params.callback,
			currency: wc_collect_params.currency,
			publicKey: wc_collect_params.key,
			onSuccess(e) {
				window.location.href = wc_collect_params.callback + '?short_code=' + e.data.code + '&reference='+ e.data.reference;
			},
			onClose(e) {
				$( '#wc-collect-form' ).show();
				$( this.el ).unblock();
			},
		})
		checkout.setup()
		checkout.open()
	}

} );