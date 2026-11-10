<?php
defined( 'ABSPATH' ) || exit;

$mitka_text = mitka_option( 'announcement' );
if ( ! $mitka_text ) {
	return;
}
?>
<div class="announcement">
	<div class="container announcement__inner">
		<p class="announcement__text"><?php echo esc_html( $mitka_text ); ?></p>
	</div>
</div>
