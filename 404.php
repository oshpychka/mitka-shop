<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="container section not-found">
	<p class="not-found__code" aria-hidden="true">404</p>
	<h1 class="not-found__title"><?php esc_html_e( 'This page is not here', 'mitka' ); ?></h1>
	<p class="not-found__lead"><?php esc_html_e( 'The link may be old or have a typo. Try the search or start from the catalog.', 'mitka' ); ?></p>
	<div class="not-found__search"><?php get_template_part( 'template-parts/header/search', null, array( 'id' => 'not-found-search' ) ); ?></div>
	<div class="not-found__actions">
		<a class="btn btn--accent" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Go to catalog', 'mitka' ); ?></a>
		<a class="btn btn--secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home page', 'mitka' ); ?></a>
	</div>
</section>
<?php
get_footer();
