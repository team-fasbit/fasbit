<!DOCTYPE html>
<html lang="en">
<?php
    include '../lib/common.php';
    require_once ("cfg.php");
    
//     error_reporting(E_ERROR | E_WARNING | E_PARSE);
// ini_set('display_errors', 1);
    
      
    $currency_id = $_REQUEST['currency'];
    $currency_id1 = $_REQUEST['currency'];
    $c_currency_id = $_REQUEST['c_currency'];
    if (!$currency_id) {
       $currency_id = 28;
       $_REQUEST['currency'] = 28;
    }    

    //$currencies = mysqli_fetch_assoc($currency_query);

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
        //$GLOBALS['REFERRAL_BASE_URL'] = $ref_response->base_url;
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


    $market = $_GET['trade'];
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
    
    foreach ($CFG->currencies as $key => $currency) {
        // if (is_numeric($key) || $currency['is_crypto'] != 'Y')
        // continue;
        
        // API::add('Stats','getCurrent',array($currency['id'],$currency1));
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
            //print_r($fields);
            //url-ify the data for the POST
            foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
            rtrim($fields_string, '&');
           //open connection
            $ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, count($fields));
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //execute post
            $result = curl_exec($ch);
            $response = json_decode($result);
            //close connection
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
            

            //
            $his_url = $REFERRAL_BASE_URL."get-usage-history.php";
            $ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, count($fields));
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //execute post
            $result = curl_exec($ch);
            $response = json_decode($result);
            //var_dump($response);
            //close connection
            curl_close($ch);
        }
        
    // end of referral

               // echo $c_currency1;echo "<br><br>"; echo $currency1;echo "<br><br>"; 
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
       
    
       //my transactions 
       // API::add('Transactions', 'get', array(false, $page1, 30, $c_currency1, $currency1, 1, $start_date1, $type1, $order_by1));
       // API::add('Transactions', 'getTypes');
    
    
    // if ($currency_info['is_crypto'] != 'Y') {
    //     API::add('BankAccounts','get',array($currency_info['id']));
    // }
        
       //echo $_REQUEST['buy_market_price']; exit;         
    $query = API::send();
   // print_r($query);die(); 
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
        //echo 'yes is_referral true';//bonus_amount
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

    //
    // referral bonus starts
    if ($_REQUEST['is_referral']) {
        //echo 'yes is_referral true';//bonus_amount
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
    
    if ($buy && !is_array(Errors::$errors)) {

        // echo $_REQUEST['buy_market_price']; exit;
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
        // print_r($query1['Orders']['checkUserOrders']['results'][0]);exit;
        // echo "<br><br>";echo $_REQUEST['buy_market_price'];echo "string";echo "<br><br>";exit;
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
        // echo "string<pre>"; print_r($operations); exit;
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
<head>
    <title><?= $CFG->exchange_name; ?> | Exchange</title>
    <?php include "bitniex/bitniex_header.php"; ?>
    <style>
        .userbuy-active {
            background-color: rgb(251, 200, 101);
        }
        tr.clickable-row {
            cursor: pointer;
        }
        .order-table.color-table1 {
            background-color: #f4f8f9;
        }
        h5.order-title1 {
            font-size: 13px;
            padding-top: 5px;
            padding-bottom: 5px;
        }
        .order-table.color-table1 {
            overflow: unset;
            overflow-x: hidden;
        }

        /* width */
        ::-webkit-scrollbar {
            width: 5px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
            box-shadow: inset 0 0 5px grey; 
            border-radius: 10px;
        }
 
        /* Handle */
        ::-webkit-scrollbar-thumb {
            background-image: linear-gradient(-134deg, #023063 0%, #021633 100%); 
            border-radius: 0px;
        }
        
        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover {
            background: #0043a5; 
        }
        .form-inner1 {
    padding: 0.5em;
}
.nav-tabs>li.active>a {
    color: #555;
    cursor: default;
    background-color: #fff;
    border: 1px solid #ddd;
    border-bottom-color: transparent;
}

.nav>li>a {
    position: relative;
    display: block;
    padding: 10px 15px !IMPORTANT;
}
.nav-tabs>li>a {
    line-height: 1;
    border: 1px solid transparent;
    border-radius: 4px 4px 0 0;
}
.buy-sell ul.nav.nav-tabs {
    margin-bottom: 10px;
}
.nav-tabs>li>a:hover {
    text-decoration: none;
}
.timeline-Footer a.u-floatLeft {
    display: none !important;
}
footer.timeline-Footer.u-cf a.u-floatLeft {
    display: none !important;
}

.btn.btn-light {
    padding: 7px 20px !IMPORTANT;
    font-size: 15px !important;
}
.form-control {
    padding: 0.375rem .75rem !important;
    font-size: 14px !important;
    line-height: 1.5 !important;
}

.order-title {
    font-size: 14px;
}
th {
    font-size: 13px !important;
    font-weight: 600 !important;
}
.page-item.disabled .page-link {
    padding: 10px 15px !IMPORTANT;
}
footer {
    margin-top: 20px;
}
.loginMessage {
    /*color: white !important;*/
}
.chart-hilights .name {
    color: #333 !IMPORTANT;
}
a.help-link {
    color: #333 !important;
}
h5.tite {
    color: #333;
}
h5.order-title {
    color: #333;
}
/*.form-inner1 {
    background-color: #e8f6f3;
}*/
.form-box .form-head p {
    color: #333;
}
.form-box .input-group-text {
    width: 54px;
}
.tab-box .nav-item {
    margin-bottom: 5px;
}
h5.order-title1 {
    color: #333 !important;
}
.form-box p.info-link {
    color: #333;
}
.form-box p.info-link a {
    color: #333;
}
div#mar-trades {
    padding: 20px;
}
.row.trade-table3 {
    padding-bottom: 5px;
}
form#buy_form .form-inner1 p.info-link {
    margin-bottom: 15px;
}
form#sell_form .form-inner1 {
    padding-bottom: 7px;
}
form#sell_form .form-inner1 p.info-link {
    margin-bottom: 15px;
}
table.dataTable thead .sorting:before, table.dataTable thead .sorting:after, table.dataTable thead .sorting_asc:before, table.dataTable thead .sorting_asc:after, table.dataTable thead .sorting_desc:before, table.dataTable thead .sorting_desc:after, table.dataTable thead .sorting_asc_disabled:before, table.dataTable thead .sorting_asc_disabled:after, table.dataTable thead .sorting_desc_disabled:before, table.dataTable thead .sorting_desc_disabled:after {
    bottom: 0px;
}
form#confirm_form .content h4 {
    font-size: 12px;
}
form#confirm_form .buy-btc.btn.btn-primary {
    font-size: 0.9em !important;
    padding: 0.5em 1.2em !important;
}
form#confirm_form p.m-t-10 {
    font-size: 13px;
}
li.paginate_button .page-link {
    position: relative;
    display: block;
    padding: 0.6rem .75rem !important;
    margin-left: -1px !important;
    line-height: 1.25 !important;
    color: #007bff !important;
    background-color: #fff !important;
    border: 1px solid #dee2e6 !important;
}

/*      chart start     */

  #chartdiv {
    border: 1px solid #ddd;
    width: 100%;
    margin-top: 1px;
    margin-bottom: 1px;
    height: 400px !important;
    padding: 1px;
    border-radius: 0;
}
 .amcharts-chart-div a {
    display: none !important;
}
.amChartsPeriodSelector.amcharts-period-selector-div div:nth-child(2) {
    display: none !important;
}

.amcharts-stock-div .amcharts-export-menu.amcharts-export-menu-bottom-right.amExportButton.active li.export-main {
    display: none;
}

        .btn.btn-yellow.btn-block {
    padding: 7px 20px !IMPORTANT;
}
.col-md-6, .col-xs-12 {
    position: relative;
    width: 100%;
    min-height: 1px;
    padding-right: 15px;
    padding-left: 15px;
}

/*      chart end       */
    </style>
</head>

<body id="wrapper">
    <div id="colorPanel" class="colorPanel">
        <a id="cpToggle" href="#"></a>
        <ul></ul>
    </div>
        <?php include "bitniex/home_nav_bar.php"; ?>
    <div class="page-container">
        <div class="container-fluid">
            
            <div class="row ">
                <div class="col-md-9 col-sm-8 col-xs-12">

                    <!-- Chart Header -->
                    <div class="row chart-title-head" id="chart_header">
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
                        <!-- <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="tab-box">
                                <div class="tab-head">
                                    <h5 class="tite">MARKETS</h5>
                                </div>
                            </div>
                        </div> -->
                    </div>

                    <!-- End of Chart Header -->


                    <!-- Chart Block -->

                    <div class="row">
                        <div class="col-md-12">
                            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
                                 <!-- Resources -->
                                <script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
                                <script src="https://www.amcharts.com/lib/3/serial.js"></script>
                                <script src="https://www.amcharts.com/lib/3/amstock.js"></script>
                                <script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
                                <link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
                                <script src="https://www.amcharts.com/lib/3/themes/none.js"></script>
                                <!-- Chart code -->


                                <script>

var n_chart_data = [];

var api_url = "chart_json.php?currency=<?php echo $currency_id1;?>&c_currency=<?php echo $c_currency_id; ?>";

$.ajax({
            type: "GET",
            url: api_url,
            dataType:'json',
            success: function(data){
              console.log(data.Data);
              data.Data.forEach(function(element) {
                console.log(element);
                var newDate = new Date(element.date*1000);
                n_chart_data.push( {
                  "date": newDate,
                  "value": element.btc_price,
                  "volume": element.btc_before
                } );
                
                console.log("single Data :"+n_chart_data);
              });
              var data_condole = data.Data;
              console.log("hello :"+$(data_condole).length);
              if($(data_condole).length == 0){
                n_chart_data.push( {
                  "date": 0,
                  "value": 0,
                  "volume": 0
                });
              }
              var chart = AmCharts.makeChart( "chartdiv", {
  "type": "stock",
  "theme": "light",
  "categoryAxesSettings": {
    "minPeriod": "mm"
  },

  "dataSets": [ {
    "color": "#b0de09",
    "fieldMappings": [ {
      "fromField": "value",
      "toField": "value"
    }, {
      "fromField": "volume",
      "toField": "volume"
    } ],

    "dataProvider": n_chart_data,
    "categoryField": "date"
  } ],

  "panels": [ {
    "showCategoryAxis": false,
    "title": "Value",
    "percentHeight": 70,

    "stockGraphs": [ {
      "id": "g1",
      "valueField": "value",
      "type": "smoothedLine",
      "lineThickness": 2,
      "bullet": "round"
    } ],


    "stockLegend": {
      "valueTextRegular": " ",
      "markerType": "none"
    }
  }, {
    "title": "Volume",
    "percentHeight": 30,
    "stockGraphs": [ {
      "valueField": "volume",
      "type": "column",
      "cornerRadiusTop": 2,
      "fillAlphas": 1
    } ],

    "stockLegend": {
      "valueTextRegular": " ",
      "markerType": "none"
    }
  } ],

  "chartScrollbarSettings": {
    "graph": "g1",
    "usePeriod": "10mm",
    "position": "top"
  },

  "chartCursorSettings": {
    "valueBalloonsEnabled": true
  },

  "periodSelector": {
    "position": "top",
    "dateFormat": "YYYY-MM-DD JJ:NN",
    "inputFieldWidth": 150,
    "periods": [ {
      "period": "hh",
      "count": 1,
      "label": "1 hour"
    }, {
      "period": "hh",
      "count": 2,
      "label": "2 hours"
    }, {
      "period": "hh",
      "count": 5,
      "selected": true,
      "label": "5 hour"
    }, {
      "period": "hh",
      "count": 12,
      "label": "12 hours"
    }, {
      "period": "MAX",
      "label": "MAX"
    } ]
  },

  "panelsSettings": {
    "usePrefixes": true
  },

  "export": {
    "enabled": true,
    "position": "bottom-right"
  }
} );

         
          }
      });

function addPanel() {
  var chart = AmCharts.charts[ 0 ];
  if ( chart.panels.length == 1 ) {
    var newPanel = new AmCharts.StockPanel();
    newPanel.allowTurningOff = true;
    newPanel.title = "Volume";
    newPanel.showCategoryAxis = false;

    var graph = new AmCharts.StockGraph();
    graph.valueField = "volume";
    graph.fillAlphas = 0.15;
    newPanel.addStockGraph( graph );

    var legend = new AmCharts.StockLegend();
    legend.markerType = "none";
    legend.markerSize = 0;
    newPanel.stockLegend = legend;

    chart.addPanelAt( newPanel, 1 );
    chart.validateNow();
  }
}

function removePanel() {
  var chart = AmCharts.charts[ 0 ];
  if ( chart.panels.length > 1 ) {
    chart.removePanel( chart.panels[ 1 ] );
    chart.validateNow();
  }
}
</script>



                    <div class="graph-1 card" id="chartdiv">
                        <!-- TradingView Widget BEGIN -->
                      
                        <!-- TradingView Widget END -->
                    </div>
                    <br><br>


                            <!-- TradingView Widget BEGIN -->
                          <!--   <div class="tradingview-widget-container">
                                <div id="tradingview_35f2b"></div>
                            </div> -->
                            <!-- TradingView Widget END -->
                        </div>
                    </div>

                    <!-- End of Chart Block -->

                    <div class="row trade-table3">
                    
                      <div class="col-md-4 col-sm-4 col-xs-12">
                            <div class="form-box">
                                <div class="form-head">
                                       <?php
                                    $req_stopbuy = $_REQUEST['stopbuy'];
                                        if (!$req_stopbuy) {
                                            $stopbuy_display ="none";
                                        }else{
                                            $stopbuy_display ="block";
                                        }
                                    ?>
                                    <div class="row">
                                    <div class="col-md-12" style="margin: auto;width: 100%;padding: 0;display: <?= $stopbuy_display?>">
                                        <!-- <span id="stop-limit-error"><? Errors::display(); ?></span> -->
                                        <? Errors::display(); ?>
                                        <style>
                                            .errors, .notice {
                                                   color: #e23535;
                                                    list-style: none;
                                                    background: #ff000036;
                                                    padding: 10px;
                                                    position: relative;
                                                    margin: 0 auto;
                                                    font-size: 1em;
                                                    text-align: center;
                                                    max-width: 90%;
                                                    left: 1px;
                                            }
                                            .success_message {
                                                   color: #118a0b;
                                                    list-style: none;
                                                    background: #62ff0036;
                                                    padding: 10px;
                                                    position: relative;
                                                    margin: 0 auto 10px auto;
                                                    font-size: 1em;
                                                    text-align: center;
                                                    max-width: 90%;
                                                    left: 1px;
                                            }
                                        </style>
                                        <?= ($notice) ? '<div class="notice">'.$notice.'</div>' : '' ?>
                                    </div>
                                </div>
                                    <h5 class="tite">STOP-LIMIT</h5>
                                    <p>You have : <span id="buy_user_available" class="buy_user_available" style="/* color: #2f8afd; */"><?= ((!empty($user_available[strtoupper($currency_info['currency'])])) ? Stringz::currency($user_available[strtoupper($currency_info['currency'])],($currency_info['is_crypto'] == 'Y')) : '0.00') ?></span> <span class="sell_currency_label"><?= $currency_info['currency'] ?></span></p>
                                    <p>You have : <span id="sell_user_available" class="sell_user_available" style="/* color: #2f8afd; */"  ><?= Stringz::currency($user_available[strtoupper($c_currency_info['currency'])],true) ?></span> <?= $c_currency_info['currency']?></p>
                                </div>
                                <div class="form-inner1 buy-sell">
                                        <ul class="nav nav-tabs">
                                             <?php
                                    $req_stopbuy_active = $_REQUEST['stopbuy'];
                                        if (!$req_stopbuy_active) {
                                            $stopbuy_active ="active";
                                        }else{
                                            $stopbuy_active ="";
                                        }
                                    $req_stopsell_active = $_REQUEST['stopsell'];
                                        if ($req_stopsell_active) {
                                            $stopsell_active ="active";
                                        }else{
                                            $stopsell_active ="";
                                        }
                                    ?>

                                            <li class="buy-active <?= $stopbuy_active ?>"><a data-toggle="tab" href="#stop-buy">Buy</a></li>
                                            <li class="sell-active <?= $stopsell_active ?>"><a data-toggle="tab" href="#stop-sell">Sell</a></li>
                                        </ul>

                                        <div class="tab-content">



                                            <!-- Stop buy form initial -->


                                            <? if(!$ask_confirm11) : ?>
                                            <div id="stop-buy" class="tab-pane <?= $stopbuy_active ?>">
                                                <?php Errors::display(); ?>                                                
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
                        <!-- <input type="hidden" id="user_fee1" value="<?= $user_fee_both['fee1'] ?>" /> -->
                        <input type="hidden" id="c_currency" value="<?= $c_currency1 ?>">
                                                <div class="content">
                                        
<!--                                         <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Stop">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><?= $currency_info['currency'] ?></span>
                                            </div>
                                        </div> -->
                                        <div class="input-group mb-3">
                                            <input name="buy_stop_price" id="buy_stop_price" type="text" class="form-control" placeholder="Limit">
                                            <!-- <input type="text" class="form-control" placeholder="Limit"> -->
                                            <div class="input-group-append">
                                                <span class="input-group-text"><?= $currency_info['currency'] ?></span>
                                            </div>
                                        </div>
                                        <div class="input-group mb-3">
                                             <input name="buy_amount11" id="buy_amount11" type="text" class="form-control" placeholder="Amount">
                                            <!-- <input type="text" class="form-control" placeholder="Amount"> -->
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

                                        <!-- Stop-limit buy referral -->
                                        <?php /*if($REFERRAL == true){ ?>
                                                <input type="hidden" name="ref_status" id="ref_status" value="1">
                                                <input type="hidden" name="bonus_amount" id="bonus_amount" value="<? echo $bonus_amount; ?>">
                                                <label class="cont" style="color: brown;font-style:  italic;">
                                                    <input 
                                                    class="checkbox" 
                                                    name="is_referral" 
                                                    id="is_referral" 
                                                    onclick="calculateBuyPrice()"
                                                    type="checkbox" value="1"
                                                    <? if($bonous_point == 0){ echo 'disabled'; } ?>
                                                     />
                                                    Use your Referral Bonus

                                                    <span style="float: right;margin-left: 50px;">    
                                                        <? echo $cur_code; ?> <? echo $bonus_amount; ?>
                                                    </span>

                                                    <span class="checkmark"></span>
                                                </label>
                                                <?php }*/ ?>

                                            <!-- End of Stop-limit buy referral -->


                                                  <input type="hidden" name="buy" value="1" />
                                                <input type="hidden" name="buy_all" id="buy_all" value="<?= $buy_all1 ?>" />
                                                <input type="hidden" name="uniq" value="<?= end($_SESSION["buysell_uniq"]) ?>" />
                                                <!-- <input type="submit" name="submit" value="Buy" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-light"/> -->
                                                <input type="button" name="submit" value="Buy" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-light" onclick="javascript:order_stop_buy();"/>
                                        <!-- <p class="text-right"><a href="#" class="btn btn-light">Buy</a> -->
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
                                            </div>

                                <!-- End of Stop buy form initial -->



                                <!-- Stop buy form confirmed -->


                                            <? else: ?>
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
                                                        <input type="hidden" name="buy_currency" value="<?= $currency1 ?>" />
                                                    </div>
                                        <div class="input-group mb-3">
                                                    <? if ($buy_stop) { ?>
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


                                                   <!--  <div class="current-otr">
                                                        <p style="margin-bottom:0px;"><?= Lang::string('buy-subtotal') ?></p>
                                                        <h4><b><?= $currency_info['fa_symbol'] ?><?= Stringz::currency($buy_amount1 * $buy_stop_price1,($currency_info['is_crypto'] == 'Y')) ?></b></h4>
                                                    </div> -->
                                                    <?php
                                                    if (($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50)) {
                                                    ?>
                                                    <table width="100%">
                                                        <tr>
                                                            <td><p style="margin-bottom:0px;">
                                                            <?= Lang::string('buy-fee') ?></p></td>
                                                            <td align="right"><h4><b><span id="sell_user_fee"><?= Stringz::currency($user_fee_bid) ?></span>%</b></h4></td>
                                                        </tr>
                                                    </table>

                                                  <!--   <div class="current-otr">
                                                        <p style="margin-bottom:0px;">
                                                            <?= Lang::string('buy-fee') ?>
                                                            <h4><b><span id="sell_user_fee"><?= Stringz::currency($user_fee_bid) ?></span>%</b></h4>
                                                        </p>
                                                    </div> -->
                                                    <?php } else { ?>
                                                    <!-- <div class="current-otr">
                                                        <p style="margin-bottom:0px;">
                                                            <?= Lang::string('buy-fee') ?>
                                                            <h4><b><span id="sell_user_fee">0.00</span>%</b></h4>
                                                        </p>
                                                    </div> -->
                                                    <table width="100%">
                                                        <tr>
                                                            <td><p style="margin-bottom:0px;">
                                                            <?= Lang::string('buy-fee') ?></p></td>
                                                            <td align="right"><h4><b><span id="sell_user_fee">0.00</span>%</b></h4></td>
                                                        </tr>
                                                    </table>
                                                    <?php } ?>


                                                    <!-- Stop-limit buy referral -->
                                        <?php /*if($REFERRAL == true && $one_point_values>0 && ($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50) ){ ?>
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
                                                <?php }*/ ?>

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

                                                  <!--   <div class="current-otr m-b-15">
                                                        <p style="margin-bottom:0px;">
                                                            <span id="buy_total_approx_label"><?= str_replace('[currency]','<span class="buy_currency_label">'.$currency_info['currency'].'</span>',Lang::string('buy-total-approx')) ?></span>
                                                        </p>
                                                        <h4>
                                                            <span id="buy_total_label" style="display:none;"><?= Lang::string('buy-total') ?></span>
                                                            <b><span id="buy_total"><?= Stringz::currency(round($buy_amount1 * $buy_stop_price1 - ($user_fee_ask * 0.01) * $buy_amount1 * $buy_stop_price1 ,($currency_info['is_crypto'] == 'Y' ? 8 : 2),PHP_ROUND_HALF_UP),($currency_info['is_crypto'] == 'Y')) ?></span></b>
                                                        </h4>
                                                    </div> -->
                                                    <? } ?>
                                        </div>
                                        <input type="hidden" name="buy" value="1" />
                                                    <input type="hidden" name="uniq" value="<?= end($_SESSION["buysell_uniq"]) ?>" />
                                                    <div class="btn-otr">
                                                        <span>
                                                        <input type="submit" name="submit" value="<?= Lang::string('confirm-buy') ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-primary" style="width: auto;display: inline-block;" />
                                                        </span>
                                                        <span>
                                                            <!-- <input id="cancel_transaction" type="submit" name="dont" value="<?= Lang::string('confirm-back') ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc" style="width: auto;display: inline-block;float: right;padding: 12px 30px;" /> -->
                                                            <input id="cancel_transaction" type="submit" name="dont" value="Back" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn btn-primary" style="width: auto;display: inline-block;float: right;">
                                                        </span>
                                                        <p class="m-t-10"> By clicking CONFIRM button an order request will be created.</p>
                                                    </div>
                                        
                                    </div>
                                </div>
                                <?php }?>
                            </div>
                        </form>


                        <!-- End of Stop buy form confirmed -->




                                            <? endif; ?>


                        <!-- Stop sell form initial -->


                                            <? if(!$ask_confirm111) : ?>
                                            <div id="stop-sell" class="tab-pane <?= $stopsell_active ?>">
                                                <form id="sell_form" action="exchange-order.php?trade=<?= $market ?>&c_currency=<?= $_REQUEST['c_currency'] ?>&currency=<?= $_REQUEST['currency'] ?>&stopsell=1" method="POST">
                                                       <?php
                                    $req_stopsell = $_REQUEST['stopsell'];
                                        if (!$req_stopsell) {
                                            $stopsell_display ="none";
                                        }else{
                                            $stopsell_display ="block";
                                        }
                                    ?>
                                    <div class="row">
                                    <div class="col-md-12" style="margin: auto;width: 100%;padding: 0;display: <?= $stopsell_display?>">
                                        <? Errors::display(); ?>
                                        <style>
                                            .errors, .notice {
                                                   color: #e23535;
                                                    list-style: none;
                                                    background: #ff000036;
                                                    padding: 10px;
                                                    position: relative;
                                                    margin: 0 auto;
                                                    font-size: 1em;
                                                    text-align: center;
                                                    max-width: 90%;
                                                    left: 1px;
                                            }
                                        </style>
                                        <?= ($notice) ? '<div class="notice">'.$notice.'</div>' : '' ?>
                                    </div>
                                </div>
                                                <div class="content">
                                       
                                        <!-- <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Stop">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><?= $currency_info['currency'] ?></span>
                                            </div>
                                        </div> -->
                                        <div class="input-group mb-3">
                                            <input name="sell_stop_price" id="sell_stop_price" type="text" class="form-control" placeholder="Limit">
                                            <!-- <input type="text" class="form-control" placeholder="Limit"> -->
                                            <div class="input-group-append">
                                                <span class="input-group-text"><?= $currency_info['currency'] ?></span>
                                            </div>
                                        </div>
                                         <div class="input-group mb-3">
                                            <input name="sell_amount11" id="sell_amount11" type="text" class="form-control" placeholder="Amount">
                                            <!-- <input type="text" class="form-control" placeholder="Amount"> -->
                                            <div class="input-group-append">
                                                <span class="input-group-text"><?= $c_currency_info['currency'] ?></span>
                                            </div>
                                        </div>
                                        <!-- <hr> -->
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Total <?= $currency_info['currency'] ?> to spend" id="sell_total1">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><?= $currency_info['currency'] ?></span>
                                            </div>
                                        </div>
                                        
                                        <input class="checkbox" name="sell_stop" id="sell_stop" type="checkbox" value="1" checked="checked" style="visibility: collapse;">
                                         <?php
                                            if(User::isLoggedIn()){
                                              include 'paxion_referal.php';
                                                if (($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50)) {
                                                    $fee_value = Stringz::currency($user_fee_bid);
                                                }else{
                                                    $fee_value = 0;                                                    
                                                }
                                            ?>
                                        <p class="info-link">Fees: <a href="#"><span id="sell_user_fee"><?= $fee_value ?></span>%</a></p><br>
                                        <input type="hidden" id="user_fee_stopsell" value="<?= $fee_value ?>"/>


                                        <!-- Stop-limit sell referral -->
                                        <?php /*if($REFERRAL == true){ ?>
                                        <input type="hidden" name="bonus_amount" id="bonus_amount" value="<? echo $bonus_amount; ?>">
                                        <label class="cont" style="color: brown;font-style:  italic;">
                                            <input 
                                            class="checkbox" 
                                            name="is_referral" 
                                            id="is_referral_sell" 
                                            onclick="calculateBuyPrice()"
                                            type="checkbox" value="1"
                                            <? if($bonous_point == 0){ echo 'disabled'; } ?>
                                             />
                                            Use your Referral Bonus

                                            <span style="float: right;margin-left: 50px;">    
                                                <? echo $cur_code; ?> <? echo $bonus_amount; ?>
                                            </span>

                                            <span class="checkmark"></span>
                                        </label>
                                        <?php }*/ ?>
                                        <!-- End of Stop-limit sell referral -->

                                        <p class="text-right">
                                             <input type="hidden" name="sell" value="1" />
                                                <input type="hidden" name="uniq" value="<?= end($_SESSION["buysell_uniq"]) ?>" />
                                                <!-- <input type="submit" name="submit" value="<?= str_replace('[c_currency]',$c_currency_info['currency'],Lang::string('sell-bitcoins')) ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-light"/> -->
                                                <input type="button" name="submit" value="<?= str_replace('[c_currency]',$c_currency_info['currency'],Lang::string('sell-bitcoins')) ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-light"  onclick="javascript:order_stop_sell();"/>                                                 
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


                                <!-- End of stop sell form initial -->


                                <!-- Stop sell form confirmed -->


                                 <? else: ?>

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
                                                        <input type="hidden" name="sell_currency" value="<?= $currency1 ?>" />
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
                                                        <input type="submit" name="submit" value="<?= Lang::string('confirm-sale') ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-primary" style="width: auto;display: inline-block;padding: 12px 30px;" />
                                                        </span>
                                                        <span>
                                                            <!-- <input id="cancel_transaction" type="submit" name="dont" value="<?= Lang::string('confirm-back') ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc" style="width: auto;display: inline-block;float: right;padding: 12px 30px;" /> -->
                                                            <input type="submit" name="dont" value="Back" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-primary" style="width: auto;display: inline-block;float: right;padding: 12px 30px;">
                                                        </span>
                                                    </div>
                                                    
                                                </form>

                                <? endif; ?>

                                <!-- End of Stop sell form confirmed -->


                                            </div>
                                           
                                        </div>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-12">

                        	<!-- Limit Buy form initial -->
                            
                            <? if(!$ask_confirm) : ?>
                                <form id="buy_form" action="exchange-order.php?trade=<?= $market ?>&c_currency=<?= $_REQUEST['c_currency'] ?>&currency=<?= $_REQUEST['currency'] ?>&buy=1" method="POST">
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
                            <div class="form-box">
                                <div class="form-head">
                                    <?php
                                    $req_buy = $_REQUEST['buy'];
                                        if (!$req_buy) {
                                            $buy_display ="none";
                                        }else{
                                            $buy_display ="block";
                                        }
                                    ?>
                                    <div class="row">
                                    <div class="col-md-12" style="margin: auto;width: 100%;padding: 0;display: <?= $buy_display?>">
                                        <? Errors::display(); ?>
                                        <style>
                                            .errors, .notice {
                                                   color: #e23535;
                                                    list-style: none;
                                                    background: #ff000036;
                                                    padding: 10px;
                                                    position: relative;
                                                    margin: 0 auto;
                                                    font-size: 1em;
                                                    text-align: center;
                                                    max-width: 90%;
                                                    left: 1px;
                                            }
                                        </style>
                                        <?= ($notice) ? '<div class="notice">'.$notice.'</div>' : '' ?>
                                    </div>
                                </div>
                                    <?php
                                    if($_REQUEST['currency'] != 27){
                                        $buy_deposite = "action=add&c_currency=".$_REQUEST['currency']."&pagevalue=1";
                                    }else{
                                        $buy_deposite = "coin=".$_REQUEST['currency']."";
                                    }
                                    ?>
                                    <h5 class="tite">BUY <span class="sell_currency_label"><?= $c_currency_info['currency'] ?></span> 
                                    <a href="deposits-withdrawls?<?= $buy_deposite ?>" class="help-link">Deposit <?= $currency_info['currency'] ?></a></h5>

                                    <p>You have : <span id="buy_user_available" class="buy_user_available" style="/* color: #2f8afd; */"><?= ((!empty($user_available[strtoupper($currency_info['currency'])])) ? Stringz::currency($user_available[strtoupper($currency_info['currency'])],($currency_info['is_crypto'] == 'Y')) : '0.00') ?></span> <span class="sell_currency_label"><?= $currency_info['currency'] ?></span></p>

                                    <p>Lowest Ask: : <span class="sell_currency_label"><?= $currency_info['currency'] ?></span></p>
                                </div>
                                <br><br>
                                <div class="form-inner1">
                                    <div class="content">
                                        <div class="input-group mb-3">
                                            <input name="buy_price" id="buy_price" type="text" class="form-control" placeholder="Price" value="<?= Stringz::currencyOutput($buy_price1) ?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><span class="sell_currency_label"><?= $currency_info['currency'] ?></span></span>
                                            </div>
                                        </div>
                                        <div class="input-group mb-3">
                                            <input name="buy_amount" id="buy_amount" type="text" class="form-control" placeholder="Amount" value="<?= Stringz::currencyOutput($buy_amount1) ?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><?= $c_currency_info['currency'] ?></span>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Total <?= $currency_info['currency'] ?> to spend" id="buy_total" value="">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><span class="sell_currency_label"><?= $currency_info['currency'] ?></span></span>
                                            </div>
                                        </div>
                                        <span id="buy_total"></span>
                                        <input class="checkbox" name="buy_limit" id="buy_limit" type="checkbox" value="1" checked="checked" style="    visibility: collapse;">
                                        <input type="hidden" name="buy" value="1" />
                                                <input type="hidden" name="buy_all" id="buy_all" value="<?= $buy_all1 ?>" />
                                                <input type="hidden" name="uniq" value="<?= end($_SESSION["buysell_uniq"]) ?>" />
                                          <?php
                                            if(User::isLoggedIn()){
                                                include 'paxion_referal.php';
                                                if (($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50)) {
                                                    $fee_value = Stringz::currency($user_fee_bid);
                                                }else{
                                                    $fee_value = 0;                                                    
                                                }
                                            ?><br>
                                            	<style>
                                            		form#buy_form .form-inner1 {
    													padding-bottom: 8px;
													}
                                            	</style>
                                        <p class="info-link">Fees:  <a href="#"><span id="buy_user_fee"><?= $fee_value ?></span>%</a></p><br><br>

                                        <!-- Buy referral -->
                                        <?php /*if($REFERRAL == true){ ?>
                                                <input type="hidden" name="ref_status" id="ref_status" value="1">
                                                <input type="hidden" name="bonus_amount" id="bonus_amount" value="<? echo $bonus_amount; ?>">
                                                <label class="cont" style="color: brown;font-style:  italic;">
                                                    <input 
                                                    class="checkbox" 
                                                    name="is_referral" 
                                                    id="is_referral" 
                                                    onclick="calculateBuyPrice()"
                                                    type="checkbox" value="1"
                                                    <? if($bonous_point == 0){ echo 'disabled'; } ?>
                                                     />
                                                    Use your Referral Bonus

                                                    <span style="float: right;margin-left: 50px;">    
                                                        <? echo $cur_code; ?> <? echo $bonus_amount; ?>
                                                    </span>

                                                    <span class="checkmark"></span>
                                                </label>
                                                <?php }*/ ?>
                                        <!-- Buy referral -->


                                        <p class="text-right">
                                       <!-- <input type="submit" name="submit" value="<?= str_replace('[c_currency]',$c_currency_info['currency'],Lang::string('buy-bitcoins')) ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-light buy_form_btn">  -->
                                       <input type="button" name="submit" value="<?= str_replace('[c_currency]',$c_currency_info['currency'],Lang::string('buy-bitcoins')) ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-light buy_form_btn" onclick="javascript:alert($('#buy_amount').val());">                                         
                                   		</p>
                                             <!-- <a href="#" class="btn btn-light">Buy</a> -->
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
                                       
                                       
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- End of Limit Buy form initial -->


                        <!-- Limit Buy form confirmed -->

                        <? else: ?>
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
                                                        <input type="submit" name="submit" value="<?= Lang::string('confirm-buy') ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-primary" style="width: auto;display: inline-block;" />
                                                        </span>
                                                        <span>
                                                            <!-- <input id="cancel_transaction" type="submit" name="dont" value="<?= Lang::string('confirm-back') ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc" style="width: auto;display: inline-block;float: right;padding: 12px 30px;" /> -->
                                                            <input id="cancel_transaction" type="submit" name="dont" value="Back" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn btn-primary" style="width: auto;display: inline-block;float: right;">
                                                        </span>
                                                        <p class="m-t-10"> By clicking CONFIRM button an order request will be created.</p>
                                                    </div>
                                        
                                    </div>
                                </div>
                                <?php }?>
                            </div>
                        </form>

                        <!-- End of Limit Buy form confirmed -->


                        <? endif; ?>
                        </div>
                        
                        <!-- Limit Sell form initial -->

                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <? if(!$ask_confirm1) : ?>
                            <div class="form-box">
                                <form id="sell_form" action="exchange-order.php?trade=<?= $market ?>&c_currency=<?= $_REQUEST['c_currency'] ?>&currency=<?= $_REQUEST['currency'] ?>&sell=1" method="POST">
                                <div class="form-head">
                                     <?php
                                    $req_sell = $_REQUEST['sell'];
                                        if (!$req_sell) {
                                            $sell_display ="none";
                                        }else{
                                            $sell_display ="block";
                                        }
                                    ?>
                                    <div class="row">
                                    <div class="col-md-12" style="margin: auto;width: 100%;padding: 0;display: <?= $sell_display?>">
                                        <? Errors::display(); ?>
                                        <style>
                                            .errors, .notice {
                                                   color: #e23535;
                                                    list-style: none;
                                                    background: #ff000036;
                                                    padding: 10px;
                                                    position: relative;
                                                    margin: 0 auto;
                                                    font-size: 1em;
                                                    text-align: center;
                                                    max-width: 90%;
                                                    left: 1px;
                                            }
                                        </style>
                                        <?= ($notice) ? '<div class="notice">'.$notice.'</div>' : '' ?>
                                    </div>
                                </div>
                                    <?php
                                    if($_REQUEST['currency'] != 27){
                                        $buy_deposite = "action=add&c_currency=".$_REQUEST['currency']."&pagevalue=1";
                                    }else{
                                        $buy_deposite = "coin=".$_REQUEST['currency']."";
                                    }
                                    ?>
                                    <h5 class="tite">SELL <?= $c_currency_info['currency'] ?> 
                                    <a href="deposits-withdrawls?<?= $buy_deposite ?>" class="help-link">Deposit <?= $c_currency_info['currency'] ?></a></h5></h5>
                                    <p>You have :<span id="sell_user_available" class="sell_user_available" style="/* color: #2f8afd; */"  ><?= Stringz::currency($user_available[strtoupper($c_currency_info['currency'])],true) ?></span> <?= $c_currency_info['currency']?></p>
                                    <p>Highest Bid: <span class="sell_currency_label"><?= $currency_info['currency'] ?></span></p>
                                </div>
                                <br><br>
                                <div class="form-inner1">
                                    <div class="content">
                                        <div class="input-group mb-3">
                                            <input name="sell_price" id="sell_price" type="text" class="form-control" placeholder="Price">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><?= $currency_info['currency'] ?></span>
                                            </div>
                                        </div>
                                        <div class="input-group mb-3">
                                            <input name="sell_amount" id="sell_amount" type="text" class="form-control" placeholder="Amount">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><?= $c_currency_info['currency'] ?></span>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Total <?= $currency_info['currency'] ?> to spend" id="sell_total">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><?= $currency_info['currency'] ?></span>
                                            </div>
                                        </div>
                                         <input class="checkbox" name="sell_limit" id="sell_limit" type="checkbox" value="1" checked="checked" style="    visibility: collapse;">
                                                    
                                         <?php
                                            if(User::isLoggedIn()){
                                                 include 'paxion_referal.php';
                                                // if ($ref_response == 0 &&  ($c_currency_id != 50 && $currency_id1 != 50)) {
                                                 if (($ref_response != 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id != 50) || ($ref_response == 0 && $c_currency_id == 50)) {
                                                     
                                                    $fee_value = Stringz::currency($user_fee_bid);
                                                }else{
                                                    $fee_value = 0;                                                    
                                                }
                                            ?><br>
                                        <p class="info-link">Fees: <a href="#"><span id="sell_user_fee"><?= $fee_value ?></span>%</a></p><br><br>


                                        <!-- Sell referral -->
                                        <?php /*if($REFERRAL == true){ ?>
                                                <input type="hidden" name="bonus_amount" id="bonus_amount" value="<? echo $bonus_amount; ?>">
                                                <label class="cont" style="color: brown;font-style:  italic;">
                                                    <input 
                                                    class="checkbox" 
                                                    name="is_referral" 
                                                    id="is_referral_sell" 
                                                    onclick="calculateBuyPrice()"
                                                    type="checkbox" value="1"
                                                    <? if($bonous_point == 0){ echo 'disabled'; } ?>
                                                     />
                                                    Use your Referral Bonus

                                                    <span style="float: right;margin-left: 50px;">    
                                                        <? echo $cur_code; ?> <? echo $bonus_amount; ?>
                                                    </span>

                                                    <span class="checkmark"></span>
                                                </label>
                                                <?php }*/ ?>
                                        <!-- End of Sell referral -->


                                        <p class="text-right">
                                             <input type="hidden" name="sell" value="1" />
                                                <input type="hidden" name="uniq" value="<?= end($_SESSION["buysell_uniq"]) ?>" />
                                                <!-- <input type="submit" name="submit" value="<?= str_replace('[c_currency]',$c_currency_info['currency'],Lang::string('sell-bitcoins')) ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-light"/> -->
                                                <input type="button" name="submit" value="<?= str_replace('[c_currency]',$c_currency_info['currency'],Lang::string('sell-bitcoins')) ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-light" onclick="javascript:alert($('#sell_amount').val());"/>                                                 
                                            
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
                                </div>
                            </form>
                            </div>

                            <!-- End of Limit Sell form initial -->


                            <? else: ?>
                               

                            <!-- Limit Sell form confirmed -->


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
                                                    <!-- <div class="current-otr">
                                                        <p style="margin-bottom:0px"><?= Lang::string('buy-subtotal') ?></p>
                                                        <h4><b><?= $currency_info['fa_symbol'] ?><?= Stringz::currency($sell_subtotal1,($currency_info['is_crypto'] == 'Y')) ?></b></h4>
                                                    </div> -->
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


                                                    <!-- <div class="current-otr">
                                                         <p style="margin-bottom:0px"><?= Lang::string('buy-fee') ?></p>
                                                        <h4><b><span id="sell_user_fee"><?= Stringz::currency($user_fee_bid) ?></span>%</b></h4>
                                                    </div> -->
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
                                                    <!-- <div class="current-otr m-b-15">
                                                        <p style="margin-bottom:0px">
                                                            <span id="sell_total_approx_label"><?= str_replace('[currency]','<span class="sell_currency_label">'.$currency_info['currency'].'</span>',Lang::string('sell-total-approx')) ?></span>
                                                        </p>
                                                        <h4>
                                                            <span id="sell_total_label" style="display:none;"><?= Lang::string('sell-total') ?></span>
                                                            <b><?= $currency_info['fa_symbol'] ?><span id="sell_total"><?= Stringz::currency($sell_total1,($currency_info['is_crypto'] == 'Y')) ?></span></b>
                                                        </h4>
                                                    </div> -->
                                        </div>
                                         <input type="hidden" name="sell" value="1" />
                                                    <input type="hidden" name="uniq" value="<?= end($_SESSION["buysell_uniq"]) ?>" />
                                                    <div class="btn-otr">
                                                        <span>
                                                        <input type="submit" name="submit" value="<?= Lang::string('confirm-sale') ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-primary" style="width: auto;display: inline-block;padding: 12px 30px;" />
                                                        </span>
                                                        <span>
                                                            <!-- <input id="cancel_transaction" type="submit" name="dont" value="<?= Lang::string('confirm-back') ?>" class="Flex__Flex-fVJVYW ghkoKS buy-btc" style="width: auto;display: inline-block;float: right;padding: 12px 30px;" /> -->
                                                            <input type="submit" name="dont" value="Back" class="Flex__Flex-fVJVYW ghkoKS buy-btc btn btn-primary" style="width: auto;display: inline-block;float: right;padding: 12px 30px;">
                                                        </span>
                                                    </div>
                                        
                                    </div>
                                </div>
                                
                            </div>
                        </form>
                            <? endif; ?>

                          <!-- End of Limit Sell form confirmed -->
                        </div>
                    </div>
                       <?php

               
        $currencies = Settings::sessionCurrency();
        // $page_title = Lang::string('order-book');
        
        $c_currency1 = $_GET['c_currency'] ? : 28;
        $currency1 = $_GET['currency'] ? : 27;

        /* $currency1 = $currencies['currency'];
        $c_currency1 = $currencies['c_currency'];
        if(!$currency1 || empty($currency1)){
            $currency1 = 13 ;
        }
        if(!$currency1 || empty($currency1)){
            $currency1 = 28 ;
        } */
        $currency_info = $CFG->currencies[$currency1];
        $c_currency_info = $CFG->currencies[$c_currency1];
        
        API::add('Orders', 'get', array(false, false, false, $c_currency1, $currency1, false, false, 1));
        API::add('Orders', 'get', array(false, false, false, $c_currency1, $currency1, false, false, false, false, 1));
        API::add('Transactions', 'get', array(false, false, 1, $c_currency1, $currency1));
        $query = API::send();
        
        $bids = $query['Orders']['get']['results'][0];
        $asks = $query['Orders']['get']['results'][1];
        // var_dump($bids); exit;
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
        $pagination = Content::pagination('transactions.php', $page1, $total, 30, 5, false);
        
        $currency_info = ($currency1) ? $CFG->currencies[strtoupper($currency1)] : array();
        
        if ($trans_realized1 > 0)
            Messages::add(str_replace('[transactions]', $trans_realized1, Lang::string('transactions-done-message')));
           

                                ?>
                    <div class="row">
                        
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        	<br><br>
                            <h5 class="order-title">BUY <?= $c_currency_info['currency'] ?> ORDERS </h5>
                            <div class="order-table color-table1" id="buy_orders_table">
                                <table class="table table-striped order-table-fixed right-data-table">
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
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <br><br>
                            <h5 class="order-title">SELL <?= $c_currency_info['currency'] ?> ORDERS </h5>
                            <div class="order-table color-table1" id="sell_orders_table">
                                <table class="table table-striped order-table-fixed right-data-table">
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
                            </div>
                        </div>
                    </div>
                    <?php
                                            if(User::isLoggedIn()){


                                                 $c_currencyy1 = $_GET['c_currency'];
                $currencyy1 = $_GET['currency'];
        $delete_id1 = (!empty($_REQUEST['delete_id'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['delete_id']) : false;
        if ($delete_id1 > 0 && $_SESSION["openorders_uniq"] == $_REQUEST['uniq']) {
            API::add('Orders','getRecord',array($delete_id1));
            $query = API::send();
            $del_order = $query['Orders']['getRecord']['results'][0];
        
            if (!$del_order) {
                Link::redirect('openorders.php?message=order-doesnt-exist');
            }
            elseif ($del_order['site_user'] != $del_order['user_id'] || !($del_order['id'] > 0)) {
                Link::redirect('openorders.php?message=not-your-order');
            }
            else {
                API::add('Orders','delete',array($delete_id1));
                $query = API::send();
                
                Link::redirect('openorders.php?message=order-cancelled&c_currency='.$c_currencyy1.'&currency='.$currencyy1.'');
            }
        }
        
        $delete_all = (!empty($_REQUEST['delete_all']));
        if ($delete_all && $_SESSION["openorders_uniq"] == $_REQUEST['uniq']) {
            API::add('Orders','deleteAll');
            $query = API::send();
            $del_order = $query['Orders']['deleteAll']['results'][0];
        
            if (!$del_order)
                Link::redirect('openorders.php?message=deleteall-error');
            else
                Link::redirect('openorders.php?message=deleteall-success');
        }
        
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
        
        $c_currency1 = $_GET['c_currency'] ? : 28;
        $currency1 = $_GET['currency'] ? : 27;
        /* $currency1 = $_SESSION['oo_currency'];
        $c_currency1 = $_SESSION['oo_c_currency']; */
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
                                            ?>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                        	<br><br>
                            <h5 class="order-title">My OPEN ORDERS</h5>
                            <div class="row">                                
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        	<br>
                            <h5 class="order-title1">BUY <?= $c_currency_info['currency'] ?> ORDERS</h5>
                            <div class="order-table color-table1" id="buy_open_orders_table">
                                <table class="table table-striped order-table-fixed right-data-table">
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

                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                                    <br>
                            <h5 class="order-title1">SELL <?= $c_currency_info['currency'] ?> ORDERS</h5>
                            <div class="order-table color-table1" id="sell_open_orders_table">
                                <table class="table table-striped order-table-fixed right-data-table">
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
                                                // <a href="edit_userbuy.php?trade=BTC-USD&order_id='.$ask['id'].'" title="'.Lang::string('orders-edit').'"><i class="fa fa-edit"></i></a>
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
                            </div>
                        </div>
                            </div>
                            
                        </div>
                    </div>
                    <?php
                        }
                    ?>

                    <?php

                     $currencies = Settings::sessionCurrency();
        // $page_title = Lang::string('order-book');
       

        $c_currency1 = $_GET['c_currency'] ? : 28;
        $currency1 = $_GET['currency'] ? : 27;


        //commented out because of not required to take currency and c_currency from session
        /* $currency1 = $currencies['currency'];
        $c_currency1 = $currencies['c_currency']; */
       /*  if(!$currency1 || empty($currency1)){
            $currency1 = 27 ;
        }
        if(!$c_currency1 || empty($c_currency1)){
            $c_currency1 = 28 ;
        } */
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
        $pagination = Content::pagination('transactions.php', $page1, $total, 30, 5, false);
        
        $currency_info = ($currency1) ? $CFG->currencies[strtoupper($currency1)] : array();
        
        if ($trans_realized1 > 0)
            Messages::add(str_replace('[transactions]', $trans_realized1, Lang::string('transactions-done-message')));
        ?>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="row">
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                	<br><br>
                                    <h5 class="order-title">TRADE HISTORY</h5>
                                </div>
                                <? // Messages::display(); ?>
                                <? // Errors::display(); ?>
                                <div class="col-md-12 col-sm-12 col-xs-12 text-right">
                                    <ul class="nav nav-tabs btn-tab" id="myTab" role="tablist">
                                        <!-- <li class="nav-item">
                                            <a class="nav-link active" id="r-btc-tab" data-toggle="tab" href="#mar-trades" role="tab" aria-controls="mar-trades" aria-selected="false">Market Trades</a>
                                        </li> -->
                                        <?php
                                            if(User::isLoggedIn()){
                                            ?>
                                        <!-- <li class="nav-item">
                                            <a class="nav-link" id="my-trades-tab" data-toggle="tab" href="#my-trades" role="tab" aria-controls="r-eth" aria-selected="false">My Trades</a>
                                        </li> -->
                                        <?php
                                            }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="mar-trades" role="tabpanel" aria-labelledby="mar-trades-tab">
                                    <div class="order-table color-table1" id="trade_history_table">
                                        <input type="hidden" id="refresh_transactions" value="1" />
                            <input type="hidden" id="page" value="<?= $page1 ?>" />
                                        <table class="table table-striped order-table-fixed right-data-table">
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
                                        <?= $pagination ?>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="my-trades" role="tabpanel" aria-labelledby="my-trades-tab">
                                    <div class="order-table color-table">
                                        <table class="table table-striped order-table-fixed">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                    <th>Price(BTC)</th>
                                                    <th>Amount(XRP)</th>
                                                    <th>Total(BTC)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>10-5-18</td>
                                                    <td>BTC</td>
                                                    <td>0.00008552</td>
                                                    <td>31983.47023933</td>
                                                    <td>2.73522637</td>
                                                </tr>
                                                <tr>
                                                    <td>10-5-18</td>
                                                    <td>BTC</td>
                                                    <td>0.00008552</td>
                                                    <td>31983.47023933</td>
                                                    <td>2.73522637</td>
                                                </tr>
                                                <tr>
                                                    <td>10-5-18</td>
                                                    <td>BTC</td>
                                                    <td>0.00008552</td>
                                                    <td>31983.47023933</td>
                                                    <td>2.73522637</td>
                                                </tr>
                                                <tr>
                                                    <td>10-5-18</td>
                                                    <td>BTC</td>
                                                    <td>0.00008552</td>
                                                    <td>31983.47023933</td>
                                                    <td>2.73522637</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-4 col-xs-12">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="tab-box">
                                <div class="tab-head">
                                    <h5 class="tite">MARKETS</h5>
                                </div>
                                <div class="tab-inner" id="market_block">
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
                                            <!-- <li class="nav-item">
                                            <?php
                                                if($active_currency_id == 44)
                                                    $class_active = "active show";
                                                else
                                                    $class_active = "";
                                            ?>
                                                <a class="nav-link <?php echo $class_active;?>" id="r-bch-tab" data-toggle="tab" href="#r-bch" role="tab" aria-controls="r-xmr" aria-selected="false">BCH</a>
                                            </li> -->
                                            <!-- <li class="nav-item">
                                            <?php
                                                if($active_currency_id == 42)
                                                    $class_active = "active show";
                                                else
                                                    $class_active = "";
                                            ?>
                                                <a class="nav-link <?php echo $class_active;?>" id="r-ltc-tab" data-toggle="tab" href="#r-ltc" role="tab" aria-controls="r-usdt" aria-selected="false">LTC</a>
                                            </li> -->
                                            <!-- <li class="nav-item">
                                            <?php
                                                if($active_currency_id == 43)
                                                    $class_active = "active show";
                                                else
                                                    $class_active = "";
                                            ?>
                                                <a class="nav-link <?php echo $class_active;?>" id="r-zec-tab" data-toggle="tab" href="#r-zec" role="tab" aria-controls="r-usdt" aria-selected="false">ZEC</a>
                                            </li> -->
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
                                                            // echo  $active_currency_id; 
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
                                                            <!-- <th>Volume</th> -->
                                                            <th>Change</th>
                                                            <!-- <th>Name</th> -->
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
                                                    <!-- <tr class="clickable-row <?= ($c_currency_info['currency'] == 'USDT' && $currency_info['currency'] == 'BTC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=USDT-BTC&c_currency=51&currency=28">
                                                        <td>
                                                            <div class="star-inner text-left">
                                                                <input id="star1" type="checkbox" name="time" />
                                                                <label for="star1"></label>
                                                                USDT/BTC
                                                            </div>
                                                        </td>
                                                        <td><span class="green-color"><?= $transactions_24hrs_usdt_btc['lastPrice'] ?></span> </td>
                                                        <td><span class="red-color"><?= $transactions_24hrs_usdt_btc['change_24hrs'] ?></span></td>
                                                    </tr> -->
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
                                                    <!-- <tr class="clickable-row <?= ($c_currency_info['currency'] == 'USDT' && $currency_info['currency'] == 'LTC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=USDT-LTC&c_currency=51&currency=42">
                                                        <td>
                                                            <div class="star-inner text-left">
                                                                <input id="star1" type="checkbox" name="time" />
                                                                <label for="star1"></label>
                                                                USDT/LTC
                                                            </div>
                                                        </td>
                                                        <td><span class="green-color"><?= $transactions_24hrs_usdt_ltc['lastPrice'] ?></span> </td>
                                                        <td><span class="red-color"><?= $transactions_24hrs_usdt_ltc['change_24hrs'] ?></span></td>
                                                    </tr> -->
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
                                                    <!-- <tr class="clickable-row <?= ($c_currency_info['currency'] == 'USDT' && $currency_info['currency'] == 'BCH') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=USDT-BCH&c_currency=51&currency=44">
                                                        <td>
                                                            <div class="star-inner text-left">
                                                                <input id="star1" type="checkbox" name="time" />
                                                                <label for="star1"></label>
                                                                USDT/BCH
                                                            </div>
                                                        </td>
                                                        <td><span class="green-color"><?= $transactions_24hrs_usdt_bch['lastPrice'] ?></span> </td>
                                                        <td><span class="red-color"><?= $transactions_24hrs_usdt_bch['change_24hrs'] ?></span></td>
                                                    </tr> -->
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
                                                    <!-- <tr class="clickable-row <?= ($c_currency_info['currency'] == 'USDT' && $currency_info['currency'] == 'ZEC') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=USDT-ZEC&c_currency=51&currency=43">
                                                        <td>
                                                            <div class="star-inner text-left">
                                                                <input id="star1" type="checkbox" name="time" />
                                                                <label for="star1"></label>
                                                                USDT/ZEC
                                                            </div>
                                                        </td>
                                                        <td><span class="green-color"><?= $transactions_24hrs_usdt_zec['lastPrice'] ?></span> </td>
                                                        <td><span class="red-color"><?= $transactions_24hrs_usdt_zec['change_24hrs'] ?></span></td>
                                                    </tr> -->
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
                                                
                                                    <!-- <tr class="clickable-row <?= ($c_currency_info['currency'] == 'BTH' && $currency_info['currency'] == 'ETH') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=BTC-ETH&c_currency=28&currency=45">
                                                        <td>
                                                            <div class="star-inner text-left">
                                                                <input id="star1" type="checkbox" name="time" />
                                                                <label for="star1"></label>
                                                                BTC/ETH
                                                            </div>
                                                        </td>
                                                        <td><span class="green-color"><?= $transactions_24hrs_btc_eth['lastPrice'] ?></span> </td>
                                                        <td><span class="red-color"><?= $transactions_24hrs_btc_eth['change_24hrs'] ?></span></td>
                                                    </tr> -->
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
                                                    <!-- <tr class="clickable-row <?= ($c_currency_info['currency'] == 'USDT' && $currency_info['currency'] == 'ETH') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=USDT-ETH&c_currency=51&currency=45">
                                                        <td>
                                                            <div class="star-inner text-left">
                                                                <input id="star1" type="checkbox" name="time" />
                                                                <label for="star1"></label>
                                                                USDT/ETH
                                                            </div>
                                                        </td>
                                                        <td><span class="green-color"><?= $transactions_24hrs_usdt_eth['lastPrice'] ?></span> </td>
                                                        <td><span class="red-color"><?= $transactions_24hrs_usdt_eth['change_24hrs'] ?></span></td>
                                                    </tr> -->
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
                                                    <!-- <tr class="clickable-row <?= ($c_currency_info['currency'] == 'BTH' && $currency_info['currency'] == 'IOX') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=BTC-IOX&c_currency=28&currency=50">
                                                        <td>
                                                            <div class="star-inner text-left">
                                                                <input id="star1" type="checkbox" name="time" />
                                                                <label for="star1"></label>
                                                                BTC/IOX
                                                            </div>
                                                        </td>
                                                        <td><span class="green-color"><?= $transactions_24hrs_btc_iox['lastPrice'] ?></span> </td>
                                                        <td><span class="red-color"><?= $transactions_24hrs_btc_iox['change_24hrs'] ?></span></td>
                                                    </tr> -->
                                                    
                                                    
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
                                                    <!-- <tr class="clickable-row <?= ($c_currency_info['currency'] == 'ETH' && $currency_info['currency'] == 'IOX') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=ETH-IOX&c_currency=45&currency=50">
                                                        <td>
                                                            <div class="star-inner text-left">
                                                                <input id="star1" type="checkbox" name="time" />
                                                                <label for="star1"></label>
                                                                ETH/IOX
                                                            </div>
                                                        </td>
                                                        <td><span class="green-color"><?= $transactions_24hrs_eth_iox['lastPrice'] ?></span> </td>
                                                        <td><span class="red-color"><?= $transactions_24hrs_eth_iox['change_24hrs'] ?></span></td>
                                                    </tr> -->
                                                    <!-- <tr class="clickable-row <?= ($c_currency_info['currency'] == 'USDT' && $currency_info['currency'] == 'IOX') ? 'userbuy-active' : "" ?>" data-href="exchange?trade=USDT-IOX&c_currency=51&currency=50">
                                                        <td>
                                                            <div class="star-inner text-left">
                                                                <input id="star1" type="checkbox" name="time" />
                                                                <label for="star1"></label>
                                                                USDT/IOX
                                                            </div>
                                                        </td>
                                                        <td><span class="green-color"><?= $transactions_24hrs_usdt_iox['lastPrice'] ?></span> </td>
                                                        <td><span class="red-color"><?= $transactions_24hrs_usdt_iox['change_24hrs'] ?></span></td>
                                                    </tr> -->
                                                    </tbody>
                                                </table>
                                            </div>

                                                <!-- IOX Stop -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="news-box">
                                <div class="news-head">
                                    <h5 class="tite">NOTICES</h5>
                                </div>
                                <div class="news-inner">
                                    <div class="content">
                                        <a class="twitter-timeline" href="https://twitter.com/Google?ref_src=twsrc%5Etfw">Tweets by Google</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include "bitniex/bitnex_footer.php"; ?>
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js">
    </script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js">
    </script>
    <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
    <!-- Color Switcher -->
    <script type="text/javascript" src="bitniex/js/jquery.colorpanel.js"></script>
    <!-- Custom Scripts -->
    <script type="text/javascript" src="bitniex/js/script.js"></script>
</body>
<!-- <script type="text/javascript">
new TradingView.widget({
    "autosize": true,
    "symbol": "Binance:TRXBTC",
    "interval": "D",
    "timezone": "Etc/UTC",
    "theme": "Light",
    "style": "1",
    "locale": "en",
    "toolbar_bg": "#f1f3f6",
    "enable_publishing": false,
    "save_image": false,
    "hideideas": true,
    "container_id": "tradingview_35f2b"
});
</script> -->
<script type="text/javascript">
$(document).ready(function() {
    $('.right-data-table').DataTable({
        // "dom": '<"top"i>rt<"bottom"flp><"clear">'
        // "sDom": "lfrti",
        // "paging":   false,
        // "ordering": false,
        // "info":     false
    });
     var r = 1;
               if (r == 1) {
                    $(".page-link").click(function(e){
                        table.page( 'next' ).draw( 'page' );
                    });
                    r = 2;
               }
});
</script>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#colorPanel').ColorPanel({
        styleSheet: '#cpswitch',
        animateContainer: '#wrapper',
        colors: {            
            '#4b77be': 'bitniex/css/skins/blue.css',
            '#000000': 'bitniex/css/skins/black.css',
            '#0b7076': 'bitniex/css/skins/default.css',
            '#c0392c': 'bitniex/css/skins/red.css',
            '#16a085': 'bitniex/css/skins/seagreen.css',
        }
    });
});
</script>
 <script type="text/javascript" src="js/ops_new1.js?v=20160210"></script>
  <script>
    $(document).ready(function(){

        $('.clickable-row').on('click', function(){
            var href = $(this).data('href');
            window.location.href = href;
        })

    });
    
    // var interval = setInterval(function(){
        
    //     if($(".tradingview-widget-container").html() != "") {
    //         $(".tradingview-widget-container").show();
    //         clearInterval(interval);
    //     }
    // }, 100)
    </script> 
    <script>
        $(document).ready(function(){

        $('.buy-active').on('click', function(){
             $('.sell-active').removeClass("active");
              $('.buy-active').addClass("active");
        })
        $('.sell-active').on('click', function(){
             $('.buy-active').removeClass("active");
              $('.sell-active').addClass("active");
        })
        $('#buy_amount11').keypress(function(e) {
            var lengths = $(this).val();
            if(lengths.length >= 1){
                calculateBuyPrice();
            }
            });
        $('#sell_amount11').keypress(function(e) {
            var lengths = $(this).val();
            if(lengths.length >= 1){
                calculateBuyPrice();
            }
            });

    });
    
    </script>

    <script>
        // $('.buy_form_btn').on('click', function(){
        //     alert();
        // })

        function order_stop_buy()
        {
            var c_currency='<?php echo $_REQUEST['c_currency']; ?>';
            var currency='<?php echo $_REQUEST['currency']; ?>';
            var trade='<?php echo $market; ?>';
            var stopbuy=1;
            var uniq='<?php end($_SESSION["buysell_uniq"]); ?>'; 
            var buyall='<?php echo $buy_all1; ?>';                   
            var buy=1;
            var buy_stop_price=$('#buy_stop_price').val();
            var buy_amount11=$('#buy_amount11').val();
            var buy_total1=$('#buy_total1').val();                    
            var sell_user_fee=$('#user_fee_stopbuy').val();    
            var buy_stop=1;        
                        
            $.ajax ({
               type: "POST",
               url: 'order-ajax.php',
               data: {                                
                        'c_currency':c_currency,
                        'currency':currency,
                        'trade':trade,
                        'stopbuy':stopbuy,
                        'uniq':uniq,
                        'buyall':buyall,
                        'buy':buy,
                        'buy_stop_price':buy_stop_price,
                        'buy_amount11':buy_amount11,
                        'buy_total1':buy_total1,
                        'sell_user_fee':sell_user_fee,
                        'buy_stop':buy_stop,
                     },
               success: function(respon)
               {
                console.log(respon);
                $('#stop-buy').html(respon);
               }
           })

        }

        function order_stop_sell()
        {
            var c_currency='<?php echo $_REQUEST['c_currency']; ?>';
            var currency='<?php echo $_REQUEST['currency']; ?>';
            var trade='<?php echo $market; ?>';
            var stopsell=1;
            var uniq='<?php end($_SESSION["buysell_uniq"]); ?>'; 
            var buyall='<?php echo $buy_all1; ?>';                   
            var sell=1;
            var sell_stop_price=$('#sell_stop_price').val();
            var sell_amount11=$('#sell_amount11').val();
            var sell_total1=$('#sell_total1').val();                    
            var sell_user_fee=$('#user_fee_stopsell').val();    
            var sell_stop=1;        
                        
            $.ajax ({
               type: "POST",
               url: 'order-ajax.php',
               data: {                                
                        'c_currency':c_currency,
                        'currency':currency,
                        'trade':trade,
                        'stopsell':stopsell,
                        'uniq':uniq,
                        'buyall':buyall,
                        'sell':sell,
                        'sell_stop_price':sell_stop_price,
                        'sell_amount11':sell_amount11,
                        'sell_total1':sell_total1,
                        'sell_user_fee':sell_user_fee,
                        'sell_stop':sell_stop,
                     },
               success: function(respon)
               {
                console.log(respon);
                $('#stop-sell').html(respon);
               }
           })

        }

        function order_stop_buy_confirm()
        {
            var c_currency='<?php echo $_REQUEST['c_currency']; ?>';
            var currency='<?php echo $_REQUEST['currency']; ?>';
            var trade='<?php echo $market; ?>';
            // var stopbuy=1;
            var uniq='<?php end($_SESSION["buysell_uniq"]); ?>';
            var buyall='<?php echo $buy_all1; ?>';                   
            var buy=1;
            var buy_stop_price=$('#buy_stop_price').val();
            var buy_amount=$('#buy_amount').val();
            var buy_currency=$('#buy_currency').val();
            var buy_total1=$('#buy_total1').val();                    
            var sell_user_fee=$('#user_fee_stopbuy').val();    
            var buy_stop=1;
            var confirmed=1;        
            var cancel=0;        
            var ref_status=$('#ref_status').val();    
            var bonus_amount=$('#bonus_amount').val(); 
            var ischecked= $('#is_referral').is(':checked');
            if(ischecked) 
            { 
                var is_referral=1;   
            }
                        
            $.ajax ({
               type: "POST",
               url: 'order-ajax-confirm.php',
               data: {                                
                        'c_currency':c_currency,
                        'currency':currency,
                        'trade':trade,
                        // 'stopbuy':stopbuy,
                        'uniq':uniq,
                        'buyall':buyall,
                        'buy':buy,
                        'buy_stop_price':buy_stop_price,
                        'buy_amount':buy_amount,
                        'buy_currency':buy_currency,
                        'buy_total1':buy_total1,
                        'sell_user_fee':sell_user_fee,
                        'buy_stop':buy_stop,
                        'confirmed':confirmed,
                        'cancel':cancel,
                        'ref_status':ref_status,
                        'bonus_amount':bonus_amount,
                        'is_referral':is_referral,
                     },
               success: function(respon)
               {
                    console.log(respon);
                    respon=respon.split('~');                    
                    if(respon[0]==1)
                    {
                    $('#stop-buy').html(respon[1]);

                    // Chart header
                    $('#chart_header').html(respon[2]);
                    // End of Chart header

                    // Chart Block
                    var n_chart_data_n = [];
                    var api_url = "chart_json.php?currency=<?php echo $currency_id1;?>&c_currency=<?php echo $c_currency_id; ?>";
                    $.ajax({
                    type: "GET",
                    url: api_url,
                    dataType:'json',
                    success: function(data){
                    console.log(data.Data);
                    data.Data.forEach(function(element) {
                    console.log(element);
                    var newDate = new Date(element.date*1000);
                    n_chart_data_n.push( {
                    "date": newDate,
                    "value": element.btc_price,
                    "volume": element.btc_before
                    } );
                    console.log("single Data :"+n_chart_data_n);
                    });
                    var data_condole = data.Data;
                    console.log("hello :"+$(data_condole).length);
                    if($(data_condole).length == 0){
                    n_chart_data_n.push( {
                    "date": 0,
                    "value": 0,
                    "volume": 0
                    });
                    }
                    var chart = AmCharts.makeChart( "chartdiv", {
                    "type": "stock",
                    "theme": "light",
                    "categoryAxesSettings": {
                    "minPeriod": "mm"
                    },

                    "dataSets": [ {
                    "color": "#b0de09",
                    "fieldMappings": [ {
                    "fromField": "value",
                    "toField": "value"
                    }, {
                    "fromField": "volume",
                    "toField": "volume"
                    } ],

                    "dataProvider": n_chart_data_n,
                    "categoryField": "date"
                    } ],

                    "panels": [ {
                    "showCategoryAxis": false,
                    "title": "Value",
                    "percentHeight": 70,

                    "stockGraphs": [ {
                    "id": "g1",
                    "valueField": "value",
                    "type": "smoothedLine",
                    "lineThickness": 2,
                    "bullet": "round"
                    } ],


                    "stockLegend": {
                    "valueTextRegular": " ",
                    "markerType": "none"
                    }
                    }, {
                    "title": "Volume",
                    "percentHeight": 30,
                    "stockGraphs": [ {
                    "valueField": "volume",
                    "type": "column",
                    "cornerRadiusTop": 2,
                    "fillAlphas": 1
                    } ],

                    "stockLegend": {
                    "valueTextRegular": " ",
                    "markerType": "none"
                    }
                    } ],

                    "chartScrollbarSettings": {
                    "graph": "g1",
                    "usePeriod": "10mm",
                    "position": "top"
                    },

                    "chartCursorSettings": {
                    "valueBalloonsEnabled": true
                    },

                    "periodSelector": {
                    "position": "top",
                    "dateFormat": "YYYY-MM-DD JJ:NN",
                    "inputFieldWidth": 150,
                    "periods": [ {
                    "period": "hh",
                    "count": 1,
                    "label": "1 hour"
                    }, {
                    "period": "hh",
                    "count": 2,
                    "label": "2 hours"
                    }, {
                    "period": "hh",
                    "count": 5,
                    "selected": true,
                    "label": "5 hour"
                    }, {
                    "period": "hh",
                    "count": 12,
                    "label": "12 hours"
                    }, {
                    "period": "MAX",
                    "label": "MAX"
                    } ]
                    },

                    "panelsSettings": {
                    "usePrefixes": true
                    },

                    "export": {
                    "enabled": true,
                    "position": "bottom-right"
                    }
                    } );


                    }
                    });

                    function addPanel() {
                    var chart = AmCharts.charts[ 0 ];
                    if ( chart.panels.length == 1 ) {
                    var newPanel = new AmCharts.StockPanel();
                    newPanel.allowTurningOff = true;
                    newPanel.title = "Volume";
                    newPanel.showCategoryAxis = false;

                    var graph = new AmCharts.StockGraph();
                    graph.valueField = "volume";
                    graph.fillAlphas = 0.15;
                    newPanel.addStockGraph( graph );

                    var legend = new AmCharts.StockLegend();
                    legend.markerType = "none";
                    legend.markerSize = 0;
                    newPanel.stockLegend = legend;

                    chart.addPanelAt( newPanel, 1 );
                    chart.validateNow();
                    }
                    }

                    function removePanel() {
                    var chart = AmCharts.charts[ 0 ];
                    if ( chart.panels.length > 1 ) {
                    chart.removePanel( chart.panels[ 1 ] );
                    chart.validateNow();
                    }
                    }
                    // End of Chart Block

                    // Market Block
                    $('#market_block').html(respon[3]);
                    // End of Market Block

                    // Balance Block
                    $('.buy_user_available').html(respon[4]);
                    $('.sell_user_available').html(respon[5]);
                    // End of Balance Block

                    // buy_orders_table
                    $('#buy_orders_table').html(respon[6]);
                    // End of buy_orders_table

                    // sell_orders_table
                    $('#sell_orders_table').html(respon[7]);
                    // End of sell_orders_table

                    // buy_open_orders_table
                    $('#buy_open_orders_table').html(respon[8]);
                    // End of buy_open_orders_table

                    // sell_open_orders_table
                    $('#sell_open_orders_table').html(respon[9]);
                    // End of sell_open_orders_table

                    // trade_history_table
                    $('#trade_history_table').html(respon[10]);
                    // End of trade_history_table

                    }
                    else
                    {
                    $('#stop-buy').html(respon);
                    }
               }
           })

        } 
        function order_stop_buy_cancel()
        {
            var c_currency='<?php echo $_REQUEST['c_currency']; ?>';
            var currency='<?php echo $_REQUEST['currency']; ?>';
            var trade='<?php echo $market; ?>';
            // var stopbuy=1;
            var uniq='<?php end($_SESSION["buysell_uniq"]); ?>';
            var buyall='<?php echo $buy_all1; ?>';                   
            var buy=1;
            var buy_stop_price=$('#buy_stop_price').val();
            var buy_amount=$('#buy_amount').val();
            var buy_currency=$('#buy_currency').val();
            var buy_total1=$('#buy_total1').val();                    
            var sell_user_fee=$('#user_fee_stopbuy').val();    
            var buy_stop=1;
            var confirmed=0;
            var cancel=1;        
            var ref_status=$('#ref_status').val();    
            var bonus_amount=$('#bonus_amount').val(); 
            var ischecked= $('#is_referral').is(':checked');
            if(ischecked) 
            { 
                var is_referral=1;   
            }
                        
            $.ajax ({
               type: "POST",
               url: 'order-ajax-cancel.php',
               data: {                                
                        'c_currency':c_currency,
                        'currency':currency,
                        'trade':trade,
                        // 'stopbuy':stopbuy,
                        'uniq':uniq,
                        'buyall':buyall,
                        'buy':buy,
                        'buy_stop_price':buy_stop_price,
                        'buy_amount':buy_amount,
                        'buy_currency':buy_currency,
                        'buy_total1':buy_total1,
                        'sell_user_fee':sell_user_fee,
                        'buy_stop':buy_stop,
                        'confirmed':confirmed,
                        'cancel':cancel,
                        'ref_status':ref_status,
                        'bonus_amount':bonus_amount,
                        'is_referral':is_referral,
                     },
               success: function(respon)
               {
                    console.log(respon);
                    // respon=respon.split('~');
                    $('#stop-buy').html(respon);
                }
           })
        }       
    </script>
</html>