<?php
// Twitter.php
class Zend_Auth_Adapter_Twitter implements Zend_Auth_Adapter_Interface
{
	/**
	 * The identity value being authenticated
	 * @var string
	 */
	private $_id = null;
	
	/**
	 * Consumer Key
	 * @var string
	 */
	private $_consKey = null;
	
	/**
	 * Consumer Secret
	 * @var string
	 */
	private $_consSecret = null;
	
	/**
	 * Call Back Url
	 * @var string
	 */
	private $_callbackUrl = null;
	
	const TWITTER_OAUTH_SITEURL = 'https://twitter.com/oauth';
	const TWITTER_OAUTH_REQUEST_TOKEN_URL = '/request_token';
	const TWITTER_OAUTH_AUTHORIZE_URL = '/authenticate';
	const TWITTER_OAUTH_ACCESS_TOKEN_URL = '/access_token';
	
	/**
	 * Constructor
	 * @param array $config
	 */
	public function __construct(array  $config)
	{
		if (isset($config['consumerSecret']))
		{
			$this->_consSecret = $config['consumerSecret'];
		}
		if (isset($config['consumerKey']))
		{
			$this->_consKey = $config['consumerKey'];
		}
		if (isset($config['callbackUrl']))
		{
			$this->_callbackUrl = $config['callbackUrl'];
		}
	}
	
	/**
	 * Autheticate 
	 * @return Zend_Auth_Result
	 */
	public function authenticate()
	
	{	
	
		$id = $this->_id;
		$consumer = new Zend_Oauth_Consumer(array(
				'siteUrl' 			=> self::TWITTER_OAUTH_SITEURL,
				'requestTokenUrl'	=> self::TWITTER_OAUTH_SITEURL.self::TWITTER_OAUTH_REQUEST_TOKEN_URL,
				'accessTokenUrl'	=> self::TWITTER_OAUTH_SITEURL.self::TWITTER_OAUTH_ACCESS_TOKEN_URL,
				'authorizeUrl'		=> self::TWITTER_OAUTH_SITEURL.self::TWITTER_OAUTH_AUTHORIZE_URL,
				'consumerKey' 		=> $this->_consKey,
				'consumerSecret'	=> $this->_consSecret
				//'callbackUrl'		=> $this->_callbackUrl
				)
		);
		
		$sess = new Zend_Session_Namespace('zend_auth_twitter');	

		//die('mmmmm');
		
		if (!isset($_GET['oauth_token']) && !$sess->requestToken)
		{ 			
		    $token = $consumer->getRequestToken();
		    $sess->requestToken = serialize($token);
		    $consumer->redirect();	
		}
		else 
		{
			try
			{			
				$token = $consumer->getAccessToken($_GET,
				             unserialize($sess->requestToken));	
				$sess->requestToken = null;
				
				$identity = array(
					'access_token'		 => serialize($token)
				);			
			   return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $identity, array("Authentication successful"));
			}
			catch (Zend_Oauth_Exception $e)
			{	
				return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, $id, array("Authenticaton failed", $e->getMessage()));
			} 		
		}			
	}
	
}
?>
