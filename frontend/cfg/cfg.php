<?php
class object {}
$CFG = new object();
if ($_SERVER['SERVER_NAME'] == 'localhost') {
	$base_url = "http://localhost/bit_live/";
	$CFG->api_url = $base_url.'api/htdocs/api.php';
	$CFG->auth_login_url = $base_url.'auth/htdocs/login.php';
	$CFG->auth_verify_token_url = $base_url.'auth/htdocs/verify_token.php';
} else {
	$base_url = "http://provenance.services/";
$CFG->api_url = 'http://api.provenance.services/api.php';
$CFG->auth_login_url = 'http://auth.provenance.services/login.php';
$CFG->auth_verify_token_url = 'http://auth.provenance.services/verify_token.php';
}


?>
