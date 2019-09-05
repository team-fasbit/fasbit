<?php
class object {}
$CFG = new object();
if ($_SERVER['SERVER_NAME'] == 'localhost') {
	$CFG->dbhost = "localhost";
	$CFG->dbname = "bitexchange";
	$CFG->dbuser = "root";
	$CFG->dbpass = "xchange123";
} else {
	$CFG->dbhost = "localhost";
$CFG->dbname = "bitexchange";
$CFG->dbuser = "root";
$CFG->dbpass = "xchange123";
}


?>
