<?php
// Bloquear añadir productos al carrito si la licencia está expirada
function bloquear_licencia_expirada()
{
	if (is_user_logged_in() && did_action('woocommerce_before_cart') === 0) {
		$user_id = get_current_user_id();
		$vigencia_licencia = get_user_meta($user_id, 'vig_lic_sanitaria', true);

		if (!empty($vigencia_licencia) && strtotime($vigencia_licencia) < time()) {
			wc_add_notice('Tu licencia sanitaria ha expirado. No puedes realizar compras.', 'error');
			return false;
		}
	}
	return true;
}
add_filter('woocommerce_is_purchasable', 'bloquear_licencia_expirada');

// Evitar completar la compra si la licencia está expirada
function bloquear_checkout_licencia_expirada()
{
	if (is_user_logged_in()) {
		$user_id = get_current_user_id();
		$vigencia_licencia = get_user_meta($user_id, 'vig_lic_sanitaria', true);

		if (!empty($vigencia_licencia) && strtotime($vigencia_licencia) < time()) {
			wc_add_notice('Tu licencia sanitaria ha expirado y no puedes finalizar la compra.', 'error');
			return false;
		}
	}
	return true;
}
add_action('woocommerce_checkout_process', 'bloquear_checkout_licencia_expirada');

// Mostrar un aviso visual en la tienda
function aviso_licencia_expirada_tienda()
{
	if (is_user_logged_in()) {
		$user_id = get_current_user_id();
		$vigencia_licencia = get_user_meta($user_id, 'vig_lic_sanitaria', true);

		if (!empty($vigencia_licencia) && strtotime($vigencia_licencia) < time()) {
			echo '<p class="woocommerce-info">⚠️ Tu licencia sanitaria ha expirado. Por favor, renueva tu licencia para continuar comprando.</p>';
		}
	}
}
add_action('woocommerce_before_shop_loop', 'aviso_licencia_expirada_tienda');
