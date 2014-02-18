<?php
$consumerKey = '8f3e8ef823d8a928240d48309f1cf054'; // consumer key, got in console
$consumerSec = '6c21f6884bcbd3cc'; // consumer secret, got in console with key
					
				//$consumerKey = 'a42d40a24fe99df1466bb781ce13c4ac'; // consumer key, got in console
				//$consumerSec = '5e722d5383eff298'; // consumer secret, got in console with key
$sigMethod   = 'HMAC-SHA1'; // signature method, e.g. HMAC-SHA1
$callbackUrl = SITE_URL . 'auth/odesk/'; // callback url, full url to your script, e.g. http://localhost/oauth.php

$requestTokenUrl        = 'https://www.odesk.com/api/auth/v1/oauth/token/request';
$accessTokenUrl         = 'https://www.odesk.com/api/auth/v1/oauth/token/access';
$userAuthorizationUrl   = 'https://www.odesk.com/services/api/auth';
?>