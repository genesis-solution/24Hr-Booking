(function ($) {
	$(document).ready(function () {
		$('form#update_api_key').on('submit', function (event) {
			event.stopPropagation();
			event.preventDefault();

			var submited_form = this;

			$(submited_form).block({
				message: '<span class="spinner is-active">',
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				},
				css: {
					padding: 0,
					margin: 0,
					width: 0,
					top: '50%',
					left: '50%',
					textAlign: 'center',
					border: '0px',
					backgroundColor: 'none',
					cursor: 'wait'
				},

			});

			$.ajax({
				url: mphb_api_keys.ajax_url,
				type: 'POST',
				data: {
					action: 'update_api_key',
					update_api_key_nonce: mphb_api_keys.update_api_key_nonce,
					key_id: $('#key_id', self.el).val(),
					description: $('#key_description', self.el).val(),
					user: $('#key_user', self.el).val(),
					permissions: $('#key_permissions', self.el).val()
				},
				dataType: 'json',
				success: function (response, status, jqXHR) {
					$('.mphb-api-message', self.el).remove();

					if (response.success) {
						var data = response.data;

						$('h2, h3', self.el).first().append('<div class="mphb-api-message updated"><p>' + data.message + '</p></div>');

						if (0 < data.consumer_key.length && 0 < data.consumer_secret.length) {
							$('#api-keys-options', self.el).remove();
							$('p.submit', self.el).empty().append(data.revoke_url);

							var template = wp.template('api-keys-template');

							$('p.submit', self.el).before(template({
								consumer_key: data.consumer_key,
								consumer_secret: data.consumer_secret
							}));
							$('#keys-qrcode').qrcode({
								text: data.rest_endpoint + '|' + data.consumer_key + '|' + data.consumer_secret,
								width: 120,
								height: 120
							});
							$(document).on('click', '.copy-key', function (e) {
								if (document.queryCommandSupported('copy')) {
									copyToClipboard('#key_consumer_key');
									showTooltip(this, 'ok');
								} else {
									showTooltip(this, 'error', mphb_api_keys.clipboard_failed);
								}
							});
							$(document).on('click', '.copy-secret', function (e) {
								if (document.queryCommandSupported('copy')) {
									copyToClipboard('#key_consumer_secret');
									showTooltip(this, 'ok');
								} else {
									showTooltip(this, 'error', mphb_api_keys.clipboard_failed);
								}
							});
						} else {
							$('#key_description', self.el).val(data.description);
							$('#key_user', self.el).val(data.user_id);
							$('#key_permissions', self.el).val(data.permissions);
						}
					} else {
						$('h2, h3', self.el)
							.first()
							.append('<div class="mphb-api-message error"><p>' + response.data.message + '</p></div>');
					}
				},
				error: function (jqXHR, status, errorThrown) {
					console.log('Error: ' + status, jqXHR);
				},
				complete: function () {
					$(submited_form).unblock();
				}
			});

		});
	});

	let tooltipElem;
	let tooltipTimeout;

	function showTooltip(elem, type = '', message = false) {
		var tooltipClasses = 'tooltip';
		if (type) {
			tooltipClasses = tooltipClasses + ' tooltip-' + type;
		}

		$('.tooltip').removeClass(tooltipClasses);
		clearTimeout(tooltipTimeout);

		$elem = $(elem);

		if (message) {
			$elem.data('tooltip', message);
		}

		$elem.toggleClass(tooltipClasses);

		tooltipTimeout = setTimeout(function () {
			$elem.toggleClass(tooltipClasses);

		}, 3000);
	}

	function copyToClipboard(element) {
		var $temp = $("<input>");
		$("body").append($temp);
		$temp.val($(element).val()).select();
		document.execCommand("copy");
		$temp.remove();
	}
})(jQuery);
