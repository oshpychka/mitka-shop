// Engraving preview on a sample of the product's material.
// Text mode mirrors the text field; star map mode adds the real sky for a date
// and city. Font size follows the longest line through --chars so the text
// always fits. "See it engraved" plays the laser animation over the plate.

import { playLaser } from './laser.js';

const MIN_CHARS = 8;

function limitText(value, maxLines, maxChars) {
	return value
		.split('\n')
		.slice(0, maxLines)
		.map((line) => line.slice(0, maxChars))
		.join('\n');
}

function formatDate(iso) {
	const [y, m, d] = iso.split('-');
	return `${d}.${m}.${y}`;
}

export function initEngraving() {
	const fieldset = document.querySelector('[data-engrave]');
	if (!fieldset) {
		return;
	}

	const $ = (selector) => fieldset.querySelector(selector);
	const plate = $('[data-engrave-plate]');
	const preview = $('[data-engrave-preview]');
	const input = $('[data-engrave-input]');
	const counter = $('[data-engrave-count]');
	const sky = $('[data-engrave-sky]');
	const meta = $('[data-engrave-meta]');
	const starsFields = $('[data-engrave-stars]');
	const modeInput = $('[data-engrave-mode-input]');
	const dateInput = $('[data-engrave-date]');
	const citySelect = $('[data-engrave-city]');
	const playButton = $('[data-engrave-play]');
	const maxLines = Number(fieldset.dataset.lines);
	const maxChars = Number(fieldset.dataset.lineChars);
	const placeholder = preview.dataset.placeholder;
	let skyModule = null;

	function renderText() {
		const limited = limitText(input.value, maxLines, maxChars);
		if (limited !== input.value) {
			const caret = Math.min(input.selectionStart, limited.length);
			input.value = limited;
			input.setSelectionRange(caret, caret);
		}

		const lines = limited
			.split('\n')
			.map((line) => line.trim())
			.filter(Boolean);
		const shown = lines.length ? lines : [placeholder];

		preview.replaceChildren(...shown.flatMap((line, i) => (i ? [document.createElement('br'), line] : [line])));
		preview.classList.toggle('is-placeholder', !lines.length);
		preview.style.setProperty('--chars', Math.max(MIN_CHARS, ...shown.map((line) => line.length)));

		const longest = Math.max(0, ...limited.split('\n').map((line) => line.length));
		counter.textContent = `${longest}/${maxChars}`;
	}

	async function renderSky() {
		if (fieldset.dataset.mode !== 'stars' || !dateInput.value) {
			return;
		}
		skyModule ||= await import(/* webpackChunkName: "sky" */ './sky.js');
		const city = citySelect.selectedOptions[0];
		const latitude = Number(city.dataset.lat);
		const longitude = Number(city.dataset.lon);
		skyModule.drawSky(sky, { date: skyModule.eveningInKyiv(dateInput.value), latitude, longitude });
		meta.textContent = `${formatDate(dateInput.value)} · ${city.textContent} · ${latitude.toFixed(2)}° N ${longitude.toFixed(2)}° E`;
	}

	function setMode(mode) {
		const stars = mode === 'stars';
		fieldset.dataset.mode = mode;
		plate.classList.toggle('is-stars', stars);
		modeInput.value = mode;
		sky.toggleAttribute('hidden', !stars); // SVG elements have no .hidden property.
		meta.hidden = !stars;
		starsFields.hidden = !stars;
		dateInput.disabled = !stars;
		citySelect.disabled = !stars;
		fieldset.querySelectorAll('[data-engrave-mode]').forEach((button) => {
			button.setAttribute('aria-pressed', String(button.dataset.engraveMode === mode));
		});
		renderSky();
	}

	function setFont(font) {
		preview.className = preview.className.replace(/engrave-font--\S+/, `engrave-font--${font}`);
	}

	input.addEventListener('input', renderText);
	fieldset.addEventListener('change', (event) => {
		if (event.target.matches('[data-engrave-font]')) {
			setFont(event.target.value);
		}
		if (event.target === dateInput || event.target === citySelect) {
			renderSky();
		}
	});
	fieldset.addEventListener('click', (event) => {
		const modeButton = event.target.closest('[data-engrave-mode]');
		if (modeButton) {
			setMode(modeButton.dataset.engraveMode);
		}
	});
	playButton.addEventListener('click', () => {
		playButton.disabled = true;
		playLaser(plate, [sky, $('[data-engrave-words]')].filter(Boolean), () => {
			playButton.disabled = false;
		});
	});

	const checked = $('[data-engrave-font]:checked');
	if (checked) {
		setFont(checked.value);
	}
	renderText();
}
