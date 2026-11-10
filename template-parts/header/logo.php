<?php
defined( 'ABSPATH' ) || exit;

$mitka_home = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
?>
<a class="logo" href="<?php echo esc_url( $mitka_home ); ?>" rel="home">
	<span class="logo__word">mitka</span>
	<span class="visually-hidden"><?php bloginfo( 'name' ); ?></span>
</a>
