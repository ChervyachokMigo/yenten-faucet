var Recapcha = 0;
var isBot = 1;

 $("#form_submit").attr("disabled", true);

var SubmitButtonTime = 4;

var validButton = $("#form_submit_2");

var imNotARobot = function(){
	var validButtonNumber = $("#checkRandomButton").val();
	var findBtn = ".submit-btn[value='"+validButtonNumber+"']";
	validButton = $(findBtn);

	$(".submit-btn").click(function() {
		if (this.value != validButtonNumber ){
			isBot = 3;			
		}
		$( "#form_submit_2" ).submit();
	});

	validButton.unbind();

	validButton.click(function() {
		if (isBot == 2){
			isBot = 0;
		}
		$( "#form_submit_2" ).submit();
	});

	var submit_button_xpos = Math.floor(Math.random() * Math.floor(26))*10;
	validButton.css('margin-left',submit_button_xpos+'px');

	validButton.removeClass('hidden');
	validButton.attr("disabled", true);

	Recapcha = 1;
	SubmitButtonTime = 4;

	validButton.html('Получить ('+SubmitButtonTime+')'); 

	var submitTimer = setInterval(function() {  

		SubmitButtonTime--; 
		validButton.html('Получить ('+SubmitButtonTime+')'); 

		if (SubmitButtonTime<1){
			validButton.html('Получить'); 
			isBot = 2;
			validButton.attr("disabled", false);
			clearInterval(submitTimer);
		}

	}, 1000);
}

var recaptcha_expiried = function(){
	validButton.addClass('hidden');
	Recapcha = 0;
}

$(window).load(function () {
    $("#faucet").removeClass("hidden");
    $("#logo").removeClass("hidden");
    $("#loading").addClass("hidden");
});


$(document).ready(function () {

		var walletCockie = document.cookie.replace(/(?:(?:^|.*;\s*)wallet\s*\=\s*([^;]*).*$)|^.*$/, "$1");

		$("#recaptcha").addClass('hidden');

		$("input[name=address]").on("input propertychange",function(){
			if ($(this).val().length==34){
				$("#recaptcha").removeClass('hidden');
				if (Recapcha == 1){
					validButton.removeClass('hidden');
					validButton.attr("disabled", true);
					SubmitButtonTime = 4;
					validButton.html('Получить ('+SubmitButtonTime+')'); 
					var submitTimer = setInterval(function() {  
						SubmitButtonTime--; 
						validButton.html('Получить  ('+SubmitButtonTime+')'); 
						if (SubmitButtonTime<1){
							validButton.html('Получить'); 
							validButton.attr("disabled", false);
							clearInterval(submitTimer);
						}
					}, 1000);
				}
			} else {
				$("#recaptcha").addClass('hidden');
				if (Recapcha == 1){
					validButton.addClass('hidden');
				}
			}
		});
		if($('input[name=address]').length) {
			if (walletCockie != undefined) {
				$('input[name=address]').val(walletCockie);
				if ($('input[name=address]').val().length==34){
					$("#recaptcha").removeClass('hidden');
				}
			}
		}

		//отправка данных
		$('form').submit(function (event) {
    		if ( validButton.val() !=  $("#checkRandomButton").val() ){
    			isBot = 3;
    		}

			document.cookie = "wallet="+$('input[name=address]').val()+";";

			validButton.addClass("hidden");
			validButton.attr("disabled", true);

			$("#logo").addClass("hidden");
			$("#loading").removeClass("hidden");

			$('#error').removeClass('alertaerro');
			$('#recaptcha').addClass('hidden');

			$('#faucet').addClass('hidden');

			if (isBot == 0){

				var formData = $("form").serialize();

				$.ajax({
					type: 'POST',
					url: 'faucet.php',
					data: formData,
					dataType: 'json',
					encode: true
				}).done(function (data) {
					if (data){
						$("#page_refresh").removeClass("hidden");
					}
					if (data.errors) {
						if (data.errors.human) {
							$('#error').append('<div class="alert alert-dismissible  alert-danger">' + data.errors.human + '</div>');
						}
						if (data.errors.address) {
							$('#error').append('<div class="alert alert-dismissible  alert-danger">' + data.errors.address + '</div>');
						}
						if (data.errors.balance) {
							$('#error').append('<div class="alert alert-dismissible  alert-danger">' + data.errors.balance + '</div>');
						}
						if (data.errors.transaction) {
							$('#error').append('<div class="alert alert-dismissible  alert-warning">' + data.boa + '</div>');
							data.balanceChange = Math.round( (parseFloat(data.balanceChange)*100) ) / 100; 
							$('#div_balance').html( (parseFloat($('#div_balance').html()) - data.balanceChange).toFixed(2).toString() );
							$('#div_balance').html( numberWithCommas( $('#div_balance').html() ) );
						}
					} else {
						$('#error').append('<div class="alert alert-dismissible  alert-success"><h3>' + data.boa + '</h3></div>');
						data.balanceChange = Math.round( (parseFloat(data.balanceChange)*100) ) / 100; 
						
						$('#div_balance').html( (parseFloat($('#div_balance').html().replace(/,/g,"")) - data.balanceChange).toFixed(2).toString() );
						$('#div_balance').html( numberWithCommas( $('#div_balance').html() ) );
					}
					$("#logo").removeClass("hidden");
					$("#loading").addClass("hidden");
				}).fail(function (data) {
					if (data) {
						$('#error').append('<div class="alert alert-dismissible  alert-danger">' + "Бубасик украл твои монеты." + '</div>');
						$("#page_refresh").removeClass("hidden");
					}
					$("#logo").removeClass("hidden");
					$("#loading").addClass("hidden");
				});
			} else {
				$('#error').append('<div class="alert alert-dismissible  alert-danger">'+
					'<button type="button" class="close" data-dismiss="alert">×</button>' + '<img width="358" src="a8ce324d98d62cb241c3510182172c33.gif">' +
					'</div>');

				$("#page_refresh").removeClass("hidden");
				
				$("#logo").removeClass("hidden");
				$("#loading").addClass("hidden");
			}
			$('#faucet').remove();
			$(".submit-btn").remove();
			event.preventDefault();

	});
});

function numberWithCommas(x) {
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}