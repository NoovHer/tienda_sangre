<?php
function gbs_mostrar_formulario_editar_usuario()
{
	// Verificar si estamos en el endpoint "editar-usuario"
	if (is_page() && isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
		$user_id = absint($_GET['user_id']);
		$usuario = get_user_by('id', $user_id);

		// Verificar si el usuario existe
		if ($usuario) {
			// Cargar los datos del usuario en variables
			$user_login = esc_attr($usuario->user_login);
			$user_email = esc_attr($usuario->user_email);
			$nombre_comercial = esc_attr(get_user_meta($user_id, 'nombre_comercial', true));
			$razon_social = esc_attr(get_user_meta($user_id, 'razon_social', true));
			$codigo_hospitalario = esc_attr(get_user_meta($user_id, 'codigo_hospitalario', true));
			$codigo_cnts = esc_attr(get_user_meta($user_id, 'codigo_cnts', true));
			$rfc_hospitalario = esc_attr(get_user_meta($user_id, 'rfc_hospitalario', true));
			$licencia_sanitaria = esc_attr(get_user_meta($user_id, 'licencia_sanitaria', true));
			$cedula = esc_attr(get_user_meta($user_id, 'cedula', true));
			$responsable_sanitario = esc_attr(get_user_meta($user_id, 'responsable_sanitario', true));
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
					font-size: 14px;
					transition: border-color 0.3s ease, box-shadow 0.3s ease;
					box-sizing: border-box;
					/* Asegura que el padding y el borde se sumen correctamente */
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
					font-size: 16px;
					font-weight: bold;
					border: none;
					padding: 12px;
					border-radius: 5px;
					cursor: pointer;
					transition: background-color 0.3s ease, transform 0.2s ease;
					width: 100%;
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
			<form method="post" class="gbs-edit-form">
				<div class="form-row">
					<label for="user_login">Nombre de Usuario:</label>
					<input type="text" name="user_login" id="user_login" value="<?php echo $user_login; ?>" required />
				</div>

				<div class="form-row">
					<label for="user_email">Correo Electrónico:</label>
					<input type="email" name="user_email" id="user_email" value="<?php echo $user_email; ?>" required />
				</div>

				<div class="form-row">
					<label for="nombre_comercial">Nombre Comercial:</label>
					<input type="text" name="nombre_comercial" id="nombre_comercial" value="<?php echo $nombre_comercial; ?>" required />
				</div>

				<div class="form-row">
					<label for="razon_social">Razón Social:</label>
					<input type="text" name="razon_social" id="razon_social" value="<?php echo $razon_social; ?>" required />
				</div>

				<div class="form-row">
					<label for="codigo_hospitalario">Código Hospitalario:</label>
					<input type="text" name="codigo_hospitalario" id="codigo_hospitalario" value="<?php echo $codigo_hospitalario; ?>" required />
				</div>

				<div class="form-row">
					<label for="licencia_sanitaria">Licencia Sanitaria:</label>
					<input type="text" name="licencia_sanitaria" id="licencia_sanitaria" value="<?php echo $licencia_sanitaria ?>" required />
				</div>

				<div class="form-row">
					<label for="responsable_sanitario">Responsable Sanitario:</label>
					<input type="text" name="responsable_sanitario" id="responsable_sanitario" value="<?php echo $responsable_sanitario ?>" required />
				</div>

				<div class="form-row">
					<label for="cedula">Cédula:</label>
					<input type="text" name="cedula" id="cedula" value="<?php echo $cedula ?>" required />
				</div>

				<div class="form-row">
					<label for="rfc_hospitalario">RFC Hospitalario:</label>
					<input type="text" name="rfc_hospitalario" id="rfc_hospitalario" value="<?php echo $rfc_hospitalario ?>" required />
				</div>

				<div class="form-row">
					<label for="codigo_cnts">Código CNTS:</label>
					<input type="text" name="codigo_cnts" id="codigo_cnts" value="<?php echo $codigo_cnts; ?>" required />
				</div>

				<div class="form-row">
					<label for="roles">Roles:</label>
					<div id="roles">
						<?php
						// Obtener todos los roles disponibles
						$roles = wp_roles()->roles;
						$user_roles = $usuario->roles; // Roles actuales del usuario

						// Definir los roles que se mostrarán en el formulario
						$roles_disponibles = ['empresa_convenio', 'laboratorio_tienda', 'logistica'];

						foreach ($roles as $role_key => $role) {
							if (in_array($role_key, $roles_disponibles)) {
								$checked = in_array($role_key, $user_roles) ? 'checked' : '';
								echo '<label><input type="checkbox" name="roles[]" value="' . esc_attr($role_key) . '" ' . $checked . '> ' . esc_html($role['name']) . '</label><br>';
							}
						}
						?>
					</div>
				</div>

				<div class="form-row">
					<input type="submit" name="guardar_usuario" value="Guardar Cambios" class="button-submit" />
				</div>
			</form>

<?php
			// Procesar el formulario cuando se envíe
			if (isset($_POST['guardar_usuario'])) {
				gbs_procesar_edicion_usuario($user_id, $_POST);
			}
		} else {
			echo '<p>Usuario no encontrado.</p>';
		}
	}
}
add_action('woocommerce_account_editar-usuario_endpoint', 'gbs_mostrar_formulario_editar_usuario');


// Procesar los datos del formulario de edición
function gbs_procesar_edicion_usuario($user_id, $data)
{
	// Validar y sanitizar los datos
	$user_login = sanitize_text_field($data['user_login']);
	$user_email = sanitize_email($data['user_email']);
	$display_name = sanitize_text_field($data['display_name']);
	$company_name = sanitize_text_field($data['company_name']);

	$rfc_hospitalario = sanitize_text_field($data['rfc_hospitalario']);
	$codigo_cnts = sanitize_text_field($data['codigo_cnts']);
	$cedula = sanitize_text_field($data['cedula']);
	$licencia_sanitaria = sanitize_text_field($data['licencia_sanitaria']);
	$responsable_sanitario = sanitize_text_field($data['responsable_sanitario']);

	// Asegurarse de que $_POST['roles'] sea un array
	$roles = isset($data['roles']) && is_array($data['roles']) ? array_map('sanitize_text_field', $data['roles']) : [];

	// Actualizar los datos del usuario
	$user_data = [
		'ID' => $user_id,
		'user_login' => $user_login,
		'user_email' => $user_email,
		'display_name' => $display_name,

	];

	// Actualizar el usuario en la base de datos
	$updated_user_id = wp_update_user($user_data);

	if (is_wp_error($updated_user_id)) {
		echo '<p class="error">Error al guardar los cambios.</p>';
	} else {
		// Guardar metas de usuario, incluyendo los nuevos campos
		update_user_meta($updated_user_id, 'company_name', $company_name);
		update_user_meta($updated_user_id, 'rfc_hospitalario', $rfc_hospitalario);
		update_user_meta($updated_user_id, 'codigo_cnts', $codigo_cnts);
		update_user_meta($updated_user_id, 'cedula', $cedula);
		update_user_meta($updated_user_id, 'licencia_sanitaria', $licencia_sanitaria);
		update_user_meta($updated_user_id, 'responsable_sanitario', $responsable_sanitario);
		// Obtener el objeto WP_User y actualizar los roles
		$user = new WP_User($updated_user_id);
		$user->set_role(''); // Elimina todos los roles actuales

		foreach ($roles as $role) {
			$user->add_role($role); // Añadir cada rol seleccionado
		}

		echo '<p class="success">Usuario actualizado correctamente.</p>';
	}
}
function obtener_nombre_usuario($user_id)
{
	// Verificar si el user_id es válido
	$usuario = get_user_by('id', $user_id);
	if ($usuario) {
		return $usuario->display_name;
	}
	return '';
}
