<?php
 
class ElanceAuthentication {
    public $CurlHeaders;
    public $ResponseCode;
 
    private $_AuthorizeUrl = "https://api.elance.com/api2/oauth/authorize";
    private $_AccessTokenUrl = "https://api.elance.com/api2/oauth/token";
 
    public function __construct() {
        $this->CurlHeaders = array();
        $this->ResponseCode = 0;
    }
 
    public function RequestAccessCode ($client_id, $redirect_url) {
        return($this->_AuthorizeUrl . "?client_id=" . $client_id . "&response_type=code&redirect_uri=" . $redirect_url);
    }
 
    // Convert an authorization code from an Elance callback into an access token.
    public function GetAccessToken($client_id, $client_secret, $auth_code) {        
        // Init cUrl.
        $r = $this->InitCurl($this->_AccessTokenUrl);
 
        // Add client ID and client secret to the headers.
        curl_setopt($r, CURLOPT_HTTPHEADER, array (
            "Authorization: Basic " . base64_encode($client_id . ":" . $client_secret),
        ));        
 
        // Assemble POST parameters for the request.
        $post_fields = "code=" . urlencode($auth_code) . "&grant_type=authorization_code";
 
        // Obtain and return the access token from the response.
        curl_setopt($r, CURLOPT_POST, true);
        curl_setopt($r, CURLOPT_POSTFIELDS, $post_fields);
 
        $response = curl_exec($r);
        if ($response == false) {
            die("curl_exec() failed. Error: " . curl_error($r));
        }
 
        //Parse JSON return object.
        return json_decode($response);
    }
 
    private function InitCurl($url) {
        $r = null;
 
        if (($r = @curl_init($url)) == false) {
            header("HTTP/1.1 500", true, 500);
            die("Cannot initialize cUrl session. Is cUrl enabled for your PHP installation?");
        }
 
        curl_setopt($r, CURLOPT_RETURNTRANSFER, 1);
 
        // Decode compressed responses.
        curl_setopt($r, CURLOPT_ENCODING, 1);
 
        // NOTE: If testing locally, add the following lines to use a dummy certificate, and to prevent cUrl from attempting to verify
        // the certificate's authenticity. See http://richardwarrender.com/2007/05/the-secret-to-curl-in-php-on-windows/ for more
        // details on this workaround. If your server has a valid SSL certificate installed, comment out these lines.
        curl_setopt($r, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($r, CURLOPT_CAINFO, "C:\wamp\bin\apache\Apache2.2.21\cacert.crt");
 
        // NOTE: For Fiddler2 debugging.
        //curl_setopt($r, CURLOPT_PROXY, '127.0.0.1:8888');
 
        return($r);
    }
 
    // A generic function that executes an Elance API request. 
    public function ExecRequest($url, $access_token, $get_params) {
        // Create request string.
        $full_url = http_build_query($url, $get_params);
 
        $r = $this->InitCurl($url);
 
        curl_setopt($r, CURLOPT_HTTPHEADER, array (
            "Authorization: Basic " . base64_encode($access_token)
        ));
 
        $response = curl_exec($r);
        if ($response == false) {
            die("curl_exec() failed. Error: " . curl_error($r));
        }
 
        //Parse JSON return object.
        return json_decode($response);        
    }
}
 
?>