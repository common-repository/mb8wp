<?
/*
Plugin Name: MB8WP
Plugin URI: http://www.btctech.co.uk/knowledgebase/2/WP-Alt-Coin-Plugin
Description: WordPress Alt-Coin Bridge Wallet Hosting And PoS Staking Pool.
Author: Scott Laurie
Version: 1.2
Author URI: http://btctech.co.uk/
*/
if(!class_exists("Bitcoin")){
require_once('easybtc.php');
}
// WP ACTION HOOKS //
add_action('activate_mb8wp/mb8wp.php', 'mb8wp_install');
add_action('admin_menu', 'mb8wp_admenu');
add_action('deactivate_mb8wp/mb8wp.php', 'mb8wp_uninstall');
add_action( 'admin_enqueue_scripts', 'mb8wp_scripts' );
add_action( 'wp_enqueue_scripts', 'mb8wp_script' );
add_action( 'wp_ajax_mb8_admjax', 'mb8_admjax' );
add_action( 'wp_ajax_mb8_verify_rpc', 'mb8_verify_rpc' );
add_action( 'wp_ajax_mb8_save_rpc', 'mb8_save_rpc' );
add_action( 'wp_ajax_mb8_gen_address', 'mb8_gen_address' );
add_action( 'wp_ajax_mb8_check_balance', 'mb8_check_balance' );
add_action( 'wp_ajax_mb8_send', 'mb8_send' );


add_action( 'wp', 'mb8wp_page_init' );

 // SHORTCODES //
 if(mb8_licserv()>0 && mb8wp_isConfig()){
	 //new check to make sure CONFIG is saved before displaying wallet shortcode.
add_shortcode( "mb8_wallet" , "mb8_wallet" );
 }
//FUNCTIONS TO USE IN OUR APP //
function mb8_send(){
	$current_user = wp_get_current_user();
if ( 0 == $current_user->ID ) {
    // Not logged in.
 esc_html_e("You must login to use the MB8 Referral System.");
	wp_die();
} else {
    // Logged in.
	$wpUserEmail = $current_user->user_email;
	$wpUserID = $current_user->ID;
	$wallet_id = "wpuid_".$wpUserID;
	global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
    $result = $wpdb->get_results("SELECT * FROM $table WHERE id = 1");
	foreach($result as $mb8wp_config){
		$mb8wp_wallet = $mb8wp_config->master_wallet;
		$rpc_user = $mb8wp_config->rpc_user;
		$rpc_pass = $mb8wp_config->rpc_pass;
		$rpc_ip = $mb8wp_config->rpc_ip;
		$rpc_port = $mb8wp_config->rpc_port;
		$mb8wp_licence = $mb8wp_config->licence_key;
		$set_stakeshare = $mb8wp_config->set_stakeshare;
		$set_stake = $mb8wp_config->set_stake;
		$set_withdraw = $mb8wp_config->set_withdraw;
		$set_autopay = $mb8wp_config->set_autopay;
		$set_txfee = round($mb8wp_config->set_txfee,8);
		$set_coinname = $mb8wp_config->set_coinname;
		
	}
$mb8 = new Bitcoin($rpc_user,$rpc_pass,$rpc_ip,$rpc_port);

}
//get the post data and send funds...
$send_to = sanitize_text_field($_POST['send_to']);
$sm = sanitize_text_field(bcadd($_POST['send_amount'], "0", 8));
if (!is_numeric($sm)){
$sm = 0;	
}
$send_amount = $sm;
$total_transfer = bcadd($send_amount, $set_txfee, 8);
//now we send it to get a TX result..
$mb8->move($wallet_id, "", $total_transfer);
$trans_txn = $mb8->sendtoaddress($send_to, $send_amount, "", "", "", true);
$ttime = strtotime("now");
$table = $wpdb->prefix."MB8WP_TXN";

$sql = "INSERT INTO $table (txn_account, txn_type, txn_amount, txn_id, txn_time, txn_address) VALUES ('$wallet_id', 'send', '$total_transfer', '$trans_txn', '$ttime', '$send_to')";
$wpdb->query($sql);
//add a new SEND record in our TX database

esc_html_e($total_transfer." transfered.");
wp_die();
}
function mb8_check_balance(){
	$current_user = wp_get_current_user();
if ( 0 == $current_user->ID ) {
    // Not logged in.
	esc_html_e("You must login to use the MB8 Referral System.");
	wp_die();
} else {
    // Logged in.
	$wpUserEmail = $current_user->user_email;
	$wpUserID = $current_user->ID;
	$wallet_id = "wpuid_".$wpUserID;
	global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
    $result = $wpdb->get_results("SELECT * FROM $table WHERE id = 1");
	foreach($result as $mb8wp_config){
		$mb8wp_wallet = $mb8wp_config->master_wallet;
		$rpc_user = $mb8wp_config->rpc_user;
		$rpc_pass = $mb8wp_config->rpc_pass;
		$rpc_ip = $mb8wp_config->rpc_ip;
		$rpc_port = $mb8wp_config->rpc_port;
		$mb8wp_licence = $mb8wp_config->licence_key;
		$set_stakeshare = $mb8wp_config->set_stakeshare;
		$set_stake = $mb8wp_config->set_stake;
		$set_withdraw = $mb8wp_config->set_withdraw;
		$set_autopay = $mb8wp_config->set_autopay;
		$set_txfee = $mb8wp_config->set_txfee;
		$set_coinname = $mb8wp_config->set_coinname;
		
	}
$mb8 = new Bitcoin($rpc_user,$rpc_pass,$rpc_ip,$rpc_port);
esc_html_e($mb8->getbalance($wallet_id));
	
}
	wp_die();
}

function mb8_gen_address(){
	$current_user = wp_get_current_user();
if ( 0 == $current_user->ID ) {
    // Not logged in.
	esc_html_e("You must login to use the MB8 Referral System.");
	wp_die();
} else {
    // Logged in.
	$wpUserEmail = $current_user->user_email;
	$wpUserID = $current_user->ID;
	$wallet_id = "wpuid_".$wpUserID;
	//get new wallet address
	global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
    $result = $wpdb->get_results("SELECT * FROM $table WHERE id = 1");
	foreach($result as $mb8wp_config){
		$mb8wp_wallet = $mb8wp_config->master_wallet;
		$rpc_user = $mb8wp_config->rpc_user;
		$rpc_pass = $mb8wp_config->rpc_pass;
		$rpc_ip = $mb8wp_config->rpc_ip;
		$rpc_port = $mb8wp_config->rpc_port;
		$mb8wp_licence = $mb8wp_config->licence_key;
		$set_stakeshare = $mb8wp_config->set_stakeshare;
		$set_stake = $mb8wp_config->set_stake;
		$set_withdraw = $mb8wp_config->set_withdraw;
		$set_autopay = $mb8wp_config->set_autopay;
		$set_txfee = $mb8wp_config->set_txfee;
		$set_coinname = $mb8wp_config->set_coinname;
		
	}
$mb8 = new Bitcoin($rpc_user,$rpc_pass,$rpc_ip,$rpc_port);
$newaddy = $mb8->getnewaddress($wallet_id);
	
	
}
echo esc_html_e($newaddy);
		wp_die();
}
function mb8_wallet(){
$current_user = wp_get_current_user();
if ( 0 == $current_user->ID ) {
    // Not logged in.
	esc_html_e("You must login to use the MB8 Referral System.");
} else {
    // Logged in.
	$wpUserEmail = $current_user->user_email;
	$wpUserID = $current_user->ID;
	$wallet_id = "wpuid_".$wpUserID;
	global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
    $result = $wpdb->get_results("SELECT * FROM $table WHERE id = 1");
	foreach($result as $mb8wp_config){
		$mb8wp_wallet = $mb8wp_config->master_wallet;
		$rpc_user = $mb8wp_config->rpc_user;
		$rpc_pass = $mb8wp_config->rpc_pass;
		$rpc_ip = $mb8wp_config->rpc_ip;
		$rpc_port = $mb8wp_config->rpc_port;
		$mb8wp_licence = $mb8wp_config->licence_key;
		$set_stakeshare = $mb8wp_config->set_stakeshare;
		$set_stake = $mb8wp_config->set_stake;
		$set_withdraw = $mb8wp_config->set_withdraw;
		$set_autopay = $mb8wp_config->set_autopay;
		$set_txfee = round($mb8wp_config->set_txfee,8);
		$set_coinname = $mb8wp_config->set_coinname;
		$set_explorer = $mb8wp_config->set_explorer;
		
	}
$mb8 = new Bitcoin($rpc_user,$rpc_pass,$rpc_ip,$rpc_port);
	$wallet_balance = $mb8->getbalance($wallet_id);
	$wallet_address = $mb8->getaccountaddress($wallet_id);
?>
<div class="mb8_block" id="sendtxn" style="display:none">
<table width="100%" border="0" cellspacing="1" cellpadding="1">
  <tr>
    <td valign="middle"><img src="<? echo plugins_url('/', __FILE__ ); ?>images/icons/send.png" width="24"></td>
    <td valign="middle" align="center"><strong>Send Coins</strong></td>
    <td><div align="right"><a href="javascript:;" onclick="document.getElementById('sendtxn').style.display='none';"><img src="<? echo plugins_url('/', __FILE__ ); ?>images/icons/remove.png" width="24" alt="Close" title="Close"></a></div></td>
  </tr>
  <tr>
    <td colspan="3">
    <span class="mb8_sfont">Address:</span>
    <div align="center"><input type="text" size="40"  placeholder="MQ6Fn1LFJeQA8f2XqKN4pfJWQKLAm8DKPd
" id="send_address" name="send_address" /></div>
    <span class="mb8_sfont">Amount:</span>
    <div align="center"><input type="text" size="15"  placeholder="0.00000000
" id="send_amount" name="send_amount" /></div>
<span class="mb8_sfont"><strong>Note:</strong> Site Fee: <strong><? esc_html_e($set_txfee); ?></strong> + network transaction fee.</span>
<div align="right">
<input type="hidden" name="send_fee" id="send_fee" value="<? esc_html_e($set_txfee); ?>" />
<button style="margin-bottom:0; margin-top:10px;" id="mb8_send_funds">Send Funds</button></div>
    </td>
  </tr>
</table>
</div>
Wallet Balance : <strong><? esc_html_e($wallet_balance); ?></strong> <? esc_html_e($set_coinname); ?><br />
Address : <div id="myaddy" style="display:inline"><? esc_html_e($wallet_address); ?></div><br />
<button onclick="document.location.href='';">Refresh</button>&nbsp;<button onclick="document.getElementById('sendtxn').style.display='';">Send Funds</button>&nbsp;<button onclick="mb8_gen_addy();">Generate Address</button>
<? if ($set_stake>0) { ?>
<span class="mb8_sfont">
<br /><hr noshade="noshade" style="margin:0px;" />
Staking Rewards: <strong><? echo mb8wp_calcStake($wallet_id, "-24 hour"); ?></strong> (24hr) <strong><? esc_html_e(mb8wp_calcStake($wallet_id, "-7 day")); ?></strong> (7 days) <strong><? esc_html_e(mb8wp_calcStake($wallet_id, "-1 month")); ?></strong> ( month)

<hr noshade="noshade" style="margin:0px;" /></span>
<br />
<? } ?>
<h3>Latest Transactions</h3>
<?
$ttime = strtotime("-24 hour");
	global $wpdb;
    $table = $wpdb->prefix."MB8WP_TXN";
    $result = $wpdb->get_results("SELECT * FROM $table WHERE txn_account = '$wallet_id' AND txn_type <> 'move' ORDER BY ID DESC LIMIT 5");
	foreach($result as $txn_details){
		if($txn_details->txn_type == "move"){
			$type = "Staking";
			?>
            
            <?
		} else {
			$type = $txn_details->txn_type;
			?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="5%" valign="middle"><? if($txn_details->txn_type == "receive") { ?><img src="<? echo plugins_url('/', __FILE__ ); ?>images/icons/tx_input.png" width="16" height="16"><? } else { ?><img src="<? echo plugins_url('/', __FILE__ ); ?>images/icons/tx_inout.png" width="16" height="16"><? } ?></td>
    <td width="15%"><? if($txn_details->txn_type == "receive") { ?>+<? } ?><? esc_html_e($txn_details->txn_amount); ?></td>
    <td width="55%"><? esc_html_e($txn_details->txn_address); ?><br /><span class="mb8_sfont"><a target="new" href="<? echo $set_explorer; ?>tx/<? esc_html_e($txn_details->txn_id); ?>"><? esc_html_e($txn_details->txn_id); ?></a></span></td>
    <td width="20%"><? esc_html_e(date("d/m/Y H:i",$txn_details->txn_time)); ?></td>
    <td width="5%">&nbsp;</td>
  </tr>
</table>

            <?
		}
?>

<?
	}
	if ($set_stake>0){
	?>
	<hr noshade="noshade" />
<h3>Latest Staking Rewards</h3>
<?
$ttime = strtotime("-24 hour");
	global $wpdb;
    $table = $wpdb->prefix."MB8WP_STAKING";
    $result = $wpdb->get_results("SELECT * FROM $table WHERE receiver_account = '$wallet_id' ORDER BY ID DESC LIMIT 5");
	foreach($result as $txn_details){
			?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center" valign="middle" width="5%"><img src="<? echo plugins_url('/', __FILE__ ); ?>images/exchange.png" width="24"></td>
    <td>+<strong><span class="mb8_sfont"><? esc_html_e( $txn_details->txn_amount); ?></span></strong></td>
    <td width="25%"><div align="right"><span class="mb8_sfont"><? esc_html_e( date("d/m/Y H:i",$txn_details->txn_date)); ?></span></div></td>
  </tr>
</table>

            <?

?>

<?
	}
	}
}
}
function mb8_save_rpc(){

	$rpc_user = sanitize_text_field($_POST['rpc_user']);
	$rpc_pass = sanitize_text_field($_POST['rpc_pass']);
	$rpc_server = sanitize_text_field($_POST['rpc_server']);
	$rpc_port = sanitize_text_field($_POST['rpc_port']);
	$set_stake = sanitize_text_field($_POST['set_stake']);
	$set_stakeshare = sanitize_text_field($_POST['set_stakeshare']);
	$mb8_key = sanitize_text_field($_POST['mb8_key']);
	$set_withdraw = sanitize_text_field($_POST['set_withdraw']);
	$set_autopay = sanitize_text_field($_POST['set_autopay']);
	$set_txfee = sanitize_text_field($_POST['set_txfee']);
	$set_coinname = sanitize_text_field($_POST['set_coinname']);
	$set_explorer = sanitize_text_field($_POST['set_explorer']);
	
	if (!$_POST['set_stake'] || !is_numeric($_POST['set_stake'])){
	$set_stake = 0;	
	}
	//now get the actual TX fee from RPC
	// and dont allow LESS as a TX fee.
	
    global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
	//do we insert or update...
	//check if we have a config
	if (!mb8wp_isConfig()){
	$sql = "INSERT INTO $table (id, rpc_port, rpc_ip, rpc_user, rpc_pass, set_stake, set_stakeshare, licence_key, set_withdraw, set_autopay, set_txfee, set_coinname, set_explorer) VALUES (1, '$rpc_port', '$rpc_server', '$rpc_user', '$rpc_pass', $set_stake, '$set_stakeshare', '$mb8_key', '$set_withdraw', $set_autopay, '$set_txfee', '$set_coinname', '$set_explorer')";
	$wpdb->query($sql);
	//$bitcoin = new Bitcoin($rpc_user,$rpc_pass,$rpc_server,$rpc_port);
//$rep = $bitcoin->settxfee($set_txfee);

?>
Settings Saved! <a href="">Refresh</a>
<?

	} else {
		//else update it...
		if (!$set_autopay){
		$set_autopay = 1000;	
		}
		$sql = "UPDATE $table SET rpc_port = '".$rpc_port."', rpc_ip = '".$rpc_server."', rpc_user = '".$rpc_user."', rpc_pass = '".$rpc_pass."', set_stake=$set_stake, set_stakeshare = '$set_stakeshare', licence_key = '$mb8_key', set_withdraw = '$set_withdraw', set_autopay=$set_autopay, set_txfee = '$set_txfee', set_coinname = '$set_coinname', set_explorer = '$set_explorer'";
	$wpdb->query($sql);
		//$bitcoin = new Bitcoin($rpc_user,$rpc_pass,$rpc_server,$rpc_port);
//$rep = $bitcoin->settxfee($set_txfee);
?>
Settings Updated! <a href="">Refresh</a>
   <?
	}

		
	wp_die();
}
function mb8_verify_rpc(){
	$rpc_user = sanitize_text_field($_POST['rpc_user']);
	$rpc_pass = sanitize_text_field($_POST['rpc_pass']);
	$rpc_server = sanitize_text_field($_POST['rpc_server']);
	$rpc_port = sanitize_text_field($_POST['rpc_port']);
	
$bitcoin = new Bitcoin($rpc_user,$rpc_pass,$rpc_server,$rpc_port);
$wallet = $bitcoin->getaccountaddress('');
if ($wallet){
	esc_html_e("Connected to wallet: ".$wallet);
} else {
echo "0";	
}
	wp_die();	
}

function mb8wp_admenu(){
add_menu_page( 'MB8 API', 'MB8 API', 'manage_options', 'mb8-api', 'mb8wp_admin' );
}
function mb8_admjax(){
	$rpc_user = sanitize_text_field($_POST['rpc_user']);
	$rpc_pass = sanitize_text_field($_POST['rpc_pass']);
	$rpc_server = sanitize_text_field($_POST['rpc_server']);
	$rpc_port = sanitize_text_field($_POST['rpc_port']);
	
esc_html_e("Trying RPC: ".$rpc_server.":".$rpc_port);

}
function mb8wp_isConfig(){
	$cnter = 0;
		global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
    $result = $wpdb->get_results("SELECT id FROM $table");
	foreach($result as $mb8wp_config){
		$cnter = $cnter + 1;
		
	}
	if($cnter>0){
		
	return true;
	} else {
		return false;
	}
}

function mb8wp_countTXN(){
	$cnter = 0;
		global $wpdb;
    $table = $wpdb->prefix."MB8WP_TXN";
    $result = $wpdb->get_results("SELECT txn_id FROM $table");
	foreach($result as $mb8wp_config){
		$cnter = $cnter + 1;
		
	}
	return $cnter;	
}
function mb8wp_countSTAKE(){
	$cnter = 0;
		global $wpdb;
    $table = $wpdb->prefix."MB8WP_TXN";
    $result = $wpdb->get_results("SELECT txn_type, txn_amount FROM $table WHERE txn_type = 'generate' and txn_account = '_empty_'");
	foreach($result as $mb8wp_config){
		$cnter = $cnter + $mb8wp_config->txn_amount;
		
	}
	return $cnter;	
}
function mb8wp_checkTXN($txn){
	$cnter = 0;
		global $wpdb;
    $table = $wpdb->prefix."MB8WP_TXN";
    $result = $wpdb->get_results("SELECT * FROM $table WHERE txn_id = '".$txn."'");
	foreach($result as $mb8wp_config){
		$cnter = $cnter + 1;
		
	}
	return $cnter;	
}
function mb8wp_getConfig($cnfg){
		global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
    $result = $wpdb->get_results("SELECT $cnfg, id FROM $table WHERE id = 1");
	foreach($result as $mb8wp_config){
		return $mb8wp_config->$cnfg;
		
	}
}
function mb8wp_admin(){
	global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
    $result = $wpdb->get_results("SELECT * FROM $table WHERE id = 1");
	foreach($result as $mb8wp_config){
		$mb8wp_wallet = $mb8wp_config->master_wallet;
		$rpc_user = $mb8wp_config->rpc_user;
		$rpc_pass = $mb8wp_config->rpc_pass;
		$rpc_ip = $mb8wp_config->rpc_ip;
		$rpc_port = $mb8wp_config->rpc_port;
		$mb8wp_licence = $mb8wp_config->licence_key;
		
	}
	if(isset($rpc_user) && isset($rpc_pass) && isset($rpc_ip) && isset($rpc_port)){
		$rpc_connected = true;
	} else {
	$rpc_connected = false;
	$errmsg = "RPC configuration not set.";
	}
?>
<style type="text/css">
.fnt_red {
	color: #F00;
}
.mb_slim {
	margin-top: 0px;
	margin-bottom: 0px;
}
</style>

<h2>MB8 API</h2>
<?
if (!$rpc_connected){
	set:
	if ($errmsg) {
?>
<strong>Error:</strong> <? esc_html_e($errmsg); ?>
<?
}
newset:
?>
<hr noshade="noshade" />
<div align="left">
  <h3 class="mb_slim">* First Time Setup</h3>
  <span class="mb_slim"><br>
  Please enter your RPC connection details.
</span></div>
<table width="100%" border="0" cellspacing="1" cellpadding="1">
  <tr>
    <td align="right">RPC IP/Host : </td>
    <td><input type="text" size="25" id="mb8_adm_rpc_server" /></td>
  </tr>
  <tr>
    <td align="right">RPC Port : </td>
    <td><input type="text" size="10" id="mb8_adm_rpc_port" /></td>
  </tr>
  <tr>
    <td align="right">RPC User : </td>
    <td><input type="text" size="25" id="mb8_adm_rpc_user" /></td>
  </tr>
  <tr>
    <td align="right">RPC Password : </td>
    <td><input type="password" size="25" id="mb8_adm_rpc_pass" /></td>
  </tr>
    <tr>
    <td colspan="2" >Enter a licence key to unlock all features.</td>
  </tr>
  <? 
  $islicenced = false;
  if ($islicenced) { ?>
    <tr>
    <td align="right">Licence Key : </td>
    <td><input type="text" size="25" value="" id="mb8_key" /></td>
  </tr>
  <? } ?>
     <tr>
    <td colspan="2" align="right">&nbsp;</td>
  </tr>
     <tr>
    <td colspan="2"><button id="mb8_adm_set">Save Settings</button>&nbsp;<div id="mb8_jax_response" style="display:inline"></div></td>
  </tr>
</table>

<?
} else {
	//get RPC status...
$bitcoin = new Bitcoin($rpc_user,$rpc_pass,$rpc_ip,$rpc_port);
$wallet = $bitcoin->getaddressesbyaccount('');
	$raw = $bitcoin->raw_response;
	if (!$raw){
		$errmsg = "No response from RPC.";
		goto set;
	}
	$wallet = json_decode($raw, TRUE);
	foreach ($wallet as $arr => $arr_data){
		if ($arr == "result"){
		$wc = count($arr_data);
		$mb8wp_wallet = "";
		for ($x = 0; $x < $wc; $x++) {
  //echo "<li>$arr_data[$x]</li>";
  	if ($mb8wp_wallet){
		$mb8wp_wallet = $mb8wp_wallet.",".$arr_data[$x];
	} else {
		$mb8wp_wallet = $arr_data[$x];
	}
}
	
		}
	}

if ($raw){
	$status = "Connected";
	//update wallet in DB
	    global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";

	$sql = "UPDATE $table SET master_wallet = '$mb8wp_wallet' WHERE ID=1";

	$wpdb->query($sql);
	
} else {
	$status = "Disconnected";
	$errmsg = "No response from RPC.";
	goto set;
}
?>
<div id="tabs">
  <ul>
    <li><a href="#tabs-1">RPC Status</a></li>
    <li><a href="#tabs-2">List Accounts</a></li>
    <li><a href="#tabs-3">Settings</a></li>
    <li><a href="#tabs-4">Statistics</a></li>
  </ul>
  <div id="tabs-4">
    <h2>Statistics</h2>

    <?
		global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
    $result = $wpdb->get_results("SELECT * FROM $table WHERE id = 1");
	foreach($result as $mb8wp_config){
		$mb8wp_wallet = $mb8wp_config->master_wallet;
		$rpc_user = $mb8wp_config->rpc_user;
		$rpc_pass = $mb8wp_config->rpc_pass;
		$rpc_ip = $mb8wp_config->rpc_ip;
		$rpc_port = $mb8wp_config->rpc_port;
		$mb8wp_licence = $mb8wp_config->licence_key;
		$set_stakeshare = $mb8wp_config->set_stakeshare;
		$set_stake = $mb8wp_config->set_stake;
		$set_withdraw = $mb8wp_config->set_withdraw;
		$set_autopay = $mb8wp_config->set_autopay;
		$set_txfee = $mb8wp_config->set_txfee;
		$set_coinname = $mb8wp_config->set_coinname;
		$set_explorer = $mb8wp_config->set_explorer;
		
	}
$mb8 = new Bitcoin($rpc_user,$rpc_pass,$rpc_ip,$rpc_port);
	$txns = $mb8->listunspent();
$tx_count = 0;
$acc_balance = 0;
$raw = $mb8->raw_response;
$array = json_decode($raw, TRUE);
foreach($array as $tx => $tx_val){
if ($tx == "result"){
	//loop through result array
	$txn = $tx_val;
}

}
//now loop the eresult

foreach($txn as $tx => $tx_val){
	$tx_count = $tx_count + 1;
	
	//echo "- $tx : $tx_val<br>";
	$spendable = false;
	foreach($tx_val as $txn => $txn_val){
		//echo "- $txn : $txn_val<br>";
		if ($txn == "amount"){
			$amount = $txn_val;
		}
		if ($txn == "spendable" && $txn_val == 1){
			$spendable = true;
		}
	}
	if ($spendable){
	$acc_balance = $acc_balance + $amount;
	}
}

	?>
 Unspent TXNS: <? esc_html_e($tx_count); ?> [<? esc_html_e($acc_balance); ?> MB8]
    </div>  
  <div id="tabs-1">
    <h2>RPC Status</h2>
RPC Connection Status: <strong><? esc_html_e($status); ?></strong>&nbsp;[<? esc_html_e( mb8wp_getConfig('rpc_ip')); ?>:<? esc_html_e(mb8wp_getConfig('rpc_port')); ?>] <div id="j_time"><? esc_html_e(date("d-m-Y H:i:s", strtotime("now"))); ?></div><br />
Master Wallet Addresses: 
<? 
$m_wallet = mb8wp_getConfig('master_wallet'); 
if (strpos($m_wallet, ',') !== false) {
//do a drop list
$m_wallet = explode(",", $m_wallet);
?>
<select>
<?
foreach($m_wallet as $addy){
?>
<option><? esc_html_e($addy); ?></option>
<?
}
?>
</select>
<?
} else {
	esc_html_e($m_wallet);
}

?><hr />
<?
if ('Connected' === $status){ //if 001
//get BTC connection

$bitcoin->getinfo();
$raw = $bitcoin->raw_response;

$array = json_decode($raw);

foreach ($array as $key => $jsons) { // This will search in the 2 jsons
//echo $key;
//echo $array.$key.$jsons;
if (is_array($jsons) || is_object($jsons)) {
     foreach($jsons as $key => $value) {
		 if ('errors' === $key && !$value) {
			 
		 } else {
			 if ($key == "balance"){
				    global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";

	$sql = "UPDATE $table SET node_balance = '$value' WHERE ID=1";

	$wpdb->query($sql);	 
			 }

			 ?>
             <div align="right" style="display:inline;"><? esc_html_e($key); ?></div> : <div align="left" style="display:inline;"><? esc_html_e($value); ?></div><br />
             <?
       
		 }
    }
}

}
//mb8wp_syncBlockChain();
?>
  </div>
  <div id="tabs-2">
    <h2>List Accounts</h2>
<?
$allowed_html = [
    'font'      => [
        'color'  => [],
        'size' => [],
    ],
    'br'     => [],
    'b'     => [],
    'strong' => [],
];

$accounts = $bitcoin->listaccounts();
$cnter = 0;
$sub_balance = 0;
foreach ($accounts as $key => $value){
	
	if (!$key){
	$key = "<font color=#FF0000>Master Account</font>";
	//echo "<b>".$key."</b> - ".$value." </br>";	
	//$master_balance = intval($value);
	$master_balance = bcadd($value, "0", 8);
	} else {
		if($key != "#"){
		$cnter = $cnter + 1;
		}
		//echo "<b>".$key."</b> - ".$value."</br>";
		$sub_balance = bcadd($sub_balance, $value, 8);
	}
	
}
$table = $wpdb->prefix."MB8WP_CONFIG";
$sql = "UPDATE $table SET sub_wallets=$cnter, sub_balance = '$sub_balance' WHERE ID=1";
$wpdb->query($sql);
foreach ($accounts as $key => $value){
	
	if (!$key){
	$key = "<font color=#FF0000>Master Account</font>";
	echo wp_kses("<b>".$key."</b> - ".bcadd($value, "0", 8)."</br>", $allowed_html);
	} else {
		if ($key == "#"){
	$key = "<font color=green>Holding Account</font>";
	echo wp_kses("<b>".$key."</b> - ".bcadd($value, "0", 8)."</br>", $allowed_html);			
		} else {
	$cnter = $cnter + 1;
	global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
    $result = $wpdb->get_results("SELECT sub_balance FROM $table WHERE id = 1");
	foreach($result as $mb8wp_config){
		$accum = $mb8wp_config->sub_balance;
	}
	$perck = $value / $accum * 100;
	//get the coins they should receive
	if ($set_stakeshare<100){
		$master_balance = $master_balance / 100 * $set_stakeshare;
	}
	$reward = $master_balance / 100 * $perck;

		echo wp_kses( "<b>".$key."</b> - ".$value." ($perck%)</br>", $allowed_html);
		if ($reward>0){
			//$mb8->move("", $key, $reward); 
		}
		}
	}
	
}	 
?>
  </div>
  <div id="tabs-3">
    <h2>Settings</h2>
<table width="100%" border="0" cellspacing="1" cellpadding="1">
  <tr>
    <td align="right">RPC IP/Host : </td>
    <td><input type="text" size="25" id="mb8_adm_rpc_server" value="<? esc_html_e($rpc_ip); ?>" /></td>
  </tr>
  <tr>
    <td align="right">RPC Port : </td>
    <td><input type="text" size="10" id="mb8_adm_rpc_port" value="<? esc_html_e($rpc_port); ?>" /></td>
  </tr>
  <tr>
    <td align="right">RPC User : </td>
    <td><input type="text" size="25" id="mb8_adm_rpc_user" value="<? esc_html_e($rpc_user); ?>" /></td>
  </tr>
  <tr>
    <td align="right">RPC Password : </td>
    <td><input type="password" size="25" id="mb8_adm_rpc_pass" value="" /></td>
  </tr>
  <? 
  $islicenced = false;
  if ($islicenced) { ?>
    <tr>
    <td colspan="2" >Enter a licence key to unlock all features.</td>
  </tr>
    <tr>
    <td align="right">Licence Key : </td>
    <td><input type="text" size="25" value="<? echo $mb8wp_licence; ?>" id="mb8_key" name="mb8_key" /></td>
  </tr>
  <? } ?>
     <tr>
    <td colspan="2" align="left">Settings :</td>
  </tr>
      <tr>
    <td align="right">Staking Node : </td>
    <td><input type="checkbox" name="set_stake" id="set_stake" <? if ($set_stake==1) { ?>checked<? } ?> onclick="adm_checkstake();" value="<? esc_html_e($set_stake); ?>" />
      <font size=1>Is this a node that stakes it's coins?</font></td>
  </tr>
       <tr>
    <td align="right" valign="top">Staking Share : </td>
    <td><input type="text" name="set_stakeshare" id="set_stakeshare" size="5" value="<? esc_html_e($set_stakeshare); ?>" />
      %<br />      <font size=1>How much % would you like to share with users. Zero (0) will disable.</font></td>
  </tr>
        <tr>
    <td align="right" valign="top">Withraw Wallet : </td>
    <td><input type="text" name="set_withdraw" id="set_withdraw" value="<? esc_html_e($set_withdraw); ?>" size="25" />
      <br />
      <font size="1">If stake share &lt; 100% you can withdraw the diffirence to a wallet.</font></td>
  </tr>
         <tr>
    <td align="right" valign="top">Auto Payout : </td>
    <td><input type="text" name="set_autopay" id="set_autopay" value="<? esc_html_e($set_autopay); ?>" size="10" />
      <br />
      <font size="1">The minimum in coins 'Holding Account' should have before auto withdraw.</font></td>
  </tr>
   <tr>
    <td align="right">Withdrawal Fee : </td>
    <td><input type="text" size="10" id="set_txfee" value="<? esc_html_e($set_txfee); ?>" />
      <font size="1">Per Withdrawal. 0 to disable.</font></td>
  </tr>
   <tr>
    <td align="right">Coin Name : </td>
    <td><input type="text" size="10" id="set_coinname" value="<? esc_html_e($set_coinname); ?>" />
      <font size="1">Abbreviation i.e BTC, LTC...</font></td>
  </tr>
    <tr>
    <td align="right">Explorer URL : </td>
    <td><input type="text" size="30" id="set_explorer" value="<? esc_html_e($set_explorer); ?>" />
      <font size="1">i.e http://yoursite.com:3001/ (inquidis etc)</font></td>
  </tr>
      <tr>
    <td colspan="2" align="right">&nbsp;</td>
  </tr>
     <tr>
    <td colspan="2"><button id="mb8_adm_set">Save Settings</button>&nbsp;<div id="mb8_jax_response" style="display:inline"></div></td>
  </tr>
</table>

  </div>
</div>

<?
}
//end if 001
}
}
function mb8wp_page_init()
{
    if(is_page('mb8-wp')){   
        $dir = plugin_dir_path( __FILE__ );
        include($dir."mb8wp_page.php");
		//echo "Welcome to the API...";
        die();
    }
}
function mb8wp_script($hook){
	
//my own custom scripts
wp_register_style('mb8-jquery-cs-style', plugins_url('/mb8.css', __FILE__ ), false, null);
wp_enqueue_style('mb8-jquery-cs-style');

wp_register_script('mb8-custom-jsfp', plugins_url('/mb8-custom-fp.js', __FILE__ ), array(), '', true);
wp_enqueue_script('mb8-custom-jsfp');
wp_localize_script( 'mb8-custom-jsfp', 'the_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );		
}
function mb8wp_scripts($hook){
if ($hook === 'toplevel_page_mb8-api'){
wp_register_style('mb8-jquery-ui-style', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', false, null);
wp_enqueue_style('mb8-jquery-ui-style');

wp_register_style('mb8-jquery-cs-style', plugins_url('/mb8.css', __FILE__ ), false, null);
wp_enqueue_style('mb8-jquery-cs-style');

wp_enqueue_script('jquery');

wp_enqueue_script('jquery-ui-tabs');
//my own custom scripts
wp_register_script('mb8-custom-js', plugins_url('/mb8-custom.js', __FILE__ ), array(), '', true);
wp_enqueue_script('mb8-custom-js');	
}


}
function mb8wp_install(){
    global $wpdb;
	$table = $wpdb->prefix."MB8WP_STAKING";
    $structure = "CREATE TABLE $table (
        id INT(9) NOT NULL AUTO_INCREMENT,
        receiver_account VARCHAR(200), UNIQUE KEY id (id), txn_amount VARCHAR(200), txn_date VARCHAR(200)
    );";
    $wpdb->query($structure);
	
    $table = $wpdb->prefix."MB8WP_CONFIG";
    $structure = "CREATE TABLE $table (
        id INT(9) NOT NULL AUTO_INCREMENT,
        master_wallet LONGTEXT,
        licence_key VARCHAR(100) DEFAULT 'free-licence',
        sub_wallets INT(9) DEFAULT 0,
	UNIQUE KEY id (id), rpc_port VARCHAR(10), rpc_ip VARCHAR(200), rpc_user VARCHAR(200), rpc_pass VARCHAR(200), node_balance VARCHAR(100), node_staking VARCHAR(100), set_stake INT(1) DEFAULT 0, set_stakeshare VARCHAR(100) DEFAULT 100, sub_balance VARCHAR(100) DEFAULT 0, local_licence LONGTEXT, cron_time VARCHAR(100), set_withdraw VARCHAR(100), set_autopay INT(10) DEFAULT 0, set_txfee VARCHAR(100) DEFAULT '0.001', set_coinname VARCHAR(50) DEFAULT 'MB8', set_explorer VARCHAR(200)
    );";
    $wpdb->query($structure);
	
	$ttime = strtotime("now");
	
	$structure = "INSERT INTO $table (master_wallet, set_stake, cron_time, set_stakeshare, set_coinname) VALUES ('', 0, '$ttime', '100', 'ALTC')";
	$wpdb->query($structure);
	
    $table = $wpdb->prefix."MB8WP_TXN";
    $structure = "CREATE TABLE $table (
        id INT(9) NOT NULL AUTO_INCREMENT,
        txn_account VARCHAR(200), UNIQUE KEY id (id), txn_type VARCHAR(80), txn_amount VARCHAR(20), txn_confirmations INT(25), txn_id VARCHAR(100), txn_time VARCHAR(25), txn_generated INT(9), txn_address VARCHAR(100), sub_balance VARCHAR(100), txn_blockhash VARCHAR(200)
    );";
    $wpdb->query($structure);


//create our page
    $the_page_title = 'mb8-wp';
    $the_page_name = 'mb8-wp';

    // the menu entry...
    delete_option("mb8wp_mb8-wp_title");
    add_option("mb8wp_mb8-wp_title", $the_page_title, '', 'yes');
    // the slug...
    delete_option("mb8wp_mb8-wp_name");
    add_option("mb8wp_mb8-wp_name", $the_page_name, '', 'yes');
    // the id...
    delete_option("mb8wp_mb8-wp_id");
    add_option("mb8wp_mb8-wp_id", '0', '', 'yes');

    $the_page = get_page_by_title( $the_page_title );

    if ( ! $the_page ) {

        // Create post object
        $_p = array();
        $_p['post_title'] = $the_page_title;
        $_p['post_content'] = "This text may be overridden by the plugin. You shouldn't edit it.";
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1); // the default 'Uncatrgorised'

        // Insert the post into the database
        $the_page_id = wp_insert_post( $_p );

    }
    else {
        // the plugin may have been previously active and the page may just be trashed...

        $the_page_id = $the_page->ID;

        //make sure the page is not trashed...
        $the_page->post_status = 'publish';
        $the_page_id = wp_update_post( $the_page );

    }
	delete_option( 'mb8wp_mb8-wp_id' );
    add_option( 'mb8wp_mb8-wp_id', $the_page_id );
}
function mb8wp_syncBlockChain(){
	echo "<li>## Running CRON jobs...</li>";
	global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
    $result = $wpdb->get_results("SELECT * FROM $table WHERE id = 1");
	foreach($result as $mb8wp_config){
		$mb8wp_wallet = $mb8wp_config->master_wallet;
		$rpc_user = $mb8wp_config->rpc_user;
		$rpc_pass = $mb8wp_config->rpc_pass;
		$rpc_server = $mb8wp_config->rpc_ip;
		$rpc_port = $mb8wp_config->rpc_port;
		$mb8wp_licence = $mb8wp_config->licence_key;
		
	}
$bitcoin = new Bitcoin($rpc_user,$rpc_pass,$rpc_server,$rpc_port);

$bitcoin->listaccounts();
$raw = $bitcoin->raw_response;
$array = json_decode($raw);
$iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
foreach($iterator as $key => $value) {
	if ($key != "id" && $key != "error") {
		if ("_empty_" == $key){
			$key = "master_account";
		}
	echo "<li>".$key."</li>";
	//this is where we have data
	
	
	if ("master_account" == $key){
	$key = "";	
	}


if ($key == ""){
	$key = "_empty_";
}
$txn_account = $key;
mb8wp_getTXN($key);


echo "##########<br>";
//end of for each USER....
	echo "<hr>";
	}
	
}
//end if function	
}
function mb8wp_getTXN($key){
	$txn_account = $key;
	if ("_empty_" == $key){
		$key = "";
	}
	echo "-- getting ".$key." --";
		global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
    $result = $wpdb->get_results("SELECT * FROM $table WHERE id = 1");
	foreach($result as $mb8wp_config){
		$mb8wp_wallet = $mb8wp_config->master_wallet;
		$rpc_user = $mb8wp_config->rpc_user;
		$rpc_pass = $mb8wp_config->rpc_pass;
		$rpc_server = $mb8wp_config->rpc_ip;
		$rpc_port = $mb8wp_config->rpc_port;
		$mb8wp_licence = $mb8wp_config->licence_key;
		
	}
$bitcoins = new Bitcoin($rpc_user,$rpc_pass,$rpc_server,$rpc_port);
$bitcoins->listtransactions($key, 10000000);
	
$raw = $bitcoins->raw_response;
$array = json_decode($raw);
$iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
echo "##########<br>";

foreach($iterator as $key => $value) {

		if ($key != "id" && $key != "error") {
//loop through $key to get all data
if ("bip125-replaceable" == $key){
	if (!isset($txn_generated)){
		
		$txn_generated = 0;
	}
	global $wpdb;
	$table = $wpdb->prefix."MB8WP_TXN";
	$istxn = false;
	echo "<li>-- end of txn (".$txn_id.") -- </li>";
    $result = $wpdb->get_results("SELECT * FROM $table WHERE txn_id = '$txn_id' AND txn_account = '$txn_account' AND txn_type = '$txn_type' AND txn_amount = '$txn_amount' AND txn_time = '$txn_time'");
	foreach($result as $mb8wp_config){
		$istxn = true;
	}
	if ($istxn){
	//$sql = "UPDATE $table SET txn_confirmations=".$txn_confirmations." WHERE txn_id = '".$txn_id."'";
	} else {
	//$sql = "INSERT INTO $table (txn_account, txn_type, txn_amount, txn_confirmations, txn_id, txn_time, txn_generated, txn_address) VALUES ('".$txn_account."', '".$txn_type."', '".$txn_amount."', ".$txn_confirmations.", '".$txn_id."', '".$txn_time."', ".$txn_generated.", '".$txn_address."')";
	}
	
	//$wpdb->query($sql);
} else {
//get key daya#
	if ($key == "address"):
		$txn_address = $value;
	elseif($key == "category"):
		$txn_type = $value;
	elseif($key == "amount"):
		$txn_amount = $value;
	elseif($key == "confirmations"):
		$txn_confirmations = $value;
	elseif($key == "txid"):
	$txn_id = $value;
		
	elseif($key == "time"):
	$txn_time = $value;
		
	elseif($key == "generated"):
		$txn_generated = $value;

	endif;
	
echo "<li>".$key." : ".$value."</li>";
}
		}


}

}
function mb8wp_uninstall(){
	 global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
	$structure = "DROP TABLE $table";
	$wpdb->query($structure);
	
	$table = $wpdb->prefix."MB8WP_TXN";
	$structure = "DROP TABLE $table";
	$wpdb->query($structure);	
	
	$table = $wpdb->prefix."MB8WP_STAKING";
	$structure = "DROP TABLE $table";
	$wpdb->query($structure);	
	
    $the_page_title = get_option( "mb8wp_mb8-wp_title" );
    $the_page_name = get_option( "mb8wp_mb8-wp_name" );

    //  the id of our page...
    $the_page_id = get_option( 'mb8wp_mb8-wp_id' );
    if( $the_page_id ) {

        wp_delete_post( $the_page_id ); // this will trash, not delete

    }

    delete_option("mb8wp_mb8-wp_title");
    delete_option("mb8wp_mb8-wp_name");
    delete_option("mb8wp_mb8-wp_id");
	
}
function mb8_licserv(){
	$ww_app_act = 1;
return $ww_app_act;	
}

function mb8wp_formatE($dat){
	if(strpos($dat, 'E-') !== false) {
	$val = preg_split("/\-/", $dat);
	$cnt = $val[1] -1;
	$data = "0.";
while($cnt>0){
	$data = $data."0";
	$cnt = $cnt - 1;
}
	$cur = str_replace(".", "", $val[0]);
	$cur = str_replace("E", "", $cur);
	$data = $data.$cur;
	return $data;
	} else {
	return $dat;	
	}
}
function mb8wp_calcStake($user, $period){
$ttime = strtotime($period);
	global $wpdb;
    $table = $wpdb->prefix."MB8WP_STAKING";
    $result = $wpdb->get_results("SELECT * FROM $table WHERE receiver_account = '$user' AND txn_date>$ttime");
	$cnt = 0;
	foreach($result as $txn_details){
		$cnt = bcadd($cnt, $txn_details->txn_amount, 8);
	}
	return $cnt;
}
?>