<?php
defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( '', $product ); ?>>
	<?php get_template_part( 'template-parts/product/card', null, array( 'product' => $product ) ); ?>
</li>
