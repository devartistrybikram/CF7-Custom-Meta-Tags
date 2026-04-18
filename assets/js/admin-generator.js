(function () {
	if (typeof document === 'undefined' || window.cf7cmtGeneratorBound) {
		return;
	}

	window.cf7cmtGeneratorBound = true;

	var lastFocusedField = null;

	function isTextArea(element) {
		return element && element.tagName === 'TEXTAREA';
	}

	function isCf7EditorField(element) {
		if (!isTextArea(element)) {
			return false;
		}

		return Boolean(
			element.closest('.contact-form-editor-box')
			|| element.closest('.wpcf7-editor-panel')
			|| element.name === 'wpcf7-form'
			|| element.id === 'wpcf7-form'
		);
	}

	document.addEventListener('focusin', function (event) {
		if (!isCf7EditorField(event.target)) {
			return;
		}

		lastFocusedField = event.target;
	});

	function getTargetField() {
		if (isCf7EditorField(lastFocusedField) && document.body.contains(lastFocusedField)) {
			return lastFocusedField;
		}

		return document.querySelector('textarea[name="wpcf7-form"]')
			|| document.querySelector('textarea#wpcf7-form')
			|| document.querySelector('.contact-form-editor-box textarea')
			|| document.querySelector('.wpcf7-editor-panel textarea');
	}

	function insertAtCursor(field, text) {
		if (!field || !text) {
			return;
		}

		field.focus();

		var start = typeof field.selectionStart === 'number' ? field.selectionStart : field.value.length;
		var end = typeof field.selectionEnd === 'number' ? field.selectionEnd : field.value.length;
		var before = field.value.slice(0, start);
		var after = field.value.slice(end);
		var prefix = start > 0 && !/\s$/.test(before) ? ' ' : '';
		var suffix = after.length > 0 && !/^\s/.test(after) ? ' ' : '';
		var insertValue = prefix + text + suffix;

		field.value = before + insertValue + after;

		var cursor = before.length + insertValue.length;

		if (typeof field.setSelectionRange === 'function') {
			field.setSelectionRange(cursor, cursor);
		}

		field.dispatchEvent(new Event('input', { bubbles: true }));
		field.dispatchEvent(new Event('change', { bubbles: true }));
	}

	document.addEventListener('click', function (event) {
		var button = event.target.closest('.cf7cmt-insert-tag');

		if (!button) {
			return;
		}

		event.preventDefault();

		var tag = button.getAttribute('data-tag') || '';
		var target = getTargetField();

		insertAtCursor(target, tag);
	});
}());
