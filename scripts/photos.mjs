// Product photos from Unsplash, cached in data/photos.json for the seed.
//
//   npm run photos                      auto: first unused result for every product without a photo
//   npm run photos -- search [slug…]    save 12 candidates per product to data/photo-candidates.json
//                                       (default: products without a photo)
//   npm run photos -- pick slug=N …     use candidate N (1-based) for a product
//
// Only search and pick call the API (one request each), so the hourly limit
// goes further when photos are chosen by hand. Rerun after the limit resets.

import { readFile, writeFile } from 'node:fs/promises';

try {
	process.loadEnvFile();
} catch {
	// No .env file: fall back to the real environment.
}

const key = process.env.UNSPLASH_ACCESS_KEY;
if (!key) {
	console.error('Set UNSPLASH_ACCESS_KEY in .env (see .env.example).');
	process.exit(1);
}

const dataDir = new URL('./data/', import.meta.url);
const products = await readJson('products.json', []);
const photos = await readJson('photos.json', {});
const candidates = await readJson('photo-candidates.json', {});
const utm = 'utm_source=mitka&utm_medium=referral';

class RateLimited extends Error {}

let remaining = Infinity;

async function readJson(file, fallback) {
	try {
		return JSON.parse(await readFile(new URL(file, dataDir), 'utf8'));
	} catch {
		return fallback;
	}
}

async function writeJson(file, value) {
	await writeFile(new URL(file, dataDir), `${JSON.stringify(value, null, '\t')}\n`);
}

async function api(url) {
	if (remaining < 1) {
		throw new RateLimited();
	}
	const res = await fetch(url, {
		headers: { Authorization: `Client-ID ${key}`, 'Accept-Version': 'v1' },
	});
	remaining = Number(res.headers.get('x-ratelimit-remaining') ?? Infinity);
	if (res.status === 429 || (res.status === 403 && remaining === 0)) {
		throw new RateLimited();
	}
	if (!res.ok) {
		throw new Error(`Unsplash ${res.status}: ${await res.text()}`);
	}
	return res.json();
}

function toCandidate(photo) {
	return {
		id: photo.id,
		url: photo.urls.raw,
		alt: photo.alt_description ?? '',
		author: photo.user.name,
		author_url: `${photo.user.links.html}?${utm}`,
		download_location: photo.links.download_location,
	};
}

async function search(product, orientation = 'squarish') {
	const query = new URLSearchParams({
		query: product.photo,
		per_page: '12',
		content_filter: 'high',
		...(orientation && { orientation }),
	});
	const { results } = await api(`https://api.unsplash.com/search/photos?${query}`);
	return results.map(toCandidate);
}

// Unsplash API guidelines: report every photo we actually use.
async function use(slug, candidate) {
	await api(candidate.download_location);
	const photo = { ...candidate, source_url: `https://unsplash.com/?${utm}` };
	delete photo.download_location;
	photos[slug] = photo;
	await writeJson('photos.json', photos);
}

const bySlug = new Map(products.map((product) => [product.slug, product]));
const [mode = 'auto', ...args] = process.argv.slice(2);

try {
	if (mode === 'search') {
		const slugs = args.length ? args : products.filter((p) => !photos[p.slug]).map((p) => p.slug);
		for (const slug of slugs) {
			if (!bySlug.has(slug)) {
				throw new Error(`Unknown product: ${slug}`);
			}
			candidates[slug] = await search(bySlug.get(slug));
			if (!candidates[slug].length) {
				candidates[slug] = await search(bySlug.get(slug), '');
			}
			await writeJson('photo-candidates.json', candidates);
			console.log(`✓ ${slug}: ${candidates[slug].length} candidates`);
		}
	} else if (mode === 'pick') {
		for (const arg of args) {
			const [slug, n] = arg.split('=');
			const candidate = candidates[slug]?.[Number(n) - 1];
			if (!candidate) {
				throw new Error(`No candidate ${n} for ${slug}. Run search first.`);
			}
			await use(slug, candidate);
			console.log(`✓ ${slug} — ${candidate.author}`);
		}
	} else {
		const used = new Set(Object.values(photos).map((photo) => photo.id));
		for (const product of products) {
			if (photos[product.slug]) {
				continue;
			}
			let pick;
			// Square shots crop best into product cards; any shape beats no photo.
			for (const orientation of ['squarish', '']) {
				pick = (await search(product, orientation)).find((photo) => !used.has(photo.id));
				if (pick) {
					break;
				}
			}
			if (!pick) {
				console.warn(`No photo for ${product.slug} ("${product.photo}")`);
				continue;
			}
			used.add(pick.id);
			await use(product.slug, pick);
			console.log(`✓ ${product.slug} — ${pick.author}`);
		}
	}
} catch (error) {
	if (!(error instanceof RateLimited)) {
		throw error;
	}
	console.warn('Unsplash hourly limit reached. Run the same command again in an hour.');
}

const missing = products.filter((product) => !photos[product.slug]).length;
console.log(`${products.length - missing}/${products.length} products have photos.`);
