$(document).ready(function () {
	$("#frm-login").submit(function (e){
		e.preventDefault();
		$.ajax({
			url: 'include/Login.php?accion=login',
			type:'POST',
			dataType: 'JSON',
			data: $(this).serialize(),
			error: function () {
				$('#login-msg').html("Experimentamos fallas técnicas.");
				$('#login-info').fadeIn();
			},
			success: function (result){
				if (result.auth){
					document.location.href="./home.php";
				}else {
					$('#login-msg').html(result.msg);
					$('#login-info').fadeIn();
				}
			}
		});
	});

	$("#frm-forgot").submit(function (e) {
		e.preventDefault();
		$.ajax({
			url: 'include/Login.php?accion=forgot',
			type: 'POST',
			dataType: 'JSON',
			data: $(this).serialize(),
			error: function() {
				$('#login-msg').html(result.msg);
				$('#login-info').fadeIn();
			}, success: function (result) {
				if (result.reset) {
					$('#login-info').addClass('alert-info');
					$('#login-info').removeClass('alert-danger');
				}else {
					$('#login-info').removeClass('alert-info');
					$('#login-info').addClass('alert-danger');
				}
				$('#login-msg').html(result.msg);
				$('#login-info').fadeIn();
			}
		});
	});
});