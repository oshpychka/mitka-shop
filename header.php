<?php
defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'mitka' ); ?></a>
<?php get_template_part( 'template-parts/header/announcement' ); ?>
<header class="site-header" data-header>
	<div class="container site-header__row">
		<button class="icon-btn site-header__burger" type="button" aria-controls="mobile-menu" aria-expanded="false" data-drawer-open="mobile-menu">
			<?php mitka_the_icon( 'menu' ); ?>
			<span class="visually-hidden"><?php esc_html_e( 'Open menu', 'mitka' ); ?></span>
		</button>
		<?php get_template_part( 'template-parts/header/logo' ); ?>
		<button class="btn btn--primary site-header__catalog" type="button" aria-controls="mega-menu" aria-expanded="false" data-mega-toggle>
			<?php mitka_the_icon( 'grid' ); ?>
			<?php esc_html_e( 'Catalog', 'mitka' ); ?>
		</button>
		<?php get_template_part( 'template-parts/header/search' ); ?>
		<?php get_template_part( 'template-parts/header/actions' ); ?>
	</div>
	<?php if ( has_nav_menu( 'primary' ) ) : ?>
		<nav class="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'mitka' ); ?>">
			<div class="container">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'site-nav__list',
						'depth'          => 1,
					)
				);
				?>
			</div>
		</nav>
	<?php endif; ?>
	<?php get_template_part( 'template-parts/header/mega-menu' ); ?>
</header>
<?php get_template_part( 'template-parts/header/mobile-menu' ); ?>
<main id="main" class="site-main" tabindex="-1">
