 <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="index"><img class="logo" src="images/logo1.png" alt="" style="filter: invert(100%);"></a>
            <div class="collapse navbar-collapse justify-content-md-center" id="navbarToggler">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="exchange?trade=BTC-USD&c_currency=28&currency=27"><span class="name">EXCHANGE</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://www.awax-bank.com"><span class="name">BANK</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://ico.awax.co.uk"><span class="name">ICO</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://bounty.awax.co.uk"><span class="name">BOUNTY</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://www.awax.co.uk"><span class="name">AWAX PROJECT</span></a>
                    </li>
                  <!--   <li class="nav-item">
                        <a class="nav-link" href="margin-trading.html"><span class="name">MARGIN TRADING</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="lending.html"><span class="name">LENDING</span></a>
                    </li> -->
                </ul>
                <?php
                                            if(!User::isLoggedIn()){
                                            ?>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login">Signin</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register">Create Account</a>
                    </li>
                </ul>
                <?php 
                    }else{
                        ?>
                         <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">BALANCES</a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="transfer-balances">
                               TRANSFER BALANCES
                            </a>
                            <a class="dropdown-item" href="deposits-withdrawls">
                                DEPOSITS & WITHDRAWLS
                            </a>
                            <a class="dropdown-item" href="history">
                                HISTORY
                            </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">ORDERS</a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="openorders?c_currency=28&currency=27">
                               MY OPEN ORDERS
                            </a>
                            <a class="dropdown-item" href="tradehistory?c_currency=28&currency=27">
                                MY TRADE HISTORY
                            </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-cog"></i></a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="mysecurity">
                               2 FACTOR-AUTHENTICATION
                            </a>
                            <a class="dropdown-item" href="api-access">
                                API KEYS
                            </a>
                            <a class="dropdown-item" href="trading-tier-status">
                                TRADING TIER STATUS
                            </a>
                            <a class="dropdown-item" href="account-activities">
                                Account Activities
                            </a>
                            <a class="dropdown-item" href="change-password">
                                CHANGE PASSWORD
                            </a>
                            
                        </div>
                    </li>
                    <li class="nav-item dropdown active">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-user"></i></a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item active" href="myprofile">
                               MY PROFILE
                            </a>
                            <a class="dropdown-item" href="logout.php?log_out=1&uniq=<?= $_SESSION["logout_uniq"] ?>">
                                LOGOUT
                            </a>
                            <!-- <a class="dropdown-item" href="bitniex/linked-accounts.html">
                               LINKED ACCOUNTS
                            </a> -->
                        </div>
                    </li>
                </ul>
                        <?php
                    }
                ?>
            </div>
        </div>
    </nav>