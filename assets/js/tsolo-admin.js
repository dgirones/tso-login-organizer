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

		/* After a tall tab, keep scroll near the tabs so a short panel does not leave a blank gap below. */
		try {
			var anchor = $wrap.find('.tsolo-nav-primary').get(0) || $wrap.get(0);
			if (anchor && typeof anchor.scrollIntoView === 'function') {
				anchor.scrollIntoView({ block: 'start', behavior: 'auto' });
			}
		} catch (scrollErr) {
			// Ignore.
		}

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
			var $cell = $input.closest('tr').find('.tsolo-slug-preview');
			var $link = $cell.find('a');
			var $code = $cell.find('code');
			var cfg = window.tsoloAdminConfig || {};
			var home = String(cfg.homeUrl || window.location.origin || '').replace(/\/+$/, '');
			var full = home ? home + preview : preview;
			$code.text(full);
			if ($link.length && home) {
				$link.attr('href', full);
			}
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

		$('.tsolo-admin-wrap').on('click', '.tsolo-check-permalinks', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var cfg = window.tsoloAdminConfig || {};
			if (!cfg.ajaxUrl || !cfg.nonce || $btn.prop('disabled')) {
				return;
			}
			var label = $btn.text();
			$btn.prop('disabled', true).text((cfg.i18n && cfg.i18n.permalinkBusy) || 'Checking…');
			$.ajax({
				url: cfg.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'tsolo_check_permalinks',
					_ajax_nonce: cfg.nonce
				}
			}).done(function (res) {
				if (!res || !res.success || !res.data || !res.data.html) {
					window.alert((cfg.i18n && cfg.i18n.permalinkFailed) || 'Failed');
					return;
				}
				var $box = $('.tsolo-admin-wrap .tsolo-permalink-status').first();
				if ($box.length) {
					$box.replaceWith(res.data.html);
				}
				if (res.data.message) {
					var noticeClass = res.data.type === 'success' ? 'notice-success' : 'notice-warning';
					var $flash = $('<div class="notice inline ' + noticeClass + ' is-dismissible tsolo-ajax-flash tsolo-admin-flash" data-tsoan-keep="1"><p></p></div>');
					$flash.find('p').text(res.data.message);
					$('.tsolo-admin-wrap .tsolo-ajax-flash').remove();
					var $anchor = $('.tsolo-admin-wrap .tsolo-nav-modules').last();
					if (!$anchor.length) {
						$anchor = $('.tsolo-admin-wrap .tsolo-nav-primary').last();
					}
					if ($anchor.length) {
						$anchor.after($flash);
					} else {
						$('.tsolo-admin-wrap .tsolo-nav-top').after($flash);
					}
				}
			}).fail(function () {
				window.alert((cfg.i18n && cfg.i18n.permalinkFailed) || 'Failed');
			}).always(function () {
				var $fresh = $('.tsolo-admin-wrap .tsolo-check-permalinks');
				if ($fresh.length) {
					$fresh.prop('disabled', false);
				} else {
					$btn.prop('disabled', false).text(label);
				}
			});
		});
	});

	var tsoloTheme = {
		_autoTimer: null,
		_dayMs: 86400000,
		_j1970: 2440588,
		_j2000: 2451545,
		_rad: Math.PI / 180,
		_e: (Math.PI / 180) * 23.4397,
		readPref: function () {
			var theme = '';
			try { theme = localStorage.getItem('tsolo_ui_theme') || ''; } catch (e) { theme = ''; }
			if (theme === 'day' || theme === 'night' || theme === 'auto') {
				return theme;
			}
			return 'auto';
		},
		getCoords: function () {
			var cfg = window.tsoloAdminConfig || {};
			var wpLat = parseFloat(cfg.lat);
			var wpLng = parseFloat(cfg.lng);
			if (!isNaN(wpLat) && !isNaN(wpLng)) {
				return { lat: wpLat, lng: wpLng };
			}
			return { lat: 41.39, lng: 2.17 };
		},
		toJulian: function (date) { return date.valueOf() / this._dayMs - 0.5 + this._j1970; },
		fromJulian: function (j) { return new Date((j + 0.5 - this._j1970) * this._dayMs); },
		toDays: function (date) { return this.toJulian(date) - this._j2000; },
		rightAscension: function (l, b) {
			return Math.atan2(Math.sin(l) * Math.cos(this._e) - Math.tan(b) * Math.sin(this._e), Math.cos(l));
		},
		declination: function (l, b) {
			return Math.asin(Math.sin(b) * Math.cos(this._e) + Math.cos(b) * Math.sin(this._e) * Math.sin(l));
		},
		solarMeanAnomaly: function (d) { return this._rad * (357.5291 + 0.98560028 * d); },
		eclipticLongitude: function (M) {
			var C = this._rad * (1.9148 * Math.sin(M) + 0.02 * Math.sin(2 * M) + 0.0003 * Math.sin(3 * M));
			return M + C + this._rad * 102.9372 + Math.PI;
		},
		sunCoords: function (d) {
			var M = this.solarMeanAnomaly(d);
			var L = this.eclipticLongitude(M);
			return { dec: this.declination(L, 0), ra: this.rightAscension(L, 0) };
		},
		julianCycle: function (d, lw) { return Math.round(d - 0.0009 - lw / (2 * Math.PI)); },
		approxTransit: function (Ht, lw, n) { return 0.0009 + (Ht + lw) / (2 * Math.PI) + n; },
		solarTransitJ: function (ds, M, L) {
			return this._j2000 + ds + 0.0053 * Math.sin(M) - 0.0069 * Math.sin(2 * L);
		},
		hourAngle: function (h, phi, d) {
			return Math.acos((Math.sin(h) - Math.sin(phi) * Math.sin(d)) / (Math.cos(phi) * Math.cos(d)));
		},
		getSetJ: function (h, lw, phi, dec, n, M, L) {
			var w = this.hourAngle(h, phi, dec);
			var a = this.approxTransit(w, lw, n);
			return this.solarTransitJ(a, M, L);
		},
		getSunTimes: function (date, lat, lng) {
			try {
				var lw = this._rad * -lng;
				var phi = this._rad * lat;
				var d = this.toDays(date);
				var n = this.julianCycle(d, lw);
				var ds = this.approxTransit(0, lw, n);
				var M = this.solarMeanAnomaly(ds);
				var L = this.eclipticLongitude(M);
				var dec = this.sunCoords(ds).dec;
				var Jnoon = this.solarTransitJ(ds, M, L);
				var Jset = this.getSetJ(-0.833 * this._rad, lw, phi, dec, n, M, L);
				var Jrise = Jnoon - (Jset - Jnoon);
				var sunrise = this.fromJulian(Jrise);
				var sunset = this.fromJulian(Jset);
				if (isNaN(sunrise.getTime()) || isNaN(sunset.getTime())) {
					return null;
				}
				return { sunrise: sunrise, sunset: sunset };
			} catch (e) {
				return null;
			}
		},
		formatClock: function (date) {
			try {
				return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
			} catch (e) {
				return '';
			}
		},
		fromSolar: function () {
			var coords = this.getCoords();
			var now = new Date();
			var times = this.getSunTimes(now, coords.lat, coords.lng);
			if (!times) {
				return (now.getHours() >= 7 && now.getHours() < 20) ? 'day' : 'night';
			}
			return (now >= times.sunrise && now < times.sunset) ? 'day' : 'night';
		},
		resolve: function (preference) {
			if (preference === 'day' || preference === 'night') {
				return preference;
			}
			return this.fromSolar();
		},
		uiText: function (key, fallback) {
			var cfg = window.tsoloAdminConfig || {};
			var i18n = cfg.themeI18n || {};
			return i18n[key] || fallback;
		},
		apply: function (preference) {
			var self = this;
			if (preference !== 'day' && preference !== 'night' && preference !== 'auto') {
				preference = 'auto';
			}
			try { localStorage.setItem('tsolo_ui_theme', preference); } catch (e) { /* ignore */ }
			var resolved = this.resolve(preference);
			if (preference === 'auto') {
				try {
					localStorage.setItem('tsolo_ui_theme_auto', JSON.stringify({ t: resolved, exp: Date.now() + 900000 }));
				} catch (e3) { /* ignore */ }
			}
			var wrap = document.getElementById('tsolo-wrap');
			if (wrap) {
				wrap.setAttribute('data-theme', resolved);
				wrap.setAttribute('data-theme-pref', preference);
			}
			document.documentElement.setAttribute('data-tsolo-theme', resolved);
			document.documentElement.setAttribute('data-tsolo-theme-pref', preference);
			if (document.body) {
				document.body.setAttribute('data-tsolo-theme', resolved);
			}
			var btn = document.getElementById('tsolo-theme-toggle');
			if (btn) {
				var labelKey = preference === 'auto' ? 'themeAuto' : (preference === 'day' ? 'themeDay' : 'themeNight');
				var labelFallback = preference === 'auto' ? 'Auto mode' : (preference === 'day' ? 'Day mode' : 'Night mode');
				var icon = preference === 'auto' ? '🌓' : (resolved === 'night' ? '🌙' : '☀️');
				var label = this.uiText(labelKey, labelFallback);
				var title = label;
				if (preference === 'auto') {
					title = label + ' — ' + this.uiText('themeAutoHint', 'Follows sunrise and sunset (changes with the seasons)');
					var coords = this.getCoords();
					var times = this.getSunTimes(new Date(), coords.lat, coords.lng);
					if (times) {
						title += ' · ' + this.formatClock(times.sunrise) + '–' + this.formatClock(times.sunset);
					}
				}
				btn.setAttribute('aria-pressed', preference === 'auto' ? 'mixed' : (resolved === 'night' ? 'true' : 'false'));
				btn.setAttribute('title', title);
				var iconEl = btn.querySelector('.tsolo-theme-icon');
				var labelEl = btn.querySelector('.tsolo-theme-label');
				if (iconEl) { iconEl.textContent = icon; }
				if (labelEl) { labelEl.textContent = label; }
			}
			if (this._autoTimer) {
				clearInterval(this._autoTimer);
				this._autoTimer = null;
			}
			if (preference === 'auto') {
				this._autoTimer = setInterval(function () {
					if (self.readPref() !== 'auto') {
						return;
					}
					var next = self.fromSolar();
					var current = document.documentElement.getAttribute('data-tsolo-theme');
					if (current !== next) {
						self.apply('auto');
					}
				}, 60000);
			}
		},
		next: function (current) {
			if (current === 'auto') { return 'day'; }
			if (current === 'day') { return 'night'; }
			return 'auto';
		},
		init: function () {
			var self = this;
			this.apply(this.readPref());
			var btn = document.getElementById('tsolo-theme-toggle');
			if (btn && !btn.getAttribute('data-tsolo-theme-bound')) {
				btn.setAttribute('data-tsolo-theme-bound', '1');
				btn.addEventListener('click', function () {
					self.apply(self.next(self.readPref()));
				});
			}
		}
	};
	tsoloTheme.init();
})(jQuery);
