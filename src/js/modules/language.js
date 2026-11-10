// Language choice: the first-visit picker and the header switcher both save
// the choice, so the picker shows once. While it is open the rest of the
// page is inert and focus stays inside it.

const KEY = 'mitka-lang';

function save(lang) {
	try {
		localStorage.setItem(KEY, lang);
	} catch {
		// Private mode: the picker will simply show again next visit.
	}
}

function close(splash) {
	document.documentElement.classList.remove('needs-lang');
	for (const node of document.body.children) {
		node.inert = false;
	}
	splash.remove();
}

export function initLanguage() {
	document.addEventListener('click', (event) => {
		const link = event.target.closest('.lang-switch a[hreflang], .lang-switch__item[lang]');
		if (link) {
			save(link.getAttribute('lang').slice(0, 2));
		}
	});

	const splash = document.querySelector('[data-lang-splash]');
	if (!splash) {
		return;
	}
	if (!document.documentElement.classList.contains('needs-lang')) {
		splash.remove();
		return;
	}

	for (const node of document.body.children) {
		node.inert = node !== splash;
	}
	const options = [...splash.querySelectorAll('[data-lang-choice]')];
	(splash.querySelector('[data-lang-current]') ?? options[0]).focus();

	splash.addEventListener('click', (event) => {
		const option = event.target.closest('[data-lang-choice]');
		if (!option) {
			return;
		}
		save(option.dataset.langChoice);
		if (option.hasAttribute('data-lang-current')) {
			event.preventDefault();
			close(splash);
		}
	});

	splash.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			const current = splash.querySelector('[data-lang-current]');
			save(current?.dataset.langChoice ?? 'uk');
			close(splash);
		}
		if (event.key === 'Tab') {
			const i = options.indexOf(document.activeElement);
			event.preventDefault();
			options[(i + (event.shiftKey ? -1 : 1) + options.length) % options.length].focus();
		}
	});
}
