<?php
if (!defined('ABSPATH')) {
	exit;
}

/*
// Modificar campos de checkout basados en la primera compra
add_filter('woocommerce_checkout_fields', 'modificar_campos_checkout_based_on_primera_compra');
function modificar_campos_checkout_based_on_primera_compra($fields)
{
	$user_id = get_current_user_id();
	if ($user_id) {
		// Verificar si el usuario tiene pedidos anteriores
		$user_orders = wc_get_orders(array(
			'customer_id' => $user_id,
			'limit' => 1 // Obtener solo un pedido para verificar
		));

		if (!empty($user_orders)) {
			// Hacer el campo de correo electrónico no editable
			if (isset($fields['billing']['billing_email'])) {
				$fields['billing']['billing_email']['custom_attributes'] = array('readonly' => 'readonly');
			}

			// Eliminar "Enviar a una dirección diferente"
			unset($fields['shipping']);
			unset($fields['order']['ship_to_different_address']);

			// Eliminar los campos de dirección de facturación excepto el correo electrónico
			unset($fields['billing']['billing_first_name']);
			unset($fields['billing']['billing_last_name']);
			unset($fields['billing']['billing_company']);
			unset($fields['billing']['billing_country']);
			unset($fields['billing']['billing_address_1']);
			unset($fields['billing']['billing_address_2']);
			unset($fields['billing']['billing_city']);
			unset($fields['billing']['billing_state']);
			unset($fields['billing']['billing_postcode']);
			unset($fields['billing']['billing_phone']);
		}
	}
	return $fields;
}

// Guardar dirección de facturación y envío durante la primera compra
add_action('woocommerce_checkout_update_order_meta', 'guardar_direccion_primera_compra');
function guardar_direccion_primera_compra($order_id)
{
	$user_id = get_current_user_id();
	if ($user_id) {
		// Verificar si el usuario tiene pedidos anteriores
		$user_orders = wc_get_orders(array(
			'customer_id' => $user_id,
			'limit' => 1
		));

		if (empty($user_orders)) {
			// Si es la primera compra, guardar la dirección de envío en los metadatos del usuario
			$order = wc_get_order($order_id);

			// Guardar dirección de envío
			update_user_meta($user_id, 'shipping_first_name', $order->get_shipping_first_name());
			update_user_meta($user_id, 'shipping_last_name', $order->get_shipping_last_name());
			update_user_meta($user_id, 'shipping_company', $order->get_shipping_company());
			update_user_meta($user_id, 'shipping_country', $order->get_shipping_country());
			update_user_meta($user_id, 'shipping_address_1', $order->get_shipping_address_1());
			update_user_meta($user_id, 'shipping_address_2', $order->get_shipping_address_2());
			update_user_meta($user_id, 'shipping_city', $order->get_shipping_city());
			update_user_meta($user_id, 'shipping_state', $order->get_shipping_state());
			update_user_meta($user_id, 'shipping_postcode', $order->get_shipping_postcode());

			// Guardar dirección de facturación
			update_user_meta($user_id, 'billing_first_name', $order->get_billing_first_name());
			update_user_meta($user_id, 'billing_last_name', $order->get_billing_last_name());
			update_user_meta($user_id, 'billing_company', $order->get_billing_company());
			update_user_meta($user_id, 'billing_country', $order->get_billing_country());
			update_user_meta($user_id, 'billing_address_1', $order->get_billing_address_1());
			update_user_meta($user_id, 'billing_address_2', $order->get_billing_address_2());
			update_user_meta($user_id, 'billing_city', $order->get_billing_city());
			update_user_meta($user_id, 'billing_state', $order->get_billing_state());
			update_user_meta($user_id, 'billing_postcode', $order->get_billing_postcode());
			update_user_meta($user_id, 'billing_phone', $order->get_billing_phone());
		}
	}
}

// Usar dirección de envío y facturación guardada
add_filter('woocommerce_checkout_posted_data', 'usar_direccion_guardada');
function usar_direccion_guardada($data)
{
	$user_id = get_current_user_id();
	if ($user_id) {
		// Verificar si el usuario tiene direcciones guardadas
		$direccion_envio_guardada = get_user_meta($user_id, 'shipping_address_1', true);
		$direccion_facturacion_guardada = get_user_meta($user_id, 'billing_address_1', true);

		if (!empty($direccion_envio_guardada)) {
			// Usar dirección de envío guardada
			$data['shipping_first_name'] = get_user_meta($user_id, 'shipping_first_name', true);
			$data['shipping_last_name'] = get_user_meta($user_id, 'shipping_last_name', true);
			$data['shipping_company'] = get_user_meta($user_id, 'shipping_company', true);
			$data['shipping_country'] = get_user_meta($user_id, 'shipping_country', true);
			$data['shipping_address_1'] = get_user_meta($user_id, 'shipping_address_1', true);
			$data['shipping_address_2'] = get_user_meta($user_id, 'shipping_address_2', true);
			$data['shipping_city'] = get_user_meta($user_id, 'shipping_city', true);
			$data['shipping_state'] = get_user_meta($user_id, 'shipping_state', true);
			$data['shipping_postcode'] = get_user_meta($user_id, 'shipping_postcode', true);
		}

		if (!empty($direccion_facturacion_guardada)) {
			// Usar dirección de facturación guardada
			$data['billing_first_name'] = get_user_meta($user_id, 'billing_first_name', true);
			$data['billing_last_name'] = get_user_meta($user_id, 'billing_last_name', true);
			$data['billing_company'] = get_user_meta($user_id, 'billing_company', true);
			$data['billing_country'] = get_user_meta($user_id, 'billing_country', true);
			$data['billing_address_1'] = get_user_meta($user_id, 'billing_address_1', true);
			$data['billing_address_2'] = get_user_meta($user_id, 'billing_address_2', true);
			$data['billing_city'] = get_user_meta($user_id, 'billing_city', true);
			$data['billing_state'] = get_user_meta($user_id, 'billing_state', true);
			$data['billing_postcode'] = get_user_meta($user_id, 'billing_postcode', true);
			$data['billing_phone'] = get_user_meta($user_id, 'billing_phone', true);
		}
	}
	return $data;
}
*/
