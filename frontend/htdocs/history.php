<!DOCTYPE html>
<html lang="en">

<?php
  // error_reporting(E_ALL);
        // ini_set('display_errors', 1);
        include '../lib/common.php';
        // echo "<pre>"; print_r($CFG); exit;
        if (User::$info['locked'] == 'Y' || User::$info['deactivated'] == 'Y')
            Link::redirect('userprofile');
        elseif (User::$awaiting_token)
            Link::redirect('verify-token');
        elseif (!User::isLoggedIn())
            Link::redirect('login'); 
            
        //     if(empty(User::$ekyc_data) || User::$ekyc_data[0]->status != 'accepted')
        // {
        //     Link::redirect('ekyc');
        // }
        
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


        API::add('Transactions','get24hData',array(28,27)); //btc
        API::add('Transactions','get24hData',array(42,27)); //ltc
        API::add('Transactions','get24hData',array(44,27)); //bch
        API::add('Transactions','get24hData',array(45,27)); //eth
        API::add('Transactions','get24hData',array(43,27)); //zec
        
        $query = API::send();

        $transactions_24hrs_btc_usd = $query['Transactions']['get24hData']['results'][0] ;
        $transactions_24hrs_ltc_usd = $query['Transactions']['get24hData']['results'][1] ;
        $transactions_24hrs_bch_usd = $query['Transactions']['get24hData']['results'][2] ;
        $transactions_24hrs_eth_usd = $query['Transactions']['get24hData']['results'][3] ;
        $transactions_24hrs_zec_usd = $query['Transactions']['get24hData']['results'][4] ;
       
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
                Link::redirect('cryptowalletnew?message=withdraw-2fa-success');
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
            // echo "string"; exit;
            $btc_to_send = $btc_amount1 - $wallet['bitcoin_sending_fee'];
            $btc_amount1 = $btc_to_send;
            if ($btc_amount1 < 0.00000001)
                Errors::add(Lang::string('withdraw-amount-zero'));
            if ($btc_amount1 > $user_available[$c_currency_info['currency']])
                Errors::add(str_replace('[c_currency]',$c_currency_info['currency'],Lang::string('withdraw-too-much')));
            if (!$query['BitcoinAddresses']['validateAddress']['results'][0])
                Errors::add(str_replace('[c_currency]',$c_currency_info['currency'],Lang::string('withdraw-address-invalid')));
            
            if (!is_array(Errors::$errors)) {
                if (User::$info['confirm_withdrawal_email_btc'] == 'Y' && !$request_2fa && !$token1) {
                    API::add('Requests','insert',array($c_currency_info['id'],$btc_amount1,$btc_address1));
                    $query = API::send();
                    Link::redirect('cryptowalletnew?notice=email');
                }
                elseif (!$request_2fa) {
                    API::token($token1);
                    API::add('Requests','insert',array($c_currency_info['id'],$btc_amount1,$btc_address1));
                    $query = API::send();
                    
                    if ($query['error'] == 'security-com-error')
                        Errors::add(Lang::string('security-com-error'));
                    
                    if ($query['error'] == 'authy-errors')
                        Errors::merge($query['authy_errors']);
                    
                    if ($query['error'] == 'security-incorrect-token')
                        Errors::add(Lang::string('security-incorrect-token'));
                    
                    if (!is_array(Errors::$errors)) {
                        if ($query['Requests']['insert']['results'][0]) {
                            if ($token1 > 0)
                                Link::redirect('cryptowalletnew?message=withdraw-2fa-success');
                            else
                                Link::redirect('cryptowalletnew?message=withdraw-success');
                        }   
                    }
                    elseif (!$no_token) {
                        $request_2fa = true;
                    }
                }
            }
            elseif (!$no_token) {
                $request_2fa = false;
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
      
        ?>
<head>
<title><?= $CFG->exchange_name; ?> | History</title>
    <?php include "bitniex/bitniex_header.php"; ?>
    <style>
        .custom-select {
        font-size: 11px;
        padding: 5px 10px;
        border-radius: 2px;
        height: 28px !important;
        }
        .left-side-inner .media.active
        {
            border-left: 3px solid #fcae51;
        }
        
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
        .order-table-box {
            overflow: hidden;
            padding: 20px;
            margin-top: 20px;
        }
        .static-table img {
            width: 20px;
        }
        .static-table .table td {
            border: 0;
            padding: 5px;
            font-size: 14px;
        }
        .static-table {
            overflow: hidden;
            padding: 20px;
            /*margin-top: 20px;*/
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
                <h1>DEPOSIT & WITHDRAWAL HISTORY</h1>
                <p class="sub-title text-center">All coins deposited to and withdrawn from your <?= $CFG->exchange_name; ?> account</p>
                <p class="text-center">Looking for your <a href="">Trade History?</a></p>
            </div>
        </div>
    </header>
    <div class="page-container">
        <div class="container-fluid">
            <div class="row profile-banner">
                <div class="container content">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <br>
                            <h5><strong>DEPOSIT HISTORY</strong></h5>
                            <div class="static-table no-height profile-banner">
                                <table class="table table-striped order-data-table">
                                    <thead>
                                        <tr>
                                            <th>Status (Confirmations)</th>
                                            <th>Coin</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                       <? if ($deposit_requests): ?>
                                        <? foreach ($deposit_requests as $request): ?>
                                            <?php if($CFG->currencies[$request['currency']]['is_crypto'] != 'Y') continue ?>
                                        <tr>
                                            <td><?=$request['status']?></td>
                                            <!-- <td><?= $request['id'] ?></td> -->
                                            <!-- <td><input type="hidden" class="localdate" value="<?= strtotime($request['date']) ?>" /></td> -->
                                            <!-- <td><?= $request['description'] ?></td> -->
                                            <td><?= $request['fa_symbol']; ?></td>
                                            <td><?=(($CFG->currencies[$request['currency']]['is_crypto'] == 'Y') ? Stringz::currency($request['amount'],true).' '.$request['fa_symbol'] : $request['fa_symbol'].Stringz::currency($request['amount'])) ?></td>
                                            <!-- <td><?= (($CFG->currencies[$request['currency']]['is_crypto'] == 'Y') ? Stringz::currency((($request['net_amount'] > 0) ? $request['net_amount'] : ($request['amount'] - $request['fee'])),true).' '.$request['fa_symbol'] : $request['fa_symbol'].Stringz::currency((($request['net_amount'] > 0) ? $request['net_amount'] : ($request['amount'] - $request['fee']))))?></td> -->
                                            
                                        </tr>
                                        <? endforeach;?>
                                    <? else: ?>
                                        <tr><td colspan="6">No Deposits</td></tr>
                                    <? endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <br>
                            <h5><strong>WITHDRAWAL HISTORY</strong></h5>
                            <div class="static-table no-height profile-banner">
                                <table class="table table-striped order-data-table">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Coin</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                         <? if ($withdraw_requests): ?>
                                        <? foreach ($withdraw_requests as $request): ?>
                                        <?php if($CFG->currencies[$request['currency']]['is_crypto'] != 'Y') continue ?>
                                        <tr>
                                            <td><?=$request['status']?></td>
                                            <!-- <td><?= $request['id'] ?></td> -->
                                            <!-- <td><input type="hidden" class="localdate" value="<?= strtotime($request['date']) ?>" /></td> -->
                                            <!-- <td><?= $request['description'] ?></td> -->
                                            <td><?= $request['fa_symbol']; ?></td>
                                            <td><?=(($CFG->currencies[$request['currency']]['is_crypto'] == 'Y') ? Stringz::currency($request['amount'],true).' '.$request['fa_symbol'] : $request['fa_symbol'].Stringz::currency($request['amount'])) ?></td>
                                            <!-- <td><?=(($CFG->currencies[$request['currency']]['is_crypto'] == 'Y') ? Stringz::currency((($request['net_amount'] > 0) ? $request['net_amount'] : ($request['amount'] - $request['fee'])),true).' '.$request['fa_symbol'] : $request['fa_symbol'].Stringz::currency((($request['net_amount'] > 0) ? $request['net_amount'] : ($request['amount'] - $request['fee']))))?></td> -->
                                            
                                        </tr>
                                        <? endforeach;?>
                                    <? else: ?>
                                        <tr><td colspan="6">No Withdrawals</td></tr>
                                    <? endif; ?>
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

</html>