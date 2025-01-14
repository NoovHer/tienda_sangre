<?php
function gbs_obtener_pedido_seguro($pedido_id)
{
	$pedido = wc_get_order($pedido_id);
	if (!$pedido || !is_a($pedido, 'WC_Order')) {
		echo '<p>El pedido no existe.</p>';
		return null;
	}
	return $pedido;
}

// Mostrar detalles del pedido
function gbs_mostrar_detalles_pedido()
{
	if (!current_user_can('gbs_manage_orders')) {
		echo '<div class="woocommerce-info">No tienes permisos para acceder a esta página.</div>';
		return;
	}

	$pedido_id = isset($_GET['order']) ? absint($_GET['order']) : 0;
	$pedido = gbs_obtener_pedido_seguro($pedido_id);

	if (!$pedido) {
		return;
	}
	echo '<div class="order-details-container">';
	echo '<h3><strong>Número de Pedido:</strong> #' . esc_html($pedido->get_order_number()) . '</h3>';
	$status = $pedido->get_status();
	// Cambiar el color según el estado del pedido
	$status_class = 'status-' . esc_attr($status);
	echo '<span class="status-label ' . esc_attr($status_class) . '">' . esc_html(wc_get_order_status_name($status)) . '</span></li>';
	// Información del pedido
	echo '<div class="woocommerce-order-details__section">';
	// Información del Cliente
	echo '<div class="woocommerce-order-details__card">';
	echo '<h3><i class="fa fa-user"></i> Información del Cliente</h3>';
	echo '<ul>';
	echo '<li><strong>Cliente:</strong> ' . esc_html($pedido->get_billing_first_name() . ' ' . $pedido->get_billing_last_name()) . '</li>';
	echo '<li><strong>Email:</strong> ' . esc_html($pedido->get_billing_email()) . '</li>';
	echo '<li><strong>Teléfono:</strong> ' . esc_html($pedido->get_billing_phone()) . '</li>';
	echo '<li><strong>Total:</strong> ' . wc_price($pedido->get_total()) . '</li>';
	echo '</ul>';
	echo '</div>'; // Cerrar tarjeta Cliente

	// Dirección de envío
	echo '<div class="woocommerce-order-details__card">';
	echo '<h3><i class="fa fa-truck"></i> Dirección de Envío</h3>';
	$shipping_address = $pedido->get_formatted_shipping_address();
	if ($shipping_address) {
		echo '<address>' . wp_kses_post($shipping_address) . '</address>';
	} else {
		echo '<p>No se especificó dirección de envío.</p>';
	}
	echo '</div>'; // Cerrar tarjeta Dirección de Envío
	echo '<div class="woocommerce-order-details">';
	echo '<h3>Productos</h3>';
	echo '<table class="woocommerce-order-details__table">';
	echo '<thead>
			<tr>
				<th>Producto</th>
				<th>Cantidad</th>
				<th>Total</th>
			</tr>
		</thead>';
	echo '<tbody>';
	foreach ($pedido->get_items() as $item) {
		echo '<tr>';
		echo '<td>' . esc_html($item->get_name()) . '</td>';
		echo '<td>' . esc_html($item->get_quantity()) . '</td>';
		echo '<td>' . wc_price($item->get_total()) . '</td>';
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';
	echo '</div>';
	// Botón para descargar el PDF
	if (class_exists('WPO_WCPDF')) {
		$pdf_url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'generate_wpo_wcpdf',
					'document_type' => 'invoice',
					'order_ids' => $pedido_id,
				),
				admin_url('admin-ajax.php')
			),
			'generate_wpo_wcpdf'
		);
		echo '<div class="woocommerce-order-details">';
		echo '<h3>Descargar PDF</h3>';
		echo '<a href="' . esc_url($pdf_url) . '" class="woocommerce-button button" target="_blank"><i class="fa fa-file-pdf"></i> Descargar en PDF</a>';
		echo '</div>';
	}
	// Formulario para cambiar estado
	echo '<div class="woocommerce-order-details">';
	echo '<h3>Actualizar Estado</h3>';
	echo '<form method="post" action="" class="woocommerce-form">';
	echo '<input type="hidden" name="pedido_id" value="' . esc_attr($pedido->get_id()) . '">';
	echo '<div class="form-row">';
	echo '<label for="nuevo_estado">Nuevo Estado:</label>';
	echo '<select name="nuevo_estado" id="nuevo_estado">';
	foreach (wc_get_order_statuses() as $status => $label) {
		echo '<option value="' . esc_attr($status) . '">' . esc_html($label) . '</option>';
	}
	echo '</select>';
	echo '</div>';
	echo '<button type="submit" name="cambiar_estado" class="woocommerce-button button">Actualizar</button>';
	echo '</form>';
	echo '</div>';
	echo '</div>';
	// Productos


	if (current_user_can('administrator')) {
		// Historial del pedido
		echo '<div class="woocommerce-order-details">';
		echo '<h3>Historial</h3>';
		$notas = wc_get_order_notes(array('order_id' => $pedido_id));

		if ($notas) {
			echo '<ul class="woocommerce-order-details__history">';
			foreach ($notas as $nota) {
				// Obtener el user_id de la nota
				$user_id = $nota->user_id;
				$user = get_user_by('id', $user_id);

				if ($user) {
					$user_name = $user->display_name;
				}

				// Mostrar la nota con el nombre del usuario que realizó la modificación
				echo '<li><span>' . esc_html($nota->date_created->date('d-m-Y H:i:s')) . ' - ' . esc_html($user_name) . ':</span> ' . esc_html($nota->content) . '</li>';
			}
			echo '</ul>';
		} else {
			echo '<p>No hay historial disponible.</p>';
		}
		echo '</div>';
	}
	// Cerrar contenedor principal
	echo '</div>';
	echo '</div>';
}


// Hook para mostrar los detalles del pedido en el endpoint
add_action('woocommerce_account_detalles-pedido_endpoint', 'gbs_mostrar_detalles_pedido');

// Procesar el cambio de estado del pedido
function gbs_procesar_cambio_estado()
{
	if (isset($_POST['cambiar_estado'], $_POST['pedido_id'], $_POST['nuevo_estado'])) {
		if (!current_user_can('gbs_manage_orders')) {
			wp_die('No tienes permisos para realizar esta acción.');
		}

		$pedido_id = absint($_POST['pedido_id']);
		$nuevo_estado = sanitize_text_field($_POST['nuevo_estado']);
		$pedido = gbs_obtener_pedido_seguro($pedido_id);

		if ($pedido) {
			// Obtener el usuario actual que realiza el cambio
			$current_user = wp_get_current_user();
			$user_name = $current_user->display_name;

			// Obtener el estado anterior
			$estado_anterior = $pedido->get_status();
			// Convertir los estados a su versión traducida
			$estado_anterior_nombre = wc_get_order_status_name($estado_anterior);
			$nuevo_estado_nombre = wc_get_order_status_name($nuevo_estado);

			// Actualizar el estado del pedido
			$pedido->update_status($nuevo_estado, 'Estado cambiado manualmente.');

			// Añadir una nota al pedido con el usuario que realizó el cambio
			$nota = sprintf('Estado cambiado de "%s" a "%s" por %s', $estado_anterior_nombre, $nuevo_estado_nombre, $user_name);
			$pedido->add_order_note($nota);

			wc_add_notice('Estado actualizado correctamente.', 'success');
		} else {
			wc_add_notice('Error al actualizar el estado.', 'error');
		}

		wp_safe_redirect(wp_get_referer());
		exit;
	}
}

add_action('template_redirect', 'gbs_procesar_cambio_estado');
