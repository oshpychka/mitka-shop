// Static demo export: crawls the local WordPress site and writes a copy that any
// static host (Vercel, Netlify, GitHub Pages) can serve from demo/.
//
//   npm run env:start && npm run export
//
// Pages, images, fonts, styles and scripts are saved with root-relative links.
// Things that need a server (cart, checkout, account, search) are handled in
// the browser by demo.js: instant search over a product index, and a notice
// instead of adding to cart.

import { execFileSync } from 'node:child_process';
import { copyFile, mkdir, readFile, readdir, rm, writeFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const ORIGIN = process.env.EXPORT_ORIGIN ?? 'http://localhost:8888';
const root = dirname(dirname(fileURLToPath(import.meta.url)));
const out = join(root, 'demo');

// Server-only pages: links to them stay, demo.js intercepts the clicks.
const SKIP_PAGES = [/^\/wp-(admin|login|json|cron)/, /^\/xmlrpc/, /^\/(en\/)?(cart|checkout|my-account|basket|place-order|account)\//, /^\/feed\//, /\/feed\/$/];
const TEXT = /\.(html|css|js|json|svg|txt|xml)$/;

const queue = ['/', '/catalog/', '/en/', '/en/catalog/'];
const seen = new Set(queue);
const failed = [];

function localPath(url) {
	const { pathname } = new URL(url, ORIGIN);
	return pathname.endsWith('/') ? join(out, pathname, 'index.html') : join(out, decodeURIComponent(pathname));
}

function enqueue(raw, base) {
	let url;
	try {
		url = new URL(raw.replaceAll('\\/', '/').replaceAll('&amp;', '&'), base);
	} catch {
		return;
	}
	if (url.origin !== ORIGIN) {
		return;
	}
	const isPage = !/\.[a-z0-9]{2,5}$/i.test(url.pathname);
	if (isPage && (url.search || SKIP_PAGES.some((re) => re.test(url.pathname)))) {
		return;
	}
	const key = url.pathname;
	if (!seen.has(key)) {
		seen.add(key);
		queue.push(key);
	}
}

function collect(text, base, isCss) {
	// URLs also hide in JSON inside attributes: \/ escapes and &quot; quotes.
	const plain = text.replaceAll('\\/', '/').replaceAll('&quot;', '"');
	const absolute = /https?:\/\/localhost:8888[^"'\s)<>,&\\]*/g;
	for (const match of plain.matchAll(absolute)) {
		enqueue(match[0], base);
	}
	if (isCss) {
		for (const match of text.matchAll(/url\(\s*['"]?([^'")]+)['"]?\s*\)/g)) {
			if (!match[1].startsWith('data:')) {
				enqueue(match[1], base);
			}
		}
	} else {
		for (const match of text.matchAll(/(?:href|src)="(\/[^"/][^"]*)"/g)) {
			enqueue(match[1], base);
		}
		for (const match of text.matchAll(/srcset="([^"]+)"/g)) {
			for (const part of match[1].split(',')) {
				enqueue(part.trim().split(/\s+/)[0], base);
			}
		}
	}
}

function rewrite(text) {
	return text.replaceAll(ORIGIN, '').replaceAll(ORIGIN.replaceAll('/', '\\/'), '');
}

async function save(path, body) {
	const file = localPath(path);
	await mkdir(dirname(file), { recursive: true });
	await writeFile(file, body);
}

const demoTag = '<link rel="stylesheet" href="/demo.css"><script src="/demo.js" defer></script>';

async function fetchOne(path) {
	const url = new URL(path, ORIGIN).href;
	const res = await fetch(url, { redirect: 'manual' });
	if (!res.ok) {
		failed.push(`${res.status} ${path}`);
		return;
	}
	const type = res.headers.get('content-type') ?? '';
	const isHtml = type.includes('text/html');
	if (isHtml || type.includes('css') || type.includes('javascript') || TEXT.test(path)) {
		let text = await res.text();
		collect(text, url, type.includes('css'));
		text = rewrite(text);
		if (isHtml) {
			text = text.replace('</head>', `${demoTag}</head>`);
		}
		await save(path, text);
	} else {
		await save(path, Buffer.from(await res.arrayBuffer()));
	}
}

// Search index per language, built inside WordPress (see scripts/demo/index.php).
function productIndex() {
	const output = execFileSync('npx', ['wp-env', 'run', 'cli', 'wp', 'eval-file', 'wp-content/themes/mitka/scripts/demo/index.php'], {
		cwd: root,
		encoding: 'utf8',
		stdio: ['ignore', 'pipe', 'ignore'],
	});
	const line = output.split('\n').find((l) => l.startsWith('MITKA_INDEX:'));
	if (!line) {
		throw new Error('Search index script printed nothing.');
	}
	const index = JSON.parse(line.slice('MITKA_INDEX:'.length));
	for (const items of Object.values(index)) {
		for (const item of items) {
			item.url = rewrite(item.url);
			item.image = rewrite(item.image);
			if (item.image) {
				enqueue(item.image, ORIGIN);
			}
		}
	}
	return index;
}

await rm(out, { recursive: true, force: true });

// Lazy-loaded chunks are not linked from any page.
for (const file of await readdir(join(root, 'assets/build'))) {
	enqueue(`/wp-content/themes/mitka/assets/build/${file}`, ORIGIN);
}

const index = productIndex();

while (queue.length) {
	const batch = queue.splice(0, 8);
	await Promise.all(batch.map(fetchOne));
}

// A page that does not exist renders the theme's 404 template.
const notFound = await fetch(`${ORIGIN}/demo-404-page/`);
await writeFile(join(out, '404.html'), rewrite(await notFound.text()).replace('</head>', `${demoTag}</head>`));

await writeFile(join(out, 'search-index.json'), JSON.stringify(index.uk));
await writeFile(join(out, 'search-index-en.json'), JSON.stringify(index.en));
await copyFile(join(root, 'scripts/demo/demo.js'), join(out, 'demo.js'));
await copyFile(join(root, 'scripts/demo/demo.css'), join(out, 'demo.css'));
await writeFile(join(out, 'vercel.json'), `${JSON.stringify({ cleanUrls: false, trailingSlash: true }, null, '\t')}\n`);
await writeFile(join(out, 'robots.txt'), 'User-agent: *\nDisallow: /\n');

// Notices for third-party material shipped in the copy.
const photos = JSON.parse(await readFile(join(root, 'scripts/data/photos.json'), 'utf8'));
const credits = Object.entries(photos)
	.map(([slug, photo]) => `${slug}: ${photo.author} — ${photo.author_url.split('?')[0]}`)
	.join('\n');
await mkdir(join(out, 'licenses'), { recursive: true });
await writeFile(join(out, 'licenses/photos.txt'), `Product photos from Unsplash (https://unsplash.com/license), placeholders for the demo.\n\n${credits}\n`);
await copyFile(join(root, 'src/js/data/sky.LICENSE.txt'), join(out, 'licenses/star-data.txt'));
await copyFile(join(root, 'assets/fonts/LICENSE.md'), join(out, 'licenses/fonts.md'));

console.log(`Exported ${seen.size} files, ${index.uk.length} + ${index.en.length} products to demo/.`);
if (failed.length) {
	console.log(`Not exported:\n  ${failed.join('\n  ')}`);
}
