<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'container section page' ); ?>>
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<h1 class="page__title"><?php the_title(); ?></h1>
		<?php $mitka_is_shop_page = function_exists( 'is_woocommerce' ) && ( is_cart() || is_checkout() || is_account_page() ); ?>
		<div class="page__content<?php echo $mitka_is_shop_page ? '' : ' prose'; ?>">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
