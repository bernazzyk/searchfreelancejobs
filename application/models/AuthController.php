<?php

class AuthController extends Zend_Controller_Action
{
    
		public $cookie_file = ''; //dirname(__FILE__).'/cookies/44cookie.txt';
		public $follow_redirects = true;
		public $headers = array();
		public $options = array();
		public $referer;
		public $user_agent;
		protected $error = '';
		protected $request;
		
		public function elanceAction() {
			Zend_Session::start(); 
			require_once('ElanceExtraction/elance-auth-lib.php');
			$auth = Zend_Auth::getInstance();
			$authStorage = $auth->getStorage();
			$user_id = (int) $authStorage->read();
			$platform_id = 3;
			if($user_id!=0)
			{
				$modelFromGeneral = new Application_Model_General();
				$UserPlatformData = $modelFromGeneral->getUserPlatformData($user_id,$platform_id);
				if(isset($UserPlatformData['access_token']))
				{
					$_SESSION['connected_platform'][$platform_id]['access_token'] = $UserPlatformData['access_token'];
					$url = SITE_URL . "auth/callbackelance";
				}
				else
				{
					error_reporting(E_ALL);
					 
					$elance_auth = new ElanceAuthentication();
					$url = $elance_auth->RequestAccessCode("4f21faa83340a00328000001", SITE_URL . "auth/callbackelance");
					//header("Location: " . $url);
				}
				 $this->_redirect($url); //sau...
			}
		}
		
		public function callbackelanceAction()
		{
			Zend_Session::start();
			$platform_id = 3;
			$auth = Zend_Auth::getInstance();
			$authStorage = $auth->getStorage();
			$user_id = (int) $authStorage->read();
			
			require_once('ElanceExtraction/elance-auth-lib.php');
			if(isset($_SESSION['connected_platform'][$platform_id]['access_token']))
			{
				$acces_token = $_SESSION['connected_platform'][$platform_id]['access_token'];
				//print $acces_token; die;
			}
			else {
		
				$curent_url =  $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
				parse_str(parse_url($curent_url, PHP_URL_QUERY), $_MY_GET);
	 
				if (!isset($_MY_GET["code"])) {
					die("Require the code parameter to validate!");
				}
				 
				$code = $_MY_GET["code"];
				$elance_auth = new ElanceAuthentication();
				$json = $elance_auth->GetAccessToken("4f21faa83340a00328000001", "tYL5A3ymBkl0zjwokx4BjA", $code);
				 
				$acces_token = $json->data->access_token;
				$_SESSION['connected_platform'][$platform_id]['access_token'] = $acces_token;
				
				
				$modelFromGeneral = new Application_Model_General();
				//$modelFromGeneral->ConnectUserPlatform($user_id,$platform_id);
				$modelFromGeneral->insertApiPlatformUser($platform_id, $user_id, $_SESSION['connected_platform'][$platform_id]['access_token']);
				
			}
			
			//Output code
			$url = 'https://api.elance.com/api2/profiles/my?access_token='.$acces_token;
			$modelFromGeneral = new Application_Model_General();
			$_SESSION['connected_platform'][$platform_id]['is_connected']=1;
			//$_SESSION['profile']['elance'] = $modelFromGeneral->cURLExtractJSONContent($url);
		
			header("Location: " . '/auth/closewindowopener/');
			die;
		}
			
		public function freelancerAction()
		{	
			Zend_Session::start(); 
			require_once ('Freelancer/SnowTigerLib.php');
			
			$platform_id = 1;
			
			$auth = Zend_Auth::getInstance();
			$authStorage = $auth->getStorage();
			
			$user_id = (int) $authStorage->read();
			
			if($user_id!=0)
			{
				$curent_url =  $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
				parse_str(parse_url($curent_url, PHP_URL_QUERY), $_MY_GET);
				
				//   unset($_SESSION['access_key']);
				if (!isset ($_SESSION['connected_platform'][$platform_id]['access_token']) && !isset ($_SESSION['connected_platform'][$platform_id]['access_token_secret']) && !isset($_MY_GET['token'])) {
					$stl = new SnowTigerLib();
					$token = $stl->getRequestToken();
					$_SESSION['api_keys'][$platform_id]['token'] = $token;
					
					echo '<a href="'.$stl->getAuthorizeURL().'">Authorize with Freelancer.com</a>';
					die;
				}else{
					if(isset($_MY_GET['token'])){
						$_SESSION['connected_platform'][$platform_id]['access_token'] = $_MY_GET['token'];
						$_SESSION['connected_platform'][$platform_id]['access_token_secret'] = $_MY_GET['secret'];
					}
					
					if(isset($_SESSION['connected_platform'][$platform_id]['access_token']) &&
						isset($_SESSION['connected_platform'][$platform_id]['access_token_secret']) &&
						($_SESSION['connected_platform'][$platform_id]['access_token'] !='') &&
						($_SESSION['connected_platform'][$platform_id]['access_token_secret']) !=''
					)
					{
						$modelFromGeneral = new Application_Model_General();
						$modelFromGeneral->insertApiPlatformUser($platform_id, $user_id,$_SESSION['connected_platform'][$platform_id]['access_token'],$_SESSION['connected_platform'][$platform_id]['access_token_secret']);
						$_SESSION['connected_platform'][$platform_id]['is_connected']=1;
						header("Location: " . '/auth/closewindowopener/');
						die;
					}
				}
			}
		}
		
		public function freelancercallbackAction()
		{
			Zend_Session::start();
			$platform_id = 1;
			
			$auth = Zend_Auth::getInstance();
			$authStorage = $auth->getStorage();
			$user_id = (int) $authStorage->read();
			
			if($user_id!=0)
			{
				$curent_url =  $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
				parse_str(parse_url($curent_url, PHP_URL_QUERY), $_MY_GET);
				
				require_once ('Freelancer/SnowTigerLib.php');
				$o = new SnowTigerLib( $_SESSION['api_keys'][$platform_id]['token']['oauth_token'] , $_SESSION['api_keys'][$platform_id]['token']['oauth_token_secret']  );
				$access_key = $o->getRequestAccessToken(  $_MY_GET['oauth_verifier'] ) ;
				
				
				
				//You can save the access_key to your database,then you can use them at the next time without Authorize again
				
				$_SESSION['connected_platform'][$platform_id]['access_token'] = $access_key['oauth_token'];
				$_SESSION['connected_platform'][$platform_id]['access_token_secret'] = $access_key['oauth_token_secret'];
				
				//Redirect to any page you want
				header("Location: " . '/auth/freelancer/');
				die;
			}
			//Header("Location:examples/index.php");
		}
		
		public function odeskAction()
		{
 			Zend_Session::start(); 

			$auth = Zend_Auth::getInstance();
			$authStorage = $auth->getStorage();
			$user_id = (int) $authStorage->read();
			$platform_id = 4;
			if($user_id!=0)
			{
				require_once('Odesk/OdeskConfig.php');
				
				$modelFromGeneral = new Application_Model_General();
				$UserPlatformData = $modelFromGeneral->getUserPlatformData($user_id,$platform_id);
				if(isset($UserPlatformData['access_token']) && isset($UserPlatformData['access_token_secret']))
				{
					$_SESSION['connected_platform'][$platform_id]['access_token'] = $UserPlatformData['access_token'];
					$_SESSION['connected_platform'][$platform_id]['access_token_secret'] = $UserPlatformData['access_token_secret'];
				}
				else 
				{
					
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
					if (!isset($_SESSION['api_keys'][$platform_id]['ODESK_REQUEST_TOKEN']) && !isset($_SESSION['api_keys'][$platform_id]['ODESK_ACCESS_TOKEN'])) {
						$token = $consumer->getRequestToken();
			
						$_SESSION['api_keys'][$platform_id]['ODESK_REQUEST_TOKEN'] = serialize($token);
						$consumer->redirect();
					}
					
				
					$curent_url =  $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
					parse_str(parse_url($curent_url, PHP_URL_QUERY), $_MY_GET);
										
			
					//print_r($_SESSION['api_keys'][$platform_id]['ODESK_REQUEST_TOKEN']);
					
					// Get access token
					if (!empty($_MY_GET) && isset($_SESSION['api_keys'][$platform_id]['ODESK_REQUEST_TOKEN'])) {
					
						$token = $consumer->getAccessToken(
									 $_MY_GET,
									 unserialize($_SESSION['api_keys'][$platform_id]['ODESK_REQUEST_TOKEN'])
								 );
								 
						// Serialize and save token
						$_SESSION['api_keys'][$platform_id]['ODESK_ACCESS_TOKEN'] = serialize($token);
						// Now that we have an Access Token, we can discard the Request Token
						$_SESSION['api_keys'][$platform_id]['ODESK_REQUEST_TOKEN'] = null;	
					}
					
					// Make an example GET request to API
					// We configure parameters and Zend_Http_Client manually,
					// but you can use your own preferred method and logic
					if (!empty($_SESSION['api_keys'][$platform_id]['ODESK_ACCESS_TOKEN'])) {
						
						//It seems that this does mean that we have already login into odesk account
						
						$token = unserialize($_SESSION['api_keys'][$platform_id]['ODESK_ACCESS_TOKEN']);
						
						/*print '++'.$_SESSION['api_keys'][$platform_id]['ODESK_ACCESS_TOKEN'];
						print '---'.$token;
						print_r($token);
						var_dump($token);
						die('here');*/
						
						$t  = $token->getToken();
						$ts = $token->getTokenSecret();
						
						
						//$_SESSION['connected_platform'][$platform_id]['access_token'] = $t;
						//$_SESSION['connected_platform'][$platform_id]['access_token_secret'] = $ts;
						
						$_SESSION['connected_platform'][$platform_id]['access_token'] = $t;
						$_SESSION['connected_platform'][$platform_id]['access_token_secret'] = $ts;
						
						$modelFromGeneral = new Application_Model_General();
						//$modelFromGeneral->ConnectUserPlatform($user_id,$platform_id);
						$modelFromGeneral->insertApiPlatformUser($platform_id, $user_id,$_SESSION['connected_platform'][$platform_id]['access_token'],$_SESSION['connected_platform'][$platform_id]['access_token_secret']);
						
					}
				}
				
				if(isset($_SESSION['connected_platform'][$platform_id]['access_token']) && isset($_SESSION['connected_platform'][$platform_id]['access_token_secret']))
				{					
					/*
					$t = $_SESSION['connected_platform'][$platform_id]['access_token'];
					$ts = $_SESSION['connected_platform'][$platform_id]['access_token_secret'];
						
					$secret_key     = $consumerSec . '&' . $ts;
				
					$params = array(
					'oauth_consumer_key'    => $consumerKey,
					'oauth_signature_method'=> $sigMethod,
					'oauth_timestamp'       => time(),
					'oauth_nonce'           => substr(md5(microtime(true)), 5),
				 //   'oauth_callback'        => $callbackUrl,
					'oauth_token'           => $t
					);

					ksort($params);

					$method = 'GET';
				
				
				$params_string  = http_build_query($params);

				$url = 'https://www.odesk.com/api/auth/v1/info.json';
				$base_string= $method . '&' . urlencode($url) . '&' . urlencode($params_string);
				$signature  = base64_encode(hash_hmac('sha1', $base_string, $secret_key, true));

				$params['oauth_signature'] = $signature;

				$params_string = http_build_query($params);
				
				$url .= '?' . $params_string;
				$modelFromGeneral = new Application_Model_General();
				$_SESSION['profile']['odesk'] = $modelFromGeneral->cURLExtractJSONContent($url);*/
				$_SESSION['connected_platform'][$platform_id]['is_connected'] = 1;
				
				header("Location: " . '/auth/closewindowopener/');
				die;					
			}
			}
		}
		
		/*
		public function testAction()
		{
			$this->connectToPlatform('zimbru','craca95tit',5, 44);
			die;
		}*/
			
		public function connectToPlatform($username,$password,$platform, $user_id)
		{	
			Zend_Session::start();
			$connected_success = false;
			$Curl_Obj = curl_init();
			//$user_id = (int)$_SESSION['Zend_Auth']['storage'];
			$cookieFile = $this->getCookieFile($user_id);
			
			$userAgent = $_SERVER['HTTP_USER_AGENT'];
			curl_setopt($Curl_Obj, CURLOPT_USERAGENT, $userAgent);
			curl_setopt ($Curl_Obj, CURLOPT_COOKIEFILE, $cookieFile); 
			curl_setopt ($Curl_Obj, CURLOPT_COOKIEJAR, $cookieFile); 
			curl_setopt ($Curl_Obj, CURLOPT_HEADER, 1);
			//curl_setopt ($Curl_Obj, CURLOPT_NOBODY, 1);
			curl_setopt ($Curl_Obj, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($Curl_Obj, CURLOPT_POST, 1);
			if($platform==6)
			{
				$login_url = 'http://www.getacoder.com/users/onlogin.php';
				$post_data = 'username='.$username.'&passwd='.$password;
			} else if($platform==10) {
				$login_url = 'https://secure.freelance.com/j_acegi_security_check';
				$post_data = 'j_username='.$username.'&j_password='.$password;
			} else if($platform==7) {
				$login_url = 'http://jobs.freelanceswitch.com/session';
				$post_data = 'login='.$username.'&password='.$password.'&return_to=&commit=Log+In';
			}
			else if($platform==9) {
				$login_url = 'https://www.ifreelance.com/user/login.aspx';
				$post_data = '__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwULLTE0NjE1NjE1NDAPFgIeFFJlZmVycmVyUGF0aEFuZFF1ZXJ5BQEvFgJmD2QWAgIDD2QWDAIDDxYCHgdWaXNpYmxlaGQCBA8WAh4LXyFJdGVtQ291bnQCBRYKZg9kFgJmDxUDIS9maW5kL3Byb2plY3RzL2Jyb3dzZS5hc3B4P3NjPTI4OQ5XZWJzaXRlIERlc2lnbgIxM2QCAQ9kFgJmDxUDIS9maW5kL3Byb2plY3RzL2Jyb3dzZS5hc3B4P3NjPTI1MhNXZWJzaXRlIFByb2dyYW1taW5nAjExZAICD2QWAmYPFQMhL2ZpbmQvcHJvamVjdHMvYnJvd3NlLmFzcHg%2Fc2M9MTM2IEludGVybmV0IE1hcmtldGluZyAvIEFkdmVydGlzaW5nATVkAgMPZBYCZg8VAyEvZmluZC9wcm9qZWN0cy9icm93c2UuYXNweD9zYz0yNTcXT3RoZXIgV3JpdGluZyAvIEVkaXRpbmcBNGQCBA9kFgJmDxUDIS9maW5kL3Byb2plY3RzL2Jyb3dzZS5hc3B4P3NjPTI2ORlBcnRpY2xlIFdyaXRpbmcgLyBFZGl0aW5nATRkAgcPFgIfAWhkAgkPFgIeBGhyZWYFFC9idXllci9yZWdpc3Rlci5hc3B4ZAIKDxYCHwMFKmh0dHBzOi8vd3d3LmlmcmVlbGFuY2UuY29tL3VzZXIvbG9naW4uYXNweGQCCw8WAh8BaBYGZg8WAh8DBTFodHRwOi8vd3d3LmlmcmVlbGFuY2UuY29tL215L21lc3NhZ2VzL2FsZXJ0cy5hc3B4ZAIBDxYCHwFoZAICDxYCHwFoZBgBBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WAgU8Y3RsMDAkbWFpbkJsb2NrQ29udGVudFBsYWNlSG9sZGVyJGxpTG9naW4kcmVtZW1iZXJNZUNoZWNrQm94BTJjdGwwMCRtYWluQmxvY2tDb250ZW50UGxhY2VIb2xkZXIkbGlMb2dpbiRidG5Mb2dpbg%3D%3D&ctl00%24mainBlockContentPlaceHolder%24liLogin%24txtLoginName='.$username.'&ctl00%24mainBlockContentPlaceHolder%24liLogin%24txtPassword='.$password.'&ctl00%24mainBlockContentPlaceHolder%24liLogin%24rememberMeCheckBox=on&ctl00%24mainBlockContentPlaceHolder%24liLogin%24btnLogin.x=31&ctl00%24mainBlockContentPlaceHolder%24liLogin%24btnLogin.y=6&ctl00%24searchSelectBox=1';
			}
			else if($platform==5) {
				$login_url = 'https://www.guru.com/login.aspx';
				//$post_data = 'scriptMgr_HiddenField=&__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwULLTE0NDI5OTY0MjAPZBYEAgMPFgIeBFRleHQFuQQ8IS0tIEdvb2dsZSBUYWcgTWFuYWdlciAtLT4NCjxub3NjcmlwdD48aWZyYW1lIHNyYz0iLy93d3cuZ29vZ2xldGFnbWFuYWdlci5jb20vbnMuaHRtbD9pZD1HVE0tTEYyMiINCmhlaWdodD0iMCIgd2lkdGg9IjAiIHN0eWxlPSJkaXNwbGF5Om5vbmU7dmlzaWJpbGl0eTpoaWRkZW4iPjwvaWZyYW1lPjwvbm9zY3JpcHQ%2BDQo8c2NyaXB0PihmdW5jdGlvbih3LGQscyxsLGkpe3dbbF09d1tsXXx8W107d1tsXS5wdXNoKHsnZ3RtLnN0YXJ0JzoNCm5ldyBEYXRlKCkuZ2V0VGltZSgpLGV2ZW50OidndG0uanMnfSk7dmFyIGY9ZC5nZXRFbGVtZW50c0J5VGFnTmFtZShzKVswXSwNCmo9ZC5jcmVhdGVFbGVtZW50KHMpLGRsPWwhPSdkYXRhTGF5ZXInPycmbD0nK2w6Jyc7ai5hc3luYz10cnVlO2ouc3JjPQ0KJy8vd3d3Lmdvb2dsZXRhZ21hbmFnZXIuY29tL2d0bS5qcz9pZD0nK2krZGw7Zi5wYXJlbnROb2RlLmluc2VydEJlZm9yZShqLGYpOw0KfSkod2luZG93LGRvY3VtZW50LCdzY3JpcHQnLCdkYXRhTGF5ZXInLCdHVE0tTEYyMicpOzwvc2NyaXB0Pg0KPCEtLSBFbmQgR29vZ2xlIFRhZyBNYW5hZ2VyIC0tPmQCBw8WAh8ABdwEPHNjcmlwdCB0eXBlPSJ0ZXh0L2phdmFzY3JpcHQiPgogdmFyIGdhSnNIb3N0ID0gKCgiaHR0cHM6IiA9PSBkb2N1bWVudC5sb2NhdGlvbi5wcm90b2NvbCkgPyAiaHR0cHM6Ly9zc2wuIiA6ICJodHRwOi8vd3d3LiIpOwogZG9jdW1lbnQud3JpdGUodW5lc2NhcGUoIiUzQ3NjcmlwdCBzcmM9JyIgKyBnYUpzSG9zdCArICJnb29nbGUtYW5hbHl0aWNzLmNvbS9nYS5qcycgdHlwZT0ndGV4dC9qYXZhc2NyaXB0JyUzRSUzQy9zY3JpcHQlM0UiKSk7CiA8L3NjcmlwdD4KIDxzY3JpcHQgdHlwZT0idGV4dC9qYXZhc2NyaXB0Ij4KdHJ5ewogIHZhciBwYWdlVHJhY2tlciA9IF9nYXQuX2dldFRyYWNrZXIoIlVBLTQzMzY4OS00Iik7CiAgIHBhZ2VUcmFja2VyLl9zZXREb21haW5OYW1lKCIuZ3VydS5jb20iKTsKICBwYWdlVHJhY2tlci5fdHJhY2tQYWdldmlldygpOwogfSBjYXRjaChlcnIpe30KIGZ1bmN0aW9uIGdvb2dsZVNpdGVTZWFyY2gocGFybXMpe3RyeXt2YXIgcGFnZVRyYWNrZXIgPSBfZ2F0Ll9nZXRUcmFja2VyKCJVQS00MzM2ODktNCIpO3BhZ2VUcmFja2VyLl90cmFja1BhZ2V2aWV3KCIvc2VhcmNoPyIrcGFybXMpO30gY2F0Y2goZXJyKXt9fTwvc2NyaXB0PmRkG%2F04%2FQOTUFoJbKJavc7EfUH8o28%3D&__EVENTVALIDATION=%2FwEWBQKqxMb%2FDgLW3fTKCAL%2FyfnfBwKGyp6GDgKX0ebqC1384HZag%2BkKmLmhZ0w040qs4aPt&scriptMgr=&ucLogin%24txtUserName%24txtUserName_TextBox='.$username.'&ucLogin%24txtPassword%24txtPassword_TextBox='.$password.'&btnLoginAccount%24btnLoginAccount_Button=Sign+in&hdnGuid=GUID';
				
			//	$post_data = 'scriptMgr_HiddenField=&__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUKLTg2MzEwMzM4Nw9kFgICAw8WAh4EVGV4dAW5BDwhLS0gR29vZ2xlIFRhZyBNYW5hZ2VyIC0tPg0KPG5vc2NyaXB0PjxpZnJhbWUgc3JjPSIvL3d3dy5nb29nbGV0YWdtYW5hZ2VyLmNvbS9ucy5odG1sP2lkPUdUTS1MRjIyIg0KaGVpZ2h0PSIwIiB3aWR0aD0iMCIgc3R5bGU9ImRpc3BsYXk6bm9uZTt2aXNpYmlsaXR5OmhpZGRlbiI%2BPC9pZnJhbWU%2BPC9ub3NjcmlwdD4NCjxzY3JpcHQ%2BKGZ1bmN0aW9uKHcsZCxzLGwsaSl7d1tsXT13W2xdfHxbXTt3W2xdLnB1c2goeydndG0uc3RhcnQnOg0KbmV3IERhdGUoKS5nZXRUaW1lKCksZXZlbnQ6J2d0bS5qcyd9KTt2YXIgZj1kLmdldEVsZW1lbnRzQnlUYWdOYW1lKHMpWzBdLA0Kaj1kLmNyZWF0ZUVsZW1lbnQocyksZGw9bCE9J2RhdGFMYXllcic%2FJyZsPScrbDonJztqLmFzeW5jPXRydWU7ai5zcmM9DQonLy93d3cuZ29vZ2xldGFnbWFuYWdlci5jb20vZ3RtLmpzP2lkPScraStkbDtmLnBhcmVudE5vZGUuaW5zZXJ0QmVmb3JlKGosZik7DQp9KSh3aW5kb3csZG9jdW1lbnQsJ3NjcmlwdCcsJ2RhdGFMYXllcicsJ0dUTS1MRjIyJyk7PC9zY3JpcHQ%2BDQo8IS0tIEVuZCBHb29nbGUgVGFnIE1hbmFnZXIgLS0%2BZGTEvZJMBaDQTuYZNi2b%2BZTWAdUHDw%3D%3D&__EVENTVALIDATION=%2FwEWBQK%2BrNvoBwLW3fTKCAL%2FyfnfBwKGyp6GDgKX0ebqC0WW7GcvKbKyjAOby%2FugKuooJj1a&scriptMgr=&ucLogin%24txtUserName%24txtUserName_TextBox=Consultingonline&ucLogin%24txtPassword%24txtPassword_TextBox=165c3660&btnLoginAccount%24btnLoginAccount_Button=Sign+in&hdnGuid=GUID';
				$post_data = 'scriptMgr_HiddenField=&__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUKLTg2MzEwMzM4Nw9kFgICAw8WAh4EVGV4dAW5BDwhLS0gR29vZ2xlIFRhZyBNYW5hZ2VyIC0tPg0KPG5vc2NyaXB0PjxpZnJhbWUgc3JjPSIvL3d3dy5nb29nbGV0YWdtYW5hZ2VyLmNvbS9ucy5odG1sP2lkPUdUTS1MRjIyIg0KaGVpZ2h0PSIwIiB3aWR0aD0iMCIgc3R5bGU9ImRpc3BsYXk6bm9uZTt2aXNpYmlsaXR5OmhpZGRlbiI%2BPC9pZnJhbWU%2BPC9ub3NjcmlwdD4NCjxzY3JpcHQ%2BKGZ1bmN0aW9uKHcsZCxzLGwsaSl7d1tsXT13W2xdfHxbXTt3W2xdLnB1c2goeydndG0uc3RhcnQnOg0KbmV3IERhdGUoKS5nZXRUaW1lKCksZXZlbnQ6J2d0bS5qcyd9KTt2YXIgZj1kLmdldEVsZW1lbnRzQnlUYWdOYW1lKHMpWzBdLA0Kaj1kLmNyZWF0ZUVsZW1lbnQocyksZGw9bCE9J2RhdGFMYXllcic%2FJyZsPScrbDonJztqLmFzeW5jPXRydWU7ai5zcmM9DQonLy93d3cuZ29vZ2xldGFnbWFuYWdlci5jb20vZ3RtLmpzP2lkPScraStkbDtmLnBhcmVudE5vZGUuaW5zZXJ0QmVmb3JlKGosZik7DQp9KSh3aW5kb3csZG9jdW1lbnQsJ3NjcmlwdCcsJ2RhdGFMYXllcicsJ0dUTS1MRjIyJyk7PC9zY3JpcHQ%2BDQo8IS0tIEVuZCBHb29nbGUgVGFnIE1hbmFnZXIgLS0%2BZGTEvZJMBaDQTuYZNi2b%2BZTWAdUHDw%3D%3D&__EVENTVALIDATION=%2FwEWBQK%2BrNvoBwLW3fTKCAL%2FyfnfBwKGyp6GDgKX0ebqC0WW7GcvKbKyjAOby%2FugKuooJj1a&scriptMgr=&ucLogin%24txtUserName%24txtUserName_TextBox='.$username.'&ucLogin%24txtPassword%24txtPassword_TextBox='.$password.'&btnLoginAccount%24btnLoginAccount_Button=Sign+in&hdnGuid=GUID';
				
				//print strlen($post_data); die;
				curl_setopt($Curl_Obj, CURLOPT_HTTPHEADER,  array(	'Content-Type: application/x-www-form-urlencoded',                                                                                
																'Content-Length: ' . strlen($post_data)
															));
				curl_setopt($Curl_Obj, CURLOPT_SSL_VERIFYPEER, FALSE);
				
			}
			else if($platform==8) {
				$login_url = 'https://www.peopleperhour.com/site/login';
				//$post_data = 'YII_CSRF_TOKEN=9b9329bce9d820582149bd4ecaf512cd9ffe09b7&LoginForm%5Bemail%5D='.$username.'&LoginForm%5Bpassword%5D='.$password.'&LoginForm%5BrememberMe%5D=0&LoginForm%5BrememberMe%5D=1&yt0=Log+In';
			} 
			else if($platform==4)
			{
				$ArrLogin = array(
					'ioBB'=>'',
					'remember_me'=>0,
					'submit'=>'Sign In',
					'username'=>$username,
					'password'=>$password
				);
				
				$login_url = 'https://www.odesk.com/login';
				$post_data = http_build_query($ArrLogin);
		
			}
			else if($platform==3)
			{
				$curent_url =  $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
				parse_str(parse_url($curent_url, PHP_URL_QUERY), $URL_GET);
				include_once('HTMLParser/simple_html_dom.php');
				$cookieFile = $this->getCookieFile($user_id);
				
				if(isset($URL_GET['challengeAnswer']) && isset($_SESSION['elance_question_redirection']))
				{
					//print_r($_SESSION['elance_question_redirection']);
					//die;
					parse_str(parse_url($_SESSION['elance_question_redirection'], PHP_URL_QUERY), $_MY_GET);
					$_MY_GET['action'] = 'submit';
					$_MY_GET['challengeAnswer'] = $URL_GET['challengeAnswer'];
					$_MY_GET['sign'] = '';
					$post_data = http_build_query($_MY_GET);
					
					$options = array(
								CURLOPT_USERAGENT		=> $_SERVER['HTTP_USER_AGENT'],
								CURLOPT_COOKIEFILE 		=> $cookieFile,
								CURLOPT_COOKIEJAR 		=> $cookieFile,
								CURLOPT_HEADER 			=> false,
								CURLOPT_RETURNTRANSFER 	=> true,
								CURLOPT_POST 			=> true,
								CURLOPT_POSTFIELDS 		=> $post_data,
								CURLOPT_VERBOSE 		=> true,
								CURLOPT_URL 			=> 'https://www.elance.com/php/trust/main/securityAuditAHR.php',
								//CURLOPT_URL 			=> $_SESSION['elance_question_redirection'],
								CURLOPT_HTTPHEADER		=> array(	'Content-Type: application/x-www-form-urlencoded; charset=utf-8',                                                                                
																	'Content-Length: ' . strlen($post_data)
																)
							);
				
					$response = $this->CurlPlatformPost($options);
					
					$StatusLog = json_decode($response,true);
					if($StatusLog['status']=='success')
					{
						unset($_SESSION['elance_question_redirection']);
						$_SESSION['connected_platform'][$platform]['is_connected'] = 1;
						$connected_success = true;
					} else if($StatusLog['status']=='error'){
						 $error_to_inform = 'Elance Error. ';
						foreach($StatusLog['errorMsgs'] as $err_value)
						{
							$error_to_inform .= $err_value;
						}
						die($error_to_inform);
					}
				}
				else 
				{
					if(isset($_SESSION['elance_question_redirection']))
					{
						unset($_SESSION['elance_question_redirection']);
					}

				
					$job_html = file_get_html('https://www.elance.com/php/landing/main/login.php');
					$token = $job_html->find('form#loginForm',0)->find('input#token',0)->getAttribute('value');
					//print $token; die;
					$ArrLogin = array(
						'mode'=>'signin',
						'crypted'=>'',
						'redirect'=>'',
						'login_type'=>'',
						'token'=>$token,
						'lnm'=>$username,
						'pwd'=>$password
					);

					$login_url = 'https://www.elance.com/php/reg/main/signInAHR.php';
					$post_data = http_build_query($ArrLogin);
					
					$options = array(
								CURLOPT_USERAGENT		=> $_SERVER['HTTP_USER_AGENT'],
								CURLOPT_COOKIEFILE 		=> $cookieFile,
								CURLOPT_COOKIEJAR 		=> $cookieFile,
								CURLOPT_HEADER 			=> false,
								CURLOPT_RETURNTRANSFER 	=> true,
								CURLOPT_POST 			=> true,
								CURLOPT_POSTFIELDS 		=> $post_data,
								CURLOPT_VERBOSE 		=> true,
								CURLOPT_URL 			=> $login_url,
								CURLOPT_HTTPHEADER		=> array(	'Content-Type: application/x-www-form-urlencoded; charset=utf-8',                                                                                
																	'Content-Length: ' . strlen($post_data)
																),
					);
					
					$response = $this->CurlPlatformPost($options);				
					$response = str_replace ("'","\"",$response);
					
					$response = '{' . $response . '}';
					$response_param = json_decode($response,true);
					
					//print_r($response_param); die;
					
					if($response_param['code']=='1')
					{
						$_SESSION['connected_platform'][$platform]['is_connected'] = 1;
						$connected_success = true;
					}
					
					else if($response_param['code']=='-1')
					{
						die($response_param['error']);
					}
					else if($response_param['code']=='4')
					{
						$_SESSION['elance_question_redirection'] = $response_param['redirect'];
						include_once('HTMLParser/simple_html_dom.php');
						$ElanceLogHtml = file_get_html($response_param['redirect']);
						$SecurityForm = $ElanceLogHtml->find('form#sa-securityForm',0);
						if($SecurityForm)
						{
							$challengeAnswerId = $SecurityForm->find('input#challengeAnswerId',0);
							if($challengeAnswerId)
							{
								$ElanceQuestion = $SecurityForm->find('div.formSection',0);
								die('Elance please response to question' . $ElanceQuestion);
							} else {
								die('You have not compete the registration on Elance. You have not specified secret questions');
							}							
						}
					}
				}					
			}
								
			if($platform!=3)
			{

			// Allow referer field when following Location redirects.
			curl_setopt($Curl_Obj, CURLOPT_AUTOREFERER, TRUE);

			// Follow server redirects.
			//curl_setopt($Curl_Obj, CURLOPT_FOLLOWLOCATION, true);

			// Return output as string.
			
					
			curl_setopt($Curl_Obj, CURLOPT_VERBOSE, 1);
			
			if($platform==8)
			{
				curl_setopt($Curl_Obj, CURLOPT_POST, 0);
				curl_setopt ($Curl_Obj, CURLOPT_URL, $login_url);
				$response = curl_exec ($Curl_Obj);
						
				$header_size = curl_getinfo($Curl_Obj, CURLINFO_HEADER_SIZE);
				$header = substr($response, 0, $header_size);
				if(strpos($header,'YII_CSRF_TOKEN=')!==false)
				{
					$start = strpos($header,'YII_CSRF_TOKEN=');
					$end = strpos($header, ';', $start);
					$YII_CSRF_TOKEN = substr($header, $start, ($end-$start));
				} else { //Daca nu e in headers inseamna ca ea deja este in fisierul [user_id]cookies.ttx
					$lines = file($cookieFile);
					//print_r($lines);
					foreach($lines as $key => $value)
					{
						if(strpos($value,'YII_CSRF_TOKEN')!==false)
						{
							$str_len = strlen($value);
							$pos = strpos($value,'YII_CSRF_TOKEN');
							$YII_CSRF_TOKEN = substr($value,$pos,($str_len-$pos-1));
							$YII_CSRF_TOKEN = str_replace("\t", '=', $YII_CSRF_TOKEN);
						}
					}
				}
				$post_data = $YII_CSRF_TOKEN.'&LoginForm%5Bemail%5D='.$username.'&LoginForm%5Bpassword%5D='.$password.'&LoginForm%5BrememberMe%5D=0&LoginForm%5BrememberMe%5D=1&yt0=Log+In';
				curl_setopt($Curl_Obj, CURLOPT_POST, 1);
			}
			// Set up post fields from login form.
			curl_setopt($Curl_Obj, CURLOPT_POSTFIELDS, $post_data); 

			// Set the url to which the data will be posted.
		
			curl_setopt ($Curl_Obj, CURLOPT_URL, $login_url);
				
			//print_r(get_headers($login_url));
				 
			// Execute the post and get the output.
			$response = curl_exec ($Curl_Obj);
			if($platform==4)
			{
				$response = curl_exec ($Curl_Obj);
			}
			//var_dump($response); die('asdasdasd');
			//var_dump($response);
			//print $response;
					
			$http_code = curl_getinfo($Curl_Obj, CURLINFO_HTTP_CODE);
					
			$header_size = curl_getinfo($Curl_Obj, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, CURLINFO_HEADER_SIZE);
			$body = substr($response, $header_size);
					
			curl_setopt($Curl_Obj, CURLOPT_POSTFIELDS, "");
			curl_close($Curl_Obj);
		
			if($platform==4)
			{
				$odesk_success = false;
				$lines = file($cookieFile);
				foreach($lines as $key => $value)
				{
					if( (strpos($value,'console_user')!==false) && (strpos($value,$username)!==false))
					{
						$odesk_success = true;
					}
				}
				if($odesk_success) //That means sign in process was successfully
				{ 
					$_SESSION['connected_platform'][$platform]['is_connected'] = 1;
					$connected_success = true;
				}
			}
			else if($platform==5)
			{
			//	print $header .'---';
			//	die('aici'); 
				if(strpos($header,'302 Found')!==false) //That means sign in process was successfully
				{
					$_SESSION['connected_platform'][$platform]['is_connected'] = 1;
					$connected_success = true;
				}
			}
			else if($platform==8)
			{
				if(strpos($header,'Location: https://www.peopleperhour.com/dashboard')!==false) //That means sign in process was successfully
				{
					$_SESSION['connected_platform'][$platform]['is_connected'] = 1;
					$connected_success = true;
				}
			}
			else if($platform==6 || $platform==9)
			{
				if(strpos($header,'Set-Cookie:')!==false) //That means sign in process was successfully
				{
					$_SESSION['connected_platform'][$platform]['is_connected'] = 1;
					$connected_success = true;
				}
			}
			else if($platform==10)
			{
				if(strpos($header,'?login_error=1')===false) //That means sign in process was successfully
				{
					$_SESSION['connected_platform'][$platform]['is_connected'] = 1;
					$connected_success = true;
				}
			}
			else if($platform==7)
			{
				if(strpos($header,'ETag:')===false) //That means sign in process was successfully
				{
					$_SESSION['connected_platform'][$platform]['is_connected'] = 1;
					$connected_success = true;
				}
			}
		}
			if($connected_success)
			{	
				$modelFromGeneral = new Application_Model_General();
				//$modelFromGeneral->ConnectUserPlatform($user_id,$platform);
				$modelFromGeneral->insertRemotePlatformUser($platform, $user_id, $username,$password);
				return true;
			}
			return false;

			/*	
			curl_setopt ($Curl_Obj, CURLOPT_URL, 'https://www.peopleperhour.com/dashboard');
			// Execute query and obtain content.
			$output = curl_exec($Curl_Obj);
				   
			echo $output;
			*/
		}
		
		public function getRemotePlatformProfile($platform_id)
		{
			Zend_Session::start();
			if($platform_id==3)
			{
				$modelFromGeneral = new Application_Model_General();
				$url = 'https://api.elance.com/api2/profiles/my?access_token='.$_SESSION['connected_platform'][3]['access_token'];
				$_SESSION['connected_platform'][3]['profile'] = $modelFromGeneral->cURLExtractJSONContent($url);
				$_SESSION['connected_platform'][3]['userid'] = $_SESSION['connected_platform'][3]['profile']['data']['providerProfile']['userId'];	
			}
			else if($platform_id==1)
			{
				require_once ('Freelancer/SnowTigerLib.php');
				$t = $_SESSION['connected_platform'][1]['access_token'];
				$ts = $_SESSION['connected_platform'][1]['access_token_secret'];
				$stl = new SnowTigerLib($t, $ts);
				$_SESSION['connected_platform'][1]['profile'] = $stl->getAccountDetails()->getArrayData();
				$_SESSION['connected_platform'][1]['userid'] = $_SESSION['connected_platform'][1]['profile']['userid'];
			}
			else if($platform_id==4)
			{
				require_once('Odesk/OdeskConfig.php');
				$t = $_SESSION['connected_platform'][4]['access_token'];
				$ts = $_SESSION['connected_platform'][4]['access_token_secret'];
				
				$secret_key     = $consumerSec . '&' . $ts;
				
				$params = array(
					'oauth_consumer_key'    => $consumerKey,
					'oauth_signature_method'=> $sigMethod,
					'oauth_timestamp'       => time(),
					'oauth_nonce'           => substr(md5(microtime(true)), 5),
					'oauth_token'           => $t
					);

				ksort($params);

				$method = 'GET';
				
				
				$params_string  = http_build_query($params);

				$url = 'https://www.odesk.com/api/auth/v1/info.json';
				$base_string= $method . '&' . urlencode($url) . '&' . urlencode($params_string);
				$signature  = base64_encode(hash_hmac('sha1', $base_string, $secret_key, true));

				$params['oauth_signature'] = $signature;

				$params_string = http_build_query($params);
				
				$url .= '?' . $params_string;
				
				$modelFromGeneral = new Application_Model_General();
				$_SESSION['connected_platform'][4]['profile'] = $modelFromGeneral->cURLExtractJSONContent($url);
				$_SESSION['connected_platform'][4]['userid'] = $_SESSION['connected_platform'][4]['profile']['auth_user']['uid'];
			}
		}
		
		public function connectPlatformsForUser($user_id)
		{
			Zend_Session::start();
			$modelFromGeneral = new Application_Model_General();
			$PlatformsInfoArray = $modelFromGeneral->getAllUserConnectedPlatforms($user_id);
			
			//print_r($PlatformsInfoArray);
			//die('here1');
			
			foreach($PlatformsInfoArray as $Values)
			{
				if((int)$Values['is_curl']==0)
				{
					if(isset($Values['access_token']))
					{
						$_SESSION['connected_platform'][$Values['platform_id']]['access_token'] = $Values['access_token'];
					}
					if(isset($Values['access_token_secret']))
					{
						$_SESSION['connected_platform'][$Values['platform_id']]['access_token_secret'] = $Values['access_token_secret'];
					}
					$_SESSION['connected_platform'][$Values['platform_id']]['is_connected'] = 1;
					//$this->getRemotePlatformProfile($Values['platform_id']);
				}
				else {
					$this->connectToPlatform($Values['username'], $Values['password'], (int)$Values['platform_id'], $user_id);
				}
			}
		}
			
		public function remoteAction()
		{
			//session_start();
			Zend_Session::start();
			
			if(isset($_SESSION['Zend_Auth']['storage']) && (int)$_SESSION['Zend_Auth']['storage']!=0)
			{
				
				$user_id = (int)$_SESSION['Zend_Auth']['storage'];
				$curent_url =  $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
				parse_str(parse_url($curent_url, PHP_URL_QUERY), $_MY_GET);
				
				if(isset($_MY_GET['username'])&& isset($_MY_GET['password']))
				{	
					$username = $_MY_GET['username'];
					$password = $_MY_GET['password'];
					$platform = (int)$_MY_GET['pl'];
					

					if($this->connectToPlatform($username,$password,$platform,$user_id))
					{
						die('1');
					} else {
						die('Error.');
					}
					
				}
			}
		}
		
		public function closewindowopenerAction() {
			echo("<script>window.opener.location.href = window.opener.location.href; self.close ();</script>");
			die;
		}
			
		public function odeskprofileAction()
		{
			$xml_string = '<iframe id="odesk_profile" src="https://www.odesk.com/api/auth/v1/info.json"></iframe>';
			$javascript = "<script>alert($('#odesk_profile').contents().find('html').html());</script>";
			print $xml_string.$javascript;
			//die;
		}
		
        public function loginAction()
        {	
            $db = $this->_getParam('db');

			if(isset($_SERVER['HTTP_REFERER']))
			{
				$referer = $_SERVER['HTTP_REFERER'];
				$curent_url =  $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
				if(substr($referer,-strlen($curent_url))!==$curent_url)
				{
					$session = new Zend_Session_Namespace('redirection');
					$session->redirection = $referer;
				}
			}
	 
            $loginForm = new Application_Model_LoginForm();

     
            if ($loginForm->isValid($_POST)) {
     
                $adapter = new Zend_Auth_Adapter_DbTable(
                    $db,
                    'accounts',
                //    'email',
                    'id',
                    'password',
					'(?) AND `confirmed` = 1'
                    );
				
				$email = $loginForm->getValue('email');
				$modelFromGeneral = new Application_Model_General();
				$user_id = $modelFromGeneral->getUserIdByEmail($email);
				
				if($user_id)
				{
					$adapter->setIdentity($user_id);
					//$adapter->setIdentity($loginForm->getValue('email'));
					//$password = md5(md5($UserInfo['password']) . 'dfd67fbcf54d99ef2dc2f900610255e4');
					$adapter->setCredential(md5(md5($loginForm->getValue('password')). 'dfd67fbcf54d99ef2dc2f900610255e4') );
				   
	//			   $adapter->setCredential($loginForm->getValue('password'));
		 
					$auth   = Zend_Auth::getInstance();
					$result = $auth->authenticate($adapter);
		 
					if ($result->isValid()) {
					
						$this->connectPlatformsForUser($user_id);
						
						$this->_helper->FlashMessenger('<span class="f_success_msg">Successful Login</span>');

						$redirection = '/';
						
						$session = new Zend_Session_Namespace('redirection');
						if (isset($session->redirection)) {
							$redirection = $session->redirection;
							unset($session->redirection);
						}
						
						$this->_redirect($redirection);
						return;
					} else {
						$this->view->message = '<span class="f_error_msg">Email or password is wrong</span>';
					}
				} else {
					$this->view->message = '<span class="f_error_msg">Email or password is wrong</span>';
				}
            }
     
			$this->view->form = $loginForm;
        }
    
		public function loginajaxAction()
		{
			$db = $this->_getParam('db');
			
			if ($this->getRequest()->isPost()) {
				$email = $this->_request->getPost('email');
				$password = $this->_request->getPost('password');
				$rememberMe = $this->getRequest()->getPost('remember_me');
				
				if (empty($email) || empty($password)) {
					$result = array(
						'success' => false,
						'message' => 'Please provide your e-mail address and password.'
					);
					$this->_helper->json($result);
				} else {
					$query = '(?)';
					$adapter = new Zend_Auth_Adapter_DbTable($db, 'accounts', 'id', 'password', $query);
					
					$modelFromGeneral = new Application_Model_General();
					$user_id = $modelFromGeneral->getUserIdByEmail($email);
					
					if ($user_id) {
						$adapter->setIdentity($user_id);
						$adapter->setCredential(md5(md5($password). 'dfd67fbcf54d99ef2dc2f900610255e4') );
						
						$auth = Zend_Auth::getInstance();
						$result = $auth->authenticate( $adapter );
						
						// Did the participant successfully login?
						if ($result->isValid()) {
							if ($rememberMe) {
								Zend_Session::rememberMe(31*24*60*60);
							}
							//$this->connectPlatformsForUser($user_id);
							$this->_helper->json(array('success' => true));
						} else {
							$this->_helper->json( array( 'success' => false, 'message' => 'Login failed. Have you confirmed your account?' ) );
						}
					} else {
						$this->_helper->json( array( 'success' => false, 'message' => 'Login failed. Have you confirmed your account?' ) );
					}
				}
			}
		}
        
		public function logoutAction()  
		{  
			Zend_Session::start();
			if(isset($_SESSION['returnUrl']) && $_SESSION['returnUrl']!='')
			{
			$this->_redirect($_SESSION['returnUrl']);
			}
			
			// clear everything - session is cleared also!  
			Zend_Auth::getInstance()->clearIdentity(); 
			session_destroy();
			$this->_redirect('/');  
			
		}
		
		public function facebookAction() {
			$token = $this->getRequest()->getParam('token',false);						
			if($token == false) {				
				return false; // redirect instead
			}
		 	Zend_Session::start();
			$auth = Zend_Auth::getInstance();
			$adapter = new Zend_Auth_Adapter_Facebook($token);
			$result = $auth->authenticate($adapter);

            //var_dump($result); echo '<br>###<br>';

			if($result->isValid()) {
				//	die('amus');
				//$user = $adapter->getUser();			
				
				$new_user_id = $adapter->getNewUserId();

 				if($new_user_id != 0)
				{
					$this->connectPlatformsForUser($new_user_id);
					$auth->getStorage()->write($new_user_id);
					$_SESSION['returnUrl'] = '/registration/updatepass';
					echo("<script language=javascript>window.opener.location.href='/registration/updatepass'; self.close ();</script>");
					die;				
					/*echo("<script>window.opener.location.href = window.opener.location.href; self.close ();</script>");*/
					echo("<script language=javascript>window.opener.location.reload(true); self.close ();</script>");
					die;
					$this->_redirect('/'); 
					return true; // redirect instead
				}
			}
			return false; // redirect instead
		}
		public function twitter2Action() {
		
			
			
			//var_dump($adapter); die;
			
			/*$configs = array('consumerSecret' => 'ZbsOVnrTb4N2LlO6PiSSnSj3K5EXCYQQ4GeZG3xRy8',
			'consumerKey' => 'Qk2UG6kOCoWFVvxre6St2w', 'callbackUrl' => '');?*/
			
			$configs = array(
						'consumerSecret' => 'sJv5ATiTIKXkcehhXZlPgzDJndz2WZcO1JgncWbEIkg',
						'consumerKey' => 'iPHammi5HfJCCu7vLkaFw', 
						'callbackUrl' => ''
					   );			
			
			$adapter = new Zend_Auth_Adapter_Twitter($configs);
			
			$auth = Zend_Auth::getInstance();
			
			$result = $auth->authenticate($adapter);
			
			var_dump($result); die('wwwwwwwwwwwwww');
			
			if($result->isValid()) {
				//die('amus');
				$user = $adapter->getUser();
				$auth->getStorage()->write($user);
				$this->_redirect('/'); 
				return true; // redirect instead
			}
			
			/*
		
		//	die('12');
			$token = new Zend_Oauth_Token_Access;
			$token->setParams(array(
			'oauth_token' => '856085400-C5w3S6doDW7k4ojiENOnFkFMadb73HZyyAjH8xEn',
			'oauth_token_secret' => 'RBrqcD1A8WnUmjN4JSNyE2MzoEm44HvzrBR8fYwYv0'
			));

		//	var_dump($token); die('----------');
			
			$twitter = new Zend_Service_Twitter(array(
			'consumerKey' => 'Qk2UG6kOCoWFVvxre6St2w', 
			'consumerSecret' => 'ZbsOVnrTb4N2LlO6PiSSnSj3K5EXCYQQ4GeZG3xRy8',
			'accessToken' => $token
));

			var_dump($twitter);*/
			//die('here');
		}
		
		public function linkedinAction() {
				Zend_Session::start();
			    $profile_image_url =  $_GET['img_url'];
				if($profile_image_url!='')
				{
					$img = file_get_contents($profile_image_url);
		        	$picfilename = @date("YmdHis").'.jpg';
					$file = realpath(dirname('.')).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'profilePictures'.DIRECTORY_SEPARATOR.$picfilename;
					file_put_contents($file, $img);
				}
			$xml_object = (object)array("id"=>$_GET['id'],"first-name"=>$_GET['first'],"last-name"=>$_GET['last'],"img_url"=>$picfilename);
			$modelFromRegistration = new Application_Model_Registration();
			$new_user_id = $modelFromRegistration->insertUserLinkedIn($xml_object);
			
			if($new_user_id != 0)
			{
				$_SESSION['Zend_Auth']['storage'] = $new_user_id;
				$this->connectPlatformsForUser($new_user_id);
				$_SESSION['returnUrl'] = '/registration/register';
				$this->_redirect('/registration/register');
			}			
			$this->_redirect('/');die;
			echo("<script>window.opener.location.reload(true); self.close ();</script>");
			die;
			
			
			/*@session_start();
			Zend_Session::start();
			$auth = Zend_Auth::getInstance();
			
			$ns = new Zend_Session_Namespace('linkedin_oauth');			
			
			$options = array(
				'localUrl' => SITE_URL . 'auth/linkedin/',
				'callbackUrl' => SITE_URL . 'auth/linkedin/',
				'requestTokenUrl' => 'https://api.linkedin.com/uas/oauth/requestToken',
				'userAuthorizationUrl' => 'https://api.linkedin.com/uas/oauth/authorize',
				'accessTokenUrl' => 'https://api.linkedin.com/uas/oauth/accessToken',
				'consumerKey' => '75fa0rgavbej7y',
				'consumerSecret' => 'qgUBpFF569NfQIHu',
			);
			$consumer = new Zend_Oauth_Consumer($options);
			
			if (!isset($_SESSION ['LINKEDIN_ACCESS_TOKEN'])){
				if(! empty ( $_GET )){					
										
					$accessToken = $consumer->getAccessToken ( $_GET, unserialize ( $_SESSION ['LINKEDIN_REQUEST_TOKEN'] ) ); 
				
					$_SESSION ['LINKEDIN_ACCESS_TOKEN'] = serialize ( $accessToken );
				}else{
					$requestToken = $consumer->getRequestToken();
					
					$_SESSION ['LINKEDIN_REQUEST_TOKEN'] = serialize ( $requestToken );
					
					$consumer->redirect();
				}
			}else{
				
				$accessToken = unserialize ( $_SESSION ['LINKEDIN_ACCESS_TOKEN'] ); 

				// Use HTTP Client with built-in OAuth request handling
				$client = $accessToken->getHttpClient($options);

				// Set LinkedIn URI
				$client->setUri('https://api.linkedin.com/v1/people/~');
				// Set Method (GET, POST or PUT)
				$client->setMethod(Zend_Http_Client::GET);
				// Get Request Response
				$response = $client->request();

				// Get the XML containing User's Profile
				$content =  $response->getBody();
				print_r($content);
				echo("<script>window.opener.location.reload(true); self.close ();</script>");
				die;
			}*/
			
			
			
			
			//session_start();
			/*Zend_Session::start();
			
			$config['base_url']             =   SITE_URL . 'auth/linkedin/';
			$config['callback_url']         =   SITE_URL . 'auth/linkedinprofile/';
			$config['linkedin_access']      =   '75fa0rgavbej7y';
			$config['linkedin_secret']      =   'qgUBpFF569NfQIHu';

			include_once "LinkedInAuth/linkedin.php";
			
			$linkedin = new LinkedIn($config['linkedin_access'], $config['linkedin_secret'], $config['callback_url'] );
			$linkedin->debug = true;
			
			# Now we retrieve a request token. It will be set as $linkedin->request_token
			$linkedin->getRequestToken();
			//$session = new Zend_Session_Namespace('redirection');
			//$session->requestToken = serialize($linkedin->request_token);
			$_SESSION['requestToken'] = serialize($linkedin->request_token);
		  
			# With a request token in hand, we can generate an authorization URL, which we'll direct the user to
			//echo "Authorization URL: " . $linkedin->generateAuthorizeUrl() . "\n\n";
					
			header("Location: " . $linkedin->generateAuthorizeUrl());
			die;*/
				
		}
				
		public function insertStorage($storage) {
			$auth = Zend_Auth::getInstance();	
			$auth->getStorage()->write($storage);
		}
		
		public function linkedinprofileAction() {
			//session_start();
			//Zend_Session::start();
			die("hi");
			
		//	$session = new Zend_Session_Namespace('redirection');
			$curent_url =  $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
  
			
			parse_str(parse_url($curent_url, PHP_URL_QUERY), $r);
			
			$config['base_url']             =   SITE_URL . 'auth/linkedin/';
			$config['callback_url']         =   SITE_URL . 'auth/linkedinprofile/';
			
			$config['linkedin_access']      =   '75fa0rgavbej7y';
			$config['linkedin_secret']      =   'qgUBpFF569NfQIHu';

			include_once "LinkedInAuth/linkedin.php";
			
	   
			# First step is to initialize with your consumer key and secret. We'll use an out-of-band oauth_callback
			$linkedin = new LinkedIn($config['linkedin_access'], $config['linkedin_secret'], $config['callback_url'] );
			$linkedin->debug = true;
		    if (isset($r['oauth_verifier'])){
				$_SESSION['oauth_verifier']     = $r['oauth_verifier'];

				$linkedin->request_token    =   unserialize($_SESSION['requestToken']);
				$linkedin->oauth_verifier   =   $_SESSION['oauth_verifier'];
				$linkedin->getAccessToken($r['oauth_verifier']);

				$_SESSION['oauth_access_token'] = serialize($linkedin->access_token);
				//header("Location: " . $config['callback_url']);
				header("Location: " . $linkedin->generateAuthorizeUrl());
				exit;
		   }
		   else{
				$linkedin->request_token    =   unserialize($_SESSION['requestToken']);
				$linkedin->oauth_verifier   =   $_SESSION['oauth_verifier'];
				$linkedin->access_token     =   unserialize($_SESSION['oauth_access_token']);
		   }


			# You now have a $linkedin->access_token and can make calls on behalf of the current member
			$xml_response = $linkedin->getProfile("~:(id,first-name,last-name,headline,picture-url)");

			$xml_object = simplexml_load_string($xml_response);
	
			if((int)$xml_object->id != 404 && (int)$xml_object->id != 401)
			{
				$modelFromRegistration = new Application_Model_Registration();
				$new_user_id = $modelFromRegistration->insertUserLinkedIn($xml_object);
				
				if($new_user_id != 0)
				{
					$_SESSION['Zend_Auth']['storage'] = $new_user_id;
					$this->connectPlatformsForUser($new_user_id);
				}
				
				//$this->insertStorage($xml_object->id);
				
				echo("<script>window.opener.location.reload(true); self.close ();</script>");
				
			}
die;
/*
			$search_response = $linkedin->search("?company-name=facebook&count=10");
			//$search_response = $linkedin->search("?title=software&count=10");

			//echo $search_response;
			$xml = simplexml_load_string($search_response);

			echo '<pre>';
			echo 'Look people who worked in facebook';
			print_r($xml);
			echo '</pre>';*/
		}

		public function twitterAction() {
		echo "<pre>";
		print_r($_GET);die;
			//session_start();
			Zend_Session::start();			
			//var_dump($_SESSION); die;

            //30.01.2014 If login with social platforms and password has been already introduced previously, don't show Password update form
            $modelFromGeneral = new Application_Model_General();
            $account_info = $modelFromGeneral->getUserById($userId);
            if(!empty($account_info["password"]) && strlen($account_info["password"]) > 0){
                if(isset($_SESSION['returnUrl']))
                    unset($_SESSION['returnUrl']);
                //$this->_redirect('/');
            }
	die;
            //30.01.2014 If login with social platforms and password has been already introduced previously, don't show Password update form
           /* $modelFromGeneral = new Application_Model_General();
            $account_info = $modelFromGeneral->getUserById($userId);
            if(!empty($account_info["password"]) && strlen($account_info["password"]) > 0){
                if(isset($_SESSION['returnUrl']))
                    unset($_SESSION['returnUrl']);
                $this->_redirect('/');
            }*/

			//if(isset($_SESSION['twitter_fm']['screen_name']) && isset($_SESSION['twitter_fm']['name']) )
			if(isset($_GET['screen_name']) && isset($_GET['name']) )
			{				
				$UserTwitter = array();
				//$UserTwitter['fname'] = $_SESSION['twitter_fm']['screen_name'];
				$UserTwitter['fname'] = $_GET['screen_name'];
				//unset($_SESSION['twitter_fm']['screen_name']);
				
				//$UserTwitter['lname'] = $_SESSION['twitter_fm']['name'];
				$UserTwitter['lname'] = $_GET['name'];
				//unset($_SESSION['twitter_fm']['name']);
				
				//$UserTwitter['social_id'] = $_SESSION['twitter_fm']['id'];
				$UserTwitter['social_id'] = $_GET['id'];
				
				$profile_image_url =  $_GET['img_url'];
				if($profile_image_url!='')
				{
					$img = file_get_contents($profile_image_url);
		        	$picfilename = @date("YmdHis").'.jpg';
					$file = realpath(dirname('.')).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'profilePictures'.DIRECTORY_SEPARATOR.$picfilename;
					file_put_contents($file, $img);
				}
				$UserTwitter['picture'] = $picfilename;
				
				//unset($_SESSION['twitter_fm']['id']);
				
				$modelFromRegistration = new Application_Model_Registration();
				$new_user_id = (int)$modelFromRegistration->insertUserTwitter($UserTwitter);
				if($new_user_id != 0)
				{
					$this->connectPlatformsForUser($new_user_id);
					$_SESSION['Zend_Auth']['storage'] = $new_user_id;
					$_SESSION['returnUrl'] = '/registration/register';
					$this->_redirect('/registration/register');
				}
				echo("<script>window.opener.location.reload(true); self.close ();</script>");
				die;
				$this->_redirect('/'); 
				return true; 
				
			}
		}
			
		public function flAction()
		{
			// 			session_start();
			$consumerKey = '209dda8981fd6a37be106cbee02cc9d4bec42ce4'; // consumer key, got in console
			$consumerSec = 'b847fab7e91ffe4f7fe8c317017ae45c226deb73'; // consumer secret, got in console with key
			
			//$consumerKey = 'a42d40a24fe99df1466bb781ce13c4ac'; // consumer key, got in console
			//$consumerSec = '5e722d5383eff298'; // consumer secret, got in console with key
			$sigMethod   = 'HMAC-SHA1'; // signature method, e.g. HMAC-SHA1
			$callbackUrl = SITE_URL . 'auth/fl/'; // callback url, full url to your script, e.g. http://localhost/oauth.php
			//$url         = 'https://www.odesk.com/services/api/keys/a42d40a24fe99df1466bb781ce13c4ac'; // api's url, e.g. http://www.odesk.com/api/mc/v1/threads/my_odesk_uid/22222.json (see MC documentation)

			$api_base = 'http://api.freelancer.com';
			
			$requestTokenUrl        = $api_base . '/RequestRequestToken/requestRequestToken.json';
			$accessTokenUrl         = $api_base . '/RequestAccessToken/requestAccessToken.json';
			//$userAuthorizationUrl   = 'https://www.odesk.com/services/api/auth';

			$config = array(
						'version'               => '1.0',
						'callbackUrl'           => $callbackUrl,
						'signatureMethod'       => $sigMethod,
						'requestTokenUrl'       => $requestTokenUrl,
						'accessTokenUrl'        => $accessTokenUrl,
				//		'userAuthorizationUrl'  => $userAuthorizationUrl,
						'consumerKey'           => $consumerKey,
						'consumerSecret'        => $consumerSec
			);

		
			
			$consumer = new Zend_Oauth_Consumer($config);
			 
			// Get request token
			if (!isset($_SESSION['REQUEST_TOKEN']) && !isset($_SESSION['ACCESS_TOKEN'])) {
				$token = $consumer->getRequestToken();

				$_SESSION['REQUEST_TOKEN'] = serialize($token);

				die('here');
				$consumer->redirect();
			}
			
			$curent_url =  $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			parse_str(parse_url($curent_url, PHP_URL_QUERY), $_MY_GET);
			//print_r($_MY_GET);

			// Get access token
			if (!empty($_MY_GET) && isset($_SESSION['REQUEST_TOKEN'])) {

				$token = $consumer->getAccessToken(
							 $_MY_GET,//$_GET,
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
				
				//It seems that this does mean that we have already login into odesk account
				
				$token = unserialize($_SESSION['ACCESS_TOKEN']);
				$t  = $token->getToken();
				$ts = $token->getTokenSecret();
				
				$params = array(
				'oauth_consumer_key'    => $consumerKey,
				'oauth_signature_method'=> $sigMethod,
				'oauth_timestamp'       => time(),
				'oauth_nonce'           => substr(md5(microtime(true)), 5),
			 //   'oauth_callback'        => $callbackUrl,
				'oauth_token'           => $t
				);

				ksort($params);

				$method = 'GET';
				$secret_key     = $consumerSec . '&' . $ts;
				$params_string  = http_build_query($params);

				$url = 'https://www.odesk.com/api/auth/v1/info.json';
				$base_string= $method . '&' . urlencode($url) . '&' . urlencode($params_string);
				$signature  = base64_encode(hash_hmac('sha1', $base_string, $secret_key, true));

				$params['oauth_signature'] = $signature;

				$params_string = http_build_query($params);
				
				$url .= '?' . $params_string;
				$modelFromGeneral = new Application_Model_General();
				$_SESSION['profile']['odesk'] = $modelFromGeneral->cURLExtractJSONContent($url);
				header("Location: " . '/auth/closewindowopener/');
			
				die;						
			}
		}

		public function getCurlContent($curl_url,$user_id)
		{
			$cookieFile = dirname(__FILE__).'/cookies/'.$user_id.'cookie.txt';
			$Curl_Obj = curl_init();

			if(!file_exists( $cookieFile)) {
				$fh = fopen($cookieFile, "w");
				fwrite($fh, '');
				fclose($fh);
				chmod($cookieFile, 0777);
			}
				
			curl_setopt ($Curl_Obj, CURLOPT_COOKIEFILE, $cookieFile); 
			curl_setopt ($Curl_Obj, CURLOPT_COOKIEJAR, $cookieFile); 

			// Set the browser you will emulate
			//$userAgent = 'Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20100101 Firefox/4.0.1';
			$userAgent = 'Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/20100101 Firefox/17.0';
			curl_setopt($Curl_Obj, CURLOPT_USERAGENT, $userAgent);

			// Don't include the header in the output.
			curl_setopt ($Curl_Obj, CURLOPT_HEADER, 0);

			// Allow referer field when following Location redirects.
			curl_setopt($Curl_Obj, CURLOPT_AUTOREFERER, TRUE);
		
			// Follow server redirects.
			//curl_setopt($Curl_Obj, CURLOPT_FOLLOWLOCATION, true);

			// Return output as string.
			curl_setopt ($Curl_Obj, CURLOPT_RETURNTRANSFER, 1);
						
			curl_setopt($Curl_Obj, CURLOPT_VERBOSE, 1);
			curl_setopt($Curl_Obj, CURLOPT_POST, 0);
			curl_setopt ($Curl_Obj, CURLOPT_URL, $curl_url);
			$response = curl_exec ($Curl_Obj);
			return $response;
		}
	
		public function mihaiAction()
		{
			Zend_Session::start();
			$_SESSION['Zend_Auth']['storage'] = 44;
			$this->connectPlatformsForUser(44);
			die("V-ati logat in accountul meu id=44 acum puteti deschide oricare pagina,deja veti fi logat");
		}
	
		public function curl_setopt_custom_postfields($ch, $postfields, $headers = null) {
			$algos = hash_algos();
			$hashAlgo = null;
			foreach ( array('sha1', 'md5') as $preferred ) {
				if ( in_array($preferred, $algos) ) {
					$hashAlgo = $preferred;
					break;
				}
			}
			if ( $hashAlgo === null ) { list($hashAlgo) = $algos; }
			$boundary =
				'----------------------------' .
				substr(hash($hashAlgo, 'cURL-php-multiple-value-same-key-support' . microtime()), 0, 12);

			$body = array();
			$crlf = "\r\n";
			$fields = array();
			foreach ( $postfields as $key => $value ) {
				if ( is_array($value) ) {
					foreach ( $value as $v ) {
						$fields[] = array($key, $v);
					}
				} else {
					$fields[] = array($key, $value);
				}
			}
			foreach ( $fields as $field ) {
				list($key, $value) = $field;
				if ( strpos($value, '@') === 0 ) {
					preg_match('/^@(.*?)$/', $value, $matches);
					list($dummy, $filename) = $matches;
					$body[] = '--' . $boundary;
					$body[] = 'Content-Disposition: form-data; name="' . $key . '"; filename="' . basename($filename) . '"';
					$body[] = 'Content-Type: application/octet-stream';
					$body[] = '';
					$body[] = file_get_contents($filename);
				} else {
					$body[] = '--' . $boundary;
					$body[] = 'Content-Disposition: form-data; name="' . $key . '"';
					$body[] = '';
					$body[] = $value;
				}
			}
			$body[] = '--' . $boundary . '--';
			$body[] = '';
			$contentType = 'multipart/form-data; boundary=' . $boundary;
			$content = join($crlf, $body);
			$contentLength = strlen($content);

			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Length: ' . $contentLength,
				'Expect: 100-continue',
				'Content-Type: ' . $contentType,
			));

			curl_setopt($ch, CURLOPT_POSTFIELDS, $content);

		}
	
		public function hasdeuAction()
		{
			Zend_Session::start();
			//$curl_url = 'http://hasdeu.creativsoft.md/admin/chadmin/childrensubcategories/?action=save&id=0';
			$this->cookie_file = dirname(__FILE__).'/cookies/44cookie.txt';
			$cookieFile = dirname(__FILE__).'/cookies/44cookie.txt';
		//	$testFile = dirname(__FILE__).'/cookies/test.txt';
			
		/*print <<<TEXT
			<html>
		<body>
			<form method="post" action="" id="frmBase" style="margin-top: 0px">
	<div>
	<input type="hidden" name="scriptMgr_HiddenField" id="scriptMgr_HiddenField" value="">
	<input type="hidden" name="__LASTFOCUS" id="__LASTFOCUS" value="">
	<input type="hidden" name="__EVENTTARGET" id="__EVENTTARGET" value="">
	<input type="hidden" name="__EVENTARGUMENT" id="__EVENTARGUMENT" value="">
	<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="/wEPDwUKLTg2MzEwMzM4Nw9kFgICAw8WAh4EVGV4dAW5BDwhLS0gR29vZ2xlIFRhZyBNYW5hZ2VyIC0tPg0KPG5vc2NyaXB0PjxpZnJhbWUgc3JjPSIvL3d3dy5nb29nbGV0YWdtYW5hZ2VyLmNvbS9ucy5odG1sP2lkPUdUTS1MRjIyIg0KaGVpZ2h0PSIwIiB3aWR0aD0iMCIgc3R5bGU9ImRpc3BsYXk6bm9uZTt2aXNpYmlsaXR5OmhpZGRlbiI+PC9pZnJhbWU+PC9ub3NjcmlwdD4NCjxzY3JpcHQ+KGZ1bmN0aW9uKHcsZCxzLGwsaSl7d1tsXT13W2xdfHxbXTt3W2xdLnB1c2goeydndG0uc3RhcnQnOg0KbmV3IERhdGUoKS5nZXRUaW1lKCksZXZlbnQ6J2d0bS5qcyd9KTt2YXIgZj1kLmdldEVsZW1lbnRzQnlUYWdOYW1lKHMpWzBdLA0Kaj1kLmNyZWF0ZUVsZW1lbnQocyksZGw9bCE9J2RhdGFMYXllcic/JyZsPScrbDonJztqLmFzeW5jPXRydWU7ai5zcmM9DQonLy93d3cuZ29vZ2xldGFnbWFuYWdlci5jb20vZ3RtLmpzP2lkPScraStkbDtmLnBhcmVudE5vZGUuaW5zZXJ0QmVmb3JlKGosZik7DQp9KSh3aW5kb3csZG9jdW1lbnQsJ3NjcmlwdCcsJ2RhdGFMYXllcicsJ0dUTS1MRjIyJyk7PC9zY3JpcHQ+DQo8IS0tIEVuZCBHb29nbGUgVGFnIE1hbmFnZXIgLS0+ZGTEvZJMBaDQTuYZNi2b+ZTWAdUHDw==">
	</div>

	<div>

		<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="/wEWBQK+rNvoBwLW3fTKCAL/yfnfBwKGyp6GDgKX0ebqC0WW7GcvKbKyjAOby/ugKuooJj1a">
	</div> 

	<input type="hidden" name="scriptMgr" id="scriptMgr">

		<div class="wrapper">
			<div class="headerRow1" align="center" style="height: 10px">
			</div>

			<!-- Guru.com Logo, Starts -->
			<div align="center" class="container">
				<div class="headerRow2 clearfix">
					<div align="left" class="paddingLeft5">
						<a href="/index.aspx" style="border: 0px;">
							<img src="/images/shim.gif" class="logoSOut" align="absmiddle" title="Guru.com Home" border="0"></a>
					</div>
				</div>
			</div>
			<!-- Guru.com Logo, Ends -->
			
			
			<div class="container">
				<!-- Login and Register panel, Starts -->
				<div>
					<table border="0" cellpadding="0" cellspacing="0" width="100%" class="paddingTop20 paddingLeft10">
						<tbody><tr>
							<td valign="top" width="43%">
								<div class="txt24px txtGreenB8D33C">Welcome to Guru</div>

								<!--Left Pane for Login, Starts -->
								<div class="leftSide">
									<table id="leftTab" cellpadding="0" cellspacing="0" border="0" width="100%">                                    
										<tbody><tr>
											<td>
												<h2 class="txt22px txt444" style="font-weight: normal; margin-top: 0px;">
													Sign in to Guru</h2>                                            
											</td>
										</tr>

										<!--Validation Summary, Starts -->
										<tr style="width: 95%;">
											<td>
												<span id="valSummary"><div id="valSummary_valSummary_ErrorText" class="errorMsg" style="display:none"><div> Incorrect username and/or password. </div></div></span>
											</td>
										</tr>
										<!--Validation Summary, Ends -->

										<!--Login Usercontrol, Starts -->
										<tr>
											<td>
												

	   
	<!--Username-->
	<div class="inputSec" style="margin-bottom:1em;">
		
		<span id="ucLogin_txtUserName"><div id="ucLogin_txtUserName_txtUserName_DefaultText" class="placeHolderBad" style="">Username</div><input name="ucLogin$txtUserName$txtUserName_TextBox" type="text" maxlength="35" id="ucLogin_txtUserName_txtUserName_TextBox" class="invalid txtInput"><span id="ucLogin_txtUserName_txtUserName_NameSign" style="" class="signBad">: (</span></span>       
	</div>

	<!--Password-->
	<div class="inputSec">
		
		<span id="ucLogin_txtPassword"><div id="ucLogin_txtPassword_txtPassword_DefaultText" class="placeHolder">Password</div><input name="ucLogin$txtPassword$txtPassword_TextBox" type="password" maxlength="32" id="ucLogin_txtPassword_txtPassword_TextBox" class="txtInput" oncopy="return false" onpaste="return false" ondrag="return false" ondrop="return false"><span id="ucLogin_txtPassword_txtPassword_NameSign" style="display:none"></span></span>
	</div>
					



											</td>
										</tr>
										<!--Login Usercontrol, Ends -->

										<tr>
											<td class="paddingTop10 forgot">
												<a href="/emp/forgotsignin.aspx" tabindex="4" class="BlueLinks">Forgot Your Username
													or Password?</a>
											</td>
										</tr>

										<tr>
											<td class="paddingTop10">
												<div>
													<span id="btnLoginAccount" errortext="Invalid Entries" class="primaryBt"><input type="submit" name="btnLoginAccount$btnLoginAccount_Button" value="Sign in" id="btnLoginAccount_btnLoginAccount_Button" class="btn inlineButton txt18px"><div id="btnLoginAccount_btnLoginAccount_Progress" style="position: fixed; z-index: 100001; left: 574px; top: 169.5px; display: none; margin-top: 35px;" conduit_fixed_handled="true" conduit_orig_mtop_val="0"><div class="progress"> <img src="/WebResource.axd?d=vvuCs0-pysRzUUEO-s0c_2YLasAr1ghA0R01Jp3mQg5IUUUj5VLkanZX4Cuuugas4fKZ_ieS4UfSbzcmHs4ponJ_4x2Hi6V5xWPGmtcQelxm5OBDeRJ0LzkhRDXAgCaQthvsoT95P1HE5SfHlLqEZTTeW-k1&amp;t=634959549685129142"></div></div></span>
												</div>
											</td>
										</tr>

									</tbody></table>
								</div>
								 <!--Left Pane for Login, Ends -->

							</td>

							<td class="separator" width="3%">
								<strong>&nbsp;</strong>
							</td>

							<!--Right Pane for redirect to Register, Starts -->
							<td valign="top">
								<div style="height: 25px;">
									&nbsp;</div>
								<div class="rightSide">
									<table cellpadding="0" cellspacing="0" border="0" width="100%">
										<tbody><tr>
											<td>
												<h2 class="txt22px txt444" style="font-weight: normal; margin-top: 0px; margin-bottom: 0px;">
													New to Guru? Join Today!</h2>

												<div class="button paddingTop15">
													<input type="button" id="btnRegister1" onClick="regRedirect();" value="Register" title="Register" class="greyButton txt18px">
												</div>
											</td>
										</tr>
									</tbody></table>
								</div>
							</td>
							<!--Right Pane for redirect to Register, Ends -->

						</tr>
					</tbody></table>
				</div>
				<!-- Login and Register panel, Ends -->

			</div>
			
			<div class="push">
			</div>
		</div>

		<!--Footer section, Starts -->
		<div class="footerBox" align="center">
			<div class="container" align="left">
				<div class="footerRow2">

					<div style="float: right">
						<a href="https://seal.verisign.com/splash?form_file=fdf/splash.fdf&amp;dn=WWW.GURU.COM&amp;lang=en" rel="nofollow" onClick="window.open(this.href); return false;" onKeyPress="window.open(this.href); return false;">
							<img src="/images/cleardot.gif" class="footerSprite verisign">
						</a><a href="#" onClick="javascript:window.open('https://www.paypal.com/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside','olcwhatispaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=800, height=350');">
							<img src="/images/cleardot.gif" class="footerSprite paypal">
						</a>
					</div>

					<div class="txt666 txt11px">
						ɠGuru.com 2012 <span>|</span> All Rights Reserved.
					 </div>

					<div style="clear: both">
					</div>
				</div>
			</div>
		</div>
		<!--Footer section, Ends -->

		<input type="hidden" name="hdnGuid" id="hdnGuid" value="GUID">

	</form>
		</body>
	</html>
	TEXT;*/
		
			$login_url = 'https://www.guru.com/login.aspx';
			$options = array(
											CURLOPT_USERAGENT		=> $_SERVER['HTTP_USER_AGENT'],
											CURLOPT_COOKIEFILE 		=> $cookieFile,
											CURLOPT_COOKIEJAR 		=> $cookieFile,
											CURLOPT_HEADER 			=> true,
											//CURLOPT_AUTOREFERER 	=> true,
											CURLOPT_RETURNTRANSFER 	=> true,
											CURLOPT_POST 			=> false,
										//	CURLOPT_VERBOSE 		=> true,
											CURLOPT_URL 			=> $login_url 
										);
			$cUrlHtml= $this->CurlPlatformPost($options); 
			//var_dump($cUrlHtml);
			//die;
			//if(isset($_POST))
		//	{
			
	//	print_r($_POST);
			
			$post_data = 'scriptMgr_HiddenField=&__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUKLTg2MzEwMzM4Nw9kFgICAw8WAh4EVGV4dAW5BDwhLS0gR29vZ2xlIFRhZyBNYW5hZ2VyIC0tPg0KPG5vc2NyaXB0PjxpZnJhbWUgc3JjPSIvL3d3dy5nb29nbGV0YWdtYW5hZ2VyLmNvbS9ucy5odG1sP2lkPUdUTS1MRjIyIg0KaGVpZ2h0PSIwIiB3aWR0aD0iMCIgc3R5bGU9ImRpc3BsYXk6bm9uZTt2aXNpYmlsaXR5OmhpZGRlbiI%2BPC9pZnJhbWU%2BPC9ub3NjcmlwdD4NCjxzY3JpcHQ%2BKGZ1bmN0aW9uKHcsZCxzLGwsaSl7d1tsXT13W2xdfHxbXTt3W2xdLnB1c2goeydndG0uc3RhcnQnOg0KbmV3IERhdGUoKS5nZXRUaW1lKCksZXZlbnQ6J2d0bS5qcyd9KTt2YXIgZj1kLmdldEVsZW1lbnRzQnlUYWdOYW1lKHMpWzBdLA0Kaj1kLmNyZWF0ZUVsZW1lbnQocyksZGw9bCE9J2RhdGFMYXllcic%2FJyZsPScrbDonJztqLmFzeW5jPXRydWU7ai5zcmM9DQonLy93d3cuZ29vZ2xldGFnbWFuYWdlci5jb20vZ3RtLmpzP2lkPScraStkbDtmLnBhcmVudE5vZGUuaW5zZXJ0QmVmb3JlKGosZik7DQp9KSh3aW5kb3csZG9jdW1lbnQsJ3NjcmlwdCcsJ2RhdGFMYXllcicsJ0dUTS1MRjIyJyk7PC9zY3JpcHQ%2BDQo8IS0tIEVuZCBHb29nbGUgVGFnIE1hbmFnZXIgLS0%2BZGTEvZJMBaDQTuYZNi2b%2BZTWAdUHDw%3D%3D&__EVENTVALIDATION=%2FwEWBQK%2BrNvoBwLW3fTKCAL%2FyfnfBwKGyp6GDgKX0ebqC0WW7GcvKbKyjAOby%2FugKuooJj1a&scriptMgr=&ucLogin%24txtUserName%24txtUserName_TextBox=zimbru&ucLogin%24txtPassword%24txtPassword_TextBox=craca95tit&btnLoginAccount%24btnLoginAccount_Button=Sign+in&hdnGuid=GUID';
			
			
			//$post_data = urlencode($_POST);
			//$post_data = http_build_query($_POST); 
			
			$options = array(
											CURLOPT_USERAGENT		=> $_SERVER['HTTP_USER_AGENT'],
											CURLOPT_COOKIEFILE 		=> $cookieFile,
											CURLOPT_COOKIEJAR 		=> $cookieFile,
											CURLOPT_HEADER 			=> true,
										//	CURLOPT_AUTOREFERER 	=> true,
											CURLOPT_RETURNTRANSFER 	=> true,
											CURLOPT_POST 			=> true,
										//	CURLOPT_VERBOSE 		=> true,
											CURLOPT_POSTFIELDS 		=> $post_data,
											CURLOPT_URL 			=> $login_url,
											/*CURLOPT_HTTPHEADER		=> array(	'Content-Type: application/x-www-form-urlencoded',                                                                                
																				'Content-Length: ' . strlen($post_data),
																				'Cookie: ASP.NET_SessionId=mgv30nrchkz5rqejuufpjnvt; __utma=48550832.352747461.1360744150.1360744150.1360744150.1; __utmb=48550832.1.10.1360744150; __utmc=48550832; __utmz=48550832.1360744150.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)'
																			)*/
										);
			$cUrlHtml= $this->CurlPlatformPost($options); 
			var_dump($cUrlHtml);
			die;
		//	}
			die;
		}
	
		public function request($method, $url, $vars = array(), $enctype = NULL) {
			$this->error = '';
			$this->request = curl_init();
			if (is_array($vars) && $enctype != 'multipart/form-data') $vars = http_build_query($vars, '', '&');
			
			$this->set_request_method($method);
			$this->set_request_options($url, $vars);
			$this->set_request_headers();
		   
			$response = curl_exec($this->request);
			
			if ($response) {
				var_dump($response);  //new CurlResponse($response);
			} else {
				$this->error = curl_errno($this->request).' - '.curl_error($this->request);
			}
			
			curl_close($this->request);
			
			return $response;
		}
	
		protected function set_request_method($method) {
			switch (strtoupper($method)) {
				case 'HEAD':
					curl_setopt($this->request, CURLOPT_NOBODY, true);
					break;
				case 'GET':
					curl_setopt($this->request, CURLOPT_HTTPGET, true);
					break;
				case 'POST':
					curl_setopt($this->request, CURLOPT_POST, true);
					break;
				default:
					curl_setopt($this->request, CURLOPT_CUSTOMREQUEST, $method);
			}
		}
	
	    protected function set_request_options($url, $vars) {
        curl_setopt($this->request, CURLOPT_URL, $url);
        if (!($vars)) curl_setopt($this->request, CURLOPT_POSTFIELDS, $vars);
        
        # Set some default CURL options
        curl_setopt($this->request, CURLOPT_HEADER, true);
        curl_setopt($this->request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->request, CURLOPT_USERAGENT, $this->user_agent);
        if ($this->cookie_file) {
            curl_setopt($this->request, CURLOPT_COOKIEFILE, $this->cookie_file);
            curl_setopt($this->request, CURLOPT_COOKIEJAR, $this->cookie_file);
        }
        //if ($this->follow_redirects) curl_setopt($this->request, CURLOPT_FOLLOWLOCATION, true);
        if ($this->referer) curl_setopt($this->request, CURLOPT_REFERER, $this->referer);
        
        # Set any custom CURL options
        foreach ($this->options as $option => $value) {
            curl_setopt($this->request, constant('CURLOPT_'.str_replace('CURLOPT_', '', strtoupper($option))), $value);
        }
    }
	
		protected function set_request_headers() {
			$headers = array();
			foreach ($this->headers as $key => $value) {
				$headers[] = $key.': '.$value;
			}
			curl_setopt($this->request, CURLOPT_HTTPHEADER, $headers);
		}
		
		public function CurlPlatformPost($options=array())
		{
			$Curl_Obj = curl_init();
			//curl_setopt($Curl_Obj, CURLOPT_SSL_VERIFYPEER, FALSE);		
			curl_setopt_array($Curl_Obj, $options);
			$response = curl_exec($Curl_Obj);
			/*
			print_r( curl_getinfo($Curl_Obj));
			if(!curl_errno($Curl_Obj))
			{
			 $info = curl_getinfo($Curl_Obj);

			 echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'];
			}*/
			//print curl_error($Curl_Obj);
			curl_close($Curl_Obj);
			
			return $response;	
		}
	
		public function getCookieFile($user_id)
		{
			$cookieFile = dirname(__FILE__).'/cookies/'.$user_id.'cookie.txt';
					
			//$cookieFile = 'cookie.txt';
			if(!file_exists( $cookieFile)) {
				$fh = fopen($cookieFile, "w");
				fwrite($fh, '');
				fclose($fh);
				chmod($cookieFile, 0777);
			}
			return $cookieFile;
		}
		
		public function CurlPlatformPost2($options=array())
		{
			$Curl_Obj = curl_init();	
			curl_setopt_array($Curl_Obj, $options);
			$response = curl_exec($Curl_Obj);

			$header_size = curl_getinfo($Curl_Obj, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, CURLINFO_HEADER_SIZE);
			$body = substr($response, $header_size);
			
			curl_close($Curl_Obj);
			
			return array('response'=>$response,'body'=>$body,'header'=>$header);	
		}

		
}