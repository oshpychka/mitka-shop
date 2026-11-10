<?php
/**
 * First-visit language picker. Shown by CSS while <html> has .needs-lang
 * (set in <head> when no choice is saved), so it never flashes for returning visitors.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'pll_the_languages' ) ) {
	return;
}

$mitka_languages = pll_the_languages(
	array(
		'raw'                    => 1,
		'hide_if_empty'          => 0,
		'hide_if_no_translation' => 0,
	)
);
if ( count( $mitka_languages ) < 2 ) {
	return;
}

$mitka_texts = array(
	'uk' => array( 'Українська', 'Продовжити українською' ),
	'en' => array( 'English', 'Continue in English' ),
);
?>
<div class="lang-splash" role="dialog" aria-modal="true" aria-labelledby="lang-splash-title" data-lang-splash>
	<div class="lang-splash__inner">
		<span class="logo__word lang-splash__logo" aria-hidden="true">mitka</span>
		<h2 class="lang-splash__title" id="lang-splash-title">
			<span lang="uk">Оберіть мову</span>
			<span class="lang-splash__title-alt" lang="en">Choose your language</span>
		</h2>
		<div class="lang-splash__options">
			<?php foreach ( $mitka_languages as $mitka_language ) : ?>
				<?php $mitka_text = $mitka_texts[ $mitka_language['slug'] ] ?? array( $mitka_language['name'], '' ); ?>
				<a class="lang-splash__option<?php echo $mitka_language['current_lang'] ? ' is-current' : ''; ?>" href="<?php echo esc_url( $mitka_language['url'] ); ?>" hreflang="<?php echo esc_attr( $mitka_language['locale'] ); ?>" lang="<?php echo esc_attr( $mitka_language['slug'] ); ?>" data-lang-choice="<?php echo esc_attr( $mitka_language['slug'] ); ?>"<?php echo $mitka_language['current_lang'] ? ' data-lang-current' : ''; ?>>
					<span class="lang-splash__code"><?php echo esc_html( strtoupper( $mitka_language['slug'] ) ); ?></span>
					<span class="lang-splash__name"><?php echo esc_html( $mitka_text[0] ); ?></span>
					<span class="lang-splash__hint"><?php echo esc_html( $mitka_text[1] ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
		<p class="lang-splash__note">
			<span lang="uk">Змінити мову можна будь-коли вгорі сторінки.</span>
			<span lang="en">You can switch at any time at the top of the page.</span>
		</p>
	</div>
</div>
