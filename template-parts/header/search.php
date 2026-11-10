<?php
defined( 'ABSPATH' ) || exit;

$mitka_search_id = $args['id'] ?? 'site-search';
?>
<form class="site-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="visually-hidden" for="<?php echo esc_attr( $mitka_search_id ); ?>"><?php esc_html_e( 'Search products', 'mitka' ); ?></label>
	<input class="site-search__input" id="<?php echo esc_attr( $mitka_search_id ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Board, wallet, gift for dad…', 'mitka' ); ?>" autocomplete="off">
	<input type="hidden" name="post_type" value="product">
	<button class="site-search__submit" type="submit">
		<?php mitka_the_icon( 'search' ); ?>
		<span class="visually-hidden"><?php esc_html_e( 'Search', 'mitka' ); ?></span>
	</button>
</form>
