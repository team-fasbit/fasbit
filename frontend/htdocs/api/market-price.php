<?php
$uri = $_SERVER['REQUEST_URI'];
$host = explode('api/',$uri);

$url = $host[1];

$params=explode('?',$url);

$endpoint=$params[0];

$market_input=explode('-',$params[1]);


include '/var/www/html/frontend/lib/common.php';

include '/var/www/html/frontend/htdocs/api/index.php';
?>