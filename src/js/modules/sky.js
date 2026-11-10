// Star map: the sky over a place at a moment, drawn as an engraving-ready SVG.
// Stars and constellation lines come from d3-celestial (src/js/data/sky.json, BSD-3).
// Equatorial coordinates → horizontal (altitude/azimuth) → stereographic projection
// of the visible half of the sky into a circle, north up and east on the left,
// as you see it lying on your back.

import sky from '../data/sky.json';

const RAD = Math.PI / 180;
const SIZE = 200;
const CENTER = SIZE / 2;
const RADIUS = 94;
const SVG_NS = 'http://www.w3.org/2000/svg';

function julianDay(date) {
	return date.getTime() / 86400000 + 2440587.5;
}

// Local sidereal time in degrees.
function siderealTime(date, longitude) {
	const gmst = 280.46061837 + 360.98564736629 * (julianDay(date) - 2451545);
	return (((gmst + longitude) % 360) + 360) % 360;
}

function projector(date, latitude, longitude) {
	const lst = siderealTime(date, longitude);
	const phi = latitude * RAD;
	const sinPhi = Math.sin(phi);
	const cosPhi = Math.cos(phi);

	return ([ra, dec]) => {
		const h = (lst - ra) * RAD;
		const d = dec * RAD;
		const sinAlt = sinPhi * Math.sin(d) + cosPhi * Math.cos(d) * Math.cos(h);
		if (sinAlt < 0) {
			return null;
		}
		const alt = Math.asin(sinAlt);
		const az = Math.atan2(-Math.cos(d) * Math.sin(h), cosPhi * Math.sin(d) - sinPhi * Math.cos(d) * Math.cos(h));
		const r = Math.tan((Math.PI / 2 - alt) / 2) * RADIUS;
		return [CENTER - r * Math.sin(az), CENTER - r * Math.cos(az)];
	};
}

// 22:00 in Kyiv on the given day, whatever the visitor's own time zone.
export function eveningInKyiv(isoDate) {
	const guess = new Date(`${isoDate}T20:00:00Z`);
	const kyivHour = Number(
		new Intl.DateTimeFormat('en-GB', { timeZone: 'Europe/Kyiv', hour: '2-digit', hourCycle: 'h23' }).format(guess),
	);
	return new Date(guess.getTime() + (22 - kyivHour) * 3600000);
}

function el(name, attrs) {
	const node = document.createElementNS(SVG_NS, name);
	for (const [key, value] of Object.entries(attrs)) {
		node.setAttribute(key, value);
	}
	return node;
}

const fmt = (n) => n.toFixed(1);

export function drawSky(svg, { date, latitude, longitude }) {
	const project = projector(date, latitude, longitude);
	const nodes = [el('circle', { class: 'sky__rim', cx: CENTER, cy: CENTER, r: RADIUS + 3 })];

	for (const line of sky.lines) {
		let d = '';
		let pen = false;
		for (const point of line) {
			const xy = project(point);
			if (!xy) {
				pen = false;
				continue;
			}
			d += `${pen ? 'L' : 'M'}${fmt(xy[0])} ${fmt(xy[1])}`;
			pen = true;
		}
		if (d.includes('L')) {
			nodes.push(el('path', { class: 'sky__line', d }));
		}
	}

	for (const [ra, dec, mag] of sky.stars) {
		const xy = project([ra, dec]);
		if (xy) {
			const r = Math.min(2.6, 0.35 + (5 - mag) * 0.38);
			nodes.push(el('circle', { class: 'sky__star', cx: fmt(xy[0]), cy: fmt(xy[1]), r: r.toFixed(2) }));
		}
	}

	svg.replaceChildren(...nodes);
}
