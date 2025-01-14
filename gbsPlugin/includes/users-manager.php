<?php
// Asegurarse de que solo se cargue si el plugin está activo
if (!defined('ABSPATH')) {
	exit;
}
// Mostrar el contenido de "Gestión de Usuarios"
function gbs_mostrar_contenido_gestion_usuarios()
{
	// CSS para la tabla y la paginación
	echo '<style>
        /* Estilo para el contenedor principal */
        .gbs-container {
            margin: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .search-user {
            display: flex;
            justify-content: flex-start;
            gap: 20px;
            margin-top: 20px;
            align-items: center;
        }

        /* Botón para crear un nuevo usuario */
        .create-user-btn,
        .search-user button,
        .clear-search-btn {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            display: inline-block;
            width: 200px;  /* Hacer que todos los botones tengan el mismo tamaño */
            text-decoration: none;
        }

        /* Estilo para el botón de crear nuevo usuario */
        .create-user-btn {
            background-color: #cc1939;
            color: white;
        }

      .create-user-btn:hover {
            background-color: #a2182f;
			color: white;
        }

        /* Estilo para el botón de búsqueda */
        .search-user button {
            background-color: #cc1939;
            color: white;
            border: none;
            transition: background-color 0.3s;
        }

        .search-user button:hover {
            background-color: #a2182f;
        }

        /* Estilo para el botón de eliminar búsqueda */
        .clear-search-btn {
            background-color: #ddd;
            color: #333;
        }

        .clear-search-btn:hover {
            background-color: #bbb;
        }

        /* Estilo para la tabla */
        .woocommerce-orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .woocommerce-orders-table th,
        .woocommerce-orders-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .woocommerce-orders-table th {
            background-color: #f4f4f4;
        }

        .woocommerce-orders-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .woocommerce-orders-table tr:hover {
            background-color: #f1f1f1;
        }

        /* Paginación */
        .custom-pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .custom-pagination a {
            text-decoration: none;
            padding: 10px 15px;
            border: 1px solid #ccc;
            background-color: #f8f8f8;
            color: #333;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .custom-pagination a:hover {
            background-color: #cc1939;
            color: #fff;
        }

        .custom-pagination a.current {
            background-color: #cc1939;
            color: #fff;
            font-weight: bold;
        }

        /* Estilos Responsivos */
        @media (max-width: 768px) {
            .search-user {
                flex-direction: column;
                gap: 10px;
            }

            .search-user input {
                width: 100%;
            }

            .woocommerce-orders-table th,
            .woocommerce-orders-table td {
                padding: 8px;
            }
        }
    </style>';

	// Verificar si el usuario tiene permisos de administrador
	if (!is_user_logged_in() || !current_user_can('gbs_manage_users')) {
		echo '<p>No tienes permisos para acceder a esta página.</p>';
		return;
	}

	echo '<div class="gbs-container">';

	// Mostrar formulario de búsqueda
?>
	<form method="get" class="search-user">
		<a href="<?php echo esc_url(wc_get_account_endpoint_url('crear-usuario')); ?>" class="create-user-btn">Nuevo Usuario</a>
		<input type="text" id="search-user" name="buscar_usuario" placeholder="Buscar por nombre, email o usuario" value="<?php echo isset($_GET['buscar_usuario']) ? esc_attr($_GET['buscar_usuario']) : ''; ?>" />
		<button type="submit">Buscar</button>
		<?php if (isset($_GET['buscar_usuario']) && !empty($_GET['buscar_usuario'])) : ?>
			<button type="button" class="clear-search-btn" onclick="window.location.href='<?php echo esc_url(remove_query_arg('buscar_usuario')); ?>';">Eliminar Búsqueda</button>
		<?php endif; ?>
	</form>

	<!-- Mostrar resultados -->
	<div id="usuarios-resultados">
		<?php gbs_mostrar_usuarios(); ?>
	</div>

	</div>

<?php
}

// Función para mostrar los usuarios con paginación
function gbs_mostrar_usuarios()
{
	// Variables de búsqueda y paginación
	$buscar = isset($_GET['buscar_usuario']) ? sanitize_text_field($_GET['buscar_usuario']) : '';
	$pagina_actual = isset($_GET['pagina']) ? absint($_GET['pagina']) : 1;
	$limite = 10;
	$offset = ($pagina_actual - 1) * $limite;

	// Argumentos para obtener los usuarios
	$args = [
		'number'  => $limite,
		'offset'  => $offset,
		'orderby' => 'registered',
		'order'   => 'DESC',
	];

	// Si hay un término de búsqueda, filtrar usuarios por nombre de usuario, correo o nombre completo
	if (!empty($buscar)) {
		$args['search'] = '*' . esc_attr($buscar) . '*';
		$args['search_columns'] = ['user_login', 'user_email', 'display_name'];
	}

	// Obtener los usuarios con los parámetros definidos
	$usuarios = get_users($args);

	// Mostrar la tabla de usuarios
	if ($usuarios) {
		echo '<table class="woocommerce-orders-table">';
		echo '<thead><tr><th>ID Usuario</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Acciones</th></tr></thead><tbody>';
		foreach ($usuarios as $usuario) {
			echo '<tr>';
			echo '<td>' . esc_html($usuario->ID) . '</td>';
			echo '<td>' . esc_html($usuario->display_name) . '</td>';
			echo '<td>' . esc_html($usuario->user_email) . '</td>';
			echo '<td>' . esc_html(implode(', ', $usuario->roles)) . '</td>';
			echo '<td><a href="' . esc_url(add_query_arg('user_id', $usuario->ID, wc_get_account_endpoint_url('editar-usuario'))) . '" class="edit-user">Editar</a></td>';
			echo '</tr>';
		}
		echo '</tbody></table>';

		// Paginación
		gbs_paginacion($pagina_actual, $limite, $buscar);
	} else {
		echo '<p>No se encontraron usuarios.</p>';
	}
}

// Función para manejar la paginación
function gbs_paginacion($pagina_actual, $limite, $buscar)
{
	$total_usuarios = count_users();
	$total_registros = $total_usuarios['total_users'];
	$total_paginas = ceil($total_registros / $limite);

	if ($total_paginas > 1) {
		echo '<div class="custom-pagination">';

		// Enlace a la página anterior
		if ($pagina_actual > 1) {
			echo '<a href="?pagina=' . ($pagina_actual - 1) . '&buscar_usuario=' . esc_attr($buscar) . '" class="prev">« Anterior</a>';
		}

		// Enlaces a las páginas
		for ($i = 1; $i <= $total_paginas; $i++) {
			echo '<a href="?pagina=' . $i . '&buscar_usuario=' . esc_attr($buscar) . '" class="' . ($i == $pagina_actual ? 'current' : '') . '">' . $i . '</a>';
		}

		// Enlace a la página siguiente
		if ($pagina_actual < $total_paginas) {
			echo '<a href="?pagina=' . ($pagina_actual + 1) . '&buscar_usuario=' . esc_attr($buscar) . '" class="next">Siguiente »</a>';
		}

		echo '</div>';
	}
}

// Función para eliminar la búsqueda cuando se hace clic en el botón de "Eliminar Búsqueda"
function gbs_eliminar_busqueda()
{
	// Eliminamos el término de búsqueda
	if (isset($_GET['buscar_usuario'])) {
		unset($_GET['buscar_usuario']);
	}
}

// Hook para la acción de eliminar búsqueda
add_action('wp_ajax_gbs_eliminar_busqueda', 'gbs_eliminar_busqueda');
add_action('wp_ajax_nopriv_gbs_eliminar_busqueda', 'gbs_eliminar_busqueda');

// Registrar el endpoint en WooCommerce
add_action('woocommerce_account_gestion-usuarios_endpoint', 'gbs_mostrar_contenido_gestion_usuarios');

// Agregar "Gestión de Usuarios" al menú de WooCommerce
function gbs_agregar_menu_gestion_usuarios($items)
{
	// Verificar si el usuario tiene permisos de administrador
	if (current_user_can('gbs_manage_users')) {
		$new_items = array('gestion-usuarios' => 'Gestión de Usuarios');
		$items = array_merge($new_items, $items);
	}

	return $items;
}
add_filter('woocommerce_account_menu_items', 'gbs_agregar_menu_gestion_usuarios');
