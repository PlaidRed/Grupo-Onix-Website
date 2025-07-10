$(document).ready(function(){

	printRoot();

	//Expandimos carpeta
	$(document).on('click','.has_child.closed',function(e) {
		e.preventDefault();
		tree_header = $(this).attr('id'); //El header de la carpeta 
		console.log(tree_header);
		tree_id = $('#'+tree_header).parent().attr('id'); //El elemento principal
		$('.tree-selected').removeClass('tree-selected'); //Estilo de seleccionado
		$('#'+tree_header).addClass('tree-selected');
		$('#'+tree_header).removeClass('closed');
		$('#'+tree_header).addClass('opened');

		//Revisamos si es la carpeta root de un cliente en específico
		var accion = 'printFolder';
		var car_id = $('#'+tree_header).attr('data-id');

		//Revisa si tiene hijos
		if($('#'+tree_header).hasClass('has_child')) {
			//Revisa si no ha sido cargado anteriormente
			if($('#'+tree_header).hasClass('nc')) {
				printFolder(car_id);
			} else {
				//Si el contenido YA fue cargado, solamente lo mostramos de nuevo
				$('#'+tree_id).find('.tree-folder-content').css('display', 'block');
				//Cambiamos a que sea MINUS (para minimizar)
				$('#'+tree_id+'-fa').removeClass('fa-folder-plus');
				$('#'+tree_id+'-fa').addClass('fa-folder-minus');
			}
		}

	});

	//Minimizamos carpeta
	$(document).on('click','.has_child.opened',function(e) {
		e.preventDefault();
		tree_header = $(this).attr('id'); //El header de la carpeta 
		tree_id = $('#'+tree_header).parent().attr('id'); //El elemento principal
		$('.tree-selected').removeClass('tree-selected'); //Estilo de seleccionado
		$('#'+tree_header).addClass('tree-selected');
		$('#'+tree_header).addClass('closed');
		$('#'+tree_header).removeClass('opened');
		//Escondemos el contenido de la carpeta
		$('#'+tree_id).find('.tree-folder-content').css('display', 'none');
		$('#'+tree_id+'-fa').removeClass('fa-folder-minus');
		$('#'+tree_id+'-fa').addClass('fa-folder-plus');
	});

	$(document).on('click','.tree-item',function(e) {
		e.preventDefault();
		var fileId = $(this).attr('data-id');
		var params = {};
		params.fileId = fileId;
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=downloadFile',
			dataType:'json',
			data: params,
			beforeSend: function(){
				$('.load-'+fileId).html('<i class="fa fa-spinner fa-2x fa-spin"></i>');
			},
			error: function(){
				bootbox.dialog({
					message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								bootbox.hideAll();
								$('.load-'+fileId).html('');
								//window.location = "../";
							}
						}
					}
				});
			},
			success: function(result){
				$('.load-'+fileId).html('');
				if(!result.error) {
					window.open('include/files/'+result.file, '_blank');
				} else {
					bootbox.dialog({
						message: result.msg,
						buttons: {
							cerrar: {
								label: "Cerrar",
								callback: function() {
									bootbox.hideAll();
								}
							}
						}
					});
				}
			}
		});
	});

});

function printRoot() {
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=printRoot',
		dataType:'json',
		error: function(){
			bootbox.dialog({
				message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							bootbox.hideAll();
							//window.location = "../";
						}
					}
				}
			});
		},
		success: function(result){
			$('.tree').html(result.arbol);
		}
	});
}

function printFolder(fileId) {
	var params = {};
	params.fileId = fileId;
	tree_id = 'car-'+fileId;

	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=printCarpeta',
		dataType:'json',
		data: params,
		beforeSend: function(){
			$('#'+tree_id).find('.tree-loader').css("display", "block");
		},
		error: function(){
			bootbox.dialog({
				message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							bootbox.hideAll();
							$('#'+tree_id).find('.tree-loader').css("display", "none");
						}
					}
				}
			});
		},
		success: function(result){
			$('#'+tree_id).find('.tree-loader').css("display", "none");
			if(!result.error) {
				//Desplegamos contenido
				$('#'+tree_id).find('.tree-folder-content').css('display', 'block');
				$('#'+tree_id).find('.tree-folder-content').html(result.arbol);

				//Cambiamos a que sea MINUS (para minimizar)
				$('#'+tree_id+'-fa').removeClass('fa-folder-plus');
				$('#'+tree_id+'-fa').addClass('fa-folder-minus');

				//Quitamos que NO se ha cargado (porque ya tenemos el contenido)
				$('#'+tree_id+"-child").removeClass('nc');
			} else {
				bootbox.dialog({
					message: result.msg,
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								bootbox.hideAll();
							}
						}
					}
				});
			}
		}
	});
}