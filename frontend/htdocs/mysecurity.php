<!DOCTYPE html>
<html lang="en">
<?php
    include '../lib/common.php';
    
    if (User::$info['locked'] == 'Y' || User::$info['deactivated'] == 'Y')
        Link::redirect('mysecurity');
    elseif (User::$awaiting_token)
        Link::redirect('verify_token');
    elseif (!User::isLoggedIn())
        Link::redirect('login');
    
    // if(empty(User::$ekyc_data) || User::$ekyc_data[0]->status != 'accepted')
    // {
    //     Link::redirect('ekyc.php');
    // }
    
    $step1 = false;
    $step2 = false;
    $step3 = false;
    $step4 = false;
    
    $authcode1 = (!empty($_REQUEST['authcode'])) ? urldecode($_REQUEST['authcode']) : false;
    if ($authcode1 && empty($_REQUEST['step'])) {
        API::add('User','getSettingsChangeRequest',array(urlencode($authcode1)));
        $query = API::send();
        $response = unserialize(base64_decode($query['User']['getSettingsChangeRequest']['results'][0]));
        if ($response) {
            if (!empty($response['authy']))
                $step1 = true;
            elseif (!empty($response['google']))
                $step3 = true;
        }
        else
            Errors::add(Lang::string('settings-request-expired'));
    }
    
    $cell1 = (!empty($_REQUEST['cell'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['cell']) : false;
    $country_code1 = (!empty($_REQUEST['country_code'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['country_code']) : false;
    
    $token1 = (!empty($_REQUEST['token'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['token']) : false;
    $remove = !empty($_REQUEST['remove']);
    
    if ($remove) {
        if (empty($_REQUEST['submitted']) || (!empty($_REQUEST['method']) && $_REQUEST['method'] == 'sms')) {
            if (User::$info['using_sms'] == 'Y') {
                if (User::sendSMS()) {
                    $sent_sms = true;
                    Messages::add(Lang::string('withdraw-sms-sent'));
                }
            }
        }
        else {
            if (!($token1 > 0))
                Errors::add(Lang::string('security-no-token'));
            
            if (!is_array(Errors::$errors)) {
                API::token($token1);
                API::add('User','disable2fa');
                $query = API::send();
            
                if ($query['error'] == 'security-incorrect-token')
                    Errors::add(Lang::string('security-incorrect-token'));
                
                if ($query['error'] == 'security-com-error')
                    Errors::add(Lang::string('security-com-error'));
            
                if ($query['error'] == 'authy-errors')
                    Errors::merge($query['authy_errors']);
            
                if ($query['error'] == 'request-expired')
                    Errors::add(Lang::string('settings-request-expired'));
            
                if (!is_array(Errors::$errors)) {
                    Link::redirect('mysecurity?message=security-disabled-message');
                }
            }
        }
    }
    
    if (!empty($_REQUEST['step']) && $_REQUEST['step'] == 1) {
        if (!($cell1 > 0) && $_REQUEST['method'] != 'google')
            Errors::add(Lang::string('security-no-cell'));
        if (!($country_code1 > 0) && $_REQUEST['method'] != 'google')
            Errors::add(Lang::string('security-no-cc'));
        
        if (!is_array(Errors::$errors)) {
            if ($_REQUEST['method'] != 'google') {
                API::add('User','registerAuthy',array($cell1,$country_code1));
                $query = API::send();
                $authy_id = $query['User']['registerAuthy']['results'][0]['user']['id'];
                $response = $query['User']['registerAuthy']['results'][0];
                
                if (!$response || !is_array($response))
                    Errors::merge(Lang::string('security-com-error'));
                
                if ($response['success'] == 'false')
                    Errors::merge($response['errors']);
            }
            
            if (!is_array(Errors::$errors)) {
                if ($_REQUEST['method'] != 'google') {
                    if ($_REQUEST['method'] == 'sms') {
                        if (User::sendSMS($authy_id))
                            $using_sms = 'Y';
                    }
                    else
                        $using_sms = 'N';
                    
                    if (!is_array(Errors::$errors)) {
                        API::add('User','enableAuthy',array($cell1,$country_code1,$authy_id,$using_sms));
                        API::add('User','settingsEmail2fa',array(array('authy'=>1),1));
                        $query = API::send();
                        //$step1 = true;
        
                        if ($query['User']['settingsEmail2fa']['results'][0])
                            Link::redirect('mysecurity?notice=email');
                    }
                }
                else {
                    if (!is_array(Errors::$errors)) {
                        API::add('User','enableGoogle2fa',array($cell1,$country_code1));
                        API::add('User','settingsEmail2fa',array(array('google'=>1),1));
                        $query = API::send();
                        //$step1 = true;
                    
                        if ($query['User']['settingsEmail2fa']['results'][0])
                            Link::redirect('mysecurity?notice=email');
                    }
                }
            }
        }
    }
    elseif (!empty($_REQUEST['step']) && $_REQUEST['step'] == 2) {
        if (!($token1 > 0))
            Errors::add(Lang::string('security-no-token'));
        
        if (!is_array(Errors::$errors)) {
            API::settingsChangeId($authcode1);
            API::token($token1);
            API::add('User','verifiedAuthy');
            $query = API::send();
        
            if (!empty($query['error'])) {
                if ($query['error'] == 'security-com-error')
                    Errors::add(Lang::string('security-com-error'));
            
                if ($query['error'] == 'authy-errors')
                    Errors::merge($query['authy_errors']);
                
                if ($query['error'] == 'request-expired')
                    Errors::add(Lang::string('settings-request-expired'));
            }
            
            if (!is_array(Errors::$errors)) {
                Messages::add(Lang::string('security-success-message'));
                
                $step2 = true;
            }
            else
                $step1 = true;
        }
        else
            $step1 = true;
    }
    elseif (!empty($_REQUEST['step']) && $_REQUEST['step'] == 3) {
        if (!($token1 > 0))
            Errors::add(Lang::string('security-no-token'));
    
        if (!is_array(Errors::$errors)) {
            API::settingsChangeId($authcode1);
            API::token($token1);
            API::add('User','verifiedGoogle');
            $query = API::send();
    
            if ($query['error'] == 'security-incorrect-token')
                Errors::add(Lang::string('security-incorrect-token'));
            
            if ($query['error'] == 'request-expired')
                Errors::add(Lang::string('settings-request-expired'));
    
            if (!is_array(Errors::$errors)) {
                Messages::add(Lang::string('security-success-message'));
                    
                $step4 = true;
            }
            else
                $step3 = true;
        }
        else
            $step3 = true;
    }
    
    // Security & Notifications Section
    if (empty($_REQUEST['prefs'])) {
        $confirm_withdrawal_2fa_btc1 = User::$info['confirm_withdrawal_2fa_btc'];
        $confirm_withdrawal_email_btc1 = User::$info['confirm_withdrawal_email_btc'];
        $confirm_withdrawal_2fa_bank1 = User::$info['confirm_withdrawal_2fa_bank'];
        $confirm_withdrawal_email_bank1 = User::$info['confirm_withdrawal_email_bank'];
        $notify_deposit_btc1 = User::$info['notify_deposit_btc'];
        $notify_deposit_bank1 = User::$info['notify_deposit_bank'];
        $notify_withdraw_btc1 = User::$info['notify_withdraw_btc'];
        $notify_withdraw_bank1 = User::$info['notify_withdraw_bank'];
        $notify_login1 = User::$info['notify_login'];
    }
    else {
        $confirm_withdrawal_2fa_btc1 = $_REQUEST['confirm_withdrawal_2fa_btc'];
        $confirm_withdrawal_email_btc1 = $_REQUEST['confirm_withdrawal_email_btc'];
        $confirm_withdrawal_2fa_bank1 = $_REQUEST['confirm_withdrawal_2fa_bank'];
        $confirm_withdrawal_email_bank1 = $_REQUEST['confirm_withdrawal_email_bank'];
        $notify_deposit_btc1 = $_REQUEST['notify_deposit_btc'];
        $notify_deposit_bank1 = $_REQUEST['notify_deposit_bank'];
        $notify_withdraw_btc1 = $_REQUEST['notify_withdraw_btc'];
        $notify_withdraw_bank1 = $_REQUEST['notify_withdraw_bank'];
        $notify_login1 = $_REQUEST['notify_login'];
    }
    
    if (!empty($_REQUEST['prefs'])) {
        if (!$email_auth && (empty($_SESSION["settings_uniq"]) || $_SESSION["settings_uniq"] != $_REQUEST['uniq']))
            Errors::add('Page expired.');
        elseif (!$no_token && !$request_2fa) {
            API::settingsChangeId($authcode1);
            API::token($token1);
            API::add('User','updateSettings',array($confirm_withdrawal_2fa_btc1,$confirm_withdrawal_email_btc1,$confirm_withdrawal_2fa_bank1,$confirm_withdrawal_email_bank1,$notify_deposit_btc1,$notify_deposit_bank1,$notify_login1,$notify_withdraw_btc1,$notify_withdraw_bank1));
            $query = API::send();
                // echo "<pre>"; print_r($query); exit;
            if (!empty($query['error'])) {
                if ($query['error'] == 'security-com-error')
                    Errors::add(Lang::string('security-com-error'));
                    
                if ($query['error'] == 'authy-errors')
                    Errors::merge($query['authy_errors']);
                    
                if ($query['error'] == 'request-expired')
                    Errors::add(Lang::string('settings-request-expired'));
                
                if ($query['error'] == 'security-incorrect-token')
                    Errors::add(Lang::string('security-incorrect-token'));
            }   
            if (!is_array(Errors::$errors)) {
                $_SESSION["settings_uniq"] = md5(uniqid(mt_rand(),true));
                Link::redirect('mysecurity?message=settings-settings-message');
            }
            else
                $request_2fa = true;
        }
    }
    // Security & Notifications Section ends
    
    
    if (!empty($_REQUEST['notice']) && $_REQUEST['notice'] == 'email')
        $notice = Lang::string('settings-change-notice');
    elseif (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'security-disabled-message')
        Messages::add(Lang::string('security-disabled-message'));
    
    if (User::$info['verified_authy'] == 'Y' || $step2)
        API::add('Content','getRecord',array('security-setup'));
    elseif (User::$info['verified_google'] == 'Y' || $step4)
        API::add('Content','getRecord',array('security-setup-google'));
    elseif ($step1)
        API::add('Content','getRecord',array('security-token'));
    elseif ($step3) {
        API::add('Content','getRecord',array('security-google'));
        API::add('User','getGoogleSecret');
    }
    else
        API::add('Content','getRecord',array('security-explain'));
    
    $query = API::send();
    $content = $query['Content']['getRecord']['results'][0];
    $secret = (!empty($query['User']['getGoogleSecret'])) ? $query['User']['getGoogleSecret']['results'][0] : false;
    $page_title = Lang::string('security');
    
    
    // History section starts here
    $page1 = (!empty($_REQUEST['page'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['page']) : false;
    $bypass = !empty($_REQUEST['bypass']);
    
    API::add('History','get',array(1,$page1));
    $query = API::send();
    $total = $query['History']['get']['results'][0];
    
    API::add('History','get',array(false,$page1,30));
    $query = API::send();
    
    $history = $query['History']['get']['results'][0];
    $pagination = Content::pagination('mysecurity.php',$page1,$total,30,5,false);
    
    $page_title = Lang::string('history');
    // History section ends here
    
    $_SESSION["settings_uniq"] = md5(uniqid(mt_rand(),true));
    if (!empty($_REQUEST['message'])) {
        if ($_REQUEST['message'] == 'settings-settings-message')
            Messages::add(Lang::string('settings-settings-message'));
    }
    ?>
<head>
    <title><?= $CFG->exchange_name; ?> | Security</title>
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
        select#method {
            background-color: inherit;
        }
        .controls.form-control {
            background-color: inherit;
            border: 0px;
        }
        input#secret {
            display: block;
            width: 100%;
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
        }
        input#token {
            display: block;
            width: 100%;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }
        .one_half .calc {
            background-color: white;
            text-align: center;
        }

            

.col-md-6, .col-xs-12, .col-lg-5, .col-sm-12 {
    position: relative;
    width: 100%;
    min-height: 1px;
    padding-right: 15px;
    padding-left: 15px;
}
.btn.btn-yellow.btn-block {
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

form#buy_form {
    background-color: unset;
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
                <h1>TWO-FACTOR AUTHENTICATION (2FA)</h1>
            </div>
        </div>
    </header>
    <div class="page-container">
        <div class="container-fluid">
            <div class="row profile-banner">
                <div class="container content">
                    <div class="profile-box">
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <br>
                                <h6><strong>Two Factor Authentication Disabled</strong></h6>
                                <p>For extra account security, we strongly recommend you enable two-factor authentication (2FA). AWAX uses Google Authenticator for 2FA.</p>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <?php  
                                    Errors::display(); 
                                    Messages::display(); 
                                ?>
                                <?php if(!empty($notice)): ?>
                                    <div class="notice">
                                        <div class="message-box-wrap alert alert-info" style="margin-top:1em;"><?=$notice?></div>
                                    </div>
                                <?php endif; ?>
                                <? if ($remove) { ?>
                                <legend><?= Lang::string('security-enter-token') ?></legend>
                                <div class="span9 marginmobile" style="padding-bottom: 0;">
                                    <form id="enable_tfa" action="mysecurity" method="POST">
                                        <input type="hidden" name="remove" value="1" />
                                        <input type="hidden" name="submitted" value="1" />
                                        <div class="control-group user-email">
                                            <!-- <span class="control-label formtexts" style="text-align: left;width: 190px;float: left;">
                                            <label class="formlabel" for="user_email" style="font-weight: 600;margin-bottom: 0;line-height: 1.9em;color: #5a5f6d;"><?= Lang::string('security-token') ?></label>
                                            </span> -->
                                            <div class="controls form-control">
                                                <input name="token" id="token" type="text" value="<?= $token1 ?>" class="col-md-12" style="margin-bottom:5px"/>
                                                <div>
                                                    <input type="submit" name="submit" style="margin-bottom:1em;float: right;" value="<?= Lang::string('security-validate') ?>" class="btn trigger-challenge-2fa btn-yellow btn-block" />
                                                    <? if (User::$info['using_sms'] == 'Y') { ?>
                                                    <input type="submit" name="sms" style="margin-bottom:1em;float: right;" value="<?= Lang::string('security-resend-sms') ?>" class="btn trigger-challenge-2fa btn-yellow btn-block" />
                                                    <? } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <? } elseif (User::$info['verified_authy'] == 'Y' || $step2) { ?>
                                <legend><?= $content['title'] ?></legend>
                                
                                <ul class="list_empty">
                                    <li>
                                        <div class="number">+<?= User::$info['country_code']?> <?= User::$info['tel']?></div>
                                    </li>
                                    <li><a class="item_label" href="javascript:return false;"><?= Lang::string('security-verified') ?></a></li>
                                </ul>
                                <a href="mysecurity?remove=1" style="margin-bottom:1em;" class="btn trigger-challenge-2fa btn-yellow btn-block"><i class="fa fa-times fa-lg"></i> <?= Lang::string('security-disable') ?></a>
                                <? } elseif (User::$info['verified_google'] == 'Y' || $step4) { ?>
                                <legend><?= $content['title'] ?></legend>
                                
                                <a href="mysecurity?remove=1" style="margin-bottom:1em;" class="btn trigger-challenge-2fa btn-yellow btn-block"><i class="fa fa-times fa-lg"></i> <?= Lang::string('security-disable') ?></a>
                                <? } elseif ($step1) { ?>
                                <legend><?= Lang::string('security-enter-token') ?></legend>
                               
                                <div class="span9 marginmobile" style="padding-bottom: 0;">
                                    <form id="enable_tfa" action="mysecurity.php" method="POST">
                                        <input type="hidden" name="step" value="2" />
                                        <input type="hidden" name="authcode" value="<?= urlencode($authcode1) ?>" />
                                        <div class="control-group user-email">
                                            <span class="control-label formtexts" style="text-align: left;width: 190px;float: left;">
                                            <label class="formlabel" for="user_email" style="font-weight: 600;margin-bottom: 0;line-height: 1.9em;color: #5a5f6d;"><?= Lang::string('security-token') ?></label>
                                            </span>
                                            <div class="controls form-control">
                                                <input name="token" id="authy-token" type="text" value="<?= $token1 ?>" />
                                                <div >
                                                    <ul class="list_empty">
                                                        <li><input type="submit" name="submit" value="<?= Lang::string('security-validate') ?>" class="but_user btn btn-yellow btn-block" /></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <? } elseif ($step3) { ?>
                                <legend><?= $content['title'] ?></legend>
                                
                                <div class="span9 marginmobile" style="padding-bottom: 0;">
                                    <form id="enable_tfa" action="mysecurity.php" method="POST">
                                        <input type="hidden" name="step" value="3" />
                                        <input type="hidden" name="authcode" value="<?= urlencode($authcode1) ?>" />
                                        <div class="content">
                                            <!-- <h3 class="section_label">
                                                <span class="left"><i class="fa fa-mobile fa-2x"></i></span>
                                                <span class="right"><?= Lang::string('security-scan-qr') ?></span>
                                            </h3> -->
                                            <div class="clear"></div>
                                            <div class="one_half">
                                                <div class="spacer"></div>
                                                <div class="spacer"></div>
                                                <div class="spacer"></div>
                                                <div class="param form-control">
                                                    <label for="secret"><?= Lang::string('security-secret-code') ?></label>
                                                    <input type="text" id="secret" name="secret" value="<?= $secret['secret'] ?>" class="col-md-12" disabled/>
                                                    <div class="clear"></div>
                                                </div>
                                                <div class="spacer"></div>
                                                <div class="calc">
                                                    <img class="qrcode" src="includes/qrcode.php?sec=1&code=otpauth://totp/<?= $secret['label'] ?>?secret=<?= $secret['secret'] ?>" />
                                                </div>
                                                <div class="spacer"></div>
                                                <div class="param form-control">
                                                    <label for="token">Enter Token</label>
                                                    <input name="token" id="token" type="text" value="<?= $token1 ?>" class="col-md-12" style="margin-bottom:5px"/>
                                                    <div class="clear"></div>
                                                </div>
                                                <input type="submit" name="submit" value="<?= Lang::string('security-validate') ?>" class="but_user center-widget btn btn-yellow btn-block" style="margin-bottom:5px;margin-top: 10px;" />
                                            </div>
                                            <div class="clear"></div>
                                        </div>
                                    </form>
                                </div>
                                <? } else { ?>
                                <legend><?= Lang::string('security-enable-two-factor') ?></legend>
                                <?= (!empty($notice)) ? '<div class="notice"><div class="message-box-wrap">'.$notice.'</div></div>' : '' ?>
                                
                                <div class="span9 marginmobile" style="padding-bottom: 0;">
                                    <form name="start_auth" id="enable_tfa" action="mysecurity.php" method="POST">
                                        <input type="hidden" name="step" value="1" />
                                        <input type="hidden" id="send_sms" name="send_sms" value="" />
                                        <input type="hidden" id="google_2fa" name="google_2fa" value="" />
                                        <div class="control-group user-email">
                                            <span class="control-label formtexts" style="text-align: left;width: 190px;float: left;">
                                            <label class="formlabel" for="user_email" style="font-weight: 600;margin-bottom: 0;line-height: 1.9em;color: #5a5f6d;"><?= Lang::string('security-method') ?></label>
                                            </span>

                                            <div class="controls form-control">
                                                <select class="form-control" name="method" id="method" style="-webkit-appearance: menulist;margin-bottom:15px;height:32px" >
                                                    <option <?= ($_REQUEST['method'] == 'google') ? 'selected="selected"' : false ?> value="google">Google Authenticator</option>
                                                    <!-- <option <?= ($_REQUEST['method'] == 'authy') ? 'selected="selected"' : false ?> value="authy">Authy</option> -->
                                                    <!-- <option <?= ($_REQUEST['method'] == 'SMS') ? 'selected="selected"' : false ?> value="SMS">SMS</option> -->
                                                </select>
                                                <!-- <div id="hidden_div" style="display: none;margin:2em 0 0;" class="controls"> -->
                                                <div class="form-group method_show" style="display: none;">
                                                    <label><?= Lang::string('security-country') ?> (<?= Lang::string('security-optional-google') ?>)</label>
                                                    <select name="country_code" id="authy-countries" class="form-control" style="margin-bottom: 20px; -webkit-appearance: menulist;min-width: 300px;border-radius: 4px !important;">
                                                    <? 
                                                        if ($country_code1 > 0) {
                                                            echo '<option value="'.$country_code1.'" selected="selected"></option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group method_show" style="display: none;">
                                                    <label><?= Lang::string('security-cell') ?> (<?= Lang::string('security-optional-google') ?>)</label>
                                                    <input name="cell" class="form-control" id="authy-cellphone" type="text" value="<?= $cell1 ?>" style="min-width: 300px;border-radius: 4px !important;height: 40px;"/>
                                                </div>
                                                <!-- </div> -->
                                                <div >
                                                    <input type="submit" name="submit" value="<?= Lang::string('security-enable') ?>" style="margin-bottom:1em;float: right;" class="btn trigger-challenge-2fa btn-yellow btn-block" />
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <br>
                                <h6><strong>Security and Notifications</strong></h6>
                                <p>For extra account security, we strongly recommend you enable two-factor authentication (2FA). AWAX uses Google Authenticator for 2FA.</p>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <legend>Security and Notifications</legend>
                                <form id="buy_form" action="mysecurity.php" method="POST" class="col-md-12">
                                        <input type="hidden" name="prefs" value="1" />
                                        <input type="hidden" name="submitted" value="1" />
                                        <input type="hidden" name="uniq" value="<?= $_SESSION["settings_uniq"] ?>" />
                                        <div class="span9 marginmobile">
                                            <div class="control-group">
                                                <? if (User::$info['verified_authy'] == 'Y' || User::$info['verified_google'] == 'Y') { ?>
                                                <label class="cont">
                                                    <input class="checkbox" name="confirm_withdrawal_2fa_btc" id="confirm_withdrawal_2fa_btc" type="checkbox" value="Y" <?= ($confirm_withdrawal_2fa_btc1 == 'Y') ? 'checked="checked"' : '' ?> />
                                                    <span class="checkmark"></span>
                                                    <?= Lang::string('settings-withdrawal-2fa-btc') ?>
                                                </label><br>
                                                <?php } ?>
                                                <label class="cont">
                                                    <input class="checkbox" name="confirm_withdrawal_email_btc" id="confirm_withdrawal_email_btc" type="checkbox" value="Y" <?= ($confirm_withdrawal_email_btc1 == 'Y') ? 'checked="checked"' : '' ?> />
                                                <span class="checkmark"></span>
                                                    <?= Lang::string('settings-withdrawal-email-btc') ?>
                                                </label><br>
                                                <? if (User::$info['verified_authy'] == 'Y' || User::$info['verified_google'] == 'Y') { ?>
                                                <label class="cont">
                                                    <input class="checkbox" name="confirm_withdrawal_2fa_bank" id="confirm_withdrawal_2fa_bank" type="checkbox" value="Y" <?= ($confirm_withdrawal_2fa_bank1 == 'Y') ? 'checked="checked"' : '' ?> />
                                                    <span class="checkmark"></span>
                                                    <?= Lang::string('settings-withdrawal-2fa-bank') ?>
                                                </label><br>
                                                <?php } ?>
                                                <label class="cont">
                                                    <input class="checkbox" name="confirm_withdrawal_email_bank" id="confirm_withdrawal_email_bank" type="checkbox" value="Y" <?= ($confirm_withdrawal_email_bank1 == 'Y') ? 'checked="checked"' : '' ?> />
                                                    <span class="checkmark"></span>
                                                    <?= Lang::string('settings-withdrawal-email-bank') ?>
                                                </label><br>
                                                <label class="cont">
                                                    <input class="checkbox" name="notify_deposit_btc" id="notify_deposit_btc" type="checkbox" value="Y" <?= ($notify_deposit_btc1 == 'Y') ? 'checked="checked"' : '' ?>/>
                                                    <span class="checkmark"></span>
                                                    <?= Lang::string('settings-notify-deposit-btc') ?>
                                                </label><br>
                                                <label class="cont">
                                                    <input class="checkbox" name="notify_deposit_bank" id="notify_deposit_bank" type="checkbox" value="Y" <?= ($notify_deposit_bank1 == 'Y') ? 'checked="checked"' : '' ?> />
                                                    <span class="checkmark"></span>
                                                    <?= Lang::string('settings-notify-deposit-bank') ?>
                                                </label><br>
                                                <label class="cont">
                                                    <input class="checkbox" name="notify_withdraw_btc" id="notify_withdraw_btc" type="checkbox" value="Y" <?= ($notify_withdraw_btc1 == 'Y') ? 'checked="checked"' : '' ?> />
                                                    <span class="checkmark"></span>
                                                    <?= Lang::string('settings-notify-withdraw-btc') ?>
                                                </label><br>
                                                <label class="cont">
                                                    <input class="checkbox" name="notify_withdraw_bank" id="notify_withdraw_bank" type="checkbox" value="Y" <?= ($notify_withdraw_bank1 == 'Y') ? 'checked="checked"' : '' ?> />
                                                    <span class="checkmark"></span>
                                                    <?= Lang::string('settings-notify-withdraw-bank') ?>
                                                </label><br>
                                                <label class="cont">
                                                    <input class="checkbox" name="notify_login" id="notify_login" type="checkbox" value="Y" <?= ($notify_login1 == 'Y') ? 'checked="checked"' : '' ?> />
                                                    <span class="checkmark"></span>
                                                    <?= Lang::string('settings-notify-login') ?>
                                                </label><br>
                                            </div>
                                            <input type="submit" name="submit" value="<?= Lang::string('settings-save-settings') ?>" style="margin-bottom:1em;" class="btn trigger-challenge-2fa  btn-yellow btn-block" />
                                        </div>
                                    </form>
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