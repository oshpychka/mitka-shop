// "See it engraved": a laser dot rasters across the plate line by line, as a
// real engraver does, revealing the design top to bottom with sparks and smoke.

const DURATION = 3200;
const ROW = 3; // px between raster passes
const MAX_SPARKS = 40;

function spawn(plate, className, x, y, vars = {}) {
	const node = document.createElement('span');
	node.className = className;
	node.style.left = `${x}px`;
	node.style.top = `${y}px`;
	for (const [key, value] of Object.entries(vars)) {
		node.style.setProperty(key, value);
	}
	node.addEventListener('animationend', () => node.remove(), { once: true });
	plate.append(node);
	return node;
}

// Geometry of the visible targets relative to the plate, measured once at the start.
function measure(plate, targets) {
	const base = plate.getBoundingClientRect();
	const boxes = targets.map((target) => {
		const r = target.getBoundingClientRect();
		return { target, top: r.top - base.top, height: r.height, left: r.left - base.left, right: r.right - base.left };
	});
	return {
		boxes,
		left: Math.min(...boxes.map((b) => b.left)),
		right: Math.max(...boxes.map((b) => b.right)),
		top: Math.min(...boxes.map((b) => b.top)),
		bottom: Math.max(...boxes.map((b) => b.top + b.height)),
	};
}

// Everything above the beam is engraved; the rest is still blank.
function reveal(area, y) {
	for (const { target, top, height } of area.boxes) {
		const hidden = Math.min(100, Math.max(0, ((top + height - y) / height) * 100));
		target.style.clipPath = hidden > 0 ? `inset(0 0 ${hidden.toFixed(2)}% 0)` : '';
	}
}

export function playLaser(plate, targets, onDone) {
	targets = targets.filter((t) => !t.hasAttribute('hidden'));
	if (plate.classList.contains('is-burning') || !targets.length) {
		return;
	}

	if (matchMedia('(prefers-reduced-motion: reduce)').matches) {
		plate.classList.add('is-fresh');
		setTimeout(() => plate.classList.remove('is-fresh'), 1200);
		onDone?.();
		return;
	}

	const area = measure(plate, targets);
	const width = area.right - area.left;
	const height = area.bottom - area.top;
	const rows = Math.max(8, Math.round(height / ROW));
	const dot = spawn(plate, 'laser-dot', area.left, area.top);
	let sparks = 0;
	let lastSmoke = 0;
	let start = 0;

	plate.classList.add('is-burning');
	reveal(area, area.top);

	function frame(now) {
		start ||= now;
		const progress = Math.min(1, (now - start) / DURATION);
		const row = progress * rows;
		const across = row % 2;
		const x = area.left + (across < 1 ? across : 2 - across) * width;
		const y = area.top + progress * height;

		dot.style.left = `${x}px`;
		dot.style.top = `${y}px`;
		reveal(area, y);

		if (sparks < MAX_SPARKS && Math.random() < 0.35) {
			sparks++;
			const angle = Math.random() * Math.PI * 2;
			const speed = 6 + Math.random() * 16;
			spawn(plate, 'laser-spark', x, y, {
				'--dx': `${Math.cos(angle) * speed}px`,
				'--dy': `${Math.sin(angle) * speed - 6}px`,
			}).addEventListener('animationend', () => sparks--, { once: true });
		}
		if (now - lastSmoke > 140) {
			lastSmoke = now;
			spawn(plate, 'laser-smoke', x, y, { '--drift': `${(Math.random() - 0.5) * 20}px` });
		}

		if (progress < 1) {
			requestAnimationFrame(frame);
			return;
		}

		targets.forEach((t) => (t.style.clipPath = ''));
		dot.classList.add('is-off');
		plate.classList.remove('is-burning');
		plate.classList.add('is-fresh');
		setTimeout(() => plate.classList.remove('is-fresh'), 1400);
		onDone?.();
	}

	requestAnimationFrame(frame);
}
