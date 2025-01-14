<?php

/**
 * Plugin Name:     Plugin GBS
 * Plugin URI:      https://gbs.mx
 * Description:     Plugin que gestiona los pedidos y usuarios de GBS.
 * Author:          Elioth Novoa
 * Author URI:      gbs.mx
 * Text Domain:     gbsPlugin
 * Domain Path:     /languages
 * Version:         0.0.3
 *
 * @package         GbsPlugin
 */

// Asegurarse de que el archivo se carga en WordPress
if (!defined('ABSPATH')) {
	exit;
}
define('PLUGIN_FILE', __FILE__);
define('PLUGIN_DIR', plugin_dir_path(PLUGIN_FILE));
define('PLUGIN_URL', plugin_dir_url(PLUGIN_FILE));

function gbs_plugin_init()
{
	if (class_exists('WooCommerce')) {
		$includes = [
			'class-wc-gateway-a-cuenta.php',
			'order-management.php',
			'order-detail.php',
			'checkout-fields.php',
			'outstanding-balance.php',
			'users-manager.php',
			'user-edit.php',
			'create-user.php',
			'status-update.php',
			'license-restrictions.php'
		];

		foreach ($includes as $file) {
			require_once PLUGIN_DIR . 'includes/' . $file;
			error_log($file . ' cargado correctamente.');
		}

		add_filter('woocommerce_payment_gateways', function ($gateways) {
			$gateways[] = 'WC_Gateway_A_Cuenta';
			return $gateways;
		});
	}
}
add_action('plugins_loaded', 'gbs_plugin_init');
function gbs_registrar_endpoints()
{
	$endpoints = ['gestion-pedidos', 'detalles-pedido', 'gestion-usuarios', 'editar-usuario', 'crear-usuario'];
	foreach ($endpoints as $endpoint) {
		add_rewrite_endpoint($endpoint, EP_ROOT | EP_PAGES);
	}
}
add_action('init', 'gbs_registrar_endpoints');

register_activation_hook(__FILE__, function () {
	flush_rewrite_rules();
});

// 4. Cargar estilos y scripts en la página "Mi Cuenta"
function gbs_plugin_enqueue_styles()
{
	if (is_account_page()) {
		wp_enqueue_style(
			'gbs-plugin-style',
			PLUGIN_URL . 'src/css/style.css',
			array(),
			'1.0',
			'all'
		);
	}
}
add_action('wp_enqueue_scripts', 'gbs_plugin_enqueue_styles');
// Encolar los scripts necesarios para AJAX
function gbs_encolar_scripts_ajax()
{
	// Asegurarse de que el script solo se cargue en la página de gestión de usuarios
	if (is_page('gestion-usuarios') || is_admin()) {
		// Encolar el script 'ajax.js'
		wp_enqueue_script('gbs-ajax-search', plugin_dir_url(__FILE__) . 'includes/js/ajax.js', array('jquery'), null, true);

		// Localizar el script para que pueda hacer uso de la URL de AJAX
		wp_localize_script('gbs-ajax-search', 'gbs_ajax_obj', array(
			'ajax_url' => admin_url('admin-ajax.php'), // URL de AJAX
		));
	}
}
add_action('wp_enqueue_scripts', 'gbs_encolar_scripts_ajax', 20);



// 5. Filtrar la visibilidad del método de pago "A Cuenta"
add_filter('woocommerce_available_payment_gateways', function ($gateways) {
	if (is_user_logged_in()) {
		$user = wp_get_current_user();
		if (!in_array('empresa_convenio', (array) $user->roles)) {
			unset($gateways['a_cuenta']);
		}
	}
	return $gateways;
});

// 6. Funciones para manejar los campos de usuario (empresa y saldo pendiente)
// Función para agregar campos personalizados en el perfil del usuario
function
gbs_agregar_campos_personalizados($user)
{
	// Verificar si el usuario tiene permiso para editar el perfil
	if (current_user_can('edit_user', $user->ID)) {
?>
		<h3>Información Adicional</h3>
		<table class="form-table">
			<!-- Campo Nombre Comercial -->
			<tr>
				<th><label for="nombre_comercial">Nombre Comercial</label></th>
				<td><input type="text" name="nombre_comercial" value="<?php echo esc_attr(get_user_meta($user->ID, 'nombre_comercial', true)); ?>" class="regular-text" /></td>
			</tr>
			<!-- Campo Razón Social -->
			<tr>
				<th><label for="razon_social">Razón Social</label></th>
				<td><input type="text" name="razon_social" value="<?php echo esc_attr(get_user_meta($user->ID, 'razon_social', true)); ?>" class="regular-text" /></td>
			</tr>
			<!-- Campo Código Hospitalario -->
			<tr>
				<th><label for="codigo_hospitalario">Código Hospitalario</label></th>
				<td><input type="text" name="codigo_hospitalario" value="<?php echo esc_attr(get_user_meta($user->ID, 'codigo_hospitalario', true)); ?>" class="regular-text" /></td>
			</tr>
			<!-- Campo Empresa -->
			<tr>
				<th><label for="empresa_convenio">Empresa</label></th>
				<td><input type="text" name="empresa_convenio" value="<?php echo esc_attr(get_user_meta($user->ID, 'empresa_convenio', true)); ?>" class="regular-text" /></td>
			</tr>
			<!-- Campo Vigencia de Licencia Sanitaria -->
			<tr>
				<th><label for="vig_lic_sanitaria">Fecha de Vigencia</label></th>
				<td><input type="date" name="vig_lic_sanitaria" value="<?php echo esc_attr(get_user_meta($user->ID, 'vig_lic_sanitaria', true)); ?>" /></td>
			</tr>
		</table>

	<?php
	}
}
add_action('show_user_profile', 'gbs_agregar_campos_personalizados');
add_action('edit_user_profile', 'gbs_agregar_campos_personalizados');

// Función para guardar los campos personalizados
// Función para guardar los campos personalizados
function gbs_guardar_campos_personalizados($user_id)
{
	// Verificar si el usuario tiene permiso para editar el perfil
	if (current_user_can('edit_user', $user_id)) {
		// Guardar los valores de los campos
		if (isset($_POST['nombre_comercial'])) {
			update_user_meta($user_id, 'nombre_comercial', sanitize_text_field($_POST['nombre_comercial']));
		}
		if (isset($_POST['razon_social'])) {
			update_user_meta($user_id, 'razon_social', sanitize_text_field($_POST['razon_social']));
		}
		if (isset($_POST['codigo_hospitalario'])) {
			update_user_meta($user_id, 'codigo_hospitalario', sanitize_text_field($_POST['codigo_hospitalario']));
		}
		if (isset($_POST['empresa_convenio'])) {
			update_user_meta($user_id, 'empresa_convenio', sanitize_text_field($_POST['empresa_convenio']));
		}
		if (isset($_POST['vig_lic_sanitaria'])) {
			update_user_meta($user_id, 'vig_lic_sanitaria', sanitize_text_field($_POST['vig_lic_sanitaria']));
		}
	}
}
add_action('personal_options_update', 'gbs_guardar_campos_personalizados');
add_action('edit_user_profile_update', 'gbs_guardar_campos_personalizados');

// Funciones para manejar el saldo pendiente
function agregar_campo_saldo($user)
{
	if (current_user_can('edit_user', $user->ID)) {
	?>
		<h3>Saldo Pendiente</h3>
		<table class="form-table">
			<tr>
				<th><label for="saldo_pendiente">Saldo Adeudado</label></th>
				<td><input type="number" step="0.01" name="saldo_pendiente" value="<?php echo esc_attr(get_user_meta($user->ID, 'saldo_pendiente', true)); ?>" class="regular-text" /></td>
			</tr>
		</table>
	<?php
	}
}
add_action('show_user_profile', 'agregar_campo_saldo');
add_action('edit_user_profile', 'agregar_campo_saldo');

// Guardar el saldo pendiente
function guardar_campo_saldo($user_id)
{
	if (current_user_can('edit_user', $user_id)) {
		update_user_meta($user_id, 'saldo_pendiente', sanitize_text_field($_POST['saldo_pendiente']));
	}
}
add_action('personal_options_update', 'guardar_campo_saldo');
add_action('edit_user_profile_update', 'guardar_campo_saldo');

// 7. Cambiar el título de las páginas "Mi Cuenta"
add_filter('the_title', 'custom_account_endpoint_titles');
function custom_account_endpoint_titles($title)
{
	global $wp_query;

	// Si estamos en la página de "Gestión de Pedidos"
	if (isset($wp_query->query_vars['gestion-pedidos']) && in_the_loop()) {
		return 'Gestor de Pedidos';
	}

	// Si estamos en la página de "Gestión de Usuarios"
	if (isset($wp_query->query_vars['gestion-usuarios']) && in_the_loop()) {
		return 'Gestor de Usuarios';
	}
	if (isset($wp_query->query_vars['crear-usuario']) && in_the_loop()) {
		return 'Crear Nuevo Usuario';
	}

	// Si estamos en la página de "editar usuario"
	if (isset($wp_query->query_vars['editar-usuario']) && in_the_loop()) {
		// Verificar que el user_id esté presente en la URL
		if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
			$user_id = absint($_GET['user_id']);
			$usuario_nombre = obtener_nombre_usuario($user_id);
			return 'Editar Usuario: ' . esc_html($usuario_nombre);
		}
	}
	return $title;
}

add_action('wp_footer', 'sallar_woocommerce_custom_update_checkout', 50);

function ocultar_opciones_mi_cuenta_por_rol($menu_links)
{
	if (is_user_logged_in()) {
		$user = wp_get_current_user();
		error_log('Rol del usuario: ' . implode(', ', $user->roles)); // Diagnóstico de roles
		$roles_restringidos = array('laboratorio_tienda');

		if (array_intersect($roles_restringidos, $user->roles)) {
			unset($menu_links['downloads']);
			unset($menu_links['orders']);
			unset($menu_links['edit-address']);
		}
	}
	return $menu_links;
}

add_filter('woocommerce_package_rates', 'limitar_envio_domicilio_por_cantidad', 10, 2);

function limitar_envio_domicilio_por_cantidad($rates, $package)
{
	$cantidad_minima = 5;
	$total_productos = WC()->cart->get_cart_contents_count();

	// Recorrer los métodos y eliminar los que no cumplan la condición
	foreach ($rates as $rate_id => $rate) {
		// Ocultar solo 'flat_rate' y 'free_shipping' si no se cumple la cantidad mínima
		if (($rate->method_id === 'free_shipping') && $total_productos < $cantidad_minima) {
			unset($rates[$rate_id]);
		}
	}
	return $rates;
}



add_filter('woocommerce_account_menu_items', 'ocultar_opciones_mi_cuenta_por_rol', 99);
// Hook para modificar la página de la tienda
add_action('woocommerce_before_shop_loop', 'custom_woocommerce_product_table');

function custom_woocommerce_product_table()
{
	if (! is_shop()) return; // Solo afecta la página de la tienda

	// Empieza la tabla
	echo '<table class="shop_table" style="width:100%; border-collapse: collapse; border: 1px solid #ddd;">';
	echo '<thead>
            <tr>
                <th>Imagen</th>
                <th>Producto</th>
                <th>Precio</th>
                <th>Variaciones</th>
                <th>Añadir al Carrito</th>
            </tr>
          </thead>';
	echo '<tbody>';

	// Query para los productos
	while (have_posts()) : the_post();
		global $product;
		if ($product->is_type('variable')) {
			echo '<tr>';
			// Imagen del producto
			echo '<td>' . get_the_post_thumbnail($product->get_id(), 'thumbnail') . '</td>';
			// Nombre del producto
			echo '<td>' . get_the_title() . '</td>';
			// Precio
			echo '<td>' . $product->get_price_html() . '</td>';

			// Selector de variaciones
			echo '<td>';
			woocommerce_variable_add_to_cart();
			echo '</td>';

			echo '</tr>';
		}
	endwhile;

	echo '</tbody>';
	echo '</table>';

	// Detener el loop de WooCommerce para que no duplique
	remove_action('woocommerce_before_shop_loop', 'custom_woocommerce_product_table');
}

function sallar_woocommerce_custom_update_checkout()
{
	if (is_checkout()) {
	?>
		<script type="text/javascript">
			jQuery(document).ready($ => {

				$('input').on('change', () => {

					$('body').off('update_checkout');

				});

			});
		</script>

<?php }
}
