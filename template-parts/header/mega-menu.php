<?php
defined( 'ABSPATH' ) || exit;

$mitka_tree = mitka_catalog_tree();
if ( ! $mitka_tree ) {
	return;
}
?>
<div class="mega-menu" id="mega-menu" hidden data-mega-menu>
	<div class="container mega-menu__grid">
		<?php foreach ( $mitka_tree as $mitka_cat ) : ?>
			<div class="mega-menu__group">
				<a class="mega-menu__title" href="<?php echo esc_url( $mitka_cat['url'] ); ?>"><?php echo esc_html( $mitka_cat['name'] ); ?></a>
				<?php if ( $mitka_cat['children'] ) : ?>
					<ul class="mega-menu__list">
						<?php foreach ( $mitka_cat['children'] as $mitka_child ) : ?>
							<li><a href="<?php echo esc_url( $mitka_child['url'] ); ?>"><?php echo esc_html( $mitka_child['name'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
