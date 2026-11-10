<?php
/**
 * Cart: a list of item rows with the totals in a side column.
 * Keeps WooCommerce's form fields and hooks so updating, removing and coupons work.
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound, WordPress.WP.I18n.TextDomainMismatch -- WooCommerce template override: its hooks and its translations.

do_action( 'woocommerce_before_cart' );
?>
<div class="cart-layout">
	<form class="woocommerce-cart-form cart-layout__items" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
		<?php do_action( 'woocommerce_before_cart_table' ); ?>
		<ul class="cart-list">
			<?php
			foreach ( WC()->cart->get_cart() as $mitka_key => $mitka_item ) :
				$mitka_product = apply_filters( 'woocommerce_cart_item_product', $mitka_item['data'], $mitka_item, $mitka_key );
				if ( ! $mitka_product instanceof WC_Product || ! $mitka_product->exists() || $mitka_item['quantity'] <= 0 ) {
					continue;
				}
				$mitka_name = apply_filters( 'woocommerce_cart_item_name', WC()->cart->get_item_product_name( $mitka_item, $mitka_product ), $mitka_item, $mitka_key );
				$mitka_link = $mitka_product->is_visible() ? $mitka_product->get_permalink( $mitka_item ) : '';
				?>
				<li class="cart-row <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $mitka_item, $mitka_key ) ); ?>">
					<a class="cart-row__media" href="<?php echo esc_url( $mitka_link ); ?>" tabindex="-1" aria-hidden="true">
						<?php echo wp_kses_post( $mitka_product->get_image( 'mitka-thumb' ) ); ?>
					</a>
					<div class="cart-row__info">
						<a class="cart-row__name" href="<?php echo esc_url( $mitka_link ); ?>"><?php echo wp_kses_post( $mitka_name ); ?></a>
						<?php echo wc_get_formatted_cart_item_data( $mitka_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce escapes item data. ?>
						<span class="cart-row__unit price"><?php echo wp_kses_post( WC()->cart->get_product_price( $mitka_product ) ); ?></span>
					</div>
					<div class="cart-row__qty">
						<?php
						woocommerce_quantity_input(
							array(
								'input_name'   => "cart[{$mitka_key}][qty]",
								'input_value'  => $mitka_item['quantity'],
								'max_value'    => $mitka_product->is_sold_individually() ? 1 : $mitka_product->get_max_purchase_quantity(),
								'min_value'    => $mitka_product->is_sold_individually() ? 1 : 0,
								'product_name' => $mitka_name,
							),
							$mitka_product
						);
						?>
					</div>
					<span class="cart-row__total price"><?php echo wp_kses_post( WC()->cart->get_product_subtotal( $mitka_product, $mitka_item['quantity'] ) ); ?></span>
					<a class="icon-btn cart-row__remove" href="<?php echo esc_url( wc_get_cart_remove_url( $mitka_key ) ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $mitka_name ) ) ); ?>">
						<?php mitka_the_icon( 'close' ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="cart-actions">
			<?php if ( wc_coupons_enabled() ) : ?>
				<div class="cart-actions__coupon">
					<label for="coupon_code" class="visually-hidden"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
					<input type="text" name="coupon_code" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>">
					<button type="submit" class="btn btn--secondary" name="apply_coupon" value="1"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
				</div>
			<?php endif; ?>
			<button type="submit" class="btn btn--ghost cart-actions__update" name="update_cart" value="1"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>
			<?php do_action( 'woocommerce_cart_actions' ); ?>
			<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
		</div>
		<?php do_action( 'woocommerce_after_cart_table' ); ?>
	</form>

	<aside class="cart-layout__totals">
		<?php woocommerce_cart_totals(); ?>
	</aside>
</div>
<?php
do_action( 'woocommerce_after_cart' );
