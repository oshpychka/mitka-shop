<?php
defined( 'ABSPATH' ) || exit;

$mitka_term    = $args['term'];
$mitka_city    = get_term_meta( $mitka_term->term_id, 'city', true );
$mitka_founded = get_term_meta( $mitka_term->term_id, 'founded', true );
$mitka_image   = mitka_term_image_id( $mitka_term );
?>
<article class="maker">
	<div class="maker__media"><?php echo wp_get_attachment_image( $mitka_image, 'mitka-thumb', false, array( 'loading' => 'lazy' ) ); ?></div>
	<div class="maker__body">
		<h3 class="maker__name"><a class="maker__link" href="<?php echo esc_url( get_term_link( $mitka_term ) ); ?>"><?php echo esc_html( $mitka_term->name ); ?></a></h3>
		<?php if ( $mitka_city || $mitka_founded ) : ?>
			<p class="maker__meta">
				<?php
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
		<p class="maker__story"><?php echo esc_html( $mitka_term->description ); ?></p>
	</div>
</article>
