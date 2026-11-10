<?php
defined( 'ABSPATH' ) || exit;

$mitka_has_wc   = function_exists( 'WC' );
$mitka_count    = $mitka_has_wc && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
$mitka_account  = $mitka_has_wc ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
$mitka_cart_url = $mitka_has_wc ? wc_get_cart_url() : home_url( '/' );
?>
<div class="site-header__actions">
	<?php get_template_part( 'template-parts/header/languages' ); ?>
	<a class="icon-btn site-header__account" href="<?php echo esc_url( $mitka_account ); ?>">
		<?php mitka_the_icon( 'user' ); ?>
		<span class="visually-hidden"><?php esc_html_e( 'Account', 'mitka' ); ?></span>
	</a>
	<a class="icon-btn site-header__cart" href="<?php echo esc_url( $mitka_cart_url ); ?>">
		<?php mitka_the_icon( 'bag' ); ?>
		<span class="visually-hidden"><?php esc_html_e( 'Cart', 'mitka' ); ?></span>
		<span class="counter" data-cart-count <?php echo $mitka_count ? '' : 'hidden'; ?>><?php echo esc_html( $mitka_count ); ?></span>
	</a>
</div>
