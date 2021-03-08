var Recapcha = 0;

 $("#form_submit").attr("disabled", true);

var SubmitButtonTime = 4;

var imNotARobot = function(){
	$("#form_submit").removeClass('hidden');
	Recapcha = 1;
	 $("#form_submit").attr("disabled", true);
	SubmitButtonTime = 4;
	$("#form_submit").html('Получить ('+SubmitButtonTime+')'); 
	var submitTimer = setInterval(function() {  
		SubmitButtonTime--; 
		$("#form_submit").html('Получить ('+SubmitButtonTime+')'); 
		if (SubmitButtonTime<1){
			$("#form_submit").html('Получить'); 
			$("#form_submit").attr("disabled", false);
			clearInterval(submitTimer);
		}
	}, 1000);
}

var recaptcha_expiried = function(){
	$("#form_submit").addClass('hidden');
	Recapcha = 0;
}

$(window).load(function () {
    $("#faucet").removeClass("hidden");
    $("#logo").removeClass("hidden");
    $("#loading").addClass("hidden");
});

$(document).ready(function () {

		var walletCockie = document.cookie.replace(/(?:(?:^|.*;\s*)wallet\s*\=\s*([^;]*).*$)|^.*$/, "$1");

		var submit_button_xpos = Math.floor(Math.random() * Math.floor(26))*10;

		$("#form_submit").css('margin-left',submit_button_xpos+'px');

		$("#form_submit").addClass('hidden');

		$("#recaptcha").addClass('hidden');

		$("input[name=address]").on("input propertychange",function(){
			if ($(this).val().length==34){
				$("#recaptcha").removeClass('hidden');
				if (Recapcha == 1){
					$("#form_submit").removeClass('hidden');
					$("#form_submit").attr("disabled", true);
					SubmitButtonTime = 4;
					$("#form_submit").html('Получить ('+SubmitButtonTime+')'); 
					var submitTimer = setInterval(function() {  
						SubmitButtonTime--; 
						$("#form_submit").html('Получить  ('+SubmitButtonTime+')'); 
						if (SubmitButtonTime<1){
							$("#form_submit").html('Получить'); 
							$("#form_submit").attr("disabled", false);
							clearInterval(submitTimer);
						}
					}, 1000);
				}
			} else {
				$("#recaptcha").addClass('hidden');
				if (Recapcha == 1){
					$("#form_submit").addClass('hidden');
				}
			}
		});

		if (walletCockie != undefined) {
			$('input[name=address]').val(walletCockie);
			if ($('input[name=address]').val().length==34){
				$("#recaptcha").removeClass('hidden');
			}
		}

		

		//отправка данных
		$('form').submit(function (event) {
    
			document.cookie = "wallet="+$('input[name=address]').val()+";";

			$("#form_submit").addClass("hidden");
			$("#form_submit").attr("disabled", true);

			$("#logo").addClass("hidden");
			$("#loading").removeClass("hidden");

			$('#error').removeClass('alertaerro');
			$('#recaptcha').addClass('hidden');

			$('#faucet').addClass('hidden');

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
					$('#error').append('<div class="alert alert-dismissable alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>' + "Бубасик украл твои монеты." + '</div>');
				}
				$("#page_refresh").removeClass("hidden");
				$("#logo").removeClass("hidden");
				$("#loading").addClass("hidden");
			});

			event.preventDefault();

	});
});
