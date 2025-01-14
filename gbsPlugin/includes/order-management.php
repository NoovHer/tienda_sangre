<?php
// Mostrar el contenido de "Gestión de Pedidos"
function gbs_mostrar_contenido_gestion_pedidos()
{
	echo '<style>
        /* Estilos generales */
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
        }

        .gbs-content-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .custom-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .custom-pagination a {
            text-decoration: none;
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f8f8f8;
            color: #333;
            font-size: 14px;
            transition: all 0.3s ease-in-out;
        }

        .custom-pagination a:hover {
            background-color: #cc1939;
            color: #fff;
            border-color: #cc1939;
        }

        .custom-pagination a.current {
            background-color: #cc1939;
            color: #fff;
            font-weight: bold;
            border-color: #cc1939;
            cursor: default;
        }

        .custom-pagination a.prev,
        .custom-pagination a.next {
            font-weight: bold;
            color:#fff;
        }

        /* Estilo para la tabla de pedidos */
        .woocommerce-orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .woocommerce-orders-table th,
        .woocommerce-orders-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .woocommerce-orders-table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .woocommerce-orders-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Estilos para el botón de "Ver Detalles" */
        .woocommerce-orders-table .view-details {
            padding: 8px 12px;
            background-color: #cc1939;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .woocommerce-orders-table .view-details:hover {
            background-color: #a2182f;
        }

        /* Estilo del buscador */
        .order-search {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-search input {
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 4px;
            border: 1px solid #ccc;
            width: 200px;
        }

        .order-search button {
            padding: 8px 16px;
            background-color: #cc1939;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .order-search button:hover {
            background-color: #a2182f;
        }
    </style>';

	// Verificar permisos de usuario
	if (!is_user_logged_in() || !current_user_can('gbs_manage_orders')) {
		echo '<p>No tienes permisos para acceder a esta página.</p>';
		return;
	}
	// Buscar por número de pedido
	$search_order_number = isset($_GET['search_order_number']) ? sanitize_text_field($_GET['search_order_number']) : '';

	// Variables de paginación
	$pagina_actual = isset($_GET['pagina']) ? absint($_GET['pagina']) : 1;
	$limite = 25;
	$offset = ($pagina_actual - 1) * $limite;

	// Contar todos los pedidos
	$args_contar = ['return' => 'ids', 'limit' => -1];
	$total_pedidos = count(wc_get_orders($args_contar));
	$total_paginas = ceil($total_pedidos / $limite);

	// Argumentos para obtener pedidos
	$args = [
		'limit'  => $limite,
		'offset' => $offset,
		'orderby' => 'date',
		'order'   => 'DESC',
		'status'  => 'any',
	];

	// Si hay un número de pedido en la búsqueda, agregarlo a los argumentos
	if ($search_order_number) {
		$all_orders = wc_get_orders(['limit' => -1]);
		$order_ids = [];
		foreach ($all_orders as $order) {
			if ($order->get_order_number() == $search_order_number) {
				$order_ids[] = $order->get_id();
			}
		}
		if (!empty($order_ids)) {
			$args['post__in'] = $order_ids;
		} else {
			$args['post__in'] = [0]; // Sin Resultados
		}
	}

	// Obtener pedidos con paginación
	$orders = wc_get_orders($args);

	echo '<div class="gbs-content-container">';
	// Mostrar el formulario de búsqueda
	echo '<div class="order-search">';
	echo '<form method="GET" action="">';
	echo '<input type="number" name="search_order_number" placeholder="Buscar por ID de Pedido" value="' . esc_attr($search_order_number) . '">';
	echo '<button type="submit">Buscar</button>';
	echo '</form>';
	echo '</div>';

	// Mostrar la tabla de pedidos
	echo '<table class="woocommerce-orders-table">';
	echo '<thead><tr><th>ID Pedido</th><th>Cliente</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>';

	if ($orders) {
		foreach ($orders as $pedido) {
			// Asegúrate de que $pedido sea un objeto WC_Order
			if ($pedido instanceof WC_Order) {
				// Obtener el nombre completo del cliente
				$cliente = $pedido->get_billing_first_name() . ' ' . $pedido->get_billing_last_name();
				// Obtener el estado del pedido
				$estado = wc_get_order_status_name($pedido->get_status());
				// Obtener el total del pedido
				$total = $pedido->get_total();
				// Asegúrate de que el total es numérico
				$total = is_numeric($total) ? $total : 0;
			} else {
				// Si no es un WC_Order (por ejemplo, si es un reembolso), asignamos valores predeterminados
				$total = 0;
			}

			echo '<tr>';
			// Mostrar el ID del pedido con el símbolo '#' al inicio
			echo '<td>#' . esc_html($pedido->get_order_number()) . '</td>';
			// Mostrar el nombre del cliente
			echo '<td>' . esc_html($cliente) . '</td>';
			// Mostrar el total del pedido con formato de moneda
			echo '<td>' . wc_price($total) . '</td>';
			// Mostrar el estado del pedido
			echo '<td>';
			echo '<select class="update-status" data-order-id="' . esc_attr($pedido->get_id()) . '">';
			foreach (wc_get_order_statuses() as $status_key => $status_label) {
				$selected = ($pedido->get_status() == str_replace('wc-', '', $status_key)) ? 'selected' : '';
				echo '<option value="' . esc_attr(str_replace('wc-', '', $status_key)) . '" ' . esc_attr($selected) . '>' . esc_html($status_label) . '</option>';
			}
			echo '</select>';
			echo '</td>';
			echo '<td>';
			// Generar enlace a los detalles del pedido en la cuenta del usuario
			echo '<a href="' . esc_url(add_query_arg('order', $pedido->get_id(), wc_get_account_endpoint_url('detalles-pedido'))) . '" class="view-details">Ver Detalles</a>';
			echo '</td>';
			echo '</tr>';
		}
	} else {
		echo '<tr><td colspan="5">No se encontraron pedidos.</td></tr>';
	}

	echo '</tbody></table>';

	// Agregar paginación
	echo '<nav class="woocommerce-pagination custom-pagination">';
	if ($total_paginas > 1) {
		// Enlace "Anterior"
		if ($pagina_actual > 1) {
			echo '<a class="prev page-numbers" href="?pagina=' . esc_attr($pagina_actual - 1) . '">&laquo; Anterior</a>';
		}

		// Números de página
		for ($i = 1; $i <= $total_paginas; $i++) {
			$class = ($i === $pagina_actual) ? 'current' : '';
			echo '<a class="page-numbers ' . esc_attr($class) . '" href="?pagina=' . esc_attr($i) . '">' . esc_html($i) . '</a>';
		}

		// Enlace "Siguiente"
		if ($pagina_actual < $total_paginas) {
			echo '<a class="next page-numbers" href="?pagina=' . esc_attr($pagina_actual + 1) . '">Siguiente &raquo;</a>';
		}
	}
	echo '</nav>';
	echo '</div>'; // cierra el contenedor principal
}

// Registrar el endpoint en WooCommerce
add_action('woocommerce_account_gestion-pedidos_endpoint', 'gbs_mostrar_contenido_gestion_pedidos');

// Agregar "Gestión de Pedidos" al menú de WooCommerce
function gbs_agregar_menu_gestion_pedidos($items)
{
	// Verificar si el usuario tiene permisos de administrador
	if (current_user_can('gbs_manage_orders')) {
		// Agregar "Gestión de Pedidos" al principio del menú
		$new_items = array('gestion-pedidos' => 'Gestión de Pedidos');
		$items = array_merge($new_items, $items);
	}

	return $items;
}
add_filter('woocommerce_account_menu_items', 'gbs_agregar_menu_gestion_pedidos');

// AJAX para actualizar el estado del pedido
function gbs_actualizar_estado_pedido()
{
	// Verificar que el usuario tiene los permisos adecuados
	if (!current_user_can('gbs_manage_orders') || !isset($_POST['order_id']) || !isset($_POST['new_status'])) {
		wp_send_json_error(array('message' => 'No tienes permisos o falta información.'));
		return;
	}

	// Obtener el ID del pedido y el nuevo estado
	$order_id = intval($_POST['order_id']);
	$new_status = sanitize_text_field($_POST['new_status']);

	// Obtener el pedido y actualizar el estado
	$order = wc_get_order($order_id);
	if ($order && array_key_exists('wc-' . $new_status, wc_get_order_statuses())) {
		$order->update_status($new_status);
		wp_send_json_success(array('message' => 'Estado actualizado con éxito.'));
	} else {
		wp_send_json_error(array('message' => 'Error al actualizar el estado.'));
	}
}
add_action('wp_ajax_gbs_actualizar_estado_pedido', 'gbs_actualizar_estado_pedido');

// Cargar el script JavaScript para manejar la actualización sin botón
function gbs_script_ajax_update()
{
?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			// Evento que detecta el cambio de estado en el select
			$('.update-status').change(function() {
				var order_id = $(this).data('order-id');
				var new_status = $(this).val();

				// Enviar la solicitud AJAX
				$.ajax({
					url: '<?php echo admin_url('admin-ajax.php'); ?>',
					method: 'POST',
					data: {
						action: 'gbs_actualizar_estado_pedido',
						order_id: order_id,
						new_status: new_status,
					},
					success: function(response) {
						if (response.success) {
							alert('Estado actualizado correctamente');
						} else {
							alert(response.data.message);
						}
					}
				});
			});
		});
	</script>
<?php
}
add_action('wp_footer', 'gbs_script_ajax_update');
