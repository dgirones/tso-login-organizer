(function () {
	var KEY = 'tsolo_ui_theme';
	function readPref() {
		var p = '';
		try {
			p = localStorage.getItem(KEY) || '';
		} catch (e) {
			p = '';
		}
		if (p === 'day' || p === 'night' || p === 'auto') {
			return p;
		}
		return 'auto';
	}
	function resolve(pref) {
		if (pref === 'day' || pref === 'night') {
			return pref;
		}
		try {
			var c = JSON.parse(localStorage.getItem(KEY + '_auto') || 'null');
			if (c && (c.t === 'day' || c.t === 'night') && c.exp > Date.now()) {
				return c.t;
			}
		} catch (e0) {
			/* ignore */
		}
		var h = (new Date()).getHours();
		return (h >= 7 && h < 20) ? 'day' : 'night';
	}
	var pref = readPref();
	var theme = resolve(pref);
	document.documentElement.setAttribute('data-tsolo-theme', theme);
	document.documentElement.setAttribute('data-tsolo-theme-pref', pref);
})();
