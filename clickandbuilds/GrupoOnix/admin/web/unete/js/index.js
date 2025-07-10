$(document).ready(function(){

	getRecord();

	$(document).on('click','.eliminar-ventaja',function(e) {

	});

	$(document).on('click','.agregar-ventaja',function(e) {
	    e.preventDefault();
	    var html_ventaja = '<div class="form-group row row-ventaja">'+
                            '  <div class="col-sm-10">'+
                            '    <textarea class="form-control" name="ventaja[]"></textarea>'+
                            '  </div>'+
                            '  <div>'+
                            '    <button type="button" class="btn btn-danger eliminar-ventaja">'+
                            '       <i class="ft-x"></i> Eliminar'+
                            '    </button>'+
                            '  </div>'+
                            '</div>';
        $('.ventajas-competitivas').append(html_ventaja);
	});
	
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
				$("#unete_banner_txt").val(result.unete_banner_txt);
				$("#unete_email").val(result.unete_email);

				$(".ventajas-competitivas").html(result.ventajas);

				$(".cont-unete_banner_img").html(result.unete_banner_img);

				$('#unete_banner_txt').summernote({
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