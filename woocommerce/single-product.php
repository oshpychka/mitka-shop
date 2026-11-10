<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	global $product;

	$mitka_maker     = mitka_product_maker( $product );
	$mitka_days      = (int) $product->get_meta( 'production_days' );
	$mitka_materials = wc_get_product_terms( $product->get_id(), 'pa_material', array( 'fields' => 'names' ) );
	$mitka_sale      = mitka_sale_percent( $product );
	?>
	<div class="container section">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php woocommerce_output_all_notices(); ?>

		<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'product-page', $product ); ?>>
			<div class="product-page__media">
				<div class="product-page__photo">
					<?php
					echo $product->get_image_id()
						? wp_get_attachment_image( $product->get_image_id(), 'woocommerce_single', false, array( 'fetchpriority' => 'high' ) )
						: wp_kses_post( wc_placeholder_img( 'woocommerce_single' ) );
					?>
					<?php if ( $mitka_sale ) : ?>
						<span class="badge badge--sale product-page__badge">&minus;<?php echo esc_html( $mitka_sale ); ?>%</span>
					<?php endif; ?>
				</div>
			</div>

			<div class="product-page__summary">
				<?php if ( $mitka_maker ) : ?>
					<a class="product-page__maker" href="<?php echo esc_url( get_term_link( $mitka_maker ) ); ?>"><?php echo esc_html( $mitka_maker->name ); ?></a>
				<?php endif; ?>
				<h1 class="product-page__title"><?php the_title(); ?></h1>
				<p class="price product-page__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>

				<?php if ( $product->get_short_description() ) : ?>
					<div class="product-page__short"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
				<?php endif; ?>

				<?php woocommerce_template_single_add_to_cart(); ?>

				<ul class="product-page__facts">
					<li>
						<?php mitka_the_icon( 'pen' ); ?>
						<span>
							<?php
							if ( $mitka_days > 0 ) {
								/* translators: %d: number of working days. */
								echo esc_html( sprintf( _n( 'Made and engraved in %d working day', 'Made and engraved in %d working days', $mitka_days, 'mitka' ), $mitka_days ) );
							} else {
								esc_html_e( 'In stock, ships the next working day', 'mitka' );
							}
							?>
						</span>
					</li>
					<li><?php mitka_the_icon( 'bag' ); ?><span><?php esc_html_e( 'Nova Poshta delivery across Ukraine, free from 1500 UAH', 'mitka' ); ?></span></li>
					<li><?php mitka_the_icon( 'user' ); ?><span><?php esc_html_e( 'Pay on delivery after you have checked the parcel', 'mitka' ); ?></span></li>
				</ul>

				<?php if ( $mitka_materials ) : ?>
					<p class="product-page__material">
						<?php esc_html_e( 'Material:', 'mitka' ); ?>
						<strong><?php echo esc_html( implode( ', ', $mitka_materials ) ); ?></strong>
					</p>
				<?php endif; ?>
			</div>
		</div>

		<div class="product-page__details">
			<?php if ( get_the_content() ) : ?>
				<section class="product-page__description" aria-labelledby="product-description">
					<h2 id="product-description"><?php esc_html_e( 'About this item', 'mitka' ); ?></h2>
					<div class="prose"><?php the_content(); ?></div>
				</section>
			<?php endif; ?>
			<?php if ( $mitka_maker ) : ?>
				<aside class="product-page__maker-card" aria-label="<?php esc_attr_e( 'Workshop', 'mitka' ); ?>">
					<p class="eyebrow"><?php esc_html_e( 'Who made it', 'mitka' ); ?></p>
					<?php get_template_part( 'template-parts/maker-card', null, array( 'term' => $mitka_maker ) ); ?>
				</aside>
			<?php endif; ?>
		</div>

		<?php woocommerce_output_related_products(); ?>
	</div>
	<?php
endwhile;

get_footer();
