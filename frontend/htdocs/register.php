<!DOCTYPE html>
<html lang="en">



<?php
// error_reporting(E_ERROR | E_WARNING | E_PARSE);
// ini_set('display_errors', 1);
include '../lib/common.php';
require_once ("cfg.php");

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

    // end of checking referral status
    
$_REQUEST['register']['country'] = (!empty($_REQUEST['register']['country'])) ? preg_replace("/[^0-9]/", "", $_REQUEST['register']['country']) : false;
$_REQUEST['register']['email'] = (!empty($_REQUEST['register']['email'])) ? preg_replace("/[^0-9a-zA-Z@\.\!#\$%\&\*+_\~\?\-]/", "", $_REQUEST['register']['email']) : false;

if (empty($CFG->google_recaptch_api_key) || empty($CFG->google_recaptch_api_secret))
    $_REQUEST['is_caco'] = (!empty($_REQUEST['form_name']) && empty($_REQUEST['is_caco'])) ? array('register' => 1) : (!empty($_REQUEST['is_caco']) ? $_REQUEST['is_caco'] : false);

if (empty($_REQUEST['form_name']))
    unset($_REQUEST['register']);

$register = new Form('register', false, false, 'form3');
unset($register->info['uniq']);
$register->verify();
$register->reCaptchaCheck();

if (!empty($_REQUEST['register']) && !$register->info['terms'])
    $register->errors[] = Lang::string('settings-terms-error');

if (!empty($_REQUEST['register']) && $CFG->register_status == 'suspended')
    $register->errors[] = Lang::string('register-disabled');

if (!empty($_REQUEST['register']) && (is_array($register->errors))) {
    $errors = array();

    if ($register->errors) {
        foreach ($register->errors as $key => $error) {
            if (stristr($error, 'login-required-error')) {
                $errors[] = Lang::string('settings-' . str_replace('_', '-', $key)) . ' ' . Lang::string('login-required-error');
            } elseif (strstr($error, '-')) {
                $errors[] = Lang::string($error);
            } else {
                $errors[] = $error;
            }
        }
    }

    Errors::$errors = $errors;
} elseif (!empty($_REQUEST['register']) && !is_array($register->errors)) {

    // API::add('User','getAlleKYC');
    // $query = API::send();
    // $email = array_search($_REQUEST['register']['email'], array_column($query['User']['getAlleKYC']['results'][0], 'email'));
    // if(!empty($email)){
    //     $errors[] = 'Email already exist.';
    //     Errors::$errors = $errors;
    // }
    // else{
            
    API::add('User', 'registerNew', array($register->info));
    // echo "INFO = ";
    // print_r($register->info) ;
    $query = API::send();
    $_SESSION["register_uniq"] = md5(uniqid(mt_rand(), true));
    $r_referral_code = $_REQUEST['referral_code'];
        if ($REFERRAL == true && $r_referral_code) {

            $referral_code = $r_referral_code;

            $url = $base_ip."check-referral-code.php?referral_code=".$referral_code;
            
            $ch = curl_init($url); 
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);      
            curl_close($ch); 
            $data = json_decode($response);

            if ($data->status == false) {
                $errors = array();
                $errors[] = 'Invalid referral code';
                Errors::$errors = $errors;
            }else{
                
                $url = $base_ip."add-user.php";

                $username = $register->info['first_name'] ." ". $register->info['last_name'];
                $fields = array(
                    'user_id' => urlencode($register->info['email']),
                    'username' => urlencode($username),
                    'referral_id' => urlencode($referral_code)
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
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                //execute post
                $result = curl_exec($ch);

                //close connection
                curl_close($ch);

                $response = json_decode($result);

                if ($response->status == false) {
                    $errors = array();
                    $errors[] = $response->message;
                    Errors::$errors = $errors;
                }
                else{
                    Link::redirect($CFG->baseurl . '/login?message=registered');
                }
                
            }
            //var_dump($response); exit;
        }else{
           
            Link::redirect($CFG->baseurl . '/login?message=registered');
        }
        
    //}
}

API::add('User', 'getCountries');
$query = API::send();


$_SESSION["register_uniq"] = md5(uniqid(mt_rand(), true));
?>
<head>
    <title><?= $CFG->exchange_name; ?> | Register</title>
  <?php include "bitniex/bitniex_header.php"; ?>
</head>
<style type="text/css">
    .errors li {
    border: 0 solid #FFFFFF;
    padding-bottom: 5px;
}
.errors {
    background-color: #FFDDDD;
    border: #F1BDBD 1px solid;
    color: #BD6767;
}.errors, .messages {
    margin-bottom: 20px;
    padding: 10px 10px 5px 10px;
    position: static;
    padding: 0;
    margin: 0 20px 0;
    background: transparent;
    border: none;
    box-shadow: none;
}ul {
    list-style: none;

}
.g-recaptcha iframe, .g-recaptcha div{
   margin: auto !important;
}

.col-md-6, .col-xs-12 {
    position: relative;
    width: 100%;
    min-height: 1px;
    padding-right: 15px;
    padding-left: 15px;
}
input.btn.btn-yellow.btn-block {
    padding: 7px 20px !important;
    font-size: 16px !important;
}
input.form-control {
    width: 100% !important;
    padding: .375rem .75rem !important;
    font-size: 1rem !important;
    line-height: 1.5 !important;
}
form.form.form3 a {
    font-size: 15px !important;
    padding: 0px !important;
}
</style>
<script src='https://www.google.com/recaptcha/api.js<?= ((!empty($CFG->language) && $CFG->language != 'en') ? '?hl=' . ($CFG->language == 'zh' ? 'zh-CN' : $CFG->language) : '') ?>'></script>
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
                <h1>CREATE YOUR ACCOUNT</h1>
            </div>
        </div>
    </header>
    <div class="page-container">
        <div class="container-fluid">
            <div class="row register-banner">
                <div class="container content">
                    <div class="register-box">
                        
                        <p><strong>Registering on BitExchange is the first step toward creating an account. Once your email is confirmed, you'll need to complete your profile and verify your identity before you can begin trading.</strong></p>
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                             
                            <?
            $currencies_list = array();
            if ($CFG->currencies) {
                foreach ($CFG->currencies as $key => $currency) {
                    if (is_numeric($key))
                        continue;

                    $currencies_list[$key] = $currency;
                }
            }

            $currencies_list1 = array();
            if ($CFG->currencies) {
                foreach ($CFG->currencies as $key => $currency) {
                    if (is_numeric($key) || $currency['is_crypto'] != 'Y')
                        continue;

                    $currencies_list1[$key] = $currency;
                }
            }

            Errors::display();
            Messages::display();

            $register->textInput('first_name', Lang::string('settings-first-name'), 'first_name', false, false, false, 'form-control');
            $register->textInput('last_name', Lang::string('settings-last-name'), false, false, false, false, 'form-control');
            $register->textInput('email', Lang::string('settings-email'), 'email', false, false, false, 'form-control');
            $register->textInput('phone', Lang::string('settings-phone'), 'phone', false, false, false, 'form-control');
            if ($REFERRAL == true) {
                $register->HTML('<label for="referral_code">Referral Code  <em>*</em> </label><input type="text" name="referral_code" value="" id="register_referral_code" class="form-control"><br>');
                //$register->textInput('referral_code', 'Referral Code', 'phone', false, false, false, 'form-control');
            }
            // $register->textInput('pan_no', Lang::string('settings-pan-number'), 'pan_no', false, false, false, 'form-control');
            // $register->selectInput('default_c_currency', Lang::string('default-c-currency'), 1, false, $currencies_list1, false, array('currency'), false, false, 'form-control');
            // $register->selectInput('default_currency', Lang::string('default-currency'), 1, false, $currencies_list, false, array('currency'), false, false, 'form-control');
            $register->checkBox('terms', Lang::string('settings-terms-accept'), false, false, false, false, false, false);
            $register->captcha(Lang::string('settings-capcha'));
            $register->HTML('<input type="hidden" name="default-currency" value="27">');
            $register->HTML('<input type="hidden" name="default-c-currency" value="28">');
            $register->HTML('<div class="form-group"><br><br><input type="submit" name="submit" value="' . Lang::string('home-register') . '" class="btn btn-yellow btn-block" /></div>');
            $register->hiddenInput('uniq', 1, $_SESSION["register_uniq"]);
            $register->display();

            ?>
               <style>
                input[name="register[terms]"] {
                    width: 15px;
                    height: 15px;
                }
                input[name="register[terms]"] + label {
                    vertical-align: middle;
                    margin-left: 5px;
                }
                
            </style>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <br>
                                <p>The email address you provide will become your BitExchange ID and will be used for all future communications, including account recovery. Protect your email account like you would your Bitniex account. Sign-ups using throwaway email addresses will be rejected.</p>
                                <p>Your password must be at least 8 characters long, but it is HIGHLY recommended that you choose a random, alphanumeric password of at least 32 characters.</p>
                                <p>EVER use a password for an exchange that you use ANYWHERE else, especially for the email address you sign up with</p>
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