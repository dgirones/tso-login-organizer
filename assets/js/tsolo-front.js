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
			var postId = parseInt($btn.attr('data-post'), 10) || 0;
			if (!window.tsoloFrontConfig || !tsoloFrontConfig.ajaxUrl || !postId || $btn.prop('disabled')) {
				return;
			}
			$btn.prop('disabled', true);
			$.post(tsoloFrontConfig.ajaxUrl, {
				action: 'tsolo_toggle_favorite',
				_ajax_nonce: tsoloFrontConfig.nonce,
				post_id: postId
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
