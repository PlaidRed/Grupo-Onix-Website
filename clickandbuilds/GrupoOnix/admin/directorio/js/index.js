$(document).ready(function(){
	getRecords();

	$(document).on('click','.btn-excel',function(e) {
		e.preventDefault();
		$.ajax({
	        type: 'POST',
	        url: 'include/Libs.php?accion=getExcel',
	        dataType:'json',
	        beforeSend: function() {
	            $("input, button").attr("disabled", "disabled");
	            $(".loader").html('<i class="ft-loader spinner font-medium-5"></i>');
	            $(".cont-guardar").html('');
	        },
	        error: function(){
	            bootbox.dialog({
	                message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
	                buttons: {
	                    cerrar: {
	                        label: "Cerrar",
	                        callback: function() {
	                            $(".loader").html("");
	                            $("input, button").removeAttr("disabled");
	                            $(".cont-guardar").html('');
	                            bootbox.hideAll();
	                        }
	                    }
	                }
	            });
	        },
	        success: function(result){
	            if(result.error) {
	                window.location = "index.php";
	            } else {
	                $(".loader").html("");
	                $("input, button").removeAttr("disabled");
	                $(".cont-guardar").html('<a class="a-excel" href="include/directorio.xlsx"><button type="button" class="btn btn-success"><i class="la la-download"></i>Descargar Excel</button></a>');
	            }
	            
	        }
	    });
	});


});

/*
 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
 * @version: 0.1 2013­12-27
 * 
 * Imprime la tabla y le da la funcionalidad adecuada
 */
function getRecords(){
	$('.table-admin').DataTable( {
        "ajax": {
		    "url": "include/Libs.php?accion=printTableAdmin",
		    "type": "POST"
		  },
		  "pageLength": 25,
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
	    }
    } );
    $('.table-usuario').DataTable( {
        "ajax": {
		    "url": "include/Libs.php?accion=printTable",
		    "type": "POST"
		  },
		  "pageLength": 25,
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
	    }
    } );
}