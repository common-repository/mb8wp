<?
echo "Welcome to MB8 API<br>";
$wac = true;
if ($wac && mb8_licserv()>0){
	// # GRAB APP CONFIG DATA /
	global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
    $result = $wpdb->get_results("SELECT * FROM $table WHERE id = 1");
	foreach($result as $mb8wp_config){
		$rpc_user = $mb8wp_config->rpc_user;
		$rpc_pass = $mb8wp_config->rpc_pass;
		$rpc_ip = $mb8wp_config->rpc_ip;
		$rpc_port = $mb8wp_config->rpc_port;
		$mb8_licence = $mb8wp_config->licence_key;
		$mb8_licence_local = $mb8wp_config->local_licence;
		$set_stakeshare = $mb8wp_config->set_stakeshare;
		$set_stake = intval($mb8wp_config->set_stake);
		$cron_time = $mb8wp_config->cron_time + 320;
		$set_withdraw = $mb8wp_config->set_withdraw;
		$set_autopay = $mb8wp_config->set_autopay;
		$set_txfee = round($mb8wp_config->set_txfee,8);
	}

//$mb8coin = new Bitcoin($rpc_user,$rpc_pass,$rpc_ip,$rpc_port);
if ($cron_time>strtotime("now")){
	echo "<br>Cron will not run early...";
	//goto cronend;
}
$cron_start = strtotime("now");
?>
--- Running Cron Jobs  (<? echo date("d-m-Y H:i:s",$cron_start); ?>) ---
<hr />
<?
// # STAKING PAYOUTS
if ($set_stake==1 && $set_stakeshare>0){
$mb8coin = new Bitcoin($rpc_user,$rpc_pass,$rpc_ip,$rpc_port);
//$fBal = fNum($mb8coin->getbalance(''));
$fBal = bcadd($mb8coin->getbalance(''), "0", 8);

echo "<li>STAKING Payouts... ($fBal)</li>";
//if ($fBal != "0.00000000" && $fBal>0){
	$preg = "{^[0-9]{1,8}$}";
if($fBal > 0) {
	
$mb8coin = new Bitcoin($rpc_user,$rpc_pass,$rpc_ip,$rpc_port);
$accounts = $mb8coin->listaccounts();
$cnter = 0;
$sub_balance = 0;
foreach ($accounts as $key => $value){
	
	if (!$key){
	$key = "<font color=#FF0000>Master Account</font>";
	//echo "<b>".$key."</b> - ".$value." </br>";	
	$master_balance = $value;
		
	if ($set_stakeshare<100){
		$tmp = fNum($master_balance);
		$master_balance = bcdiv($master_balance, "100", 8);
		$master_balance = bcmul($master_balance, $set_stakeshare, 8);
		$escrow = bcsub($tmp, $master_balance, 8);
		$escrow = bcsub($escrow, $set_txfee, 8);

		echo "<li>ESCROW: $escrow</li>";
		if ($escrow>0){
			echo "<li>Sending $escrow MB8 coin to escrow.</li>";
		$mb8coin->move("", "#", $escrow);
		}
	} else {
		$escrow = 1;
	}
	
	} else {
		$cnter = $cnter + 1;
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
	//echo "<b>".$key."</b> - ".$value."</br>";	
	} else {
		if ($key == "#") {
			
		} else {
		$cnter = $cnter + 1;
	global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
    $result = $wpdb->get_results("SELECT sub_balance FROM $table WHERE id = 1");
	foreach($result as $mb8wp_config){
		$accum = $mb8wp_config->sub_balance;
	}
	$perck = fNum($value / $accum * 100);
	;
	//get the coins they should receive

	$rtmp = bcdiv($master_balance, "100", 8);
	$reward  = bcmul($rtmp, $perck, 8);

	//save the account to DB if its not already...
	
		echo "<b>".$key."</b> - ".$value." ($perck%) [$reward MB8] </br>";
		if ($reward != "0.00000000"){
			$mb8coin->move("", $key, "$reward"); 
	global $wpdb;
    $table = $wpdb->prefix."MB8WP_STAKING";

	$sql = "INSERT INTO $table (receiver_account, txn_amount, txn_date) VALUES ('$key', '$reward', '".strtotime("now")."')";

	$wpdb->query($sql);	
		}
		}
	}
	
}	 	
}
}
// # STAKING PAYOUTS

// # AUTO PAYOUT ESCROW /
if ($set_withdraw && $set_stake==1){
$mb8coin = new Bitcoin($rpc_user,$rpc_pass,$rpc_ip,$rpc_port);
$escrow_balance = bcsub($mb8coin->getbalance('#'), $set_txfee, 8);
echo "<li>Escrow BALANCE: $escrow_balance;</li>";
if ($escrow_balance >= $set_autopay){
echo "<li>AUTO PAYOUT.... ($escrow_balance) to $set_withdraw</li>";
$mb8coin->move("#", "", $set_txfee);
//DB for TX's and FEES
$mb8coin->move("#", "", $escrow_balance);
//DB for TX's and FEES
$send = $escrow_balance;
$txn = $mb8coin->sendtoaddress($set_withdraw, $send);

}
}
// # AUTO PAYOUT ESCROW /

// # RECORD TRANSACTIONS ON ACCOUNTS /

	//loop through accounts
	$mb8coin = new Bitcoin($rpc_user,$rpc_pass,$rpc_ip,$rpc_port);
	$arr_accounts = $mb8coin->listaccounts(0);
	foreach($arr_accounts as $acc => $acc_bal){
		if(strpos($acc, 'wpuid_') !== false){
	echo "<li>$acc : - : $acc_bal</li>";
		//now get listtransactions for each acc
		$xpoint = 0;
		$txns = $mb8coin->listtransactions($acc, 1000, $xpoint);
			foreach($txns as $tx_id => $tx_array){
				$save_txn = 1;
				//echo "<li>$tx_id : - : </li>";
					foreach($tx_array as $tx_dat => $tx_val){
						if (!is_array($tx_val)){
							
						if ($tx_dat == "account"){
						$transaction_account = $tx_val;	
						}
						if ($tx_dat == "address"){
						$transaction_address = $tx_val;	
						}
						if ($tx_dat == "amount"){

						$transaction_amount = mb8wp_formatE($tx_val);	

						}
						if ($tx_dat == "blockhash"){
						$transaction_blockhash = $tx_val;	
						}
						if ($tx_dat == "txid"){
						$transaction_txid = $tx_val;	
						}
						if ($tx_dat == "time"){
						$transaction_time = $tx_val;	
						}
						
						if ($tx_dat == "category"){
							$transaction_cat = $tx_val;
						}
						
						}
						
					}
					//save TXN here
					if ($save_txn>0){
						if ($transaction_cat == "move"){
							$transaction_address = "";
							$transaction_txid = "";
							$transaction_blockhash = "";
						}
					$table = $wpdb->prefix."MB8WP_TXN";
					$sql = "INSERT INTO $table (txn_account, txn_type, txn_amount, txn_id, txn_time, txn_address, txn_blockhash) VALUES ('$acc', '$transaction_cat', '$transaction_amount', '$transaction_txid', '$transaction_time', '$transaction_address', '$transaction_blockhash')";
					//echo $sql;
					//now check if we should save it...
						global $wpdb;
    $result = $wpdb->get_results("SELECT * FROM $table WHERE txn_id = '$transaction_txid' AND txn_type = '$transaction_cat' AND txn_amount = '$transaction_amount' AND txn_time = '$transaction_time'");
	$cnts = 0;
	foreach($result as $mb8wp_config){
		$cnts = $cnts + 1;
	}
	
	if($cnts<1){
		//chech if its a receive and SEND EMAIL maybe?
		
		$wpdb->query($sql);
	}
					}
			}
		}
	}
		//in each account get TXNS
		
// # RECORD TRANSACTIONS ON ACCOUNTS /

// # END OF CRON JOBS
$cron_end = strtotime("now");
?>
<hr />
--- The End Of Cron Jobs (<? echo date("d-m-Y H:i:s",$cron_end); ?>) ---
<?
}
$cron_end = strtotime("now");
    global $wpdb;
    $table = $wpdb->prefix."MB8WP_CONFIG";
    $structure = "UPDATE $table set cron_time = '$cron_end'";
    $wpdb->query($structure);
cronend:
function fNum($input){
	if($input < 0){
    return number_format(0-abs($input), 8, '.', '');
}
else {
    return number_format((float)$input, 8, '.', '');
}
}
?>