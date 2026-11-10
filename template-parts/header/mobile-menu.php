<?php
defined( 'ABSPATH' ) || exit;

$mitka_tree  = mitka_catalog_tree();
$mitka_phone = mitka_option( 'phone' );
?>
<div class="drawer drawer--left" id="mobile-menu" hidden data-drawer>
	<div class="drawer__backdrop" data-drawer-close></div>
	<div class="drawer__panel" role="dialog" aria-modal="true" aria-labelledby="mobile-menu-title">
		<div class="drawer__head">
			<p class="drawer__title" id="mobile-menu-title"><?php esc_html_e( 'Menu', 'mitka' ); ?></p>
			<button class="icon-btn" type="button" data-drawer-close>
				<?php mitka_the_icon( 'close' ); ?>
				<span class="visually-hidden"><?php esc_html_e( 'Close menu', 'mitka' ); ?></span>
			</button>
		</div>
		<div class="drawer__body">
			<?php if ( $mitka_tree ) : ?>
				<nav class="mobile-menu__catalog" aria-label="<?php esc_attr_e( 'Catalog', 'mitka' ); ?>">
					<?php foreach ( $mitka_tree as $mitka_cat ) : ?>
						<?php if ( $mitka_cat['children'] ) : ?>
							<details class="mobile-menu__group">
								<summary class="mobile-menu__link">
									<?php echo esc_html( $mitka_cat['name'] ); ?>
									<?php mitka_the_icon( 'chevron-down' ); ?>
								</summary>
								<ul class="mobile-menu__sub">
									<li><a href="<?php echo esc_url( $mitka_cat['url'] ); ?>"><?php esc_html_e( 'All in category', 'mitka' ); ?></a></li>
									<?php foreach ( $mitka_cat['children'] as $mitka_child ) : ?>
										<li><a href="<?php echo esc_url( $mitka_child['url'] ); ?>"><?php echo esc_html( $mitka_child['name'] ); ?></a></li>
									<?php endforeach; ?>
								</ul>
							</details>
						<?php else : ?>
							<a class="mobile-menu__link" href="<?php echo esc_url( $mitka_cat['url'] ); ?>">
								<?php echo esc_html( $mitka_cat['name'] ); ?>
								<?php mitka_the_icon( 'chevron-right' ); ?>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location'       => 'primary',
						'container'            => 'nav',
						'container_aria_label' => __( 'Primary', 'mitka' ),
						'menu_class'           => 'mobile-menu__links',
						'depth'                => 1,
					)
				);
			}
			?>
			<?php get_template_part( 'template-parts/header/languages' ); ?>
			<?php if ( $mitka_phone ) : ?>
				<a class="mobile-menu__phone" href="<?php echo esc_url( mitka_phone_href( $mitka_phone ) ); ?>">
					<?php mitka_the_icon( 'phone' ); ?>
					<?php echo esc_html( $mitka_phone ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
