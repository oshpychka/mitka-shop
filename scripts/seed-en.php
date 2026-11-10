<?php
/**
 * English layer for the demo store, run at the end of seed.php.
 *
 * Polylang (free) keeps one post or term per language and links them as
 * translations. Free Polylang needs unique slugs, so English products and
 * terms get an "-en" suffix and English pages their own slugs.
 */

defined( 'WP_CLI' ) || exit;

const MITKA_EN_SUFFIX = '-en';

function mitka_en_setup_polylang() {
	$languages = PLL()->model->languages;
	foreach (
		array(
			array(
				'locale'     => 'uk',
				'name'       => 'Українська',
				'slug'       => 'uk',
				'flag'       => 'ua',
				'term_group' => 0,
			),
			array(
				'locale'     => 'en_US',
				'name'       => 'English',
				'slug'       => 'en',
				'flag'       => 'us',
				'term_group' => 1,
			),
		) as $language
	) {
		if ( ! PLL()->model->get_language( $language['slug'] ) ) {
			$result = $languages->add( array_merge( $language, array( 'no_default_cat' => true ) ) );
			if ( is_wp_error( $result ) ) {
				WP_CLI::error( $result->get_error_message() );
			}
		}
	}

	$options                  = PLL()->options;
	$options['default_lang']  = 'uk';
	$options['hide_default']  = true;
	$options['force_lang']    = 1;
	$options['rewrite']       = true;
	$options['browser']       = false;
	$options['media_support'] = false;
	$options['post_types']    = array( 'product' );
	$options['taxonomies']    = array( 'product_cat', 'mitka_maker', 'mitka_occasion', 'pa_material' );
	$options->save();
	$languages->clean_cache();
}

/**
 * Everything created before Polylang had languages is Ukrainian.
 */
function mitka_en_mark_ukrainian() {
	$posts = get_posts(
		array(
			'post_type'      => array( 'product', 'page' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'lang'           => '',
		)
	);
	foreach ( $posts as $id ) {
		if ( ! pll_get_post_language( $id ) ) {
			pll_set_post_language( $id, 'uk' );
		}
	}

	$terms = get_terms(
		array(
			'taxonomy'   => array( 'product_cat', 'mitka_maker', 'mitka_occasion', 'pa_material', 'nav_menu' ),
			'hide_empty' => false,
			'lang'       => '',
		)
	);
	foreach ( $terms as $term ) {
		if ( 'nav_menu' !== $term->taxonomy && ! pll_get_term_language( $term->term_id ) ) {
			pll_set_term_language( $term->term_id, 'uk' );
		}
	}
}

function mitka_en_term( $taxonomy, $uk_slug, $name, $description = '' ) {
	$uk = get_term_by( 'slug', $uk_slug, $taxonomy );
	if ( ! $uk ) {
		return 0;
	}
	$translated = pll_get_term( $uk->term_id, 'en' );
	if ( $translated ) {
		return $translated;
	}

	$term = wp_insert_term(
		$name,
		$taxonomy,
		array(
			'slug'        => $uk_slug . MITKA_EN_SUFFIX,
			'description' => $description,
		)
	);
	if ( is_wp_error( $term ) ) {
		WP_CLI::error( "{$taxonomy}/{$uk_slug}: " . $term->get_error_message() );
	}

	foreach ( array( 'order', 'founded' ) as $meta ) {
		$value = get_term_meta( $uk->term_id, $meta, true );
		if ( '' !== $value ) {
			update_term_meta( $term['term_id'], $meta, $value );
		}
	}
	pll_set_term_language( $term['term_id'], 'en' );
	pll_save_term_translations(
		array(
			'uk' => $uk->term_id,
			'en' => $term['term_id'],
		)
	);
	return $term['term_id'];
}

function mitka_en_taxonomies( $en ) {
	foreach ( $en['categories'] as $slug => $category ) {
		mitka_en_term( 'product_cat', $slug, $category['name'], $category['description'] );
	}
	foreach ( $en['makers'] as $slug => $maker ) {
		$id = mitka_en_term( 'mitka_maker', $slug, $maker['name'], $maker['story'] );
		update_term_meta( $id, 'city', $maker['city'] );
	}
	foreach ( $en['occasions'] as $slug => $name ) {
		mitka_en_term( 'mitka_occasion', $slug, $name );
	}
	foreach ( $en['materials'] as $slug => $name ) {
		mitka_en_term( 'pa_material', $slug, $name );
	}
	WP_CLI::success( 'English categories, makers, occasions and materials created.' );
}

function mitka_en_products( $products, $en, $labels ) {
	$suffix = fn( $slugs ) => array_map( fn( $slug ) => $slug . MITKA_EN_SUFFIX, (array) $slugs );

	foreach ( $products as $item ) {
		$uk_post = get_page_by_path( $item['slug'], OBJECT, 'product' );
		if ( ! $uk_post || pll_get_post( $uk_post->ID, 'en' ) ) {
			continue;
		}
		$text = $en['products'][ $item['slug'] ];

		$en_item = array_merge(
			$item,
			$text,
			array(
				'slug'      => $item['slug'] . MITKA_EN_SUFFIX,
				'cat'       => $suffix( $item['cat'] )[0],
				'maker'     => $item['maker'] . MITKA_EN_SUFFIX,
				'occasions' => $suffix( $item['occasions'] ),
				'material'  => $suffix( $item['material'] ),
			)
		);
		if ( ! empty( $item['variations'] ) ) {
			$en_item['variations'] = array_map(
				fn( $row ) => array_map( fn( $value ) => is_string( $value ) ? ( $en['values'][ $value ] ?? $value ) : $value, $row ),
				$item['variations']
			);
		}

		mitka_seed_product( $en_item, $labels );

		$en_post = get_page_by_path( $en_item['slug'], OBJECT, 'product' );
		pll_set_post_language( $en_post->ID, 'en' );
		pll_save_post_translations(
			array(
				'uk' => $uk_post->ID,
				'en' => $en_post->ID,
			)
		);
		$thumbnail = get_post_thumbnail_id( $uk_post );
		if ( $thumbnail ) {
			set_post_thumbnail( $en_post, $thumbnail );
		}
	}

	// Photos picked after the first run: keep English thumbnails in step.
	foreach ( $products as $item ) {
		$uk_post = get_page_by_path( $item['slug'], OBJECT, 'product' );
		$en_id   = $uk_post ? pll_get_post( $uk_post->ID, 'en' ) : 0;
		$photo   = $uk_post ? get_post_thumbnail_id( $uk_post ) : 0;
		if ( $en_id && $photo && get_post_thumbnail_id( $en_id ) !== $photo ) {
			set_post_thumbnail( $en_id, $photo );
		}
	}

	WP_CLI::success( 'English products created.' );
}

function mitka_en_pages() {
	$pages = array(
		'cart'       => array( 'cart', 'Cart', 'basket', '[woocommerce_cart]' ),
		'checkout'   => array( 'checkout', 'Checkout', 'place-order', '[woocommerce_checkout]' ),
		'my-account' => array( 'my-account', 'My account', 'account', '[woocommerce_my_account]' ),
		'delivery'   => array(
			'delivery',
			'Delivery and payment',
			'shipping',
			"<!-- wp:paragraph --><p>We ship with Nova Poshta across Ukraine, to a branch or a parcel locker.</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2>Timing</h2><!-- /wp:heading -->\n<!-- wp:list --><ul><li>Engraved items are made in 1–2 working days.</li><li>Box fillings without engraving ship the next working day.</li><li>Nova Poshta delivery usually takes 1–3 days.</li></ul><!-- /wp:list -->\n<!-- wp:heading --><h2>Cost</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>At the carrier's rates. Free from 1500 UAH.</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2>Payment</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>You pay at the branch after checking the parcel. No prepayment needed.</p><!-- /wp:paragraph -->",
		),
		'returns'    => array(
			'returns',
			'Returns and exchanges',
			'returns-policy',
			"<!-- wp:paragraph --><p>Items without engraving can be returned or exchanged within 14 days if unused.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Engraved items are made for you, so we do not take them back unless there is a defect or a mistake in the inscription on our side. Then we remake it for free.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>To return an item, send us the order number and a photo.</p><!-- /wp:paragraph -->",
		),
		'about'      => array(
			'about',
			'About us',
			'about-us',
			"<!-- wp:paragraph --><p>Mitka is a laser workshop in Lviv. We engrave names, dates and wishes on things made by small Ukrainian workshops and put them together into gift boxes.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>We check every item by hand before engraving and before shipping. If something is off, we redo it.</p><!-- /wp:paragraph -->",
		),
		'contacts'   => array(
			'contacts',
			'Contacts',
			'contact',
			"<!-- wp:paragraph --><p>Write or call if you want a box built for you, engraving for your team, or details about an order.</p><!-- /wp:paragraph -->\n<!-- wp:list --><ul><li>Phone: +380&nbsp;67&nbsp;000&nbsp;14&nbsp;27</li><li>Email: hello@mitka.shop</li><li>Workshop: 12 Horodotska St, Lviv</li><li>Mon–Fri 9:00–19:00, Sat 10:00–16:00</li></ul><!-- /wp:list -->",
		),
		'privacy'    => array(
			'privacy',
			'Privacy policy',
			'privacy-policy',
			"<!-- wp:paragraph --><p>We collect only what an order needs: your name, phone, email, city and Nova Poshta branch.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Only the workshop and the delivery service see this data. We never pass it to ad networks or sell it.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>To delete your data, write to hello@mitka.shop.</p><!-- /wp:paragraph -->",
		),
		'terms'      => array(
			'terms',
			'Terms of use',
			'terms-of-use',
			'<!-- wp:paragraph --><p>By placing an order you accept the delivery, payment and return terms on this site. You check the engraving text yourself before ordering: we engrave it exactly as written.</p><!-- /wp:paragraph -->',
		),
	);

	$ids = array();
	foreach ( $pages as $key => list( $uk_slug, $title, $slug, $content ) ) {
		$uk = get_page_by_path( $uk_slug );
		if ( ! $uk ) {
			continue;
		}
		$en_id = pll_get_post( $uk->ID, 'en' );
		if ( ! $en_id ) {
			$en_id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_name'    => $slug,
					'post_title'   => $title,
					'post_content' => $content,
				)
			);
			pll_set_post_language( $en_id, 'en' );
			pll_save_post_translations(
				array(
					'uk' => $uk->ID,
					'en' => $en_id,
				)
			);
		}
		// The slug may have been taken by WordPress's sample privacy page on an earlier run.
		if ( get_post_field( 'post_name', $en_id ) !== $slug ) {
			wp_update_post(
				array(
					'ID'        => $en_id,
					'post_name' => $slug,
				)
			);
		}
		$ids[ $key ] = $en_id;
	}

	WP_CLI::success( 'English pages created.' );
	return $ids;
}

function mitka_en_menu( $name, $items ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		return $menu->term_id;
	}
	$menu_id = wp_create_nav_menu( $name );
	foreach ( $items as $item ) {
		wp_update_nav_menu_item( $menu_id, 0, array_merge( array( 'menu-item-status' => 'publish' ), $item ) );
	}
	return $menu_id;
}

function mitka_en_menus( $pages ) {
	$term = function ( $taxonomy, $slug, $title ) {
		return array(
			'menu-item-title'     => $title,
			'menu-item-object'    => $taxonomy,
			'menu-item-object-id' => get_term_by( 'slug', $slug . MITKA_EN_SUFFIX, $taxonomy )->term_id,
			'menu-item-type'      => 'taxonomy',
		);
	};
	$page = fn( $key ) => array(
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $pages[ $key ],
		'menu-item-type'      => 'post_type',
	);

	$en = array(
		'primary'        => mitka_en_menu(
			'Main menu',
			array(
				$term( 'product_cat', 'boxes', 'Gift boxes' ),
				$term( 'mitka_occasion', 'for-him', 'For him' ),
				$term( 'mitka_occasion', 'for-her', 'For her' ),
				$term( 'mitka_occasion', 'colleagues', 'Colleagues' ),
				$term( 'mitka_occasion', 'wedding', 'Wedding' ),
				$term( 'mitka_occasion', 'new-home', 'Housewarming' ),
			)
		),
		'footer-buyers'  => mitka_en_menu( 'For buyers', array( $page( 'delivery' ), $page( 'returns' ), $page( 'privacy' ) ) ),
		'footer-company' => mitka_en_menu( 'Company', array( $page( 'about' ), $page( 'contacts' ), $page( 'terms' ) ) ),
	);

	$uk        = get_theme_mod( 'nav_menu_locations', array() );
	$nav_menus = array();
	foreach ( $en as $location => $menu_id ) {
		$nav_menus[ get_stylesheet() ][ $location ] = array(
			'uk' => $uk[ $location ] ?? 0,
			'en' => $menu_id,
		);
	}
	PLL()->options['nav_menus'] = $nav_menus;
	PLL()->options->save();

	WP_CLI::success( 'English menus created.' );
}

/**
 * Translations for strings Polylang keeps in its own string table:
 * site title and tagline, and the theme's translatable options.
 */
function mitka_en_strings() {
	$strings = array(
		'Mitka'                                          => 'Mitka',
		'Подарунки з вашим підписом'                     => 'Gifts that carry your words',
		'Безкоштовна доставка Новою Поштою від 1500 грн' => 'Free Nova Poshta delivery from 1500 UAH',
		'Львів, вул. Городоцька, 12'                     => '12 Horodotska St, Lviv',
		'Пн–Пт 9:00–19:00, Сб 10:00–16:00'               => 'Mon–Fri 9:00–19:00, Sat 10:00–16:00',
	);

	$language = PLL()->model->get_language( 'en' );
	$mo       = new PLL_MO();
	$mo->import_from_db( $language );
	foreach ( $strings as $original => $translation ) {
		$mo->add_entry( $mo->make_entry( $original, $translation ) );
	}
	$mo->export_to_db( $language );

	WP_CLI::success( 'English site strings saved.' );
}

$mitka_en = array_merge(
	wp_json_file_decode( $mitka_data_dir . '/products.en.json', array( 'associative' => true ) ),
	wp_json_file_decode( $mitka_data_dir . '/taxonomies.en.json', array( 'associative' => true ) )
);

mitka_en_setup_polylang();
mitka_en_mark_ukrainian();
mitka_en_taxonomies( $mitka_en );
mitka_en_products( $mitka_products, $mitka_en, $mitka_en['labels'] );
mitka_en_menus( mitka_en_pages() );
mitka_en_strings();
foreach ( array( 'uk', 'en' ) as $mitka_lang ) {
	delete_transient( 'mitka_catalog_tree_' . $mitka_lang );
}
