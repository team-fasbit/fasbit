
<?php
//     error_reporting(E_ERROR | E_WARNING | E_PARSE);
// ini_set('display_errors', 1);
//     include '../lib/common.php';
//     require_once ("cfg.php");

API::add('User','getRegistereKYCReferal',array($_SESSION['session_id']));
$query = API::send();

$referal_email_id1 = $query['User']['getRegistereKYCReferal']['results'][0]['email'];
$REFERRAL_BASE_URL = "http://18.223.166.16/api/";
$name = User::$info['first_name'].' '.User::$info['last_name'];
            $url = $REFERRAL_BASE_URL."referal_user_details.php?name=1";
            $fields = array(
                'user_id' => $referal_email_id1,
            );
            $referal_email_id = "v4syokesh@gmail.com";
        
            $fields1 = json_encode($referal_email_id);
            	 $base_ip = "http://18.223.166.16/api/";
          // CHECKING REFErral status 
    $ch = curl_init("http://18.223.166.16/api/referal_user_details.php?id=$referal_email_id1"); 
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);      
    curl_close($ch);
    $ref_response = json_decode($output);
            // print_r($ref_response);
?>