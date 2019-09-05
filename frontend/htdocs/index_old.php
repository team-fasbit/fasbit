<!DOCTYPE html>
<html lang="en">

    <?php include '../lib/common.php';
    require_once ("cfg.php");
        // $conn = new mysqli("localhost","root","xchange123","bitexchange_cash");

    if(!User::isLoggedIn()) { $is_user_login=0; }else{
        $is_user_login=1;
    }

        $explode_value = explode("-", $_REQUEST['currency']);
        // echo $explode_value[0];  echo "<br>";  echo $explode_value[1];   echo "<br>";  echo $explode_value[2];  echo "<br>";  echo $explode_value[3];  
        // $currency_id1 = $explode_value[3];
        // $c_currency_id = $explode_value[2];  
        $currency_id1 = 51;
        $c_currency_id = 28;
        if ($_REQUEST['currency'] == 28 || !$_REQUEST['currency']) {
            $currency_id1 = 51;
            $c_currency_id = 28;  
        }
        
        $currency_id = $_REQUEST['currency'];
        if (!$currency_id) {
           $currency_id = 28;
           $_REQUEST['currency'] = 28;
        }
        include "includes/sonance_header.php";
        
        $currencies = Settings::sessionCurrency();
        $currency1 = $currencies['currency'];
        $c_currency1 = $currencies['c_currency'];
        $usd_field = 'usd_ask';
        API::add('Transactions','get',array(false,false,1,$c_currency1,$currency1));
        API::add('Stats','getCurrent',array($currencies['c_currency'],$currencies['currency']));
        //27-USD, 28-BTC, 42-LTC, 43-ZEC, 44-BCH, 45-ETH, 50-IOX, 51-USDT
        // API::add('Transactions','get24hData',array(28,27));
        API::add('Transactions','get24hData',array(28,51)); //BTC-USDT
        // API::add('Transactions','get24hData',array(42,27));
        API::add('Transactions','get24hData',array(42,51)); //LTC-USDT
        API::add('Transactions','get24hData',array(42,28)); //LTC-BTC
        // API::add('Transactions','get24hData',array(43,27));
        API::add('Transactions','get24hData',array(43,51)); //ZEC-USDT
        API::add('Transactions','get24hData',array(43,28)); //ZEC-BTC
        // API::add('Transactions','get24hData',array(45,27));
        API::add('Transactions','get24hData',array(45,51)); //ETH-USDT
        API::add('Transactions','get24hData',array(45,28)); //ETH-BTC
        API::add('Transactions','get24hData',array(42,45)); //LTC-ETH
        API::add('Transactions','get24hData',array(43,45)); //ZEC-ETH
        // API::add('Transactions','get24hData',array(44,27));
        API::add('Transactions','get24hData',array(44,51)); //BCH-USDT
        API::add('Transactions','get24hData',array(44,28)); //BCH-BTC
        // API::add('Transactions','get24hData',array(50,27));
        API::add('Transactions','get24hData',array(50,51)); //IOX-USDT
        API::add('Transactions','get24hData',array(50,28)); //IOX-BTC

        API::add('Transactions','get24hData',array(51,28)); //USDT-BTC

        API::add('Transactions','get24hData',array(28,45)); //BTC-ETH
        API::add('Transactions','get24hData',array(44,45)); //BCH-ETH
        API::add('Transactions','get24hData',array(50,45)); //IOX-ETH
        API::add('Transactions','get24hData',array(51,45)); //USDT-ETH

        API::add('Transactions','get24hData',array(28,50)); //BTC-IOX
        API::add('Transactions','get24hData',array(42,50)); //LTC-IOX
        API::add('Transactions','get24hData',array(43,50)); //ZEC-IOX
        API::add('Transactions','get24hData',array(45,50)); //ETH-IOX
        API::add('Transactions','get24hData',array(44,50)); //BCH-IOX
        API::add('Transactions','get24hData',array(51,50)); //USDT-IOX
        $query = API::send();
        // echo "<pre>"; print_r($query['Stats']['getCurrent']['results']); exit;
        $transactions_24hrs_btc_usdt = $query['Transactions']['get24hData']['results'][0] ;
        $transactions_24hrs_ltc_usdt = $query['Transactions']['get24hData']['results'][1] ;
        $transactions_24hrs_ltc_btc = $query['Transactions']['get24hData']['results'][2] ;
        $transactions_24hrs_zec_usdt = $query['Transactions']['get24hData']['results'][3] ;
        $transactions_24hrs_zec_btc = $query['Transactions']['get24hData']['results'][4] ;
        $transactions_24hrs_eth_usdt = $query['Transactions']['get24hData']['results'][5] ;
        $transactions_24hrs_eth_btc = $query['Transactions']['get24hData']['results'][6] ;
        $transactions_24hrs_ltc_eth = $query['Transactions']['get24hData']['results'][7] ;
        $transactions_24hrs_zec_eth = $query['Transactions']['get24hData']['results'][8] ;
        $transactions_24hrs_bch_usdt = $query['Transactions']['get24hData']['results'][9] ;
        $transactions_24hrs_bch_btc = $query['Transactions']['get24hData']['results'][10] ;
        $transactions_24hrs_iox_usdt = $query['Transactions']['get24hData']['results'][11] ;
        $transactions_24hrs_iox_btc = $query['Transactions']['get24hData']['results'][12] ;

        $transactions_24hrs_usdt_btc = $query['Transactions']['get24hData']['results'][13] ;

        $transactions_24hrs_btc_eth = $query['Transactions']['get24hData']['results'][14] ;
        $transactions_24hrs_bch_eth = $query['Transactions']['get24hData']['results'][15] ;
        $transactions_24hrs_iox_eth = $query['Transactions']['get24hData']['results'][16] ;
        $transactions_24hrs_usdt_eth = $query['Transactions']['get24hData']['results'][17] ;

        $transactions_24hrs_btc_iox = $query['Transactions']['get24hData']['results'][18] ;
        $transactions_24hrs_ltc_iox = $query['Transactions']['get24hData']['results'][19] ;
        $transactions_24hrs_zec_iox = $query['Transactions']['get24hData']['results'][20] ;
        $transactions_24hrs_eth_iox = $query['Transactions']['get24hData']['results'][21] ;
        $transactions_24hrs_bch_iox = $query['Transactions']['get24hData']['results'][22] ;
        $transactions_24hrs_usdt_iox = $query['Transactions']['get24hData']['results'][23] ;
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
        <style>
            .table td, .table th,.table tr{
                cursor: auto;
            }
            footer {
    margin-top: 0px;
}
        </style>
    <body id="wrapper">
        <?php include "includes/sonance_navbar.php"; ?>
        <script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script> 
        <header>
            <!--<div class="banner row no-margin">-->
                    <img src="images/banner.jpg" alt="banner" class="responsive" id="banner1">
                <div class="container content">
                    <!--<h1><?= $CFG->exchange_name; ?> - Exchange The World</h1>
                   <?php if($is_user_login==0)
                   { ?>
                    <div class="links">
                        <p><a href="register">Create Account</a><span class="line"></span>Already Registered? <a href="login">Login</a></p>
                    </div><?php } ?>-->
                    <!--<div class="row">
                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="banner-images">
                                <a href="https://bitexchange.systems/cryptocurrency-wallet-script/" target="_blank" rel="nofollow"><img src="sonance/img/banner/1.png"></a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="banner-images">
                                <a href="https://bitexchange.systems/bitcoin-exchange-android-app-theme/" target="_blank" rel="nofollow"><img src="sonance/img/banner/2.png"></a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="banner-images">
                                <a href="https://bitexchange.live/api-docs.php" target="_blank" rel="nofollow"><img src="sonance/img/banner/3.png"></a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="banner-images">
                                <a href="http://bitcoinscript.bitexchange.systems/2018/01/cryptocurrency-exchange-software-security.html" target="_blank" rel="nofollow"><img src="sonance/img/banner/4.png"></a>
                            </div>-->
                        </div>
                    </div>
                </div>
            </div>
            <div class="sticky row no-margin">
                <marquee behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();">
                    <div class="container">
                        <div class="row">
                            <div style="padding:0 3em;"><span><a href="#"><span><b>BTC</b></span>&nbsp; <i><?=$transactions_24hrs_btc_usdt['lastPrice'] ? $transactions_24hrs_btc_usdt['lastPrice'] : '0.00'?></i></a></span></div>
                            <div style="padding:0 3em;"><span><a href="#"><span><b>LTC</b></span>&nbsp; <i><?=$transactions_24hrs_ltc_usdt['lastPrice'] ? $transactions_24hrs_ltc_usdt['lastPrice'] : '0.00'?></i></a></span></div>
                            <div style="padding:0 3em;"><span><a href="#"><span><b>ZEC</b></span>&nbsp; <i><?=$transactions_24hrs_zec_usdt['lastPrice'] ? $transactions_24hrs_zec_usdt['lastPrice'] : '0.00'?></i></a></span></div>
                            <div style="padding:0 3em;"><span><a href="#"><span><b>BCH</b></span>&nbsp; <i><?=$transactions_24hrs_bch_usdt['lastPrice'] ? $transactions_24hrs_bch_usdt['lastPrice'] : '0.00'?></i></a></span></div>
                            <div style="padding:0 3em;"><span><a href="#"><span><b>ETH</b></span>&nbsp; <i><?=$transactions_24hrs_eth_usdt['lastPrice'] ? $transactions_24hrs_eth_usdt['lastPrice'] : '0.00'?></i></a></span></div>
                             <div style="padding:0 3em;"><span><a href="#"><span><b>IOX</b></span>&nbsp; <i><?=$transactions_24hrs_iox_usdt['lastPrice'] ? $transactions_24hrs_iox_usdt['lastPrice'] : '0.00'?></i></a></span></div>
                        
                            <?
                                /* if ($curr_list) {
                                    foreach ($curr_list as $key => $currency) {
                                        if (is_numeric($key) || $currency['id'] == $c_currency_info['id'])
                                            continue;
                                
                                        $last_price = Stringz::currency($stats['last_price'] * ((empty($currency_info) || $currency_info['currency'] == 'USD') ? 1/$currency[$usd_field] : $currency_info[$usd_field] / $currency[$usd_field]),2,4);
                                        echo '<div style="padding:0 3em;"><span><a href="#"><span><b>'.$currency['currency'].'</b></span>&nbsp; <i>'.$last_price.'</i></a></span></div>';
                                    }
                                } */
                                ?>
                            <!--  <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <p><a href="#"><span><?= $CFG->exchange_name; ?> Lists Red Pulse (RPX)</span><i>(01-12)</i></a></p>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <p><a href="#"><span><?= $CFG->exchange_name; ?> Lists Red Pulse (RPX)</span><i>(01-12)</i></a></p>
                                </div> -->
                        </div>
                    </div>
                </marquee>
            </div>
        </header>
        <div class="page-container">
            <div class="container">
                <div class="row statistics-widget">
                    <div class="col">
                        <a href="userbuy?trade=BTC-USDT" class="statistics-widget-link">
                            <div class="statistics-widget-grid">
                                <div class="content">
                                    <h5>BTC/USDT</h5>
                                    <h6>
                                        <strong class="green-color"><? echo $transactions_24hrs_btc_usdt['lastPrice'] ? Stringz::currency($transactions_24hrs_btc_usdt['lastPrice'],2,4) : '0.00' ; ?></strong><!--  $0.05 -->
                                    </h6>
                                    <p>Volume : <? echo $transactions_24hrs_btc_usdt['transactions_24hrs'] ? $transactions_24hrs_btc_usdt['transactions_24hrs'] : '0.00'; ?> BTC</p>
                                </div>
                                <span class="status green-color"><? echo $transactions_24hrs_btc_usdt['change_24hrs'] ? $transactions_24hrs_btc_usdt['change_24hrs'] : '0.00'; ?></span>
                                <div class="chart-bar">
                                    <svg version="1.1" class="highcharts-root" xmlns="http://www.w3.org/2000/svg">
                                        <g transform="translate(0.5,0.5)">
                                            <path id="BNBBTC" stroke="rgba(244,220,174,1)" fill="none" stroke-width="1" d="M0 0 L10 8 L20 2 L30 1 L40 4 L50 9 L60 23 L70 24 L80 23 L90 26 L100 35 L110 40 L120 40 L130 37 L140 38 L150 30 L160 26 L170 23 L180 28 L190 24 L200 17 L210 21 L220 24 L230 24"></path>
                                            <path id="BNBBTCfill" fill="rgba(254,251,245,1)" stroke="none" d="M0 40 L0 0 L10 8 L20 2 L30 1 L40 4 L50 9 L60 23 L70 24 L80 23 L90 26 L100 35 L110 40 L120 40 L130 37 L140 38 L150 30 L160 26 L170 23 L180 28 L190 24 L200 17 L210 21 L220 24 L230 24 L230 40"></path>
                                        </g>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="userbuy?trade=LTC-USDT" class="statistics-widget-link">
                            <div class="statistics-widget-grid">
                                <div class="content">
                                    <h5>LTC/USDT</h5>
                                    <h6>
                                        <strong class="green-color"><? echo $transactions_24hrs_ltc_usdt['lastPrice'] ? Stringz::currency($transactions_24hrs_ltc_usdt['lastPrice'],2,4) : '0.00' ; ?></strong><!--  $0.05 -->
                                    </h6>
                                    <p>Volume : <? echo $transactions_24hrs_ltc_usdt['transactions_24hrs'] ? $transactions_24hrs_ltc_usdt['transactions_24hrs'] : '0.00'; ?> BTC</p>
                                </div>
                                <span class="status green-color"><? echo $transactions_24hrs_ltc_usdt['change_24hrs'] ? $transactions_24hrs_ltc_usdt['change_24hrs'] : '0.00'; ?></span>
                                <div class="chart-bar">
                                    <svg version="1.1" class="highcharts-root" xmlns="http://www.w3.org/2000/svg">
                                        <g transform="translate(0.5,0.5)">
                                            <path id="TRXBTC" stroke="rgba(244,220,174,1)" fill="none" stroke-width="1" d="M0 30 L10 27 L20 35 L30 38 L40 22 L50 38 L60 38 L70 35 L80 30 L90 30 L100 35 L110 40 L120 35 L130 38 L140 32 L150 30 L160 35 L170 30 L180 25 L190 13 L200 0 L210 13 L220 10 L230 13"></path>
                                            <path id="TRXBTCfill" fill="rgba(254,251,245,1)" stroke="none" d="M0 40 L0 30 L10 27 L20 35 L30 38 L40 22 L50 38 L60 38 L70 35 L80 30 L90 30 L100 35 L110 40 L120 35 L130 38 L140 32 L150 30 L160 35 L170 30 L180 25 L190 13 L200 0 L210 13 L220 10 L230 13 L230 40"></path>
                                        </g>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="userbuy?trade=BCH-USDT" class="statistics-widget-link">
                            <div class="statistics-widget-grid">
                                <div class="content">
                                    <h5>BCH/USDT</h5>
                                    <h6>
                                        <strong class="green-color"><? echo $transactions_24hrs_bch_usdt['lastPrice'] ? Stringz::currency($transactions_24hrs_bch_usdt['lastPrice'],2,4) : '0.00' ; ?></strong><!--  $0.05 -->
                                    </h6>
                                    <p>Volume : <? echo $transactions_24hrs_bch_usdt['transactions_24hrs'] ? $transactions_24hrs_bch_usdt['transactions_24hrs'] : '0.00'; ?> BTC</p>
                                </div>
                                <span class="status green-color"><? echo $transactions_24hrs_bch_usdt['transactions_24hrs'] ? $transactions_24hrs_bch_usdt['change_24hrs'] : '0.00'; ?></span>
                                <div class="chart-bar">
                                    <svg version="1.1" class="highcharts-root" xmlns="http://www.w3.org/2000/svg">
                                        <g transform="translate(0.5,0.5)">
                                            <path id="RPXBTC" stroke="rgba(244,220,174,1)" fill="none" stroke-width="1" d="M0 13 L10 0 L20 15 L30 19 L40 20 L50 24 L60 24 L70 25 L80 22 L90 26 L100 22 L110 22 L120 32 L130 33 L140 36 L150 30 L160 37 L170 40 L180 35 L190 33 L200 34 L210 32 L220 34 L230 29"></path>
                                            <path id="RPXBTCfill" fill="rgba(254,251,245,1)" stroke="none" d="M0 40 L0 13 L10 0 L20 15 L30 19 L40 20 L50 24 L60 24 L70 25 L80 22 L90 26 L100 22 L110 22 L120 32 L130 33 L140 36 L150 30 L160 37 L170 40 L180 35 L190 33 L200 34 L210 32 L220 34 L230 29 L230 40"></path>
                                        </g>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="userbuy?trade=ZEC-USDT" class="statistics-widget-link">
                            <div class="statistics-widget-grid">
                                <div class="content">
                                    <h5>ZEC/USDT</h5>
                                    <h6>
                                        <strong class="green-color"><? echo $transactions_24hrs_zec_usdt['lastPrice'] ? Stringz::currency($transactions_24hrs_zec_usdt['lastPrice'],2,4) : '0.00' ; ?></strong><!--  $0.05 -->
                                    </h6>
                                    <p>Volume : <? echo $transactions_24hrs_zec_usdt['transactions_24hrs'] ? $transactions_24hrs_zec_usdt['transactions_24hrs'] : '0.00'; ?> BTC</p>
                                </div>
                                <span class="status green-color"><? echo $transactions_24hrs_zec_usdt['transactions_24hrs'] ? $transactions_24hrs_zec_usdt['change_24hrs'] : '0.00'; ?></span>
                                <div class="chart-bar">
                                    <svg version="1.1" class="highcharts-root" xmlns="http://www.w3.org/2000/svg">
                                        <g transform="translate(0.5,0.5)">
                                            <path id="GTOBTC" stroke="rgba(244,220,174,1)" fill="none" stroke-width="1" d="M0 7 L10 6 L20 8 L30 9 L40 3 L50 0 L60 6 L70 3 L80 8 L90 3 L100 3 L110 14 L120 12 L130 32 L140 38 L150 36 L160 35 L170 40 L180 37 L190 27 L200 32 L210 37 L220 39 L230 39"></path>
                                            <path id="GTOBTCfill" fill="rgba(254,251,245,1)" stroke="none" d="M0 40 L0 7 L10 6 L20 8 L30 9 L40 3 L50 0 L60 6 L70 3 L80 8 L90 3 L100 3 L110 14 L120 12 L130 32 L140 38 L150 36 L160 35 L170 40 L180 37 L190 27 L200 32 L210 37 L220 39 L230 39 L230 40"></path>
                                        </g>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </div>
                     <div class="col">
                        <a href="userbuy?trade=ZEC-USDT" class="statistics-widget-link">
                            <div class="statistics-widget-grid">
                                <div class="content">
                                    <h5>IOX/USDT</h5>
                                    <h6>
                                        <strong class="green-color"><? echo $transactions_24hrs_iox_usdt['lastPrice'] ? Stringz::currency($transactions_24hrs_iox_usdt['lastPrice'],2,4) : '0.00' ; ?></strong><!--  $0.05 -->
                                    </h6>
                                    <p>Volume : <? echo $transactions_24hrs_iox_usdt['transactions_24hrs'] ? $transactions_24hrs_iox_usdt['transactions_24hrs'] : '0.00'; ?> IOX</p>
                                </div>
                                <span class="status green-color"><? echo $transactions_24hrs_iox_usdt['transactions_24hrs'] ? $transactions_24hrs_iox_usdt['change_24hrs'] : '0.00'; ?></span>
                                <div class="chart-bar">
                                  <!--   <svg version="1.1" class="highcharts-root" xmlns="http://www.w3.org/2000/svg">
                                        <g transform="translate(0.5,0.5)">
                                            <path id="GTOBTC" stroke="rgba(244,220,174,1)" fill="none" stroke-width="1" d="M0 7 L10 6 L20 8 L30 9 L40 3 L50 0 L60 6 L70 3 L80 8 L90 3 L100 3 L110 14 L120 12 L130 32 L140 38 L150 36 L160 35 L170 40 L180 37 L190 27 L200 32 L210 37 L220 39 L230 39"></path>
                                            <path id="GTOBTCfill" fill="rgba(254,251,245,1)" stroke="none" d="M0 40 L0 7 L10 6 L20 8 L30 9 L40 3 L50 0 L60 6 L70 3 L80 8 L90 3 L100 3 L110 14 L120 12 L130 32 L140 38 L150 36 L160 35 L170 40 L180 37 L190 27 L200 32 L210 37 L220 39 L230 39 L230 40"></path>
                                        </g>
                                    </svg> -->
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="graph-outer">
                    <!-- TradingView Widget BEGIN -->

                    <!-- <form name="chat_filter" method="get" action=""> -->
                            <!-- <input type="hidden" name="trade" value="<?php echo $_REQUEST['trade']; ?>"> -->
                        <!-- <div class="row">
                            <div class="col-md-2 col-sm-6 col-xs-6">
                                <select class="form-group form-control" name="currency"> -->
                                <!--  <?php while($currency = mysqli_fetch_assoc($currency_query)) { ?>
                                        <option 
                                        <?php if($_REQUEST['currency'] == $currency['id']) { 
                                           $g_name = $currency['currency']; ?> 
                                        selected
                                        <?php } ?>
                                        value="<?php echo $currency['id']; ?>">
                                            <?php echo $currency['currency']; ?>
                                        </option>
                                    <?php } ?> -->
                                <?php
                                // if ($_REQUEST['currency'] != 28) {
                                //     ?>
                                   <!-- <option value="<?php echo $explode_value[0];?>-<?php echo $explode_value[1];?>-<?php echo $explode_value[2];?>-<?php echo $explode_value[3]; ?>"><?php echo $explode_value[0]; ?>/<?php echo $explode_value[1]; ?></option> -->
                                     <?php
                                //     $g_name = $explode_value[0].'- '.$explode_value[1];
                                    // echo $g_name ;
                                // }
                                ?>
                                
                                <!-- <option value="BTC-USD-28-27">BTC/USD</option>
                                <option value="LTC-USD-42-27">LTC/USD</option>
                                <option value="ETH-USD-45-27">ETH/USD</option>
                                <option value="BCH-USD-44-27">BCH/USD</option>
                                <option value="ZEC-USD-43-27">ZEC/USD</option>
                                <option value="IOX-USD-50-27">IOX/USD</option> -->
                              <!--   <option value="BTC-USDT-28-51">BTC/USDT</option>
                                <option value="LTC-USDT-42-51">LTC/USDT</option>
                                <option value="ETH-USDT-45-51">ETH/USDT</option>
                                <option value="BCH-USDT-44-51">BCH/USDT</option>
                                <option value="ZEC-USDT-43-51">ZEC/USDT</option>
                                <option value="IOX-USDT-50-51">IOX/USDT</option> 

                                <option value="LTC-BTC-42-28">LTC/BTC</option>
                                <option value="ETH-BTC-45-28">ETH/BTC</option>
                                <option value="BCH-BTC-44-28">BCH/BTC</option>
                                <option value="ZEC-BTC-43-28">ZEC/BTC</option>
                                <option value="IOX-BTC-50-28">IOX/BTC</option>
                                <option value="USDT-BTC-51-28">USDT/BTC</option> -->

                               <!--  <option value="BTC-LTC-28-42">BTC/LTC</option>
                                <option value="ETH-LTC-45-42">ETH/LTC</option>
                                <option value="ZEC-LTC-43-42">ZEC/LTC</option>
                                <option value="BCH-LTC-44-42">BCH/LTC</option>
                                <option value="IOX-LTC-50-42">IOX/LTC</option>
                                <option value="USDT-LTC-51-42">USDT/LTC</option>

                                <option value="BTC-BCH-28-44">BTC/BCH</option>
                                <option value="ETH-BCH-45-44">ETH/BCH</option>
                                <option value="ZEC-BCH-43-44">ZEC/BCH</option>
                                <option value="LTC-BCH-42-44">LTC/BCH</option>
                                <option value="IOX-BCH-50-44">IOX/BCH</option>
                                <option value="USDT-BCH-51-44">USDT/BCH</option>

                                <option value="BTC-ZEC-28-43">BTC/ZEC</option>
                                <option value="LTC-ZEC-42-43">LTC/ZEC</option>
                                <option value="ETH-ZEC-45-43">ETH/ZEC</option>
                                <option value="BCH-ZEC-44-43">BCH/ZEC</option>
                                <option value="IOX-ZEC-50-43">IOX/ZEC</option>
                                <option value="USDT-ZEC-51-43">USDT/ZEC</option> -->

                                <!-- <option value="BTC-ETH-28-45">BTC/ETH</option>
                                <option value="LTC-ETH-42-45">LTC/ETH</option>
                                <option value="BCH-ETH-44-45">BCH/ETH</option>
                                <option value="ZEC-ETH-43-45">ZEC/ETH</option>
                                <option value="IOX-ETH-50-45">IOX/ETH</option>
                                <option value="USDT-ETH-51-45">USDT/ETH</option>

                                <option value="BTC-ETH-28-50">BTC/IOX</option>
                                <option value="LTC-ETH-42-50">LTC/IOX</option>
                                <option value="BCH-ETH-44-50">BCH/IOX</option>
                                <option value="ZEC-ETH-43-50">ZEC/IOX</option>
                                <option value="IOX-ETH-45-50">ETH/IOX</option>
                                <option value="USDT-ETH-51-50">USDT/IOX</option>

                                </select>
                            </div>
                            <div class="col-md-8 col-sm-6 col-xs-6">
                                <button style="width: 10%;padding-left: 13px;" class="btn btn-primary btn-change">GO</button>
                            </div>
                        </div>
                        </form> -->
                     <div id="chart_div"></div>

                    <!-- TradingView Widget END -->
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="home-data-table">
                            <nav class="nav-justified">
                                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                    <a class="nav-item nav-link active" id="nav-iox-tab" data-toggle="tab" href="#nav-iox" role="tab" aria-controls="nav-iox" aria-selected="false">IOX Markets</a>
                                    <a class="nav-item nav-link" id="nav-btc-tab" data-toggle="tab" href="#nav-btc" role="tab" aria-controls="nav-btc" aria-selected="false">BTC Markets</a>
                                    <a class="nav-item nav-link" id="nav-eth-tab" data-toggle="tab" href="#nav-eth" role="tab" aria-controls="nav-eth" aria-selected="false">ETH Markets</a>
                                    <a class="nav-item nav-link" id="nav-usdt-tab" data-toggle="tab" href="#nav-usdt" role="tab" aria-controls="nav-usdt" aria-selected="true">USDT Markets</a>
                                </div>
                            </nav>
                            <div class="tab-content" id="nav-tabContent">


                                <div class="tab-pane fade show active" id="nav-iox" role="tabpanel" aria-labelledby="nav-iox-tab">
                                    <table id="hm-data-table" class="table row-border hm-data-table table-hover" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>Pair</th>
                                                <th>Last Price</th>
                                                <th>24h Change</th>
                                                <th>24h Volume</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr >
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        BTC/IOX
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_iox_iox['lastPrice'] ? Stringz::currency($transactions_24hrs_iox_iox['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_iox_iox['change_24hrs'] ? $transactions_24hrs_iox_iox['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_iox_iox['transactions_24hrs'] ? $transactions_24hrs_iox_iox['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        LTC/IOX
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_ltc_iox['lastPrice'] ? Stringz::currency($transactions_24hrs_ltc_iox['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_ltc_iox['change_24hrs'] ? $transactions_24hrs_ltc_iox['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_ltc_iox['transactions_24hrs'] ? $transactions_24hrs_ltc_iox['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        ETH/IOX
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_eth_iox['lastPrice'] ? Stringz::currency($transactions_24hrs_eth_iox['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_eth_iox['change_24hrs'] ? $transactions_24hrs_eth_iox['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_eth_iox['transactions_24hrs'] ? $transactions_24hrs_eth_iox['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        BCH/IOX
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_bch_iox['lastPrice'] ? Stringz::currency($transactions_24hrs_bch_iox['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_bch_iox['change_24hrs'] ? $transactions_24hrs_bch_iox['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_bch_iox['transactions_24hrs'] ? $transactions_24hrs_bch_iox['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        ZEC/IOX
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_zec_iox['lastPrice'] ? Stringz::currency($transactions_24hrs_zec_iox['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_zec_iox['change_24hrs'] ? $transactions_24hrs_zec_iox['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_zec_iox['transactions_24hrs'] ? $transactions_24hrs_zec_iox['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        USDT/IOX
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_usdt_iox['lastPrice'] ? Stringz::currency($transactions_24hrs_usdt_iox['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_usdt_iox['change_24hrs'] ? $transactions_24hrs_usdt_iox['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_usdt_iox['transactions_24hrs'] ? $transactions_24hrs_usdt_iox['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    </div>
                                
                                <div class="tab-pane fade" id="nav-btc" role="tabpanel" aria-labelledby="nav-btc-tab">
                                    <table id="hm-data-table" class="table row-border hm-data-table table-hover" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>Pair</th>
                                                <th>Last Price</th>
                                                <th>24h Change</th>
                                                <th>24h Volume</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        LTC/BTC
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_ltc_btc['lastPrice'] ? Stringz::currency($transactions_24hrs_ltc_btc['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_ltc_btc['change_24hrs'] ? $transactions_24hrs_ltc_btc['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_ltc_btc['transactions_24hrs'] ? $transactions_24hrs_ltc_btc['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        BCH/BTC
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_bch_btc['lastPrice'] ? Stringz::currency($transactions_24hrs_bch_btc['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_bch_btc['change_24hrs'] ? $transactions_24hrs_bch_btc['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_bch_btc['transactions_24hrs'] ? $transactions_24hrs_bch_btc['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        ETH/BTC
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_eth_btc['lastPrice'] ? Stringz::currency($transactions_24hrs_eth_btc['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_eth_btc['change_24hrs'] ? $transactions_24hrs_eth_btc['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_eth_btc['transactions_24hrs'] ? $transactions_24hrs_eth_btc['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        ZEC/BTC
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_zec_btc['lastPrice'] ? Stringz::currency($transactions_24hrs_zec_btc['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_zec_btc['change_24hrs'] ? $transactions_24hrs_zec_btc['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_zec_btc['transactions_24hrs'] ? $transactions_24hrs_zec_btc['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        IOX/BTC
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_iox_btc['lastPrice'] ? Stringz::currency($transactions_24hrs_iox_btc['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_iox_btc['change_24hrs'] ? $transactions_24hrs_iox_btc['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_iox_btc['transactions_24hrs'] ? $transactions_24hrs_iox_btc['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        USDT/BTC
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_usdt_btc['lastPrice'] ? Stringz::currency($transactions_24hrs_usdt_btc['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_usdt_btc['change_24hrs'] ? $transactions_24hrs_usdt_btc['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_usdt_btc['transactions_24hrs'] ? $transactions_24hrs_usdt_btc['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="nav-eth" role="tabpanel" aria-labelledby="nav-eth-tab">
                                    <table id="hm-data-table" class="table row-border hm-data-table table-hover" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>Pair</th>
                                                <th>Last Price</th>
                                                <th>24h Change</th>
                                                <th>24h Volume</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr >
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        BTC/ETH
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_iox_iox['lastPrice'] ? Stringz::currency($transactions_24hrs_iox_iox['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_iox_iox['change_24hrs'] ? $transactions_24hrs_iox_iox['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_iox_iox['transactions_24hrs'] ? $transactions_24hrs_iox_iox['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        LTC/ETH
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_ltc_iox['lastPrice'] ? Stringz::currency($transactions_24hrs_ltc_iox['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_ltc_iox['change_24hrs'] ? $transactions_24hrs_ltc_iox['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_ltc_iox['transactions_24hrs'] ? $transactions_24hrs_ltc_iox['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        BCH/ETH
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_bch_iox['lastPrice'] ? Stringz::currency($transactions_24hrs_bch_iox['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_bch_iox['change_24hrs'] ? $transactions_24hrs_bch_iox['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_bch_iox['transactions_24hrs'] ? $transactions_24hrs_bch_iox['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        ZEC/ETH
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_zec_iox['lastPrice'] ? Stringz::currency($transactions_24hrs_zec_iox['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_zec_iox['change_24hrs'] ? $transactions_24hrs_zec_iox['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_zec_iox['transactions_24hrs'] ? $transactions_24hrs_zec_iox['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        IOX/ETH
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_iox_iox['lastPrice'] ? Stringz::currency($transactions_24hrs_iox_iox['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_iox_iox['change_24hrs'] ? $transactions_24hrs_iox_iox['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_iox_iox['transactions_24hrs'] ? $transactions_24hrs_iox_iox['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        USDT/ETH
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_usdt_eth['lastPrice'] ? Stringz::currency($transactions_24hrs_usdt_eth['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_usdt_eth['change_24hrs'] ? $transactions_24hrs_usdt_eth['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_usdt_eth['transactions_24hrs'] ? $transactions_24hrs_usdt_eth['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    </div>

                                    <div class="tab-pane fade" id="nav-usdt" role="tabpanel" aria-labelledby="nav-usdt-tab">
                                    <table id="hm-data-table" class="table row-border hm-data-table table-hover" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>Pair</th>
                                                <th>Last Price</th>
                                                <th>24h Change</th>
                                                <th>24h Volume</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr >
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        BTC/USDT
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_btc_usdt['lastPrice'] ? Stringz::currency($transactions_24hrs_btc_usdt['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_btc_usdt['change_24hrs'] ? $transactions_24hrs_btc_usdt['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_btc_usdt['transactions_24hrs'] ? $transactions_24hrs_btc_usdt['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        LTC/USDT
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_ltc_usdt['lastPrice'] ? Stringz::currency($transactions_24hrs_ltc_usdt['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_ltc_usdt['change_24hrs'] ? $transactions_24hrs_ltc_usdt['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_ltc_usdt['transactions_24hrs'] ? $transactions_24hrs_ltc_usdt['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        BCH/USDT
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_bch_usdt['lastPrice'] ? Stringz::currency($transactions_24hrs_bch_usdt['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_bch_usdt['change_24hrs'] ? $transactions_24hrs_bch_usdt['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_bch_usdt['transactions_24hrs'] ? $transactions_24hrs_bch_usdt['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        ZEC/USDT
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_zec_usdt['lastPrice'] ? Stringz::currency($transactions_24hrs_zec_usdt['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_zec_usdt['change_24hrs'] ? $transactions_24hrs_zec_usdt['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_zec_usdt['transactions_24hrs'] ? $transactions_24hrs_zec_usdt['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        ETH/USDT
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_eth_usdtt['lastPrice'] ? Stringz::currency($transactions_24hrs_eth_usdtt['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_eth_usdtt['change_24hrs'] ? $transactions_24hrs_eth_usdtt['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_eth_usdtt['transactions_24hrs'] ? $transactions_24hrs_eth_usdtt['transactions_24hrs'] : '0.00' ; ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="star-inner">
                                                        <input id="star1" type="checkbox" name="time" />
                                                        <!-- <label for="star1"><i class="fas fa-star"></i></label> -->
                                                        IOX/USDT
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="green-color"><? echo $transactions_24hrs_iox_usdt['lastPrice'] ? Stringz::currency($transactions_24hrs_iox_usdt['lastPrice'],2,4) : '0.00' ; ?></span> <!-- <span class="gray-color">/ $944.16</span> -->
                                                </td>
                                                <td><span class="red-color"><? echo $transactions_24hrs_iox_usdt['change_24hrs'] ? $transactions_24hrs_iox_usdt['change_24hrs'] : '0.00' ; ?></span></td>
                                                <td><? echo $transactions_24hrs_iox_usdt['transactions_24hrs'] ? $transactions_24hrs_iox_usdt['transactions_24hrs'] : '0.00' ; ?></td>
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
        <?php 
        $sql = "SELECT A.date,A.btc_price,B.currency FROM `transactions` A,`currencies` B WHERE A.c_currency = B.id AND  A.c_currency = $c_currency_id  AND A.currency = $currency_id1";
        $my_query = mysqli_query($conn,$sql);
        // echo "string";
        // print_r(mysqli_fetch_assoc($my_query));
        ?>
        <img src="images/banner2.jpg" alt="banner" class="responsive" id="banner1">
        <?php include "includes/sonance_footer.php"; ?>

        <script type='text/javascript'>


        google.charts.load('current', {'packages':['annotatedtimeline']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('date', 'Date');
        data.addColumn('number', '<?php echo $g_name; ?>');
        data.addColumn('string', 'title1');
        data.addColumn('string', 'text1');
        data.addRows([
            <?php while ($value = mysqli_fetch_assoc($my_query)) { 

                $d = date("d",strtotime($value['date']));
                $y = date("Y",strtotime($value['date']));
                $m = date("m",strtotime($value['date']));

                ?>
           [new Date(<?php echo $y; ?>, <?php echo $m-1; ?> ,<?php echo $d; ?>), <?php echo $value['btc_price']; ?>, undefined, undefined] ,
        <?php } if(!mysqli_fetch_assoc($my_query)){ 
            
            $year = date("Y");
            $month = date("m");
            $date = date("d"); ?>
            [new Date(<?php echo $year; ?>, <?php echo $month-1; ?> ,<?php echo $date; ?>), 0, undefined, undefined] 
            
            <?php } ?>
        ]);

        var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('chart_div'));
        chart.draw(data, {displayAnnotations: false});
      }
    

    //load_chart();
    </script>

        <!-- <div class="fb_chat" style=""> 
            <a href="https://www.messenger.com/t/194868894428597" class="fb-msg-btn-chat" target="_blank" rel="nofollow"> Contact us on Facebook</a> 
        </div> -->
</html>