<style type="text/css">
    .dropdown-menu
{
left : initial;
right:0;
}
</style>
<nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- <a class="navbar-brand" href="index">
                <h3  style="color:#fff;">Blockstreet</h3>
               <img src="images/star.png" alt="img" class="logo-star">
                <img src="images/logo1.png" alt="img" class="main-logo" style="filter: invert(100%);" />
                <img class="logo" src="sonance/img/logo.png" alt="">
            </a> -->
         <!--    <a class="navbar-brand" href="index">
                <img src="images/star.png" alt="img" class="logo-star">
                <img src="images/logo1.png" alt="img" class="main-logo" style="filter: invert(100%);" /> -->
                <!-- <img class="logo" src="sonance/img/logo.png" alt=""> -->
          <!--   </a> -->
            <?php if (User::isLoggedIn()): ?>
            <div class="collapse navbar-collapse justify-content-md-center" id="navbarToggler">
                <ul class="navbar-nav ml-auto">
                    
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            Last Price<br/>
                            <span class="text-success"><?= number_format($currentPair['lastPrice'], 8) ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            24th Change<br/>
                            <span class="text-danger"><?= number_format($currentPair['change_24hrs'], 8) ?> %</span>
                        </a>
                    </li>
                   <!--  <li class="nav-item">
                        <a class="nav-link" href="cryptoaddress">
                            24th Hight<br/>
                            <span >-0.44%</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cryptoaddress">
                            24th Low<br/>
                            <span>-0.44%</span>
                        </a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            24th Volume<br/>
                           <span class="green-text"><?= number_format($currentPair['transactions_24hrs'], 8) ?></span> <?= $c_currency_info['currency'] ?></span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <?php //echo $_SERVER['REQUEST_URI'];
                        $mystring = $_SERVER['REQUEST_URI'];
                            $first = strtok($mystring, '?');
                            if ($first == "/advanced-trade") {
                                $url_parame = "/advanced-trade" ;
                            }
                            if ($first == "/advanced-trade-new") {
                                $url_parame = "/advanced-trade-new" ;
                            }
                            ?>
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="line-height : 44px;"><?php echo str_replace("-", '/', $_REQUEST['trade']);  ?></a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown" style="overflow-y: scroll;height: 330px;">

                               <!--  <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=BTC-USD&c_currency=28&currency=27">BTC/USD</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=LTC-USD&c_currency=42&currency=27">LTC/USD</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=ETH-USD&c_currency=45&currency=27">ETH/USD</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=BCH-USD&c_currency=44&currency=27">BCH/USD</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=ZEC-USD&c_currency=43&currency=27">ZEC/USD</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=IOX-USD&c_currency=50&currency=27">IOX/USD</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=IOX-USD&c_currency=51&currency=27">USDT/USD</a> -->

                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=LTC-BTC&c_currency=42&currency=28">LTC/BTC</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=ETH-BTC&c_currency=45&currency=28">ETH/BTC</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=BCH-BTC&c_currency=44&currency=28">BCH/BTC</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=ZEC-BTC&c_currency=43&currency=28">ZEC/BTC</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=IOX-BTC&c_currency=50&currency=28">IOX/BTC</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=IOX-BTC&c_currency=51&currency=28">USDT/BTC</a>

                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=BTC-LTC&c_currency=28&currency=42">BTC/LTC</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=ETH-LTC&c_currency=45&currency=42">ETH/LTC</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=ZEC-LTC&c_currency=43&currency=42">ZEC/LTC</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=BCH-LTC&c_currency=44&currency=42">BCH/LTC</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=IOX-LTC&c_currency=50&currency=42">IOX/LTC</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=IOX-LTC&c_currency=51&currency=42">USDT/LTC</a>

                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=BTC-BCH&c_currency=28&currency=44">BTC/BCH</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=ETH-BCH&c_currency=45&currency=44">ETH/BCH</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=ZEC-BCH&c_currency=43&currency=44">ZEC/BCH</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=LTC-BCH&c_currency=42&currency=44">LTC/BCH</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=IOX-BCH&c_currency=50&currency=44">IOX/BCH</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=IOX-BCH&c_currency=51&currency=44">USDT/BCH</a>

                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=BTC-ZEC&c_currency=28&currency=43">BTC/ZEC</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=LTC-ZEC&c_currency=42&currency=43">LTC/ZEC</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=ETH-ZEC&c_currency=45&currency=43">ETH/ZEC</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=BCH-ZEC&c_currency=44&currency=43">BCH/ZEC</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=IOX-ZEC&c_currency=50&currency=43">IOX/ZEC</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=IOX-ZEC&c_currency=51&currency=43">USDT/ZEC</a>

                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=BTC-ETH&c_currency=28&currency=45">BTC/ETH</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=LTC-ETH&c_currency=42&currency=45">LTC/ETH</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=BCH-ETH&c_currency=44&currency=45">BCH/ETH</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=ZEC-ETH&c_currency=43&currency=45">ZEC/ETH</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=IOX-ETH&c_currency=50&currency=45">IOX/ETH</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=IOX-ETH&c_currency=51&currency=45">USDT/ETH</a>

                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=BTC-USD&c_currency=28&currency=51">BTC/USDT</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=LTC-USD&c_currency=42&currency=51">LTC/USDT</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=ETH-USD&c_currency=45&currency=51">ETH/USDT</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=BCH-USD&c_currency=44&currency=51">BCH/USDT</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=ZEC-USD&c_currency=43&currency=51">ZEC/USDT</a>
                                <a class="dropdown-item" href="<?php echo $url_parame;?>?trade=IOX-USD&c_currency=50&currency=51">IOX/USDT</a>

                            <!-- <a class="dropdown-item" href="javascript:void(0);">
                                INR/ZEC
                            </a>
                           
                            <a class="dropdown-item" href="javascript:void(0);">
                                USD/BTC
                            </a>
                             <a class="dropdown-item" href="javascript:void(0);">
                                PPT/BTC
                            </a> -->
                        </div>
                    </li>
                    <!-- <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle"  style="line-height: 44px;" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user"></i></a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="myprofile">
                                <strong>Account</strong><br>
                                <span><?= User::$info['first_name']?></span>
                            </a>
                            <a class="dropdown-item" href="mysecurity">
                                Security
                            </a>
                            <a class="dropdown-item" href="logout.php?log_out=1&uniq=<?= $_SESSION["logout_uniq"] ?>">
                                Logout
                            </a>
                        </div>
                    </li> -->
                </ul>
            </div>
            <? else: ?>
            <div class="collapse navbar-collapse justify-content-md-center" id="navbarToggler">
                <ul class="navbar-nav ml-auto">  
                    <li class="nav-item">
                        <a class="nav-link" href="#">Support</a>
                    </li>              
                    <li class="nav-item">
                        <a class="nav-link" href="login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register">Register</a>
                    </li>
                </ul>
            </div>
            <? endif; ?>
        </div>
    </nav>

<script>
window.onload = function() {
    
    //if the current page is simple trade page 
    if(window.location.pathname.search("/userbuy") != -1) {
        
        document.querySelector('title').innerHTML = '<?=$CFG->exchange_name?> | Simple Trade'
    } 
    // if the current page any other page hading heading container
    else  {
        document.querySelector('title').innerHTML = '<?=$CFG->exchange_name?> | Advanced Trade'
    }
    
}
</script>