<?php
// Registrar un endpoint para "Crear Usuario"
function gbs_registrar_endpoint_crear_usuario()
{
	add_rewrite_endpoint('crear-usuario', EP_ROOT | EP_PAGES);
}
add_action('init', 'gbs_registrar_endpoint_crear_usuario');

// Mostrar el formulario de creación de usuario en el endpoint
function gbs_mostrar_formulario_crear_usuario()
{
	// Verificar si el usuario tiene permisos
	if (!current_user_can('gbs_manage_users')) {
		echo '<p>No tienes permisos para crear un usuario.</p>';
		return;
	}

	// Mostrar formulario para crear un nuevo usuario
?>
	<style>
		/* Estilos generales para el formulario */
		form {
			max-width: 800px;
			margin: 40px auto;
			padding: 30px;
			border: 1px solid #ccc;
			border-radius: 10px;
			background: linear-gradient(135deg, #ffffff, #f7f7f7);
			box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
			font-family: Arial, sans-serif;
		}

		/* Estilos para las filas con flex */
		/* Estilos para las filas con flex */
		/* Estilos para las filas del formulario */
		form .form-row {
			display: flex;
			flex-direction: column;
			/* Establece los elementos en una sola columna */
			gap: 20px;
			/* Espacio entre los elementos */
			margin-bottom: 20px;
			/* Espacio debajo de cada fila */
		}

		/* Estilo para los divs dentro de las filas (los campos) */
		form .form-row>div {
			flex: 1 1 100%;
			/* Cada campo ocupa el 100% del ancho */
			box-sizing: border-box;
			/* Asegura que padding y bordes no sobrepasen el tamaño total */
		}

		/* Estilo para las etiquetas dentro de los divs */
		form .form-row label {
			font-weight: 600;
			color: #333;
			display: block;
			margin-bottom: 8px;
		}

		/* Estilos para los inputs */
		form .form-row input {
			width: 100%;
			/* Hace que los inputs ocupen el 100% del ancho disponible dentro de su contenedor */
			padding: 12px;
			border: 1px solid #bbb;
			border-radius: 5px;
			background-color: #fcfcfc;
			font-size: 14px;
			transition: border-color 0.3s ease, box-shadow 0.3s ease;
			box-sizing: border-box;
			/* Asegura que el padding y el borde se sumen correctamente */
		}

		/* Estilos para los inputs al recibir foco */
		form .form-row input:focus {
			border-color: #0073aa;
			box-shadow: 0 0 8px rgba(0, 115, 170, 0.3);
		}


		form #roles {
			margin-bottom: 20px;
		}

		form #roles label {
			display: flex;
			align-items: center;
			font-weight: normal;
			color: #555;
			margin-bottom: 10px;
		}

		form input[type="checkbox"] {
			width: auto;
			margin-right: 10px;
		}

		form button {
			background-color: #0073aa;
			color: white;
			font-size: 16px;
			font-weight: bold;
			border: none;
			padding: 12px;
			border-radius: 5px;
			cursor: pointer;
			transition: background-color 0.3s ease, transform 0.2s ease;
			width: 100%;
		}

		form button:hover {
			background-color: #005f8d;
			transform: scale(1.02);
		}

		/* Mensajes de error o éxito */
		form p {
			font-size: 14px;
			padding: 10px;
			border-radius: 4px;
			margin-bottom: 20px;
		}

		form p.error {
			background-color: #fdecea;
			color: #d9534f;
			border: 1px solid #f5c6cb;
		}

		form p.success {
			background-color: #eaffea;
			color: #28a745;
			border: 1px solid #c3e6cb;
		}
	</style>

	<form method="POST" action="">
	<label for="user_login">Nombre de Usuario:</label>
		<input type="text" name="user_login" id="user_login" required />

		<label for="user_email">Correo Electrónico:</label>
		<input type="email" name="user_email" id="user_email" required />

		<label for="nombre_comercial">Nombre Comercial:</label>
		<input type="text" name="nombre_comercial" id="nombre_comercial" required />

		<label for="razon_social">Razón Social:</label>
		<input type="text" name="razon_social" id="razon_social" required />

		<label for="licencia_sanitaria">Licencia Sanitaria:</label>
		<input type="text" name="licencia_sanitaria" id="licencia_sanitaria" required />

		<label for="responsable_sanitario">Responsable Sanitario:</label>
		<input type="text" name="responsable_sanitario" id="responsable_sanitario" required />

		<label for="cedula">Cédula:</label>
		<input type="text" name="cedula" id="cedula" required />

		<label for="codigo_hospitalario">Código Hospitalario:</label>
		<input type="text" name="codigo_hospitalario" id="codigo_hospitalario" required />

		<label for="rfc_hospitalario">RFC Hospitalario:</label>
		<input type="text" name="rfc_hospitalario" id="rfc_hospitalario" required />

		<label for="codigo_cnts">Código CNTS:</label>
		<input type="text" name="codigo_cnts" id="codigo_cnts" required />


		<div id="roles">
			<label for="roles">Roles:</label>
			<div id="roles">
				<?php
				// Obtener todos los roles disponibles
				$roles = wp_roles()->roles;

				// Definir los roles que se mostrarán en el formulario
				$roles_disponibles = ['empresa_convenio', 'laboratorio_tienda', 'logistica']; // Lista de roles permitidos

				foreach ($roles as $role_key => $role) {
					// Comprobar si el rol está en la lista de roles disponibles
					if (in_array($role_key, $roles_disponibles)) {
						echo '<label><input type="checkbox" name="roles[]" value="' . esc_attr($role_key) . '"> ' . esc_html($role['name']) . '</label><br>';
					}
				}
				?>
			</div>

			<button type="submit" name="crear_usuario" class="button">Crear Usuario</button>
	</form>

<?php
}
add_action('woocommerce_account_crear-usuario_endpoint', 'gbs_mostrar_formulario_crear_usuario');

// Procesar la creación de usuario desde el formulario
function gbs_procesar_creacion_usuario()
{
	if (isset($_POST['crear_usuario'])) {

		if (!current_user_can('gbs_manage_users')) {
			echo '<p>No tienes permisos para crear un usuario.</p>';
			return;
		}

		$user_login = sanitize_text_field($_POST['user_login']);
		$user_email = sanitize_email($_POST['user_email']);
		$nombre_comercial = sanitize_text_field($_POST['nombre_comercial']);
		$razon_social = sanitize_text_field($_POST['razon_social']);
		$codigo_hospitalario = sanitize_text_field($_POST['codigo_hospitalario']);
		$rfc_hospitalario = sanitize_text_field($_POST['rfc_hospitalario']);
		$codigo_cnts = sanitize_text_field($_POST['codigo_cnts']);
		$licencia_sanitaria = sanitize_text_field($_POST['licencia_sanitaria']);
		$responsable_sanitario = sanitize_text_field($_POST['responsable_sanitario']);
		$cedula = sanitize_text_field($_POST['cedula']);
		$roles = isset($_POST['roles']) ? array_map('sanitize_text_field', $_POST['roles']) : [];

		if (username_exists($user_login) || email_exists($user_email)) {
			echo '<p>Error: El nombre de usuario o el correo ya están registrados.</p>';
			return;
		}

		$user_pass = wp_generate_password();
		$user_id = wp_create_user($user_login, $user_pass, $user_email);

		if (is_wp_error($user_id)) {
			echo '<p>Error al crear el usuario: ' . $user_id->get_error_message() . '</p>';
		} else {
			$user = new WP_User($user_id);
			foreach ($roles as $role) {
				$user->add_role($role);
			}

			// Guardar metadatos
			update_user_meta($user_id, 'nombre_comercial', $nombre_comercial);
			update_user_meta($user_id, 'razon_social', $razon_social);
			update_user_meta($user_id, 'codigo_hospitalario', $codigo_hospitalario);
			update_user_meta($user_id, 'rfc_hospitalario', $rfc_hospitalario);
			update_user_meta($user_id, 'codigo_cnts', $codigo_cnts);
			update_user_meta($user_id, 'licencia_sanitaria', $licencia_sanitaria);
			update_user_meta($user_id, 'responsable_sanitario', $responsable_sanitario);
			update_user_meta($user_id, 'cedula', $cedula);

			// Enviar correo con detalles
			$mensaje = "Hola $user_login, tu cuenta ha sido creada exitosamente.\n\nUsuario: $user_login\nContraseña: $user_pass";
			wp_mail($user_email, 'Tu cuenta ha sido creada', $mensaje);

			echo '<p>¡Usuario creado con éxito!</p>';
		}
	}
}
add_action('init', 'gbs_procesar_creacion_usuario');

// Agregar los campos adicionales al perfil del usuario en el backend
function gbs_agregar_campos_perfil_usuario($user)
{
	if (!current_user_can('edit_user', $user->ID)) {
		return;
	}
?>
	<h3>Información Adicional del Hospital</h3>
	<table class="form-table">
		<tr>
			<th><label for="nombre_comercial">Nombre Comercial</label></th>
			<td><input type="text" name="nombre_comercial" value="<?php echo esc_attr(get_user_meta($user->ID, 'nombre_comercial', true)); ?>" /></td>
		</tr>
		<tr>
			<th><label for="razon_social">Razón Social</label></th>
			<td><input type="text" name="razon_social" value="<?php echo esc_attr(get_user_meta($user->ID, 'razon_social', true)); ?>" /></td>
		</tr>
		<tr>
			<th><label for="codigo_hospitalario">Código Hospitalario</label></th>
			<td><input type="text" name="codigo_hospitalario" value="<?php echo esc_attr(get_user_meta($user->ID, 'codigo_hospitalario', true)); ?>" /></td>
		</tr>
		<tr>
			<th><label for="rfc_hospitalario">RFC Hospitalario</label></th>
			<td><input type="text" name="rfc_hospitalario" value="<?php echo esc_attr(get_user_meta($user->ID, 'rfc_hospitalario', true)); ?>" /></td>
		</tr>
		<tr>
			<th><label for="codigo_cnts">Código CNTS</label></th>
			<td><input type="text" name="codigo_cnts" value="<?php echo esc_attr(get_user_meta($user->ID, 'codigo_cnts', true)); ?>" /></td>
		</tr>
	</table>
<?php
}
add_action('show_user_profile', 'gbs_agregar_campos_perfil_usuario');
add_action('edit_user_profile', 'gbs_agregar_campos_perfil_usuario');

// Guardar los campos adicionales en el backend
function gbs_guardar_campos_perfil_usuario($user_id)
{
	if (!current_user_can('edit_user', $user_id)) {
		return;
	}

	update_user_meta($user_id, 'nombre_comercial', sanitize_text_field($_POST['nombre_comercial']));
	update_user_meta($user_id, 'razon_social', sanitize_text_field($_POST['razon_social']));
	update_user_meta($user_id, 'codigo_hospitalario', sanitize_text_field($_POST['codigo_hospitalario']));
	update_user_meta($user_id, 'rfc_hospitalario', sanitize_text_field($_POST['rfc_hospitalario']));
	update_user_meta($user_id, 'codigo_cnts', sanitize_text_field($_POST['codigo_cnts']));
}
add_action('personal_options_update', 'gbs_guardar_campos_perfil_usuario');
add_action('edit_user_profile_update', 'gbs_guardar_campos_perfil_usuario');
