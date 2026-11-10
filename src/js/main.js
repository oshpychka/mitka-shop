import '../scss/main.scss';
import { initDrawers } from './modules/drawer.js';
import { initMegaMenu } from './modules/mega-menu.js';
import { initEngraving } from './modules/engraving.js';
import { initPhoneMask } from './modules/phone.js';
import { initQuantity } from './modules/quantity.js';
import { initLanguage } from './modules/language.js';

initDrawers();
initMegaMenu();
initEngraving();
initPhoneMask();
initQuantity();
initLanguage();

// WooCommerce redraws the checkout and cart with jQuery; re-apply after it does.
if (window.jQuery) {
	window.jQuery(document.body).on('updated_checkout updated_cart_totals updated_wc_div', () => {
		initPhoneMask();
		initQuantity();
	});
}
