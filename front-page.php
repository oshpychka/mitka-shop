<?php
defined( 'ABSPATH' ) || exit;

get_header();

$mitka_categories = mitka_top_categories();
$mitka_boards     = mitka_term( 'boards', 'product_cat' );
$mitka_hero_image = $mitka_boards ? mitka_term_image_id( $mitka_boards ) : 0;
$mitka_boxes      = mitka_term( 'boxes', 'product_cat' );
$mitka_shop_url   = mitka_shop_url();
$mitka_occasions  = get_terms(
	array(
		'taxonomy'   => 'mitka_occasion',
		'hide_empty' => true,
		'orderby'    => 'meta_value_num',
		'meta_key'   => 'order', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- a handful of terms.
	)
);
$mitka_popular    = wc_get_products(
	array(
		'status'  => 'publish',
		'limit'   => 8,
		'orderby' => 'RAND(14)',
	)
);
$mitka_box_items  = $mitka_boxes ? wc_get_products(
	array(
		'status'   => 'publish',
		'limit'    => 4,
		'orderby'  => 'ID',
		'order'    => 'ASC',
		'category' => array( $mitka_boxes->slug ),
	)
) : array();
$mitka_makers     = get_terms(
	array(
		'taxonomy'   => 'mitka_maker',
		'hide_empty' => true,
		'exclude'    => array_filter( array( mitka_term( 'mitka', 'mitka_maker' )->term_id ?? 0 ) ),
		'number'     => 6,
	)
);
$mitka_steps      = array(
	array(
		'title' => __( 'Pick a gift', 'mitka' ),
		'text'  => __( 'Boards, leather, glass and ready boxes from small Ukrainian workshops.', 'mitka' ),
	),
	array(
		'title' => __( 'Add your words', 'mitka' ),
		'text'  => __( 'Type a name, a date or a few warm words and see right away how the engraving will look.', 'mitka' ),
	),
	array(
		'title' => __( 'We engrave and ship', 'mitka' ),
		'text'  => __( 'Ready in 1–2 days, sent by Nova Poshta. You pay on delivery after checking it.', 'mitka' ),
	),
);
?>
<section class="hero">
	<div class="container hero__grid">
		<div class="hero__text">
			<p class="eyebrow"><?php esc_html_e( 'Laser workshop in Lviv', 'mitka' ); ?></p>
			<h1 class="hero__title"><?php esc_html_e( 'Gifts that carry your words', 'mitka' ); ?></h1>
			<p class="hero__lead"><?php esc_html_e( 'We engrave names, dates and wishes on things made by Ukrainian craftspeople, and pack them into boxes that are a joy to open.', 'mitka' ); ?></p>
			<div class="hero__actions">
				<a class="btn btn--accent" href="<?php echo esc_url( $mitka_shop_url ); ?>"><?php esc_html_e( 'Choose a gift', 'mitka' ); ?></a>
				<?php if ( $mitka_boxes ) : ?>
					<a class="btn btn--secondary" href="<?php echo esc_url( get_term_link( $mitka_boxes ) ); ?>"><?php esc_html_e( 'Ready gift boxes', 'mitka' ); ?></a>
				<?php endif; ?>
			</div>
			<ul class="hero__facts">
				<li><strong><?php esc_html_e( '1–2 days', 'mitka' ); ?></strong> <?php esc_html_e( 'to engrave', 'mitka' ); ?></li>
				<li><strong><?php esc_html_e( 'Free', 'mitka' ); ?></strong> <?php esc_html_e( 'engraving on every item', 'mitka' ); ?></li>
				<li><strong><?php esc_html_e( 'Payment', 'mitka' ); ?></strong> <?php esc_html_e( 'on delivery', 'mitka' ); ?></li>
			</ul>
		</div>
		<div class="hero__media">
			<div class="engrave-stage engrave-stage--hero" data-tone="wood">
				<?php
				if ( $mitka_hero_image ) {
					echo wp_get_attachment_image( $mitka_hero_image, 'large', false, array( 'fetchpriority' => 'high' ) );
				}
				?>
				<p class="engrave-stage__text engrave-font--script" style="--chars: 12" aria-hidden="true"><?php echo esc_html_x( 'Home is you', 'hero engraving sample', 'mitka' ); ?><br>2026</p>
			</div>
		</div>
	</div>
</section>

<?php if ( $mitka_categories ) : ?>
	<section class="section container" aria-labelledby="home-categories">
		<div class="section-head">
			<h2 id="home-categories"><?php esc_html_e( 'Catalog', 'mitka' ); ?></h2>
			<a class="section-head__link" href="<?php echo esc_url( $mitka_shop_url ); ?>"><?php esc_html_e( 'All products', 'mitka' ); ?><?php mitka_the_icon( 'chevron-right' ); ?></a>
		</div>
		<ul class="tiles">
			<?php foreach ( $mitka_categories as $mitka_cat ) : ?>
				<li class="tile">
					<div class="tile__media"><?php echo wp_get_attachment_image( mitka_term_image_id( $mitka_cat ), 'mitka-card', false, array( 'loading' => 'lazy' ) ); ?></div>
					<a class="tile__link" href="<?php echo esc_url( get_term_link( $mitka_cat ) ); ?>"><?php echo esc_html( $mitka_cat->name ); ?></a>
					<span class="tile__count">
						<?php
						/* translators: %d: number of products. */
						echo esc_html( sprintf( _n( '%d product', '%d products', $mitka_cat->count, 'mitka' ), $mitka_cat->count ) );
						?>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>

<?php if ( $mitka_occasions && ! is_wp_error( $mitka_occasions ) ) : ?>
	<section class="section section--tint" aria-labelledby="home-occasions">
		<div class="container">
			<h2 id="home-occasions"><?php esc_html_e( 'Who is it for?', 'mitka' ); ?></h2>
			<ul class="chips chips--wrap">
				<?php foreach ( $mitka_occasions as $mitka_occasion ) : ?>
					<li><a class="chip chip--lg" href="<?php echo esc_url( get_term_link( $mitka_occasion ) ); ?>"><?php echo esc_html( $mitka_occasion->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
<?php endif; ?>

<?php if ( $mitka_popular ) : ?>
	<section class="section container" aria-labelledby="home-popular">
		<div class="section-head">
			<h2 id="home-popular"><?php esc_html_e( 'Popular now', 'mitka' ); ?></h2>
			<a class="section-head__link" href="<?php echo esc_url( $mitka_shop_url ); ?>"><?php esc_html_e( 'See all', 'mitka' ); ?><?php mitka_the_icon( 'chevron-right' ); ?></a>
		</div>
		<ul class="products">
			<?php foreach ( $mitka_popular as $mitka_product ) : ?>
				<li><?php get_template_part( 'template-parts/product/card', null, array( 'product' => $mitka_product ) ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>

<section class="section container" aria-labelledby="home-steps">
	<h2 id="home-steps"><?php esc_html_e( 'How it works', 'mitka' ); ?></h2>
	<ol class="steps">
		<?php foreach ( $mitka_steps as $mitka_step ) : ?>
			<li class="steps__item">
				<h3 class="steps__title"><?php echo esc_html( $mitka_step['title'] ); ?></h3>
				<p><?php echo esc_html( $mitka_step['text'] ); ?></p>
			</li>
		<?php endforeach; ?>
	</ol>
</section>

<?php if ( $mitka_box_items ) : ?>
	<section class="section section--ink" aria-labelledby="home-boxes">
		<div class="container">
			<div class="section-head">
				<div>
					<h2 id="home-boxes"><?php esc_html_e( 'Ready gift boxes', 'mitka' ); ?></h2>
					<p class="section-head__lead"><?php echo esc_html( $mitka_boxes->description ); ?></p>
				</div>
				<a class="section-head__link" href="<?php echo esc_url( get_term_link( $mitka_boxes ) ); ?>"><?php esc_html_e( 'All boxes', 'mitka' ); ?><?php mitka_the_icon( 'chevron-right' ); ?></a>
			</div>
			<ul class="products">
				<?php foreach ( $mitka_box_items as $mitka_product ) : ?>
					<li><?php get_template_part( 'template-parts/product/card', null, array( 'product' => $mitka_product ) ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
<?php endif; ?>

<?php if ( $mitka_makers && ! is_wp_error( $mitka_makers ) ) : ?>
	<section class="section container" aria-labelledby="home-makers">
		<h2 id="home-makers"><?php esc_html_e( 'The people who make it', 'mitka' ); ?></h2>
		<ul class="makers">
			<?php foreach ( $mitka_makers as $mitka_maker ) : ?>
				<li><?php get_template_part( 'template-parts/maker-card', null, array( 'term' => $mitka_maker ) ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>
<?php
get_footer();
