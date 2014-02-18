<?php
 
require_once('elance-auth-lib.php');
 
error_reporting(E_ALL);
 
$elance_auth = new ElanceAuthentication();
$url = $elance_auth->RequestAccessCode("511d7902e4b0c1666cb1238f", "http://www.freelancer.fm/zelance/callback.php");
 
header("Location: " . $url);
 
?>