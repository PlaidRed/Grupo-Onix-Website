$(document).ready(function(){
	getMarcas();

	$(document).on('click','.guardar',function(e) {
		e.preventDefault();
		var sorted = $( ".cont-marcas" ).sortable( "serialize");

		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=saveOrder',
			data: sorted,
			dataType:'json',
			beforeSend: function(){
				$('input, file, textarea, button, select').each(function(){
					$(this).attr('disabled','disabled');
				});
			},
			error: function(){
				$('input, file, textarea, button, select').each(function(){
					$(this).removeAttr('disabled');
				});
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
			success: function(result){
				$('input, file, textarea, button, select').each(function(){
					$(this).removeAttr('disabled');
				});
				bootbox.dialog({
					message: result.msg,
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								if(result.error) {
									bootbox.hideAll();
									$('#'+result.focus).focus();
								} else {
									window.location = "index.php";
								}
							}
						}
					}
				});
			}
		});

	});

});

/*
 * @author: Cynthia Castillo
 * 
 * Imprime las imágenes
 */
function getMarcas(){
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getMarcas',
		dataType:'json',
		beforeSend: function(){
			$('#cont-marcas').html("<div class='loader'></div>");
		},
		error: function(){
			bootbox.dialog({
				message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							bootbox.hideAll();
							window.location = "index.php";
						}
					}
				}
			});
		},
		success: function(result){
			if(!result.error){

				$(".cont-marcas").html(result.marcas);

				$( function() {
					$( "#sortable" ).sortable();
				} );
			}
			else {
				window.location = "index.php";
			}
		}
	});
}