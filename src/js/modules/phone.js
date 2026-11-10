// Ukrainian phone mask: +380 (67) 123 45 67.
// Accepts typing and pasting in any common form (0671234567, +38 067…, 380…),
// keeps the caret after the same digit while editing in the middle, and marks
// an incomplete number invalid when the field is left.

const PREFIX = '+380 ';
const DIGITS = 12; // 380 + 9

function normalize(raw) {
	let digits = raw.replace(/\D/g, '');
	if (digits.startsWith('380')) {
		// Already international.
	} else if (digits.startsWith('80')) {
		digits = `3${digits}`;
	} else if (digits.startsWith('0')) {
		digits = `38${digits}`;
	} else if (digits) {
		digits = `380${digits}`;
	}
	// Operator codes never start with 0: "+380 0…" means the 0 of "067…".
	if (digits.startsWith('3800')) {
		digits = `380${digits.slice(4)}`;
	}
	return digits.slice(0, DIGITS);
}

// Digits of what is in the field. Anything after an existing "+380" is read
// as a number of its own, so typing "+38 067…" or "0671…" over the prefilled
// prefix works. Unambiguous: Ukrainian operator codes never start with 0, 38 or 80.
function digitsOf(text) {
	// The prefix can end up anywhere if a digit lands before it ("0+380 ").
	const at = text.indexOf('+380');
	if (at === -1) {
		return normalize(text);
	}
	return normalize(text.slice(0, at) + text.slice(at + 4)) || '380';
}

function format(digits) {
	const local = digits.slice(3);
	if (!local) {
		return digits ? PREFIX : '';
	}
	const parts = [local.slice(0, 2), local.slice(2, 5), local.slice(5, 7), local.slice(7, 9)];
	let out = `+380 (${parts[0]}`;
	if (local.length >= 2) {
		out += ')';
	}
	for (const part of parts.slice(1)) {
		if (part) {
			out += ` ${part}`;
		}
	}
	return out;
}

// Position in `text` right after the n-th digit.
function caretAfterDigits(text, n) {
	if (n <= 0) {
		return Math.min(text.length, PREFIX.length);
	}
	let seen = 0;
	for (let i = 0; i < text.length; i++) {
		if (/\d/.test(text[i]) && ++seen === n) {
			return i + 1;
		}
	}
	return text.length;
}

function onInput(event) {
	const input = event.target;
	const before = input.value.slice(0, input.selectionStart);
	const digitsBefore = digitsOf(before).length;
	const digits = digitsOf(input.value);
	input.value = format(digits.length > 3 ? digits : '380');
	const caret = caretAfterDigits(input.value, digitsBefore);
	input.setSelectionRange(caret, caret);
	input.removeAttribute('aria-invalid');
}

function caretToEnd(input) {
	const end = input.value.length;
	input.setSelectionRange(end, end);
}

function onFocus(event) {
	const input = event.target;
	if (!input.value) {
		input.value = PREFIX;
	}
	// Browsers place the caret on their own after focus; put it after the prefix.
	caretToEnd(input);
	requestAnimationFrame(() => {
		if (document.activeElement === input && input.selectionStart < PREFIX.length) {
			caretToEnd(input);
		}
	});
}

function onBlur(event) {
	const input = event.target;
	const digits = digitsOf(input.value);
	if (digits.length <= 3) {
		input.value = '';
		return;
	}
	input.setAttribute('aria-invalid', String(digits.length !== DIGITS));
}

export function initPhoneMask(root = document) {
	root.querySelectorAll('input[type="tel"]:not([data-phone-mask])').forEach((input) => {
		input.dataset.phoneMask = '';
		input.setAttribute('inputmode', 'tel');
		input.setAttribute('autocomplete', 'tel');
		input.setAttribute('placeholder', '+380 (__) ___ __ __');
		input.maxLength = 19;
		if (input.value) {
			input.value = format(normalize(input.value));
		}
		input.addEventListener('input', onInput);
		input.addEventListener('focus', onFocus);
		input.addEventListener('blur', onBlur);
	});
}
