<!DOCTYPE html>
<html lang="en">
<?php
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', 1);
    include '../lib/common.php';
    
    $conn = new mysqli("localhost","root","xchange123","bitexchange");

    if (User::$info['locked'] == 'Y' || User::$info['deactivated'] == 'Y') {
        Link::redirect('userprofile.php');
    } elseif (User::$awaiting_token) {
        Link::redirect('verify-token.php');
    } elseif (!User::isLoggedIn()) {
        Link::redirect('login.php');
    }

    $cur_sql = "SELECT * FROM currencies";
    $currency_query = mysqli_query($conn,$cur_sql); 
    $currency_id = $_REQUEST['currency'];
    if (!$currency_id) {
       // $currency_id = 28;
       // $_REQUEST['currency'] = 28;
    }

     
        // if ($_REQUEST['message']) {
        //         Messages::add(Lang::string($_REQUEST['message']));
        // }
    /*  Displaying Balance Start */
    
    /**
     * fetching loggin user data
     */
    API::add('User','getInfo',array($_SESSION['session_id']));
    $fetchUserDataQuery = API::send();
    $user_data = $fetchUserDataQuery['User']['getInfo']['results'][0];
    $user_id = $user_data['id']; 
    
    API::add('User','getAvailable');
    API::add('User','getUserBalance', array($user_id,27)); //usd
    API::add('User','getUserBalance', array($user_id,28)); //btc
    API::add('User','getUserBalance', array($user_id,44)); //bch
    API::add('User','getUserBalance', array($user_id,45)); //eth
    API::add('User','getUserBalance', array($user_id,42)); //ltc
    API::add('User','getUserBalance', array($user_id,43)); //zec
    API::add('User','getUserBalance', array($user_id,50)); //iox
    API::add('User','getUserBalance', array($user_id,51)); //usdt
    API::add('User','getUserBalance', array($user_id,52)); //poly
    
    //fetching last 24 hrs transactions data
    API::add('Transactions','get24hData',array(28,51)); //btc
    API::add('Transactions','get24hData',array(42,51)); //ltc
    API::add('Transactions','get24hData',array(44,51)); //bch
    API::add('Transactions','get24hData',array(45,51)); //eth
    API::add('Transactions','get24hData',array(43,51)); //zec
    API::add('Transactions','get24hData',array(50,51)); //iox
    API::add('Transactions','get24hData',array(51,28)); //usdt
    API::add('Transactions','get24hData',array(52,27)); //poly

////////////////////        Added For Balances On Hold       ////////////////////////////////
    API::add('User','getOnHold');
    API::add('User','getVolume');
    API::add('FeeSchedule','getRecord',array(User::$info['fee_schedule']));
    API::add('Stats','getBTCTraded',array($_SESSION['c_currency']));
    API::add('Currencies','getMain');
   
///////////////////////////////////////////////////////////////////////////////////////// 
    foreach ($CFG->currencies as $key => $currency) {
        if (is_numeric($key) || $currency['is_crypto'] != 'Y') { continue; }
        API::add('Stats','getCurrent',array($currency['id'], 27));
    }
    
    $query = API::send();
    
    $usdtoall = $query['Stats']['getCurrent']['results'];
    
    foreach ($usdtoall as $row) {
    $checkusd[$row['market']] = $row;
    }
    $user_available = $query['User']['getAvailable']['results'][0];
    $user_balances = $query['User']['getUserBalance']['results'];
/////////////////           Added For Balances On Hold       ////////////////////////////////
    $currencies = $CFG->currencies;
    $on_hold = $query['User']['getOnHold']['results'][0];
    $available = $query['User']['getAvailable']['results'][0];
    $volume = $query['User']['getVolume']['results'][0];
    $fee_bracket = $query['FeeSchedule']['getRecord']['results'][0];
    $total_btc_volume = $query['Stats']['getBTCTraded']['results'][0][0]['total_btc_traded'];
    $main = $query['Currencies']['getMain']['results'][0];
////////////////////////////////////////////////////////////////////////////////////////////////////
     
    $transactions_24hrs_btc_usd1 = $query['Transactions']['get24hData']['results'][0] ;
    $transactions_24hrs_ltc_usd1 = $query['Transactions']['get24hData']['results'][1] ;
    $transactions_24hrs_bch_usd1 = $query['Transactions']['get24hData']['results'][2] ;
    $transactions_24hrs_eth_usd1 = $query['Transactions']['get24hData']['results'][3] ;
    $transactions_24hrs_zec_usd1 = $query['Transactions']['get24hData']['results'][4] ;
    $transactions_24hrs_iox_usd1 = $query['Transactions']['get24hData']['results'][5] ;
    $transactions_24hrs_usdt_usd1 = $query['Transactions']['get24hData']['results'][6] ;
    $transactions_24hrs_poly_usd1 = $query['Transactions']['get24hData']['results'][7] ;
    
    $user_balances_usd1 = $user_available['USD'];
    $user_balances_btc1 = $user_available['BTC'];
    $user_balances_bch1 = $user_available['BCH'];
    $user_balances_eth1 = $user_available['ETH'];
    $user_balances_ltc1 = $user_available['LTC'];
    $user_balances_zec1 = $user_available['ZEC'];
    $user_balances_iox1 = $user_available['IOX'];
    $user_balances_usdt1 = $user_available['USDT'];
    $user_balances_poly1 = $user_available['POLY'];


    /* Displaying Balance End */


    /* Creating Address Start */

    if ((!empty($_REQUEST['c_currency']) && array_key_exists(strtoupper($_REQUEST['c_currency']),$CFG->currencies)))
    $_SESSION['ba_c_currency'] = $_REQUEST['c_currency'];
else if (empty($_SESSION['ba_c_currency']))
    $_SESSION['ba_c_currency'] = $_SESSION['c_currency'];


$c_currency = $_SESSION['ba_c_currency'];
$pagevalue = $_REQUEST['pagevalue'];
API::add('BitcoinAddresses','get',array(false,$c_currency,false,30,1));
API::add('Content','getRecord',array('bitcoin-addresses'));
$query = API::send();
// print_r($query);echo "<br><br>".$c_currency;
$bitcoin_addresses = $query['BitcoinAddresses']['get']['results'][0];
$content = $query['Content']['getRecord']['results'][0];

// echo count($bitcoin_addresses);exit;

if($query['BitcoinAddresses']['get']['results'][0] == ""){

    if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'add' || $_SESSION["btc_uniq"] == $_REQUEST['uniq']) {
        if (strtotime($bitcoin_addresses[0]['date']) >= strtotime('-1 day'))
            Errors::add('You can only add one new '.$CFG->currencies[$c_currency]['currency'] .' address every 24 hours.');
    
        if (!is_array(Errors::$errors)) {
            echo "string1";
            if($c_currency == 52 && $_REQUEST['c_currency'] == 52 || $_REQUEST['pagevalue'] == 1)
        {
            echo "string";

          
          
            $url = "http://18.217.104.193:8546/";
          

          $ch = curl_init( $url );
          # Setup request to send json via POST.
          // $sub_params = array($currency_name, $from_address, $password, $_REQUEST['btc_address'], $_REQUEST['btc_amount']);
          $sub_params = array('password');
          $params = array(
              'jsonrpc' => '2.0', 
              'method' => 'createAddressForToken', 
              'params' => $sub_params, 
              'id' => 1, 
          );
          
          $payload = json_encode($params);
           
          curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
          
          curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
          
          # Return response instead of printing.
          curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
          
          # Send request.
          $result = curl_exec($ch);
          
          curl_close($ch);
          
          if ($is_error) {
              unset($is_error);          
          }

          $result = json_decode($result);
          $result_password = $result->result->password;
          $result_address = $result->result->address;
          print_r($result);
          // if ($result->result->error) {
          //     $is_error = $result->result->error->message;
          //     Errors::add($result->result->error->message);
          
          // }

          if ($result) {
              
                API::add('BitcoinAddresses','getNewMethode',array($c_currency,$result_address,$result_password));
                API::add('BitcoinAddresses','get',array(false,$c_currency,false,30,1));
                $query = API::send();
                $bitcoin_addresses = $query['BitcoinAddresses']['get']['results'][0];
          }
            
        }else{
            API::add('BitcoinAddresses','getNew',array($c_currency));
            API::add('BitcoinAddresses','get',array(false,$c_currency,false,30,1));
            $query = API::send();
            $bitcoin_addresses = $query['BitcoinAddresses']['get']['results'][0];
        }
        
        
        Messages::add(Lang::string('bitcoin-addresses-added'));
            
           
        }
    }
}
    /* Creating Address End */


    
    /* Send and Receive Bitcoin Address Start  */

    $page1 = (!empty($_REQUEST['page'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['page']) : false;
        $currencies = Settings::sessionCurrency();
        API::add('BankAccounts','get');
        API::add('User','getAvailable');
        API::add('BitcoinAddresses','get',array(false,$currencies['c_currency'],false,1,1));
        API::add('Content','getRecord',array('deposit-bank-instructions'));
        API::add('Content','getRecord',array('deposit-no-bank'));
        API::add('Wallets','getWallet',array($currencies['c_currency']));
        foreach ($CFG->currencies as $key => $currency) {
            if (is_numeric($key) || $currency['is_crypto'] != 'Y')
                continue;
                
            API::add('Stats','getCurrent',array($currency['id'], 27));
        }


        API::add('Transactions','get24hData',array(28,51)); //btc
        API::add('Transactions','get24hData',array(42,51)); //ltc
        API::add('Transactions','get24hData',array(44,51)); //bch
        API::add('Transactions','get24hData',array(45,51)); //eth
        API::add('Transactions','get24hData',array(43,51)); //zec
        API::add('Transactions','get24hData',array(50,51)); //iox
        API::add('Transactions','get24hData',array(52,27)); //poly
        // API::add('Transactions','get24hData',array(51,28)); //usdt
        
        $query = API::send();
        // print_r($query);
        $transactions_24hrs_btc_usd = $query['Transactions']['get24hData']['results'][0] ;
        $transactions_24hrs_ltc_usd = $query['Transactions']['get24hData']['results'][1] ;
        $transactions_24hrs_bch_usd = $query['Transactions']['get24hData']['results'][2] ;
        $transactions_24hrs_eth_usd = $query['Transactions']['get24hData']['results'][3] ;
        $transactions_24hrs_zec_usd = $query['Transactions']['get24hData']['results'][4] ;
        $transactions_24hrs_iox_usd = $query['Transactions']['get24hData']['results'][5] ;
        $transactions_24hrs_usdt_usd = $query['Transactions']['get24hData']['results'][6] ;
        $transactions_24hrs_poly_usd = $query['Transactions']['get24hData']['results'][7] ;

        // print_r($transactions_24hrs_iox_usd);
       
        $inrtoall = $query['Stats']['getCurrent']['results'];
        
        foreach ($inrtoall as $row) {
            $checkinr[$row['market']] = $row;
        }
        
        $bank_accounts = $query['BankAccounts']['get']['results'][0];
        $bitcoin_addresses = $query['BitcoinAddresses']['get']['results'][0];
        $user_available = $query['User']['getAvailable']['results'][0];
        // echo "<pre>"; print_r($user_available); exit;
        
        $wallet = $query['Wallets']['getWallet']['results'][0];
        $c_currency_info = $CFG->currencies[$currencies['c_currency']];
        $btc_address1 = (!empty($_REQUEST['btc_address'])) ?  preg_replace("/[^\da-z]/i", "",$_REQUEST['btc_address']) : false;
        // echo "string ".$btc_address1; exit;
        $btc_amount1 = (!empty($_REQUEST['btc_amount'])) ? Stringz::currencyInput($_REQUEST['btc_amount']) : 0;
        $btc_total1 = ($btc_amount1 > 0) ? $btc_amount1 - $wallet['bitcoin_sending_fee'] : 0;
        $account1 = (!empty($_REQUEST['account'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['account']) : false;
        $fiat_amount1 = (!empty($_REQUEST['fiat_amount'])) ? Stringz::currencyInput($_REQUEST['fiat_amount']) : 0;
        $fiat_total1 = ($fiat_amount1 > 0) ? $fiat_amount1 - $CFG->fiat_withdraw_fee : 0;
        $token1 = (!empty($_REQUEST['token'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['token']) : false;
        $authcode1 = (!empty($_REQUEST['authcode'])) ? $_REQUEST['authcode'] : false;
        $request_2fa = false;
        $no_token = false;
        
        if ($authcode1) {
            API::add('Requests','emailValidate',array(urlencode($authcode1)));
            $query = API::send();
        
            if ($query['Requests']['emailValidate']['results'][0]) {
                Link::redirect('deposits-withdrawls?message=withdraw-2fa-success');
            }
            else {
                Errors::add(Lang::string('settings-request-expired'));
            }
        }
        API::add('Requests','get',array(1,false,false,1));
        API::add('Requests','get',array(false,$page1,15,1));
        $query = API::send();
        
        $withdraw_requests = $query['Requests']['get']['results'][1];
        // echo "<pre>"; print_r($withdraw_requests); exit;
        
        API::add('Requests','get',array(1));
        API::add('Requests','get',array(false,$page1,15));
        $query = API::send();
        $deposit_requests = $query['Requests']['get']['results'][1];
        // echo "<pre>"; print_r($deposit_requests); exit;
        
        if ($CFG->withdrawals_status == 'suspended')
            Errors::add(Lang::string('withdrawal-suspended'));
        
        if ($btc_address1)
            API::add('BitcoinAddresses','validateAddress',array($currencies['c_currency'],$btc_address1));
            $query = API::send();
         // echo "<pre>"; print_r($query['BitcoinAddresses']['validateAddress']['results']);
        
        if (!empty($_REQUEST['bitcoins'])) {
            
              // ETH Token Send 
     
 if (isset($_REQUEST['c_currency']) && $_REQUEST['c_currency'] == 52) {
                // get user balance
          API::add('BitcoinAddresses','getUserBalance',array($_REQUEST['c_currency']));
          $query1 = API::send();
          $user_balance = $query1['BitcoinAddresses']['getUserBalance']['results'][0][0]['balance'];
          // print_r($user_balance >= $_REQUEST['btc_amount']);exit;
        if ($user_balance >= $_REQUEST['btc_amount']) {
            # code...
    
      // echo "hello1<br>";
          API::add('BitcoinAddresses','erc20_config',array($_REQUEST['c_currency']));
          $query = API::send();
          $contract_address = $query['BitcoinAddresses']['erc20_config']['results'][0][0]['contract_address'];
           // var_dump($query);exit;
      
          API::add('BitcoinAddresses','getCurrentUser',array($_REQUEST['c_currency']));
          $query = API::send();
          $from_address = $query['BitcoinAddresses']['getCurrentUser']['results'][0][0]['address'];
          $password = $query['BitcoinAddresses']['getCurrentUser']['results'][0][0]['address_key'];
          
          
          
          if ($_REQUEST['c_currency'] == 52) {
            $url = "http://18.217.104.193:8546/";
          }

          $ch = curl_init( $url );
          # Setup request to send json via POST.
          // $sub_params = array($currency_name, $from_address, $password, $_REQUEST['btc_address'], $_REQUEST['btc_amount']);
          $sub_params = array($contract_address,$from_address, $password, $_REQUEST['btc_address'], $_REQUEST['btc_amount']);
          // print_r($sub_params);exit;
          $params = array(
              'jsonrpc' => '2.0', 
              'method' => 'sendTokens', 
              'params' => $sub_params, 
              'id' => 1, 
          );
          
          $payload = json_encode($params);
           
          curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
          
          curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
          
          # Return response instead of printing.
          curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
          
          # Send request.
          $result = curl_exec($ch);
          
          curl_close($ch);
          
          if ($is_error) {
              unset($is_error);         
          }

          $result = json_decode($result);
          // echo "value = ".$result->result->hash."<br>";
          // echo "error = ".$result->error->message."<br>";
          // print_r($result);exit;
          if ($result->error->message) {
              $is_error = $result->error->message;
              if ($is_error =='insufficient funds (version=4.0.15)') {
                  Errors::add('gas required exceeds allowance or always failing transaction');          
              }else{
                Errors::add($result->error->message);          
              }
              
          }elseif ($result->result->hash) {
                Messages::add(Lang::string('withdraw-success'));

          // print_r($user_balance);
          // reduce user balance
          $calculate_user_balance = $user_balance-$_REQUEST['btc_amount'];
          API::add('BitcoinAddresses','reduceUserBalance',array($_REQUEST['c_currency'],$calculate_user_balance));
          $query11 = API::send();
          Link::redirect("deposits-withdrawls?c_currency=".$_REQUEST['c_currency']."&message=withdraw-success");
          // echo "<br><br>";
          // print_r($query11);
          }
        }else{
            Errors::add('You do not have enough of the specified currency.');   
        }
      }

            
        }
        
        if (!empty($_REQUEST['message'])) {
            if ($_REQUEST['message'] == 'withdraw-2fa-success')
                Messages::add(Lang::string('withdraw-2fa-success'));
            elseif ($_REQUEST['message'] == 'withdraw-success')
                Messages::add(Lang::string('withdraw-success'));
        }
        
        if (!empty($_REQUEST['notice']) && $_REQUEST['notice'] == 'email')
            $notice = Lang::string('withdraw-email-notice');


    /* Send and Receive Bitcoin Address End  */

    // binance api for btc
     API::add('Currencies','getAllcurrience');
     $currencies_query1 = API::send();
     // print_r($currencies_query1);echo "<br><br>";
     $currencies_querys = $currencies_query1['Currencies']['getAllcurrience']['results'][0];
     // print_r($currencies_querys);echo "<br><br>";
     $btc_total = 0;
     $usdt_total = 0;
     foreach ($currencies_querys as $currencies_query) {
     

     	if (($currencies_query['id'] != 27) && ($currencies_query['id'] != 50) && ($currencies_query['id'] != 51)) {

                if ($currencies_query['id'] == 44) {
                    $currencies_name = "BCC";//echo "<br><br>"; 
                }else{
                    $currencies_name = $currencies_query['currency'];//echo "<br><br>"; 
                }
     		 

     		 API::add('User','getUserBalance', array($user_id,$currencies_query['id']));
     		 $getUserBalance = API::send();
     		 // print_r($getUserBalance);
     		 $getUserBalance1 = $getUserBalance['User']['getUserBalance']['results'][0][0]['balance']; 
     		 // echo "<br><br>"; 
     		 // echo count($getUserBalance1); 
             // echo $getUserBalance1;
             // echo "<br><br>"; 

     		 // echo "<br><br>"; 
     		 if ($getUserBalance1 != 0) {
     		 	// echo "getUserBalance = ".$getUserBalance1."<br><br>";

     		    $url = 'https://api.binance.com/api/v1/ticker/price?symbol='.$currencies_name.'USDT';
    
    			$ch = curl_init();

    			curl_setopt($ch, CURLOPT_URL, $url);
    			curl_setopt($ch, CURLOPT_POST, 0);
    			// curl_setopt($ch, CURLOPT_POSTFIELDS,$query);
    			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    			curl_setopt($ch, CURLOPT_HTTPGET, 1);
    			$return = curl_exec ($ch);
    			curl_close ($ch);
    			// echo "<br><br>"; 
    			$decoded_data = json_decode($return, true);
    			// echo $decoded_data['symbol']." = ".$decoded_data['price'];
                if($decoded_data['price']!=0)
                {
    			$our_balance = ($getUserBalance1*$decoded_data['price']);
                }else
                {
                    $our_balance = $getUserBalance1;
                }
                // echo $our_balance; exit;
    			$usdt_total = $usdt_total + $our_balance;
    			// echo "<br><br>"; 
    			// echo $usdt_total; 
     		 }

     		 
     	}else if($currencies_query['id'] == 51)
        {
             API::add('User','getUserBalance', array($user_id,$currencies_query['id']));
             $getUserBalance = API::send();
             // print_r($getUserBalance);
             $getUserBalance1 = $getUserBalance['User']['getUserBalance']['results'][0][0]['balance']; 

             $usdt_total = $usdt_total + $getUserBalance1;
        }
   
     }
     if ($usdt_total != 0) {

                // echo "HI";
            // echo $usdt_total; 
                
                $url = 'https://blockchain.info/tobtc?currency=USD&value='.$usdt_total.'';

                // echo $url; 
    
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 0);
                // curl_setopt($ch, CURLOPT_POSTFIELDS,$query);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                $return1 = curl_exec ($ch);
                curl_close ($ch);
                // echo "<br><br>"; 
                // echo $return1; 
                // $decoded_data1 = json_decode($return1, true);

                // echo $decoded_data1; 
                // $btc_total = $decoded_data1;
                $btc_total = $return1;

        }
     // print_r($user_balances);
    
    // exit;

    
    ?>
<head>
    <title><?= $CFG->exchange_name; ?> | Deposit and Withdrawls</title>
    <?php include "bitniex/bitniex_header.php"; ?>
    <style>
        .errors
        {
        background: #ff000029;
        color: red;
        position: relative;
        width: 100%;
        right: 0;
        margin-top:20px;
        }
        .messages
        {
        background: #00800038;
        color: green;
        position: relative;
        width: 100%;
        right: 0;
        margin-top: 20px;
        }
        ul.messages li,ul.errors li {
            padding: 20px;
            list-style-type: none;
        }
        .static-table.dw img {
            width: 20px;
        }
        .qrcode_div1{
            display: none;
        }
        .qrcode_div2{
            display: none;
        }
        .qrcode_div3{
            display: none;
        }
        .qrcode_div4{
            display: none;
        }
        .qrcode_div5{
            display: none;
        }
        .input-caption {
            position: absolute;
            right: 15px;
            top: 6px;
            font-weight: 700;
        }
        p.estimate-value {
    		text-align: right;
    		padding-right: 150px;
		}
        form#buy_form {
            padding: 20px;
        }
        .notice {
                color: green;
                list-style: none;
                background: #00800038;
                padding: 10px;
                position: relative;
                margin: 0 auto;
                font-size: 1em;
                text-align: center;
                max-width: 90%;
                left: 1px;
        }
    </style>
</head>

<body id="wrapper">
    <div id="colorPanel" class="colorPanel">
        <a id="cpToggle" href="#"></a>
        <ul></ul>
    </div>
    <?php include "bitniex/home_nav_bar.php"; ?>
    <header>
        <div class="banner row no-margin">
            <div class="container content">
                <br>
                <h1>BALANCES, DEPOSITS & WITHDRAWALS</h1>
                <!-- <p class="sub-title text-center">Estimated value of holdings: $0.00 USD / 0.00000000 BTC</p>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <br>
                <p class="text-center">$0.00 remaining of $0.00 USD <a href="">daily limit</a>.</p> -->
            </div>
        </div>
    </header>
    <div class="page-container">
        <div class="container-fluid">
            <div class="row profile-banner">
                <div class="container content">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                             <? Messages::display(); ?>
                            <? Errors::display(); ?>
                            <?= ($notice) ? '<div class="notice">'.$notice.'</div>' : '' ?>
                            <br>
                            <div class="static-table dw">
                            	<!-- <p class="estimate-value"><b>Estimate value : </b> <?= $btc_total ?>BTC / $ <?= $usdt_total ?> </p> -->
                                <table class="table table-striped order-data-table">
                                    <thead>
                                        <tr>
                                            <th>Coin</th>
                                            <th>Name</th>
                                            <th>Total Balance</th>
                                            <!-- <th>On Orders</th> -->
                                            <th>Value</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            if($_REQUEST['coin'] == 52){
                                                $back_color = "background-color: rgb(16, 158, 165);";
                                                $color_value = "color: white;";
                                        }else{
                                                $back_color = "";
                                                $color_value = "";
                                            }
                                        ?>
                                        <tr style="<?= $back_color ?>">
                                            <td><img src="images/poly.png"></td>
                                            <td><a href="" style="<?= $color_value ?>">POLY<span class="name">(Polymath Token)</span></a></td>
                                            <td style="<?= $color_value ?>"><?= Stringz::currency1($user_balances_poly1,true) ?> POLY</td>
                                            <td style="<?= $color_value ?>">$<?=Stringz::currency1($transactions_24hrs_poly_usd1['lastPrice'] * $user_balances_poly1,true); ?></td>
                                            <td>
                                                <a style="<?= $color_value ?>" href="deposits-withdrawls?action=add&c_currency=52&pagevalue=1">Deposit</a>
                                                <a style="<?= $color_value ?>" href="deposits-withdrawls?action=add&c_currency=52&pagevalue=2">Withdraw</a>
                                            </td>
                                        </tr>
                                        <?php
                                        if($c_currency == 52 && $pagevalue == 1){
                                            $show = "show";
                                            $address = $bitcoin_addresses[0]['address'];
                                            $balance = Stringz::currency($user_available[$c_currency_info['currency']],true);
                                            $currency_name = $c_currency_info['currency'];
                                            $display_qrcode = $bitcoin_addresses[0]['address'];
                                        }else{
                                            $show = "";
                                            $address = "";
                                            $balance = "";
                                            $currency_name = "";
                                            $display_qrcode = "";
                                        }
                                        if($c_currency == 52 && $pagevalue == 1){
                                        ?>
                                        <tr class="collapse <?= $show ?>" id="dw_d1">
                                            <td colspan="6">
                                                <div class="form-box tbl">
                                                    <div class="form-inner">
                                                        <div class="content">
                                                            <a class="cls-btn" data-toggle="collapse" href="#dw_d1" role="button" aria-expanded="false" aria-controls="dw_d1">Close</a>
                                                            <p>You have <strong><?= $balance ?> </strong> <?= $currency_name ?> available for withdrawal.</p>
                                                            <div class="input-group mb-3">
                                                                <input type="text" class="form-control" placeholder="Address" value="<?= $address;?>" readonly>
                                                            </div>
                                                            <div class="qrcode_div1">
                                                                <div class="form-group" style="text-align: center;margin-top:2em;">
                                                                <img class="qrcode" src="includes/qrcode.php?code=<?= $display_qrcode ?>" style="width: 114px;height: 114px; "/>
                                                                <p class="info-link hide_qr1">Hide QR Code</p>
                                                                </div>
                                                            </div>
                                                            <p class="info-link show_qr1">Show QR Code</p>
                                                            <!-- <div class="input-group mb-3">
                                                                <input type="text" class="form-control" placeholder="Amount">
                                                            </div>
                                                            <p class="info-link">Fees: <strong class="red-color">-5.00000000</strong></p>
                                                            <hr>
                                                            <p class="info-link">Total: <strong class="">0.00000000 ZRX</strong></p>
                                                            <p class="text-right"><a href="#" class="btn btn-light">Deposit</a>
                                                            </p> -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                            }
                                        if($c_currency == 52 && $pagevalue == 2){
                                            $show = "show";
                                            $address = $bitcoin_addresses[0]['address'];
                                            $balance = Stringz::currency($user_available[$c_currency_info['currency']],true);
                                            $currency_name = $c_currency_info['currency'];
                                        }else{
                                            $show = "";
                                            $address = "";
                                            $balance = "";
                                            $currency_name = "";
                                        }
                                        if($c_currency == 52 && $pagevalue == 2){
                                        ?>
                                        <tr class="collapse <?= $show ?>" id="dw_w1">
                                            <td colspan="6">
                                                <div class="form-box tbl">
                                                    <div class="form-inner">
                                                        <div class="content">
                                                            <form id="buy_form" action="deposits-withdrawls.php?c_currency=<?=$_REQUEST['c_currency']?>" method="POST">
                                                            <a class="cls-btn" data-toggle="collapse" href="#dw_w1" role="button" aria-expanded="false" aria-controls="dw_w1">Close</a>
                                                            <p>You have <strong><?= $balance ?> </strong> <?= $currency_name ?> available for withdrawal.</p>
                                                            <div class="input-group mb-3">
                                                                <input type="text" class="form-control " id="btc_address" name="btc_address" value="<?= $btc_address1 ?>" />
                                                            </div>
                                                            <div class="input-group mb-3">
                                                                <input type="text" class="form-control" id="btc_amount" name="btc_amount" value="<?= Stringz::currency($btc_amount1,true) ?>" />
                                                                <div class="input-caption"><?= $c_currency_info['currency'] ?></div>
                                                            </div>
                                                            <p class="info-link">Fees: <strong class="red-color"><span id="withdraw_btc_network_fee"><?= Stringz::currencyOutput($wallet['bitcoin_sending_fee']) ?></span> <?= $c_currency_info['currency'] ?></strong></p>
                                                            <hr>
                                                            <p class="info-link">Total: <strong class=""><span id="withdraw_btc_total"><?= Stringz::currency($btc_total1,true) ?></span><span id="withdraw_btc_total_label"><?= $c_currency_info['currency'] ?> </span></strong></p>
                                                            <p class="text-right">
                                                                <input type="hidden" name="bitcoins" value="1" />
                                                                <!-- <a href="#" class="btn btn-light">Withdraw</a> -->
                                                                <input type="submit" name="submit" value="Withdraw" class="btn btn-light">
                                                            </p>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                            }
                                        ?>
                                       



                                        <?php
                                            if($_REQUEST['coin'] == 27){
                                                $back_color = "background-color: rgb(16, 158, 165);";
                                                $color_value = "color: white;";
                                        }else{
                                                $back_color = "";
                                                $color_value = "";
                                            }
                                        ?>
                                        <tr style="<?= $back_color ?>">
                                            <td><img src="images/dollar.png"></td>
                                            <td><a href="" style="<?= $color_value ?>">USD<span class="name">(US Dollars)</span></a></td>
                                            <td colspan="2" style="<?= $color_value ?>">$<?= Stringz::currency($user_balances_usd1,true) ?>
                                                <p><small>(Us dollars in your usd wallet)</small></p></td>
                                            <td>
                                            <!-- <a data-toggle="collapse" href="#dw_d2" role="button" aria-expanded="false" aria-controls="dw_d2">Deposit</a>
                                            <a data-toggle="collapse" href="#dw_w2" role="button" aria-expanded="false" aria-controls="dw_w2">Withdraw</a> -->
                                            <!-- <a href="" class="outline-btn">Trade</a> -->
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include "bitniex/bitnex_footer.php"; ?>
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js "></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js " integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q " crossorigin="anonymous "></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js " integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl " crossorigin="anonymous "></script>
    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js "></script>
    <script type="text/javascript " language="javascript " src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js ">
    </script>
    <script type="text/javascript " language="javascript " src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js ">
    </script>
    <!-- Color Switcher -->
    <script type="text/javascript" src="bitniex/js/jquery.colorpanel.js"></script>
    <!-- Custom Scripts -->
    <script type="text/javascript " src="bitniex/js/script.js "></script>
</body>
<script type="text/javascript ">
$(document).ready(function() {
    $('.order-data-table').DataTable();
});
</script>
<script type="text/javascript ">
jQuery(document).ready(function($) {
    $(".clickable-row ").click(function() {
        window.location = $(this).data("href ");
    });
});
</script>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#colorPanel').ColorPanel({
        styleSheet: '#cpswitch',
        animateContainer: '#wrapper',
        colors: {
            '#0b7076': 'bitniex/css/skins/default.css',
            '#000000': 'bitniex/css/skins/black.css',
            '#4b77be': 'bitniex/css/skins/blue.css',
            '#c0392c': 'bitniex/css/skins/red.css',
            '#16a085': 'bitniex/css/skins/seagreen.css',
        }
    });
});
</script>
<script type="text/javascript" src="js/ops.js?v=20160210"></script>
<script>
    jQuery(document).ready(function() {
        // Div 1
        $(".show_qr1").click(function(){
            $(".qrcode_div1").css("display","block");
            $(".show_qr1").css("display","none");
        });
        $(".hide_qr1").click(function(){
            $(".qrcode_div1").css("display","none");
            $(".show_qr1").css("display","block");
        });
        // Div 2
        $(".show_qr2").click(function(){
            $(".qrcode_div2").css("display","block");
            $(".show_qr2").css("display","none");
        });
        $(".hide_qr2").click(function(){
            $(".qrcode_div2").css("display","none");
            $(".show_qr2").css("display","block");
        });
        // Div 3
        $(".show_qr3").click(function(){
            $(".qrcode_div3").css("display","block");
            $(".show_qr3").css("display","none");
        });
        $(".hide_qr3").click(function(){
            $(".qrcode_div3").css("display","none");
            $(".show_qr3").css("display","block");
        });
        // Div 4
        $(".show_qr4").click(function(){
            $(".qrcode_div4").css("display","block");
            $(".show_qr4").css("display","none");
        });
        $(".hide_qr4").click(function(){
            $(".qrcode_div4").css("display","none");
            $(".show_qr4").css("display","block");
        });
        // Div 5
        $(".show_qr5").click(function(){
            $(".qrcode_div5").css("display","block");
            $(".show_qr5").css("display","none");
        });
        $(".hide_qr5").click(function(){
            $(".qrcode_div5").css("display","none");
            $(".show_qr5").css("display","block");
        });


    });
</script>
</html>