<?php
session_start();
require_once ('SnowTigerLib.php');
$o = new SnowTigerLib( $_SESSION['token']['oauth_token'] , $_SESSION['token']['oauth_token_secret']  );

// file_put_contents('text_req.txt', print_r($_REQUEST, true));
$access_key = $o->getRequestAccessToken(  $_REQUEST['oauth_verifier'] ) ;
//print_r($_SESSION['access_key']);
 print_r($access_key);
// print_r($_REQUEST);die;
//You can save the access_key to your database,then you can use them at the next time without Authorize again
$_SESSION['access_key'] = $access_key;

// print_r($_SESSION['access_key']);
// die;

//Redirect to any page you want
Header("Location:examples/index.php");
?>