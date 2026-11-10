<?php

defined( 'WP_CLI' ) || exit;

$mitka_data_dir   = __DIR__ . '/data';
$mitka_taxonomies = wp_json_file_decode( $mitka_data_dir . '/taxonomies.json', array( 'associative' => true ) );
$mitka_products   = wp_json_file_decode( $mitka_data_dir . '/products.json', array( 'associative' => true ) );
$mitka_photos     = file_exists( $mitka_data_dir . '/photos.json' )
	? wp_json_file_decode( $mitka_data_dir . '/photos.json', array( 'associative' => true ) )
	: array();

$mitka_materials = array(
	'oak'       => 'Дуб',
	'walnut'    => 'Горіх',
	'beech'     => 'Бук',
	'acacia'    => 'Акація',
	'bamboo'    => 'Бамбук',
	'plywood'   => 'Фанера',
	'leather'   => 'Шкіра',
	'canvas'    => 'Бавовна',
	'linen'     => 'Льон',
	'steel'     => 'Сталь',
	'glass'     => 'Скло',
	'ceramic'   => 'Кераміка',
	'acrylic'   => 'Акрил',
	'honey'     => 'Мед',
	'coffee'    => 'Кава',
	'tea'       => 'Чай',
	'chocolate' => 'Шоколад',
	'candle'    => 'Віск',
	'box'       => 'Набір',
);

$mitka_variation_labels = array(
	'size'     => 'Розмір',
	'color'    => 'Колір',
	'pattern'  => 'Візерунок',
	'paper'    => 'Папір',
	'material' => 'Матеріал',
	'scent'    => 'Аромат',
);

function mitka_seed_language() {
	WP_CLI::runcommand( 'language core install uk --activate', array( 'return' => true ) );
	WP_CLI::runcommand( 'language plugin install --all uk', array( 'return' => true ) );
	WP_CLI::success( 'Ukrainian language packs installed.' );
}

function mitka_seed_settings() {
	$settings = array(
		'woocommerce_currency'                         => 'UAH',
		'woocommerce_currency_pos'                     => 'right_space',
		'woocommerce_price_thousand_sep'               => ' ',
		'woocommerce_price_decimal_sep'                => ',',
		'woocommerce_price_num_decimals'               => 0,
		'woocommerce_default_country'                  => 'UA:UA30',
		'woocommerce_store_city'                       => 'Львів',
		'woocommerce_allowed_countries'                => 'specific',
		'woocommerce_specific_allowed_countries'       => array( 'UA' ),
		'woocommerce_ship_to_countries'                => 'specific',
		'woocommerce_specific_ship_to_countries'       => array( 'UA' ),
		'woocommerce_enable_guest_checkout'            => 'yes',
		'woocommerce_enable_signup_and_login_from_checkout' => 'yes',
		'woocommerce_enable_myaccount_registration'    => 'yes',
		'woocommerce_enable_reviews'                   => 'yes',
		'woocommerce_review_rating_required'           => 'yes',
		'woocommerce_coming_soon'                      => 'no',
		'woocommerce_checkout_phone_field'             => 'required',
		'woocommerce_checkout_company_field'           => 'hidden',
		'woocommerce_checkout_address_2_field'         => 'hidden',
		'woocommerce_checkout_terms_and_conditions_checkbox_text' => 'Я приймаю [terms]',
		'woocommerce_checkout_privacy_policy_text'     => 'Ваші дані потрібні лише для того, щоб виконати й доставити замовлення. Детальніше: [privacy_policy].',
		'woocommerce_registration_privacy_policy_text' => 'Ваші дані потрібні для роботи кабінету. Детальніше: [privacy_policy].',
		'woocommerce_weight_unit'                      => 'kg',
		'woocommerce_dimension_unit'                   => 'cm',
		'woocommerce_permalinks'                       => array(
			'product_base'  => 'product',
			'category_base' => 'category',
			'tag_base'      => 'tag',
		),
		'permalink_structure'                          => '/%postname%/',
		'blogname'                                     => 'Mitka',
		'blogdescription'                              => 'Подарунки з вашим підписом',
		'timezone_string'                              => 'Europe/Kyiv',
		'date_format'                                  => 'd.m.Y',
		'time_format'                                  => 'H:i',
		'start_of_week'                                => 1,
		'mitka_options'                                => array(
			'phone'        => '+380 67 000 14 27',
			'email'        => 'hello@mitka.shop',
			'address'      => 'Львів, вул. Городоцька, 12',
			'hours'        => 'Пн–Пт 9:00–19:00, Сб 10:00–16:00',
			'instagram'    => 'https://instagram.com/',
			'facebook'     => 'https://facebook.com/',
			'telegram'     => 'https://t.me/',
			'announcement' => 'Безкоштовна доставка Новою Поштою від 1500 грн',
		),
	);

	foreach ( $settings as $key => $value ) {
		update_option( $key, $value );
	}

	update_option(
		'woocommerce_cod_settings',
		array(
			'enabled'            => 'yes',
			'title'              => 'Оплата при отриманні',
			'description'        => 'Оплачуєте замовлення у відділенні Нової Пошти після огляду.',
			'instructions'       => 'Ми напишемо, коли замовлення буде готове до відправки.',
			'enable_for_methods' => array(),
			'enable_for_virtual' => 'yes',
		)
	);

	WP_CLI::runcommand( 'wc tool run install_pages --user=1', array( 'return' => true ) );
	$pages = array(
		'shop'      => array( 'Каталог', 'catalog', null ),
		'cart'      => array( 'Кошик', 'cart', '[woocommerce_cart]' ),
		'checkout'  => array( 'Оформлення замовлення', 'checkout', '[woocommerce_checkout]' ),
		'myaccount' => array( 'Мій кабінет', 'my-account', '[woocommerce_my_account]' ),
	);
	foreach ( $pages as $page => list( $title, $slug, $content ) ) {
		$page_id = wc_get_page_id( $page );
		if ( $page_id <= 0 ) {
			continue;
		}
		$update = array(
			'ID'         => $page_id,
			'post_title' => $title,
			'post_name'  => $slug,
		);
		// Classic shortcodes instead of blocks: the theme styles them.
		if ( $content ) {
			$update['post_content'] = $content;
		}
		wp_update_post( $update );
	}

	update_option( 'woocommerce_ship_to_destination', 'billing_only' );
	mitka_seed_shipping();

	WP_CLI::success( 'Store settings applied.' );
}

function mitka_seed_shipping() {
	foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
		if ( 'Україна' === $zone['zone_name'] ) {
			return;
		}
	}

	$zone = new WC_Shipping_Zone();
	$zone->set_zone_name( 'Україна' );
	$zone->add_location( 'UA', 'country' );
	$zone->save();

	$methods = array(
		'free_shipping' => array(
			'title'      => 'Нова Пошта, безкоштовно',
			'requires'   => 'min_amount',
			'min_amount' => '1500',
		),
		'flat_rate'     => array(
			'title' => 'Нова Пошта, за тарифами перевізника',
			'cost'  => '0',
		),
	);
	foreach ( $methods as $type => $settings ) {
		$instance_id = $zone->add_shipping_method( $type );
		update_option( "woocommerce_{$type}_{$instance_id}_settings", array_merge( array( 'enabled' => 'yes' ), $settings ) );
	}
}

function mitka_seed_term( $taxonomy, $slug, $name, $args = array() ) {
	$existing = get_term_by( 'slug', $slug, $taxonomy );
	if ( $existing ) {
		if ( isset( $args['description'] ) && $existing->description !== $args['description'] ) {
			wp_update_term( $existing->term_id, $taxonomy, array( 'description' => $args['description'] ) );
		}
		return $existing->term_id;
	}
	$term = wp_insert_term( $name, $taxonomy, array_merge( array( 'slug' => $slug ), $args ) );
	if ( is_wp_error( $term ) ) {
		WP_CLI::error( $term->get_error_message() );
	}
	return $term['term_id'];
}

function mitka_seed_taxonomies( $data ) {
	foreach ( $data['categories'] as $index => $category ) {
		$term_id = mitka_seed_term( 'product_cat', $category['slug'], $category['name'], array( 'description' => $category['description'] ) );
		update_term_meta( $term_id, 'order', $index );
	}

	foreach ( $data['makers'] as $maker ) {
		$term_id = mitka_seed_term( 'mitka_maker', $maker['slug'], $maker['name'], array( 'description' => $maker['story'] ) );
		update_term_meta( $term_id, 'city', $maker['city'] );
		update_term_meta( $term_id, 'founded', $maker['founded'] );
	}

	foreach ( $data['occasions'] as $index => $occasion ) {
		$term_id = mitka_seed_term( 'mitka_occasion', $occasion['slug'], $occasion['name'] );
		update_term_meta( $term_id, 'order', $index );
	}

	WP_CLI::success( 'Categories, makers and occasions created.' );
}

function mitka_seed_material_attribute( $materials ) {
	if ( ! wc_attribute_taxonomy_id_by_name( 'material' ) ) {
		wc_create_attribute(
			array(
				'name'         => 'Матеріал',
				'slug'         => 'material',
				'type'         => 'select',
				'has_archives' => false,
			)
		);
		register_taxonomy( 'pa_material', 'product' );
	}

	foreach ( $materials as $slug => $name ) {
		mitka_seed_term( 'pa_material', $slug, $name );
	}
}

function mitka_term_ids( $taxonomy, $slugs ) {
	return array_map(
		fn( $slug ) => get_term_by( 'slug', $slug, $taxonomy )->term_id,
		(array) $slugs
	);
}

function mitka_seed_product( $item, $labels ) {
	if ( get_page_by_path( $item['slug'], OBJECT, 'product' ) ) {
		return;
	}

	$is_variable = ! empty( $item['variations'] );
	$product     = $is_variable ? new WC_Product_Variable() : new WC_Product_Simple();

	$product->set_name( $item['name'] );
	$product->set_slug( $item['slug'] );
	$product->set_status( 'publish' );
	$product->set_short_description( $item['short'] );
	$product->set_description( $item['description'] );
	$product->set_category_ids( mitka_term_ids( 'product_cat', $item['cat'] ) );
	$product->set_sku( 'MT-' . strtoupper( substr( md5( $item['slug'] ), 0, 6 ) ) );
	$product->set_manage_stock( false );
	$product->set_stock_status( 'instock' );

	$material = new WC_Product_Attribute();
	$material->set_id( wc_attribute_taxonomy_id_by_name( 'material' ) );
	$material->set_name( 'pa_material' );
	$material->set_options( mitka_term_ids( 'pa_material', $item['material'] ) );
	$material->set_visible( true );
	$attributes = array( $material );

	if ( $is_variable ) {
		$key    = array_key_first( array_diff_key( $item['variations'][0], array( 'price' => true ) ) );
		$option = new WC_Product_Attribute();
		$option->set_name( $labels[ $key ] );
		$option->set_options( array_column( $item['variations'], $key ) );
		$option->set_visible( true );
		$option->set_variation( true );
		$attributes[] = $option;
	} else {
		$product->set_regular_price( $item['price'] );
		if ( ! empty( $item['sale'] ) ) {
			$product->set_sale_price( $item['sale'] );
		}
	}

	$product->set_attributes( $attributes );
	$product->update_meta_data( '_mitka_engraving', $item['engraving'] ? 'yes' : 'no' );
	$product->update_meta_data( 'production_days', $item['days'] );
	if ( ! empty( $item['cells'] ) ) {
		$product->update_meta_data( 'box_cells', $item['cells'] );
	}
	$product_id = $product->save();

	wp_set_object_terms( $product_id, $item['maker'], 'mitka_maker' );
	wp_set_object_terms( $product_id, $item['occasions'], 'mitka_occasion' );

	if ( $is_variable ) {
		$attribute_key = sanitize_title( $labels[ $key ] );
		foreach ( $item['variations'] as $row ) {
			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $product_id );
			$variation->set_attributes( array( $attribute_key => $row[ $key ] ) );
			$variation->set_regular_price( $row['price'] );
			$variation->set_stock_status( 'instock' );
			$variation->save();
		}
		$product->set_default_attributes( array( $attribute_key => $item['variations'][0][ $key ] ) );
		$product->save();
		WC_Product_Variable::sync( $product_id );
	}
}

function mitka_seed_photo( $slug, $photo ) {
	$post = get_page_by_path( $slug, OBJECT, 'product' );
	if ( ! $post ) {
		return false;
	}

	$current = get_post_thumbnail_id( $post );
	if ( $current ) {
		$current_photo = get_post_meta( $current, '_mitka_unsplash', true );
		if ( ( $current_photo['id'] ?? '' ) === $photo['id'] ) {
			return false;
		}
		wp_delete_attachment( $current, true );
	}

	$tmp = download_url( $photo['url'] . '&w=1200&h=1200&fit=crop&fm=jpg&q=80' );
	if ( is_wp_error( $tmp ) ) {
		WP_CLI::warning( "{$slug}: " . $tmp->get_error_message() );
		return false;
	}

	$attachment_id = media_handle_sideload(
		array(
			'name'     => $slug . '.jpg',
			'tmp_name' => $tmp,
		),
		$post->ID,
		$post->post_title,
		array( 'post_excerpt' => sprintf( 'Фото: %s / Unsplash', $photo['author'] ) )
	);
	if ( is_wp_error( $attachment_id ) ) {
		wp_delete_file( $tmp );
		WP_CLI::warning( "{$slug}: " . $attachment_id->get_error_message() );
		return false;
	}

	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $photo['alt'] ? $photo['alt'] : $post->post_title );
	update_post_meta( $attachment_id, '_mitka_unsplash', $photo );
	set_post_thumbnail( $post, $attachment_id );
	return true;
}

function mitka_seed_photos( $photos ) {
	if ( ! $photos ) {
		WP_CLI::log( 'No scripts/data/photos.json yet, skipping photos (run npm run photos).' );
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$added = 0;
	foreach ( $photos as $slug => $photo ) {
		$added += (int) mitka_seed_photo( $slug, $photo );
	}
	WP_CLI::success( "{$added} product photos added or replaced." );
}

function mitka_seed_menu( $location, $name, $items ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		return;
	}
	$menu_id = wp_create_nav_menu( $name );
	foreach ( $items as $item ) {
		wp_update_nav_menu_item( $menu_id, 0, array_merge( array( 'menu-item-status' => 'publish' ), $item ) );
	}
	$locations              = get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

function mitka_seed_pages() {
	$pages = array(
		'delivery' => array(
			'Доставка і оплата',
			"<!-- wp:paragraph --><p>Відправляємо Новою Поштою по всій Україні: у відділення або поштомат.</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2>Терміни</h2><!-- /wp:heading -->\n<!-- wp:list --><ul><li>Товари з гравіюванням виготовляємо за 1–2 робочі дні.</li><li>Наповнення для боксів без гравіювання відправляємо наступного робочого дня.</li><li>Доставка Новою Поштою зазвичай займає 1–3 дні.</li></ul><!-- /wp:list -->\n<!-- wp:heading --><h2>Вартість</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>За тарифами перевізника. Від 1500 грн доставка безкоштовна.</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2>Оплата</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>Оплачуєте у відділенні після того, як оглянули посилку. Передоплата не потрібна.</p><!-- /wp:paragraph -->",
		),
		'returns'  => array(
			'Повернення та обмін',
			"<!-- wp:paragraph --><p>Товари без гравіювання можна повернути або обміняти протягом 14 днів, якщо вони не були у використанні.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Речі з гравіюванням зроблені саме для вас, тому їх не повертаємо, окрім випадків, коли є брак або помилка в написі з нашого боку. Тоді переробимо безкоштовно.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Щоб оформити повернення, напишіть нам номер замовлення та фото товару.</p><!-- /wp:paragraph -->",
		),
		'about'    => array(
			'Про нас',
			"<!-- wp:paragraph --><p>Mitka — лазерна майстерня у Львові. Ми гравіюємо імена, дати й побажання на речах від невеликих українських майстерень і збираємо з них подарункові бокси.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Кожну річ перевіряємо руками перед гравіюванням і перед відправкою. Якщо щось не так, переробляємо.</p><!-- /wp:paragraph -->",
		),
		'contacts' => array(
			'Контакти',
			"<!-- wp:paragraph --><p>Пишіть або телефонуйте, якщо хочете зібрати бокс під себе, замовити гравіювання для команди чи уточнити деталі замовлення.</p><!-- /wp:paragraph -->\n<!-- wp:list --><ul><li>Телефон: +380&nbsp;67&nbsp;000&nbsp;14&nbsp;27</li><li>Пошта: hello@mitka.shop</li><li>Майстерня: Львів, вул. Городоцька, 12</li><li>Пн–Пт 9:00–19:00, Сб 10:00–16:00</li></ul><!-- /wp:list -->",
		),
		'privacy'  => array(
			'Політика конфіденційності',
			"<!-- wp:paragraph --><p>Ми збираємо лише те, що потрібно для виконання замовлення: ім’я, телефон, email, місто й відділення Нової Пошти.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Ці дані бачать тільки працівники майстерні та служба доставки. Ми не передаємо їх рекламним мережам і не продаємо.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Щоб видалити свої дані, напишіть на hello@mitka.shop.</p><!-- /wp:paragraph -->",
		),
		'terms'    => array(
			'Умови використання',
			'<!-- wp:paragraph --><p>Оформлюючи замовлення, ви погоджуєтеся з умовами доставки, оплати та повернення, описаними на сайті. Текст гравіювання ви перевіряєте самі перед оформленням: ми гравіюємо його так, як він написаний.</p><!-- /wp:paragraph -->',
		),
	);

	$ids = array();
	foreach ( $pages as $slug => list( $title, $content ) ) {
		$existing     = get_page_by_path( $slug );
		$ids[ $slug ] = $existing ? $existing->ID : wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => $slug,
				'post_title'   => $title,
				'post_content' => $content,
			)
		);
	}

	update_option( 'wp_page_for_privacy_policy', $ids['privacy'] );
	update_option( 'woocommerce_terms_page_id', $ids['terms'] );

	$page_item = fn( $slug ) => array(
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $ids[ $slug ],
		'menu-item-type'      => 'post_type',
	);
	mitka_seed_menu( 'footer-buyers', 'Покупцям', array( $page_item( 'delivery' ), $page_item( 'returns' ), $page_item( 'privacy' ) ) );
	mitka_seed_menu( 'footer-company', 'Компанія', array( $page_item( 'about' ), $page_item( 'contacts' ), $page_item( 'terms' ) ) );

	WP_CLI::success( 'Pages and footer menus created.' );
}

function mitka_seed_menus() {
	$occasion = fn( $slug, $title ) => array(
		'menu-item-title'     => $title,
		'menu-item-object'    => 'mitka_occasion',
		'menu-item-object-id' => get_term_by( 'slug', $slug, 'mitka_occasion' )->term_id,
		'menu-item-type'      => 'taxonomy',
	);

	mitka_seed_menu(
		'primary',
		'Головне меню',
		array(
			array(
				'menu-item-title'     => 'Готові бокси',
				'menu-item-object'    => 'product_cat',
				'menu-item-object-id' => get_term_by( 'slug', 'boxes', 'product_cat' )->term_id,
				'menu-item-type'      => 'taxonomy',
			),
			$occasion( 'for-him', 'Для нього' ),
			$occasion( 'for-her', 'Для неї' ),
			$occasion( 'colleagues', 'Колегам' ),
			$occasion( 'wedding', 'Весілля' ),
			$occasion( 'new-home', 'Новосілля' ),
		)
	);

	WP_CLI::success( 'Menus created.' );
}

mitka_seed_language();
mitka_seed_settings();

// WordPress sample content: "Hello world", "Sample Page" and the draft privacy page.
wp_delete_post( 1, true );
foreach ( array( 'sample-page', 'privacy-policy' ) as $mitka_sample ) {
	$mitka_page = get_page_by_path( $mitka_sample );
	if ( $mitka_page && 'en' !== ( function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $mitka_page->ID ) : '' ) ) {
		wp_delete_post( $mitka_page->ID, true );
	}
}
mitka_seed_taxonomies( $mitka_taxonomies );
mitka_seed_material_attribute( $mitka_materials );

foreach ( $mitka_products as $mitka_item ) {
	mitka_seed_product( $mitka_item, $mitka_variation_labels );
}
WP_CLI::success( count( $mitka_products ) . ' products in catalog.' );

mitka_seed_photos( $mitka_photos );

mitka_seed_menus();
mitka_seed_pages();
require __DIR__ . '/seed-en.php';
flush_rewrite_rules();
