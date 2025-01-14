<?php
// Agregar un campo personalizado al pedido para el estado interno
function agregar_campo_estado_interno($post_id)
{
	$order = wc_get_order($post_id);
	woocommerce_wp_select(array(
		'id' => '_estado_interno',
		'label' => 'Estado Interno',
		'options' => array(
			'pendiente' => 'Pendiente',
			'validacion' => 'Validación de grupo sanguíneo',
			'anticuerpos' => 'Rastro de anticuerpos',
			'compatibilidad' => 'Compatibilidad',
			'liberacion' => 'Liberación'
		)
	));
}
add_action('woocommerce_admin_order_data_after_order_details', 'agregar_campo_estado_interno');

// Guardar el estado interno al actualizar el pedido
function guardar_estado_interno_personalizado($post_id)
{
	$estado_interno = isset($_POST['_estado_interno']) ? sanitize_text_field($_POST['_estado_interno']) : '';
	update_post_meta($post_id, '_estado_interno', $estado_interno);
}
add_action('woocommerce_process_shop_order_meta', 'guardar_estado_interno_personalizado');
// Mostrar el estado interno en la lista de pedidos
function mostrar_estado_interno_en_lista($columns)
{
	$columns['estado_interno'] = 'Estado Interno';
	return $columns;
}
add_filter('manage_edit-shop_order_columns', 'mostrar_estado_interno_en_lista');

function contenido_estado_interno_en_lista($column, $post_id)
{
	if ($column == 'estado_interno') {
		$estado_interno = get_post_meta($post_id, '_estado_interno', true);
		echo esc_html($estado_interno);
	}
}
add_action('manage_shop_order_posts_custom_column', 'contenido_estado_interno_en_lista', 10, 2);
