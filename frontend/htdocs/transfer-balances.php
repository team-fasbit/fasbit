<!DOCTYPE html>
<html lang="en">
<?php 
        include '../lib/common.php';

    $conn = new mysqli("localhost","root","xchange123","bitexchange_cash");

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
       $currency_id = 28;
       $_REQUEST['currency'] = 28;
    }
    
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
    API::add('Transactions','get24hData',array(28,27)); //btc
    API::add('Transactions','get24hData',array(42,27)); //ltc
    API::add('Transactions','get24hData',array(44,27)); //bch
    API::add('Transactions','get24hData',array(45,27)); //eth
    API::add('Transactions','get24hData',array(43,27)); //zec
    API::add('Transactions','get24hData',array(50,27)); //iox
    API::add('Transactions','get24hData',array(51,27)); //usdt
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
     
    $transactions_24hrs_btc_usd = $query['Transactions']['get24hData']['results'][0] ;
    $transactions_24hrs_ltc_usd = $query['Transactions']['get24hData']['results'][1] ;
    $transactions_24hrs_bch_usd = $query['Transactions']['get24hData']['results'][2] ;
    $transactions_24hrs_eth_usd = $query['Transactions']['get24hData']['results'][3] ;
    $transactions_24hrs_zec_usd = $query['Transactions']['get24hData']['results'][4] ;
    $transactions_24hrs_iox_usd = $query['Transactions']['get24hData']['results'][5] ;
    $transactions_24hrs_usdt_usd = $query['Transactions']['get24hData']['results'][6] ;
    $transactions_24hrs_poly_usd = $query['Transactions']['get24hData']['results'][7] ;
    
    $user_balances_usd = $user_available['USD'];
    $user_balances_btc = $user_available['BTC'];
    $user_balances_bch = $user_available['BCH'];
    $user_balances_eth = $user_available['ETH'];
    $user_balances_ltc = $user_available['LTC'];
    $user_balances_zec = $user_available['ZEC'];
    $user_balances_iox = $user_available['IOX'];
    $user_balances_usdt = $user_available['USDT'];
    $user_balances_poly = $user_available['POLY'];
    
    $zec_usd = $checkusd['ZEC']['last_price'] * $user_balances_zec;  // echo  $checkusd['ZEC']['last_price'];  185
    $btc_usd = $checkusd['BTC']['last_price'] * $user_balances_btc;  // echo $checkusd['BTC']['last_price'];  6002
    $bch_usd = $checkusd['BCH']['last_price'] * $user_balances_bch; // echo $checkusd['BCH']['last_price']; 83
    $eth_usd = $checkusd['ETH']['last_price'] * $user_balances_eth;  //   echo $checkusd['ETH']['last_price']; 1186.9
    $ltc_usd = $checkusd['LTC']['last_price'] * $user_balances_ltc; //echo $checkusd['LTC']['last_price']; 7000
    $iox_usd = $checkusd['IOX']['last_price'] * $user_balances_iox; //echo $checkusd['IOX']['last_price']; 
    $usdt_usd = $checkusd['USDT']['last_price'] * $user_balances_usdt; //echo $checkusd['USDT']['last_price']; 
    $poly_usd = $checkusd['POLY']['last_price'] * $user_balances_poly;  
    $totalBalance = $user_balances_usd + $poly_usd;
    $fiatBalance = $user_balances_usd;
    $cryptoBalance = $poly_usd;
    $fiatBalance = number_format($fiatBalance, 2);
    $cryptoBalance = number_format($cryptoBalance, 2);
    $totalBalance = number_format($totalBalance, 2);
?>
<head>
    <title><?= $CFG->exchange_name; ?> | Transfer Balances</title>
   <?php include "bitniex/bitniex_header.php"; ?>

    <style>
        .custom-select {
        font-size: 11px;
        padding: 5px 10px;
        border-radius: 2px;
        height: 28px !important;
        }
        .messages
        {
            background: #0080002e;
            color: green;
            width: 100%;
            position: relative;
            margin: 1em auto 0 20px;
        }
        .errors
        {
            list-style-type: none;
            padding: 20px;
            background: #ff00003d;
            color: red;
            width: 100%;
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
        .row.profile-banner {
            /*top: 0px;*/
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
                <h1>TRANSFER BALANCES</h1>
                <p class="sub-title text-center">Move funds between your Exchange <!-- , Margin, and Lending accounts -->.</p>
            </div>
        </div>
    </header>
    <div class="page-container">
        <div class="container-fluid">
            <div class="row profile-banner">
                <div class="container content">
                   <!--  <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="form-box full">
                                <div class="form-inner">
                                    <div class="content row">
                                        <div class="col-md-12">
                                            <p class="mb-0">Transfer</p>
                                        </div>
                                        <div class="col-md-2 col-sm form-group mb-0 mt-2">
                                            <input type="text" class="form-control" placeholder="Amount">
                                        </div>
                                        <div class="col-md-2 input-group mb-0 mt-2">
                                            <select class="form-control">
                                                <option value="BTC">BTC</option>
                                                <option value="BTS">BTS</option>
                                                <option value="CLAM">CLAM</option>
                                                <option value="DOGE">DOGE</option>
                                                <option value="DASH">DASH</option>
                                                <option value="LTC">LTC</option>
                                                <option value="MAID">MAID</option>
                                                <option value="STR">STR</option>
                                                <option value="XMR">XMR</option>
                                                <option value="XRP">XRP</option>
                                                <option value="ETH">ETH</option>
                                                <option value="FCT">FCT</option>
                                            </select>
                                            <span class="mt-1 ml-1">from</span>
                                        </div>
                                        <div class="col-md-3 input-group mb-0 mt-2">
                                            <select class="form-control">
                                                <option value="exchange">Exchange</option>
                                                <option value="margin">Margin</option>
                                                <option value="lending">Lending</option>
                                            </select>
                                            <span class="mt-1 ml-1">account to</span>
                                        </div>
                                        <div class="col-md-3 input-group mb-0 mt-2">
                                            <select class="form-control">
                                                <option value="exchange">Exchange</option>
                                                <option value="margin">Margin</option>
                                                <option value="lending">Lending</option>
                                            </select>
                                            <span class="mt-1 ml-1">account</span>
                                        </div>
                                        <div class="col-md-2 mb-1 mt-2">
                                            <a href="#" class="btn btn-light">Transfer</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="static-table">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Coin</th>
                                            <th>Name</th>
                                           <!--  <th>Exchange</th>
                                            <th>Margin</th>
                                            <th>Lending</th> -->
                                            <th>Total Balance</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                   
                                <tbody>
                                    
                                    <tr>
                                        <td><img src="images/poly.png"></td>
                                        <td><a href="">POLY<span class="name">(Polymath Token)</span></a></td>
                                        <td><?= Stringz::currency($user_balances_poly,true) ?> POLY</td>
                                        <td><a href="deposits-withdrawls?coin=52">Deposit/Withdraw</a></td>
                                    </tr>
                                   <tr>
                                        <td><img src="images/dollar.png" style="width:20px; height:20px;"></td>
                                        <td><a href="">USD<span class="name">(US Dollars)</span></a></td>
                                        <td>$<?= Stringz::currency($user_balances_usd,true) ?>
                                            <p><small>(Us dollars in your usd wallet)</small></p></td>
                                        <td><!-- <a href="deposits-withdrawls?coin=27">Deposit/Withdraw</a> --></td>
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
    $(".clickable-row").click(function() {
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