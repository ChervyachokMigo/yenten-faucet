$(document).ready(function () {

		var walletCockie = document.cookie.replace(/(?:(?:^|.*;\s*)wallet\s*\=\s*([^;]*).*$)|^.*$/, "$1");

		if (walletCockie != undefined) {
			$('input[name=address]').val(walletCockie);
		}

		//отправка данных
		$('form').submit(function (event) {
    
			document.cookie = "wallet="+$('input[name=address]').val()+";";

			$("#form_submit").addClass("hidden");

			$("#logo").addClass("hidden");
			$("#loading").removeClass("hidden");

			

			$('#error').removeClass('alertaerro');
			$('#recaptcha').addClass('hidden');


			var formData = $("form").serialize();

			$.ajax({
				type: 'POST',
				url: 'faucet.php',
				data: formData,
				dataType: 'json',
				encode: true
			}).done(function (data) {
				//console.log(data.errors);
				if (data.errors) {
					if (data.errors.human) {
						$('#error').append('<div class="alert alert-dismissable alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>' + data.errors.human + '</div>');
					}
					if (data.errors.address) {
						$('#error').append('<div class="alert alert-dismissable alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>' + data.errors.address + '</div>');
					}
					if (data.errors.balance) {
						$('#error').append('<div class="alert alert-dismissable alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>' + data.errors.balance + '</div>');
					}
				} else {
					$('#error').append('<div class="alert alert-dismissable alert-success"><button type="button" class="close" data-dismiss="alert">×</button><h3>' + data.boa + '</h3></div>');
				}
				$("#page_refresh").removeClass("hidden");
				$("#logo").removeClass("hidden");
				$("#loading").addClass("hidden");
			}).fail(function (data) {
				//console.log(data);
				if (data) {
					$('#error').append('<div class="alert alert-dismissable alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>' + "Бубасик украл твои монеты.2" + '</div>');
				}
				$("#page_refresh").removeClass("hidden");
				$("#logo").removeClass("hidden");
				$("#loading").addClass("hidden");
			});

			event.preventDefault();

	});
});
