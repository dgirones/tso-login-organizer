(function ($) {
	'use strict';

	function tsoloSwitchTab(tab) {
		var $wrap = $('.tsolo-admin-wrap');
		if (!$wrap.length || !tab) {
			return false;
		}
		var $panel = $wrap.find('.tsolo-tab-panel[data-tsolo-panel="' + tab + '"]');
		if (!$panel.length) {
			return false;
		}

		$wrap.find('.tsolo-nav-tabs .nav-tab').removeClass('nav-tab-active');
		$wrap.find('.tsolo-nav-tabs .nav-tab[data-tsolo-tab="' + tab + '"]').addClass('nav-tab-active');

		$wrap.find('.tsolo-tab-panel').each(function () {
			var $p = $(this);
			var active = $p.is($panel);
			$p.toggleClass('is-active', active);
			if (active) {
				$p.removeAttr('hidden').css('display', '');
			} else {
				$p.attr('hidden', 'hidden').css('display', 'none');
			}
		});

		if (window.history && window.history.replaceState) {
			try {
				var url = new URL(window.location.href);
				url.searchParams.set('page', 'tso-login-organizer');
				url.searchParams.set('tab', tab);
				window.history.replaceState({}, '', url.toString());
			} catch (e) {
				// Older browsers: ignore URL sync.
			}
		}
		return true;
	}

	$(function () {
		$('.tsolo-collide-module').on('change', function () {
			if (!this.checked) {
				return;
			}
			if (!window.tsoloAdminConfig || !tsoloAdminConfig.collisions || !tsoloAdminConfig.collisions.length) {
				return;
			}
			window.alert(tsoloAdminConfig.i18n.collisionWarn + '\n\n' + tsoloAdminConfig.collisions.join(', '));
		});

		$('.tsolo-slugs-table').on('input', 'input[data-tsolo-slug-key]', function () {
			var $input = $(this);
			var raw = String($input.val() || '').replace(/^\/+|\/+$/g, '');
			var preview = raw ? '/' + raw + '/' : '/';
			var $link = $input.closest('tr').find('.tsolo-slug-preview a');
			var origin = window.location.origin || '';
			$link.find('code').text(preview);
			if ($link.length && origin) {
				$link.attr('href', origin + preview);
			}
		});

		$('#tsolo-check-permalinks').on('click', function () {
			if (!window.tsoloAdminConfig || !tsoloAdminConfig.ajaxUrl) {
				return;
			}
			var $btn = $(this);
			var $result = $('#tsolo-permalink-result');
			$btn.prop('disabled', true);
			$.post(tsoloAdminConfig.ajaxUrl, {
				action: 'tsolo_check_permalinks',
				_ajax_nonce: tsoloAdminConfig.nonce
			})
				.done(function (response) {
					if (!response || !response.success || !response.data) {
						return;
					}
					var data = response.data;
					var html = '<p><strong>' + (data.ok ? '✓' : '!') + '</strong> ' + $('<div/>').text(data.message || '').html() + '</p>';
					if (data.fix_steps && data.fix_steps.length) {
						html += '<ol>';
						data.fix_steps.forEach(function (step) {
							html += '<li>' + $('<div/>').text(step).html() + '</li>';
						});
						html += '</ol>';
					}
					if (data.settings_url) {
						html +=
							'<p><a class="button button-small" href="' +
							data.settings_url +
							'" target="_blank" rel="noopener noreferrer">' +
							(tsoloAdminConfig.i18n.permalinkSettings || 'Permalinks') +
							'</a></p>';
					}
					$result.removeClass('is-ok').removeAttr('hidden').html(html);
					if (data.ok) {
						$result.addClass('is-ok');
					}
					$('.tsolo-permalink-inline')
						.text(data.message || '')
						.removeClass('is-ok is-warn')
						.addClass(data.ok ? 'is-ok' : 'is-warn');
				})
				.always(function () {
					$btn.prop('disabled', false);
				});
		});

		$('.tsolo-admin-wrap').on('click', 'a[data-tsolo-tab]', function (e) {
			var tab = $(this).attr('data-tsolo-tab');
			if (!tab) {
				return;
			}
			if (tsoloSwitchTab(tab)) {
				e.preventDefault();
			}
		});
	});
})(jQuery);
