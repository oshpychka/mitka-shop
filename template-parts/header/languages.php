<?php
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'pll_the_languages' ) ) {
	return;
}

$mitka_languages = pll_the_languages(
	array(
		'raw'           => 1,
		'hide_if_empty' => 0,
	)
);

if ( count( $mitka_languages ) < 2 ) {
	return;
}
?>
<ul class="lang-switch" aria-label="<?php esc_attr_e( 'Language', 'mitka' ); ?>">
	<?php foreach ( $mitka_languages as $mitka_language ) : ?>
		<li>
			<?php if ( $mitka_language['current_lang'] ) : ?>
				<span class="lang-switch__item is-current" aria-current="true"><?php echo esc_html( strtoupper( $mitka_language['slug'] ) ); ?></span>
			<?php else : ?>
				<a class="lang-switch__item" href="<?php echo esc_url( $mitka_language['url'] ); ?>" hreflang="<?php echo esc_attr( $mitka_language['locale'] ); ?>" lang="<?php echo esc_attr( $mitka_language['locale'] ); ?>"><?php echo esc_html( strtoupper( $mitka_language['slug'] ) ); ?></a>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
