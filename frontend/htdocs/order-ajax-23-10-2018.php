<?php
include '../lib/common.php';
require_once("cfg.php");

$currency_id = $_REQUEST['currency'];
$currency_id1 = $_REQUEST['currency'];
$c_currency_id = $_REQUEST['c_currency'];

// CHECKING REFErral status 
$ch = curl_init("http://18.223.166.16/api/get-settings.php"); 
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);      
curl_close($ch);
$ref_response = json_decode($output);
if ($ref_response->is_referral == 1) {
$GLOBALS['REFERRAL'] = true;
$GLOBALS['REFERRAL_BASE_URL'] = "http://18.223.166.16/api/";
}else{
$GLOBALS['REFERRAL'] = false; 
}
// end of checking referral status

// Getting referred by user id     
API::add('Orders','getRow',array('site_users','email','where user="'.User::$info['user'].'"'));
$query = API::send();
$user_email = $query['Orders']['getRow']['results'][0]['email'];    
$user_name = User::$info['first_name'].' '.User::$info['last_name'];
$urlr = $REFERRAL_BASE_URL."get-referrer-id.php";
$fieldsr = array(
'user_id' => urlencode($user_name),
'trans_id' => urlencode($user_name),                
'name' => urlencode($user_name),
'email' => urlencode($user_email)
);
foreach($fieldsr as $key=>$value) { $fields_stringr .= $key.'='.$value.'&'; }
rtrim($fields_stringr, '&');
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $urlr);
curl_setopt($ch,CURLOPT_POST, count($fieldsr));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_stringr);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$referrer_details = curl_exec($ch);
$referrer_d = json_decode($referrer_details);                        
curl_close($ch);            
// End of Getting referred by user id

$market = $_REQUEST['trade'];
$currencies = Settings::sessionCurrency();

$buy = (!empty($_REQUEST['buy']));
$sell = (!empty($_REQUEST['sell']));
$ask_confirm = false;
$ask_confirm1 = false;
$ask_confirm11 = false;
$ask_confirm111 = false;
$currency1 = $currencies['currency'];
$c_currency1 = $currencies['c_currency'];

list($c_currency1, $currency1 ) = explode("-",$market) ;
foreach ($CFG->currencies as $key => $currency) {
if( strtolower($c_currency1) == strtolower( $currency['currency'] )){
$c_currency1 = $currency['id'] ;
}
if( strtolower( $currency1 ) == strtolower( $currency['currency'] ) ){
$currency1 = $currency['id'];
}
}

$currency_info = $CFG->currencies[$currency1];
$c_currency_info = $CFG->currencies[$c_currency1];

$from_currency = $c_currency_info['currency'];
$to_currency = $currency_info['currency'];

$confirmed = (!empty($_REQUEST['confirmed'])) ? $_REQUEST['confirmed'] : false;
$cancel = (!empty($_REQUEST['cancel'])) ? $_REQUEST['cancel'] : false;
$bypass = (!empty($_REQUEST['bypass'])) ? $_REQUEST['bypass'] : false;
$buy_market_price1 = 0;
$sell_market_price1 = 0;
$buy_limit = 1;
$sell_limit = 1;
if ($buy || $sell) {
if (empty($_SESSION["buysell_uniq"]) || empty($_REQUEST['uniq']) || !in_array($_REQUEST['uniq'],$_SESSION["buysell_uniq"]))
Errors::add('Page expired.');
}

// start of referral 
if ($REFERRAL == true) {

$name = User::$info['first_name'].' '.User::$info['last_name'];

$url = $REFERRAL_BASE_URL."get-user-bonus.php?name=1";

$fields = array(
'user_id' => urlencode($name),
'name' => urlencode($name),                
'email' => urlencode($user_email)
);
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$result = curl_exec($ch);
$response = json_decode($result);
curl_close($ch);
$one_point_value = $response->settings->one_point_value;

$referral_code = $response->data->referral_code; 
$bonous_point = $response->data->bonous_point;

if ($to_currency == 'USD') {
$bonus_amount = (float) $bonous_point / (float) $one_point_value;
$cur_code = '$';
}else{
$one_point_values = $response->settings->$to_currency;
$bonus_amount = (float) $bonous_point / (float) $one_point_values;
$cur_code = $to_currency;
}


// $his_url = $REFERRAL_BASE_URL."get-usage-history.php";
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$result = curl_exec($ch);
$response = json_decode($result);
curl_close($ch);
}

// end of referral

API::add('User','hasCurrencies');
API::add('Orders','getBidAsk',array($c_currency1,$currency1));
API::add('Orders','get',array(false,false,10,$c_currency1,$currency1,false,false,1));
API::add('Orders','get',array(false,false,10,$c_currency1,$currency1,false,false,false,false,1));
API::add('Transactions','get',array(false,false,1,$c_currency1,$currency1));
API::add('Transactions','get24hData',array(28,51));
API::add('Transactions','get24hData',array(42,51));
API::add('Transactions','get24hData',array(42,28));
API::add('Transactions','get24hData',array(43,51));
API::add('Transactions','get24hData',array(43,28));
API::add('Transactions','get24hData',array(43,51));
API::add('Transactions','get24hData',array(43,28));
API::add('Transactions','get24hData',array(45,51));
API::add('Transactions','get24hData',array(45,28));
API::add('Transactions','get24hData',array(42,45));
API::add('Transactions','get24hData',array(43,45));
API::add('Transactions','get24hData',array(44,51));
API::add('Transactions','get24hData',array(44,28));
API::add('Transactions','get24hData',array($c_currency1, $currency1));
API::add('Transactions','get24hData',array(28,42)); //btc-ltc
API::add('Transactions','get24hData',array(45,42)); //eth-ltc
API::add('Transactions','get24hData',array(43,42)); //zec-ltc
API::add('Transactions','get24hData',array(44,42)); //bch-ltc

API::add('Transactions','get24hData',array(28,44)); //btc-bch
API::add('Transactions','get24hData',array(45,44)); //btc-eth
API::add('Transactions','get24hData',array(43,44)); //btc-zec
API::add('Transactions','get24hData',array(42,44)); //btc-ltc

API::add('Transactions','get24hData',array(28,43)); //btc-zec
API::add('Transactions','get24hData',array(42,43)); //ltc-zec
API::add('Transactions','get24hData',array(45,43)); //eth-zec
API::add('Transactions','get24hData',array(44,43)); //bch-zec

API::add('Transactions','get24hData',array(28,45)); //btc-eth
API::add('Transactions','get24hData',array(42,45)); //ltc-eth
API::add('Transactions','get24hData',array(44,45)); //bch-eth
API::add('Transactions','get24hData',array(43,45)); //zec-eth


//IOX       

API::add('Transactions','get24hData',array(28,50)); //btc-iox
API::add('Transactions','get24hData',array(42,50)); //ltc-iox
API::add('Transactions','get24hData',array(44,50)); //bch-iox
API::add('Transactions','get24hData',array(43,50)); //zec-iox
API::add('Transactions','get24hData',array(45,50)); //eth-iox
API::add('Transactions','get24hData',array(51,50)); //usdt-iox

API::add('Transactions','get24hData',array(50,28)); //iox-btc
API::add('Transactions','get24hData',array(50,42)); //iox-ltc
API::add('Transactions','get24hData',array(50,44)); //iox-bch
API::add('Transactions','get24hData',array(50,43)); //iox-zec
API::add('Transactions','get24hData',array(50,45)); //iox-eth
API::add('Transactions','get24hData',array(50,51)); //iox-usdt

$query = API::send();
$currentPair = $query['Transactions']['get24hData']['results'][13];
$total = $query['Transactions']['get']['results'][0];
$user_available_currencies = $query['User']['hasCurrencies']['results'];
$current_bid = $query['Orders']['getBidAsk']['results'][0]['bid'];
$current_ask =  $query['Orders']['getBidAsk']['results'][0]['ask'];    
$bids = $query['Orders']['get']['results'][0];
$asks = $query['Orders']['get']['results'][1];

API::add('FeeSchedule','getRecord',array(User::$info['fee_schedule']));
API::add('User','getAvailable');
$feequery = API::send();
$user_fee_both = $feequery['FeeSchedule']['getRecord']['results'][0];
$user_available = $feequery['User']['getAvailable']['results'][0];

$user_fee_bid = ($buy && ((Stringz::currencyInput($_REQUEST['buy_amount']) > 0 && Stringz::currencyInput($_REQUEST['buy_price']) >= $asks[0]['btc_price']) || !empty($_REQUEST['buy_market_price']) || empty($_REQUEST['buy_amount']))) ? $feequery['FeeSchedule']['getRecord']['results'][0]['fee'] : $feequery['FeeSchedule']['getRecord']['results'][0]['fee1'];
$user_fee_ask = ($sell && ((Stringz::currencyInput($_REQUEST['sell_amount']) > 0 && Stringz::currencyInput($_REQUEST['sell_price']) <= $bids[0]['btc_price']) || !empty($_REQUEST['sell_market_price']) || empty($_REQUEST['sell_amount']))) ? $feequery['FeeSchedule']['getRecord']['results'][0]['fee'] : $feequery['FeeSchedule']['getRecord']['results'][0]['fee1'];
$transactions = $query['Transactions']['get']['results'][0];
$my_transactions = $query['Transactions']['get']['results'][1];
$usd_field = 'usd_ask';
$transactions_24hrs_btc_usd = $query['Transactions']['get24hData']['results'][0] ;
$transactions_24hrs_ltc_usd = $query['Transactions']['get24hData']['results'][1] ;
$transactions_24hrs_ltc_btc = $query['Transactions']['get24hData']['results'][2] ;
$transactions_24hrs_zec_usd = $query['Transactions']['get24hData']['results'][3] ;
$transactions_24hrs_zec_btc = $query['Transactions']['get24hData']['results'][4] ;
$transactions_24hrs_eth_usd = $query['Transactions']['get24hData']['results'][5] ;
$transactions_24hrs_eth_btc = $query['Transactions']['get24hData']['results'][6] ;
$transactions_24hrs_ltc_eth = $query['Transactions']['get24hData']['results'][7] ;
$transactions_24hrs_zec_eth = $query['Transactions']['get24hData']['results'][8] ;
$transactions_24hrs_bch_usd = $query['Transactions']['get24hData']['results'][9] ;
$transactions_24hrs_bch_btc = $query['Transactions']['get24hData']['results'][10] ;

$transactions_24hrs_btc_ltc = $query['Transactions']['get24hData']['results'][14] ;
$transactions_24hrs_eth_ltc = $query['Transactions']['get24hData']['results'][15] ;
$transactions_24hrs_zec_ltc = $query['Transactions']['get24hData']['results'][16] ;
$transactions_24hrs_bch_ltc = $query['Transactions']['get24hData']['results'][17] ;

$transactions_24hrs_btc_bch = $query['Transactions']['get24hData']['results'][18] ;
$transactions_24hrs_eth_bch = $query['Transactions']['get24hData']['results'][19] ;
$transactions_24hrs_zec_bch = $query['Transactions']['get24hData']['results'][20] ;
$transactions_24hrs_ltc_bch = $query['Transactions']['get24hData']['results'][21] ;

$transactions_24hrs_btc_zec = $query['Transactions']['get24hData']['results'][22] ;
$transactions_24hrs_ltc_zec = $query['Transactions']['get24hData']['results'][23] ;
$transactions_24hrs_eth_zec = $query['Transactions']['get24hData']['results'][24] ;
$transactions_24hrs_bch_zec = $query['Transactions']['get24hData']['results'][25] ;

$transactions_24hrs_btc_eth = $query['Transactions']['get24hData']['results'][26] ;
$transactions_24hrs_ltc_eth = $query['Transactions']['get24hData']['results'][27] ;
$transactions_24hrs_bch_eth = $query['Transactions']['get24hData']['results'][28] ;
$transactions_24hrs_zec_eth = $query['Transactions']['get24hData']['results'][29] ;

$transactions_24hrs_btc_iox = $query['Transactions']['get24hData']['results'][30] ;
$transactions_24hrs_ltc_iox = $query['Transactions']['get24hData']['results'][31] ;
$transactions_24hrs_bch_iox = $query['Transactions']['get24hData']['results'][32] ;
$transactions_24hrs_zec_iox = $query['Transactions']['get24hData']['results'][33] ;
$transactions_24hrs_eth_iox = $query['Transactions']['get24hData']['results'][34] ;
$transactions_24hrs_usdt_iox = $query['Transactions']['get24hData']['results'][35] ;

$transactions_24hrs_iox_btc = $query['Transactions']['get24hData']['results'][36] ;
$transactions_24hrs_iox_ltc = $query['Transactions']['get24hData']['results'][37] ;
$transactions_24hrs_iox_bch = $query['Transactions']['get24hData']['results'][38] ;
$transactions_24hrs_iox_zec = $query['Transactions']['get24hData']['results'][39] ;
$transactions_24hrs_iox_eth = $query['Transactions']['get24hData']['results'][40] ;
$transactions_24hrs__iox_usdt = $query['Transactions']['get24hData']['results'][41] ;

$i = 0;
$stats = array();
$market_stats = array();
foreach ($CFG->currencies as $key => $currency) {
if (is_numeric($key) || $currency['is_crypto'] != 'Y')
continue;

$k = $query['Stats']['getCurrent']['results'][$i]['market'];
if ($CFG->currencies[$k]['id'] == $c_currency1)
$stats = $query['Stats']['getCurrent']['results'][$i];

$market_stats[$k] = $query['Stats']['getCurrent']['results'][$i];
$i++;
}

if ($currency_info['is_crypto'] != 'Y')
$bank_accounts = $query['BankAccounts']['get']['results'][0];

if ($_REQUEST['stopbuy'] == 1) {
$buy_amount1 = (!empty($_REQUEST['buy_amount11'])) ? Stringz::currencyInput($_REQUEST['buy_amount11']) : 0;

}elseif ($_REQUEST['buy'] == 1) {
$buy_amount1 = (!empty($_REQUEST['buy_amount'])) ? Stringz::currencyInput($_REQUEST['buy_amount']) : 0;
}
$buy_price1 = (!empty($_REQUEST['buy_price'])) ? Stringz::currencyInput($_REQUEST['buy_price']) : $current_ask;
$buy_subtotal1 = $buy_amount1 * $buy_price1;
$buy_fee_amount1 = ($user_fee_bid * 0.01) * $buy_subtotal1;

// referral bonus starts
if ($_REQUEST['is_referral']) {
if ($_REQUEST['bonus_amount']) {
$buy_fee_amount1 = $buy_fee_amount1 - $_REQUEST['bonus_amount'];
}
}
// end of referral bonus

$buy_total1 = round($buy_subtotal1 + $buy_fee_amount1,($currency_info['is_crypto'] == 'Y' ? 8 : 2),PHP_ROUND_HALF_UP);
$buy_stop = false;
$buy_stop_price1 = false;
$buy_all1 = (!empty($_REQUEST['buy_all']));
if ($_REQUEST['stopsell'] == 1) {
$sell_amount1 = (!empty($_REQUEST['sell_amount11'])) ? Stringz::currencyInput($_REQUEST['sell_amount11']) : 0;

}elseif ($_REQUEST['sell'] == 1) {
$sell_amount1 = (!empty($_REQUEST['sell_amount'])) ? Stringz::currencyInput($_REQUEST['sell_amount']) : 0;
}

$sell_price1 = (!empty($_REQUEST['sell_price'])) ? Stringz::currencyInput($_REQUEST['sell_price']) : $current_bid;
$sell_subtotal1 = $sell_amount1 * $sell_price1;
$sell_fee_amount1 = ($user_fee_ask * 0.01) * $sell_subtotal1;

// referral bonus starts
if ($_REQUEST['is_referral']) {
if ($_REQUEST['bonus_amount']) {
$sell_fee_amount1 = $sell_fee_amount1 - $_REQUEST['bonus_amount'];
}
}
//
$sell_total1 = round($sell_subtotal1 - $sell_fee_amount1,($currency_info['is_crypto'] == 'Y' ? 8 : 2),PHP_ROUND_HALF_UP);
$sell_stop = false;
$sell_stop_price1 = false;

if ($CFG->trading_status == 'suspended')
Errors::add(Lang::string('buy-trading-disabled'));

if ($buy) {

$buy_market_price1 = (!empty($_REQUEST['buy_market_price']));
$buy_market_price1 = $_REQUEST['buy_market_price']?$_REQUEST['buy_market_price']:"";
$buy_price1 = ($buy_market_price1) ? $current_ask : $buy_price1;
$buy_stop = (!empty($_REQUEST['buy_stop']));
$buy_stop_price1 = ($buy_stop) ? Stringz::currencyInput($_REQUEST['buy_stop_price']) : false;
$buy_limit = (!empty($_REQUEST['buy_limit']));
$buy_limit = (!$buy_stop && !$buy_market_price1) ? 1 : $buy_limit;

if (!$confirmed && !$cancel) {
API::add('Orders','checkPreconditions',array(1,$c_currency1,$currency_info,$buy_amount1,(($buy_stop && !$buy_limit) ? $buy_stop_price1 : $buy_price1),$buy_stop_price1,$user_fee_bid,$user_available[$currency_info['currency']],$current_bid,$current_ask,$buy_market_price1,false,false,$buy_all1));
$query = API::send();        
if (!$buy_market_price1)
API::add('Orders','checkUserOrders',array(1,$c_currency1,$currency_info,false,(($buy_stop && !$buy_limit) ? $buy_stop_price1 : $buy_price1),$buy_stop_price1,$user_fee_bid,$buy_stop));

$query1 = API::send();
$errors1 = $query['Orders']['checkPreconditions']['results'][0];
if (!empty($errors1['error']))
Errors::add($errors1['error']['message']);
$errors2 = (!empty($query1['Orders']['checkUserOrders']['results'][0])) ? $query1['Orders']['checkUserOrders']['results'][0] : false;
if (!empty($errors2['error']))
Errors::add($errors2['error']['message']);

if (!$errors1 && !$errors2){
if ($_REQUEST['stopbuy'] == 1) 
$ask_confirm11 = true;  // Stop - buy
else if($_REQUEST['buy'] == 1)    
$ask_confirm = true;  //  Limit - buy
}

}
else if (!$cancel) {         

// Bonus point used
if ($_REQUEST['is_referral']) {
$fee_amount=($buy_subtotal1*$user_fee_bid)/100;
if($fee_amount<$bonous_point)
{
$buy_bonus_points_used = $fee_amount;
}
else if($fee_amount>=$bonous_point)
{
$buy_bonus_points_used = $bonous_point;
}
}
// End of Bonus point used

API::add('Orders','executeOrder',array(1,(($buy_stop && !$buy_limit) ? $buy_stop_price1 : $buy_price1),$buy_amount1,$c_currency1,$currency1,$user_fee_bid,$buy_market_price1,false,false,false,$buy_stop_price1,false,false,$buy_all1,$referrer_d->referrer_id,$buy_bonus_points_used));
$query = API::send();        
$operations = $query['Orders']['executeOrder']['results'][0];
if (!empty($operations['error'])) {
Errors::add($operations['error']['message']);
}
else if ($operations['new_order'] > 0) {
$_SESSION["buysell_uniq"][time()] = md5(uniqid(mt_rand(),true));
if (count($_SESSION["buysell_uniq"]) > 3) {
unset($_SESSION["buysell_uniq"][min(array_keys($_SESSION["buysell_uniq"]))]);
}

// updating referral bonus
$name = User::$info['first_name'].' '.User::$info['last_name'];
$url = $REFERRAL_BASE_URL."use-bonus.php?name=1";
$fields = array(
'user_id' => urlencode($name),
'trans_id' => urlencode($name),
// 'points' => urlencode($bonous_point),
'points' => urlencode($buy_bonus_points_used),
'name' => urlencode($name),
'email' => urlencode($user_email)
);
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$result = curl_exec($ch);
$response = json_decode($result);
curl_close($ch);

Link::redirect('openorders?c_currency='.$_REQUEST["c_currency"].'&currency='.$_REQUEST["currency"].'&transactions='.$operations["transactions"].'new_order=1');

exit;
}
else {
$_SESSION["buysell_uniq"][time()] = md5(uniqid(mt_rand(),true));
if (count($_SESSION["buysell_uniq"]) > 3) {
unset($_SESSION["buysell_uniq"][min(array_keys($_SESSION["buysell_uniq"]))]);
}

// updating referral bonus
$name = User::$info['first_name'].' '.User::$info['last_name'];
$url = $REFERRAL_BASE_URL."use-bonus.php?name=1";
$fields = array(
'user_id' => urlencode($name),
'trans_id' => urlencode($name),
// 'points' => urlencode($bonous_point),
'points' => urlencode($buy_bonus_points_used),
'name' => urlencode($name),
'email' => urldecode($user_email)
);
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$result = curl_exec($ch);
$response = json_decode($result);
curl_close($ch);

Link::redirect('openorders?c_currency='.$_REQUEST["c_currency"].'&currency='.$_REQUEST["currency"].'&transactions='.$operations["transactions"].'');

exit;
}
}
}

if ($sell) {
$sell_market_price1 = (!empty($_REQUEST['sell_market_price']));
$sell_price1 = ($sell_market_price1) ? $current_bid : $sell_price1;
$sell_stop = (!empty($_REQUEST['sell_stop']));
$sell_stop_price1 = ($sell_stop) ? Stringz::currencyInput($_REQUEST['sell_stop_price']) : false;
$sell_limit = (!empty($_REQUEST['sell_limit']));
$sell_limit = (!$sell_stop && !$sell_market_price1) ? 1 : $sell_limit;

if (!$confirmed && !$cancel) {
API::add('Orders','checkPreconditions',array(0,$c_currency1,$currency_info,$sell_amount1,(($sell_stop && !$sell_limit) ? $sell_stop_price1 : $sell_price1),$sell_stop_price1,$user_fee_ask,$user_available[$c_currency_info['currency']],$current_bid,$current_ask,$sell_market_price1));
$query = API::send();
if (!$sell_market_price1)
API::add('Orders','checkUserOrders',array(0,$c_currency1,$currency_info,false,(($sell_stop && !$sell_limit) ? $sell_stop_price1 : $sell_price1),$sell_stop_price1,$user_fee_ask,$sell_stop));

$query1 = API::send();
$errors1 = $query['Orders']['checkPreconditions']['results'][0];
if (!empty($errors1['error']))
Errors::add($errors1['error']['message']);
$errors2 = (!empty($query1['Orders']['checkUserOrders']['results'][0])) ? $query1['Orders']['checkUserOrders']['results'][0] : false;
if (!empty($errors2['error']))
Errors::add($errors2['error']['message']);

if (!$errors1 && !$errors2){
if ($_REQUEST['stopsell'] == 1) 
$ask_confirm111 = true;  //  Stop - Sell
else if($_REQUEST['sell'] == 1)    
$ask_confirm1 = true;  // Limit - Sell
}
}
else if (!$cancel) {

// Bonus point used
if ($_REQUEST['is_referral']) {
$fee_amount=($sell_subtotal1*$user_fee_ask)/100;
if($fee_amount<$bonous_point)
{
$sell_bonus_points_used = $fee_amount;
}
else if($fee_amount>=$bonous_point)
{
$sell_bonus_points_used = $bonous_point;
}
}
// End of Bonus point used

API::add('Orders','executeOrder',array(0,($sell_stop && !$sell_limit) ? $sell_stop_price1 : $sell_price1,$sell_amount1,$c_currency1,$currency1,$user_fee_ask,$sell_market_price1,false,false,false,$sell_stop_price1,false,false,false,$referrer_d->referrer_id,'',$sell_bonus_points_used));
$query = API::send();
$operations = $query['Orders']['executeOrder']['results'][0];

if (!empty($operations['error'])) {
Errors::add($operations['error']['message']);
}
else if ($operations['new_order'] > 0) {
$_SESSION["buysell_uniq"][time()] = md5(uniqid(mt_rand(),true));
if (count($_SESSION["buysell_uniq"]) > 3) {
unset($_SESSION["buysell_uniq"][min(array_keys($_SESSION["buysell_uniq"]))]);
}

// updating referral bonus
$name = User::$info['first_name'].' '.User::$info['last_name'];
$url = $REFERRAL_BASE_URL."use-bonus.php?name=1";
$fields = array(
'user_id' => urlencode($name),
'trans_id' => urlencode($name),
// 'points' => urlencode($bonous_point),
'points' => urlencode($sell_bonus_points_used),
'name' => urlencode($name),
'email' => urldecode($user_email)
);
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$result = curl_exec($ch);
$response = json_decode($result);
curl_close($ch);

Link::redirect('openorders?c_currency='.$_REQUEST["c_currency"].'&currency='.$_REQUEST["currency"].'&transactions='.$operations["transactions"].'new_order=1');

exit;
}
else {
$_SESSION["buysell_uniq"][time()] = md5(uniqid(mt_rand(),true));
if (count($_SESSION["buysell_uniq"]) > 3) {
unset($_SESSION["buysell_uniq"][min(array_keys($_SESSION["buysell_uniq"]))]);
}

// updating referral bonus
$name = User::$info['first_name'].' '.User::$info['last_name'];
$url = $REFERRAL_BASE_URL."use-bonus.php?name=1";
$fields = array(
'user_id' => urlencode($name),
'trans_id' => urlencode($name),
// 'points' => urlencode($bonous_point),
'points' => urlencode($sell_bonus_points_used),
'name' => urlencode($name),
'email' => urldecode($user_email)
);
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$result = curl_exec($ch);
$response = json_decode($result);
curl_close($ch);

Link::redirect('openorders?c_currency='.$_REQUEST["c_currency"].'&currency='.$_REQUEST["currency"].'&transactions='.$operations["transactions"].''); //newly added 
exit;
}
}
}

$notice = '';
if ($ask_confirm && $sell) {
if (!$bank_accounts && $currency_info['is_crypto'] != 'Y')
$notice .= '<div class="message-box-wrap">'.str_replace('[currency]',$currency_info['currency'],Lang::string('buy-errors-no-bank-account')).'</div>';

if (($buy_limit && $buy_stop) || ($sell_limit && $sell_stop))
$notice .= '<div class="message-box-wrap">'.Lang::string('buy-notify-two-orders').'</div>';
}

$select = "" ;
foreach ($CFG->currencies as $key => $currency) {
if (is_numeric($key) || $currency['is_crypto'] != 'Y')
continue;
if($c_currency1 == $currency['id'])
$select = $currency['currency'] ;
}

$page_title = Lang::string('buy-sell');
$_SESSION["buysell_uniq"][time()] = md5(uniqid(mt_rand(),true));
if (count($_SESSION["buysell_uniq"]) > 3) {
unset($_SESSION["buysell_uniq"][min(array_keys($_SESSION["buysell_uniq"]))]);
}
?>


<!-- Buy stop form -->

<?php if($_REQUEST['stopbuy']==1) { ?>

<form id="confirm_form" action="exchange-order.php?trade=<?= $market ?>&c_currency=<?= $_REQUEST['c_currency'] ?>&currency=<?= $_REQUEST['currency'] ?>" method="POST">
<input type="hidden" name="confirmed" value="1" />
<input type="hidden" id="buy_all" name="buy_all" value="<?= $buy_all1 ?>" />
<input type="hidden" id="cancel" name="cancel" value="" />
<div class="form-box">
<? if ($buy) { ?>
<div class="form-inner1">
<div class="content">
<div class="bskbTZ">
<p style="margin-bottom:0px;"><?= Lang::string('buy-amount') ?></p>
<h4><b><?= Stringz::currency($buy_amount1,true) ?></b></h4>
<input type="hidden" name="buy_amount" id="buy_amount" value="<?= Stringz::currencyOutput($buy_amount1) ?>" />
</div>
<div class="bskbTZ">
<p style="margin-bottom:0px;"><?= Lang::string('buy-with-currency') ?></p>
<h4><b><?= $currency_info['currency'] ?></b></h4>
<input type="hidden" name="buy_currency" id="buy_currency" value="<?= $currency1 ?>" />
</div>
<div class="input-group mb-3">
<?php if ($buy_stop) { ?>
<div class="bskbTZ">
<p style="margin-bottom:0px;"><?= Lang::string('buy-stop-price') ?></p>
<h4><b><?= Stringz::currency($buy_stop_price1,($currency_info['is_crypto'] == 'Y')) ?></b></h4>
<input type="hidden" name="buy_stop_price" id="buy_stop_price" value="<?= Stringz::currencyOutput($buy_stop_price1) ?>" />
</div>
<?php } ?>
</div>
<div class="input-group mb-3">
<? if ($buy_stop) { ?>
<label class="cont" style="padding-left:2em;"><?= Lang::string('buy-stop') ?>   
<input disabled="disabled" class="checkbox" name="dummy" id="buy_stop" type="checkbox" value="1" <?= ($buy_stop && !$buy_market_price1) ? 'checked="checked"' : '' ?> style="vertical-align: middle;margin-left: 5px;width: 20px;height: 20px;"/>
<input type="hidden" name="buy_stop" value="<?= $buy_stop ?>" />
<?php } ?>
</div>
<hr>
<div class="input-group mb-3">
<? if ($buy_stop) { ?> 

<table width="100%">
<tr>
<td><p style="margin-bottom:0px;"><?= Lang::string('buy-subtotal') ?></p></td>
<td align="right"><h4><b><?= $currency_info['fa_symbol'] ?><?= Stringz::currency($buy_amount1 * $buy_stop_price1,($currency_info['is_crypto'] == 'Y')) ?></b></h4></td>
</tr>
</table>
<?php
if (($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50)) {
?>
<table width="100%">
<tr>
<td><p style="margin-bottom:0px;">
<?= Lang::string('buy-fee') ?></p></td>
<td align="right"><h4><b><span id="sell_user_fee"><?= Stringz::currency($user_fee_bid) ?></span>%</b></h4></td>
<input type="hidden" id="user_fee_stopbuy" value="<?= $fee_value ?>"/>
</tr>
</table>
<?php } ?>


<!-- Stop-limit buy referral -->

<?php if($REFERRAL == true && $one_point_values>0 && ($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50) ){ ?>
<input type="hidden" name="ref_status" id="ref_status" value="1">
<input type="hidden" name="bonus_amount" id="bonus_amount" value="<? echo $bonus_amount; ?>">
<label class="cont" style="color: brown;font-style:  italic;">
<input 
class="checkbox" 
name="is_referral" 
id="is_referral" 
onclick="calculateBuyPrice_stop_limit_buy()"
type="checkbox" value="1"
<? if($bonous_point == 0){ echo 'disabled'; } ?>
/>
Use your Referral Bonus

<span style="float: right;margin-left: 50px;">    
<? echo $cur_code; ?> <? echo $bonus_amount; ?>
</span>

<span class="checkmark"></span>
</label>
<?php } ?>
<!-- End of Stop-limit buy referral -->
<table width="100%">
<tr>
<td><p style="margin-bottom:0px;">
<span id="buy_total_approx_label"><?= str_replace('[currency]','<span class="buy_currency_label">'.$currency_info['currency'].'</span>',Lang::string('buy-total-approx')) ?></span>
</p></td>
<?php
if (($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50)) {
?>
<td align="right"><h4>
<span id="buy_total_label" style="display:none;"><?= Lang::string('buy-total') ?></span>
<b><span id="buy_total"><?= Stringz::currency(round($buy_amount1 * $buy_stop_price1 + ($user_fee_ask * 0.01) * $buy_amount1 * $buy_stop_price1 ,($currency_info['is_crypto'] == 'Y' ? 8 : 2),PHP_ROUND_HALF_UP),($currency_info['is_crypto'] == 'Y')) ?></span></b>
</h4></td>
<?php } else { ?>
<td align="right"><h4>
<span id="buy_total_label" style="display:none;"><?= Lang::string('buy-total') ?></span>
<b><span id="buy_total"><?= $currency_info['fa_symbol'] ?><?= Stringz::currency($buy_amount1 * $buy_stop_price1,($currency_info['is_crypto'] == 'Y')) ?></span></b>
</h4></td>
<?php } ?>
</tr>
</table>
<? } ?>
</div>
<input type="hidden" name="buy" value="1" />
<input type="hidden" name="uniq" value="<?= end($_SESSION["buysell_uniq"]) ?>" />
<div class="btn-otr">
<span>
<input type="button" name="submit" value="<?= Lang::string('confirm-buy') ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-primary" style="width: auto;display: inline-block;" onclick="javascript:order_stop_buy_confirm();"/>
</span>
<span>
<input id="cancel_transaction" type="button" name="dont" value="Back" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn btn-primary" style="width: auto;display: inline-block;float: right;"  onclick="javascript:order_stop_buy_cancel();">
</span>
<p class="m-t-10"> By clicking CONFIRM button an order request will be created.</p>
</div>

</div>
</div>
<?php }?>
</div>
</form>

<?php } ?>

<!-- End of Buy stop form -->


<!-- Sell stop form -->

<?php if($_REQUEST['stopsell']==1) { ?>

<form id="confirm_form" action="exchange-order.php?trade=<?= $market ?>&c_currency=<?= $_REQUEST['c_currency'] ?>&currency=<?= $_REQUEST['currency'] ?>&stopbuy=1" method="POST">
<input type="hidden" name="confirmed" value="1" />
<input type="hidden" id="buy_all" name="buy_all" value="<?= $buy_all1 ?>" />
<input type="hidden" id="cancel" name="cancel" value="" />

<div class="bskbTZ">
<p style="margin-bottom:0px;"><?= Lang::string('sell-amount') ?></p>
<h4><b><?= Stringz::currency($sell_amount1,true) ?></b></h4>
<input type="hidden" name="sell_amount" id="sell_amount" value="<?= Stringz::currencyOutput($sell_amount1) ?>" />
</div>
<div class="bskbTZ">
<p style="margin-bottom:0px;"><?= Lang::string('buy-with-currency') ?></p>
<h4><b><?= $currency_info['currency'] ?></b></h4>
<input type="hidden" name="sell_currency" id="sell_currency" value="<?= $currency1 ?>" />
</div>

<? if ($sell_stop) { ?>
<div class="bskbTZ">
<p style="margin-bottom:0px;"><?= Lang::string('buy-stop-price') ?></p>
<h4><b><?= Stringz::currency($sell_stop_price1,($currency_info['is_crypto'] == 'Y')) ?></b></h4>
<input type="hidden" name="sell_stop_price" id="sell_stop_price" value="<?= Stringz::currencyOutput($sell_stop_price1) ?>" />
</div>
<?php } ?>

<? if ($sell_stop) { ?>
<label class="cont"><?= Lang::string('buy-stop') ?>   <input disabled="disabled" class="checkbox" name="dummy" id="sell_stop" type="checkbox" value="1" <?= ($sell_stop && !$sell_market_price1) ? 'checked="checked"' : '' ?> style="vertical-align: middle;margin-left: 5px;width: 20px;height: 20px;"/>
<input type="hidden" name="sell_stop" value="<?= $sell_stop ?>" />
<?php } ?>
<span class="checkmark"></span>
</label>
<? if ($sell_stop) { ?>
<div class="current-otr">
<p style="margin-bottom:0px"><?= Lang::string('buy-subtotal') ?> </p>
<h4><b><?= $currency_info['fa_symbol'] ?><?= Stringz::currency($sell_amount1 * $sell_stop_price1,($currency_info['is_crypto'] == 'Y')) ?></b></h4>
</div>
<div class="current-otr">
<p style="margin-bottom:0px">
<?= Lang::string('buy-fee') ?>
</p>
<h4><b><span id="sell_user_fee"><?= Stringz::currency($user_fee_bid) ?></span>%</b></h4>
</div>

<!-- Stop-limit sell referral -->
<?php if($REFERRAL == true && $one_point_values>0 && ($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50) ){ ?>
<input type="hidden" name="ref_status" id="ref_status" value="1">
<input type="hidden" name="bonus_amount" id="bonus_amount" value="<? echo $bonus_amount; ?>">
<label class="cont" style="color: brown;font-style:  italic;">
<input 
class="checkbox" 
name="is_referral" 
id="is_referral_sell" 
onclick="calculateBuyPrice_stop_limit_sell()"
type="checkbox" value="1"
<? if($bonous_point == 0){ echo 'disabled'; } ?>
/>
Use your Referral Bonus

<span style="float: right;margin-left: 50px;">    
<? echo $cur_code; ?> <? echo $bonus_amount; ?>
</span>

<span class="checkmark"></span>
</label>
<?php } ?>
<!-- End of Stop-limit sell referral -->

<div class="current-otr m-b-15">
<p style="margin-bottom:0px">
<span id="sell_total_approx_label"><?= str_replace('[currency]','<span class="sell_currency_label">'.$currency_info['currency'].'</span>',Lang::string('sell-total-approx')) ?></span>
</p>
<h4>
<span id="sell_total_label" style="display:none;"><?= Lang::string('sell-total') ?></span>
<b><?= $currency_info['fa_symbol'] ?><span id="sell_total"><?= Stringz::currency(round($sell_amount1 * $sell_stop_price1 - ($user_fee_ask * 0.01) * $sell_amount1 * $sell_stop_price1 ,($currency_info['is_crypto'] == 'Y' ? 8 : 2),PHP_ROUND_HALF_UP),($currency_info['is_crypto'] == 'Y')) ?></span></b>
</h4>
</div>
<? }?>
<input type="hidden" name="sell" value="1" />
<input type="hidden" name="uniq" value="<?= end($_SESSION["buysell_uniq"]) ?>" />
<div class="btn-otr">
<span>
<input type="button" name="submit" value="<?= Lang::string('confirm-sale') ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-primary" style="width: auto;display: inline-block;padding: 12px 30px;" onclick="javascript:order_stop_sell_confirm();"/>
</span>
<span>
<input type="button" name="dont" value="Back" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-primary" style="width: auto;display: inline-block;float: right;padding: 12px 30px;"  onclick="javascript:order_stop_sell_cancel();">
</span>
</div>

</form>

<?php } ?>

<!-- End of sell stop form -->


<!-- Limit Buy Form -->

<?php if($_REQUEST['limit_buy']==1) { ?>

<form id="confirm_form" action="exchange-order.php?trade=<?= $market ?>&c_currency=<?= $_REQUEST['c_currency'] ?>&currency=<?= $_REQUEST['currency'] ?>&buy=1" method="POST">
<input type="hidden" name="confirmed" value="1" />
<input type="hidden" id="buy_all" name="buy_all" value="<?= $buy_all1 ?>" />
<input type="hidden" id="cancel" name="cancel" value="" />
<div class="form-box">
<? if ($buy && !$ask_confirm11) { ?>
<div class="form-inner1">
<div class="content">
<div class="bskbTZ">
<p style="margin-bottom:0px;"><?= Lang::string('buy-amount') ?></p>
<h4><b><?= Stringz::currency($buy_amount1,true) ?></b></h4>
<input type="hidden" name="buy_amount" id="buy_amount" value="<?= Stringz::currencyOutput($buy_amount1) ?>" />
</div>
<div class="bskbTZ">
<p style="margin-bottom:0px;"><?= Lang::string('buy-with-currency') ?></p>
<h4><b><?= $currency_info['currency'] ?></b></h4>
<input type="hidden" name="buy_currency" value="<?= $currency1 ?>" />
</div>
<div class="input-group mb-3">
<? if ($buy_limit || $buy_market_price1) { ?>
<div class="bskbTZ">
<p style="margin-bottom:0px;"><?= ($buy_market_price1) ? Lang::string('buy-price') : Lang::string('buy-limit-price') ?></p>
<h4><b><?= Stringz::currency($buy_price1,($currency_info['is_crypto'] == 'Y')) ?></b></h4>
<input type="hidden" name="buy_price" id="buy_price" value="<?= Stringz::currencyOutput($buy_price1) ?>" />
</div>
<?php } ?>
</div>
<div class="input-group mb-3">
<? if ($buy_limit) { ?>
<label class="cont"><?= Lang::string('buy-limit') ?>   <input disabled="disabled" class="checkbox" name="dummy" id="buy_limit" type="checkbox" value="1" <?= ($buy_limit && !$buy_market_price1) ? 'checked="checked"' : '' ?> style="vertical-align: middle;margin-left: 5px;width: 20px;height: 20px;"/>
<input type="hidden" name="buy_limit" value="<?= $buy_limit ?>"/>
<?php } ?>
</div>
<hr>
<div class="input-group mb-3">
<div class="current-otr">
<p style="margin-bottom:0px;"><?= Lang::string('buy-subtotal') ?></p>
<h4><b><?= $currency_info['fa_symbol'] ?><?= Stringz::currency($buy_subtotal1,($currency_info['is_crypto'] == 'Y')) ?></b></h4>
</div>
</div>

<?php
if (($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50)) {
?>
<div class="input-group mb-3">
<div class="current-otr">
<p style="margin-bottom:0px;"><?= Lang::string('buy-fee') ?></p>
<h4><b><span id="sell_user_fee"><?= Stringz::currency($user_fee_bid) ?></span>%</b></h4>
</div>
</div>
<?php } else { ?>
<div class="input-group mb-3">
<div class="current-otr">
<p style="margin-bottom:0px;"><?= Lang::string('buy-fee') ?></p>
<h4><b><span id="sell_user_fee">0.00</span>%</b></h4>
</div>
</div>
<?php } ?>


<!-- Buy referral -->
<?php if($REFERRAL == true && $one_point_values>0 && ($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50)){ ?>
<input type="hidden" name="ref_status" id="ref_status" value="1">
<input type="hidden" name="bonus_amount" id="bonus_amount" value="<? echo $bonus_amount; ?>">
<label class="cont" style="color: brown;font-style:  italic;">
<input 
class="checkbox" 
name="is_referral" 
id="is_referral" 
onclick="calculateBuyPrice_buy()"
type="checkbox" value="1"
<? if($bonous_point == 0){ echo 'disabled'; } ?>
/>
Use your Referral Bonus

<span style="float: right;margin-left: 50px;">    
<? echo $cur_code; ?> <? echo $bonus_amount; ?>
</span>

<span class="checkmark"></span>
</label>
<?php } ?>

<!-- End of Buy referral -->


<div class="input-group mb-3">
<div class="current-otr m-b-15">
<p style="margin-bottom:0px;">
<span id="buy_total_approx_label"><?= str_replace('[currency]','<span class="buy_currency_label">'.$currency_info['currency'].'</span>',Lang::string('buy-total-approx')) ?></span>
</p>
<h4>
<span id="buy_total_label" style="display:none;"><?= Lang::string('buy-total') ?></span>
<b>
<?php
if (($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50)) {
?>
<?= $currency_info['fa_symbol'] ?><span id="buy_total"><?= Stringz::currency($buy_total1,($currency_info['is_crypto'] == 'Y')) ?></span>

<?php } else { ?>
<span id="buy_total"><?= Stringz::currency($buy_subtotal1,($currency_info['is_crypto'] == 'Y')) ?></span>
<?php } ?>

</b>


</h4>
</div>
</div>
<input type="hidden" name="buy" value="1" />
<input type="hidden" name="uniq" value="<?= end($_SESSION["buysell_uniq"]) ?>" />
<div class="btn-otr">
<span>
<input type="button" name="submit" value="<?= Lang::string('confirm-buy') ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-primary" style="width: auto;display: inline-block;" onclick="javascript:order_limit_buy_confirm();"/>
</span>
<span>
<input id="cancel_transaction" type="button" name="dont" value="Back" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn btn-primary" style="width: auto;display: inline-block;float: right;" onclick="javascript:order_limit_buy_cancel();">
</span>
<p class="m-t-10"> By clicking CONFIRM button an order request will be created.</p>
</div>

</div>
</div>
<?php }?>
</div>
</form>

<?php } ?>

<!-- End of Limit Buy Form -->


<!-- Limit Sell Form -->

<?php if($_REQUEST['limit_sell']==1) { ?>

<form id="confirm_form" action="exchange-order.php?trade=<?= $market ?>&c_currency=<?= $_REQUEST['c_currency'] ?>&currency=<?= $_REQUEST['currency'] ?>" method="POST">
<input name="sell_amount" id="sell_amount" type="hidden" class="form-control" placeholder="Amount" value="<?= $_REQUEST['sell_amount'] ?>">
<input type="hidden" name="confirmed" value="1" />
<input type="hidden" id="buy_all" name="buy_all" value="<?= $buy_all1 ?>" />
<input type="hidden" id="cancel" name="cancel" value="" />

<div class="form-box">

<div class="form-inner1">
<div class="content">
<div class="bskbTZ">
<p style="margin-bottom:0px;"><?= Lang::string('sell-amount') ?></p>
<h4><b><?= Stringz::currency($sell_amount1,true) ?></b></h4>
<input type="hidden" name="sell_amount" value="<?= Stringz::currencyOutput($sell_amount1) ?>" />
</div>
<div class="bskbTZ">
<p style="margin-bottom:0px;"><?= Lang::string('buy-with-currency') ?></p>
<h4><b><?= $currency_info['currency'] ?></b></h4>
<input type="hidden" name="sell_currency" value="<?= $currency1 ?>" />
</div>
<div class="input-group mb-3">
<? if ($sell_limit || $sell_market_price1) { ?>
<div class="bskbTZ">
<p style="margin-bottom:0px;"><?= ($sell_market_price1) ? Lang::string('buy-price') : Lang::string('buy-limit-price') ?></p>
<h4><b><?= Stringz::currency($sell_price1,($currency_info['is_crypto'] == 'Y')) ?></b></h4>
<input type="hidden" name="sell_price" id="sell_price" value="<?= Stringz::currencyOutput($sell_price1) ?>" />
</div>
<?php } ?>
</div>
<div class="input-group mb-3">
<? if ($sell_limit) { ?>
<label class="cont"><?= Lang::string('buy-limit') ?>   <input disabled="disabled" class="checkbox" name="dummy" id="sell_limit" type="checkbox" value="1" <?= ($sell_limit && !$sell_market_price1) ? 'checked="checked"' : '' ?> style="vertical-align: middle;margin-left: 5px;width: 20px;height: 20px;"/>
<input type="hidden" name="sell_limit" value="<?= $sell_limit ?>" />
<?php } ?>
</div>
<hr>
<div class="input-group mb-3">
<table width="100%">
<tr>
<td><p style="margin-bottom:0px"><?= Lang::string('buy-subtotal') ?></p></td>
<td align="right"><h4><b><?= $currency_info['fa_symbol'] ?><?= Stringz::currency($sell_subtotal1,($currency_info['is_crypto'] == 'Y')) ?></b></h4></td>
</tr>
</table>
<?php
if (($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50)) {
?>
<table width="100%">
<tr>
<td><p style="margin-bottom:0px"><?= Lang::string('buy-fee') ?></p></td>
<td align="right"><h4><b><span id="sell_user_fee"><?= Stringz::currency($user_fee_bid) ?></span>%</b></h4></td>
</tr>
</table>
<?php } else { ?>
<table width="100%">
<tr>
<td><p style="margin-bottom:0px"><?= Lang::string('buy-fee') ?></p></td>
<td align="right"><h4><b><span id="sell_user_fee">0.00</span>%</b></h4></td>
</tr>
</table>
<?php } ?>

<!-- Sell referral -->
<?php if($REFERRAL == true && $one_point_values>0 && ($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50)){ ?>
<input type="hidden" name="ref_status_sell" id="ref_status_sell" value="1">
<input type="hidden" name="bonus_amount" id="bonus_amount" value="<? echo $bonus_amount; ?>">
<label class="cont" style="color: brown;font-style:  italic;">
<input 
class="checkbox" 
name="is_referral" 
id="is_referral_sell" 
onclick="calculateBuyPrice_sell()"
type="checkbox" value="1"
<? if($bonous_point == 0){ echo 'disabled'; } ?>
/>
Use your Referral Bonus

<span style="float: right;margin-left: 50px;">    
<? echo $cur_code; ?> <? echo $bonus_amount; ?>
</span>

<span class="checkmark"></span>
</label>
<?php } ?>
<!-- End of Sell referral -->

<table width="100%">
<tr>
<td><p style="margin-bottom:0px">
<span id="sell_total_approx_label"><?= str_replace('[currency]','<span class="sell_currency_label">'.$currency_info['currency'].'</span>',Lang::string('sell-total-approx')) ?></span>
</p></td>
<?php
if (($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50)) {
?>
<td align="right"><h4>
<span id="sell_total_label" style="display:none;"><?= Lang::string('sell-total') ?></span>
<b><?= $currency_info['fa_symbol'] ?><span id="sell_total"><?= Stringz::currency($sell_total1,($currency_info['is_crypto'] == 'Y')) ?></span></b>
</h4></td>
<?php } else { ?>
<td align="right"><h4>
<span id="sell_total_label" style="display:none;"><?= Lang::string('sell-total') ?></span>
<b><?= $currency_info['fa_symbol'] ?><span id="sell_total"><?= Stringz::currency($sell_subtotal1,($currency_info['is_crypto'] == 'Y')) ?></span></b>
</h4></td>
<?php } ?>
</tr>
</table>
</div>
<input type="hidden" name="sell" value="1" />
<input type="hidden" name="uniq" value="<?= end($_SESSION["buysell_uniq"]) ?>" />
<div class="btn-otr">
<span>
<input type="button" name="submit" value="<?= Lang::string('confirm-sale') ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-primary" style="width: auto;display: inline-block;padding: 12px 30px;" onclick="javascript:order_limit_sell_confirm();"/>
</span>
<span>
<input type="button" name="dont" value="Back" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-primary" style="width: auto;display: inline-block;float: right;padding: 12px 30px;" onclick="javascript:order_limit_sell_cancel();">
</span>
</div>

</div>
</div>

</div>
</form>

<?php } ?>

<!-- End of Limit Sell Form -->