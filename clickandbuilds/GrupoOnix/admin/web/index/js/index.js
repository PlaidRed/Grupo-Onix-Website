var productos = 0;
var aseguradoras = 0;
$(document).ready(function(){

	getRecord();
	
	/*
	 * @author: Cynthia Castillo
	 * 
	 * Guardar
	 */
	$(document).on('click','.guardar',function(e) {
		e.preventDefault();
		$('#frm-marca').submit();
	});

	$(document).on('submit','#frm-marca',function(e) {
		e.preventDefault();
    var formdata = new FormData($('form[id="frm-marca"]')[0]);
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=saveRecord',
			data: formdata,
		    processData: false,
		    contentType: false,
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


function getRecord() {
	params = {};
	params.id = $("#id").val();
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=showRecord',
		dataType:'json',
		data: params,
		beforeSend: function(){
			$('#table-content').html("<div class='loader'></div>");
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
				$("#home_banner_txt").val(result.home_banner_txt);

				$("#home_compania_txt").html(result.home_compania_txt);

				$("#home_productos_txt").html(result.home_productos_txt);

				$(".cont-home_banner_img").html(result.home_banner_img);

				$('#home_banner_txt').summernote({
				    fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
				    tabsize: 2,
				    height: 300
				});
				
			}
			else {
				window.location = "index.php";
			}
		}
	});	
}