export function initMegaMenu() {
	const toggle = document.querySelector('[data-mega-toggle]');
	const menu = document.querySelector('[data-mega-menu]');
	if (!toggle || !menu) {
		return;
	}

	const setOpen = (open) => {
		menu.hidden = !open;
		toggle.setAttribute('aria-expanded', String(open));
	};

	toggle.addEventListener('click', () => setOpen(menu.hidden));

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && !menu.hidden) {
			setOpen(false);
			toggle.focus();
		}
	});

	document.addEventListener('click', (event) => {
		if (!menu.hidden && !menu.contains(event.target) && !toggle.contains(event.target)) {
			setOpen(false);
		}
	});
}
