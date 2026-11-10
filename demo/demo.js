// Demo copy only (added by scripts/export-static.mjs). A static host has no
// WordPress behind it, so this file stands in for what needs a server:
// - search: instant results from /search-index.json as you type;
// - cart, checkout, account: a short notice instead of a page that cannot work.

(() => {
	const EN = document.documentElement.lang.startsWith('en');
	const T = EN
		? {
				notice: 'This is a demo of the site: cart, checkout and account work in the full version.',
				engraved: (text) => `Engraving “${text}” would go to the cart. `,
				nothing: (query) => `Nothing found for “${query}”. Try a shorter word.`,
				index: '/search-index-en.json',
			}
		: {
				notice: 'Це демо-версія сайту: кошик, оформлення й кабінет працюють у повній версії.',
				engraved: (text) => `Гравіювання «${text}» потрапило б у кошик. `,
				nothing: (query) => `За запитом «${query}» нічого не знайшли. Спробуйте коротше слово.`,
				index: '/search-index.json',
			};
	const NOTICE = T.notice;
	const SERVER_PATHS = /^\/(en\/)?(cart|checkout|my-account|basket|place-order|account)\//;
	let toastTimer;
	let index = null;

	function toast(text) {
		let box = document.querySelector('.toasts');
		if (!box) {
			box = document.createElement('div');
			box.className = 'toasts';
			box.setAttribute('role', 'status');
			box.setAttribute('aria-live', 'polite');
			document.body.append(box);
		}
		box.replaceChildren();
		const node = document.createElement('div');
		node.className = 'toast';
		node.textContent = text;
		box.append(node);
		clearTimeout(toastTimer);
		toastTimer = setTimeout(() => node.remove(), 4000);
	}

	// Add to cart (product page form and the bag button on cards).
	document.addEventListener(
		'submit',
		(event) => {
			const form = event.target;
			if (form.matches('form.cart')) {
				event.preventDefault();
				event.stopImmediatePropagation();
				const engraving = form.querySelector('[data-engrave-input]')?.value.trim();
				toast(engraving ? T.engraved(engraving.replace(/\n/g, ' / ')) + NOTICE : NOTICE);
			}
			if (form.matches('.site-search')) {
				event.preventDefault();
				form
					.querySelector('input[type="search"]')
					.dispatchEvent(new Event('input', { bubbles: true }));
			}
		},
		true,
	);

	document.addEventListener(
		'click',
		(event) => {
			const link = event.target.closest('a[href]');
			if (!link) {
				return;
			}
			const url = new URL(link.href, location.href);
			if (
				url.origin === location.origin &&
				(SERVER_PATHS.test(url.pathname) || url.searchParams.has('add-to-cart'))
			) {
				event.preventDefault();
				toast(NOTICE);
			}
		},
		true,
	);

	// Instant search.
	async function loadIndex() {
		index ||= fetch(T.index).then((res) => res.json());
		return index;
	}

	function normalize(text) {
		return text.toLowerCase().replace(/[’'ʼ]/g, '').replace(/ё/g, 'е');
	}

	function render(panel, items, query) {
		panel.replaceChildren();
		if (!items.length) {
			const empty = document.createElement('p');
			empty.className = 'demo-search__empty';
			empty.textContent = T.nothing(query);
			panel.append(empty);
			return;
		}
		for (const item of items.slice(0, 8)) {
			const a = document.createElement('a');
			a.className = 'demo-search__item';
			a.href = item.url;
			a.innerHTML =
				'<span class="demo-search__img"></span><span class="demo-search__text"><span class="demo-search__name"></span><span class="demo-search__meta"></span></span><span class="demo-search__price price"></span>';
			if (item.image) {
				const img = document.createElement('img');
				img.src = item.image;
				img.alt = '';
				img.loading = 'lazy';
				a.querySelector('.demo-search__img').append(img);
			}
			a.querySelector('.demo-search__name').textContent = item.name;
			a.querySelector('.demo-search__meta').textContent = item.category;
			const price = a.querySelector('.demo-search__price');
			if (item.old) {
				const del = document.createElement('del');
				del.textContent = item.old;
				price.append(del, ' ');
				const ins = document.createElement('ins');
				ins.textContent = item.price;
				price.append(ins);
			} else {
				price.textContent = item.price;
			}
			panel.append(a);
		}
	}

	document.querySelectorAll('.site-search').forEach((form) => {
		const input = form.querySelector('input[type="search"]');
		const panel = document.createElement('div');
		panel.className = 'demo-search';
		panel.hidden = true;
		panel.setAttribute('role', 'listbox');
		form.append(panel);

		input.addEventListener('input', async () => {
			const query = input.value.trim();
			if (query.length < 2) {
				panel.hidden = true;
				return;
			}
			const words = normalize(query).split(/\s+/);
			const items = (await loadIndex()).filter((item) => {
				const haystack = normalize(`${item.name} ${item.category}`);
				return words.every((word) => haystack.includes(word));
			});
			render(panel, items, query);
			panel.hidden = false;
		});
		input.addEventListener('focus', loadIndex, { once: true });
		document.addEventListener('click', (event) => {
			if (!form.contains(event.target)) {
				panel.hidden = true;
			}
		});
		input.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') {
				panel.hidden = true;
			}
		});
	});
})();
