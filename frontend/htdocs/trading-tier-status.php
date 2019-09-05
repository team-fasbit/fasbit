<!DOCTYPE html>
<html lang="en">
<?php

include '../lib/common.php';
    require_once ("cfg.php");
    // $conn = new mysqli("localhost","root","xchange123","bitexchange_cash");
//     error_reporting(E_ERROR | E_WARNING | E_PARSE);
// ini_set('display_errors', 1);
    if (User::$info['locked'] == 'Y' || User::$info['deactivated'] == 'Y') {
        Link::redirect('userprofile.php');
    } elseif (User::$awaiting_token) {
        Link::redirect('verify-token.php');
    } elseif (!User::isLoggedIn()) {
        Link::redirect('login.php');
    }
  
    $currency_id = $_REQUEST['currency'];
    $currency_id1 = $_REQUEST['currency'];
    $c_currency_id = $_REQUEST['c_currency'];
    if (!$currency_id) {
       $currency_id = 28;
       $_REQUEST['currency'] = 28;
    }
    API::add('FeeSchedule','getRecordAll',User::$info['fee_schedule']);
    API::add('FeeSchedule','getRecord',array(User::$info['fee_schedule']));
    $feequery = API::send();
    // print_r($feequery);
    $feeschedules = $feequery['FeeSchedule']['getRecordAll']['results'][0];    
    $fee_bracket = $feequery['FeeSchedule']['getRecord']['results'][0];
    // echo "<pre>"; print_r($feeschedules);echo "</pre>";
   // echo "<pre>"; print_r($fee_bracket);echo "</pre>"; 
?>

<head>
    <title><?= $CFG->exchange_name; ?> | Trading Tier Status</title>
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
        .trade-table {
            overflow: hidden;
            padding: 20px;
            margin-top: 20px;
        }
        table.table.table-striped th {
            font-size: 15px;
        }
        table.table.table-striped td {
            font-size: 14px;
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
                <h1>TRADING TIER STATUS</h1>
            </div>
        </div>
    </header>
    <div class="page-container">
        <div class="container-fluid">
            <div class="row profile-banner">
                <div class="container content">
                    <div class="profile-box">
                        <div class="row">
                            <div class="col-md-12 col-xs-12 text-center">
                                <br>
                                <h5>Your current maker/taker fee is <strong><? echo $fee_bracket[fee1]; ?>% / <? echo $fee_bracket[fee]; ?>%</strong></h5>
                                <!-- <p><small>PROGRESS TOWARD NEXT TIER</small></p> -->
                                <!-- <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                </div> -->
                                
                                <div class="trade-table profile-banner">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Maker Fee (%)</th>
                                                <th>Taker Fee (%)</th>
                                                <th>From (fiat/mon.)</th>
                                                <th>To (fiat/mon.)</th>
                                                <!-- <th>Global Reducer (crypto/24h)</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                           <?php 
                                                if (count($fee_bracket) >= 8) { 
                                                    
                                                        foreach ($fee_bracket as $feeschedule) {
                                                        ?>
                                                        <tr>
                                                            <td><? echo $feeschedule[fee1];?></td>
                                                            <td><? echo $feeschedule[fee];?></td>
                                                            <td><? echo $feeschedule[from_usd];?></td>
                                                            <td><? echo $feeschedule[to_usd];?></td>
                                                        </tr>
                                                        <?php
                                                        }
                                                }elseif (count($fee_bracket) == 7) {
                                                        ?>
                                                             <tr>
                                                            <td><? echo $fee_bracket[fee1];?></td>
                                                            <td><? echo $fee_bracket[fee];?></td>
                                                            <td><? echo $fee_bracket[from_usd];?></td>
                                                            <td><? echo $fee_bracket[to_usd];?></td>
                                                        </tr>
                                                        <?php
                                                }
                                           ?>
                                        </tbody>
                                    </table>
                                    <br>
                                    <p>Every 24 hours, we will calculate the last 30 days of trading volume for this account and dynamically adjust fees according to the schedule above.</p>
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
    </script>
    <!-- Color Switcher -->
    <script type="text/javascript" src="bitniex/js/jquery.colorpanel.js"></script>
    <!-- Custom Scripts -->
    <script type="text/javascript" src="bitniex/js/script.js"></script>
</body>
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