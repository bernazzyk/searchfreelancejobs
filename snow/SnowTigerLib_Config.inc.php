<?php
/*
WARNING: Modify the file must be saved as no BOM header file
*/

//Set the data environment
//true: sandbox environment ;false: formal environment
$apiConfig['TestMode'] = false;//true;

//Your Consumer Token
$apiConfig['ConsumerToken'] = 'e2cd52f6417d0c0dcf6d7a81b6120132086e44ec';
//$apiConfig['ConsumerToken'] = '209dda8981fd6a37be106cbee02cc9d4bec42ce4';
//Your Consumer Secret
$apiConfig['ConsumerSecret'] = 'db1e5f8c5dde1208ae154e62dec238db6a9a4219';
//$apiConfig['ConsumerSecret'] = 'b847fab7e91ffe4f7fe8c317017ae45c226deb73';
//Callback url
$apiConfig['CallBack'] = 'http://freelancer.fm/snow/callback.php?';

//result format xml/json
//$apiConfig['Format'] = 'xml';
$apiConfig['Format'] = 'json';

//Set the signature method,only support hmac now
$apiConfig['SignMethod'] = 'hmac';

//Turn on or off the error tips
//True: turn off ;False: turn on
$apiConfig['CloseError'] = false;

//Turn on or off the API call logs
//True: turn on ;False: turn off
$apiConfig['ApiLog'] = true;

//Turn on or off the error logs
//True: turn on ;False: turn off
$apiConfig['Errorlog'] = true;

//Set the number of retries when failed to call the API
//This can improve the stability of API
$apiConfig['RestNumberic'] = 3;

/***************************************
 *http connection config
 **************************************/
// Set connect timeout
$apiConfig['connecttimeout'] = 30;
// Set timeout default
$apiConfig['timeout'] = 30;
//Set the useragnet
$apiConfig['useragent'] = 'SnowTigerLib v0.1';

return $apiConfig;