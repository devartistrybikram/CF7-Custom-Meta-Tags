(function () {
	if (typeof window === 'undefined' || typeof document === 'undefined' || typeof cf7cmtConfig === 'undefined') {
		return;
	}

	var cookiePrefix = cf7cmtConfig.cookiePrefix || 'cf7cmt_';
	var utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];

	function setCookie(name, value, days) {
		if (!name) {
			return;
		}

		var expires = new Date();
		expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
		document.cookie = name + '=' + encodeURIComponent(value || '') + '; expires=' + expires.toUTCString() + '; path=/; SameSite=Lax';
	}

	function getQueryParam(key) {
		var params = new URLSearchParams(window.location.search);
		return params.get(key) || '';
	}

	function createUuid() {
		if (window.crypto && typeof window.crypto.randomUUID === 'function') {
			return window.crypto.randomUUID();
		}

		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (character) {
			var random = Math.random() * 16 | 0;
			var value = character === 'x' ? random : ((random & 0x3) | 0x8);

			return value.toString(16);
		});
	}

	function persistPageContext() {
		if (cf7cmtConfig.currentUrl) {
			setCookie(cookiePrefix + 'page_url', cf7cmtConfig.currentUrl, cf7cmtConfig.utmCookieDays);
		}

		if (cf7cmtConfig.pageTitle) {
			setCookie(cookiePrefix + 'page_title', cf7cmtConfig.pageTitle, cf7cmtConfig.utmCookieDays);
		}

		if (document.referrer) {
			setCookie(cookiePrefix + 'referrer_url', document.referrer, cf7cmtConfig.utmCookieDays);
		} else if (cf7cmtConfig.referrerUrl) {
			setCookie(cookiePrefix + 'referrer_url', cf7cmtConfig.referrerUrl, cf7cmtConfig.utmCookieDays);
		}
	}

	function persistUtms() {
		utmKeys.forEach(function (key) {
			var value = getQueryParam(key);

			if (value) {
				setCookie(cookiePrefix + key, value, cf7cmtConfig.utmCookieDays);
			}
		});
	}

	function ensureSubmissionUuid() {
		if (document.cookie.indexOf(cookiePrefix + 'submission_uuid=') !== -1) {
			return;
		}

		setCookie(cookiePrefix + 'submission_uuid', createUuid(), cf7cmtConfig.submissionCookieDays);
	}

	function rotateSubmissionUuid(event) {
		if (!event || !event.detail || event.detail.status !== 'mail_sent') {
			return;
		}

		setCookie(cookiePrefix + 'submission_uuid', createUuid(), cf7cmtConfig.submissionCookieDays);
	}

	persistPageContext();
	persistUtms();
	ensureSubmissionUuid();

	document.addEventListener('wpcf7submit', rotateSubmissionUuid, false);
}());

