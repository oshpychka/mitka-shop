<?php
defined( 'ABSPATH' ) || exit;

$mitka_product = $args['product'];
$mitka_makers  = get_the_terms( $mitka_product->get_id(), 'mitka_maker' );
$mitka_maker   = $mitka_makers && ! is_wp_error( $mitka_makers ) ? $mitka_makers[0]->name : '';
$mitka_sale    = mitka_sale_percent( $mitka_product );
$mitka_engrave = mitka_is_engravable( $mitka_product );
?>
<article class="card">
	<div class="card__media">
		<?php echo wp_kses_post( $mitka_product->get_image( 'mitka-card', array( 'loading' => 'lazy' ) ) ); ?>
		<?php if ( $mitka_sale || $mitka_engrave ) : ?>
			<div class="card__badges">
				<?php if ( $mitka_sale ) : ?>
					<span class="badge badge--sale">&minus;<?php echo esc_html( $mitka_sale ); ?>%</span>
				<?php endif; ?>
				<?php if ( $mitka_engrave ) : ?>
					<span class="badge badge--light"><?php esc_html_e( 'Engraving', 'mitka' ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php if ( $mitka_maker ) : ?>
		<p class="card__meta"><?php echo esc_html( $mitka_maker ); ?></p>
	<?php endif; ?>
	<h3 class="card__title">
		<a class="card__link" href="<?php echo esc_url( $mitka_product->get_permalink() ); ?>"><?php echo esc_html( $mitka_product->get_name() ); ?></a>
	</h3>
	<div class="card__foot">
		<span class="price card__price"><?php echo wp_kses_post( $mitka_product->get_price_html() ); ?></span>
		<?php if ( $mitka_product->is_type( 'simple' ) && $mitka_product->is_purchasable() && $mitka_product->is_in_stock() && ! $mitka_engrave ) : ?>
			<a class="icon-btn card__add" href="<?php echo esc_url( $mitka_product->add_to_cart_url() ); ?>" rel="nofollow">
				<?php mitka_the_icon( 'bag' ); ?>
				<span class="visually-hidden"><?php esc_html_e( 'Add to cart:', 'mitka' ); ?> <?php echo esc_html( $mitka_product->get_name() ); ?></span>
			</a>
		<?php endif; ?>
	</div>
</article>
