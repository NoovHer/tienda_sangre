<?php
// Función para agregar saldo inicial al crear un usuario
function agregar_saldo_inicial_al_crear_usuario($user_id)
{
	if (!metadata_exists('user', $user_id, 'saldo_pendiente')) {
		update_user_meta($user_id, 'saldo_pendiente', 0);
	}
}
add_action('user_register', 'agregar_saldo_inicial_al_crear_usuario');

// Función para actualizar el saldo pendiente del usuario
function actualizar_saldo_pendiente_usuario($order_id)
{
	if (!class_exists('WooCommerce')) return;

	$order = wc_get_order($order_id);
	$user_id = $order->get_user_id();

	if (!$user_id || $user_id <= 0) {
		error_log('No se pudo obtener el user_id del pedido.');
		return;
	}

	// Obtener el saldo actual del usuario
	$saldo_actual = get_user_meta($user_id, 'saldo_pendiente', true);
	if (!$saldo_actual) {
		$saldo_actual = 0;
	}

	// Obtener el método de pago del pedido
	$payment_method = $order->get_payment_method();

	// Verificar si el método de pago es "a_cuenta"
	if ($payment_method !== 'a_cuenta') {
		error_log("El método de pago no es 'a_cuenta'. Es: $payment_method");
		return;
	}

	// Determinar el ajuste basado en el estado del pedido
	$new_status = $order->get_status();

	// Si el pedido está en espera, sumamos el total al saldo
	if (in_array($new_status, array('on-hold'))) {
		$saldo_actual += $order->get_total();
	}

	// Si el pedido está completado o cancelado, restamos el total al saldo
	if (in_array($new_status, array('completed', 'cancelled'))) {
		$saldo_actual -= $order->get_total();
	}

	// Actualizar el saldo pendiente del usuario
	update_user_meta($user_id, 'saldo_pendiente', $saldo_actual);

	// Registrar la acción en las notas del pedido
	$order->add_order_note("El saldo pendiente del usuario fue actualizado: $" . number_format($saldo_actual, 2));
}

add_action('woocommerce_order_status_changed', 'actualizar_saldo_pendiente_usuario', 10, 1);
add_action('woocommerce_checkout_order_processed', 'actualizar_saldo_pendiente_usuario', 10, 1);

// Shortcode para mostrar el saldo pendiente de un usuario
function mostrar_saldo_pendiente_usuario_shortcode($atts)
{
	// Verificar si el usuario está conectado
	if (!is_user_logged_in()) {
		return 'Debes iniciar sesión para ver tu saldo pendiente.';
	}

	// Obtener el usuario actualmente conectado
	$user = wp_get_current_user();

	// Registrar información para depuración
	//error_log("Usuario logueado: " . $user->user_login);

	// Obtener el saldo pendiente del usuario desde los metadatos
	$saldo_pendiente = get_user_meta($user->ID, 'saldo_pendiente', true);

	// Registrar el saldo pendiente para depuración
	//error_log("Saldo pendiente de {$user->user_login}: $saldo_pendiente");

	// Verificar si el saldo pendiente es 0 o no existe
	if (!$saldo_pendiente || $saldo_pendiente <= 0) {
		return '';
	}

	// Mostrar el saldo pendiente en formato de moneda
	return 'Tu saldo pendiente es: $' . number_format($saldo_pendiente, 2);
}

// Registrar el shortcode
add_shortcode('saldo_pendiente_usuario', 'mostrar_saldo_pendiente_usuario_shortcode');
