<!DOCTYPE html>



<html lang="en">

<?php 
        include '../lib/common.php';
        require_once ("cfg.php");
        if(!User::isLoggedIn()) { $is_user_login=0; }else{
        $is_user_login=1;
    }

        $explode_value = explode("-", $_REQUEST['currency']);
        // echo $explode_value[0];  echo "<br>";  echo $explode_value[1];   echo "<br>";  echo $explode_value[2];  echo "<br>";  echo $explode_value[3];  
        $currency_id1 = $explode_value[3];
        $c_currency_id = $explode_value[2];  
        if ($_REQUEST['currency'] == 28 || !$_REQUEST['currency']) {
            $currency_id1 = 27;
            $c_currency_id = 28;  
        }
        
        $currency_id = $_REQUEST['currency'];
        if (!$currency_id) {
           $currency_id = 28;
           $_REQUEST['currency'] = 28;
        }

        $currencies = Settings::sessionCurrency();
        $currency1 = $currencies['currency'];
        $c_currency1 = $currencies['c_currency'];
        $usd_field = 'usd_ask';
        API::add('Transactions','get',array(false,false,1,$c_currency1,$currency1));
        API::add('Stats','getCurrent',array($currencies['c_currency'],$currencies['currency']));
        //27-USD, 28-BTC, 42-LTC, 43-ZEC, 44-BCH, 45-ETH
        API::add('Transactions','get24hData',array(28,27));
        API::add('Transactions','get24hData',array(42,27));
        API::add('Transactions','get24hData',array(42,28));
        API::add('Transactions','get24hData',array(43,27));
        API::add('Transactions','get24hData',array(43,28));
        API::add('Transactions','get24hData',array(45,27));
        API::add('Transactions','get24hData',array(45,28));
        API::add('Transactions','get24hData',array(42,45));
        API::add('Transactions','get24hData',array(43,45));
        API::add('Transactions','get24hData',array(44,27));
        API::add('Transactions','get24hData',array(44,28));
        $query = API::send();
        // echo "<pre>"; print_r($query['Stats']['getCurrent']['results']); exit;
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
        $currency_info = $CFG->currencies[$currencies['currency']];
        $c_currency_info = $CFG->currencies[$currencies['c_currency']];
        $currency_majors = array('USD','EUR','CNY','RUB','CHF','JPY','GBP','CAD','AUD');
        $c_majors = count($currency_majors);
        $curr_list = $CFG->currencies;
        $curr_list1 = array();
        foreach ($currency_majors as $currency) {
            if (empty($curr_list[$currency]))
                continue;
        
            $curr_list1[$currency] = $curr_list[$currency];
            unset($curr_list[$currency]);
        }
        $curr_list = array_merge($curr_list1,$curr_list);
        // echo "<pre>"; print_r($curr_list); exit;
        $stats = $query['Stats']['getCurrent']['results'][0];
        if ($stats['daily_change'] > 0)
            $arrow = '<i id="up_or_down" class="fa fa-caret-up price-green"></i> ';
        elseif ($stats['daily_change'] < 0)
            $arrow = '<i id="up_or_down" class="fa fa-caret-down price-red"></i> ';
        else
            $arrow = '<i id="up_or_down" class="fa fa-minus"></i> ';
        ?>
    
<head>
   <?php include "bitniex/bitniex_header.php"; ?>
   <style>
       a.btn.btn-yellow {
            width: auto;
        }


.btn-yellow {
    min-width: 100px;
    display: inline-block;
    padding: 7px 20px !IMPORTANT;
    border: 1px solid #c18102;
    background-color: #ffab06;
    border-radius: 3px;
    font-size: 16px !IMPORTANT;
    font-weight: 900;
    color: rgba(0, 0, 0, 0.7);
    text-decoration: none!important;
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
                <h1>Welcome to Bitexchange STO Exchange</h1>
                <p class="sub-title text-center">We are a digital asset exchange offering maximum security and advanced trading features.</p>
            </div>
        </div>
    </header>
    <div class="page-container">
        <div class="container-fluid">
            <div class="row feature-banner">
                <div class="container content">
                    <h2 class="text-center">Trade securely on the world's most active digital asset exchange.</h2>
                    <p class="text-center"><a href="register" class="btn btn-yellow">Create Your Account</a></p>
                    <p class="login-link text-center">Already a member? <a href="login">Sign in.</a></p>
                </div>
            </div>
            <div class="row features-list">
                <div class="container">
                    <div class="row">
                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <h5>Keeping hackers out.</h5>
                            <p>The vast majority of customer deposits are stored offline in air-gapped cold storage. We only keep enough online to facilitate active trading, which greatly minimizes risk and exposure.</p>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <h5>Monitoring around the clock.</h5>
                            <p>Our auditing programs monitor exchange activity 24/7/365. Their job is to report and block any suspicious activity before it becomes a problem.</p>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <h5>Your funds are yours. Period.</h5>
                            <p>Any funds you put into the exchange are only used to facilitate trading through your account. Unlike banks, we do not operate on fractional reserves.</p>
                        </div>
                    </div>
                </div>
            </div>
            <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="https://code.highcharts.com/stock/highstock.js"></script>
<script src="https://code.highcharts.com/stock/modules/exporting.js"></script>
<script src="https://code.highcharts.com/stock/modules/export-data.js"></script>

<script>
    $.getJSON('https://www.highcharts.com/samples/data/aapl-c.json', function (data) {

    // Create the chart
    Highcharts.stockChart('chartfunction', {

        rangeSelector: {
            selected: 1
        },

        title: {
            text: 'AAPL Stock Price'
        },

        scrollbar: {
            barBackgroundColor: 'gray',
            barBorderRadius: 7,
            barBorderWidth: 0,
            buttonBackgroundColor: 'gray',
            buttonBorderWidth: 0,
            buttonBorderRadius: 7,
            trackBackgroundColor: 'none',
            trackBorderWidth: 1,
            trackBorderRadius: 8,
            trackBorderColor: '#CCC'
        },

        series: [{
            name: 'AAPL Stock Price',
            data: data,
            tooltip: {
                valueDecimals: 2
            }
        }]
    });
});
</script>
 <div class="graph-1 card" id="chartfunction">
                        <!-- TradingView Widget BEGIN -->
                      
                        <!-- TradingView Widget END -->
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