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
$stop_buy=0;
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

echo 1;
echo '~';
$stop_buy=1;
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

echo 1;
echo '~';
$stop_buy=1;
}
}
}

if ($sell && !is_array(Errors::$errors)) {
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

<?php 
if($stop_buy==0) {
Errors::display(); 
}
else
{
echo '<div class="success_message">Order Created Successfully</div>';
}
?>

<form id="buy_form" action="exchange-order.php?trade=<?= $market ?>&c_currency=<?= $_REQUEST['c_currency'] ?>&currency=<?= $_REQUEST['currency'] ?>&stopbuy=1" method="POST">

<input type="hidden" id="is_crypto" value="<?= $currency_info['is_crypto'] ?>" />
<?php
if (($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50)) { ?>
<input type="hidden" id="user_fee" value="<?= $user_fee_both['fee'] ?>" />
<input type="hidden" id="user_fee1" value="<?= $user_fee_both['fee1'] ?>" />
<?php } else { ?>
<input type="hidden" id="user_fee" value="0" />
<input type="hidden" id="user_fee1" value="0" />
<?php } ?>
<input type="hidden" id="c_currency" value="<?= $c_currency1 ?>">
<div class="content">

<div class="input-group mb-3">
<input name="buy_stop_price" id="buy_stop_price" type="text" class="form-control" placeholder="Limit">
<div class="input-group-append">
<span class="input-group-text"><?= $currency_info['currency'] ?></span>
</div>
</div>
<div class="input-group mb-3">
<input name="buy_amount11" id="buy_amount11" type="text" class="form-control" placeholder="Amount">
<div class="input-group-append">
<span class="input-group-text"><?= $c_currency_info['currency'] ?></span>
</div>
</div>
<!-- <hr> -->
<div class="input-group mb-3">
<input type="text" class="form-control" placeholder="Total <?= $currency_info['currency'] ?> to spend" id="buy_total1" name="buy_total1" value="">
<div class="input-group-append">
<span class="input-group-text"><?= $currency_info['currency'] ?></span>
</div>
</div>
<input class="checkbox" name="buy_stop" id="buy_stop" type="checkbox" value="1" checked="checked" style="    visibility: collapse;">
<?php
if(User::isLoggedIn()){
include 'paxion_referal.php';
if (($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50)) {
$fee_value = Stringz::currency($user_fee_bid);
}else{
$fee_value = 0;                                                    
}
?>
<br>
<p class="info-link">Fees: <a href="#"><span id="sell_user_fee"><?= $fee_value ?></span>%</a></p><br><br>
<input type="hidden" id="user_fee_stopbuy" value="<?= $fee_value ?>"/>

<input type="hidden" name="buy" value="1" />
<input type="hidden" name="buy_all" id="buy_all" value="<?= $buy_all1 ?>" />
<input type="hidden" name="uniq" value="<?= end($_SESSION["buysell_uniq"]) ?>" />
<input type="button" name="submit" value="Buy" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-light" onclick="javascript:order_stop_buy();"/>
<?php
}else{
?>
<style>
form#buy_form {height: 370px !important;}
div#stop-buy form#buy_form {height: 220px !important;}
form#sell_form {height: 370px !important;}
.form-box p.info-link a {color: rgb(51, 51, 51);}
</style>
<div class="loginMessage">
<a href="/login" class="standard">Sign In</a> or 
<a href="/register" class="standard">Create an Account</a> to  trade.
</div>
<?php
}
?>
</p>
</div>
</form>

<!-- End of Buy stop form -->


<!-- Chart Header -->

<?php 
if($stop_buy==1) 
{ 
echo '~';
?>

<div class="col-md-4 col-sm-6 col-xs-12">
<div class="chart-title">
<h6 class="name">Paxion Exchange</h6>
<p class="code"><?= $c_currency_info['currency'] ?><span class="gray-color">/<?= $currency_info['currency'] ?></span></p>
</div>
</div>
<div class="col-md-4 col-sm-6 col-xs-12">
<div class="chart-hilights">
<div class="row">
<div class="lastPrice">
<div class="name">Last Price</div>
<div class="info"><strong><span class="green-color"><?= number_format($currentPair['lastPrice'], 8) ?></span></strong></div>
</div>
<div class="change">
<div class="name">24hr Change</div>
<div class="info"><strong><span class="red-color"><?= number_format($currentPair['change_24hrs'], 8) ?></span></strong></div>
</div>
<div class="high">
<div class="name">24h Volume</div>
<div class="info"><strong><span class="gray-color"><?= number_format($currentPair['transactions_24hrs'], 8) ?></span> <?= $c_currency_info['currency'] ?></strong></div>
</div>
<div class="low no-border">
<div class="name">24hr Low</div>
<div class="info">0.00008001</div>
</div>
</div>
</div>
</div>

<?php } ?>

<!-- End of Chart Header -->


<!-- Markets Block -->

<?php 
if($stop_buy==1) 
{ 
echo '~';
?>


<div class="content">
<ul class="nav nav-tabs" id="myTab" role="tablist">

<?php
$active_currency_id = $_REQUEST['currency'];
$active_currency = $_REQUEST['c_currency'];
$class_active = "";
?>
<li class="nav-item">
<?php
if($active_currency_id == 28)
$class_active = "active show";
else
$class_active = "";
?>
<a class="nav-link <?php echo $class_active;?>" id="r-btc-tab" data-toggle="tab" href="#r-btc" role="tab" aria-controls="r-btc" aria-selected="false">BTC</a>
</li>
<li class="nav-item">
<?php
if($active_currency_id == 45)
$class_active = "active show";
else
$class_active = "";
?>
<a class="nav-link <?php echo $class_active;?>" id="r-eth-tab" data-toggle="tab" href="#r-eth" role="tab" aria-controls="r-eth" aria-selected="false">ETH</a>
</li>                                            
<li class="nav-item">
<?php
if($active_currency_id == 50)
$class_active = "active show";
else
$class_active = "";
?>
<a class="nav-link <?php echo $class_active;?>" id="r-iox-tab" data-toggle="tab" href="#r-iox" role="tab" aria-controls="r-iox" aria-selected="false">IOX</a>
</li>
<li class="nav-item">
<?php
if($active_currency_id == 51)
$class_active = "active show";
else
$class_active = "";
?>
<a class="nav-link <?php echo $class_active;?>" id="r-usd-tab" data-toggle="tab" href="#r-usd" role="tab" aria-controls="r-btc" aria-selected="false">USDT</a>
</li>
</ul>
<div class="tab-content" id="myTabContent">
<!-- USD Start -->
<?php
if($active_currency_id == 51)
$class_active = "active show";
else
$class_active = "";
?>
<div class="tab-pane fade <?php echo $class_active;?>" id="r-usd" role="tabpanel" aria-labelledby="r-usd-tab">
<table class="table row-border table-hover" cellspacing="0" width="100%">
<thead>
<tr>
<th width="120">Coin</th>
<th>Price</th>
<th>Change</th>
</tr>
</thead>
<tbody>

<tr class="clickable-row <?= ($c_currency_info['currency'] == 'IOX' && $currency_info['currency'] == 'USDT') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=IOX-USDT&c_currency=50&currency=51">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
IOX/USDT
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs__iox_usdt['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs__iox_usdt['change_24hrs'] ?></span></td>
</tr>

<tr class="clickable-row <?= ($c_currency_info['currency'] == 'BTC' && $currency_info['currency'] == 'USDT') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=BTC-USDT&c_currency=28&currency=51">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
BTC/USDT
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_btc_usd['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_btc_usd['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'ETH' && $currency_info['currency'] == 'USDT') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=ETH-USDT&c_currency=45&currency=51">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
ETH/USDT
</div>
</td>
<td><span class="red-color"><?= $transactions_24hrs_eth_usd['lastPrice'] ?></span> </td>
<td><span class="green-color"><?= $transactions_24hrs_eth_usd['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'BCH' && $currency_info['currency'] == 'USDT') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=BCH-USDT&c_currency=44&currency=51">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
BCH/USDT
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_bch_usd['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_bch_usd['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'LTC' && $currency_info['currency'] == 'USDT') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=LTC-USDT&c_currency=42&currency=51">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
LTC/USDT
</div>
</td>
<td><span class="red-color"><?= $transactions_24hrs_ltc_usd['lastPrice'] ?></span> </td>
<td><span class="green-color"><?= $transactions_24hrs_ltc_usd['change_24hrs'] ?></span></td>
</tr>


<tr class="clickable-row <?= ($c_currency_info['currency'] == 'ZEC' && $currency_info['currency'] == 'USDT') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=ZEC-USDT&c_currency=43&currency=51">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
ZEC/USDT
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_zec_usd['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_zec_usd['change_24hrs'] ?></span></td>
</tr>

</tbody>
</table>
</div>

<!-- USD Stop -->
<!-- BTC Start -->
<?php
if($active_currency_id == 28)
$class_active = "active show";
else
$class_active = "";
?>
<div class="tab-pane fade <?php echo $class_active;?>" id="r-btc" role="tabpanel" aria-labelledby="r-btc-tab">
<table class="table row-border table-hover" cellspacing="0" width="100%">
<thead>
<tr>
<th width="120">Coin</th>
<th>Price</th>
<!-- <th>Volume</th> -->
<th>Change</th>
<!-- <th>Name</th> -->
</tr>
</thead>
<tbody>

<tr class="clickable-row <?= ($c_currency_info['currency'] == 'IOX' && $currency_info['currency'] == 'BTC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=IOX-BTC&c_currency=50&currency=28">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
IOX/BTC
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_iox_btc['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_iox_btc['change_24hrs'] ?></span></td>
</tr>

<tr class="clickable-row <?= ($c_currency_info['currency'] == 'ETH' && $currency_info['currency'] == 'BTC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=ETH-BTC&c_currency=45&currency=28">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
ETH/BTC
</div>
</td>
<td><span class="red-color"><?= $transactions_24hrs_eth_btc['lastPrice'] ?></span> </td>
<td><span class="green-color"><?= $transactions_24hrs_eth_btc['change_24hrs'] ?></span></td>
</tr>

<tr class="clickable-row <?= ($c_currency_info['currency'] == 'BCH' && $currency_info['currency'] == 'BTC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=BCH-BTC&c_currency=44&currency=28">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
BCH/BTC
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_bch_btc['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_bch_btc['change_24hrs'] ?></span></td>
</tr>

<tr class="clickable-row <?= ($c_currency_info['currency'] == 'LTC' && $currency_info['currency'] == 'BTC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=LTC-BTC&c_currency=42&currency=28">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
LTC/BTC
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_ltc_btc['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_ltc_btc['change_24hrs'] ?></span></td>
</tr>


<tr class="clickable-row <?= ($c_currency_info['currency'] == 'ZEC' && $currency_info['currency'] == 'BTC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=ZEC-BTC&c_currency=43&currency=28">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
ZEC/BTC
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_zec_btc['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_bch_btc['change_24hrs'] ?></span></td>
</tr>                                                    

</tbody>
</table>
</div>

<!-- BTC Stop -->
<!-- LTC Start -->
<?php
if($active_currency_id == 42)
$class_active = "active show";
else
$class_active = "";
?>
<div class="tab-pane fade <?php echo $class_active;?>" id="r-ltc" role="tabpanel" aria-labelledby="r-ltc-tab">
<table class="table row-border table-hover" cellspacing="0" width="100%">
<thead>
<tr>
<th width="120">Coin</th>
<th>Price</th>
<!-- <th>Volume</th> -->
<th>Change</th>
<!-- <th>Name</th> -->
</tr>
</thead>
<tbody>

<tr class="clickable-row <?= ($c_currency_info['currency'] == 'BTC' && $currency_info['currency'] == 'LTC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=BTC-LTC&c_currency=28&currency=42">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
BTC/LTC
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_btc_ltc['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_btc_ltc['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'ETH' && $currency_info['currency'] == 'LTC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=ETH-LTC&c_currency=45&currency=42">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
ETH/LTC
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_eth_ltc['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_eth_ltc['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'ZEC' && $currency_info['currency'] == 'LTC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=ZEC-LTC&c_currency=43&currency=42">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
ZEC/LTC
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_zec_ltc['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_zec_ltc['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'BCH' && $currency_info['currency'] == 'LTC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=BCH-LTC&c_currency=44&currency=42">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
BCH/LTC
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_bch_ltc['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_bch_ltc['change_24hrs'] ?></span></td>
</tr>

<tr class="clickable-row <?= ($c_currency_info['currency'] == 'IOX' && $currency_info['currency'] == 'LTC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=IOX-LTC&c_currency=50&currency=42">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
IOX/LTC
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_iox_ltc['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_iox_ltc['change_24hrs'] ?></span></td>
</tr>
</tbody>
</table>
</div>

<!-- LTC Stop -->
<!-- BCH Start -->
<?php
if($active_currency_id == 44)
$class_active = "active show";
else
$class_active = "";
?>
<div class="tab-pane fade <?php echo $class_active;?>" id="r-bch" role="tabpanel" aria-labelledby="r-bch-tab">
<table class="table row-border table-hover" cellspacing="0" width="100%">
<thead>
<tr>
<th width="120">Coin</th>
<th>Price</th>
<!-- <th>Volume</th> -->
<th>Change</th>
<!-- <th>Name</th> -->
</tr>
</thead>
<tbody>

<tr class="clickable-row <?= ($c_currency_info['currency'] == 'BTC' && $currency_info['currency'] == 'BCH') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=BTH-BCH&c_currency=28&currency=44">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
BTC/BCH
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_btc_bch['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_btc_bch['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'ETH' && $currency_info['currency'] == 'BCH') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=ETH-BCH&c_currency=45&currency=44">
<td>
<div class="star-inner text-left" style="font-size:9px">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
ETH/BCH
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_eth_bch['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_eth_bch['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'ZEC' && $currency_info['currency'] == 'BCH') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=ZEC-BCH&c_currency=43&currency=44">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
ZEC/BCH
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_zec_bch['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_zec_bch['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'LTC' && $currency_info['currency'] == 'BCH') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=LTC-BCH&c_currency=42&currency=44">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
LTC/BCH
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_ltc_bch['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_ltc_bch['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'IOX' && $currency_info['currency'] == 'BCH') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=IOX-BCH&c_currency=50&currency=44">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
IOX/BCH
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_iox_bch['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_iox_bch['change_24hrs'] ?></span></td>
</tr>

</tbody>
</table>
</div>

<!-- BCH Stop -->
<!-- ZEC Start -->
<?php
if($active_currency_id == 43)
$class_active = "active show";
else
$class_active = "";
?>
<div class="tab-pane fade <?php echo $class_active;?>" id="r-zec" role="tabpanel" aria-labelledby="r-zec-tab">
<table class="table row-border table-hover" cellspacing="0" width="100%">
<thead>
<tr>
<th width="120">Coin</th>
<th>Price</th>
<!-- <th>Volume</th> -->
<th>Change</th>
<!-- <th>Name</th> -->
</tr>
</thead>
<tbody>

<tr class="clickable-row <?= ($c_currency_info['currency'] == 'BTC' && $currency_info['currency'] == 'ZEC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=BTC-ZEC&c_currency=28&currency=43">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
BTC/ZEC
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_btc_zec['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_btc_zec['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'LTC' && $currency_info['currency'] == 'ZEC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=LTC-ZEC&c_currency=42&currency=43">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
LTC/ZEC
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_ltc_zec['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_ltc_zec['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'ETH' && $currency_info['currency'] == 'ZEC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=ETH-ZEC&c_currency=45&currency=43">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
ETH/ZEC
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_eth_zec['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_eth_zec['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'BCH' && $currency_info['currency'] == 'ZEC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=BCH-ZEC&c_currency=44&currency=43">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
BCH/ZEC
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_bch_zec['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_bch_zec['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'IOX' && $currency_info['currency'] == 'ZEC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=IOX-ZEC&c_currency=50&currency=43">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
IOX/ZEC
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_iox_zec['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_iox_zec['change_24hrs'] ?></span></td>
</tr>

</tbody>
</table>
</div>

<!-- ZEC Stop -->
<!-- ETH Start -->
<?php
if($active_currency_id == 45)
$class_active = "active show";
else
$class_active = "";
?>
<div class="tab-pane fade <?php echo $class_active;?>" id="r-eth" role="tabpanel" aria-labelledby="r-eth-tab">
<table class="table row-border table-hover" cellspacing="0" width="100%">
<thead>
<tr>
<th width="120">Coin</th>
<th>Price</th>
<!-- <th>Volume</th> -->
<th>Change</th>
<!-- <th>Name</th> -->
</tr>
</thead>
<tbody>


<tr class="clickable-row <?= ($c_currency_info['currency'] == 'IOX' && $currency_info['currency'] == 'ETH') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=IOX-ETH&c_currency=50&currency=45">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
IOX/ETH
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_iox_eth['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_iox_eth['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row" data-href="exchange?trade=BCH-ETH&c_currency=44&currency=45">
<td>
<div class="star-inner text-left" style="font-size:9px">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
BCH/ETH
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_bch_eth['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_bch_eth['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'LTC' && $currency_info['currency'] == 'ETH') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=LTC-ETH&c_currency=42&currency=45">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
LTC/ETH
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_ltc_eth['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_ltc_eth['change_24hrs'] ?></span></td>
</tr>

<tr class="clickable-row <?= ($c_currency_info['currency'] == 'ZEC' && $currency_info['currency'] == 'ETH') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=ZEC-ETH&c_currency=43&currency=45">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
ZEC/ETH
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_zec_eth['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_zec_eth['change_24hrs'] ?></span></td>
</tr>                                                    

</tbody>
</table>
</div>

<!-- ETH Stop -->
<!-- IOX Start -->
<?php
if($active_currency_id == 50)
$class_active = "active show";
else
$class_active = "";
?>
<div class="tab-pane fade <?php echo $class_active;?>" id="r-iox" role="tabpanel" aria-labelledby="r-iox-tab">
<table class="table row-border table-hover" cellspacing="0" width="100%">
<thead>
<tr>
<th width="120">Coin</th>
<th>Price</th>
<!-- <th>Volume</th> -->
<th>Change</th>
<!-- <th>Name</th> -->
</tr>
</thead>
<tbody>

<tr class="clickable-row" data-href="exchange?trade=BCH-IOX&c_currency=44&currency=50">
<td>
<div class="star-inner text-left" style="font-size:9px">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
BCH/IOX
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_bch_iox['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_bch_iox['change_24hrs'] ?></span></td>
</tr>
<tr class="clickable-row <?= ($c_currency_info['currency'] == 'LTC' && $currency_info['currency'] == 'IOX') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=LTC-IOX&c_currency=42&currency=50">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
LTC/IOX
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_ltc_iox['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_ltc_iox['change_24hrs'] ?></span></td>
</tr>

<tr class="clickable-row <?= ($c_currency_info['currency'] == 'ZEC' && $currency_info['currency'] == 'IOX') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=ZEC-IOX&c_currency=43&currency=50">
<td>
<div class="star-inner text-left">
<input id="star1" type="checkbox" name="time" />
<label for="star1"></label>
ZEC/IOX
</div>
</td>
<td><span class="green-color"><?= $transactions_24hrs_zec_iox['lastPrice'] ?></span> </td>
<td><span class="red-color"><?= $transactions_24hrs_zec_iox['change_24hrs'] ?></span></td>
</tr>
</tbody>
</table>
</div>

<!-- IOX Stop -->
</div>
</div>


<?php } ?>

<!-- End of Markets Block -->


<!-- Balance Block -->

<?php 
if($stop_buy==1) 
{ 
echo '~';

API::add('FeeSchedule','getRecord',array(User::$info['fee_schedule']));
API::add('User','getAvailable');
$feequery = API::send();
$user_fee_both = $feequery['FeeSchedule']['getRecord']['results'][0];
$user_available = $feequery['User']['getAvailable']['results'][0];

?>

<!-- buy user available -->
<?= ((!empty($user_available[strtoupper($currency_info['currency'])])) ? Stringz::currency($user_available[strtoupper($currency_info['currency'])],($currency_info['is_crypto'] == 'Y')) : '0.00') ?>

<?php echo '~'; ?>

<!-- sell user available -->
<?= Stringz::currency($user_available[strtoupper($c_currency_info['currency'])],true) ?>

<?php } ?>

<!-- End of Balance Block -->

<!-- buy_orders_table -->

<?php 

$currencies = Settings::sessionCurrency();
$c_currency1 = $_REQUEST['c_currency'] ? : 28;
$currency1 = $_REQUEST['currency'] ? : 27;
$currency_info = $CFG->currencies[$currency1];
$c_currency_info = $CFG->currencies[$c_currency1];

API::add('Orders', 'get', array(false, false, false, $c_currency1, $currency1, false, false, 1));
API::add('Orders', 'get', array(false, false, false, $c_currency1, $currency1, false, false, false, false, 1));
API::add('Transactions', 'get', array(false, false, 1, $c_currency1, $currency1));
$query = API::send();

$bids = $query['Orders']['get']['results'][0];
$asks = $query['Orders']['get']['results'][1];
$last_transaction = $query['Transactions']['get']['results'][0][0];
$last_trans_currency = ($last_transaction['currency'] == $currency_info['id']) ? false : (($last_transaction['currency1'] == $currency_info['id']) ? false : ' (' . $CFG->currencies[$last_transaction['currency1']]['currency'] . ')');
$last_trans_symbol = $currency_info['fa_symbol'];
$last_trans_color = ($last_transaction['maker_type'] == 'sell') ? 'price-green' : 'price-red';

if ((!empty($_REQUEST['c_currency']) && array_key_exists(strtoupper($_REQUEST['c_currency']), $CFG->currencies)))
$_SESSION['oo_c_currency'] = preg_replace("/[^0-9]/", "", $_REQUEST['c_currency']);
else if (empty($_SESSION['oo_c_currency']) || $_REQUEST['c_currency'] == 'All')
$_SESSION['oo_c_currency'] = false;

if ((!empty($_REQUEST['currency']) && array_key_exists(strtoupper($_REQUEST['currency']), $CFG->currencies)))
$_SESSION['oo_currency'] = preg_replace("/[^0-9]/", "", $_REQUEST['currency']);
else if (empty($_SESSION['oo_currency']) || $_REQUEST['currency'] == 'All')
$_SESSION['oo_currency'] = false;

if ((!empty($_REQUEST['order_by'])))
$_SESSION['oo_order_by'] = preg_replace("/[^a-z]/", "", $_REQUEST['order_by']);
else if (empty($_SESSION['oo_order_by']))
$_SESSION['oo_order_by'] = false;

$open_currency1 = $_SESSION['oo_currency'];
$open_c_currency1 = $_SESSION['oo_c_currency'];
$order_by1 = $_SESSION['oo_order_by'];
$trans_realized1 = (!empty($_REQUEST['transactions'])) ? preg_replace("/[^0-9]/", "", $_REQUEST['transactions']) : false;
$id1 = (!empty($_REQUEST['id'])) ? preg_replace("/[^0-9]/", "", $_REQUEST['id']) : false;
$bypass = (!empty($_REQUEST['bypass']));

API::add('Orders', 'get', array(false, false, false, $c_currency1, $currency1, 1, false, 1, $order_by1, false, 1));
API::add('Orders', 'get', array(false, false, false, $c_currency1, $currency1, 1, false, false, $order_by1, 1, 1));
$query = API::send();

$open_bids = $query['Orders']['get']['results'][0];
$open_asks = $query['Orders']['get']['results'][1];
$open_currency_info = ($open_currency1) ? $CFG->currencies[strtoupper($open_currency1)] : false;

if (!empty($_REQUEST['new_order']) && !$trans_realized1)
Messages::add(Lang::string('transactions-orders-new-message'));
if (!empty($_REQUEST['edit_order']) && !$trans_realized1)
Messages::add(Lang::string('transactions-orders-edit-message'));
elseif (!empty($_REQUEST['new_order']) && $trans_realized1 > 0)
Messages::add(str_replace('[transactions]', $trans_realized1, Lang::string('transactions-orders-done-message')));
elseif (!empty($_REQUEST['edit_order']) && $trans_realized1 > 0)
Messages::add(str_replace('[transactions]', $trans_realized1, Lang::string('transactions-orders-done-edit-message')));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'order-doesnt-exist')
Errors::add(Lang::string('orders-order-doesnt-exist'));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'not-your-order')
Errors::add(Lang::string('orders-not-yours'));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'order-cancelled')
Messages::add(Lang::string('orders-order-cancelled'));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'deleteall-error')
Errors::add(Lang::string('orders-order-cancelled-error'));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'deleteall-success')
Messages::add(Lang::string('orders-order-cancelled-all'));

$_SESSION["openorders_uniq"] = md5(uniqid(mt_rand(), true));

API::add('Transactions', 'get', array(1, $page1, 30, $c_currency1, $currency1, 1, $start_date1, $type1, $order_by1));
$query = API::send();
$total = $query['Transactions']['get']['results'][0];

API::add('Transactions', 'get', array(false, $page1, 30, $c_currency1, $currency1, 1, $start_date1, $type1, $order_by1));
API::add('Transactions', 'getTypes');
$query = API::send();

$transactions = $query['Transactions']['get']['results'][0];
$transaction_types = $query['Transactions']['getTypes']['results'][0];

$currency_info = ($currency1) ? $CFG->currencies[strtoupper($currency1)] : array();


if($stop_buy==1) 
{ 
echo '~';
?>

<table class="table table-striped order-table-fixed right-data-table buy_orders_table">
<thead>
<tr>
<th>Type</th>
<th><?= Lang::string('orders-price') ?></th>
<th><?= Lang::string('orders-amount') ?></th>
<th><?= Lang::string('orders-value') ?></th>
</tr>
</thead>
<tbody>
<?
if ($asks) {
$i = 0;
foreach ($asks as $ask) {

$min_ask = (empty($min_ask) || $ask['btc_price'] < $min_ask) ? $ask['btc_price'] : $min_ask;
$max_ask = (empty($max_ask) || $ask['btc_price'] > $max_ask) ? $ask['btc_price'] : $max_ask;
$mine = (!empty(User::$info['user']) && $ask['user_id'] == User::$info['user'] && $ask['btc_price'] == $ask['fiat_price']) ? '<a class="fa fa-user" href="open-orders.php?id=' . $ask['id'] . '" title="' . Lang::string('home-your-order') . '"></a>' : '';

if ($ask['market_price'] == 'N'  && $ask['stop_price'] > 0) {
$type = '<div class="identify stop_order" style="background-color:#DB82FF;text-align:center;color:white;">S</div>';
} elseif ($ask['market_price'] == 'Y') {
$type = '<div class="identify market_order" style="background-color:#EFE62F;text-align:center;color:white;">M</div>';
}else {
$type = '<div class="identify limit_order" style="background-color:#FF8282;text-align:center;color:white;">L</div>';
}


echo '
<tr id="ask_' . $ask['id'] . '" class="ask_tr">
<td>'.$type.'</td>
<td>' . $mine . $currency_info['fa_symbol'] . '<span class="order_price">' . Stringz::currency1($ask['btc_price']) . '</span> ' . (($ask['btc_price'] != $ask['fiat_price']) ? '<a title="' . str_replace('[currency]', $CFG->currencies[$ask['currency']]['currency'], Lang::string('orders-converted-from')) . '" class="fa fa-exchange" href="" onclick="return false;"></a>' : '') . '</td>
<td><span class="order_amount">' . Stringz::currency1($ask['btc'], true) . '</span> ' . $c_currency_info['currency'] . '</td>
<td>' . $currency_info['fa_symbol'] . '<span class="order_value">' . Stringz::currency1(($ask['btc_price'] * $ask['btc'])) . '</span></td>
</tr>';
$i++;
}
}else{

}

?>
</tbody>
</table>


<?php } ?>

<!-- End of buy_orders_table -->

<!-- sell_orders_table -->

<?php 
if($stop_buy==1) 
{ 
echo '~';
?>

<table class="table table-striped order-table-fixed right-data-table sell_orders_table">
<thead>
<tr>
<th>Type</th>
<th><?= Lang::string('orders-price') ?></th>
<th><?= Lang::string('orders-amount') ?></th>
<th><?= Lang::string('orders-value') ?></th>
</tr>
</thead>
<tbody>
<?php
if ($bids) {
$i = 0;
foreach ($bids as $bid) {


$min_bid = (empty($min_bid) || $bid['btc_price'] < $min_bid) ? $bid['btc_price'] : $min_bid;
$max_bid = (empty($max_bid) || $bid['btc_price'] > $max_bid) ? $bid['btc_price'] : $max_bid;
$mine = (!empty(User::$info['user']) && $bid['user_id'] == User::$info['user'] && $bid['btc_price'] == $bid['fiat_price']) ? '<a class="fa fa-user" href="open-orders.php?id=' . $bid['id'] . '" title="' . Lang::string('home-your-order') . '"></a>' : '';
if ($bid['market_price'] == 'N' && $bid['stop_price'] > 0) {
$type = '<div class="identify stop_order" style="background-color:#DB82FF;text-align:center;color:white;">S</div>';
} elseif ($bid['market_price'] == 'Y') {
$type = '<div class="identify market_order" style="background-color:#EFE62F;text-align:center;color:white;">M</div>';
}else {
$type = '<div class="identify limit_order" style="background-color:#FF8282;text-align:center;color:white;">L</div>';
}

echo '
<tr id="bid_' . $bid['id'] . '" class="bid_tr">
<td>'.$type.'</td>
<td>' . $mine .  $currency_info['fa_symbol'] . '<span class="order_price">' . Stringz::currency1($bid['btc_price']) . '</span> ' . (($bid['btc_price'] != $bid['fiat_price']) ? '<a title="' . str_replace('[currency]', $CFG->currencies[$bid['currency']]['currency'], Lang::string('orders-converted-from')) . '" class="fa fa-exchange" href="" onclick="return false;"></a>' : '') . '</td>
<td><span class="order_amount">' . Stringz::currency1($bid['btc'], true) . '</span> ' . $c_currency_info['currency'] . '</td>
<td>' . $currency_info['fa_symbol'] . '<span class="order_value">' . Stringz::currency1(($bid['btc_price'] * $bid['btc'])) . '</span></td>
</tr>';
$i++;
}
}else{

}

?>
</tbody>
</table>


<?php } ?>

<!-- End of sell_orders_table -->


<!-- buy_open_orders_table -->

<?php 

$c_currencyy1 = $_REQUEST['c_currency'];
$currencyy1 = $_REQUEST['currency'];

if ((!empty($_REQUEST['c_currency']) && array_key_exists(strtoupper($_REQUEST['c_currency']),$CFG->currencies)))
$_SESSION['oo_c_currency'] = preg_replace("/[^0-9]/", "",$_REQUEST['c_currency']);
else if (empty($_SESSION['oo_c_currency']) || $_REQUEST['c_currency'] == 'All')
$_SESSION['oo_c_currency'] = false;

if ((!empty($_REQUEST['currency']) && array_key_exists(strtoupper($_REQUEST['currency']),$CFG->currencies)))
$_SESSION['oo_currency'] = preg_replace("/[^0-9]/", "",$_REQUEST['currency']);
else if (empty($_SESSION['oo_currency']) || $_REQUEST['currency'] == 'All')
$_SESSION['oo_currency'] = false;

if ((!empty($_REQUEST['order_by'])))
$_SESSION['oo_order_by'] = preg_replace("/[^a-z]/", "",$_REQUEST['order_by']);
else if (empty($_SESSION['oo_order_by']))
$_SESSION['oo_order_by'] = false;

$c_currency1 = $_REQUEST['c_currency'] ? : 28;
$currency1 = $_REQUEST['currency'] ? : 27;
$order_by1 = $_SESSION['oo_order_by'];
$trans_realized1 = (!empty($_REQUEST['transactions'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['transactions']) : false;
$id1 = (!empty($_REQUEST['id'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['id']) : false;
$bypass = (!empty($_REQUEST['bypass']));

API::add('Orders','get',array(false,false,false,$c_currency1,$currency1,1,false,1,$order_by1,false,1));
API::add('Orders','get',array(false,false,false,$c_currency1,$currency1,1,false,false,$order_by1,1,1));
$query = API::send();

$bids = $query['Orders']['get']['results'][0];
$asks = $query['Orders']['get']['results'][1];
$currency_info = ($currency1) ? $CFG->currencies[strtoupper($currency1)] : false;

if (!empty($_REQUEST['new_order']) && !$trans_realized1)
Messages::add(Lang::string('transactions-orders-new-message'));
if (!empty($_REQUEST['edit_order']) && !$trans_realized1)
Messages::add(Lang::string('transactions-orders-edit-message'));
elseif (!empty($_REQUEST['new_order']) && $trans_realized1 > 0)
Messages::add(str_replace('[transactions]',$trans_realized1,Lang::string('transactions-orders-done-message')));
elseif (!empty($_REQUEST['edit_order']) && $trans_realized1 > 0)
Messages::add(str_replace('[transactions]',$trans_realized1,Lang::string('transactions-orders-done-edit-message')));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'order-doesnt-exist')
Errors::add(Lang::string('orders-order-doesnt-exist'));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'not-your-order')
Errors::add(Lang::string('orders-not-yours'));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'order-cancelled')
Messages::add(Lang::string('orders-order-cancelled'));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'deleteall-error')
Errors::add(Lang::string('orders-order-cancelled-error'));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'deleteall-success')
Messages::add(Lang::string('orders-order-cancelled-all'));

$_SESSION["openorders_uniq"] = md5(uniqid(mt_rand(),true));

if($stop_buy==1) 
{ 
echo '~';
?>


<table class="table table-striped order-table-fixed right-data-table sellopenorderstable">
<thead>
<tr>
<th>Type</th>
<th><?= Lang::string('orders-price') ?></th>
<th><?= Lang::string('orders-amount') ?></th>
<th><?= Lang::string('orders-value') ?></th>
<th>Action</th>
</tr>
</thead>
<tbody>
<? 
if ($bids) {
foreach ($bids as $bid) {
$blink = ($bid['id'] == $id1) ? 'blink' : '';
$double = 0;
if ($bid['market_price'] == 'Y')
$type = '<div class="identify market_order" style="background-color:#EFE62F;text-align:center;color:white;">M</div>';
elseif ($bid['fiat_price'] > 0 && !($bid['stop_price'] > 0))
$type = '<div class="identify limit_order" style="background-color:#FF8282;text-align:center;color:white;">L</div>';
elseif ($bid['stop_price'] > 0 && !($bid['fiat_price'] > 0))
$type = '<div class="identify stop_order" style="background-color:#DB82FF;text-align:center;color:white;">S</div>';
elseif ($bid['stop_price'] > 0 && $bid['fiat_price'] > 0) {
$type = '<div class="identify limit_order" style="background-color:#FF8282;text-align:center;color:white;">L</div>';
$double = 1;
}

echo '
<tr id="bid_'.$bid['id'].'" class="bid_tr '.$blink.'">
<input type="hidden" class="usd_price" value="'.Stringz::currency(((empty($bid['usd_price'])) ? $bid['usd_price'] : $bid['btc_price']),($CFG->currencies[$bid['currency']]['is_crypto'] == 'Y')).'" />
<input type="hidden" class="order_date" value="'.$bid['date'].'" />
<input type="hidden" class="is_crypto" value="'.$bid['is_crypto'].'" />
<td>'.$type.'</td>
<td><span class="currency_char">'.$CFG->currencies[$bid['currency']]['fa_symbol'].'</span><span class="order_price">'.Stringz::currency(($bid['fiat_price'] > 0) ? $bid['fiat_price'] : $bid['stop_price'],($CFG->currencies[$bid['currency']]['is_crypto'] == 'Y')).'</span></td>
<td><span class="order_amount">'.Stringz::currency($bid['btc'],true).'</span> '.$CFG->currencies[$bid['c_currency']]['currency'].'</td>
<td><span class="currency_char">'.$CFG->currencies[$bid['currency']]['fa_symbol'].'</span><span class="order_value">'.Stringz::currency($bid['btc'] * (($bid['fiat_price'] > 0) ? $bid['fiat_price'] : $bid['stop_price']),($CFG->currencies[$bid['currency']]['is_crypto'] == 'Y')).'</span></td>
<td>

<a href="openorders.php?delete_id='.$bid['id'].'&uniq='.$_SESSION["openorders_uniq"].'&c_currency='.$c_currencyy1.'&currency='.$currencyy1.'" title="'.Lang::string('orders-delete').'"><i class="fa fa-times"></i></a></td>
</tr>';
if ($double) {
echo '
<tr id="bid_'.$bid['id'].'" class="bid_tr double">
<input type="hidden" class="is_crypto" value="'.$bid['is_crypto'].'" />
<td><div class="identify stop_order">S</div></td>
<td><span class="currency_char">'.$CFG->currencies[$bid['currency']]['fa_symbol'].'</span><span class="order_price">'.Stringz::currency($bid['stop_price'],($CFG->currencies[$bid['currency']]['is_crypto'] == 'Y')).'</span></td>
<td><span class="order_amount">'.Stringz::currency($bid['btc'],true).'</span> '.$CFG->currencies[$bid['c_currency']]['currency'].'</td>
<td><span class="currency_char">'.$CFG->currencies[$bid['currency']]['fa_symbol'].'</span><span class="order_value">'.Stringz::currency($bid['btc']*$bid['stop_price'],($CFG->currencies[$bid['currency']]['is_crypto'] == 'Y')).'</span></td>
<td><span class="oco"><i class="fa fa-arrow-up"></i> OCO</span></td>
</tr>';
}
}
}else{

}

?>
</tbody>
</table>


<?php } ?>

<!-- End of buy_open_orders_table -->

<!-- sell_open_orders_table -->

<?php
if($stop_buy==1) 
{ 
echo '~';
?>

<table class="table table-striped order-table-fixed right-data-table buy_open_orders_table">
<thead>
<tr>
<th>Type</th>
<th><?= Lang::string('orders-price') ?></th>
<th><?= Lang::string('orders-amount') ?></th>
<th><?= Lang::string('orders-value') ?></th>
<th>Action</th>
</tr>
</thead>
<tbody>
<? 
if ($asks) {
foreach ($asks as $ask) {
$blink = ($ask['id'] == $id1) ? 'blink' : '';
$double = 0;
if ($ask['market_price'] == 'Y')
$type = '<div class="identify market_order" style="background-color:#EFE62F;text-align:center;color:white;">M</div>';
elseif ($ask['fiat_price'] > 0 && !($ask['stop_price'] > 0))
$type = '<div class="identify limit_order" style="background-color:#FF8282;text-align:center;color:white;">L</div>';
elseif ($ask['stop_price'] > 0 && !($ask['fiat_price'] > 0))
$type = '<div class="identify stop_order" style="background-color:#DB82FF;text-align:center;color:white;">S</div>';
elseif ($ask['stop_price'] > 0 && $ask['fiat_price'] > 0) {
$type = '<div class="identify limit_order" style="background-color:#FF8282;text-align:center;color:white;">L</div>';
$double = 1;
}

echo '
<tr id="ask_'.$ask['id'].'" class="ask_tr '.$blink.'">
<input type="hidden" class="usd_price" value="'.Stringz::currency(((empty($ask['usd_price'])) ? $ask['usd_price'] : $ask['btc_price']),($CFG->currencies[$ask['currency']]['is_crypto'] == 'Y')).'" />
<input type="hidden" class="order_date" value="'.$ask['date'].'" />
<input type="hidden" class="is_crypto" value="'.$ask['is_crypto'].'" />
<td>'.$type.'</td>
<td><span class="currency_char">'.$CFG->currencies[$ask['currency']]['fa_symbol'].'</span><span class="order_price">'.Stringz::currency(($ask['fiat_price'] > 0) ? $ask['fiat_price'] : $ask['stop_price'],($CFG->currencies[$ask['currency']]['is_crypto'] == 'Y')).'</span></td>
<td><span class="order_amount">'.Stringz::currency($ask['btc'],true).'</span> '.$CFG->currencies[$ask['c_currency']]['currency'].'</td>
<td><span class="currency_char">'.$CFG->currencies[$ask['currency']]['fa_symbol'].'</span><span class="order_value">'.Stringz::currency($ask['btc'] * (($ask['fiat_price'] > 0) ? $ask['fiat_price'] : $ask['stop_price']),($CFG->currencies[$ask['currency']]['is_crypto'] == 'Y')).'</span></td>
<td> 
<a href="openorders.php?delete_id='.$ask['id'].'&uniq='.$_SESSION["openorders_uniq"].'&c_currency='.$c_currencyy1.'&currency='.$currencyy1.'" title="'.Lang::string('orders-delete').'"><i class="fa fa-times"></i></a></td>
</tr>';
if ($double) {
echo '
<tr id="ask_'.$ask['id'].'" class="ask_tr double">
<input type="hidden" class="is_crypto" value="'.$ask['is_crypto'].'" />
<td><div class="identify stop_order">S</div></td>
<td><span class="currency_char">'.$CFG->currencies[$ask['currency']]['fa_symbol'].'</span><span class="order_price">'.Stringz::currency($ask['stop_price'],($CFG->currencies[$ask['currency']]['is_crypto'] == 'Y')).'</span></td>
<td><span class="order_amount">'.Stringz::currency($ask['btc'],true).'</span> '.$CFG->currencies[$ask['c_currency']]['currency'].'</td>
<td><span class="currency_char">'.$CFG->currencies[$ask['currency']]['fa_symbol'].'</span><span class="order_value">'.Stringz::currency($ask['stop_price']*$ask['btc'],($CFG->currencies[$ask['currency']]['is_crypto'] == 'Y')).'</span></td>
<td><span class="oco"><i class="fa fa-arrow-up"></i> OCO</span></td>
</tr>';
}
}
}else{

}

?>
</tbody>
</table>

<?php } ?>

<!-- End of sell_open_orders_table -->

<!-- trade_history_table -->

<?php 

$currencies = Settings::sessionCurrency();
$c_currency1 = $_REQUEST['c_currency'] ? : 28;
$currency1 = $_REQUEST['currency'] ? : 27;
$currency_info = $CFG->currencies[$currency1];
$c_currency_info = $CFG->currencies[$c_currency1];

API::add('Orders', 'get', array(false, false, false, $c_currency1, $currency1, false, false, 1));
API::add('Orders', 'get', array(false, false, false, $c_currency1, $currency1, false, false, false, false, 1));
API::add('Transactions', 'get', array(false, false, 1, $c_currency1, $currency1));
$query = API::send();

$bids = $query['Orders']['get']['results'][0];
$asks = $query['Orders']['get']['results'][1];
$last_transaction = $query['Transactions']['get']['results'][0][0];
$last_trans_currency = ($last_transaction['currency'] == $currency_info['id']) ? false : (($last_transaction['currency1'] == $currency_info['id']) ? false : ' (' . $CFG->currencies[$last_transaction['currency1']]['currency'] . ')');
$last_trans_symbol = $currency_info['fa_symbol'];
$last_trans_color = ($last_transaction['maker_type'] == 'sell') ? 'price-green' : 'price-red';

if ((!empty($_REQUEST['c_currency']) && array_key_exists(strtoupper($_REQUEST['c_currency']), $CFG->currencies)))
$_SESSION['oo_c_currency'] = preg_replace("/[^0-9]/", "", $_REQUEST['c_currency']);
else if (empty($_SESSION['oo_c_currency']) || $_REQUEST['c_currency'] == 'All')
$_SESSION['oo_c_currency'] = false;

if ((!empty($_REQUEST['currency']) && array_key_exists(strtoupper($_REQUEST['currency']), $CFG->currencies)))
$_SESSION['oo_currency'] = preg_replace("/[^0-9]/", "", $_REQUEST['currency']);
else if (empty($_SESSION['oo_currency']) || $_REQUEST['currency'] == 'All')
$_SESSION['oo_currency'] = false;

if ((!empty($_REQUEST['order_by'])))
$_SESSION['oo_order_by'] = preg_replace("/[^a-z]/", "", $_REQUEST['order_by']);
else if (empty($_SESSION['oo_order_by']))
$_SESSION['oo_order_by'] = false;

$open_currency1 = $_SESSION['oo_currency'];
$open_c_currency1 = $_SESSION['oo_c_currency'];
$order_by1 = $_SESSION['oo_order_by'];
$trans_realized1 = (!empty($_REQUEST['transactions'])) ? preg_replace("/[^0-9]/", "", $_REQUEST['transactions']) : false;
$id1 = (!empty($_REQUEST['id'])) ? preg_replace("/[^0-9]/", "", $_REQUEST['id']) : false;
$bypass = (!empty($_REQUEST['bypass']));

API::add('Orders', 'get', array(false, false, false, $c_currency1, $currency1, 1, false, 1, $order_by1, false, 1));
API::add('Orders', 'get', array(false, false, false, $c_currency1, $currency1, 1, false, false, $order_by1, 1, 1));
$query = API::send();

$open_bids = $query['Orders']['get']['results'][0];
$open_asks = $query['Orders']['get']['results'][1];
$open_currency_info = ($open_currency1) ? $CFG->currencies[strtoupper($open_currency1)] : false;

if (!empty($_REQUEST['new_order']) && !$trans_realized1)
Messages::add(Lang::string('transactions-orders-new-message'));
if (!empty($_REQUEST['edit_order']) && !$trans_realized1)
Messages::add(Lang::string('transactions-orders-edit-message'));
elseif (!empty($_REQUEST['new_order']) && $trans_realized1 > 0)
Messages::add(str_replace('[transactions]', $trans_realized1, Lang::string('transactions-orders-done-message')));
elseif (!empty($_REQUEST['edit_order']) && $trans_realized1 > 0)
Messages::add(str_replace('[transactions]', $trans_realized1, Lang::string('transactions-orders-done-edit-message')));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'order-doesnt-exist')
Errors::add(Lang::string('orders-order-doesnt-exist'));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'not-your-order')
Errors::add(Lang::string('orders-not-yours'));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'order-cancelled')
Messages::add(Lang::string('orders-order-cancelled'));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'deleteall-error')
Errors::add(Lang::string('orders-order-cancelled-error'));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'deleteall-success')
Messages::add(Lang::string('orders-order-cancelled-all'));

$_SESSION["openorders_uniq"] = md5(uniqid(mt_rand(), true));

//transaction

API::add('Transactions', 'get', array(1, $page1, 30, $c_currency1, $currency1, 1, $start_date1, $type1, $order_by1));
$query = API::send();
$total = $query['Transactions']['get']['results'][0];

API::add('Transactions', 'get', array(false, $page1, 30, $c_currency1, $currency1, 1, $start_date1, $type1, $order_by1));
API::add('Transactions', 'getTypes');
$query = API::send();

$transactions = $query['Transactions']['get']['results'][0];
$transaction_types = $query['Transactions']['getTypes']['results'][0];
// $pagination = Content::pagination('transactions.php', $page1, $total, 30, 5, false);

$currency_info = ($currency1) ? $CFG->currencies[strtoupper($currency1)] : array();

if ($trans_realized1 > 0)
Messages::add(str_replace('[transactions]', $trans_realized1, Lang::string('transactions-done-message')));

if($stop_buy==1) 
{ 
echo '~';
?>

<input type="hidden" id="refresh_transactions" value="1" />
<input type="hidden" id="page" value="<?= $page1 ?>" />
<table class="table table-striped order-table-fixed right-data-table trade_history_table">
<thead>
<tr>
<th><?= Lang::string('transactions-type') ?></th>
<th><?= Lang::string('transactions-time') ?></th>
<th><?= Lang::string('orders-amount') ?></th>
<th><?= Lang::string('transactions-fiat') ?></th>
<th><?= Lang::string('orders-price') ?></th>
<th><?= Lang::string('transactions-fee') ?></th>
</tr>
</thead>
<tbody>
<?
if ($transactions) {
foreach ($transactions as $transaction) {
$trans_symbol = $CFG->currencies[$transaction['currency']]['fa_symbol'];
echo '
<tr id="transaction_' . $transaction['id'] . '">
<input type="hidden" class="is_crypto" value="' . $transaction['is_crypto'] . '" />
<td>' . $transaction['type'] . '</td>
<td><input type="hidden" class="localdate" value="' . (strtotime($transaction['date'])) . '" /></td>
<td>' . Stringz::currency($transaction['btc'], true) . ' ' . $CFG->currencies[$transaction['c_currency']]['fa_symbol'] . '</td>
<td><span class="currency_char">' . $trans_symbol . '</span><span>' . Stringz::currency($transaction['btc_net'] * $transaction['fiat_price'], ($transaction['is_crypto'] == 'Y')) . '</span></td>
<td><span class="currency_char">' . $trans_symbol . '</span><span>' . Stringz::currency($transaction['fiat_price'], ($transaction['is_crypto'] == 'Y')) . '</span></td>
<td><span class="currency_char">' . $trans_symbol . '</span><span>' . Stringz::currency($transaction['fee'] * $transaction['fiat_price'], ($transaction['is_crypto'] == 'Y')) . '</span></td>
</tr>';
}
}else{

}

?>
</tbody>
</table>

<?php } ?>

<!-- End of trade_history_table -->


<script type="text/javascript" src="js/ops_new1.js?v=20160210"></script>
<script type="text/javascript">
$(document).ready(function() {
$('.buy_orders_table').DataTable({ });
$('.sell_orders_table').DataTable({ });
$('.buy_open_orders_table').DataTable({ });
$('.sellopenorderstable').DataTable({ });
$('.trade_history_table').DataTable({ });
var r = 1;
if (r == 1) {
$(".page-link").click(function(e){
table.page( 'next' ).draw( 'page' );
});
r = 2;
}

});
</script>