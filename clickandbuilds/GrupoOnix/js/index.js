$(document).ready(function(){
	/*Encargado de enviar el formulario de contacto*/
	$(document).on('submit', 'form', function(e) {
		e.preventDefault();
		$.ajax({
			url: "includes/contacto.php",
			type: 'POST',
			data: $(this).serialize(),
			dataType: 'JSON',
			beforeSend: function(){
				$('.container-alert').html('<div class="spinner-border"></div>');
				$('input, file, textarea, button, select').each(function(){
					$(this).attr('disabled','disabled');
				});
			},
			error: function () {
				var html_alert = '<div class="alert alert-danger" role="alert">'+
			                         'Experimentamos fallas técnicas. Intente más tarde.'+
			                         '  </div>';
			    $('.container-alert').html(html_alert);
			    $('input, file, textarea, button, select').each(function(){
					$(this).removeAttr('disabled');
				});  
			}, 
			success: function (result) {

				$('input, file, textarea, button, select').each(function(){
					$(this).removeAttr('disabled');
				}); 
				
				if (result.error == false) {
					$('form').each (function(){
						this.reset();
					});

					var html_alert = '<div class="alert alert-success" role="alert">'+
			                         result.msg+
			                         '  </div>';

				} else {
					$("form #"+result.focus).focus();

					var html_alert = '<div class="alert alert-danger" role="alert">'+
			                         result.msg+
			                         '  </div>';

				}

				$('.container-alert').html(html_alert);
			}

		});
	});

});