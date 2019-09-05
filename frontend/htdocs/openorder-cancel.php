<?php 
include '../lib/common.php';       

$buysell = $_REQUEST['buysell'];

$c_currencyy1 = $_REQUEST['c_currency'];
$currencyy1 = $_REQUEST['currency'];

$currencies = Settings::sessionCurrency();
$currency1 = $currencies['currency'];
$c_currency1 = $currencies['c_currency'];

$currency_info = $CFG->currencies[$currency1];
$c_currency_info = $CFG->currencies[$c_currency1];

$delete_id1 = (!empty($_REQUEST['delete_id'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['delete_id']) : false;
if ($delete_id1 > 0) {
API::add('Orders','getRecord',array($delete_id1));
$query = API::send();
$del_order = $query['Orders']['getRecord']['results'][0];

if (!$del_order) {
echo 0;
echo '~';
echo 'Order does not Exist!';
}
elseif ($del_order['site_user'] != $del_order['user_id'] || !($del_order['id'] > 0)) {
echo 0;
echo '~';
echo 'This Order is not yours!';
}
else {
API::add('Orders','delete',array($delete_id1));
$query = API::send();
echo 1;
echo '~';
echo 'Order Cancelled Successfully!';
}
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
$_SESSION["openorders_uniq"] = md5(uniqid(mt_rand(),true));


echo '~';

if($buysell=='buy') {
?>


<table class="table table-striped order-table-fixed" id="right-data-table">
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

<a class="buy_open_order_loader'.$bid['id'].'" style="display:none;"><img src="images/loader.gif" style="width:50%;"/></a>

<a onclick="buy_cancel_order(\''.$bid['id'].'\',\''.$_SESSION["openorders_uniq"].'\',\''.$c_currencyy1.'\',\''.$currencyy1.'\',\'buy_open_orders_table\');" title="'.Lang::string('orders-delete').'" class="remove_icon'.$bid['id'].'"><i class="fa fa-times"></i></a></td>
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

<?php } else if($buysell=='sell') { ?>

<table class="table table-striped order-table-fixed" id="right-data-table1">
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

<a class="sell_open_order_loader'.$ask['id'].'" style="display:none;"><img src="images/loader.gif" style="width:50%;"/></a>

<a onclick="sell_cancel_order(\''.$ask['id'].'\',\''.$_SESSION["openorders_uniq"].'\',\''.$c_currencyy1.'\',\''.$currencyy1.'\',\'sell_open_orders_table\');" title="'.Lang::string('orders-delete').'" class="remove_icon_sell'.$ask['id'].'"><i class="fa fa-times"></i></a>

</td>
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

<script type="text/javascript">
$(document).ready(function() {
$('#right-data-table').DataTable({ });
$('#right-data-table1').DataTable({ });
});
</script>

<!-- Chart Header -->

<?php 
API::add('Transactions','get24LowestData',array($c_currency1,$currency1)); 
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
       
    API::add('Transactions','get24hData',array(27,52)); //poly-usd
       
    API::add('Transactions','get24hData',array(52,27)); //usd-poly

$query = API::send();
$low24price = $query['Transactions']['get24LowestData']['results'][0][0]['lowest'];
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


    $transactions_24hrs_usd_poly = $query['Transactions']['get24hData']['results'][42] ;
    $transactions_24hrs_poly_usd = $query['Transactions']['get24hData']['results'][43] ;

echo '~';
?>

<div class="col-md-4 col-sm-6 col-xs-12">
<div class="chart-title">
                                <h6 class="name">Paxion Exchange</h6>
                                <p class="code"><?= $c_currency_info['currency'] ?>/<?= $currency_info['currency']?></p>
                            </div>
</div>
<div class="col-md-8 col-sm-6 col-xs-12">
<div class="chart-hilights">
                                <div class="row">
                                    <div class="lastPrice">
                                        <div class="name">Last Price</div>
                                        <div class="info"><?= number_format($currentPair['lastPrice'], 8) ?></div>
                                    </div>
                                    <div class="change">
                                        <div class="name">24hr Change</div>
                                        <div class="info"><?= number_format($currentPair['change_24hrs'], 8) ?></div>
                                    </div>
                                    <div class="high">
                                        <div class="name">24hr High</div>
                                        <div class="info">0.000000</div>
                                    </div>
                                    <div class="low no-border">
                                        <div class="name">24hr Low</div>
                                        <div class="info"><?= number_format($low24price, 8) ?> <?= $currency_info['currency'] ?></div>
                                    </div>
                                </div>
                                <div class="row no-border">
                                    <div class="volume no-border">
                                        <div class="name">24hr Volume:</div>
                                        <div class="info"><strong><?= number_format($currentPair['change_24hrs'], 8) ?></strong> <span class="name name1"><?= $c_currency_info['currency'] ?></span> / <strong><?= number_format($low24price, 8) ?> <?= $currency_info['currency'] ?></strong> <span class="name name2"><?= $currency_info['currency']?></span></div>
                                    </div>
                                </div>
                            </div>
</div>

<!-- End of Chart Header -->

<!-- Markets Block -->

<?php 
echo '~';

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
       
    API::add('Transactions','get24hData',array(27,52)); //poly-usd
       
    API::add('Transactions','get24hData',array(52,27)); //usd-poly

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


    $transactions_24hrs_usd_poly = $query['Transactions']['get24hData']['results'][42] ;
    $transactions_24hrs_poly_usd = $query['Transactions']['get24hData']['results'][43] ;
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
                                                <a class="nav-link" id="r-usdt-tab" data-toggle="tab" href="#r-usdt" role="tab" aria-controls="r-usdt" aria-selected="false">USDT</a>
                                            </li>
                                        </ul>
<div class="tab-content" id="myTabContent">
                                             <?php
                                                if($active_currency_id == 51)
                                                    $class_active = "active show";
                                                else
                                                    $class_active = "";
                                            ?>
                                            <div class="tab-pane fade <?php echo $class_active;?>" id="r-usdt" role="tabpanel" aria-labelledby="r-usdt-tab">
                                                <table class="table row-border table-hover" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th><label for="star1"><i class="fas fa-star" style="font-size: 10px;margin-left: -4px;"></i></label></th>
                                                            <th>Coin</th>
                                                            <th>Price</th>
                                                            <!-- <th>Volume</th> -->
                                                            <th>Change</th>
                                                            <!-- <th>Name</th> -->
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'IOX' && $currency_info['currency'] == 'USDT') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=IOX-USDT&c_currency=50&currency=51">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>IOX</td>
                                                            <td><span class=""><?= $transactions_24hrs__iox_usdt['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.724</span></td> -->
                                                            <td><span class="red-color"><?= $transactions_24hrs__iox_usdt['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">BitCrystals</span></td> -->
                                                        </tr>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'BTC' && $currency_info['currency'] == 'USDT') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=BTC-USDT&c_currency=28&currency=51">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>BTC</td>
                                                            <td><span class=""><?= $transactions_24hrs_btc_usd['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.559</span></td> -->
                                                            <td><span class="green-color"><?= $transactions_24hrs_btc_usd['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">PeerCoin</span></td> -->
                                                        </tr>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'ETH' && $currency_info['currency'] == 'USDT') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=ETH-USDT&c_currency=45&currency=51">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>ETH</td>
                                                            <td><span class=""><?= $transactions_24hrs_eth_usd['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.724</span></td> -->
                                                            <td><span class="red-color"><?= $transactions_24hrs_eth_usd['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">BitCrystals</span></td> -->
                                                        </tr>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'BCH' && $currency_info['currency'] == 'USDT') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=BCH-USDT&c_currency=44&currency=51">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>BCH</td>
                                                            <td><span class=""><?= $transactions_24hrs_bch_usd['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.559</span></td> -->
                                                            <td><span class="green-color"><?= $transactions_24hrs_bch_usd['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">PeerCoin</span></td> -->
                                                        </tr>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'LTC' && $currency_info['currency'] == 'USDT') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=LTC-USDT&c_currency=42&currency=51">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>LTC</td>
                                                            <td><span class=""><?= $transactions_24hrs_ltc_usd['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.724</span></td> -->
                                                            <td><span class="red-color"><?= $transactions_24hrs_ltc_usd['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">BitCrystals</span></td> -->
                                                        </tr>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'ZEC' && $currency_info['currency'] == 'USDT') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=ZEC-USDT&c_currency=43&currency=51">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>ZEC</td>
                                                            <td><span class=""><?= $transactions_24hrs_zec_usd['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.559</span></td> -->
                                                            <td><span class="green-color"><?= $transactions_24hrs_zec_usd['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">PeerCoin</span></td> -->
                                                        </tr>
                                                        
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="tab-pane fade" id="r-eth" role="tabpanel" aria-labelledby="r-eth-tab">
                                                <table class="table row-border table-hover" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th><label for="star1"><i class="fas fa-star" style="font-size: 10px;margin-left: -4px;"></i></label></th>
                                                            <th>Coin</th>
                                                            <th>Price</th>
                                                            <!-- <th>Volume</th> -->
                                                            <th>Change</th>
                                                            <!-- <th>Name</th> -->
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'IOX' && $currency_info['currency'] == 'ETH') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=IOX-ETH&c_currency=50&currency=45">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>IOX</td>
                                                            <td><span class=""><?= $transactions_24hrs_iox_eth['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.724</span></td> -->
                                                            <td><span class="red-color"><?= $transactions_24hrs_iox_eth['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">BitCrystals</span></td> -->
                                                        </tr>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'BCH' && $currency_info['currency'] == 'ETH') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=BCH-ETH&c_currency=44&currency=45">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>BCH</td>
                                                            <td><span class=""><?= $transactions_24hrs_bch_eth['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.559</span></td> -->
                                                            <td><span class="green-color"><?= $transactions_24hrs_bch_eth['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">PeerCoin</span></td> -->
                                                        </tr>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'ZEC' && $currency_info['currency'] == 'ETH') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=ZEC-ETH&c_currency=43&currency=45">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>ZEC</td>
                                                            <td><span class=""><?= $transactions_24hrs_zec_eth['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.724</span></td> -->
                                                            <td><span class="red-color"><?= $transactions_24hrs_zec_eth['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">BitCrystals</span></td> -->
                                                        </tr>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'LTC' && $currency_info['currency'] == 'ETH') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=LTC-ETH&c_currency=42&currency=45">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>LTC</td>
                                                            <td><span class=""><?= $transactions_24hrs_ltc_eth['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.559</span></td> -->
                                                            <td><span class="green-color"><?= $transactions_24hrs_ltc_eth['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">PeerCoin</span></td> -->
                                                        </tr>
                                                        
                                                    </tbody>
                                                </table>
                                            </div>
                                             <?php
                                                if($active_currency_id == 50)
                                                    $class_active = "active show";
                                                else
                                                    $class_active = "";
                                            ?>
                                            <div class="tab-pane fade" id="r-iox" role="tabpanel" aria-labelledby="r-iox-tab">
                                                <table class="table row-border table-hover" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th><label for="star1"><i class="fas fa-star" style="font-size: 10px;margin-left: -4px;"></i></label></th>
                                                            <th>Coin</th>
                                                            <th>Price</th>
                                                            <!-- <th>Volume</th> -->
                                                            <th>Change</th>
                                                            <!-- <th>Name</th> -->
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'BCH' && $currency_info['currency'] == 'IOX') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=BCH-IOX&c_currency=44&currency=50">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>BCH</td>
                                                            <td><span class=""><?= $transactions_24hrs_bch_iox['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.724</span></td> -->
                                                            <td><span class="red-color"><?= $transactions_24hrs_bch_iox['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">BitCrystals</span></td> -->
                                                        </tr>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'LTC' && $currency_info['currency'] == 'IOX') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=LTC-IOX&c_currency=42&currency=50">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>LTC</td>
                                                            <td><span class=""><?= $transactions_24hrs_ltc_iox['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.559</span></td> -->
                                                            <td><span class="green-color"><?= $transactions_24hrs_ltc_iox['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">PeerCoin</span></td> -->
                                                        </tr>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'ZEC' && $currency_info['currency'] == 'IOX') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=ZEC-IOX&c_currency=43&currency=50">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>ZEC</td>
                                                            <td><span class=""><?= $transactions_24hrs_zec_iox['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.724</span></td> -->
                                                            <td><span class="red-color"><?= $transactions_24hrs_zec_iox['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">BitCrystals</span></td> -->
                                                        </tr>
                                                        
                                                    </tbody>
                                                </table>
                                            </div>
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
                                                            <th><label for="star1"><i class="fas fa-star" style="font-size: 10px;margin-left: -4px;"></i></label></th>
                                                            <th>Coin</th>
                                                            <th>Price</th>
                                                            <!-- <th>Volume</th> -->
                                                            <th>Change</th>
                                                            <!-- <th>Name</th> -->
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'IOX' && $currency_info['currency'] == 'BTC') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=IOX-BTC&c_currency=50&currency=28">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>IOX</td>
                                                            <td><span class=""><?= $transactions_24hrs_iox_btc['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.724</span></td> -->
                                                            <td><span class="red-color"><?= $transactions_24hrs_iox_btc['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">BitCrystals</span></td> -->
                                                        </tr>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'ETH' && $currency_info['currency'] == 'BTC') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=ETH-BTC&c_currency=45&currency=28">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>ETH</td>
                                                            <td><span class=""><?= $transactions_24hrs_eth_btc['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.559</span></td> -->
                                                            <td><span class="green-color"><?= $transactions_24hrs_eth_btc['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">PeerCoin</span></td> -->
                                                        </tr>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'BCH' && $currency_info['currency'] == 'BTC') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=LTC-BTC&c_currency=42&currency=28">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>BCH</td>
                                                            <td><span class=""><?= $transactions_24hrs_ltc_btc['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.724</span></td> -->
                                                            <td><span class="red-color"><?= $transactions_24hrs_ltc_btc['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">BitCrystals</span></td> -->
                                                        </tr>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'ZEC' && $currency_info['currency'] == 'BTC') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=ZEC-BTC&c_currency=43&currency=28">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>ZEC</td>
                                                            <td><span class=""><?= $transactions_24hrs_zec_btc['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.559</span></td> -->
                                                            <td><span class="green-color"><?= $transactions_24hrs_bch_btc['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">PeerCoin</span></td> -->
                                                        </tr>
                                                        <tr class="clickable clickable-row <?= ($c_currency_info['currency'] == 'LTC' && $currency_info['currency'] == 'BTC') ? 'userbuy-active' : "" ?>" data-href="exchange_ui?trade=LTC-BTC&c_currency=42&currency=28">
                                                            <td>
                                                                <div class="star-inner">
                                                                    <input id="star1" type="checkbox" name="time" />
                                                                    <label for="star1"><i class="fas fa-star"></i></label>
                                                                    
                                                                </div>
                                                            </td>
                                                            <td>LTC</td>
                                                            <td><span class=""><?= $transactions_24hrs_ltc_btc['lastPrice'] ?></span> </td>
                                                            <!-- <td><span class="">3.724</span></td> -->
                                                            <td><span class="red-color"><?= $transactions_24hrs_ltc_btc['change_24hrs'] ?></span></td>
                                                            <!-- <td><span class="">BitCrystals</span></td> -->
                                                        </tr>
                                                        
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
</div>

<!-- End of Markets Block -->


<script type="text/javascript">
$(document).ready(function() {
$('.clickable-row').on('click', function(){
var href = $(this).data('href');
window.location.href = href;
})

});
</script>

<!-- Balance Block -->

<?php 
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


echo '~';
?>
<div class="col-md-6 col-sm-6 col-xs-12">
                            <h5 class="order-title">BUY ORDERS <!-- <span>Total: <b>0.000000</b> <?= $currency_info['currency'] ?></span> --></h5>
                            <div class="order-table color-table">
                                <table class="table table-striped order-table-fixed">
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
                                                if ($i=0) {
                                                	echo '<tr style=""><td colspan="4" style="padding: 0px;border-top: unset;">
                                                	<hr style="margin-top:  0px !important;margin-bottom:  0px !important;border-top: 1px solid #22842a !important;"></td></tr>';
                                                }
                                                
                                                echo '
                                            <tr id="bid_' . $bid['id'] . '" class="bid_tr buy_order">
                                                <td>'.$type.'</td>
                                                <td>' . $mine .  $currency_info['fa_symbol'] . '<span class="order_price_' . $bid['id'] . '">' . Stringz::currency1($bid['btc_price']) . '</span> ' . (($bid['btc_price'] != $bid['fiat_price']) ? '<a title="' . str_replace('[currency]', $CFG->currencies[$bid['currency']]['currency'], Lang::string('orders-converted-from')) . '" class="fa fa-exchange" href="" onclick="return false;"></a>' : '') . '</td>
                                                <td><span class="order_amount_' . $bid['id'] . '">' . Stringz::currency1($bid['btc'], true) . '</span> ' . $c_currency_info['currency'] . '</td>
                                                <td>' . $currency_info['fa_symbol'] . '<span class="order_value_' . $bid['id'] . '">' . Stringz::currency1(($bid['btc_price'] * $bid['btc'])) . '</span></td>
                                            </tr>';
                                                $i++;
                                            }
                                        }else{
                                               echo "<tr><td colspan='5'>No Buy Orders</td></tr>";
                                        }
                                        
                                        ?>
                                    </tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>

<!-- End of buy_orders_table -->

<!-- sell_orders_table -->

<?php 
echo '~';
?>
<div class="col-md-6 col-sm-6 col-xs-12">
                            <h5 class="order-title">SELL ORDERS <!-- <span>Total: <b>0.000000</b> <?= $c_currency_info['currency']  ?></span> --></h5>
                            <div class="order-table color-table" id="buy_orders_table">
                                <table class="table table-striped order-table-fixed">
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
                                                if ($i=0) {
                                                	echo '<tr style=""><td colspan="4" style="padding: 0px;border-top: unset;">
                                                	<hr style="margin-top:  0px !important;margin-bottom:  0px !important;border-top: 1px solid #be4940 !important;"></td></tr>';
                                                }
                                           
                                                echo '
                                                <tr id="ask_' . $ask['id'] . '" class="ask_tr sell_order">
                                                <td>'.$type.'</td>
                                                    <td>' . $mine . $currency_info['fa_symbol'] . '<span class="order_price_' . $ask['id'] . '">' . Stringz::currency1($ask['btc_price']) . '</span> ' . (($ask['btc_price'] != $ask['fiat_price']) ? '<a title="' . str_replace('[currency]', $CFG->currencies[$ask['currency']]['currency'], Lang::string('orders-converted-from')) . '" class="fa fa-exchange" href="" onclick="return false;"></a>' : '') . '</td>
                                                    <td><span class="order_amount_' . $ask['id'] . '">' . Stringz::currency1($ask['btc'], true) . '</span> ' . $c_currency_info['currency'] . '</td>
                                                    <td>' . $currency_info['fa_symbol'] . '<span class="order_value_' . $ask['id'] . '">' . Stringz::currency1(($ask['btc_price'] * $ask['btc'])) . '</span></td>
                                                </tr>';
                                                $i++;
                                            }
                                        }else{
                                                    echo "<tr><td colspan='5'>No Sell Orders</td></tr>";
                                        }
                                        
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
<script type="text/javascript">
$(document).ready(function() {
$('.buy_orders_table').DataTable({ });
$('.sell_orders_table').DataTable({ });
});
</script>


<!-- End of sell_orders_table -->


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

echo '~';
?>

                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="row">
                            	<?php 
                            	if(User::isLoggedIn()){  ?>
                                <div class="col-md-6 col-sm-6 col-xs-12 trade-history">
                                	<?php
                                }else{ ?>
                                	<div class="col-md-12 col-sm-12 col-xs-12 trade-history">
                               <?php }
                                	?>
                                    <label class="order-title">TRADE HISTORY</label>
                                    <div class="text-right">
                                    <ul class="nav nav-tabs btn-tab" id="myTab" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="r-btc-tab" data-toggle="tab" href="#mar-trades" role="tab" aria-controls="mar-trades" aria-selected="false">Market Trades</a>
                                        </li>
                                        <?php
                                            if(User::isLoggedIn()){
                                            ?>
                                        <li class="nav-item">
                                            <a class="nav-link" id="my-trades-tab" data-toggle="tab" href="#my-trades1" role="tab" aria-controls="r-eth" aria-selected="false">My Trades</a>
                                        </li>
                                        <?php
                                            }
                                        ?>
                                    </ul>
                                </div>

                                <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="mar-trades" role="tabpanel" aria-labelledby="mar-trades-tab">
                                	 <input type="hidden" id="refresh_transactions" value="1" />
                            <input type="hidden" id="page" value="<?= $page1 ?>" />
                                    <div class="order-table color-table">
                                        <table class="table table-striped order-table-fixed">
                                            <thead>
                                                <tr>
                                    <th><?= Lang::string('transactions-type') ?>1</th>
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
                                                    <td>' . Stringz::currency1($transaction['btc'], true) . ' ' . $CFG->currencies[$transaction['c_currency']]['fa_symbol'] . '</td>
                                                    <td><span class="currency_char">' . $trans_symbol . '</span><span>' . Stringz::currency1($transaction['btc_net'] * $transaction['fiat_price'], ($transaction['is_crypto'] == 'Y')) . '</span></td>
                                                    <td><span class="currency_char">' . $trans_symbol . '</span><span>' . Stringz::currency1($transaction['fiat_price'], ($transaction['is_crypto'] == 'Y')) . '</span></td>
                                                    <td><span class="currency_char">' . $trans_symbol . '</span><span>' . Stringz::currency1($transaction['fee'] * $transaction['fiat_price'], ($transaction['is_crypto'] == 'Y')) . '</span></td>
                                                </tr>';
                                            }
                                        }else{
                                               echo "<tr><td colspan='6'>No Transactions</td></tr>";
                                        }
                                        
                                        ?>
                                            </tbody>
                                        </table>
                                        <?= $pagination ?>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="my-trades1" role="tabpanel" aria-labelledby="my-trades-tab">
                                	<?php
                                        API::add('Transactions', 'getnew', array(false, $page1, 100, $c_currency1, $currency1, 1, $start_date1, $type1, $order_by1));
                                        API::add('Transactions', 'getTypes');
                                        $query = API::send();
                                        // print_r($query);
                                        $transactions = $query['Transactions']['getnew']['results'][0];
                                        // print_r($transactions);exit;
                                        $transaction_types = $query['Transactions']['getTypes']['results'][0];
                                        $pagination = Content::pagination('transactions.php', $page1, $total, 100, 5, false);
                                        ?>
                                    <div class="order-table color-table">
                                        <table class="table table-striped order-table-fixed">
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
                                                            <td>'.$transaction['date'].'<input type="hidden" class="localdate" value="' . (strtotime($transaction['date'])) . '" /></td>
                                                            <td>' . Stringz::currency1($transaction['btc'], true) . ' ' . $CFG->currencies[$transaction['c_currency']]['fa_symbol'] . '</td>
                                                            <td><span class="currency_char">' . $trans_symbol . '</span><span>' . Stringz::currency1($transaction['btc_net'] * $transaction['fiat_price'], ($transaction['is_crypto'] == 'Y')) . '</span></td>
                                                            <td><span class="currency_char">' . $trans_symbol . '</span><span>' . Stringz::currency1($transaction['fiat_price'], ($transaction['is_crypto'] == 'Y')) . '</span></td>
                                                            <td><span class="currency_char">' . $trans_symbol . '</span><span>' . Stringz::currency1($transaction['fee'] * $transaction['fiat_price'], ($transaction['is_crypto'] == 'Y')) . '</span></td>
                                                        </tr>';
                                                    }
                                                }else{
                                                    // echo '<tr id="no_transactions" style="' . (is_array($transactions) ? 'display:none;' : '') . '"><td colspan="6" style="padding: 0;"><div class="" style="background: #f4f6f8; text-align:  center;
                                                    //     "><img src="images/no-results.gif" style="width: 300px;height: auto;    float: none;" ></div></td></tr>';
                                                    echo "<tr><td colspan='6'>No Transactions</td></tr>";
                                                }
                                                
                                                ?>
                                        </tbody>
                                        </table>
                                        <?= $pagination ?>
                                    </div>
                                </div>
                            </div>
                        </div>

<script type="text/javascript">
	$('.trade_history_table1').DataTable({ });	
</script>

<script type="text/javascript">
$(document).ready(function() {
});
$('.buy_order').on('click', function(){
// alert(this.id);
var buy_tr = this.id;
var buy_order_id = buy_tr.replace('bid_', '');
// alert(buy_tr);
// alert(buy_order_id);
var order_price1 = ".order_price_"+buy_order_id;
var order_price = $(order_price1).text();
var order_amount1 = ".order_amount_"+buy_order_id;
var order_amount = $(order_amount1).text();
var order_value1 = ".order_value_"+buy_order_id;
var order_value = $(order_value1).text();
// alert(order_price);
$('.sell_price_table').val(order_price);
$('.sell_amount_table').val(order_amount);
// $('.sell_total_table').val(order_value);
calculateBuyPrice();
})



$('.sell_order').on('click', function(){
// alert(this.id);
var sell_tr = this.id;
var sell_order_id = sell_tr.replace('ask_', '');
// alert(sell_tr);
// alert(sell_order_id);
var order_price1 = ".order_price_"+sell_order_id;
var order_price = $(order_price1).text();
var order_amount1 = ".order_amount_"+sell_order_id;
var order_amount = $(order_amount1).text();
var order_value1 = ".order_value_"+sell_order_id;
var order_value = $(order_value1).text();
// alert(order_price);
$('.buy_price_table').val(order_price);
$('.buy_amount_table').val(order_amount);
// $('.buy_total_table').val(order_value);
calculateBuyPrice();
})
</script>

<!-- End of trade_history_table -->


<script type="text/javascript" src="js/ops_new1.js?v=20160210"></script>