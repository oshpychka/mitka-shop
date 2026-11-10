<?php
defined( 'ABSPATH' ) || exit;

get_header();

$mitka_term       = is_product_taxonomy() ? get_queried_object() : null;
$mitka_is_maker   = $mitka_term && 'mitka_maker' === $mitka_term->taxonomy;
$mitka_categories = mitka_top_categories();
$mitka_shop_url   = mitka_shop_url();
?>
<div class="container section catalog">
	<?php get_template_part( 'template-parts/breadcrumbs' ); ?>

	<header class="catalog__head">
		<?php if ( $mitka_is_maker ) : ?>
			<p class="eyebrow"><?php esc_html_e( 'Workshop', 'mitka' ); ?></p>
		<?php endif; ?>
		<h1 class="catalog__title"><?php woocommerce_page_title(); ?></h1>
		<?php if ( $mitka_term && $mitka_term->description ) : ?>
			<p class="catalog__lead"><?php echo esc_html( $mitka_term->description ); ?></p>
		<?php endif; ?>
		<?php if ( $mitka_is_maker ) : ?>
			<p class="catalog__meta">
				<?php
				$mitka_city    = get_term_meta( $mitka_term->term_id, 'city', true );
				$mitka_founded = get_term_meta( $mitka_term->term_id, 'founded', true );
				echo esc_html(
					implode(
						' · ',
						array_filter(
							array(
								$mitka_city,
								/* translators: %s: year the workshop was founded. */
								$mitka_founded ? sprintf( __( 'since %s', 'mitka' ), $mitka_founded ) : '',
							)
						)
					)
				);
				?>
			</p>
		<?php endif; ?>
	</header>

	<?php if ( $mitka_categories && ! $mitka_is_maker ) : ?>
		<nav class="catalog__cats" aria-label="<?php esc_attr_e( 'Categories', 'mitka' ); ?>">
			<ul class="chips">
				<?php $mitka_all = is_shop() && ! is_search(); ?>
				<li><a class="chip<?php echo $mitka_all ? ' is-active' : ''; ?>" href="<?php echo esc_url( $mitka_shop_url ); ?>"<?php echo $mitka_all ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'All', 'mitka' ); ?></a></li>
				<?php foreach ( $mitka_categories as $mitka_cat ) : ?>
					<?php $mitka_current = $mitka_term && $mitka_term->term_id === $mitka_cat->term_id; ?>
					<li><a class="chip<?php echo $mitka_current ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $mitka_cat ) ); ?>"<?php echo $mitka_current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $mitka_cat->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>

	<?php woocommerce_output_all_notices(); ?>

	<?php if ( woocommerce_product_loop() ) : ?>
		<div class="catalog__toolbar">
			<?php
			woocommerce_result_count();
			woocommerce_catalog_ordering();
			?>
		</div>
		<?php
		woocommerce_product_loop_start();
		while ( have_posts() ) {
			the_post();
			wc_get_template_part( 'content', 'product' );
		}
		woocommerce_product_loop_end();
		woocommerce_pagination();
		?>
	<?php else : ?>
		<div class="empty-state">
			<p class="empty-state__title"><?php esc_html_e( 'Nothing found', 'mitka' ); ?></p>
			<p><?php esc_html_e( 'Try a shorter word, check the spelling, or pick a category above.', 'mitka' ); ?></p>
		</div>
		<?php
		$mitka_suggest = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => 4,
				'orderby' => 'RAND(14)',
			)
		);
		?>
		<?php if ( $mitka_suggest ) : ?>
			<h2 class="catalog__suggest-title"><?php esc_html_e( 'You might like', 'mitka' ); ?></h2>
			<ul class="products">
				<?php foreach ( $mitka_suggest as $mitka_product ) : ?>
					<li><?php get_template_part( 'template-parts/product/card', null, array( 'product' => $mitka_product ) ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	<?php endif; ?>
</div>
<?php
get_footer();
