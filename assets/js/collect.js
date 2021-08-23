jQuery( function( $ ) {
	console.log('Here Here');

	let collect_submit = false;

	$( '#wc-collect-form' ).hide();

	wcCollectFormHandler();

	jQuery( '#collect-payment-button' ).click( function() {
		return wcCollectFormHandler();
	} );

	jQuery( '#collect_form form#order_review' ).submit( function() {
		return wcCollectFormHandler();
	} );


	function wcCollectFormHandler() {

		$( '#wc-collect-form' ).hide();

		if ( collect_submit ) {
			collect_submit = false;
			return true;
		}

		let $form = $( 'form#payment-form, form#order_review' );
		collect_txnref = $form.find( 'input.collect_txnref' );
		collect_txnref.val( '' );

		let amount = Number( wc_collect_params.amount );

		let callback = function( response ) {
			$form.append( '<input type="hidden" class="collect_txnref" name="collect_txnref" value="' + response.trxref + '"/>' );
			collect_submit = true;

			$form.submit();

			$( 'body' ).block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				},
				css: {
					cursor: "wait"
				}
			} );
		};

		const CollectCheckoutService = window.CollectCheckout;

		const checkout = new CollectCheckoutService({
			email: wc_collect_params.email,
			firstName: wc_collect_params.first_name,
			lastName: wc_collect_params.last_name,
			reference: wc_collect_params.txnref,
			amount: amount,
			callback_url: callback,
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


		return false;

	}

} );