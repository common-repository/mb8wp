function adm_checkstake(){
	var val = jQuery("#set_stake").val();
	if (val<1){
		jQuery("#set_stake").val('1');
	} else {
		jQuery("#set_stake").val('0');
	}
}
//setTimeout(function() { adm_refresh() }, 5000);
function adm_refresh(){
	jQuery("#j_time").html('test');
}
  jQuery( function() {
    $( "#tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
    $( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
  } );
  
jQuery("#mb8_adm_config").click(function(){
  $( "#mb8cfg" ).slideToggle( "slow", function() {
    // Animation complete.
  });
});

jQuery("#mb8_adm_set").click(function(){
	//verify we have all details
	var rpc_user = $("#mb8_adm_rpc_user").val();
	var rpc_pass = $("#mb8_adm_rpc_pass").val();
	var rpc_server = $("#mb8_adm_rpc_server").val();
	var rpc_port = $("#mb8_adm_rpc_port").val();
	var set_stake = $("#set_stake").val();
	var set_stakeshare = $("#set_stakeshare").val();
	var mb8_key = $("#mb8_key").val();
	var set_withdraw = $("#set_withdraw").val();
	var set_autopay = $("#set_autopay").val();
	var set_txfee = $("#set_txfee").val();
	var set_coinname = $("#set_coinname").val();
	var set_explorer = $("#set_explorer").val();
	
	if (!rpc_user){
		alert('Please enter all RPC connection details.');
		return false;
	}
	if (!rpc_pass){
		alert('Please enter all RPC connection details.');
		return false;
	}
	if (!rpc_server){
		alert('Please enter all RPC connection details.');
		return false;
	}
	if (!rpc_port){
		alert('Please enter all RPC connection details.');
		return false;
	}
	
	$("#mb8_jax_response").html('Connecting to RPC, please wait...');
	//now do a test
	jQuery(document).ready(function($) {

		var data = {
			'action': 'mb8_admjax',
			'rpc_user': rpc_user,
			'rpc_pass': rpc_pass,
			'rpc_server': rpc_server,
			'rpc_port': rpc_port,
			'mb8_key': mb8_key,
			'set_withdraw': set_withdraw,
			'set_autopay': set_autopay,
			'set_txfee': set_txfee,
			'set_coinname': set_coinname,
			'set_explorer': set_explorer
			
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			//alert('Got this from the server: ' + response);
			//$("#mb8_jax_response").html(response);
			var mb8_errcode = response;
			if (mb8_errcode<1){
			$("#mb8_jax_response").html('Error connecting to RPC.');
			return false	
			}
			$("#mb8_jax_response").html(response);
			//do a new JAX request to see if its working...
					var data = {
			'action': 'mb8_verify_rpc',
			'rpc_user': rpc_user,
			'rpc_pass': rpc_pass,
			'rpc_server': rpc_server,
			'rpc_port': rpc_port,
			'mb8_key': mb8_key,
			'set_withdraw': set_withdraw,
			'set_autopay': set_autopay,
			'set_txfee': set_txfee,
			'set_coinname': set_coinname,
			'set_explorer': set_explorer
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			//alert('Got this from the server: ' + response);
			//$("#mb8_jax_response").html(response);
			var mb8_errcode = response;
			if (mb8_errcode<1){
			$("#mb8_jax_response").html('Error connecting to RPC.');
			return false	
			}
			$("#mb8_jax_response").html(response);
			//this worked lets AJAX the data to DB...
			var data = {
			'action': 'mb8_save_rpc',
			'rpc_user': rpc_user,
			'rpc_pass': rpc_pass,
			'rpc_server': rpc_server,
			'rpc_port': rpc_port,
			'set_stake': set_stake,
			'set_stakeshare': set_stakeshare,
			'mb8_key': mb8_key,
			'set_withdraw': set_withdraw,
			'set_autopay': set_autopay,
			'set_txfee': set_txfee,
			'set_coinname': set_coinname,
			'set_explorer': set_explorer
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			//alert('Got this from the server: ' + response);
			//$("#mb8_jax_response").html(response);
			var mb8_errcode = response;
			if (mb8_errcode<1){
			$("#mb8_jax_response").html('Error writing to DB.');
			return false	
			}
			$("#mb8_jax_response").html(response);
			
			//END OF FEATURE
			
		});
		});
		});
	});
});
