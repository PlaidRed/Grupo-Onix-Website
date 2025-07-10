$(document).ready(function(){

	var myDate = new Date();
	var prettyDate =myDate.getDate() + '/' + (myDate.getMonth()+1) + '/' + myDate.getFullYear();
	$("#fecha_ingreso").val(prettyDate);

	$( ".fecha" ).datepicker({
		dateFormat: "dd/mm/yy"
	});

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2013-01-03
	 * 
	 * Carga por medio de AJAX el select de Máquinas
	 */
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=showProfiles',
		dataType:'json',
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
			$('#container-sel').html(result.select);
		}
	});

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2014-01-02
	 * 
	 * Guardar
	 */
	$(document).on('click','.guardar',function(e) {
		e.preventDefault();
		$('#frm-usuario').submit();
	});

	$(document).on('submit','#frm-usuario',function(e) {
		e.preventDefault();
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=saveRecord',
			data: $('#frm-usuario').serialize(),
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