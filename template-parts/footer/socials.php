<?php
defined( 'ABSPATH' ) || exit;

$mitka_socials = array_filter(
	array(
		'instagram' => mitka_option( 'instagram' ),
		'facebook'  => mitka_option( 'facebook' ),
		'telegram'  => mitka_option( 'telegram' ),
	)
);

if ( ! $mitka_socials ) {
	return;
}
?>
<ul class="socials">
	<?php foreach ( $mitka_socials as $mitka_network => $mitka_url ) : ?>
		<li>
			<a class="icon-btn" href="<?php echo esc_url( $mitka_url ); ?>" target="_blank" rel="noopener noreferrer">
				<?php mitka_the_icon( $mitka_network ); ?>
				<span class="visually-hidden"><?php echo esc_html( ucfirst( $mitka_network ) ); ?></span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
