# Mitka

WooCommerce theme for Mitka, a Ukrainian workshop of engraved gifts and gift boxes. Ukrainian and English, live engraving preview, gift box builder and Nova Poshta checkout.

## Stack

WordPress, WooCommerce, ACF, Polylang, PHP 8.3, SCSS, vanilla JS, Webpack

## Run locally

Requires Node 24, Composer and Docker.

```bash
npm install
composer install
npm run build
npm run env:start
npm run seed
```

The site runs at http://localhost:8888, admin at http://localhost:8888/wp-admin (admin / password).

Use `npm run dev` to rebuild assets on change.

## Product photos

Placeholder photos come from Unsplash. Put an access key in `.env` (see `.env.example`), then:

```bash
npm run photos -- search <slug…>   # save 12 candidates per product
npm run photos -- pick slug=N …    # use candidate N, cached in scripts/data/photos.json
npm run seed                       # download picked photos into WordPress
```

The free API key allows 50 requests an hour; both commands resume where they stopped. Replace these with real product photos before launch.

## Static demo

`demo/` is a static copy of the site for showing it without a server (Vercel, Netlify, GitHub Pages). Cart, checkout and account show a demo notice; search works in the browser.

```bash
npm run env:start
npm run export
```

Commit the updated `demo/` to redeploy. On Vercel the project's Root Directory is `demo`, with no build command.
