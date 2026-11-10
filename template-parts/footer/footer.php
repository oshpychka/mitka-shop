<?php
defined( 'ABSPATH' ) || exit;

$mitka_tree    = mitka_catalog_tree();
$mitka_phone   = mitka_option( 'phone' );
$mitka_email   = mitka_option( 'email' );
$mitka_hours   = mitka_option( 'hours' );
$mitka_address = mitka_option( 'address' );
$mitka_menus   = array(
	'footer-buyers'  => __( 'For buyers', 'mitka' ),
	'footer-company' => __( 'Company', 'mitka' ),
);
?>
<footer class="site-footer">
	<div class="container site-footer__grid">
		<div class="site-footer__brand">
			<?php get_template_part( 'template-parts/header/logo' ); ?>
			<p class="site-footer__about"><?php esc_html_e( 'Engraved gifts and gift boxes from a small workshop in Lviv.', 'mitka' ); ?></p>
			<?php get_template_part( 'template-parts/footer/socials' ); ?>
		</div>
		<?php if ( $mitka_tree ) : ?>
			<nav class="site-footer__col" aria-labelledby="footer-catalog">
				<p class="site-footer__heading" id="footer-catalog"><?php esc_html_e( 'Catalog', 'mitka' ); ?></p>
				<ul class="site-footer__list">
					<?php foreach ( $mitka_tree as $mitka_cat ) : ?>
						<li><a href="<?php echo esc_url( $mitka_cat['url'] ); ?>"><?php echo esc_html( $mitka_cat['name'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>
		<?php foreach ( $mitka_menus as $mitka_location => $mitka_title ) : ?>
			<?php if ( has_nav_menu( $mitka_location ) ) : ?>
				<nav class="site-footer__col" aria-labelledby="<?php echo esc_attr( $mitka_location ); ?>">
					<p class="site-footer__heading" id="<?php echo esc_attr( $mitka_location ); ?>"><?php echo esc_html( $mitka_title ); ?></p>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => $mitka_location,
							'container'      => false,
							'menu_class'     => 'site-footer__list',
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>
		<?php endforeach; ?>
		<div class="site-footer__col">
			<p class="site-footer__heading"><?php esc_html_e( 'Contacts', 'mitka' ); ?></p>
			<ul class="site-footer__list site-footer__contacts">
				<?php if ( $mitka_phone ) : ?>
					<li><a class="nowrap" href="<?php echo esc_url( mitka_phone_href( $mitka_phone ) ); ?>"><?php echo esc_html( $mitka_phone ); ?></a></li>
				<?php endif; ?>
				<?php if ( $mitka_email ) : ?>
					<li><a href="mailto:<?php echo esc_attr( antispambot( $mitka_email ) ); ?>"><?php echo esc_html( antispambot( $mitka_email ) ); ?></a></li>
				<?php endif; ?>
				<?php if ( $mitka_hours ) : ?>
					<li class="site-footer__muted"><?php echo esc_html( $mitka_hours ); ?></li>
				<?php endif; ?>
				<?php if ( $mitka_address ) : ?>
					<li class="site-footer__muted"><?php echo esc_html( $mitka_address ); ?></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
	<div class="container site-footer__bottom">
		<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> Mitka</p>
		<p><?php esc_html_e( 'Payment on delivery via Nova Poshta', 'mitka' ); ?></p>
	</div>
</footer>
