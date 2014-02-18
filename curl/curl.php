<?php
$login_url = 'http://www.getacoder.com/users/login.php';

//These are the post data username and password
$post_data = 'username=zimbru&passwd=craca95tit';

//Create a curl object
$ch = curl_init();

//Set the useragent
$agent = $_SERVER["HTTP_USER_AGENT"];
curl_setopt($ch, CURLOPT_USERAGENT, $agent);

//Set the URL
curl_setopt($ch, CURLOPT_URL, $login_url );

//This is a POST query
curl_setopt($ch, CURLOPT_POST, 1 );

//Set the post data
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

//We want the content after the query
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//Follow Location redirects
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

/*
Set the cookie storing files
Cookie files are necessary since we are logging and session data needs to be saved
*/

curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');

//Execute the action to login
$postResult = curl_exec($ch);

var_dump($postResult);
?>
