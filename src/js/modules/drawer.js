const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select, textarea, summary, [tabindex]:not([tabindex="-1"])';

let active = null;
let opener = null;

function trapFocus(event) {
	if (!active || event.key !== 'Tab') {
		return;
	}
	const items = [...active.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent !== null);
	const first = items[0];
	const last = items[items.length - 1];

	if (event.shiftKey && document.activeElement === first) {
		event.preventDefault();
		last.focus();
	} else if (!event.shiftKey && document.activeElement === last) {
		event.preventDefault();
		first.focus();
	}
}

function onKeydown(event) {
	if (event.key === 'Escape') {
		closeDrawer();
		return;
	}
	trapFocus(event);
}

export function openDrawer(id, trigger = null) {
	const drawer = document.getElementById(id);
	if (!drawer || drawer === active) {
		return;
	}
	closeDrawer({ restoreFocus: false });

	active = drawer;
	opener = trigger;
	drawer.hidden = false;
	document.body.classList.add('is-locked');
	trigger?.setAttribute('aria-expanded', 'true');

	requestAnimationFrame(() => {
		drawer.classList.add('is-open');
		drawer.querySelector(FOCUSABLE)?.focus();
	});
	document.addEventListener('keydown', onKeydown);
}

export function closeDrawer({ restoreFocus = true } = {}) {
	if (!active) {
		return;
	}
	const drawer = active;
	active = null;

	drawer.classList.remove('is-open');
	document.body.classList.remove('is-locked');
	document.removeEventListener('keydown', onKeydown);
	opener?.setAttribute('aria-expanded', 'false');

	const panel = drawer.querySelector('.drawer__panel');
	const hide = () => {
		drawer.hidden = true;
	};
	if (matchMedia('(prefers-reduced-motion: reduce)').matches) {
		hide();
	} else {
		panel.addEventListener('transitionend', hide, { once: true });
	}

	if (restoreFocus) {
		opener?.focus();
	}
	opener = null;
}

export function initDrawers() {
	document.addEventListener('click', (event) => {
		const trigger = event.target.closest('[data-drawer-open]');
		if (trigger) {
			event.preventDefault();
			openDrawer(trigger.dataset.drawerOpen, trigger);
			return;
		}
		if (event.target.closest('[data-drawer-close]')) {
			closeDrawer();
		}
	});
}
