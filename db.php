<?php

$db = new mysqli('localhost', 'login', 'pass', 'phpmyadmin');

date_default_timezone_set('Asia/Yekaterinburg');
$date = isset($_REQUEST['date'])?strtotime($_REQUEST['date']):time();
$date = date("Y-m-d H:i:s", $date);
$pr = 1;
?>