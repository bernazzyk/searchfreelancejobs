<?php
/**
 * THIS IS AN EXAMPLE FOR OAUTH AUTHENTICATION METHOD VIA WEB USING ZEND FRAMEWORK
 */

# See detailed information about Zend_Oauth and 
# Zend Framework at http://framework.zend.com/manual/en/manual.html
# oDesk just provides an example for querying API "as-is"

session_start();

$consumerKey = 'a42d40a24fe99df1466bb781ce13c4ac'; // consumer key, got in console
$consumerSec = '5e722d5383eff298'; // consumer secret, got in console with key
$sigMethod   = 'HMAC-SHA1'; // signature method, e.g. HMAC-SHA1
$callbackUrl = 'http://freelancer.fm/odesk_oauth.php'; // callback url, full url to your script, e.g. http://localhost/oauth.php
$url         = 'https://www.odesk.com/services/api/keys/a42d40a24fe99df1466bb781ce13c4ac'; // api's url, e.g. http://www.odesk.com/api/mc/v1/threads/my_odesk_uid/22222.json (see MC documentation)

$requestTokenUrl        = 'https://www.odesk.com/api/auth/v1/oauth/token/request';
$accessTokenUrl         = 'https://www.odesk.com/api/auth/v1/oauth/token/access';
$userAuthorizationUrl   = 'https://www.odesk.com/services/api/auth';

require_once '/library/Zend/Oauth/Consumer.php';
require_once '/library/Zend/Json.php';

$config = array(
            'version'               => '1.0',
            'callbackUrl'           => $callbackUrl,
            'signatureMethod'       => $sigMethod,
            'requestTokenUrl'       => $requestTokenUrl,
            'accessTokenUrl'        => $accessTokenUrl,
            'userAuthorizationUrl'  => $userAuthorizationUrl,
            'consumerKey'           => $consumerKey,
            'consumerSecret'        => $consumerSec
);

$consumer = new Zend_Oauth_Consumer($config);
 
// Get request token
if (!isset($_SESSION['REQUEST_TOKEN']) && !isset($_SESSION['ACCESS_TOKEN'])) {
    $token = $consumer->getRequestToken();

    $_SESSION['REQUEST_TOKEN'] = serialize($token);

    $consumer->redirect();
}

// Get access token
if (!empty($_GET) && isset($_SESSION['REQUEST_TOKEN'])) {
    $token = $consumer->getAccessToken(
                 $_GET,
                 unserialize($_SESSION['REQUEST_TOKEN'])
             );

    // Serialize and save token
    $_SESSION['ACCESS_TOKEN'] = serialize($token);
    // Now that we have an Access Token, we can discard the Request Token
    $_SESSION['REQUEST_TOKEN'] = null;
}

// Make an example GET request to API
// We configure parameters and Zend_Http_Client manually,
// but you can use your own preferred method and logic
if (!empty($_SESSION['ACCESS_TOKEN'])) {
    $token = unserialize($_SESSION['ACCESS_TOKEN']);
    $t  = $token->getToken();
    $ts = $token->getTokenSecret();

    $params = array(
                'oauth_consumer_key'    => $consumerKey,
                'oauth_signature_method'=> $sigMethod,
                'oauth_timestamp'       => time(),
                'oauth_nonce'           => substr(md5(microtime(true)), 5),
                'oauth_callback'        => $callbackUrl,
                'oauth_token'           => $t
    );

    ksort($params);

    $method = 'GET';
    $secret_key     = $consumerSec . '&' . $ts;
    $params_string  = http_build_query($params);

    $base_string= $method . '&' . urlencode($url) . '&' . urlencode($params_string);
    $signature  = base64_encode(hash_hmac('sha1', $base_string, $secret_key, true));

    $params['oauth_signature'] = $signature;

    $params_string = http_build_query($params);
    $url .= '?' . $params_string;

    $client = new Zend_Http_Client();
    $client->setUri($url);
    $client->setMethod(Zend_Http_Client::GET);
    $response = $client->request();
 
    $data = Zend_Json::decode($response->getBody());
    var_dump($data);
}
?>
