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

$page_title = Lang::string('api-access-setup');
 include "includes/sonance_header.php"; 
// include 'includes/head.php';
?>
<style>
	.info {
    color: #4a84bc;
    border-color: #c6e3ff;
    background-color: #e6f3ff;
    border-radius: 0px;
    border: 1px solid #bbb;
    margin-bottom: 20px;
    font-size: 13px;
}
.message-box-wrap {
    border: 0px solid #fff;
    padding: 10px;
    font-size: 15px;
}
td.api-label.first {
    color: #FFF;
    background-color: #aaaaaa;
}
td.api-key {
    text-align: center;
    background-color: #f5f5f5;
    font-size: 16px;
    color: #1889c1;
    font-weight: bold;
}
td.api-label {
    background-color: #f5f5f5;
}
.testimonials-4 .content {
    padding: 20px;
    background-color: #fff;
    font-size: 12px;
    font-weight: 600;
    overflow: auto;
}
label {
    font-size: 16px;
}
input.btn.form-control {
    font-size: 16px;
}
.btn-size {
    font-size: 14px;
}
a.but_user {
    width: auto !important;
    display: block;
    padding: 14px !IMPORTANT;
    color: #fff!important;
    border: 0;
    background-color: #4fc992 !important;
    border-radius: 2px;
    font-size: 14px !important;
    font-weight: 900;
    text-align: center;
    width: 200px !important;
}
footer .links ul li {
    display: list-item !important;
}
ul.list_empty {
    list-style-type: none;
}
.banner:before {
    content: ' ';
    display: block;
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    opacity: 0.95;
    background-color: #fff;
    z-index: 2;
}
</style>
<title><?= $CFG->exchange_name; ?> | Api Access</title>
    <?php include "bitniex/bitniex_header.php"; ?>
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
                <h1>Api Access</h1>
            </div>
        </div>
    </header>
<!-- <div class="page_title">
	<div class="container">
		<div class="title"><h1><?= $page_title ?></h1></div>
        <div class="pagenation">&nbsp;<a href="index.php"><?= Lang::string('home') ?></a> <i>/</i> <a href="account.php"><?= Lang::string('account') ?></a> <i>/</i> <a href="api-access.php"><?= $page_title ?></a></div>
	</div>
</div> -->
<div class="page-container">
        <div class="container-fluid">
            <div class="row profile-banner">
                <div class="container content">
                    <div class="profile-box">
                        <div class="row">
			  <div class="col-md-12">
                        <br>
                       				<? 
            Errors::display(); 
            Messages::display();
            if (!empty($info_message)) {
            	?>
            	<style>
            		.text.dotted {
    					border: 3px dashed #77c79a;
    					padding: 10px 15px;
    					margin-bottom: 15px;
					}
					.text.dotted span.bigger {
    					font-size: 20px;
    					font-weight: 900;
    					line-height: 3;
					}
            	</style>
            	<?php
				echo '<div class="text dotted"><p>'.$info_message.'</p></div><div class="clear"></div><div class="mar_top1"></div>';
			} 
			else {
			?>
			<div class="info"><div class="message-box-wrap"><?= Lang::string('api-go-to-docs') ?></div></div>
			<?
			}
            ?>
            <? if ($no_2fa) { ?>
            <style>
            	.content1 {
    				text-align: center;
				}
            	.content1 ul.list_empty li a.but_user {
    				margin-left: auto;
    				margin-right: auto;
    				margin-top: 20px;
				}
            </style>
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
				<!-- <h3 class="section_label">
					<span class="left"><i class="fa fa-mobile fa-2x"></i></span>
					<span class="right"><?= Lang::string('security-enter-token') ?></span>
				</h3> -->
				<form id="enable_tfa" action="api-access.php" method="POST">
					<input type="hidden" name="request_2fa" value="1" />
					<input type="hidden" name="permissions" value="<?= urlencode(serialize($permissions)) ?>" />
					<input type="hidden" name="remove_id" value="<?= $remove_id1 ?>" />
					<input type="hidden" name="action" value="<?= preg_replace("/[^a-z]/", "",$_REQUEST['action']) ?>" />
					<div class="buyform">
						<div class="one_half row">
							<div class="spacer"></div>
							<div class="spacer"></div>
							<div class="spacer"></div>
							<div class="form-group col-md-4">
								<!-- <label for="token"><?= Lang::string('security-token') ?></label> -->
								<label for="token">Enter Your Token</label>
								<input class="form-control" name="token" id="token" type="text" value="<?= $token1 ?>" />
								<div class="clear"></div>
							</div>
							<div class="form-group col-md-4">
								<label for="token" style="visibility: hidden;">Your Token</label>
								<input type="submit" name="submit" value="<?= Lang::string('security-validate') ?>" class="btn btn-size form-control">
								<? if (User::$info['using_sms'] == 'Y') { ?>
								<input type="submit" name="submit" value="<?= Lang::string('security-resend-sms') ?>" class="btn btn-size form-control">
								<? } ?>
							</div>
							 <div class="mar_top2"></div>
							 <!-- <ul class="list_empty">
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
	           	<!-- <ul class="list_empty">
					<li> -->
					<div class="row">
						<div class="col-md-4">
							<a style="display:block;" href="api-access.php?action=add" class="btn text-black btn-size"><i class="fa fa-plus fa-lg"></i> <?= Lang::string('api-add-new') ?></a>
						</div>
					
					<!-- </li> -->
					<? if ($api_keys) { ?>
					<!-- <li> -->
						<div class="col-md-4">
						<button type="submit" style="display:block;background: #007bff !important;" class="btn btn-size"><?= Lang::string('api-add-save') ?></button>
						
						</div>
						<!-- <input style="display:block;" type="submit" class="btn" value="<?= Lang::string('api-add-save') ?>"  style="background: #007bff !important;"> -->
					<!-- </li> -->
					<? } ?>
				<!-- </ul> -->
				</div>
		    	<div class="info-table-outer">
		    		<table id="info-data-table " class="order-data-table table-list trades table row-border table-hover balance-table" cellspacing="0 " width="100% ">
		    			<thead>
						<tr>
							<th colspan="6"><?= Lang::string('api-keys') ?></th>
						</tr>
						</thead>
						<tbody>
						<? 
						if ($api_keys) {
							foreach ($api_keys as $api_key) {
						?>
						<tr>
							<td class="api-label first"><?= Lang::string('api-key') ?>:</td>
							<td class="api-key" colspan="4"><?= $api_key['key'] ?></td>
							<td style="background-color: #f5f5f5;border-bottom: 1px solid #dee2e6;"><a href="api-access.php?remove_id=<?= $api_key['id'] ?>&action=delete"><i class="fa fa-minus-circle"></i> <?= Lang::string('bank-accounts-remove') ?></a></td>
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
						</tbody>
					</table>
				</div>
			</form>
           	<? } ?>
                       
                        </div>
                    </div>
			
            <!-- <div class="mar_top8"></div> -->
        </div>
	</div>
	<? //include 'includes/sidebar_account.php'; ?>
</div>
</div>
</div>
<?// include 'includes/foot.php'; ?>
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
<script type="text/javascript ">
$(document).ready(function() {
    $('.order-data-table').DataTable();
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
