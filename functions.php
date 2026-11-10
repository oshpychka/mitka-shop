<?php

defined( 'ABSPATH' ) || exit;

define( 'MITKA_DIR', get_template_directory() );
define( 'MITKA_URI', get_template_directory_uri() );

require MITKA_DIR . '/inc/setup.php';
require MITKA_DIR . '/inc/cleanup.php';
require MITKA_DIR . '/inc/security.php';
require MITKA_DIR . '/inc/enqueue.php';
require MITKA_DIR . '/inc/helpers.php';
require MITKA_DIR . '/inc/i18n.php';
require MITKA_DIR . '/inc/options.php';
require MITKA_DIR . '/inc/taxonomies.php';
require MITKA_DIR . '/inc/menus.php';
require MITKA_DIR . '/inc/woocommerce.php';
require MITKA_DIR . '/inc/engraving.php';
require MITKA_DIR . '/inc/checkout.php';
