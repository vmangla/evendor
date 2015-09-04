<?php
session_start();
require 'FBConnect.php';
$objFBConnect=new FBConnect();
$objFBConnect->call();
$objFBConnect->clearSession();
header("Location:index.php");
exit;
?>
