<!DOCTYPE html>
<html lang="en">
<?php
// error_reporting(E_ALL); 
// ini_set('display_errors', 'On');
include '../lib/common.php';

$page_title = Lang::string('login-forgot');
$email1 = (!empty($_REQUEST['forgot']['email'])) ? preg_replace("/[^0-9a-zA-Z@\.\!#\$%\&\*+_\~\?\-]/", "",$_REQUEST['forgot']['email']) : false;
$captcha_error = false;

if (!empty($_REQUEST['forgot']) && $email1 && $_SESSION["forgot_uniq"] == $_REQUEST['uniq']) {
    if (empty($CFG->google_recaptch_api_key) || empty($CFG->google_recaptch_api_secret)) {
        include_once 'securimage/securimage.php';
        $securimage = new Securimage();
        $captcha_error = (empty($_REQUEST['forgot']['captcha']) || !$securimage->check($_REQUEST['forgot']['captcha']));
    }
    else {
        $captcha = new Form('captcha');
        $captcha->reCaptchaCheck(1);
        if (!empty($captcha->errors) && is_array($captcha->errors)) {
            $captcha_error = true;
            Errors::add($captcha->errors['recaptcha']);
        }
    }
    
    if (!$captcha_error) {
        API::add('User','resetUser',array($email1));
        $query = API::send();

        Messages::$messages = array();
        Messages::add(Lang::string('login-password-sent-message'));
    }
    else {
        // echo "capcha-error"; exit;
        Errors::add(Lang::string('login-capcha-error'));
    }
}

$_SESSION["forgot_uniq"] = md5(uniqid(mt_rand(),true));
?>
<head>
    <title><?= $CFG->exchange_name; ?> | Forgot Password</title>
   <?php include "bitniex/bitniex_header.php"; ?>

<script src='https://www.google.com/recaptcha/api.js<?= ((!empty($CFG->language) && $CFG->language != 'en') ? '?hl='.($CFG->language == 'zh' ? 'zh-CN' : $CFG->language) : '') ?>''></script>
<style>
.message-box-wrap
{
position: static !important;
    width: 100%;
    margin: 10px 0;
    padding: 10px !important;
    background-color: #DFFBE4 !important;
    border: #A9ECB4 1px solid;
    color: #1EA133;
    box-shadow: none !important;
    border-radius: 3px;
    }
.messages {
position: relative !important;
font-size: 14px !important;
z-index: 999 !important;

}
.rc-anchor-normal{
    width: 99% !important;
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
                <h1>RESET YOUR PASSWORD</h1>
            </div>
        </div>
    </header>
    <div class="page-container">
        <div class="container-fluid">
            <div class="row register-banner">
                <div class="container content">
                    <div class="register-box">
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <h5><strong>Reset</strong></h5>
                                 <? 
                    if (count(Errors::$errors) > 0) {
                        echo '<span style="display: inline-block;margin: 0 20px;font-size: 14px;color: red;">'.Errors::$errors[0].'</span>';
                    }
                    
                    if (count(Messages::$messages) > 0) {
                        echo '
                    <div class="messages" id="div4">
                        <div class="message-box-wrap">
                            '.Messages::$messages[0].'
                        </div>
                    </div>';
                    }
                ?>
                <form method="POST" action="forgot.php" name="forgot">  
                <div class="form-group">
                    <div class="input-group">
                        <!-- <span class="input-group-addon"><i class="fas fa-envelope"></i></span> -->
                        <input type="email" class="form-control"  name="forgot[email]" value="<?= $email1 ?>" placeholder="Enter your email.." >
                    </div>
                </div>
                <? 
                if (empty($CFG->google_recaptch_api_key) || empty($CFG->google_recaptch_api_secret)) { ?>
                <div>
                    <div><?= Lang::string('settings-capcha') ?></div> 
                    <img class="captcha_image" src="securimage/securimage_show.php" />
                </div>
                <div class="loginform_inputs">
                    <div class="input_contain">
                        <i class="fa fa-arrow-circle-o-up"></i>
                        <input type="text" class="login" name="forgot[captcha]" value="" />
                    </div>
                </div>
                <? } else { ?>
                <div style="margin-bottom:10px;">
                    <div class="g-recaptcha" data-sitekey="<?= $CFG->google_recaptch_api_key ?>"></div>
                </div>
                <? } ?>
                <input type="hidden" name="uniq" value="<?= $_SESSION["forgot_uniq"] ?>" />
                <div class="form-group">
                    <input class="btn btn-yellow btn-block" type="submit" name="submit" value="<?= Lang::string('login-forgot-send-new') ?>" class="btn btn-primary" />
                </div>
            </form>
                               
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <h5><strong>Don't have an account?</strong></h5>
                                <p>Create one to start trading on the world's most active digital asset exchange.</p>
                                 <div class="form-group">
                                    <a class="btn btn-yellow btn-block" href="register">Create your account</a>
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