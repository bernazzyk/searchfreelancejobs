<?php
/**
 *
 * See an example at http://im.chregu.tv/oauth.php
 *
 * USAGE
 *
 * Register and get the oauth keys from http://twitter.com/oauth_clients
 *

define("TWITTER_CONSUMER_KEY", "XeX49bfZt39rUBIPTGJLA");
define("TWITTER_CONSUMER_SECRET", "jcG5P9JSMaVC5D8TLH5b7WcHz2WXiIXxpIfSG14");


$auth = Zend_Auth::getInstance();
$sess = new Zend_Session_Namespace('zend_auth_twitter');

if (! $auth->hasIdentity()) {
    $ada = new Zend_Auth_Adapter_Twitter(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
    $result = $auth->authenticate($ada);
    $sess->screenname = $result->getScreenname();
}

print "Hello " . $sess->screenname . " with twitter id " . $auth->getIdentity();

 *
 */
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Auth
 * @subpackage Zend_Auth_Adapter
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * @see Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';
/**
 * A Zend_Auth Authentication Adapter allowing the use of twitter as an
 * authentication mechanism
 *
 * @category   Zend
 * @package    Zend_Auth
 * @subpackage Zend_Auth_Adapter
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Auth_Adapter_Twitter implements Zend_Auth_Adapter_Interface
{
    /**
     * The identity value being authenticated
     *
     * @var string
     */
    private $_id = null;
    private $_conskey = 'Qk2UG6kOCoWFVvxre6St2w';
    private $_conssec = 'ZbsOVnrTb4N2LlO6PiSSnSj3K5EXCYQQ4GeZG3xRy8';
    const TWITTER_OAUTH_HOST = "https://twitter.com";
    const TWITTER_REQUEST_TOKEN_URL = "/oauth/request_token";
    const TWITTER_AUTHORIZE_URL = "/oauth/authenticate";
    const TWITTER_ACCESS_TOKEN_URL = "/oauth/access_token";
    /**
     * Constructor
     *
     * @param Zend_Controller_Response_Abstract $response an optional response
     *        object to perform HTTP or HTML form redirection
     * @return void
     */
    public function __construct ($conskey, $conssec)
    {
        $this->_conskey = $conskey;
        $this->_conssec = $conssec;
    }
    /**
     * Sets the value to be used as the identity
     *
     * @param  string $id the identity value
     * @return Zend_Auth_Adapter_twitter Provides a fluent interface
     */
    public function setIdentity ($id)
    {
        $this->_id = $id;
        return $this;
    }
    /**
     * Authenticates the given twitter identity.
     * Defined by Zend_Auth_Adapter_Interface.
     *
     * @throws Zend_Auth_Adapter_Exception If answering the authentication query is impossible
     * @return Zend_Auth_Result
     */
    public function authenticate ()
    {
        $req_url = self::TWITTER_OAUTH_HOST . self::TWITTER_REQUEST_TOKEN_URL;
        $authurl = self::TWITTER_OAUTH_HOST . self::TWITTER_AUTHORIZE_URL;
        $acc_url = self::TWITTER_OAUTH_HOST . self::TWITTER_ACCESS_TOKEN_URL;
        $id = $this->_id;
        $oauth = new OAuth($this->_conskey, $this->_conssec, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
        try {
            $oauth->enableDebug();
            $sess = new Zend_Session_Namespace('zend_auth_twitter');
            if (! isset($_GET['oauth_token']) && ! $sess->secret) {
				die('asd');
                $request_token_info = $oauth->getRequestToken($req_url);
                $sess->secret = $request_token_info['oauth_token_secret'];
                                error_log("fooo " . $sess->secret);

                header('Location: ' . $authurl . '?oauth_token=' . $request_token_info['oauth_token']);
                die();
            } else {
                $oauth->setToken($_GET['oauth_token'], $sess->secret);
                $access_token_info = $oauth->getAccessToken($acc_url);

                $sess->secret = null;
                $lastresponse = $oauth->getLastResponse();
                parse_str($lastresponse, $get);
				
				var_dump($get);
				die('here');
				
                if (isset($get['user_id'])) {
                    return new Zend_Auth_Result_Twitter(Zend_Auth_Result::SUCCESS, $get, array("Authentication successful"));
                } else {
                    return new Zend_Auth_Result_Twitter(Zend_Auth_Result::FAILURE, $get, array("Authentication failed" , $oauth->getLastResponse()));
                }
            }
        } catch (Exception $e) {
            $sess->secret = $request_token_info['oauth_token_secret'];
            return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, $id, array("Authentication failed" , $oauth->getLastResponse()));
        }
    }
}
