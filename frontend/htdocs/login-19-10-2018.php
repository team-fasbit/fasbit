<!DOCTYPE html>
<html lang="en">
 <?php 
        include '../lib/common.php';
        if (User::isLoggedIn())
            Link::redirect('login');
        $page_title = Lang::string('home-login');
        // $user1 = (!empty($_REQUEST['login']['user'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['login']['user']) : false;
        $user1 = $_REQUEST['login']['user'];
        $pass1 = (!empty($_REQUEST['login']['pass'])) ? preg_replace($CFG->pass_regex, "",$_REQUEST['login']['pass']) : false;
        
        if (!empty($_REQUEST['submitted'])) {
            if (empty($user1)) {
                Errors::add(Lang::string('login-user-empty-error'));
            }
        
            if (empty($pass1)) {
                Errors::add(Lang::string('login-password-empty-error'));
            }
            
            // if (!empty($_REQUEST['submitted']) && (empty($_SESSION["register_uniq"]) || $_SESSION["register_uniq"] != $_REQUEST['uniq']))
            //  Errors::add('Page expired.');
            
            if (!empty(User::$attempts) && User::$attempts > 3 && !empty($CFG->google_recaptch_api_key) && !empty($CFG->google_recaptch_api_secret)) {
                $captcha = new Form('captcha');
                $captcha->reCaptchaCheck(1);
                if (!empty($captcha->errors) && is_array($captcha->errors)) {
                    Errors::add($captcha->errors['recaptcha']);
                }
            }
            if (!is_array(Errors::$errors)) {
                $login = User::logIn($user1,$pass1);
                // echo "<pre>"; print_r($login); exit;
                if ($login && empty($login['error'])) {
                    if (!empty($login['message']) && $login['message'] == 'awaiting-token') {
                        $_SESSION["register_uniq"] = md5(uniqid(mt_rand(),true));
                        Link::redirect('verify-token');
                    }
                    elseif (!empty($login['message']) && $login['message'] == 'logged-in' && $login['no_logins'] == 'Y') {
                        $_SESSION["register_uniq"] = md5(uniqid(mt_rand(),true));
                        Link::redirect('change-password');
                    }
                    elseif (!empty($login['message']) && $login['message'] == 'logged-in') {
                        $_SESSION["register_uniq"] = md5(uniqid(mt_rand(),true));
                        Link::redirect('deposits-withdrawls');
                    }
                }
                elseif (!$login || !empty($login['error'])) {
                    Errors::add(Lang::string('login-invalid-login-error'));
                }
            }
        }
        
        if (!empty($_REQUEST['message']) && $_REQUEST['message'] == 'registered')
            Messages::add(Lang::string('register-success'));
        
        $_SESSION["register_uniq"] = md5(uniqid(mt_rand(),true));
        ?>
        <style>
            .messages, .errors{
            position: relative;
            margin: 0 0 1em 3em;
        }
        </style>
<head>
   <?php include "bitniex/bitniex_header.php"; ?>
<style type="text/css">
    .errors li {
    border: 0 solid #FFFFFF;
    padding-bottom: 5px;
}
.errors, .messages {
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
.col-md-6, .col-xs-12 {
    position: relative;
    width: 100%;
    min-height: 1px;
    padding-right: 15px;
    padding-left: 15px;
}
a.btn.btn-yellow.btn-block {
    min-width: 100px;
    display: inline-block;
    padding: 7px 20px !important;
    border: 1px solid #c18102;
    background-color: #ffab06;
    border-radius: 3px;
    font-size: 16px !important;
    font-weight: 900;
    color: rgba(0, 0, 0, 0.7);
    text-decoration: none!important;
}
input.form-control {
    width: 100% !important;
    padding: .375rem .75rem !important;
    font-size: 1rem !important;
    line-height: 1.5 !important;
}
p.login-link a {
    font-size: 13px !important;
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
                <h1>SIGN IN TO YOUR ACCOUNT</h1>
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
                                <h5><strong>Sign In</strong></h5>
                                  <? 
                        if (count(Errors::$errors) > 0) {
                            echo '<span style="display: inline-block;margin: 0 0 1em;font-size: 14px;width: 100%;
                        color: red;background: #f7e0e0;padding: 10px;border-radius: 3px;">'.Errors::$errors[0].'</span>';
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
                         <form method="POST" action="login" name="login">
                        <div class="form-group">
                            <div class="input-group">
                                <!-- <span class="input-group-addon"><i class="fas fa-envelope"></i></span> -->
                                <input class="form-control" type="email" name="login[user]" value="" placeholder="Email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <!-- <span class="input-group-addon"><i class="fas fa-key"></i></span> -->
                                <input class="form-control" type="password" name="login[pass]" value="" placeholder="Password" required>
                            </div>
                        </div>
                        <? if (!empty(User::$attempts) && User::$attempts > 2 && !empty($CFG->google_recaptch_api_key) && !empty($CFG->google_recaptch_api_secret)) { ?>
                        <div style="margin-bottom:10px;">
                            <div class="g-recaptcha" data-sitekey="<?= $CFG->google_recaptch_api_key ?>"></div>
                        </div>
                        <? } ?>
                        <input type="hidden" name="submitted" value="1" />
                        <input type="hidden" name="uniq" value="<?= $_SESSION["register_uniq"] ?>" />
                        <button type="submit" class="btn btn-yellow btn-block"><?= Lang::string('home-login') ?></button>
                        <!-- <a href="profile.html" class="btn btn-primary">Login</a> -->
                        <p class="login-link"><a href="forgot">Forgot your password?</a></p>
                    </form>
                              <!--   <div class="form-group">
                                    <label for="exampleInputEmail1">Email</label>
                                    <input type="email" class="form-control" placeholder="Enter email">
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" class="form-control" placeholder="Password">
                                </div>
                                <div class="form-group">
                                    <a class="btn btn-yellow btn-block" href="profile.html">Sign in</a>
                                </div>
                                <p class="login-link"><a href="forgot-password.html">Forgot your password?</a></p> -->
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