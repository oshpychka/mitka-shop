// − / + buttons around WooCommerce quantity inputs instead of the browser's
// tiny spinners. In the cart a change updates the cart by itself.

const UPDATE_DELAY = 600;

function step(input, direction) {
	const min = input.min === '' ? 0 : Number(input.min);
	const max = input.max === '' ? Infinity : Number(input.max);
	const stepSize = Number(input.step) || 1;
	const next = Math.min(max, Math.max(min, (Number(input.value) || 0) + direction * stepSize));
	if (next !== Number(input.value)) {
		input.value = String(next);
		input.dispatchEvent(new Event('change', { bubbles: true }));
	}
}

function button(label, icon, direction, input) {
	const node = document.createElement('button');
	node.type = 'button';
	node.className = 'quantity__btn';
	node.setAttribute('aria-label', label);
	node.innerHTML = `<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="${icon}" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>`;
	node.addEventListener('click', () => step(input, direction));
	return node;
}

export function initQuantity(root = document) {
	const labels = document.documentElement.lang.startsWith('uk')
		? { less: 'Менше', more: 'Більше' }
		: { less: 'Decrease', more: 'Increase' };

	root.querySelectorAll('.quantity:not(.has-steppers)').forEach((wrap) => {
		const input = wrap.querySelector('input.qty');
		if (!input || input.type === 'hidden') {
			return;
		}
		wrap.classList.add('has-steppers');
		wrap.prepend(button(labels.less, 'M6 12h12', -1, input));
		wrap.append(button(labels.more, 'M6 12h12M12 6v12', 1, input));
	});

	const cartForm = document.querySelector('.woocommerce-cart-form');
	if (cartForm && !cartForm.dataset.autoUpdate) {
		cartForm.dataset.autoUpdate = '';
		let timer;
		cartForm.addEventListener('change', (event) => {
			if (!event.target.matches('input.qty')) {
				return;
			}
			clearTimeout(timer);
			timer = setTimeout(() => {
				const update = cartForm.querySelector('[name="update_cart"]');
				update.disabled = false;
				update.click();
			}, UPDATE_DELAY);
		});
	}
}
