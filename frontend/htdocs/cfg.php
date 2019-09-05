<?php
$conn = new mysqli("localhost","brian","mfqda0$&yWpP1n","exchange");
 $cur_sql = "SELECT * FROM currencies WHERE is_active='Y'";
        $currency_query = mysqli_query($conn,$cur_sql);

        $base_ip = "https://fasbit.com/api/";
          // CHECKING REFErral status 
    $ch = curl_init("https://fasbit.com/api/get-settings.php"); 
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);      
    curl_close($ch);
    $ref_response = json_decode($output);
    if ($ref_response->is_referral == 1) {
        $GLOBALS['REFERRAL'] = true;
        $GLOBALS['REFERRAL_BASE_URL'] = $base_ip;
        //$GLOBALS['REFERRAL_BASE_URL'] = $ref_response->base_url;
    }else{
       $GLOBALS['REFERRAL'] = false; 
    }

     // $GLOBALS['REFERRAL'] = false;
?>
