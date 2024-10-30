function mb8_gen_addy(){
	jQuery(document).ready(function($) {

		var data = {
			'action': 'mb8_gen_address'
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(the_ajax_script.ajaxurl, data, function(response) {
			//alert(response);
			$("#myaddy").html(response);
		});
		
	});
}
jQuery("#mb8_send_funds").click(function(){
	jQuery(document).ready(function($) {
	var send_address = jQuery("#send_address").val();
	var send_amount = round(jQuery("#send_amount").val(),8);
	var send_fee = round(jQuery("#send_fee").val(),8);
	var tot_balance = round(send_amount + send_fee,8);
	
	if (!send_address){
		alert('You must enter a recipient address.');
		return false;
	}
	if (!send_amount || send_amount<0){
		alert('Amount must be a number and greater than zero (0)');
		return false;
	}
		

	var conf = confirm('Sending '+send_amount+' MB8 to : '+send_address+'\nSite Fees: '+send_fee+' MB8 + network fees.\nTotal: '+tot_balance+' MB8');
	
	if (conf == true){
		var data = {
			'action': 'mb8_check_balance'
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(the_ajax_script.ajaxurl, data, function(response) {
			
			var user_balance = response;
	if(user_balance<tot_balance){
		alert('Sorry your balance : '+user_balance+' is not enough for this transaction.');
		return false;
	}
	//now we can send the funds...
			var data = {
			'action': 'mb8_send',
			'send_to': send_address,
			'send_amount': send_amount
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(the_ajax_script.ajaxurl, data, function(response) {

			alert(response);
	
		});
		});
		
		
	} else {
		document.getElementById('sendtxn').style.display='none';
	}
});
	});
function round(value, decimals) {
  return Number(Math.round(value+'e'+decimals)+'e-'+decimals);
}