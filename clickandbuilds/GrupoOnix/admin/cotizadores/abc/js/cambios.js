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
		var formdata = new FormData($('#frm-marca')[0]);
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=saveRecord',
			data: formdata,
			processData: false,
			contentType: false,
			dataType:'json',
			beforeSend: function(){
				$('input, file, textarea, button, select').attr('disabled','disabled');
			},
			error: function(){
				$('input, file, textarea, button, select').removeAttr('disabled');
				bootbox.dialog({
					message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
					buttons: { cerrar: { label: "Cerrar" } }
				});
			},
			success: function(result){
				$('input, file, textarea, button, select').removeAttr('disabled');
				bootbox.dialog({
					message: result.msg,
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								if(result.error) {
									$('#' + result.focus).focus();
								} else {
									// Only add to calendar if password was actually changed
									if(result.password_changed) {
										$.ajax({
											type: 'POST',
											url: 'include/Libs.php?accion=showRecord',
											dataType: 'json',
											data: { id: $("#id").val() },
											success: function(record) {
												if(!record.error) {
													const titulo = record.titulo;
													const fechaCambio = record.fecha_cambio; // use DB timestamp

													// Add to calendar
													if (window.calendar && typeof window.calendar.addEvent === 'function') {
														window.calendar.addEvent({
															title: `Contraseña cambiada: "${titulo}"`,
															start: fechaCambio,
															allDay: true,
															backgroundColor: '#28a745',
															borderColor: '#28a745'
														});
													}

													console.log('Password change event added to calendar:', titulo, fechaCambio);
												}
											}
										});
									}
									
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
				$("#user").val(result.user);
				$("#password").val(result.password);
				$("#titulo").val(result.titulo);
				$("#liga").val(result.liga);
				$("#imagen").after(result.imagen);
			}
			else {
				window.location = "index.php";
			}
		}
	});	
}