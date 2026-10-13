(function ($) {
	'use strict';

	function handleAjaxForm($form) {
		$form.on('submit', function (e) {
			if (!$form.attr('data-ajax')) {
				return;
			}
			e.preventDefault();
			var $alerts = $form.closest('.tsolo').find('.tsolo-alerts');
			if (!$alerts.length) {
				$alerts = $('<div class="tsolo-alerts" />').prependTo($form.closest('.tsolo'));
			}
			$.ajax({
				url: $form.attr('action'),
				method: 'POST',
				data: $form.serialize() + '&ajax=1',
				dataType: 'json'
			}).done(function (res) {
				if (res && res.success) {
					if (res.data && res.data.redirect) {
						window.location = res.data.redirect;
						return;
					}
					if (res.data && res.data.notice) {
						$alerts.html(res.data.notice);
					}
					return;
				}
				if (res && res.data && res.data.errors) {
					$alerts.html(res.data.errors);
					return;
				}
				$alerts.html('<div class="tsolo-alert">' + (window.tsoloFrontConfig && tsoloFrontConfig.i18n ? tsoloFrontConfig.i18n.unexpected : 'Error') + '</div>');
			}).fail(function () {
				$alerts.html('<div class="tsolo-alert">' + (window.tsoloFrontConfig && tsoloFrontConfig.i18n ? tsoloFrontConfig.i18n.unexpected : 'Error') + '</div>');
			});
		});
	}

	$(function () {
		$('form.tsolo-form[data-ajax]').each(function () {
			handleAjaxForm($(this));
		});

		$(document).on('click', '.tsolo-favorite-toggle', function () {
			var $btn = $(this);
			if (!window.tsoloFrontConfig || !tsoloFrontConfig.ajaxUrl) {
				return;
			}
			$.post(tsoloFrontConfig.ajaxUrl, {
				action: 'tsolo_toggle_favorite',
				_ajax_nonce: tsoloFrontConfig.nonce,
				post_id: $btn.data('post')
			}).done(function (res) {
				if (res && res.success && res.data && res.data.label) {
					$btn.text(res.data.label);
				}
			});
		});
	});
})(jQuery);
