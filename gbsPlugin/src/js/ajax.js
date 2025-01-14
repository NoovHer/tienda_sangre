jQuery(document).ready(function ($) {
	$('#search-user').on('input', function () {
		var searchTerm = $(this).val();

		// Hacer la petición AJAX
		$.ajax({
			url: gbs_ajax_obj.ajax_url, // Aquí se asegura que se usa el AJAX de WordPress
			type: 'GET',
			data: {
				action: 'gbs_buscar_usuarios', // Acción que se maneja en WordPress
				buscar_usuario: searchTerm
			},
			success: function (response) {
				$('#usuarios-resultados').html(response);
			},
			error: function () {
				$('#usuarios-resultados').html('Hubo un error al cargar los resultados.');
			}
		});
	});
});
