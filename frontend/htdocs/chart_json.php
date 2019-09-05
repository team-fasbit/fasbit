<?php
    // error_reporting(E_ERROR | E_WARNING | E_PARSE);
    // ini_set('display_errors', 1);
$currency_id = $_REQUEST['currency'];
$currency_id1 = $_REQUEST['c_currency'];
$conn = new mysqli("localhost","root","xchange123","bitexchange");

 $sql = "SELECT DISTINCT DATE(A.date) AS 'date' FROM `transactions` A,`currencies` B WHERE A.c_currency = B.id AND A.c_currency = $currency_id1 AND A.currency = $currency_id ORDER BY date ASC";
$my_query1 = mysqli_query($conn,$sql);
$my_query = mysqli_fetch_assoc($my_query1);

$temp = array();
$result_value = array();
while ($row = mysqli_fetch_assoc($my_query1)) {

	$sql1 = "SELECT MIN( A.btc_price ) AS 'lowest', MAX( A.btc_price ) AS 'highest' FROM `transactions` A,`currencies` B WHERE A.c_currency = B.id AND A.c_currency = $currency_id1 AND A.currency = $currency_id AND A.date like '%".$row['date']."%'";

	$my_query11 = mysqli_query($conn,$sql1);
	$temp1 = mysqli_fetch_assoc($my_query11);

	$result_value['lowest'] =  $temp1['lowest'];
	$result_value['highest'] =  $temp1['highest'];
	$result_value['date'] =  $row['date'];
	$temp[] = $result_value;
}

echo '{ "Data" : '.json_encode($temp).'}';

?>
