/* globals credImport, ajaxurl */
(function ($) {
	const opts = $.extend({
			nonce: '',
			payMethod: '',
			midFrom: '',
			merchantKeyFrom: ''
		}, credImport);

	$('#import-mid').on('click', function (e) {
		let that = $(this),
			mid = $('#woocommerce_shpg_nicepay_' + opts.payMethod + '_mid');
		e.preventDefault();
		$.ajax(ajaxurl, {
			method: 'POST',
			data: {
				action: 'shpg_request_import_mid',
				from: opts.midFrom,
				_wpnonce: opts.nonce,
			},
			success: function (response) {
				if (response.success) {
					that.remove();
					mid.val(response.data.mid);
				}
			}
		});
	});

	$('#import-merchant_key').on('click', function (e) {
		let that = $(this),
			merchantKey = $('#woocommerce_shpg_nicepay_' + opts.payMethod + '_merchant_key');
		e.preventDefault();
		$.ajax(ajaxurl, {
			method: 'POST',
			data: {
				action: 'shpg_request_import_merchant_key',
				from: opts.merchantKeyFrom,
				_wpnonce: opts.nonce,
			},
			success: function (response) {
				if (response.success) {
					that.remove();
					merchantKey.val(response.data.merchantKey);
				}
			}
		});
	});
})(jQuery);
