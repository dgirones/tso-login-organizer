/**
 * reCAPTCHA v3 token injection before form submit.
 */
(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
	}

	ready(function () {
		var cfg = window.tsoloCaptchaConfig;
		if (!cfg || cfg.provider !== 'v3' || !cfg.siteKey || typeof window.grecaptcha === 'undefined') {
			return;
		}

		function bindForm(form) {
			if (!form || form.getAttribute('data-tsolo-captcha-bound')) {
				return;
			}
			form.setAttribute('data-tsolo-captcha-bound', '1');
			form.addEventListener('submit', function (ev) {
				var tokenInput = form.querySelector('.tsolo-recaptcha-v3-token, input[name="g-recaptcha-response"]');
				if (!tokenInput) {
					return;
				}
				if (tokenInput.value) {
					return;
				}
				ev.preventDefault();
				window.grecaptcha.ready(function () {
					window.grecaptcha
						.execute(cfg.siteKey, { action: cfg.action || 'tsolo' })
						.then(function (token) {
							tokenInput.value = token;
							if (typeof form.requestSubmit === 'function') {
								form.requestSubmit();
							} else {
								form.submit();
							}
						})
						.catch(function () {
							tokenInput.value = '';
						});
				});
			});
		}

		document.querySelectorAll('form.tsolo-form, form#loginform, form#registerform, form#lostpasswordform').forEach(bindForm);
	});
})();
