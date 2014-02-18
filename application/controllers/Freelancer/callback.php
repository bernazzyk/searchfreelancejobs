<?php
session_start();
require_once ('SnowTigerLib.php');
$o = new SnowTigerLib( $_SESSION['token']['oauth_token'] , $_SESSION['token']['oauth_token_secret']  );

$access_key = $o->getRequestAccessToken(  $_REQUEST['oauth_verifier'] ) ;

print $access_key; die;

//You can save the access_key to your database,then you can use them at the next time without Authorize again
$_SESSION['access_key'] = $access_key;
//Redirect to any page you want
Header("Location:examples/index.php");
?>