$(document).ready(function(){
	getRecords();

	/*
	 * @author: Cynthia Castillo
	 * @version: 0.1 2018­-08-30
	 * 
	 * Acción de Borrar Perfil
	 */
	$(document).on('click','.borrar',function(e){
		e.preventDefault();
		var nombre = $(this).attr("data-name");
		var id = $(this).attr("data-id");
		var params = {};
		params.id = id;
		params.accion = 'deleteRecord';
		bootbox.dialog({
			message: "¿Desea eliminar "+nombre+"?",
			buttons: {
				aceptar: {
					label: "Aceptar",
					className: "btn-primary",
					callback: function() {
						$.ajax({
							type:'post',
							data:params,
							url:'include/Libs.php',
							dataType:'json',
							error:function(){
								bootbox.dialog({
									message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
									buttons: {
										cerrar: {
											label: "Cerrar",
											callback: function() {
												bootbox.hideAll();
											}
										}
									}
								});
							},
							success:function(result){
								bootbox.dialog({
									message: result.msg,
									title: result.title,
									buttons: {
										cerrar: {
											label: "Cerrar",
											callback: function() {
												bootbox.hideAll();
												$('.table-clientes').DataTable().destroy();
												getRecords();
											}
										}
									}
								});
							}
						});
					}
				},
				cancelar: {
					label: "Cancelar",
					className: "btn-danger",
					callback: function() {
						$('.modal-dialog').modal('hide');
					}
				}
			}
		});
	});	

});

/*
 * @author: Cynthia Castillo
 * @version: 0.1 2018­-08-30
 * 
 * Imprime la tabla y le da la funcionalidad adecuada
 */
function getRecords(){
	$('.table-clientes').DataTable( {
        "ajax": {
		    "url": "include/Libs.php?accion=printTable",
		    "type": "POST"
		},
		"language": {
		    "emptyTable":     "No se encontraron registros",
		    "info":           "Mostrando _START_ a _END_ de _TOTAL_",
		    "infoEmpty":      "Mostrando 0 de 0",
		    "lengthMenu":     "Mostrando _MENU_ registros",
		    "loadingRecords": "Cargando...",
		    "processing":     "Procesando...",
		    "search":         "Buscar:",
		    "zeroRecords":    "No se encontraron registros",
		    "paginate": {
		        "first":      "Primera",
		        "last":       "Última",
		        "next":       "Siguiente",
		        "previous":   "Anterior"
		    }
	    },
	    "pageLength": 50,
	    "bSort": false,
    } );
}