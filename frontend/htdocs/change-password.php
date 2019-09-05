<!DOCTYPE html>
<html lang="en">

<?php
    include '../lib/common.php';
    
    if (User::$awaiting_token)
        Link::redirect('verify-token?email_auth=1&authcode='.urlencode($_REQUEST['authcode']));
    elseif (!User::isLoggedIn())
        Link::redirect('login');
    
    $authcode1 = (!empty($_REQUEST['authcode'])) ? urldecode($_REQUEST['authcode']) : false;
    $authcode_valid = false;
    $uniq1 = (!empty($_REQUEST['settings'])) ? $_REQUEST['settings']['uniq'] : $_REQUEST['uniq'];
    $token1 = (!empty($_REQUEST['token'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['token']) : false;
    $request_2fa = false;
    
    /*
    if (!empty($_REQUEST['ex_request'])) {
        $_REQUEST = unserialize(urldecode($_REQUEST['ex_request']));
    }
    */
    
    // check for authcode or redirect if invalid
    if ($authcode1) {
        API::add('User','getSettingsChangeRequest',array(urlencode($authcode1)));
        $query = API::send();
        $authcode_valid = $query['User']['getSettingsChangeRequest']['results'][0];
    }
    
    // if (!$authcode1 || !$authcode_valid) {
    //  User::logOut(true);
    //  Link::redirect('login.php');
    //  exit;
    // }
    
    // check if form submitted and process
    if (!empty($_REQUEST['settings'])) {
        $match = preg_match_all($CFG->pass_regex,$_REQUEST['settings']['pass'],$matches);
        $_REQUEST['settings']['pass'] = preg_replace($CFG->pass_regex, "",$_REQUEST['settings']['pass']);
        $too_few_chars = (mb_strlen($_REQUEST['settings']['pass'],'utf-8') < $CFG->pass_min_chars);
    }
    
    API::add('User','getInfo',array($_SESSION['session_id']));
    $query = API::send();
    
    $personal = new Form('settings',false,false,'form1','site_users');
    $personal->verify();
    $personal->get($query['User']['getInfo']['results'][0]);
    
    if (!empty($_REQUEST['settings']) && $_SESSION['cp_uniq'] != $uniq1)
            $personal->errors[] = 'Page expired.';
    
    if (!empty($match))
        $personal->errors[] = htmlentities(str_replace('[characters]',implode(',',array_unique($matches[0])),Lang::string('login-pass-chars-error')));
    if (!empty($too_few_chars))
        $personal->errors[] = Lang::string('login-password-error');
    
    // check if we should request 2fa
    /*
    if (!empty($_REQUEST['settings']) && !$token1 && !is_array($personal->errors) && !is_array(Errors::$errors)) {
        if (!empty($_REQUEST['request_2fa'])) {
            if (!($token1 > 0)) {
                $no_token = true;
                $request_2fa = true;
                Errors::add(Lang::string('security-no-token'));
            }
        }
    
        if (User::$info['verified_authy'] == 'Y' || User::$info['verified_google'] == 'Y') {
            if ($_REQUEST['send_sms'] || User::$info['using_sms'] == 'Y') {
                if (User::sendSMS()) {
                    $sent_sms = true;
                    Messages::add(Lang::string('withdraw-sms-sent'));
                }
            }
            $request_2fa = true;
        }
    }
    */
    
    // display errors or send pass change request
    if (!empty($_REQUEST['settings']) && !empty($personal->errors)) {
        $errors = array();
        foreach ($personal->errors as $key => $error) {
            if (stristr($error,'login-required-error')) {
                $errors[] = Lang::string('settings-'.str_replace('_','-',$key)).' '.Lang::string('login-required-error');
            }
            elseif (strstr($error,'-')) {
                $errors[] = Lang::string($error);
            }
            else {
                $errors[] = $error;
            }
        }
        Errors::$errors = $errors;
    }
    elseif (!empty($_REQUEST['settings']) && empty($personal->errors)) {
        if (empty($no_token) && !$request_2fa) {
            //$authcode2 = (User::$info['verified_authy'] == 'Y' || User::$info['verified_google'] == 'Y') ? false : $authcode1;
            //API::settingsChangeId($authcode2);
            //API::token($token1);
            if($authcode1){
            API::settingsChangeId($authcode1);
            API::add('User','changePassword',array($personal->info['pass']));
            }
            else{
                API::add('User','firstLoginPassChange',array($personal->info['pass']));
            }
            $query = API::send();
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
                $_SESSION["cp_uniq"] = md5(uniqid(mt_rand(),true));
                Link::redirect('myprofile?message=settings-personal-message');
            }
            else
                $request_2fa = true;
        }
    }
    else {
        $personal->info['pass'] = false;
    }
    
    $_SESSION["cp_uniq"] = md5(uniqid(mt_rand(),true));
    // $page_title = Lang::string('change-password');
    
    ?>

<head>
    <title><?= $CFG->exchange_name; ?> | Change Password</title>
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
        .form-control
        {
        height : 32px;
        }
        .message-box-wrap
        {
        margin-top:20px;
        }       

.col-md-6, .col-xs-12 {
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
                <h1>CHANGE YOUR PASSWORD</h1>
            </div>
        </div>
    </header>
    <div class="page-container">
        <div class="container-fluid">
            <div class="row register-banner">
                <div class="container content">
                    <div class="register-box">
                        <br>
                        <div class="row">
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
                              <!--   <div class="form-group">
                                    <label>Old Password</label>
                                    <input type="password" class="form-control" placeholder="Old Password">
                                </div> -->
                                <!-- <div class="form-group">
                                    <label>New Password</label>
                                    <input type="password" class="form-control" placeholder="New Password">
                                </div>
                                <div class="form-group">
                                    <label>Repeat New Password</label>
                                    <input type="password" class="form-control" placeholder="Repeat New Password">
                                </div>
                                
                                <br>
                                <div class="form-group">
                                    <a class="btn btn-yellow btn-block">Change Password</a>
                                </div> -->
                                <? if (!$request_2fa) { ?>
                                <div class="content">
                                    <?
                                        $personal->passwordInput('pass','New Password', false, false, false, "form-control");
                                        $personal->passwordInput('pass2', Lang::string('settings-pass-confirm'),false,false,false,'form-control',false,false,'pass');
                                        $personal->HTML('<div class="form_button"><input type="submit" name="submit" value="'.Lang::string('settings-save-password').'" class="btn btn-yellow btn-block" /></div>');
                                        $personal->hiddenInput('uniq',1,$_SESSION["cp_uniq"]);
                                        $personal->HTML('<input type="hidden" name="authcode" value="'.urlencode($authcode1).'" />');
                                        $personal->display();
                                        ?>
                                    <div class="clear"></div>
                                </div>
                                <? } else { ?>
                                <div class="content">
                                    <h3 class="section_label">
                                        <span class="left"><i class="fa fa-mobile fa-2x"></i></span>
                                        <span class="right"><?= Lang::string('security-enter-token') ?></span>
                                    </h3>
                                    <form id="enable_tfa" action="change-password.php" method="POST">
                                        <input type="hidden" name="request_2fa" value="1" />
                                        <input type="hidden" name="authcode" value="<?= urlencode($authcode1) ?>" />
                                        <input type="hidden" name="uniq" value="<?= $_SESSION["cp_uniq"] ?>" />
                                        <input type="hidden" name="ex_request" value="<?= urlencode(serialize($_REQUEST)) ?>" />
                                        <div class="buyform">
                                            <div class="one_half">
                                                <div class="spacer"></div>
                                                <div class="spacer"></div>
                                                <div class="spacer"></div>
                                                <div class="param">
                                                    <label for="token"><?= Lang::string('security-token') ?></label>
                                                    <input name="token" id="token" type="text" value="<?= $token1 ?>" />
                                                    <div class="clear"></div>
                                                </div>
                                                <div class="mar_top2"></div>
                                                <ul class="list_empty">
                                                    <li><input type="submit" name="submit" value="<?= Lang::string('security-validate') ?>" class="but_user" /></li>
                                                    <? if (User::$info['using_sms'] == 'Y') { ?>
                                                    <li><input type="submit" name="sms" value="<?= Lang::string('security-resend-sms') ?>" class="but_user" /></li>
                                                    <? } ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="clear"></div>
                                </div>
                                <? } ?>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <br>
                                <p>Use this form to change your password.</p>
                                <p>After you submit it, you will be logged out and will need to log in with your new password.</p>
                                <p>Your password must be at least 8 characters long, but it is HIGHLY recommended that you choose a random, alphanumeric password of at least 32 characters.</p>
                                <p>NEVER use a password for an exchange that you use ANYWHERE else.</p>
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