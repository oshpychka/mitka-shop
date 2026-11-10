<?php
defined( 'ABSPATH' ) || exit;

global $product;

$mitka_max       = MITKA_ENGRAVING_LINES * MITKA_ENGRAVING_LINE_CHARS + MITKA_ENGRAVING_LINES - 1;
$mitka_materials = wc_get_product_terms( $product->get_id(), 'pa_material', array( 'fields' => 'names' ) );
$mitka_stars     = mitka_supports_star_map( $product );
$mitka_today     = wp_date( 'Y-m-d' );
?>
<fieldset class="engrave" data-engrave data-mode="text" data-lines="<?php echo esc_attr( MITKA_ENGRAVING_LINES ); ?>" data-line-chars="<?php echo esc_attr( MITKA_ENGRAVING_LINE_CHARS ); ?>">
	<legend class="engrave__legend">
		<?php mitka_the_icon( 'pen' ); ?>
		<?php esc_html_e( 'Engraving', 'mitka' ); ?>
		<span class="engrave__free"><?php esc_html_e( 'free', 'mitka' ); ?></span>
	</legend>

	<?php if ( $mitka_stars ) : ?>
		<input type="hidden" name="mitka_engraving_mode" value="text" data-engrave-mode-input>
		<div class="engrave__modes" role="group" aria-label="<?php esc_attr_e( 'What to engrave', 'mitka' ); ?>">
			<button class="engrave__mode" type="button" aria-pressed="true" data-engrave-mode="text"><?php esc_html_e( 'Text', 'mitka' ); ?></button>
			<button class="engrave__mode" type="button" aria-pressed="false" data-engrave-mode="stars"><?php esc_html_e( 'Star map + text', 'mitka' ); ?></button>
		</div>
	<?php endif; ?>

	<div class="engrave-plate" data-tone="<?php echo esc_attr( mitka_engraving_tone( $product ) ); ?>" data-engrave-plate>
		<div class="engrave-plate__canvas">
			<?php if ( $mitka_stars ) : ?>
				<svg class="engrave-plate__sky" viewBox="0 0 200 200" aria-hidden="true" data-engrave-sky hidden></svg>
			<?php endif; ?>
			<div class="engrave-plate__words" data-engrave-words>
				<p class="engrave-plate__text engrave-font--classic is-placeholder" aria-hidden="true" data-engrave-preview data-placeholder="<?php esc_attr_e( 'Your text here', 'mitka' ); ?>"><?php esc_html_e( 'Your text here', 'mitka' ); ?></p>
				<?php if ( $mitka_stars ) : ?>
					<p class="engrave-plate__meta" aria-hidden="true" data-engrave-meta hidden></p>
				<?php endif; ?>
			</div>
		</div>
		<button class="engrave-plate__play" type="button" data-engrave-play>
			<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13l10-6.5-10-6.5Z" fill="currentColor"/></svg>
			<?php esc_html_e( 'See it engraved', 'mitka' ); ?>
		</button>
		<?php if ( $mitka_materials ) : ?>
			<span class="engrave-plate__label">
				<?php
				/* translators: %s: material name, e.g. Oak. */
				echo esc_html( sprintf( __( 'Preview on %s', 'mitka' ), mb_strtolower( $mitka_materials[0] ) ) );
				?>
			</span>
		<?php endif; ?>
	</div>

	<?php if ( $mitka_stars ) : ?>
		<div class="engrave__stars" data-engrave-stars hidden>
			<div class="field">
				<label class="field__label" for="mitka-stars-date"><?php esc_html_e( 'Date', 'mitka' ); ?></label>
				<input type="date" id="mitka-stars-date" name="mitka_stars_date" value="<?php echo esc_attr( $mitka_today ); ?>" min="1900-01-01" max="<?php echo esc_attr( wp_date( 'Y-m-d', strtotime( '+1 year' ) ) ); ?>" data-engrave-date disabled>
			</div>
			<div class="field">
				<label class="field__label" for="mitka-stars-city"><?php esc_html_e( 'City', 'mitka' ); ?></label>
				<select id="mitka-stars-city" name="mitka_stars_city" data-engrave-city disabled>
					<?php foreach ( mitka_star_cities() as $mitka_key => $mitka_city ) : ?>
						<option value="<?php echo esc_attr( $mitka_key ); ?>" data-lat="<?php echo esc_attr( $mitka_city[1] ); ?>" data-lon="<?php echo esc_attr( $mitka_city[2] ); ?>" <?php selected( 'lviv', $mitka_key ); ?>><?php echo esc_html( $mitka_city[0] ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<p class="engrave__hint engrave__stars-hint"><?php esc_html_e( 'The real sky over that city at 22:00 Kyiv time: every star you could see that night.', 'mitka' ); ?></p>
		</div>
	<?php endif; ?>

	<div class="field">
		<label class="field__label" for="mitka-engraving"><?php esc_html_e( 'Your text', 'mitka' ); ?></label>
		<textarea class="engrave__input" id="mitka-engraving" name="mitka_engraving" rows="2" maxlength="<?php echo esc_attr( $mitka_max ); ?>" placeholder="<?php esc_attr_e( 'For Dad, with love', 'mitka' ); ?>" aria-describedby="mitka-engraving-hint" data-engrave-input></textarea>
		<p class="engrave__hint" id="mitka-engraving-hint">
			<span>
				<?php
				printf(
					/* translators: 1: number of lines, 2: characters per line. */
					esc_html__( 'Up to %1$d lines, %2$d characters each.', 'mitka' ),
					(int) MITKA_ENGRAVING_LINES,
					(int) MITKA_ENGRAVING_LINE_CHARS
				);
				?>
			</span>
			<span class="engrave__count" data-engrave-count aria-live="polite"></span>
		</p>
	</div>

	<div class="engrave__fonts" role="radiogroup" aria-label="<?php esc_attr_e( 'Font', 'mitka' ); ?>">
		<?php foreach ( mitka_engraving_fonts() as $mitka_key => $mitka_label ) : ?>
			<label class="engrave__font">
				<input class="visually-hidden" type="radio" name="mitka_engraving_font" value="<?php echo esc_attr( $mitka_key ); ?>" <?php checked( 'classic', $mitka_key ); ?> data-engrave-font>
				<span class="chip engrave__font-chip engrave-font--<?php echo esc_attr( $mitka_key ); ?>"><?php echo esc_html( $mitka_label ); ?></span>
			</label>
		<?php endforeach; ?>
	</div>
</fieldset>
