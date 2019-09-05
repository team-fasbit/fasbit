<!DOCTYPE html>
<html lang="en">
<?php 
        include '../lib/common.php';

        if (User::$info['locked'] == 'Y' || User::$info['deactivated'] == 'Y')
            Link::redirect('settings.php');
        elseif (User::$awaiting_token)
            Link::redirect('verify-token.php');
        elseif (!User::isLoggedIn())
            Link::redirect('login.php');
        
        $currencies = Settings::sessionCurrency();
        // $page_title = Lang::string('order-book');
       API::add('User','getInfo',array($_SESSION['session_id']));
    $fetchUserDataQuery = API::send();
    $user_data = $fetchUserDataQuery['User']['getInfo']['results'][0];
    $user_id = $user_data['id']; 

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
        
        API::add('Transactions', 'get', array(1, $page1, 100, $c_currency1, $currency1, 1, $start_date1, $type1, $order_by1));
        $query = API::send();
        $total = $query['Transactions']['get']['results'][0];
        
        API::add('Transactions', 'getallcurriences', array(false, $page1, 100, $c_currency1, $currency1, 1, $start_date1, $type1, $order_by1));
        // API::add('Transactions', 'getnew', array(false, $page1, 100, $c_currency1, $currency1, 1, $start_date1, $type1, $order_by1));
        API::add('Transactions', 'getTypes');
        $query = API::send();
        // print_r($query);
        $transactions = $query['Transactions']['getallcurriences']['results'][0];
        // print_r($transactions);exit;
        $transaction_types = $query['Transactions']['getTypes']['results'][0];
        $pagination = Content::pagination('transactions.php', $page1, $total, 100, 5, false);
        
        $currency_info = ($currency1) ? $CFG->currencies[strtoupper($currency1)] : array();
        
        if ($trans_realized1 > 0)
            Messages::add(str_replace('[transactions]', $trans_realized1, Lang::string('transactions-done-message')));
?>
<head>
    <title><?= $CFG->exchange_name; ?> | Trade History</title>
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
        .table td, .table th {
            padding: .75rem !important;
            border-top: 1px solid #cbcfd4;
        }
        div#DataTables_Table_0_info {
            text-align: left;
        }
        li.paginate_button.page-item.active a.page-link {
            color: white !important;
        }
        .paginate_button.page-item a.page-link {
            padding: .5rem .75rem !important;
            color: #007bff;
        }
        input.form-control.form-control-sm {
            padding: .25rem .5rem !important;
            font-size: .875rem !important;
            line-height: 1.5 !important;
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
                <h1>MY TRADE HISTORY</h1>
            </div>
        </div>
    </header>
    <div class="page-container">
        <div class="container-fluid">
            <div class="row profile-banner">
                <div class="container content">
                     <div class="row">
                    <? Messages::display(); ?>
                    <? Errors::display(); ?>
                    <div class="col-md-12">
                        <!-- <form action="" class="form-inline" style="padding: 20px;background: white;margin-top: 20px;">
                            <div class="form-group">
                                <label for="sel1" style="font-size: 12px;">Currency Pair &nbsp;
                                </label>

                            </div>
                            <div class="form-group">
                                <select class="form-control custom-select" id="c_currency_select" style="width:100px;">
                                    <? if ($CFG->currencies): ?>
                                        <? foreach ($CFG->currencies as $key => $currency): ?>
                                            <? if (is_numeric($key) || $currency['is_crypto'] != 'Y') continue; ?>
                                            <option <?= $currency['id'] == $c_currency1 ? 'selected="selected"' : '' ?>  value="<?=$currency['id']?>">
                                                <?=$currency['currency'] ?>
                                            </option>
                                        <? endforeach; ?>
                                    <? endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-control custom-select" id="currency_select" style="margin-left:5px;width:100px;">
                                    <? if ($CFG->currencies): ?>
                                        <? foreach ($CFG->currencies as $key => $currency): ?>
                                            <? if (is_numeric($key) || $currency['id'] == $c_currency1) continue; ?>
                                            <?php if ($currency['id'] != 27) {?>
                                                
                                            <option <?= $currency['id'] == $currency1 ? 'selected="selected"' : '' ?>  value="<?=$currency['id']?>">
                                                <?=$currency['currency'] ?>
                                            </option>
                                            <?php }   ?>
                                        <? endforeach; ?>
                                    <? endif; ?>
                                </select>
                            </div>  -->
                            <!-- <div class="form-group" style="margin-left:10px">
                            <a class="download" href="transactions_downloaded.php?c_currency=<?php echo $c_currency1; ?>&currency=<?php echo $currency1; ?>" ><i class="fa fa-download"></i> <?= Lang::string('transactions-download') ?></a>
                            </div> -->
                         <!-- </form> -->
                       <!--  <span class="float-right">
                            <a href="#tradehistory" data-toggle="modal" style="    position: relative;top: -3em;right: 1em;">
                                <svg style="width:15px;height:15px;" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 50 50" xml:space="preserve">
                                <circle style="fill:#47a0dc" cx="25" cy="25" r="25"></circle>
                                <line style="fill:none;stroke:#FFFFFF;stroke-width:4;stroke-linecap:round;stroke-miterlimit:10;" x1="25" y1="37" x2="25" y2="39"></line>
                                <path style="fill:none;stroke:#FFFFFF;stroke-width:4;stroke-linecap:round;stroke-miterlimit:10;" d="M18,16
                                    c0-3.899,3.188-7.054,7.1-6.999c3.717,0.052,6.848,3.182,6.9,6.9c0.035,2.511-1.252,4.723-3.21,5.986
                                    C26.355,23.457,25,26.261,25,29.158V32"></path><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g>
                                <g></g><g></g><g></g><g></g><g></g><g></g><g></g>
                                </svg>
                            </a>
                            </span> -->
                    </div>
                </div>
                    <div class="order-table-box">
                        <div class="row">
                            <div class="col-md-12 col-xs-12 text-center">
                                <br>
                                <input type="hidden" id="refresh_transactions" value="1" />
                            <input type="hidden" id="page" value="<?= $page1 ?>" />
                                <table class="table row-border table-hover order-data-table right-data-table" cellspacing="0 " width="100% ">
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
                                                if ($user_id == $transaction['site_user']) {
                                                    $transaction_type = "Buy";
                                                }elseif ($user_id == $transaction['site_user1']) {
                                                    $transaction_type = "Sell";
                                                }
                                                echo '
                                                <tr id="transaction_' . $transaction['id'] . '">
                                                    <input type="hidden" class="is_crypto" value="' . $transaction['is_crypto'] . '" />
                                                    <td>' . $transaction_type . '</td>
                                                    <td>'.$transaction['date'].'<input type="hidden" class="localdate" value="' . (strtotime($transaction['date'])) . '" /></td>
                                                    <td>' . Stringz::currency($transaction['btc'], true) . ' ' . $CFG->currencies[$transaction['c_currency']]['fa_symbol'] . '</td>
                                                    <td><span class="currency_char">' . $trans_symbol . '</span><span>' . Stringz::currency($transaction['btc_net'] * $transaction['fiat_price'], ($transaction['is_crypto'] == 'Y')) . '</span></td>
                                                    <td><span class="currency_char">' . $trans_symbol . '</span><span>' . Stringz::currency($transaction['fiat_price'], ($transaction['is_crypto'] == 'Y')) . '</span></td>
                                                    <td><span class="currency_char">' . $trans_symbol . '</span><span>' . Stringz::currency($transaction['fee'] * $transaction['fiat_price'], ($transaction['is_crypto'] == 'Y')) . '</span></td>
                                                </tr>';
                                            }
                                        }else{
                                            echo '<tr id="no_transactions" style="' . (is_array($transactions) ? 'display:none;' : '') . '"><td colspan="6" style="padding: 0;"><div class="" style="background: #f4f6f8; text-align:  center;
                                                "><img src="images/no-results.gif" style="width: 300px;height: auto;    float: none;" ></div></td></tr>';
                                        }
                                        
                                        ?>
                                </tbody>
                            </table>
                            <?= $pagination ?>
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
     var r = 1;
               if (r == 1) {
                    $(".page-link").click(function(e){
                        table.page( 'next' ).draw( 'page' );
                    });
                    r = 2;
               }
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

<script>
            function redirectBasedOnCurrencies(c_currency, currency)
            {
                var url = window.location.origin+window.location.pathname+"?c_currency="+c_currency+"&currency="+currency;
                console.log(url);
                window.location.href = url;
            }
            
            $(document).ready(function(){
                $("#c_currency_select").on('change', function(){
                    redirectBasedOnCurrencies($(this).val(), $('#currency_select').find('option:selected').val());
                });
                $("#currency_select").on('change', function(){
                    redirectBasedOnCurrencies($("#c_currency_select").find('option:selected').val(), $(this).val());
                });
            });
            
</script>

</html>