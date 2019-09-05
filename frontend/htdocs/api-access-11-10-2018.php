<!DOCTYPE html>
<html lang="en">
<?php
include '../lib/common.php';

if (User::$info['locked'] == 'Y' || User::$info['deactivated'] == 'Y')
    Link::redirect('userprofile.php');
elseif (User::$awaiting_token)
    Link::redirect('verify-token.php');
elseif (!User::isLoggedIn())
    Link::redirect('login.php');

$request_2fa = false;
$no_2fa = false;

if (!(User::$info['verified_authy'] == 'Y' || User::$info['verified_google'] == 'Y')) {
    $no_2fa = true;
}

if (!$request_2fa && !$no_2fa) {
    API::add('APIKeys','get');
    $query = API::send();
    $api_keys = $query['APIKeys']['get']['results'][0];
}

$token1 = (!empty($_REQUEST['token'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['token']) : false;
$remove_id1 = (!empty($_REQUEST['remove_id'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['remove_id']) : false;
if (!empty($_REQUEST['permissions']))
    $permissions = (is_array($_REQUEST['permissions'])) ? $_REQUEST['permissions'] : unserialize(urldecode($_REQUEST['permissions']));
else
    $permissions = false;

if (!empty($_REQUEST['action']) && ($_REQUEST['action'] == 'edit' || $_REQUEST['action'] == 'add' || $_REQUEST['action'] == 'delete')) {
    if (!$token1) {
        if (!empty($_REQUEST['request_2fa'])) {
            if (!($token1 > 0)) {
                $no_token = true;
                $request_2fa = true;
                Errors::add(Lang::string('security-no-token'));
            }
        }
    
        if (User::$info['verified_authy'] == 'Y' || User::$info['verified_google'] == 'Y') {
            if (!empty($_REQUEST['send_sms']) || User::$info['using_sms'] == 'Y') {
                if (User::sendSMS()) {
                    $sent_sms = true;
                    Messages::add(Lang::string('withdraw-sms-sent'));
                }
            }
            $request_2fa = true;
        }
    }
    else {
        API::token($token1);
        if ($_REQUEST['action'] == 'edit')
            API::add('APIKeys','edit',array($permissions));
        elseif ($_REQUEST['action'] == 'add')
            API::add('APIKeys','add');
        elseif ($_REQUEST['action'] == 'delete')
            API::add('APIKeys','delete',array($remove_id1));
        $query = API::send();
        
        if (!empty($query['error'])) {
            if ($query['error'] == 'security-com-error')
                Errors::add(Lang::string('security-com-error'));
            
            if ($query['error'] == 'authy-errors')
                Errors::merge($query['authy_errors']);
            
            if ($query['error'] == 'security-incorrect-token')
                Errors::add(Lang::string('security-incorrect-token'));
        }
        
        if ($_REQUEST['action'] == 'delete' && !$query['APIKeys']['delete']['results'][0])
            Link::redirect('api-access.php?error=delete');
        
        if (!is_array(Errors::$errors)) {
            if ($_REQUEST['action'] == 'edit')
                Link::redirect('api-access.php?message=edit');
            elseif ($_REQUEST['action'] == 'add') {
                $secret = $query['APIKeys']['add']['results'][0];
                Messages::add(Lang::string('api-add-message'));
                $info_message = str_replace('[secret]',$secret,Lang::string('api-add-show-secret'));
                
                API::add('APIKeys','get');
                $query = API::send();
                $api_keys = $query['APIKeys']['get']['results'][0];
            }
            elseif ($_REQUEST['action'] == 'delete')
                Link::redirect('api-access.php?message=delete');
        }
        else
            $request_2fa = true;
    }
}

if (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'edit')
    Messages::add(Lang::string('api-edit-message'));
elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'delete')
    Messages::add(Lang::string('api-delete-message'));
elseif (!empty($_REQUEST['error']) && $_REQUEST['error'] == 'delete')
    Errors::add(Lang::string('api-delete-error'));

?>
<head>
    <title><?= $CFG->exchange_name; ?> | API Access</title>
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
        ul.list_empty li {
    list-style-type: none;
}
.but_user:hover {
    background-color: #c68509;
    /* color: #fff; */
    text-decoration: none!important;
}
.but_user {
    min-width: 100px;
    display: block;
    padding: 7px 20px;
    border: 1px solid #c18102;
    background-color: #ffab06;
    border-radius: 3px;
    font-size: 16px;
    font-weight: 900;
    color: rgba(0, 0, 0, 0.7);
    text-decoration: none!important;
    width: 200px;
    margin-left: auto;
    margin-right: auto;
}
.table-style table.table-list.trades {
    width: 100%;
}
input#token {
    display: block;
    /* width: 100%; */
    padding: .375rem .75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: .25rem;
    transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    margin-bottom: 20px;
    margin-left: auto;
    margin-right: auto;
}
.text.dotted {
    margin-bottom: 20px;
}
.text.dotted p {
    font-size: 15px;
}
.text.dotted p span.bigger {
    font-size: 18px;
    font-weight: 600;
    line-height: 3;
}
ul.list_empty li {
    margin-top: 10px;
}
p.access-p a {
    color: #0b7076 !important;
    font-size: 15px;
    font-weight: 600;
}

      

.col-md-6, .col-xs-12, .col-lg-5, .col-sm-12 {
    position: relative;
    width: 100%;
    min-height: 1px;
    padding-right: 15px;
    padding-left: 15px;
}
.btn.btn-yellow.btn-block, .but_user {
    padding: 7px 20px !important;
    font-size: 16px !important;
}
input.form-control {
    width: 100% !important;
    padding: .375rem .75rem !important;
    font-size: 1rem !important;
    line-height: 1.5 !important;
}
input.btn.btn-yellow.btn-block {
    width: auto;
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
                <h1>API ACCESS</h1>
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
                                <?  Errors::display(); 
                                    Messages::display();
                                    if (!empty($info_message)) {
                                        echo '<div class="text dotted profile-banner"><p>'.$info_message.'</p></div><div class="clear"></div><div class="mar_top1"></div>';
                                    } 
                                    else {
                                ?>
                                    <h6><strong>Enable API access on your account to generate keys.</strong></h6>
                                <p class="access-p">Please see the <a href="#">API documentation</a> for information on how to use your API keys.</p>
                                <?
                                    }
                                ?>  

                                <br>
                                <!-- <div class="form-group text-center">
                                    <a class="btn btn-yellow">Enable API</a>
                                </div> -->

                                <? if ($no_2fa) { ?>
            <div class="content1">
                <h3 class="section_label">
                    <span class="left"><i class="fa fa-ban fa-2x"></i></span>
                    <span class="right"><?= Lang::string('api-disabled') ?></span>
                </h3>
                <div class="clear"></div>
                <div class="mar_top1"></div>
                <div class="text"><?= Lang::string('api-disabled-explain') ?></div>
                <div class="mar_top3"></div>
                <div class="clear"></div>
                <ul class="list_empty">
                    <li><a class="but_user" href="mysecurity"><?= Lang::string('api-setup-security') ?></a></li>
                </ul>
                <div class="clear"></div>
            </div>
            <? } elseif ($request_2fa) { ?>
            <div class="content">
                <h3 class="section_label">
                    <!-- <span class="left"><i class="fa fa-mobile fa-2x"></i></span> -->
                    <span class="right">Enter Your Token<!-- <?= Lang::string('security-enter-token') ?> --></span>
                </h3>
                <form id="enable_tfa" action="api-access.php" method="POST">
                    <input type="hidden" name="request_2fa" value="1" />
                    <input type="hidden" name="permissions" value="<?= urlencode(serialize($permissions)) ?>" />
                    <input type="hidden" name="remove_id" value="<?= $remove_id1 ?>" />
                    <input type="hidden" name="action" value="<?= preg_replace("/[^a-z]/", "",$_REQUEST['action']) ?>" />
                    <div class="buyform">
                        <div class="one_half">
                            <div class="spacer"></div>
                            <div class="spacer"></div>
                            <div class="spacer"></div>
                            <div class="param">
                                <!-- <label for="token"><?= Lang::string('security-token') ?></label> -->
                                <input name="token" id="token" type="text" value="<?= $token1 ?>" />
                                <div class="clear"></div>
                            </div>
                             <div class="mar_top2"></div>
                             <input type="submit" name="submit" value="<?= Lang::string('security-validate') ?>" class="but_user" />
                             <? if (User::$info['using_sms'] == 'Y') { ?>
                                <input type="submit" name="sms" value="<?= Lang::string('security-resend-sms') ?>" class="but_user" />
                                <? } ?>
                           <!--   <ul class="list_empty">
                                <li><input type="submit" name="submit" value="<?= Lang::string('security-validate') ?>" class="but_user" /></li>
                                <? if (User::$info['using_sms'] == 'Y') { ?>
                                <li><input type="submit" name="sms" value="<?= Lang::string('security-resend-sms') ?>" class="but_user" /></li>
                                <? } ?>
                            </ul> -->
                        </div>
                    </div>
                </form>
                <div class="clear"></div>
            </div>
            <? } else { ?>
            <div class="clear"></div>
            <form id="add_bank_account" action="api-access.php" method="POST">
                <input type="hidden" name="action" value="edit" />
                <ul class="list_empty">
                    <li><a style="display:block;" href="api-access.php?action=add" class="but_user"><i class="fa fa-plus fa-lg"></i> <?= Lang::string('api-add-new') ?></a></li>
                    <? if ($api_keys) { ?><li><input style="display:block;" type="submit" class="but_user" value="<?= Lang::string('api-add-save') ?>" /></li><? } ?>
                </ul>
                <div class="table-style">
                    <table class="table-list trades table table-border table-striped">
                        <tr>
                            <th colspan="5"><?= Lang::string('api-keys') ?></th>
                        </tr>
                        <? 
                        if ($api_keys) {
                            foreach ($api_keys as $api_key) {
                        ?>
                        <tr>
                            <td class="api-label first"><?= Lang::string('api-key') ?>:</td>
                            <td class="api-key" colspan="3"><?= $api_key['key'] ?></td>
                            <td><a href="api-access.php?remove_id=<?= $api_key['id'] ?>&action=delete"><i class="fa fa-minus-circle"></i> <?= Lang::string('bank-accounts-remove') ?></a></td>
                        </tr>
                        <tr>
                            <td class="api-label"><?= Lang::string('api-permissions') ?>:</td>
                            <td class="inactive">
                                <input type="checkbox" id="permission_<?= $api_key['id'] ?>_view" name="permissions[<?= $api_key['id'] ?>][view]" value="Y" <?= ($api_key['view'] == 'Y') ? 'checked="checked"' : '' ?> />
                                <label for="permission_<?= $api_key['id'] ?>_view"><?= Lang::string('api-permission_view') ?></label>
                            </td>
                            <td class="inactive">
                                <input type="checkbox" id="permission_<?= $api_key['id'] ?>_orders" name="permissions[<?= $api_key['id'] ?>][orders]" value="Y"<?= ($api_key['orders'] == 'Y') ? 'checked="checked"' : '' ?> />
                                <label for="permission_<?= $api_key['id'] ?>_orders"><?= Lang::string('api-permission_orders') ?></label>
                            </td>
                            <td class="inactive">
                                <input type="checkbox" id="permission_<?= $api_key['id'] ?>_view" name="permissions[<?= $api_key['id'] ?>][withdraw]" value="Y" <?= ($api_key['withdraw'] == 'Y') ? 'checked="checked"' : '' ?> />
                                <label for="permission_<?= $api_key['id'] ?>_withdraw"><?= Lang::string('api-permission_withdraw') ?></label>
                            </td>
                            <td class="inactive"></td>
                        </tr>
                        <?
                            }
                        }
                        else {
                            echo '<tr><td colspan="5">'.Lang::string('api-keys-no').'</td></tr>';
                        }
                        ?>
                    </table>
                </div>
            </form>
            <? } ?>
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