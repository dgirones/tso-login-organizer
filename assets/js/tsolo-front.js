(function ($) {
	'use strict';

	function handleAjaxForm($form) {
		$form.on('submit', function (e) {
			if (!$form.attr('data-ajax')) {
				return;
			}
			// reCAPTCHA v3: the captcha script fetches the token and re-submits; do not send an empty one.
			var $v3 = $form.find('.tsolo-recaptcha-v3-token');
			if ($v3.length && !$v3.val()) {
				return;
			}
			e.preventDefault();
			var resetCaptcha = function () {
				$v3.val('');
				try {
					if (window.grecaptcha && $form.find('.g-recaptcha').length) { window.grecaptcha.reset(); }
					if (window.turnstile && $form.find('.cf-turnstile').length) { window.turnstile.reset(); }
				} catch (err) { /* ignore */ }
			};
			var $alerts = $form.closest('.tsolo').find('.tsolo-alerts');
			if (!$alerts.length) {
				$alerts = $('<div class="tsolo-alerts" />').prependTo($form.closest('.tsolo'));
			}
			$.ajax({
				url: $form.attr('action'),
				method: 'POST',
				data: $form.serialize() + '&ajax=1',
				dataType: 'json'
			}).always(function (res) {
				if (!(res && res.success && res.data && res.data.redirect)) {
					resetCaptcha();
				}
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
			var postId = parseInt($btn.attr('data-post'), 10) || 0;
			if (!window.tsoloFrontConfig || !tsoloFrontConfig.ajaxUrl || !postId || $btn.prop('disabled')) {
				return;
			}
			$btn.prop('disabled', true);
			var send = function () {
				return $.post(tsoloFrontConfig.ajaxUrl, {
					action: 'tsolo_toggle_favorite',
					_ajax_nonce: tsoloFrontConfig.nonce,
					post_id: postId
				});
			};
			// A cached page can carry an expired nonce: fetch a fresh one once and retry.
			send().then(null, function (xhr) {
				if (!xhr || xhr.status !== 403) {
					return $.Deferred().reject(xhr).promise();
				}
				return $.post(tsoloFrontConfig.ajaxUrl, { action: 'tsolo_refresh_nonce' }).then(function (r) {
					if (r && r.success && r.data && r.data.nonce) {
						tsoloFrontConfig.nonce = r.data.nonce;
						return send();
					}
					return $.Deferred().reject().promise();
				});
			}).done(function (res) {
				if (res && res.success && res.data && res.data.label) {
					var active = !!res.data.active;
					$btn.text(res.data.label);
					$btn.attr('aria-pressed', active ? 'true' : 'false');
					var $item = $btn.closest('.tsolo-favorite-item');
					if ($item.length && !active) {
						$item.remove();
						var $list = $('.tsolo-favorites-list');
						if ($list.length && !$list.children().length) {
							$list.replaceWith('<p class="tsolo-favorites-empty">' + (tsoloFrontConfig.i18n && tsoloFrontConfig.i18n.noFavorites ? tsoloFrontConfig.i18n.noFavorites : 'No favorites yet.') + '</p>');
						}
					}
				}
			}).always(function () {
				$btn.prop('disabled', false);
			});
		});
	});
})(jQuery);
