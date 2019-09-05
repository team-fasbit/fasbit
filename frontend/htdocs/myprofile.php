<!DOCTYPE html>
<html lang="en">
<?php 
include '../lib/common.php';
    if (User::$info['locked'] == 'Y' || User::$info['deactivated'] == 'Y')
        Link::redirect('myprofile');
    elseif (User::$awaiting_token)
        Link::redirect('verify_token');
    elseif (!User::isLoggedIn())
        Link::redirect('login');
    
        require_once ("cfg.php");
    $token1 = (!empty($_REQUEST['token'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['token']) : false;
    $authcode1 = (!empty($_REQUEST['authcode'])) ? $_REQUEST['authcode'] : false;
    $email_auth = false;
    $match = false;
    $request_2fa = false;
    $too_few_chars = false;
    $expired = false;
    $no_token = false;
    $same_currency = false;

    // CHECKING REFErral status 
    $ch = curl_init("http://18.223.166.16/api/get-settings.php"); 
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);      
    curl_close($ch);
    $ref_response = json_decode($output);
    if ($ref_response->is_referral == 1) {
        $GLOBALS['REFERRAL'] = true;
        $GLOBALS['REFERRAL_BASE_URL'] = "http://18.223.166.16/api/";
        //$GLOBALS['REFERRAL_BASE_URL'] = $ref_response->base_url;
    }else{
       $GLOBALS['REFERRAL'] = false; 
    }
    
    API::add('User','getInfo',array($_SESSION['session_id']));
    API::add('Currencies','getMain');
    $query = API::send();

    $userInfo = $query['User']['getInfo']['results'][0];

    $main = $query['Currencies']['getMain']['results'][0];
    if ($authcode1) {
        API::add('User','getSettingsChangeRequest',array(urlencode($authcode1)));
        $query = API::send();
    // echo "<pre>"; print_r($query['User']['getSettingsChangeRequest']['results'][0]); exit;
        if ($query['User']['getSettingsChangeRequest']['results'][0]) {
            $_REQUEST = unserialize(base64_decode($query['User']['getSettingsChangeRequest']['results'][0]));
            // echo "This is in authcode" ;
            /* echo "<pre>" ;
            print_r($_REQUEST) ;
            exit ; */
            unset($_REQUEST['submitted']);
            $email_auth = true;
        }
        else
            Errors::add(Lang::string('settings-request-expired'));
    }
    if (empty($_REQUEST['settings']['pass'])) {
        unset($_REQUEST['settings']['pass']);
        unset($_REQUEST['settings']['pass2']);
        unset($_REQUEST['verify_fields']['pass']);
        unset($_REQUEST['verify_fields']['pass2']);
    }
    else {
        $_REQUEST['verify_fields']['pass'] = 'password';
        $_REQUEST['verify_fields']['pass2'] = 'password';
    }
    if (!empty($_REQUEST['settings'])) {
        // echo "String <pre>" ;
        // print_r($_REQUEST) ;
       
        if (!$email_auth && (empty($_SESSION["settings_uniq"]) || $_SESSION["settings_uniq"] != $_REQUEST['settings']['uniq']))
            $expired = true;
        
        if (!empty($_REQUEST['settings']['pass'])) {
            $match = preg_match_all($CFG->pass_regex,$_REQUEST['settings']['pass'],$matches);
            $too_few_chars = (mb_strlen($_REQUEST['settings']['pass'],'utf-8') < $CFG->pass_min_chars);
        }
        
        if (!empty($_REQUEST['settings']['pass'])) {
            $_REQUEST['settings']['pass'] = preg_replace($CFG->pass_regex, "",$_REQUEST['settings']['pass']);
            $_REQUEST['settings']['pass2'] = preg_replace($CFG->pass_regex, "",$_REQUEST['settings']['pass2']);
        }
        
        // if ($_REQUEST['settings']['default_currency'] == $_REQUEST['settings']['default_c_currency']) {
        //     $same_currency = true;
        //     $_REQUEST['settings']['default_currency'] = false;
        // }
        
        $_REQUEST['settings']['first_name'] = preg_replace("/[^\pL a-zA-Z0-9@\s\._-]/u", "",$_REQUEST['settings']['first_name']);
        $_REQUEST['settings']['last_name'] = preg_replace("/[^\pL a-zA-Z0-9@\s\._-]/u", "",$_REQUEST['settings']['last_name']);
        //$_REQUEST['settings']['country'] = preg_replace("/[^0-9]/", "",$_REQUEST['settings']['country']);
        $_REQUEST['settings']['email'] = preg_replace("/[^0-9a-zA-Z@\.\!#\$%\&\*+_\~\?\-]/", "",$_REQUEST['settings']['email']);
      //  $_REQUEST['settings']['chat_handle'] = preg_replace("/[^\pL a-zA-Z0-9@\s\._-]/u", "",$_REQUEST['settings']['chat_handle']);
    }
    
    $personal = new Form('settings',false,false,'form1','site_users');
    if (!empty($query['User']['getInfo']['results'][0])){
        $personal->get($query['User']['getInfo']['results'][0]);
    }
    
    if (!$personal->info['email'])
        unset($personal->info['email']);
    //echo "<pre>";var_dump($_REQUEST);die();
    $personal->verify();
    
    if ($expired)
        $personal->errors[] = 'Page expired.';
    if ($match)
        $personal->errors[] = htmlentities(str_replace('[characters]',implode(',',array_unique($matches[0])),Lang::string('login-pass-chars-error')));
    if ($too_few_chars) 
        $personal->errors[] = Lang::string('login-password-error');
    // if ($same_currency)
    //     $personal->errors[] = Lang::string('same-currency-error');
    
    
    if (!empty($_REQUEST['submitted']) && empty($_REQUEST['settings'])) {
        if (!$email_auth && (empty($_SESSION["settings_uniq"]) || $_SESSION["settings_uniq"] != $_REQUEST['uniq']))
            Errors::add('Page expired.');
    }
    
    if (!empty($_REQUEST['submitted']) && !$token1 && !is_array($personal->errors) && !is_array(Errors::$errors)) {
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
        else {
            API::add('User','settingsEmail2fa',array($_REQUEST));
            $query = API::send();
            
            $_SESSION["settings_uniq"] = md5(uniqid(mt_rand(),true));
            Link::redirect('myprofile?notice=email');
        }
    }
    
    if (!empty($_REQUEST['settings']) && is_array($personal->errors)) {
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
        $request_2fa = false;
    }
    elseif (!empty($_REQUEST['settings']) && !is_array($personal->errors)) {
        if (empty($no_token) && !$request_2fa) {
            API::settingsChangeId($authcode1);
            API::token($token1);
            // echo "<pre>"; print_r($personal->info); exit;
            API::add('User','updatePersonalInfo',array($personal->info));
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
                $_SESSION["settings_uniq"] = md5(uniqid(mt_rand(),true));
                Link::redirect('myprofile?message=settings-personal-message');
            }
            else
                $request_2fa = true;
        }
    }
    
    if (!empty($_REQUEST['prefs'])) {
        if (!$email_auth && (empty($_SESSION["settings_uniq"]) || $_SESSION["settings_uniq"] != $_REQUEST['uniq']))
            Errors::add('Page expired.');
        elseif (!$no_token && !$request_2fa) {
            API::settingsChangeId($authcode1);
            API::token($token1);
            API::add('User','updateSettings',array($confirm_withdrawal_2fa_btc1,$confirm_withdrawal_email_btc1,$confirm_withdrawal_2fa_bank1,$confirm_withdrawal_email_bank1,$notify_deposit_btc1,$notify_deposit_bank1,$notify_login1,$notify_withdraw_btc1,$notify_withdraw_bank1));
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
                $_SESSION["settings_uniq"] = md5(uniqid(mt_rand(),true));
                Link::redirect('settings?message=settings-settings-message');
            }
            else
                $request_2fa = true;
        }
    }
    
    if (!empty($_REQUEST['message'])) {
        if ($_REQUEST['message'] == 'settings-personal-message')
            Messages::add(Lang::string('settings-personal-message'));
        elseif ($_REQUEST['message'] == 'settings-settings-message')
            Messages::add(Lang::string('settings-settings-message'));
        elseif ($_REQUEST['message'] == 'settings-account-deactivated')
            Messages::add(Lang::string('settings-account-deactivated'));
        elseif ($_REQUEST['message'] == 'settings-account-reactivated')
            Messages::add(Lang::string('settings-account-reactivated'));
        elseif ($_REQUEST['message'] == 'settings-account-locked')
            Messages::add(Lang::string('settings-account-locked'));
        elseif ($_REQUEST['message'] == 'settings-account-unlocked')
            Messages::add(Lang::string('settings-account-unlocked'));
    }
    
    if (!empty($_REQUEST['notice']) && $_REQUEST['notice'] == 'email')
        $notice = Lang::string('settings-change-notice');
    
    $cur_sel = array();
    if ($CFG->currencies) {
        foreach ($CFG->currencies as $key => $currency) {
            if (is_numeric($key))
                continue;
            
            $cur_sel[$key] = $currency;
        }
    }
    
    $cur_sel1 = array();
    if ($CFG->currencies) {
        foreach ($CFG->currencies as $key => $currency) {
            if (is_numeric($key) || $currency['is_crypto'] != 'Y')
                continue;
    
            $cur_sel1[$key] = $currency;
        }
    }
    
    $_SESSION["settings_uniq"] = md5(uniqid(mt_rand(),true));


    /* history section starts here */
    $page1 = (!empty($_REQUEST['page'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['page']) : false;
    $bypass = !empty($_REQUEST['bypass']);

    API::add('History','get',array(1,$page1));
    $query = API::send();
    $total = $query['History']['get']['results'][0];

    API::add('History','get',array(false,$page1,30));
    $query = API::send();

    $history = $query['History']['get']['results'][0];
    $pagination = Content::pagination('usersecurity.php',$page1,$total,30,5,false);

    $page_title = Lang::string('history');
    /* history section endss here */

    // start of referral 
        if ($REFERRAL == true) {

            $user_email = $userInfo['email'];
            $url = $REFERRAL_BASE_URL."get-user-bonus.php";

            $fields = array(
                'user_id' => urlencode($user_email),
                'name' => urlencode($name),                
                'email' => urlencode($user_email)
            );

            //url-ify the data for the POST
            foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
            rtrim($fields_string, '&');
           //open connection
            $ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, count($fields));
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //execute post
            $result = curl_exec($ch);
            $response = json_decode($result);
            //close connection
            curl_close($ch);
            $referral_code = $response->data->referral_code;
            $bonous_point = $response->data->bonous_point;

            //
            $his_url = $REFERRAL_BASE_URL."get-usage-history.php";
            $ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, count($fields));
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //execute post
            $result = curl_exec($ch);
            $response = json_decode($result);
            //var_dump($response);
            //close connection
            curl_close($ch);
        }
        
    // end of referral
    
?>
<head>
    <title><?= $CFG->exchange_name; ?> | My Profile</title>
   <?php include "bitniex/bitniex_header.php"; ?>
</head>

<body id="wrapper">
    <div id="colorPanel" class="colorPanel">
        <a id="cpToggle" href="#"></a>
        <ul></ul>
    </div>
    <?php include "bitniex/home_nav_bar.php"; ?>
     
    <style>
        footer .links ul li {
    /* display: inline-block; */
    list-style: none;
    display: block;
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
        .form-control
        {
            height : 32px;
            font-size: 13px;
        }
        .profile-content h6{
            font-size: 13px;
        }
        .messages, .errors {
    list-style-type: none;
    background: #DFFBE4;
    padding: 15px;
    border-radius: 3px;
    /*position: absolute;*/
    right: 20px;
    font-size: 14px;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    /*z-index: 999;*/
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
form.form.form1 {
    padding: 20px;
}
    </style>
    <header>
        <div class="banner row no-margin">
            <div class="container content">
                <br>
                <h1>User Profile Settings</h1>
                <!-- <p class="sub-title text-center">To begin trading, you’ll first need to submit your profile for verification.</p> -->
                <!-- <p class="text-center"><small>Note: you may only have one profile. If you have more than one account, you need to link them rather than submit multiple profiles.</small></p> -->
            </div>
        </div>
    </header>
    <div class="page-container">
        <div class="container-fluid">
            <div class="row profile-banner">
                <div class="container content">
                     <?php 
                    Errors::display(); 
                    Messages::display();
                    ?>
                <?php if(!empty($notice)): ?>
                     <div class="notice">
                    <div class="message-box-wrap alert alert-info"><?=$notice?></div>
                </div>
                <?php endif; ?>
                    <div class="profile-box">
                        <div class="row">
                             <?php // if ($REFERRAL == true) { ?>
                    <!-- <div class="col-lg-7 col-sm-12"> -->
                    <?php //}else{ ?>
                    <div class="col-sm-12">
                    <?php //} ?>
                     <div class="pro card">
                            <div class="card-header">
                                <h6><strong>Personal Details</strong></h6>
                            </div>
                                <div class="card-body">
                                    <div>
                                        <!-- <div class="profile-img" style="background-image: url(sonance/img/user.png);"></div> -->
                                        <div class="profile-content" style="padding-left: 0;">
                                            <h6><strong>Name :</strong> <?=$userInfo['first_name'].' '.$userInfo['last_name'] ?></h6>
                                            <h6><strong>Email : </strong><?=$userInfo['email'] ?></h6>
                                            <h6><strong>Phone :</strong> <?=$userInfo['phone'] ?></h6>
                                        </div>
                                    </div>
                                </div>
                        </div>
                                
                                    <?php
                                        // if ($userInfo['first_name'] && $userInfo['last_name'] && $userInfo['email'] && $userInfo['country'] != 0) {
                                        //     $complete_value1 ="";
                                        // }else{
                                        //     $complete_value1 ="disabled";
                                        // }
                                        // if ($userInfo['first_name']) {$first_readonly ="readonly";}else{$first_readonly ="";}
                                        // if ($userInfo['last_name']) {$last_readonly ="readonly";}else{$last_readonly ="";}
                                        // if ($userInfo['email']) {$email_readonly ="readonly";}else{$email_readonly ="";}
                                        // if ($userInfo['country'] != 0) {$country_readonly ="readonly";$country_value = $userInfo['country'];}else{$country_readonly ="";$country_value = "";}
                                    ?>
                                <!-- <div class="form-group">
                                    <label for="exampleInputEmail1">Username / Email</label>
                                    <input type="email" class="form-control" <? // echo $email_readonly;?> value="<?=$userInfo['email']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">First Name</label>
                                    <input type="text" class="form-control" <? // echo $first_readonly;?> value="<?=$userInfo['first_name'];?>">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Last Name</label>
                                    <input type="text" class="form-control" <? // echo $last_readonly;?> value="<?=$userInfo['last_name']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Country</label>
                                    <input type="text" class="form-control" <? // echo $country_readonly;?> value="<?=$country_value; ?>">
                                </div> -->
                            </div>
                             <?php if ($REFERRAL == true) { ?>

                    <div class="col-lg-12 col-sm-12"><br>
                        <div class="pro card">
                        <div class="card-header">
                            <h6><strong>Referral point</strong></h6>
                        </div>
                        <input type="text" style="display: none;" name="ref_code" id="referral_code" value="<? echo $referral_code; ?>">
                        <div class="card-body">
                            <h6>
                                <strong>Referral code:</strong>&nbsp;<? echo $referral_code; ?> 
                            </h6>
                            <h6>
                                <strong>Available Points:</strong>&nbsp;<? echo $bonous_point; ?> Points 
                            </h6>
                            <p><a href="#referaltrans" data-toggle="modal"><u>View transactions</u></a></p>

                            <p>1 Referral point = $ <?php echo 1 / $ref_response->one_point_value; ?></p>
                            <p>1 Referral point = BTC <?php echo 1 / $ref_response->BTC; ?></p>
                            <p>1 Referral point = LTC <?php echo 1 / $ref_response->LTC; ?></p>
                            <p>1 Referral point = BCH <?php echo 1 / $ref_response->BCH; ?></p>
                            <p>1 Referral point = ZEC <?php echo 1 / $ref_response->ZEC; ?></p>
                            <p>1 Referral point = ETH <?php echo 1 / $ref_response->ETH; ?></p>
                             <p>1 Referral point = IOX <?php echo 1 / $ref_response->IOX; ?></p>
                             <p>1 Referral point = USDT <?php echo 1 / $ref_response->USDT; ?></p>
                        </div>
                        </div>
                    </div>

                    <?php } ?> 
                          <!--   <div class="col-md-6 col-xs-12">
                               <h5><strong>Level 1 Verification</strong></h5>
                                <p>DAily withdrawl limit $2000 USD Equivalent</p>
                                <div class="form-group">
                                    <a class="btn btn-yellow btn-block <? // echo $complete_value1;?>">COMPLETE</a>
                                </div>
                            </div> -->
                        </div>
                           
                        <br>
                    <div class="row">
                        <div class="col-md-12 col-xs-12">
                            <div class="pro card">
                                 <?php 
                                    $personal->passwordInput('pass',Lang::string('settings-pass'), false, false, false, 'form-control');
                                    $personal->passwordInput('pass2',Lang::string('settings-pass-confirm'),false,false,false,'form-control',false,false,'pass');
                                    $personal->textInput('first_name',Lang::string('settings-first-name'), false, false, false, false, 'form-control');
                                    $personal->textInput('last_name',Lang::string('settings-last-name'), false, false, false, false, 'form-control');
                                    $personal->textInput('phone',Lang::string('settings-Phone'),false, false, false, false, 'form-control');
                                    $personal->textInput('email',Lang::string('settings-email'),'email', false, false, false, 'form-control', 'disabled');
                                    $personal->HTML('<div class="form_button"><input type="submit" name="submit" value="'.Lang::string('settings-save-info').'" class="btn btn-yellow btn-block" /></div><input type="hidden" name="submitted" value="1" />');
                                    $personal->hiddenInput('uniq',1,$_SESSION["settings_uniq"]);
                                    $personal->display();
                                    ?>
                            </div>
                        </div>
                            <!-- <div class="col-md-6 col-xs-12">
                                <?php
                                        //if ($userInfo['street_address'] && $userInfo['city'] && $userInfo['state'] && $userInfo['postal_code'] && $userInfo['phone'] && $userInfo['date_birth']) {
                                       //     $complete_value1 ="";
                                       // }else{
                                         //   $complete_value1 ="disabled";
                                      //  }
                                       // if ($userInfo['street_address']) {$street_address_readonly ="readonly";}else{$street_address_readonly ="";}
                                       // if ($userInfo['city']) {$city_readonly ="readonly";}else{$city_readonly ="";}
                                       // if ($userInfo['state']) {$state_readonly ="readonly";}else{$state_readonly ="";}
                                       // if ($userInfo['postal_code'] != 0) {$postal_code_readonly ="readonly";}else{$postal_code_readonly ="";}
                                       // if ($userInfo['phone'] != 0) {$phone_readonly ="readonly";}else{$phone_readonly ="";}
                                       // if ($userInfo['date_birth'] != 0) {$date_birth_readonly ="readonly";}else{$date_birth_readonly ="";}
                                    ?>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Street Address</label>
                                    <input type="text" class="form-control" <? // echo $street_address_readonly;?> value="<?=$userInfo['street_address']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">City</label>
                                    <input type="text" class="form-control" <? // echo $city_readonly;?> value="<?=$userInfo['city']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">State</label>
                                    <input type="text" class="form-control" <? // echo $state_readonly;?> value="<?=$userInfo['state']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Postal Code</label>
                                    <input type="text" class="form-control" <? // echo $postal_code_readonly;?> value="<?=$userInfo['postal_code']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Country</label>
                                    <input type="text" class="form-control" <? // echo $country_readonly;?> value="<?=$country_value; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Phone Number</label>
                                    <input type="number" class="form-control" <? // echo $phone_readonly;?> value="<?=$userInfo['phone']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Date Of Birth</label>
                                    <input type="date" class="form-control" <? // echo $date_birth_readonly;?> value="<?=$userInfo['date_birth']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <h5><strong>Level 2 Verification</strong></h5>
                                <p>DAily withdrawl limit $7000 USD Equivalent</p>
                                <div class="form-group">
                                    <button class="btn btn-yellow btn-block" <? // echo $complete_value1;?>>INCOMPLETE</button>
                                </div>
                            </div> -->
                        </div>
                        <!-- <hr>
                        <br>
                       <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Scan Your Photo ID</label>
                                    <input type="file" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Picture of Yourself</label>
                                    <input type="file" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <h5><strong>Level 3 Verification</strong></h5>
                                <p>Daily withdrawl limit $25000 USD Equivalent</p>
                                <div class="form-group">
                                    <button class="btn btn-yellow btn-block" disabled="">INCOMPLETE</button>
                                </div>
                            </div>
                        </div> 
                        <div class="row">
                            <div class="col-md-12 col-xs-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="exampleCheck1" checked>
                                    <label class="form-check-label" for="exampleCheck1">I agree to the Terms of Use.</label>
                                </div> 
                                <br>
                               <div class="form-group">
                                    <a class="btn btn-yellow btn-block">SAVE PROFILE</a>
                                </div>
                            </div>
                        </div> -->
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