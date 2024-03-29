<?php
    include '../lib/common.php';
    
    if (User::$info['locked'] == 'Y' || User::$info['deactivated'] == 'Y') {
    	Link::redirect('userprofile.php');
    } elseif (User::$awaiting_token) {
    	Link::redirect('verify-token.php');
    } elseif (!User::isLoggedIn()) {
    	Link::redirect('login.php');
    }

    // deposit details get by sivabharathy

    $transaction_id = $_REQUEST['transaction_id'];

$currency1 = (!empty($_REQUEST['currency'])) ? preg_replace("/[^0-9]/", "", $_REQUEST['currency']) : false;
//$amount = (!empty($_REQUEST['amount'])) ? preg_replace("/[^0-9]/", "", $_REQUEST['amount']) : false;
$amount = (!empty($_REQUEST['amount'])) ? $_REQUEST['amount'] : false;
$description1 = (!empty($_REQUEST['description'])) ? preg_replace("/[^\pL 0-9a-zA-Z!@#$%&*?\.\-\_ ]/u", "", $_REQUEST['description']) : false;
$bank_name = (!empty($_REQUEST['bank_name'])) ? preg_replace("/[^\pL 0-9a-zA-Z!@#$%&*?\.\-\_ ]/u", "", $_REQUEST['bank_name']) : false;
$pan_no = (!empty($_REQUEST['pan_no'])) ? preg_replace("/[^\pL 0-9a-zA-Z!@#$%&*?\.\-\_ ]/u", "", $_REQUEST['pan_no']) : false;
// $remove_id1 = (!empty($_REQUEST['remove_id'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['remove_id']) : false;

$status = 1;

if ($_REQUEST['action']) {

    if ((empty($transaction_id))) {
        Errors::add('Transaction id is required');
    }

    if ((empty($bank_name))) {
        Errors::add('Bank name is required');
    }

    if ((empty($amount))) {
        Errors::add('Amount is required');
    }

    if ($amount == 0) {
        $status = 0;
        Errors::add('Amount should be greater than zero');
    }
    if (!preg_match('/^[0-9]*$/', $_REQUEST['amount'])) {
        Errors::add('Amount is only numbers');
    }

}
// if ($account1 > 0) {
//     if (empty($_REQUEST['uniq'])) {
//         Errors::add('Page expired.');
//     }

// }
if($_GET['deposit']=='add'){
  Messages::add('Successfully added deposit tranasaction');
}

if($_GET['deposit']=='add_failed'){
  Messages::add('Unable to add deposit');
}


$page1 = (!empty($_REQUEST['page'])) ? preg_replace("/[^0-9]/", "", $_REQUEST['page']) : false;
$currencies = Settings::sessionCurrency();
API::add('Requests', 'get', array(1, false, false, false, 27));
API::add('Requests', 'get', array(false, $page1, 15, false, 27));
API::add('BankAccounts', 'get');
API::add('Content', 'getRecord', array('deposit-bank-instructions'));
API::add('Content', 'getRecord', array('deposit-no-bank'));
API::add('User', 'getAvailable');

$query = API::send();
$user_available = $query['User']['getAvailable']['results'][0];

$bank_accounts = $query['BankAccounts']['get']['results'][0];
// echo "<pre>"; print_r($bank_accounts); exit;
$total = $query['Requests']['get']['results'][0];
$requests = $query['Requests']['get']['results'][1];
$bank_instructions = $query['Content']['getRecord']['results'][0];
// $pagination = $pagination = Content::pagination('deposit.php', $page1, $total, 15, 5, false);
$page_title = Lang::string('deposit');
if (!empty($transaction_id) && $status == 1) {
    $_REQUEST['action'] = false;
    $amount = (float) $amount;
    API::add('Requests', 'insertDeposit', array($currency1, $amount, $bank_name, $transaction_id));
    API::add('Requests', 'get', array(1, false, false, false, 27));
    API::add('Requests', 'get', array(false, $page1, 15, false, 27));
    $query = API::send();
    Messages::add('Successfully added deposit tranasaction');
    $requests = $query['Deposit']['get']['results'][0];
    $total = $query['Requests']['get']['results'][0];
    $requests = $query['Requests']['get']['results'][1];
    $_REQUEST['transaction_id'] = false;
    $_REQUEST['amount'] = false;
    Link::redirect('depositnew.php?deposit=add');
}
if (empty($_REQUEST['bypass'])) {
}

    // end of deposit details get by sivabharathy
       
    $market = $_GET['trade'];
    $currencies = Settings::sessionCurrency();
         
    $buy = (!empty($_REQUEST['buy']));
    $sell = (!empty($_REQUEST['sell']));
    $ask_confirm = false;
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
    
    foreach ($CFG->currencies as $key => $currency) {
    	if (is_numeric($key) || $currency['is_crypto'] != 'Y')
    	continue;
    	
    	API::add('Stats','getCurrent',array($currency['id'],$currency1));
    }
               
    API::add('User','hasCurrencies');
    API::add('Orders','getBidAsk',array($c_currency1,$currency1));
    API::add('Orders','get',array(false,false,10,$c_currency1,$currency1,false,false,1));
    API::add('Orders','get',array(false,false,10,$c_currency1,$currency1,false,false,false,false,1));
    API::add('Transactions','get',array(false,false,1,$c_currency1,$currency1));
    API::add('Transactions','get24hData',array(28,27));
    API::add('Transactions','get24hData',array(42,27));
    API::add('Transactions','get24hData',array(42,28));
    API::add('Transactions','get24hData',array(43,27));
    API::add('Transactions','get24hData',array(43,28));
    API::add('Transactions','get24hData',array(43,27));
    API::add('Transactions','get24hData',array(43,28));
    API::add('Transactions','get24hData',array(45,27));
    API::add('Transactions','get24hData',array(45,28));
    API::add('Transactions','get24hData',array(42,45));
    API::add('Transactions','get24hData',array(43,45));
    API::add('Transactions','get24hData',array(44,27));
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

       API::add('Transactions','get24hData',array(28,50)); //btc-IOX
       API::add('Transactions','get24hData',array(42,50)); //ltc-IOX
       API::add('Transactions','get24hData',array(44,50)); //bch-IOX
       API::add('Transactions','get24hData',array(43,50)); //zec-IOX
       API::add('Transactions','get24hData',array(45,50)); //eth-IOX

       API::add('Transactions','get24hData',array(50,27)); //IOX-USD
       API::add('Transactions','get24hData',array(50,28)); //IOX-btc
       API::add('Transactions','get24hData',array(50,42)); //IOX-ltc
       API::add('Transactions','get24hData',array(50,44)); //IOX-bch
       API::add('Transactions','get24hData',array(50,43)); //IOX-zec
       API::add('Transactions','get24hData',array(50,45)); //IOX-eth

       API::add('Transactions','get24hData',array(51,27)); //USDT-USD
       API::add('Transactions','get24hData',array(51,28)); //USDT-btc
       API::add('Transactions','get24hData',array(51,42)); //USDT-ltc
       API::add('Transactions','get24hData',array(51,44)); //USDT-bch
       API::add('Transactions','get24hData',array(51,43)); //USDT-zec
       API::add('Transactions','get24hData',array(51,45)); //USDT-eth
       API::add('Transactions','get24hData',array(51,50)); //USDT-IOX
       
    
       //my transactions
       API::add('Transactions', 'get', array(false, $page1, 30, $c_currency1, $currency1, 1, $start_date1, $type1, $order_by1));
       API::add('Transactions', 'getTypes');
    
    
    if ($currency_info['is_crypto'] != 'Y') {
    	API::add('BankAccounts','get',array($currency_info['id']));
    }
    	
               
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
    // echo "<pre>"; print_r($user_available); exit;
    // echo "<pre>"; print_r($user_fee_both); exit;
    
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

    $transactions_24hrs_iox_usd = $query['Transactions']['get24hData']['results'][35] ;
    $transactions_24hrs_iox_btc = $query['Transactions']['get24hData']['results'][36] ;
    $transactions_24hrs_iox_ltc = $query['Transactions']['get24hData']['results'][37] ;
    $transactions_24hrs_iox_bch = $query['Transactions']['get24hData']['results'][38] ;
    $transactions_24hrs_iox_zec = $query['Transactions']['get24hData']['results'][39] ;
    $transactions_24hrs_iox_eth = $query['Transactions']['get24hData']['results'][40] ;

    $transactions_24hrs_usdt_usd = $query['Transactions']['get24hData']['results'][41] ;
    $transactions_24hrs_usdt_btc = $query['Transactions']['get24hData']['results'][42] ;
    $transactions_24hrs_usdt_ltc = $query['Transactions']['get24hData']['results'][43] ;
    $transactions_24hrs_usdt_bch = $query['Transactions']['get24hData']['results'][44] ;
    $transactions_24hrs_usdt_zec = $query['Transactions']['get24hData']['results'][45] ;
    $transactions_24hrs_usdt_eth = $query['Transactions']['get24hData']['results'][46] ;
    $transactions_24hrs_usdt_iox = $query['Transactions']['get24hData']['results'][47] ;
    
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
    
    $buy_amount1 = (!empty($_REQUEST['buy_amount'])) ? Stringz::currencyInput($_REQUEST['buy_amount']) : 0;
    $buy_price1 = (!empty($_REQUEST['buy_price'])) ? Stringz::currencyInput($_REQUEST['buy_price']) : $current_ask;
    $buy_subtotal1 = $buy_amount1 * $buy_price1;
    $buy_fee_amount1 = ($user_fee_bid * 0.01) * $buy_subtotal1;
    $buy_total1 = round($buy_subtotal1 + $buy_fee_amount1,($currency_info['is_crypto'] == 'Y' ? 8 : 2),PHP_ROUND_HALF_UP);
    $buy_stop = false;
    $buy_stop_price1 = false;
    $buy_all1 = (!empty($_REQUEST['buy_all']));
    
    $sell_amount1 = (!empty($_REQUEST['sell_amount'])) ? Stringz::currencyInput($_REQUEST['sell_amount']) : 0;
    $sell_price1 = (!empty($_REQUEST['sell_price'])) ? Stringz::currencyInput($_REQUEST['sell_price']) : $current_bid;
    $sell_subtotal1 = $sell_amount1 * $sell_price1;
    $sell_fee_amount1 = ($user_fee_ask * 0.01) * $sell_subtotal1;
    $sell_total1 = round($sell_subtotal1 - $sell_fee_amount1,($currency_info['is_crypto'] == 'Y' ? 8 : 2),PHP_ROUND_HALF_UP);
    $sell_stop = false;
    $sell_stop_price1 = false;
    
    if ($CFG->trading_status == 'suspended')
    	Errors::add(Lang::string('buy-trading-disabled'));
    
    if ($buy && !is_array(Errors::$errors)) {
    	$buy_market_price1 = (!empty($_REQUEST['buy_market_price']));
    	$buy_price1 = ($buy_market_price1) ? $current_ask : $buy_price1;
    	$buy_stop = (!empty($_REQUEST['buy_stop']));
    	$buy_stop_price1 = ($buy_stop) ? Stringz::currencyInput($_REQUEST['buy_stop_price']) : false;
    	$buy_limit = (!empty($_REQUEST['buy_limit']));
    	$buy_limit = (!$buy_stop && !$buy_market_price1) ? 1 : $buy_limit;
    	
    	if (!$confirmed && !$cancel) {
    	API::add('Orders','checkPreconditions',array(1,$c_currency1,$currency_info,$buy_amount1,(($buy_stop && !$buy_limit) ? $buy_stop_price1 : $buy_price1),$buy_stop_price1,$user_fee_bid,$user_available[$currency_info['currency']],$current_bid,$current_ask,$buy_market_price1,false,false,$buy_all1));
    	if (!$buy_market_price1)
    	API::add('Orders','checkUserOrders',array(1,$c_currency1,$currency_info,false,(($buy_stop && !$buy_limit) ? $buy_stop_price1 : $buy_price1),$buy_stop_price1,$user_fee_bid,$buy_stop));
    	
    	$query = API::send();
    	$errors1 = $query['Orders']['checkPreconditions']['results'][0];
    	if (!empty($errors1['error']))
    	Errors::add($errors1['error']['message']);
    	$errors2 = (!empty($query['Orders']['checkUserOrders']['results'][0])) ? $query['Orders']['checkUserOrders']['results'][0] : false;
    	if (!empty($errors2['error']))
    	Errors::add($errors2['error']['message']);
    	
    	if (!$errors1 && !$errors2)
    	$ask_confirm = true;
    	}
    	else if (!$cancel) {
    	API::add('Orders','executeOrder',array(1,(($buy_stop && !$buy_limit) ? $buy_stop_price1 : $buy_price1),$buy_amount1,$c_currency1,$currency1,$user_fee_bid,$buy_market_price1,false,false,false,$buy_stop_price1,false,false,$buy_all1));
    	$query = API::send();
    	$operations = $query['Orders']['executeOrder']['results'][0];
        // echo "string<pre>"; print_r($operations); exit;
    	if (!empty($operations['error'])) {
    	Errors::add($operations['error']['message']);
    	}
    	else if ($operations['new_order'] > 0) {
    	$_SESSION["buysell_uniq"][time()] = md5(uniqid(mt_rand(),true));
    	if (count($_SESSION["buysell_uniq"]) > 3) {
    	unset($_SESSION["buysell_uniq"][min(array_keys($_SESSION["buysell_uniq"]))]);
    	}
    	
    	Link::redirect('openorders.php',array('transactions'=>$operations['transactions'],'new_order'=>1));
    	exit;
    	}
    	else {
    	$_SESSION["buysell_uniq"][time()] = md5(uniqid(mt_rand(),true));
    	if (count($_SESSION["buysell_uniq"]) > 3) {
    	unset($_SESSION["buysell_uniq"][min(array_keys($_SESSION["buysell_uniq"]))]);
    	}
    	
    	Link::redirect('openorders.php',array('transactions'=>$operations['transactions']));
    	exit;
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
    	if (!$sell_market_price1)
    	API::add('Orders','checkUserOrders',array(0,$c_currency1,$currency_info,false,(($sell_stop && !$sell_limit) ? $sell_stop_price1 : $sell_price1),$sell_stop_price1,$user_fee_ask,$sell_stop));
    	
    	$query = API::send();
    	$errors1 = $query['Orders']['checkPreconditions']['results'][0];
    	if (!empty($errors1['error']))
    	Errors::add($errors1['error']['message']);
    	$errors2 = (!empty($query['Orders']['checkUserOrders']['results'][0])) ? $query['Orders']['checkUserOrders']['results'][0] : false;
    	if (!empty($errors2['error']))
    	Errors::add($errors2['error']['message']);
    	
    	if (!$errors1 && !$errors2)
    	$ask_confirm = true;
    	}
    	else if (!$cancel) {
    	API::add('Orders','executeOrder',array(0,($sell_stop && !$sell_limit) ? $sell_stop_price1 : $sell_price1,$sell_amount1,$c_currency1,$currency1,$user_fee_ask,$sell_market_price1,false,false,false,$sell_stop_price1));
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
    	
    	Link::redirect('openorders.php',array('transactions'=>$operations['transactions'],'new_order'=>1));
    	exit;
    	}
    	else {
    	$_SESSION["buysell_uniq"][time()] = md5(uniqid(mt_rand(),true));
    	if (count($_SESSION["buysell_uniq"]) > 3) {
    	unset($_SESSION["buysell_uniq"][min(array_keys($_SESSION["buysell_uniq"]))]);
    	}
    	
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
<!DOCTYPE html>
<html lang="en">
    <style>
        .input-caption {
        position: relative;
        float: right;
        top: -28px;
        right: 6px;
        height: 28px;
        padding-top: 5px;
        }
        .custom-select
        {
        font-size: 11px;
        padding: 5px 10px;
        border-radius: 2px;
        height: 28px !important;
        }
        label.cont
        {
            width:100%;
        }
        .pull-right 
        {
            float:right;
        }

        .current-otr p 
        {
            margin: 5px 0;
        }
        .left-side-widget .nav-link:hover,
        .left-side-widget .nav-link:focus,
        .left-side-widget .nav-link:visited,
        .left-side-widget .nav-link:active{
            color:#000 !important;
        }
    </style>
   <!DOCTYPE html>
<html lang="en">
    <?php include "includes/sonance_header.php";  ?>
    <body id="wrapper">
   <?php include "includes/sonance_navbar.php"; ?>
   <header>
            <div class="banner row">
                <div class="container content">
                    <h1>Fiat Wallet</h1>
                    <p class="text-white text-center">Make a Deposit, view your wallet account balance and transactions.</p>
                    <div class="text-center">
                      <a href="manageaccounts.php" class="btn" style="background: #007bff !important;">Manage Bank Account</a> 
                    </div>
                </div>
            </div>
        </header>
    <div class="page-container">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-12">

                    <?Errors::display();?>

                    <? Messages::display(); ?>

                    <div class="pro card">
                        <div class="card-header">
                            <h6>
                                <strong>Your Fiat Currency Transactions</strong>
                                <span class="float-right">
                                    <a href="#fiatcurrency" data-toggle="modal">
                                    <svg style="width:15px;height:15px;" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                         viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve" >
                                    <circle style="fill:#47a0dc" cx="25" cy="25" r="25"/>
                                    <line style="fill:none;stroke:#FFFFFF;stroke-width:4;stroke-linecap:round;stroke-miterlimit:10;" x1="25" y1="37" x2="25" y2="39"/>
                                    <path style="fill:none;stroke:#FFFFFF;stroke-width:4;stroke-linecap:round;stroke-miterlimit:10;" d="M18,16
                                        c0-3.899,3.188-7.054,7.1-6.999c3.717,0.052,6.848,3.182,6.9,6.9c0.035,2.511-1.252,4.723-3.21,5.986
                                        C26.355,23.457,25,26.261,25,29.158V32"/><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g>
                                    <g></g><g></g><g></g><g></g><g></g><g></g><g></g>
                                    </svg>
                                </a>
                                </span>
                            </h6>
                        </div>

                        

                        <div class="card-body">

                            <div class="deposite-otr">
                                
                                <form id="add_deposit" action="depositnew.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="currency" value="27">
                                    <div class="form-group">
                                        <label>Transaction Id</label>
                                       <input type="text" class="form-control" name="transaction_id" value="<?php echo $_REQUEST['transaction_id']; ?>" >
                                    </div>
                                    <div class="form-group">
                                        <label>Bank Name</label>
                                        <select id="bank_name" name="bank_name" class="form-control">
                            <?
$i = 1;
if ($bank_accounts) {
    foreach ($bank_accounts as $account) {
        echo '<option ' . (($i == 1) ? 'selected="selected"' : '') . ' value="' . $account['id'] . '">' . $account['account_number'] . ' - (' . $account['currency'] . ')</option>';
        ++$i;
    }
}
?>
                                </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Amount</label>
                                        <input type="text" class="form-control" name="amount"  value="<?php echo $_REQUEST['amount']; ?>" >
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn">Add</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="col-md-6 col-sm-6 col-xs-12">

                    <div class="pro card"> 
                        <div class="card-header">
                            <h6><strong>Fiat Wallet</strong>
                                
                            </h6>
                        </div>
                        
                        <div class="card-body">
                            <div class="media">
                          <!-- <img class="mr-3" src="images/dollar.png" alt="" width="40" height="40"> -->
                          <div class="media-body">
                           <!--  <p class="mb-0"><b>USD Wallet</b></p>
                            <span>$ <?= Stringz::currency($user_available['USD']) ?></span> -->
                             <div class="">
                                <a href="manageaccounts.php"  class="btn text-black">Manage Accounts</a>
                                <a href="withdraw.php" class="btn" style="background: #007bff !important;">Withdraw</a> 
                            </div>
                          </div>
                        </div>
                        </div>
                    </div>

                    <!-- exchagne admin bank details -->
                    <div class="pro card" style="display: none;"> 
                        <div class="card-header">
                            <h6><strong>How to deposit fiat currency ? <a href="#" data-toggle="collapse" data-target="#deposite-process">Click Here</a></strong>
                                
                            </h6>
                        </div>
                        
                        <div class="card-body collapse" id="deposite-process">
                    
                          
                            <?echo $bank_instructions['content']; ?>
                         
                        </div>
                        
                    </div>
                    <!-- exchagne admin bank details -->


                    <div class="pro card">
                        <div class="card-header">
                            <h6><strong>History</strong>
                                 <span class="float-right">
                                    <a href="#dephistory" data-toggle="modal">
                                    <svg style="width:15px;height:15px;" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                         viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve" >
                                    <circle style="fill:#47a0dc" cx="25" cy="25" r="25"/>
                                    <line style="fill:none;stroke:#FFFFFF;stroke-width:4;stroke-linecap:round;stroke-miterlimit:10;" x1="25" y1="37" x2="25" y2="39"/>
                                    <path style="fill:none;stroke:#FFFFFF;stroke-width:4;stroke-linecap:round;stroke-miterlimit:10;" d="M18,16
                                        c0-3.899,3.188-7.054,7.1-6.999c3.717,0.052,6.848,3.182,6.9,6.9c0.035,2.511-1.252,4.723-3.21,5.986
                                        C26.355,23.457,25,26.261,25,29.158V32"/><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g>
                                    <g></g><g></g><g></g><g></g><g></g><g></g><g></g>
                                    </svg>
                                </a>
                                </span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="">
                                <table class="table table-border">
                                    <thead>
                                        <tr>
                                            <th scope="col">Date</th>
                                            <th scope="col">Type</th>
                                            <th scope="col">Coin</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 

                                        if ($requests) {
    foreach ($requests as $request) {
        ?>

        <?php $d = date_create($request['date']);?>
                                        <tr>
                                            <td scope="row">
                                                <?php echo $request['date'];//date_format($d, "d"); ?>
                                            </td>
                                            <td>Deposit</td>
                                            <td>USD</td>
                                            <td>
                                                <?php echo (($CFG->currencies[$request['currency']]['is_crypto'] == 'Y') ? Stringz::currency($request['amount'], true) . ' ' . $request['fa_symbol'] : $request['fa_symbol'] . Stringz::currency($request['amount'])) ?>
                                            </td>
                                            <td><?=$request['status']; ?></td>
                                        </tr>

                                        <?
    }
} else {
    echo '<div style="padding:20% 35%;">' . Lang::string('deposit-no') . '</div>';
}
?>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--modal-1-->
<div class="modal fade" id="fiatcurrency" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Deposits</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Here you can:</p>
        <ul>
            <li>Add the transaction details after you have made the fiat currency deposit.</li>
            <li>View the transaction details of the Fiat currency Deposit transactions.</li>
            <li>Place a Withdraw request.</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="dephistory" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Deposits</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Here you can view the transaction details of the deposits you have made.</p>
        
      </div>
    </div>
  </div>
</div>
      <?php include "includes/sonance_footer.php"; ?>
    <script type="text/javascript">
    $(document).ready(function() {
        //$('.selectpicker').select2();

        /**
            if no bank accounts added remove depoisite add button
         */
        if(!$('select#bank_name option').length) {
            $("#add_deposit").find("button[type='submit']").attr('disabled', 'disabled').html('Add bank account first')
        }


    });
    </script>
</body>

</html>